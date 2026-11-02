# Avaluació de TryPost com a base de XarxesGest

Repositori: https://github.com/trypostit/trypost · Revisat: 2 de novembre de 2026 (commit `ad0b83e`, release `v2.0.1`)

## Veredicte

**Sí, recomano fer servir TryPost com a base** (fork) en lloc de partir de zero. Fa servir pràcticament el mateix stack que proposàvem (Laravel + Horizon + Redis + MySQL), ja resol el 60–70 % de l'abast (connexió i publicació a LinkedIn, Instagram i X, calendari, cues, aprovacions, multi-marca, analítica i IA de text) i redueix l'esforç estimat de **~336 h a ~150 h**. Tot i així, hi falten les peces més "Metricool" (generació d'imatges, millor hora, tendències, informes) i caldrà assumir el cost de mantenir un fork d'un projecte que canvia ràpid.

## Què és

- Programador de xarxes socials *open source* i autoallotjable, "pensat per a agents d'IA".
- **Llicència AGPL-3.0**. El mode `SELF_HOSTED=true` desactiva tota la facturació (Stripe/Cashier) i ofereix **totes les funcions sense límits**.
- Stack: **Laravel 13**, PHP ≥ 8.3 (en la pràctica), Inertia + Vue, Horizon, Redis, Reverb (websockets), Passport (API i MCP), `laravel/ai` (proveïdors d'IA).
- Base de dades: **PostgreSQL o MySQL**. La CI executa els tests amb **tots dos (Postgres 16 i MySQL 8.4)** → compatible amb el MySQL de CloudPanel.
- Projecte molt actiu: últim commit de l'1 de novembre de 2026, 129 migracions i ~730 fitxers de test.

## Cobertura dels requisits de XarxesGest

| Requisit | TryPost | Comentari |
|---|---|---|
| Multi-marca (Torns, SysRevAI…) | ✅ | *Workspaces* amb canals, membres i configuració pròpia. Diversos comptes per xarxa per workspace. |
| Connexió i publicació a **Instagram** | ✅ | Connexió directa (*Instagram Login*) o via Facebook. Feed, carrusel, Reels i Stories. |
| Connexió i publicació a **LinkedIn** | ✅ | Perfil personal **i** pàgina d'empresa (publicadors separats). Cal igualment l'aprovació de la Community Management API per a pàgines. |
| Connexió i publicació a **X** | ✅ | OAuth 2.0. Opció `X_DEFUSE_LINKS` per desactivar els enllaços i evitar la tarifa de 0,20 $ per post amb URL. |
| Calendari, cues i reprogramació | ✅ | Calendari mensual, arrossegar i deixar anar, cues per canal amb franges horàries, publicacions recurrents. |
| Adaptació del contingut per xarxa | ✅ | Editor "escriu una vegada, adapta per xarxa" amb previsualització en directe, validació de tipus de contingut i mèdia per xarxa. |
| Aprovacions i rols | ✅ | Flux d'enviament a revisió i aprovació/rebuig. Notes a les publicacions. |
| **Claude per a textos** | ✅ (amb ajust) | `AI_TEXT_PROVIDER=anthropic` + `ANTHROPIC_TEXT_MODEL`. Agents: assistent d'escriptura, escurçador, generador d'idees i text alternatiu. Vegeu el risc de `temperature` més avall. |
| **Claude per a anàlisi** | 🟡 | No hi ha anàlisi automàtica, però el **servidor MCP (80+ eines)** permet connectar-hi Claude (claude.ai o Claude Code) i demanar-li "analitza els insights del mes d'Instagram de Torns i proposa 10 publicacions". |
| **ChatGPT per a imatges** | ❌ | No genera imatges. Només hi ha pujada de fitxers, Unsplash, Google Drive/Photos i Canva. **Cal desenvolupar-ho.** |
| Analítica | ✅ / 🟡 | Instagram, X i LinkedIn (pàgina) amb mètriques per compte i per post. LinkedIn personal només per post (likes i comentaris). |
| **Millor moment per publicar** | ❌ | Les franges de cua es configuren a mà. **Cal desenvolupar-ho.** |
| **Monitorització de tendències i propostes** | ❌ | El generador d'idees només fa servir la descripció del negoci i el públic, sense dades externes. **Cal desenvolupar-ho.** |
| Informes PDF | ❌ | Hi ha API d'analítica, però no informes exportables. |
| Competidors, safata d'entrada | ❌ | No inclosos (eren opcionals, fase 5). |
| Català | ❌ | 16 idiomes (inclòs castellà), però no català. Afegir-lo és traduir fitxers `lang/`. |
| Extres | ➕ | API REST, webhooks, repurposing, feeds RSS, plantilles, etiquetes, signatures, 13 xarxes en total (TikTok, YouTube, Facebook, Threads…). |

## Riscos i punts d'atenció

1. **`temperature` als agents d'IA:** els quatre agents de `app/Ai/Agents/` declaren `#[Temperature(...)]`. Els models Claude actuals (Opus 5.5, Sonnet 5.5, Sonnet 5) **rebutgen els valors de `temperature` amb un error 400**, i la versió d'API `2026-10-21` els rebutja en tots els models. Cal comprovar si `laravel/ai` envia aquest paràmetre a Anthropic. Si l'envia, la correcció és treure l'atribut (quatre línies), però és el primer que s'ha de provar.
2. **Ritme de canvis i fork:** el projecte fa canvis disruptius (p. ex. la v2.0 ha fusionat taules i canviat el contracte de l'API i de l'MCP, amb un deploy en manteniment). Per poder incorporar actualitzacions cal mantenir les personalitzacions **aïllades** (mòduls i classes noves, mínimes modificacions al codi original) i fer *rebase* periòdic.
3. **Dependència d'un sol mantenidor** (Paulo Castellano). Mitigació: com que tenim el codi, podem continuar pel nostre compte si s'abandona.
4. **AGPL-3.0:** l'ús intern per a les marques pròpies és lliure. Si es modifica i l'utilitzen persones externes per xarxa, cal oferir-los el codi font modificat (§13). Per a ús propi no té cap impacte pràctic, però **no s'hauria d'incorporar codi propietari que no es vulgui publicar mai**.
5. **Reverb (websockets):** és un procés més (port 8080) que cal mantenir amb Supervisor i exposar a Nginx. Opcionalment es pot desactivar (`BROADCAST_CONNECTION=log`) i perdre només les actualitzacions en temps real.
6. **Telemetria:** inclou PostHog i Laravel Nightwatch; es desactiven deixant buides les claus corresponents.
7. Part de la documentació interna està en portuguès.

## Desplegament a CloudPanel (instal·lació nativa, sense Docker)

El `compose.prod.yaml` del projecte fa servir Caddy als ports 80/443, que xoca amb l'Nginx de CloudPanel. Recomano la instal·lació nativa:

1. Lloc **PHP 8.4** amb plantilla Laravel per a `xarxesgest.soportic.es` + Let's Encrypt.
2. Base de dades **MySQL 8.4** (la versió provada a la CI; evitar MariaDB, que no està provada).
3. Redis + Supervisor amb dos programes: `php artisan horizon` i `php artisan reverb:start`.
4. Bloc `location /app` a la configuració de vhost de CloudPanel com a proxy de websockets cap a `127.0.0.1:8080`.
5. Cron de CloudPanel: `* * * * * php artisan schedule:run`.
6. Compilar el frontend (`npm ci && npm run build`) a GitHub Actions i desplegar per SSH (no cal Node.js en producció).
7. `.env`: `SELF_HOSTED=true`, `DB_CONNECTION=mysql`, `AI_TEXT_PROVIDER=anthropic`, `ANTHROPIC_TEXT_MODEL=claude-opus-5-5`, claus de LinkedIn/Instagram/X i URL de retorn `https://xarxesgest.soportic.es/accounts/{xarxa}/callback`.

## Pla revisat sobre TryPost

| Bloc | Hores |
|---|---|
| Fork, instal·lació a CloudPanel, CI/CD, configuració de xarxes i proves de publicació reals | 20 h |
| Ajust de Claude 5.x (`temperature`, model, guia d'estil per marca als prompts) | 8 h |
| Traducció al català | 10 h |
| **= MVP en ús real** (publicació a les 3 xarxes + textos amb Claude) | **≈ 38 h** |
| Generació d'imatges amb OpenAI `gpt-image` dins l'editor i la biblioteca de mèdia (retall automàtic per xarxa) | 30 h |
| Millor moment per publicar (mapa de calor a partir de les mètriques, suggeriment de franges a les cues) | 24 h |
| Tendències i propostes setmanals (hashtags IG, cerca a X, *web search* de Claude → idees al tauler d'idees) | 36 h |
| Informes mensuals PDF amb resum de Claude | 20 h |
| **= Versió completa** | **≈ 148 h** (vs. ≈ 336 h partint de zero) |

Els costos operatius (APIs de X, Claude i OpenAI) són els mateixos de la proposta original (secció 7 de `PROPOSTA.md`). Les connexions a les xarxes tenen exactament els mateixos requisits: TryPost no evita l'aprovació de LinkedIn per a les pàgines ni el pagament per ús de X.

## Propers passos proposats

1. Fer un fork privat de TryPost (p. ex. `soportic-orb/xarxesgest` o un repositori nou) i instal·lar-lo a `xarxesgest.soportic.es`.
2. Enviar la sol·licitud de la Community Management API a LinkedIn amb l'app ja en línia.
3. Provar la publicació real a cada xarxa i la integració amb Claude (risc 1).
4. Desenvolupar els mòduls que falten (imatges → millor hora → tendències → informes) com a codi aïllat dins del fork.
