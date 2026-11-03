# Desplegament de SocialGest a CloudPanel

Guia pas a pas per instal·lar SocialGest (XarxesGest, fork de TryPost) a `https://socialgest.soportic.es` en un VPS amb CloudPanel, sense Docker. És el procés seguit a la instal·lació real de novembre de 2026. Els fitxers de suport són a `deploy/cloudpanel/`.

A les ordres, `soportic-socialgest` és l'usuari del lloc a CloudPanel. Si en fas servir un altre, canvia'l.

**Requisits:** 2 vCPU, 4 GB de RAM lliures i 25 GB de disc lliure com a mínim. Ubuntu 24.04 amb CloudPanel 2.x. Cal accés `root` per SSH.

---

## 1. Comprovacions del servidor

Com a `root`:

```bash
lsb_release -ds; nproc; free -h | head -2; df -h / | tail -1; php8.4 -m | grep -E '^(bcmath|intl|gd|imagick|redis|pdo_mysql|zip|mbstring)$'; mysql --version
```

- Han de sortir les vuit extensions de PHP. **`imagick` és imprescindible**: sense ella el calendari dona error 500. Si en falta alguna: `apt-get install -y php8.4-<extensió> && systemctl restart php8.4-fpm`.
- CloudPanel sol instal·lar **MariaDB 10.11**. L'app hi funciona: la suite de backend hi passa sencera, excepte els tests d'HEIC, que depenen de la llibreria del sistema. Al `.env` cal `DB_CONNECTION=mariadb`. Amb MySQL 8, cal `mysql`.

## 2. Paquets del servidor

Com a `root`:

```bash
apt-get update && apt-get install -y supervisor redis-server && systemctl enable --now supervisor redis-server
```

```bash
systemctl is-active supervisor redis-server; redis-cli ping; ss -ltnp | grep -E ':(6379|8080|8090)\b'
```

El port **8080** el fa servir l'Nginx de CloudPanel per al bloc intern del vhost. Per això Reverb escolta al **8090**, que ha d'estar lliure.

## 3. DNS, lloc i certificat (panell de CloudPanel)

1. Al DNS del domini, crea un registre **A** `socialgest` que apunti a la IP del VPS. Si el domini passa per Cloudflare, posa'l **sense proxy** (núvol gris).
2. **Sites → Add Site → Create a PHP Site**: Application **Laravel 12**, que serveix per a Laravel 13; domini `socialgest.soportic.es`; PHP **8.4**; Site User `soportic-socialgest`.
3. **Sites → socialgest.soportic.es → SSL/TLS → New Let's Encrypt Certificate.**
4. **Sites → socialgest.soportic.es → PHP Settings**: `memory_limit` 512M, `max_execution_time` 300, `upload_max_filesize` 1024M i `post_max_size` 1024M.

Comprovació, com a `root`:

```bash
echo "DNS: $(dig +short socialgest.soportic.es) | VPS: $(curl -4 -s ifconfig.me)"
```

## 4. Codi

Com a usuari del lloc:

```bash
su - soportic-socialgest
```

Node 22, que només serveix per compilar el frontend:

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash && source ~/.nvm/nvm.sh && nvm install 22 && node -v
```

Clona el repositori dins del directori del lloc. Així es conserven els permisos que hi posa CloudPanel:

```bash
cd ~/htdocs/socialgest.soportic.es && rm -rf public && git clone https://github.com/soportic-orb/XarxesGest.git . && git checkout main && git log --oneline -1
```

## 5. Base de dades i `.env`

1. **Sites → socialgest.soportic.es → Databases → Add Database**: base de dades i usuari `socialgest`. Copia la contrasenya.
2. Crea el `.env` a partir de la plantilla i genera'n les claus de Reverb:

```bash
cp deploy/cloudpanel/env.production.example .env && sed -i "s/^REVERB_APP_ID=.*/REVERB_APP_ID=$(shuf -i 100000-999999 -n1)/; s/^REVERB_APP_KEY=.*/REVERB_APP_KEY=$(openssl rand -hex 16)/; s/^REVERB_APP_SECRET=.*/REVERB_APP_SECRET=$(openssl rand -hex 32)/" .env
```

3. Edita'l amb `nano .env` i omple com a mínim:
   - `DB_PASSWORD`;
   - `HORIZON_ALLOWED_EMAILS`, amb el teu correu;
   - `ANTHROPIC_API_KEY`;
   - `MAIL_*` (vegeu el pas 9).

   Si la contrasenya té caràcters especials, posa-la entre cometes.

4. Dependències, clau de l'app, claus d'OAuth i comprovació de la base de dades. `composer install` necessita que el `.env` ja existeixi, perquè en acabar arrenca Laravel:

```bash
php8.4 /usr/local/bin/composer install --no-dev --no-interaction --optimize-autoloader 2>&1 | tail -3 && php8.4 artisan key:generate --force && php8.4 artisan passport:keys && php8.4 artisan db:show 2>&1 | head -4
```

Les claus de Passport es queden a `storage/oauth-*.key`, i Passport les llegeix si `PASSPORT_*_KEY` és buit. No cal copiar-les al `.env`.

## 6. Primer desplegament i usuari propietari

```bash
bash deploy/cloudpanel/deploy.sh 2>&1 | tee ~/deploy-primer.log | grep -E "^==>|ERROR|FAIL|failed"
```

Han de sortir les etapes `==> …` fins a `==> Done`. Els avisos de `horizon:terminate` i `reverb:restart` del primer cop són normals.

Crea el teu usuari. El registre públic està tancat; la resta d'usuaris s'hi afegeixen per invitació. A CloudPanel, PHP no detecta el terminal com a interactiu dins d'un `su -`, així que la contrasenya es passa per una variable d'entorn:

```bash
read -rsp "Contrasenya (mín. 12 caràcters): " P && echo && XARXESGEST_ADMIN_PASSWORD="$P" php8.4 artisan xarxesgest:create-admin --name="Nom Cognom" --email=correu@soportic.es; unset P
```

No facis servir `db:seed --class=UserSeeder`: crea `admin@trypost.it` amb la contrasenya `password`.

## 7. Nginx (panell de CloudPanel)

Edita el vhost a **Sites → socialgest.soportic.es → Vhost**. No l'editis a `/etc/nginx`, perquè CloudPanel el regenera. La plantilla té dos blocs `server`: un de **públic** (80/443), que ho passa tot a un d'**intern** (8080), que és el que executa PHP. Els canvis, amb el text exacte, són a `deploy/cloudpanel/nginx-vhost-snippet.conf`:

1. **Bloc públic, abans de `location / {`:** el `location /app`, que porta els websockets cap a Reverb (8090), i `client_max_body_size 1024m;`.
2. **Bloc públic, dins de `location / {`**, després de la línia de `X-Forwarded-For`: les capçaleres `X-Forwarded-Proto` i `X-Forwarded-Port`.
3. **Bloc intern, després de les línies `listen 8080`:** `client_max_body_size 1024m;`.

## 8. Processos en segon pla i cron

Com a `root`:

```bash
sed 's/SITE_USER/soportic-socialgest/g' /home/soportic-socialgest/htdocs/socialgest.soportic.es/deploy/cloudpanel/supervisor-socialgest.conf > /etc/supervisor/conf.d/socialgest.conf && mkdir -p /home/soportic-socialgest/logs && chown soportic-socialgest: /home/soportic-socialgest/logs && supervisorctl reread && supervisorctl update
```

**Cron:** a **Sites → socialgest.soportic.es → Cron Jobs**, afegeix una tasca amb `*` als cinc camps i aquesta ordre:

```
php8.4 /home/soportic-socialgest/htdocs/socialgest.soportic.es/artisan schedule:run >> /dev/null 2>&1
```

Comprovació, com a `root`:

```bash
cd /home/soportic-socialgest/htdocs/socialgest.soportic.es && supervisorctl status | grep xarxesgest; nginx -t 2>&1 | tail -1; KEY=$(grep ^REVERB_APP_KEY= .env | cut -d= -f2) && curl -s -o /dev/null -w "websocket: %{http_code}\n" --http1.1 --max-time 5 -H "Connection: Upgrade" -H "Upgrade: websocket" -H "Sec-WebSocket-Version: 13" -H "Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==" "https://socialgest.soportic.es/app/$KEY?protocol=7"; sudo -u soportic-socialgest php8.4 artisan horizon:status
```

Ha de sortir:
- els dos processos en estat `RUNNING`;
- `syntax is ok`;
- `websocket: 101`;
- `Horizon is running.`

## 9. Correu (SMTP)

Laravel 13 llegeix `MAIL_SCHEME`, no `MAIL_ENCRYPTION`: deixa'l buit per al port 587 (STARTTLS) i posa `smtps` per al 465. `MAIL_FROM_ADDRESS` ha de ser un remitent que el compte SMTP pugui fer servir.

Prova d'enviament:

```bash
php8.4 artisan tinker --execute 'Illuminate\Support\Facades\Mail::raw("Prova de correu de SocialGest.", fn ($m) => $m->to("correu@soportic.es")->subject("Prova SMTP SocialGest")); echo "enviat\n";'
```

## 10. Xarxes socials

URL de retorn (*redirect URI*) de cada xarxa:

| Xarxa | Portal | Redirect URI |
|---|---|---|
| LinkedIn | https://www.linkedin.com/developers/apps | `https://socialgest.soportic.es/accounts/linkedin/callback` |
| X | https://developer.x.com | `https://socialgest.soportic.es/accounts/x/callback` |
| Instagram | https://developers.facebook.com/apps | `https://socialgest.soportic.es/accounts/instagram/callback` |

**LinkedIn**

1. **App 1 (perfil personal):** productes *Sign In with LinkedIn using OpenID Connect* i *Share on LinkedIn*, que s'aproven a l'instant. Posa'n el Client ID i el secret a `LINKEDIN_CLIENT_ID` i `LINKEDIN_CLIENT_SECRET`.
2. **App 2 (pàgines d'empresa):** LinkedIn exigeix que la *Community Management API* sigui **l'únic producte** de la seva app. Crea'n una de nova i demana-hi només aquest producte.
3. TryPost fa servir una sola app de LinkedIn i, quan les pàgines estan activades, demana també els permisos de les pàgines. Per això la plantilla porta `LINKEDIN_PAGE_ENABLED=false` fins que l'app 2 estigui aprovada i l'app hi estigui adaptada.

**X**

1. Crea un projecte i una app, carrega crèdits i posa un límit de despesa mensual.
2. A *User authentication settings*: **Read and write**, **Web App, Automated App or Bot** i la redirect URI.
3. Copia el **Client ID** i el **Client Secret d'OAuth 2.0**, no els d'OAuth 1.0a. El secret només es mostra una vegada.
4. Decideix el valor de `X_DEFUSE_LINKS`.

**Instagram**

1. Cada compte ha de ser **professional** (Empresa o Creador).
2. Crea una app amb el cas d'ús *Manage messaging & content on Instagram* i obre **Use cases → Customize → API setup with Instagram login**. En panells antics, el camí és **Instagram → API setup with Instagram login**.
3. A *Set up Instagram business login → Business login settings*, posa la redirect URI.
4. Copia l'**Instagram app ID** i l'**Instagram app secret** d'aquesta pàgina, no els d'*App settings → Basic*.
5. Afegeix cada compte com a **Instagram Tester** (*App roles → Roles*) i accepta la invitació des de cada compte d'Instagram. Així no cal passar *App Review*.

Després de cada canvi al `.env`:

```bash
php8.4 artisan optimize >/dev/null && php8.4 artisan horizon:terminate
```

## 11. Proves

1. Entra a l'app i crea un workspace per marca.
2. Connecta els canals de cada marca.
3. Fes una publicació **ara mateix** i una de **programada a 5 minuts**. La programada valida el cron i Horizon.
4. Prova l'**assistent d'IA** de l'editor, que valida Claude.
5. Comprova que a `/horizon` no hi ha feines fallides.

**Problemes trobats a la instal·lació real:**
- **Avatars o imatges trencats:** cal `FILESYSTEM_DISK=public`. Si hi ha fitxers desats a `storage/app/private`, copia'ls a `storage/app/public`.
- **El navegador diu «no segur»:** si `curl` des de fora verifica el certificat bé, és el navegador, que recorda una excepció de quan encara no hi havia certificat. Tanca'l del tot, o prova en una finestra privada.

## 12. Actualitzacions

Cada vegada que es fusionin canvis a `main`, com a `soportic-socialgest`:

```bash
cd ~/htdocs/socialgest.soportic.es && bash deploy/cloudpanel/deploy.sh
```

Per incorporar versions noves de TryPost, fes-ho a la màquina de desenvolupament, no al servidor:

```bash
git remote add upstream https://github.com/trypostit/trypost.git
```

```bash
git fetch upstream --tags && git merge upstream/main
```

Abans, llegeix les notes de la versió de TryPost: algunes, com la 2.0, demanen passos manuals.

## 13. Còpies de seguretat

A **Admin Area → Backups**, activa les còpies remotes (S3, Dropbox, Google Drive, SFTP…). Han d'incloure la base de dades `socialgest`, el `.env` i `storage/app/public`.

Sense el `.env` no es poden desxifrar els tokens de les xarxes connectades, perquè estan xifrats amb l'`APP_KEY`.
