# Deployment Guide — Contabo VPS

Everything about deploying, updating, and operating `real-estate-api` on the Contabo server.

## Overview

| Item | Value |
|---|---|
| Server | Contabo VPS — `62.171.156.55` (`vmi3284029.contaboserver.net`) |
| SSH | `ssh deploy@62.171.156.55` |
| App URL | `http://62.171.156.55:8081` |
| Runtime | Docker Compose (`docker-compose.prod.yml`) |
| App container | `real-estate-api-app-1` — nginx + PHP-FPM (`serversideup/php:8.5-fpm-nginx`), listens on container port 8080, published on host port **8081** |
| DB container | `real-estate-api-db-1` — Postgres 16 (alpine), **not published to the internet**, data in Docker volume `real-estate-api_pgdata` |
| Server directory | `~/real-estate-api` (home of the `deploy` user) |

### Why port 8081?

The server hosts other projects. Ports already taken: **80/443** (Caddy, reverse proxy for n8n), **3500** (traveller-be). Port 8081 was free. Do not change the published port without checking `docker ps` and `sudo ss -tlnp` first.

### Shared-server safety rules

This server runs other production projects (n8n + Caddy, traveller-be + redis, be-nihongo). Docker Compose isolates our stack by project name (`real-estate-api`): own containers, own private network, own volume. Rules:

1. **Never** run `docker compose down -v` (deletes the DB volume) or `docker system prune -a --volumes` (destroys other projects' images/volumes too).
2. **Never** publish port 5432. The DB is reachable only from the app container over the internal Docker network (`DB_HOST=db`).
3. **Never** enable/reconfigure `ufw` broadly — other projects depend on open ports. Only ever `sudo ufw allow <port>/tcp` for a specific new port.
4. Safe commands: `up -d --build`, plain `down`, `restart`, `logs`, `exec`.

## Architecture on the server

```
Internet ──:8081──> app container (nginx + php-fpm, Laravel)
                         │  internal Docker network (real-estate-api_default)
                         └────> db container (Postgres 16)
                                    └── volume: real-estate-api_pgdata

Same host, separate stacks (do not touch):
Internet ──:80/443──> Caddy ──> n8n        (n8n compose project)
Internet ──:3500───> traveller-be ─> redis
```

## Files that make deployment work

| File | Purpose |
|---|---|
| `Dockerfile` | Builds the app image. Base `serversideup/php:8.5-fpm-nginx` + `pdo_pgsql`. PHP version **must satisfy `composer.lock`** (built on PHP 8.5; Symfony v8.1 packages need ≥ 8.4). |
| `docker-compose.prod.yml` | Two services: `app` (port 8081→8080) and `db` (Postgres 16 + healthcheck + volume). `app` waits for `db` healthy. |
| `.dockerignore` | Excludes `vendor`, `node_modules`, `.env`, `.git`, tests from the image. |
| `.env` (server only) | Production secrets. Lives only at `~/real-estate-api/.env` on the server. **Never committed, never rsynced.** |

## Production `.env`

Copy `.env.example`, then set at minimum (real values live only on the server / in the team password manager):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:<generate with: php artisan key:generate --show>
APP_URL=http://62.171.156.55:8081

LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=db            # Docker service name — NOT localhost, NOT an IP
DB_PORT=5432
DB_DATABASE=real_estate
DB_USERNAME=real_estate
DB_PASSWORD=<strong random — also consumed by the db container via ${DB_PASSWORD} in compose>

FRONTEND_URL=<frontend origin, used for CORS>
N8N_WEBHOOK_URL=<n8n webhook URL>
CLICKUP_API_TOKEN=<ClickUp personal token>
CLICKUP_LIST_ID=<ClickUp list id>
PUBLISH_WEBHOOK_SECRET=<random hex — must match what n8n/ClickUp callers send in X-Webhook-Secret>
```

Notes:

- `DB_HOST=db` is the compose service name; containers resolve it over the internal network.
- The prod `APP_KEY` is different from local on purpose. Never regenerate it once data exists (it encrypts data).
- Integrations (n8n, ClickUp) fail silently with a `Log::warning` when their env vars are empty — the app still runs.
- n8n runs on this same server; it calls us back at `http://62.171.156.55:8081/api/webhooks/...` (built from `APP_URL`).

## First-time deployment (already done — for reference / disaster recovery)

```bash
# 0. Inspect before touching anything (shared server!)
docker ps --format 'table {{.Names}}\t{{.Image}}\t{{.Ports}}'
sudo ss -tlnp | grep -E ':8081 '        # must be empty
sudo ufw status

# 1. Ship code from local machine
rsync -az --exclude vendor --exclude node_modules --exclude .env \
  ./ deploy@62.171.156.55:~/real-estate-api/

# 2. On the server
ssh deploy@62.171.156.55
cd ~/real-estate-api
nano .env                                # paste production env (see above)

docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d

# 3. Database (answer "yes" at the production prompts)
docker compose -f docker-compose.prod.yml exec app php artisan migrate
docker compose -f docker-compose.prod.yml exec app php artisan db:seed   # demo users: admin/password

# 4. Caches
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache

# 5. Verify
curl http://localhost:8081/api/properties          # on the server
curl http://62.171.156.55:8081/api/properties      # from your machine
```

Notes from the actual first deploy:

- `migrate:status` on a fresh DB errors with `Migration table not found` — this is normal; run `migrate` first.
- If the composer step fails with `symfony/* requires php >= 8.4` — the Dockerfile base image PHP is older than what `composer.lock` was built with. Bump the `FROM` tag; do **not** run `composer update` on the server.
- If a build error looks already-fixed, check the server actually has the latest files (`head -1 Dockerfile`) — a forgotten rsync is the usual cause.

## Updating / redeploying (every code change)

```bash
# 1. From the local repo root — ship code
rsync -az --exclude vendor --exclude node_modules --exclude .env \
  ./ deploy@62.171.156.55:~/real-estate-api/

# 2. Rebuild + restart + migrate + refresh caches
ssh deploy@62.171.156.55 'cd ~/real-estate-api \
  && docker compose -f docker-compose.prod.yml up -d --build \
  && docker compose -f docker-compose.prod.yml exec app php artisan migrate --force \
  && docker compose -f docker-compose.prod.yml exec app php artisan config:cache \
  && docker compose -f docker-compose.prod.yml exec app php artisan route:cache'

# 3. Smoke test
curl http://62.171.156.55:8081/api/properties
```

`--force` on `migrate` only skips the interactive "run in production?" confirmation — required in non-interactive one-liners. It is not a destructive flag. Plain `migrate` only applies *new* migrations. The destructive commands are `migrate:fresh` / `migrate:refresh` (they drop tables) — never run those in production.

**`config:cache` trap:** after any `.env` change on the server you must re-run `php artisan config:cache`, otherwise the old values stay active.

## Day-2 operations

```bash
COMPOSE="docker compose -f docker-compose.prod.yml"   # run from ~/real-estate-api

$COMPOSE ps                        # container status + health
$COMPOSE logs -f app               # tail app logs (Laravel log also in storage/logs/)
$COMPOSE exec app php artisan tinker    # REPL inside the app
$COMPOSE exec db psql -U real_estate real_estate     # SQL console
$COMPOSE restart app               # restart app only
$COMPOSE down && $COMPOSE up -d    # full recycle (volume/data survives)
```

### Database backup / restore

```bash
# backup (run on server)
mkdir -p ~/backups
docker compose -f docker-compose.prod.yml exec -T db \
  pg_dump -U real_estate real_estate > ~/backups/re_$(date +%F).sql

# restore
cat ~/backups/re_2026-07-10.sql | docker compose -f docker-compose.prod.yml exec -T db \
  psql -U real_estate real_estate
```

Recommended: add the backup command to the deploy user's crontab (`crontab -e`), daily.

### Reset this project's database only (destroys OUR data, touches nothing else)

```bash
docker compose -f docker-compose.prod.yml down
docker volume rm real-estate-api_pgdata
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate
docker compose -f docker-compose.prod.yml exec app php artisan db:seed
```

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `curl localhost:8081` fails **on your laptop** | `localhost` is your machine. Use the server IP, or run curl over SSH. |
| Works on server, not from outside | Firewall. `sudo ufw status` → `sudo ufw allow 8081/tcp`. |
| 500 errors, blank | `$COMPOSE logs app` and `storage/logs/laravel.log`. Check `APP_KEY` set and DB reachable. |
| `SQLSTATE` connection refused | `db` container unhealthy (`$COMPOSE ps`), or `DB_HOST` not `db`, or password mismatch between `.env` and the volume's original password. |
| env change has no effect | Re-run `config:cache` (see trap above). |
| composer PHP version error during build | Dockerfile `FROM` tag older than `composer.lock`'s PHP. Bump the tag. |
| Permission denied on storage/ | `$COMPOSE exec app chown -R www-data:www-data storage bootstrap/cache` |
| Webhooks 401 | Caller's `X-Webhook-Secret` header must equal `PUBLISH_WEBHOOK_SECRET`; endpoint 401s when env value empty. |

## Known limitations / next steps

- **HTTP only.** No TLS on :8081. Next step: point a real domain at the server and add a vhost to the existing Caddy (it already terminates TLS for n8n) reverse-proxying to `real-estate-api-app-1:8080`, then stop publishing 8081. Requires joining the Caddy container to our Docker network — coordinate carefully, Caddy belongs to the n8n stack.
- Deployment is manual rsync. Next step: git-based deploy or CI (GitHub Actions → ssh).
- Seeded demo credentials (`admin`/`password`) exist in production — rotate or delete before real users arrive.
