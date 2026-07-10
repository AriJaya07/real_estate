# Environment Setup

## Local development

Prereqs: PHP 8.5, Composer, PostgreSQL, Node 20+ (for the frontend repo).

```bash
# backend
cd real-estate-api
composer install
cp .env.example .env
php artisan key:generate
# create Postgres db `real_estate` (local convention: user `arijaya`, no password)
php artisan migrate --seed
php artisan serve            # http://localhost:8000

# frontend (sibling repo Real-estate/)
cd ../Real-estate
npm install
cp .env.example .env         # NUXT_PUBLIC_API_BASE if not default
npm run dev                  # http://localhost:3000
```

Demo logins (seeded): `admin` / `password`, `testuser` / `password123`.

## Environment variables (backend)

| Var | Local | Production | Notes |
|---|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `local` / `true` | `production` / `false` | |
| `APP_KEY` | `key:generate` | separate prod key | Never reuse local key in prod; never rotate once prod has data. |
| `APP_URL` | `http://localhost:8000` | `http://62.171.156.55:8081` | Also feeds `publish_callback_url` sent to n8n. |
| `DB_CONNECTION` | `pgsql` | `pgsql` | |
| `DB_HOST` | `127.0.0.1` | `db` | Prod value is the Docker compose service name. |
| `DB_DATABASE` | `real_estate` | `real_estate` | |
| `DB_USERNAME` / `DB_PASSWORD` | local user | `real_estate` / strong secret | Prod password also provisions the Postgres container (`${DB_PASSWORD}` in compose). |
| `FRONTEND_URL` | `http://localhost:3000` | frontend origin | CORS. |
| `N8N_WEBHOOK_URL` | n8n webhook URL | same | Empty ⇒ n8n skipped (fallback to direct ClickUp). |
| `CLICKUP_API_TOKEN` | personal token | same | Empty ⇒ ClickUp disabled, logs warning. |
| `CLICKUP_LIST_ID` | list id | same | Project 1 list. |
| `PUBLISH_WEBHOOK_SECRET` | random hex | same | Inbound webhooks must send it as `X-Webhook-Secret`; empty ⇒ webhooks 401. |

Integration config is read through `config/services.php` — remember `php artisan config:cache` after changing prod env values.

Real secret values: server `.env` only / team password manager. Never in git, docs, or rsync.

## Frontend env

`NUXT_PUBLIC_API_BASE` — API base URL; defaults to the local API in `nuxt.config` runtimeConfig.

## Third-party services

- **ClickUp** — human review board. Task per submission; list id in env. Token is a personal API token.
- **n8n** — AI-processing pipeline, self-hosted **on the same Contabo server** (behind Caddy at `vmi3284029.contaboserver.net`). Receives submission webhooks; calls back to `/api/webhooks/*`.

## Deployment target

Contabo VPS via Docker Compose — full guide in [`deployment.md`](deployment.md).
