# XarxesGest — Proposta de desenvolupament

**Plataforma autoallotjada de gestió de xarxes socials per a les marques de Soportic** (Torns, SysRevAI, …)
URL de destinació: `https://xarxesgest.soportic.es` · Servidor: VPS gestionat amb CloudPanel
Data: novembre 2026 · Versió del document: 1.0

---

## 1. Resum executiu

XarxesGest és una plataforma pròpia, inspirada en Metricool, per a:

1. **Planificar i publicar automàticament** contingut a **LinkedIn, Instagram i X** des d'un calendari únic, amb diverses marques i diversos comptes per marca.
2. **Adaptar automàticament cada peça** al format de cada xarxa (longitud del text, to, hashtags, mida i proporció de la imatge, vídeo/carrusel).
3. **Generar textos amb Claude** (Anthropic) i **imatges amb ChatGPT** (OpenAI, models `gpt-image`).
4. **Analitzar el rendiment** (mètriques, informes, millors hores per publicar) i **monitoritzar tendències** per proposar continguts.

**Viabilitat:** és **viable** amb les tres xarxes. Instagram i LinkedIn (perfil personal) es poden connectar sense cost i sense revisió prèvia, perquè la plataforma només gestiona comptes propis. **Publicar a pàgines d'empresa de LinkedIn** requereix que LinkedIn aprovi l'accés a la *Community Management API* (gratuïta però discrecional). **X** és l'única xarxa amb cost directe d'API: des del 2026 funciona amb pagament per ús.

**Cost operatiu estimat:** unes **60–140 $/mes** per a 4 marques (~200 publicacions/mes), sense comptar el VPS. Vegeu la secció 7.

**Esforç de desenvolupament estimat:** **~200 h per al MVP** (publicació + IA) i **~340 h per a la versió completa** (amb analítica i tendències). Vegeu la secció 8.

---

## 2. Abast funcional (paritat amb Metricool)

| Mòdul Metricool | Funcionalitat a XarxesGest | Fase | Viabilitat amb les API |
|---|---|---|---|
| **Planificador** | Calendari mensual/setmanal amb arrossegar i deixar anar, esborranys, cua, publicació automàtica, republicació i duplicat | F1 | ✅ Total |
| **Multi-marca** | Espais de treball per marca (logo, colors, to de veu, hashtags, comptes connectats) | F1 | ✅ Total |
| **Adaptació per xarxa** | Una publicació "mare" que genera variants per xarxa: text, hashtags, mencions, imatge reescalada o regenerada | F2 | ✅ Total |
| **Generació IA de text** | Claude: redacció, variants A/B, reescriptura amb el to de la marca, fils per a X, carrusels | F2 | ✅ Total |
| **Generació IA d'imatge** | ChatGPT `gpt-image`: imatges per prompt amb l'estil de la marca, edició i variacions | F2 | ✅ Total |
| **Biblioteca de mèdia** | Imatges i vídeos per marca, etiquetes, reutilització | F2 | ✅ Total |
| **Analítica** | Seguidors, abast, impressions, interaccions per publicació i per compte, evolució temporal | F3 | ✅ IG i LinkedIn (pàgines) · ⚠️ X té cost de lectura · ⚠️ perfil personal de LinkedIn amb mètriques limitades |
| **Millor moment per publicar** | Mapa de calor dia × hora calculat amb les mètriques pròpies i l'activitat dels seguidors (IG) | F3 | ✅ IG (`online_followers`) · ✅ calculat per a la resta amb l'històric propi |
| **Informes** | Informes PDF mensuals per marca, amb un resum escrit per Claude | F3 | ✅ |
| **Competidors** | Seguiment de comptes de la competència | F5 | ✅ IG (*Business Discovery*) · ⚠️ X (cost de lectura) · ❌ LinkedIn (no hi ha API) |
| **Hashtags i tendències** | Monitorització de hashtags, paraules clau i notícies del sector, amb propostes de publicació | F4 | ⚠️ Parcial (vegeu la secció 5) |
| **Safata d'entrada** | Comentaris i mencions unificats, amb respostes suggerides per IA | F5 | ✅ IG · ✅ LinkedIn (pàgines) · ⚠️ X (cost de lectura) |
| **Enllaç a la bio (SmartLinks)** | Pàgina d'enllaços per a Instagram, servida des del mateix domini | F5 | ✅ (no depèn de cap API) |
| **Fluxos d'aprovació** | Esborrany → revisió → aprovat → programat; rols per usuari | F5 | ✅ |
| **Anuncis (Ads)** | Gestió de campanyes de pagament | Fora d'abast | — |

---

## 3. Arquitectura tècnica

### 3.1 Stack recomanat

L'objectiu és un stack que CloudPanel gestioni de forma nativa, amb poques peces mòbils i fàcil de mantenir per una sola persona.

| Capa | Tecnologia | Motiu |
|---|---|---|
| Backend | **Laravel 12** (PHP 8.4) | CloudPanel té plantilla de vhost per a Laravel; cues, scheduler, xifratge i OAuth integrats |
| Panell / UI | **Filament 4** (Livewire) + **FullCalendar** | CRUD i panell d'administració molt ràpids de construir; calendari amb arrossegar i deixar anar |
| Base de dades | **MySQL 8 / MariaDB** (gestionada per CloudPanel) | Còpies de seguretat des de CloudPanel |
| Cues i memòria cau | **Redis** + **Laravel Horizon** | Publicacions programades, reintents, ingesta de mètriques i crides IA asíncrones |
| Tasques programades | `php artisan schedule:run` cada minut (cron de CloudPanel) | Dispara publicacions, refresc de tokens i recollida de mètriques |
| Processos | **Supervisor** per a Horizon | Workers sempre actius |
| Imatges | **Intervention Image** (GD/Imagick) | Reescalat i retall a la mida exacta de cada xarxa, conversió a JPEG per a IG |
| Vídeo (opcional) | **FFmpeg** | Validació i transcodificació de Reels i vídeos |
| IA text | **SDK oficial d'Anthropic per a PHP** | Claude per a textos, adaptació, anàlisi i informes |
| IA imatge | Client PHP d'OpenAI | `gpt-image` per generar i editar imatges |
| OAuth | **Laravel Socialite** + proveïdors propis | LinkedIn (OIDC), Meta/Instagram, X (OAuth 2.0 PKCE) |
| SSL i domini | Let's Encrypt via CloudPanel | `xarxesgest.soportic.es` |

> Alternativa considerada: Node.js (Next.js + BullMQ). També és compatible amb CloudPanel (lloc Node.js amb proxy invers), però exigeix gestionar més processos (PM2, build) i no té un equivalent a Filament per al panell d'administració. Laravel redueix l'esforç estimat al voltant d'un 20–25 %.

### 3.2 Diagrama de components

```
                    ┌────────────────────────── VPS (CloudPanel) ──────────────────────────┐
 Navegador ──HTTPS──▶ Nginx ─▶ PHP-FPM (Laravel + Filament)                               │
                    │              │            │                                          │
                    │              ▼            ▼                                          │
                    │          MySQL        Redis ◀── Horizon workers (Supervisor)         │
                    │                                   │                                  │
                    │   cron: schedule:run (1 min) ─────┤                                  │
                    │                                   ├─▶ PublishJob  ──▶ API LinkedIn / Instagram / X
                    │                                   ├─▶ MetricsJob  ──▶ API d'insights
                    │                                   ├─▶ TrendsJob   ──▶ Claude (web search) + API xarxes
                    │                                   ├─▶ AiTextJob   ──▶ Claude API
                    │                                   └─▶ AiImageJob  ──▶ OpenAI gpt-image
                    │   /storage/media (URL signades públiques, necessàries per a Instagram)          │
                    └───────────────────────────────────────────────────────────────────────┘
```

### 3.3 Model de dades (principal)

- `brands` — nom, logo, colors, **guia d'estil i to de veu** (prompt base per a Claude), hashtags habituals, zona horària.
- `social_accounts` — `brand_id`, xarxa, tipus (perfil o pàgina), identificador extern, **tokens xifrats** (`encrypted` cast), data de caducitat, scopes i estat.
- `posts` — publicació "mare": brief, text base, estat (esborrany / en revisió / aprovada / programada / publicada / error), autor.
- `post_variants` — una per xarxa i compte: text adaptat, hashtags, mèdia, `scheduled_at`, `published_at`, ID extern, error i reintents.
- `media` — fitxers, origen (pujat / IA), prompt, dimensions, variants reescalades.
- `metrics_snapshots` — mètriques per variant i per compte amb marca de temps (sèries temporals).
- `best_times` — mapa de calor calculat per compte (7 × 24).
- `trends` / `suggestions` — tendències detectades i propostes de publicació generades.
- `ai_usage` — registre de tokens i cost per crida (control de despesa per marca).

### 3.4 Motor d'adaptació per xarxa

Cada publicació "mare" passa per un adaptador per xarxa que aplica regles fixes i, opcionalment, una reescriptura amb Claude:

| | **X** | **LinkedIn** | **Instagram** |
|---|---|---|---|
| Longitud del text | 280 caràcters (25.000 amb Premium); opció de **fil** automàtic | 3.000 caràcters; ganxo a les 2–3 primeres línies | 2.200 caràcters al peu |
| To per defecte | Directe i breu | Professional, aporta valor | Visual, proper, amb CTA |
| Hashtags | 1–2 | 3–5 | Límit configurable (Instagram n'ha anat reduint el màxim recomanat i permès) |
| Enllaços | Penalitzats en cost d'API (vegeu la secció 4.3) | Permesos (vista prèvia de l'enllaç) | No clicables al peu → enllaç a la bio |
| Imatge recomanada | 1600×900 (16:9) o 1:1 | 1200×1200, 1080×1350 o 1200×627 | 1080×1350 (4:5), 1080×1080, 1080×1920 (Stories i Reels) |
| Format | JPG/PNG/GIF, fins a 4 imatges | Imatge, multiimatge, document PDF (carrusel), vídeo | **Només JPEG**, carrusel de fins a 10 elements, Reels, Stories |

Les imatges es generen amb `gpt-image` a la proporció més propera i després es **retallen o reescalen automàticament** a cada mida de destinació, amb l'opció de reencuadrar manualment a l'editor.

---

## 4. Connexió amb les xarxes socials: viabilitat i cost

### 4.1 Instagram (Meta Graph API — *Instagram API with Instagram Login*)

| Aspecte | Detall |
|---|---|
| **Requisits** | Compte d'Instagram **professional** (Empresa o Creador). Des del 2024 ja no cal vincular-lo a una pàgina de Facebook. Cal una app a Meta for Developers. |
| **Revisió de Meta** | **No cal** per a comptes propis: amb accés *Standard* l'app funciona amb els comptes dels usuaris que hi tenen un rol (administrador, desenvolupador o tester). Com que tots els comptes són de Soportic, n'hi ha prou. Només caldria *App Review* i verificació d'empresa si es volgués oferir a tercers. |
| **Permisos** | `instagram_business_basic`, `instagram_business_content_publish`, `instagram_business_manage_comments`, `instagram_business_manage_insights` |
| **Publicació** | Imatge, carrusel, Reels i Stories. Procés en dos passos (crear contenidor → `media_publish`). **La mèdia ha d'estar en una URL pública**: es serveix des de `xarxesgest.soportic.es` amb URL signades temporals. |
| **Límits** | **100 publicacions per API cada 24 h** per compte (el carrusel compta com una). Es pot consultar amb `/content_publishing_limit`. Els contenidors caduquen en 24 h. |
| **Analítica** | Insights per publicació i per compte, incloent-hi les hores en què els seguidors estan connectats (base del "millor moment"). |
| **Tendències** | Cerca de hashtags: **màxim 30 hashtags únics cada 7 dies** per compte. *Business Discovery* per a mètriques públiques de competidors. |
| **Tokens** | Token de llarga durada de 60 dies, renovable automàticament (tasca programada). |
| **Cost** | **0 €** |

### 4.2 LinkedIn

LinkedIn distingeix dos casos molt diferents:

| | **Perfil personal** | **Pàgina d'empresa** (Torns, SysRevAI…) |
|---|---|---|
| Producte | *Share on LinkedIn* + *Sign In with LinkedIn (OIDC)* | **Community Management API** |
| Scope | `w_member_social` | `w_organization_social`, `r_organization_social`, `rw_organization_admin` |
| Alta | Autoservei, immediat | **Sol·licitud i revisió per part de LinkedIn**: primer *Development tier* i després *Standard tier*. Demana una entitat legal (Soportic), política de privacitat publicada, vídeo de demostració de l'app i descripció de l'ús. L'aprovació és discrecional i pot trigar setmanes. Si es rebutja l'accés *Standard*, no es pot tornar a demanar amb la mateixa app. |
| Requisit addicional | — | L'usuari que connecta ha de tenir rol d'**administrador** o de **content admin** a la pàgina. |
| Publicació | Text, imatge, multiimatge, vídeo, document (carrusel PDF), article | El mateix, en nom de la pàgina |
| Analítica | Molt limitada | Estadístiques de pàgina, seguidors i publicacions |
| Tokens | Access token de 60 dies; cal reautenticar | Amb accés aprovat hi ha refresh token (365 dies) |
| **Cost** | **0 €** | **0 €** (el cost és el temps del procés d'aprovació) |

**Recomanació:** sol·licitar la Community Management API **des del primer dia**, amb l'app ja desplegada a `xarxesgest.soportic.es`, perquè la revisió no bloquegi el calendari. Mentrestant, publicar al perfil personal. **Pla B** si LinkedIn no ho aprova: fer servir un agregador amb accés ja aprovat (Ayrshare, Upload-Post, Late…) només per a LinkedIn, amb un cost de l'ordre de desenes de dòlars al mes segons el pla. Cal verificar-ne les condicions.

### 4.3 X (Twitter) — API v2

| Aspecte | Detall |
|---|---|
| **Model de preus** | Des del febrer del 2026 el model per defecte és el **pagament per ús** (crèdits prepagats); els plans fixos Basic i Pro s'han anat retirant al llarg del 2026. |
| **Cost d'escriptura (fonts de tercers, nov. 2026)** | Uns **0,015 $ per publicació de text o amb imatge** i uns **0,20 $ per publicació que conté una URL** (canvi de l'abril del 2026). |
| **Cost de lectura** | Uns **0,005 $ per post llegit** (mètriques, mencions, cerques), amb un sostre mensual. |
| **Autenticació** | OAuth 2.0 amb PKCE (`tweet.read`, `tweet.write`, `users.read`, `media.write`, `offline.access`). Una sola app de desenvolupador per a tots els comptes de les marques. |
| **Publicació** | Text, fins a 4 imatges, vídeo i fils. |
| **Analítica** | Mètriques públiques per post (impressions, likes, reposts, respostes) mitjançant lectura de pagament. |
| **Tendències** | Cerca recent per paraules clau (pagament per lectura). L'endpoint de tendències per ubicació no és una opció econòmica. |

**Implicacions de disseny per contenir el cost:**
- Evitar URL al text de X sempre que es pugui (fer servir "enllaç a la bio" o una imatge amb CTA). La plataforma **avisa del sobrecost** abans de programar.
- Llegir mètriques de cada post a les 24 h, 72 h i 7 dies, en comptes de fer-ho de forma contínua.
- **Límit de despesa mensual configurable** per a X dins de XarxesGest (aturada suau quan s'arriba al llindar).

> ⚠️ Els preus de X han canviat diverses vegades el 2026 i les xifres anteriors provenen de fonts de tercers. Cal confirmar-les a `docs.x.com` / developer console abans de pressupostar.

### 4.4 Resum de connexió

| Xarxa | Viabilitat | Aprovació prèvia | Cost API/mes (4 marques, ~30 posts/mes per xarxa i marca) |
|---|---|---|---|
| Instagram | ✅ Alta | No (comptes propis) | 0 € |
| LinkedIn (perfil) | ✅ Alta | No | 0 € |
| LinkedIn (pàgines) | ⚠️ Depèn de LinkedIn | **Sí** (Community Management API) | 0 € (o agregador com a pla B) |
| X | ✅ Alta | No | **~15–40 $** (vegeu el detall a la secció 7) |

---

## 5. Monitorització de tendències i propostes

Les API oficials ofereixen poques dades de tendències globals (és una limitació que també té Metricool). La proposta combina tres fonts:

1. **Dades pròpies (cost 0):** quins temes, formats, hores i hashtags han funcionat millor a cada marca. Claude analitza l'històric setmanalment i en treu conclusions accionables.
2. **Senyals de les xarxes:**
   - Instagram: cerca de fins a 30 hashtags/setmana per compte (top i recents) i *Business Discovery* de competidors.
   - X: cerques recents de paraules clau del sector amb un pressupost de lectures limitat.
3. **Web i notícies del sector:** Claude amb l'eina de **web search** (cerca notícies, publicacions de referents i temes emergents per a cada sector, com ara RRHH/torns o IA aplicada a la revisió de sistemes).

**Sortida:** cada dilluns, una **proposta setmanal per marca** amb 5–10 idees de publicació (tema, angle, xarxa, format, hora recomanada i esborrany del text), que es poden enviar al calendari amb un clic.

**Millor moment per publicar:** es calcula per compte combinant (a) les hores amb més seguidors connectats (IG), (b) la taxa d'interacció per franja de les publicacions pròpies i (c) valors de referència del sector mentre no hi hagi prou històric. El planificador suggereix automàticament la franja òptima lliure.

---

## 6. Integració amb IA

### 6.1 Claude (Anthropic) — textos i anàlisi

| Ús | Detall |
|---|---|
| Generació de publicacions | A partir d'un brief i de la guia d'estil de la marca, genera la publicació mare i les variants per xarxa en una sola crida, amb **sortides estructurades** (JSON validat). |
| Reescriptura i to | "Més curt", "més tècnic", "més proper", traducció CA/ES/EN. |
| Anàlisi i informes | Resum setmanal i mensual de mètriques, detecció de patrons i recomanacions. |
| Tendències | Web search i síntesi de propostes (secció 5). |
| Respostes | Suggeriments de resposta a comentaris (fase 5). |

- Model per defecte: **Claude Opus 5.5** (`claude-opus-5-5`, 4 $ entrada / 20 $ sortida per milió de tokens). Es pot canviar per marca o per tipus de tasca a la configuració, per exemple **Claude Sonnet 5.5** (2 $ / 10 $) per a reescriptures ràpides si es vol reduir cost.
- **Prompt caching** de la guia d'estil de cada marca (la lectura de memòria cau costa una fracció del preu d'entrada) i **Batch API** (−50 %) per a informes i propostes setmanals que no són urgents.
- Registre de tokens i cost per crida a `ai_usage`, amb límit mensual per marca.

### 6.2 ChatGPT (OpenAI) — imatges

| Ús | Detall |
|---|---|
| Generació | Prompt construït per Claude a partir del text de la publicació + estil visual de la marca (paleta, tipus d'il·lustració, elements prohibits). |
| Edició | Variacions, canvi de fons, inserció del logo (o superposició del logo amb Intervention Image, més fiable). |
| Formats | Generació a la proporció més propera (quadrat, vertical o horitzontal) i retall automàtic per xarxa. |
| Model | Família **gpt-image** vigent (`gpt-image-2` a nov. 2026; `gpt-image-1` i `1.5` estan en retirada). El model és configurable. |
| Cost orientatiu | ~0,03–0,08 $ per imatge segons mida i qualitat (fonts de tercers; confirmar a la pàgina de preus d'OpenAI). |

---

## 7. Costos operatius mensuals estimats

**Escenari:** 4 marques, ~200 publicacions/mes en total (~50 per xarxa), ~120 imatges generades (comptant-hi regeneracions), informe setmanal i propostes setmanals per marca.

| Concepte | Càlcul aproximat | Cost/mes |
|---|---|---|
| VPS (2 vCPU, 4 GB RAM, 80 GB SSD) | Si ja es disposa del servidor de CloudPanel, 0 € addicionals | 0–25 € |
| Claude — generació de textos | ~600 crides (amb iteracions) × ~0,05 $ (Opus 5.5) | ~30 $ |
| Claude — informes i anàlisi | 16 informes/mes amb Batch API | ~5 $ |
| Claude — tendències (web search + tokens) | ~600 cerques/mes (10 $/1.000) + tokens | ~10–20 $ |
| OpenAI — imatges | 120–240 imatges × 0,03–0,08 $ | ~5–20 $ |
| X — publicació | ~120 posts/mes: meitat sense URL (0,015 $), meitat amb URL (0,20 $) | ~13 $ (≈ 2 $ si s'eviten les URL) |
| X — lectura (mètriques, mencions, cerca) | ~3.000–5.000 lectures × 0,005 $ | ~15–25 $ |
| Instagram | — | 0 € |
| LinkedIn | — (o agregador com a pla B) | 0 € (+ pla B opcional) |
| **Total** | | **≈ 60–140 $/mes** |

Amb Sonnet 5.5 en comptes d'Opus 5.5 per a les tasques de text, el cost de Claude es redueix aproximadament a la meitat. A tall de comparació, Metricool cobra per marca a partir dels plans de pagament i limita marques i funcions segons el pla, mentre que XarxesGest no té límit de marques.

---

## 8. Pla de desenvolupament

### 8.1 Fases i estimació

| Fase | Contingut | Hores |
|---|---|---|
| **F0 — Infraestructura** | Lloc Laravel a CloudPanel, SSL, base de dades, Redis, Supervisor/Horizon, cron, còpies de seguretat, CI/CD des de GitHub (deploy per SSH), autenticació amb 2FA | 16 h |
| **F1 — Nucli de publicació** | Marques, usuaris i rols; OAuth i gestió de tokens per a les 3 xarxes; editor de publicacions; calendari; cua de publicació amb reintents i notificacions d'error; previsualització per xarxa | 110 h |
| **F2 — IA i mèdia** | Integració Claude (generació, adaptació, reescriptura); integració gpt-image; biblioteca de mèdia; retall automàtic; guia d'estil per marca; control de cost IA | 70 h |
| **= MVP** | **Publicació multi-xarxa programada amb IA** | **≈ 196 h** |
| **F3 — Analítica** | Ingesta de mètriques; dashboards per marca i xarxa; mapa de calor de millor hora; informes PDF amb resum de Claude | 80 h |
| **F4 — Tendències i propostes** | Monitorització de hashtags i paraules clau; web search; propostes setmanals; suggeriment automàtic d'hora | 60 h |
| **= Versió completa** | | **≈ 336 h** |
| **F5 — Extres (opcional)** | Safata d'entrada unificada, competidors, enllaç a la bio, fluxos d'aprovació, publicació de vídeo/Reels avançada | 80 h |

Les estimacions inclouen proves, documentació i desplegament. Amb desenvolupament assistit per Claude Code es poden reduir de forma significativa, però es mantenen conservadores per a la planificació.

### 8.2 Calendari orientatiu

| Setmana | Fita |
|---|---|
| 1 | F0 completada. App en producció a `xarxesgest.soportic.es`. **Sol·licituds enviades a LinkedIn (Community Management API)**, app de Meta i app de X creades. |
| 2–5 | F1: publicació real a IG, LinkedIn (perfil) i X des del calendari. |
| 6–7 | F2: IA de text i imatge integrada. **MVP en ús real.** |
| 8–10 | F3: analítica i millor moment. |
| 11–12 | F4: tendències i propostes setmanals. |
| 13+ | F5 segons prioritats. Pàgines de LinkedIn quan arribi l'aprovació. |

### 8.3 Desplegament a CloudPanel

1. Crear un **lloc PHP** amb la plantilla **Laravel** per a `xarxesgest.soportic.es` (PHP 8.4) i un registre DNS A cap al VPS.
2. Emetre el certificat **Let's Encrypt** des de CloudPanel.
3. Crear la base de dades MySQL i l'usuari des de CloudPanel.
4. Instal·lar Redis, Supervisor i FFmpeg al servidor (`apt`), i configurar un programa Supervisor per a `php artisan horizon`.
5. Afegir el cron `* * * * * php /home/<usuari>/htdocs/xarxesgest.soportic.es/artisan schedule:run` des de la secció *Cron Jobs* de CloudPanel.
6. Configurar còpies de seguretat remotes de fitxers i base de dades (CloudPanel les admet cap a S3, Dropbox, etc.).
7. Deploy automàtic: GitHub Actions → SSH → `git pull`, `composer install --no-dev`, `php artisan migrate --force`, `php artisan optimize`, `horizon:terminate`.

URL de retorn OAuth que caldrà registrar a cada xarxa:
- `https://xarxesgest.soportic.es/oauth/linkedin/callback`
- `https://xarxesgest.soportic.es/oauth/instagram/callback`
- `https://xarxesgest.soportic.es/oauth/x/callback`

---

## 9. Seguretat i compliment

- Tokens d'accés i de refresc **xifrats a la base de dades** (`APP_KEY` fora del repositori).
- Claus d'API (Anthropic, OpenAI, X, Meta, LinkedIn) només a `.env` al servidor.
- Accés amb **2FA** obligatori i rols (admin, editor, revisor).
- Política de privacitat i condicions d'ús publicades a `/privacy` i `/terms` (exigides per Meta i LinkedIn).
- Endpoint d'eliminació de dades de Meta (*data deletion callback*).
- Registre d'auditoria de publicacions i canvis.
- Compliment RGPD: dades allotjades al VPS propi; els proveïdors d'IA reben només el contingut necessari per a cada tasca.

---

## 10. Riscos i mitigacions

| Risc | Impacte | Mitigació |
|---|---|---|
| LinkedIn no aprova la Community Management API o hi triga | No es pot publicar a les pàgines d'empresa | Sol·licitar-la la setmana 1; publicar al perfil mentrestant; agregador com a pla B per a LinkedIn |
| Canvis de preu o de condicions de l'API de X | Augment de cost | Límit de despesa configurable, evitar URL, lectures mínimes, X desactivable per marca |
| Canvis a les API de Meta (versions del Graph API cada ~3 mesos) | Trencament de funcions | Versió de l'API fixada i revisada trimestralment; proves d'integració |
| Caducitat de tokens | Publicacions fallides | Renovació automàtica, avisos per correu 7 dies abans, reconnexió amb un clic |
| Publicació fallida en el moment programat | Contingut no publicat | Reintents amb backoff, estat d'error visible i notificació |
| Contingut IA inadequat | Reputació de marca | Revisió humana obligatòria abans de programar (configurable per marca) |
| Cost IA desbocat | Factura inesperada | Registre de consum i límits mensuals per marca i proveïdor |

---

## 11. Decisions pendents

1. **LinkedIn:** publicar a les **pàgines d'empresa** de cada marca, al **perfil personal** o a tots dos? (Determina si cal la Community Management API.)
2. **Comptes d'Instagram:** confirmar que tots són professionals (Empresa o Creador).
3. **X:** quins comptes, i quin límit de despesa mensual vols fixar?
4. **Servidor:** és el mateix VPS de CloudPanel que ja allotja altres llocs? (Recursos: mínim 2 vCPU i 4 GB de RAM.)
5. **Usuaris:** només tu, o també col·laboradors amb rols (editor o revisor)?
6. **Abast inicial:** començar pel MVP (F0–F2) i decidir F3–F5 després de l'ús real?

---

## 12. Fonts consultades (preus i límits a novembre del 2026)

- Meta — Instagram Platform, Content Publishing: https://developers.facebook.com/documentation/instagram-platform/content-publishing
- Límits d'Instagram (resum de tercers): https://bundle.social/blog/instagram-api-rate-limits
- LinkedIn — Community Management API: https://developer.linkedin.com/product-catalog/marketing/community-management-api
- LinkedIn — Community Management App Review: https://learn.microsoft.com/en-us/linkedin/marketing/community-management-app-review
- LinkedIn — Increasing Access: https://learn.microsoft.com/en-us/linkedin/marketing/increasing-access
- Publicar a pàgines de LinkedIn per API (guia, oct. 2026): https://www.upload-post.com/how-to/post-to-linkedin-company-page-api/
- X API, pagament per ús (tercers): https://opentweet.io/how-to/x-api-pay-per-use-explained · https://bundle.social/blog/x-api-pricing-2026-costs-limits · https://postproxy.dev/blog/x-api-pricing-2026
- OpenAI GPT Image, preus (tercers): https://unifically.com/de/blogs/gpt-image-2 · https://aireiter.com/blog/gpt-image-2-api-pricing
- Preus de Claude: documentació oficial d'Anthropic (Opus 5.5: 4 $/20 $ per MTok; Sonnet 5.5: 2 $/10 $ per MTok)

> Els preus de tercers s'han de confirmar a les pàgines oficials de cada proveïdor abans de comprometre pressupost.
