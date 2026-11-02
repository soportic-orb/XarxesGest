# Desplegament de XarxesGest a CloudPanel

Guia pas a pas per instal·lar XarxesGest (fork de TryPost) a `https://xarxesgest.soportic.es` en un VPS amb CloudPanel, sense Docker. Els fitxers de suport són a `deploy/cloudpanel/`.

**Requisits del servidor:** 2 vCPU, 4 GB de RAM, 40 GB de disc lliure com a mínim. Ubuntu 24.04 o Debian 12 amb CloudPanel 2.x. Cal accés `root` per SSH per als passos 3 i 6.

---

## 1. DNS

Crea un registre **A** `xarxesgest.soportic.es` que apunti a la IP del VPS (i AAAA si fas servir IPv6).

## 2. Lloc i base de dades (panell de CloudPanel)

1. **Sites → Add Site → Create a PHP Site**
   - Application: **Laravel 12** (serveix també per a Laravel 13)
   - Domain: `xarxesgest.soportic.es`
   - PHP: **8.4**
   - Apunta't el nom d'usuari del lloc (a la guia en diem `SITE_USER`).
2. **Sites → xarxesgest.soportic.es → SSL/TLS → New Let's Encrypt Certificate.**
3. **Databases → Add Database**: base de dades `xarxesgest`, usuari `xarxesgest` i una contrasenya forta. Cal **MySQL 8.x** (la que instal·la CloudPanel per defecte); no s'ha provat amb MariaDB.
4. **Sites → xarxesgest.soportic.es → PHP Settings**:
   - `memory_limit` = 512M
   - `upload_max_filesize` i `post_max_size` = 1024M (vídeos de Reels)
   - `max_execution_time` = 300

## 3. Paquets del servidor (SSH com a root)

```bash
apt-get update && apt-get install -y redis-server supervisor ffmpeg git unzip
```

```bash
systemctl enable --now redis-server supervisor
```

L'app necessita aquestes extensions PHP: `bcmath`, `intl`, `gd`, **`imagick`** (sense ella el calendari dona error 500), `redis`, `pdo_mysql`, `zip` i `mbstring`. El PHP 8.4 de CloudPanel les sol portar totes; comprova-ho amb:

```bash
php8.4 -m | grep -E 'bcmath|intl|^gd|imagick|redis|pdo_mysql|zip|mbstring'
```

Han de sortir les vuit. Si en falta alguna, instal·la el paquet corresponent (p. ex. `apt-get install -y php8.4-imagick`) i reinicia PHP-FPM (`systemctl restart php8.4-fpm`).

## 4. Codi i dependències (SSH com a `SITE_USER`)

Entra com a usuari del lloc (`su - SITE_USER` o SSH directe) i instal·la Node 22 amb nvm (només serveix per compilar el frontend):

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash && source ~/.nvm/nvm.sh && nvm install 22
```

Clona el repositori al directori del lloc. CloudPanel hi crea un `index.php` de mostra; l'esborrem primer:

```bash
cd ~/htdocs && rm -rf xarxesgest.soportic.es && git clone https://github.com/soportic-orb/xarxesgest.git xarxesgest.soportic.es && cd xarxesgest.soportic.es
```

> El repositori és privat: fes servir una *deploy key* (Settings → Deploy keys a GitHub) o un token d'accés amb permís de lectura.

Configura l'entorn:

```bash
cp deploy/cloudpanel/env.production.example .env && nano .env
```

Omple com a mínim: `DB_PASSWORD`, `MAIL_*`, `REVERB_APP_ID`/`KEY`/`SECRET` (valors aleatoris, p. ex. `openssl rand -hex 16`), `ANTHROPIC_API_KEY` i les claus de les xarxes (pas 7). Després instal·la les dependències PHP i genera les claus:

```bash
php8.4 /usr/local/bin/composer install --no-dev --no-interaction --optimize-autoloader
```

```bash
php8.4 artisan key:generate && php8.4 artisan passport:keys --show
```

Copia les dues claus que mostra l'última comanda a `PASSPORT_PRIVATE_KEY` i `PASSPORT_PUBLIC_KEY` del `.env` (en una sola línia, amb `\n` literals) i esborra els fitxers `storage/oauth-*.key`.

Primer desplegament (instal·la dependències, compila, migra la base de dades i escalfa les memòries cau):

```bash
DEPLOY_BRANCH=main bash deploy/cloudpanel/deploy.sh
```

Crea el teu usuari propietari (el registre públic està desactivat; la resta d'usuaris s'hi afegeixen per invitació):

```bash
php8.4 artisan xarxesgest:create-admin --name="Octavi Rodríguez" --email=octavirodriguezblanco@gmail.com
```

> No facis servir `db:seed --class=UserSeeder`: crea un usuari `admin@trypost.it` amb la contrasenya `password`.

## 5. Nginx: websockets i mida de pujada

A **Sites → xarxesgest.soportic.es → Vhost**, enganxa el contingut de `deploy/cloudpanel/nginx-vhost-snippet.conf` dins del bloc `server { listen 443 … }`, abans de `location / {`, i desa.

## 6. Processos en segon pla (SSH com a root)

```bash
sed "s/SITE_USER/<el-teu-site-user>/g" /home/<el-teu-site-user>/htdocs/xarxesgest.soportic.es/deploy/cloudpanel/supervisor-xarxesgest.conf > /etc/supervisor/conf.d/xarxesgest.conf
```

```bash
supervisorctl reread && supervisorctl update && supervisorctl status
```

Han d'aparèixer `xarxesgest-horizon` i `xarxesgest-reverb` en estat `RUNNING`.

**Cron:** a **Sites → xarxesgest.soportic.es → Cron Jobs**, afegeix:

- Frequency: `* * * * *`
- Command: `php8.4 /home/SITE_USER/htdocs/xarxesgest.soportic.es/artisan schedule:run >> /dev/null 2>&1`

## 7. Connexió amb les xarxes

URL de retorn (*redirect URI*) que cal registrar a cada portal:

| Xarxa | Portal | Redirect URI |
|---|---|---|
| LinkedIn (perfil i pàgines) | https://www.linkedin.com/developers/apps | `https://xarxesgest.soportic.es/accounts/linkedin/callback` |
| X | https://developer.x.com | `https://xarxesgest.soportic.es/accounts/x/callback` |
| Instagram (Instagram Login) | https://developers.facebook.com/apps | `https://xarxesgest.soportic.es/accounts/instagram/callback` |

**LinkedIn**
1. Crea l'app associada a la pàgina d'empresa de Soportic (cal verificar-la des de la pàgina).
2. Productes: *Sign In with LinkedIn using OpenID Connect* i *Share on LinkedIn* (immediats) → perfil personal.
3. Sol·licita **Community Management API** per a les pàgines de Torns, SysRevAI, etc. Necessitaràs: política de privacitat publicada, un vídeo de l'app en funcionament i la descripció de l'ús (gestió de les pàgines pròpies). Fes-ho tan aviat com l'app estigui en línia.
4. Copia el *Client ID* i el *Client Secret* a `LINKEDIN_CLIENT_ID` i `LINKEDIN_CLIENT_SECRET`.

**X**
1. Crea un projecte i una app al portal de desenvolupadors i carrega crèdits de pagament per ús.
2. *User authentication settings*: OAuth 2.0, tipus **Web App**, permisos **Read and write**, *Callback URI* i *Website URL* com a la taula.
3. Copia el *Client ID* i el *Client Secret* d'OAuth 2.0 a `X_CLIENT_ID` i `X_CLIENT_SECRET`.
4. Decideix `X_DEFUSE_LINKS` (vegeu el `.env`) i posa un límit de despesa al portal de X.

**Instagram**
1. Comprova que cada compte d'Instagram és **professional** (Empresa o Creador).
2. Crea una app de tipus *Business* i afegeix-hi el producte **Instagram → API setup with Instagram login**.
3. Afegeix cada compte d'Instagram de les marques com a **Instagram Tester** (*App roles → Roles*) i accepta la invitació des de l'app d'Instagram de cada compte (*Configuració → Apps i llocs web → Invitacions de tester*). Així no cal passar *App Review*.
4. Copia l'*Instagram App ID* i l'*Instagram App Secret* a `INSTAGRAM_CLIENT_ID` i `INSTAGRAM_CLIENT_SECRET`.

Després de canviar el `.env`:

```bash
php8.4 artisan optimize && php8.4 artisan horizon:terminate
```

## 8. Primeres proves

1. Entra a `https://xarxesgest.soportic.es` amb l'usuari del pas 4 i tria **Català** a les preferències si no surt per defecte.
2. Crea un *workspace* per marca (Torns, SysRevAI…).
3. A cada workspace, connecta els canals: LinkedIn, Instagram i X.
4. Publica ara mateix una publicació de prova a cada xarxa i comprova-la a la xarxa.
5. Programa'n una per d'aquí a 5 minuts i comprova que surt sola (això valida el cron i Horizon).
6. A l'editor, fes servir l'**assistent d'IA** (reescriure, escurçar, generar) per validar la connexió amb Claude.
7. A **Horizon** (`https://xarxesgest.soportic.es/horizon`) no hi ha d'haver feines fallides.

## 9. Actualitzacions

Cada vegada que es fusionin canvis a `main`:

```bash
cd ~/htdocs/xarxesgest.soportic.es && bash deploy/cloudpanel/deploy.sh
```

Incorporar versions noves de TryPost (a la màquina de desenvolupament, no al servidor):

```bash
git remote add upstream https://github.com/trypostit/trypost.git
```

```bash
git fetch upstream --tags && git merge upstream/main
```

Llegeix abans les notes de la versió de TryPost: algunes (com la 2.0) demanen passos manuals de migració.

## 10. Còpies de seguretat

A **Sites → xarxesgest.soportic.es → Backups** (fitxers) i **Databases → Backups** (MySQL), activa còpies diàries remotes (S3, Dropbox, Google Drive, SFTP…). Inclou sobretot `.env` i `storage/app` (la mèdia de les publicacions).
