# Environment Setup — Frontend

## Prereqs

Node 20+, npm. Backend API running (see [`../real-estate-api/docs/environment.md`](../real-estate-api/docs/environment.md)).

## Local development

```bash
npm install
cp .env.example .env        # optional — defaults already point at local API
npm run dev                 # http://localhost:3000
```

Backend must be up at `http://localhost:8000` (`php artisan serve` in `../real-estate-api`).

Demo logins (seeded by the API): `admin` / `password`, `testuser` / `password123`.

## Environment variables

| Var | Default | Purpose |
|---|---|---|
| `NUXT_PUBLIC_API_BASE` | `http://localhost:8000/api` (hardcoded in `nuxt.config.ts` runtimeConfig) | API base URL. Override per environment. Production API: `http://62.171.156.55:8081/api`. |

That's the only env var. Secrets live backend-side; this repo must never hold tokens.

## Commands

```bash
npm run dev          # dev server :3000 (HMR)
npm run build        # production build (.output/)
npm run generate     # static generation (SPA payload)
npm run preview      # serve the production build locally
npx vue-tsc --noEmit # type check (TS pinned 5.9 — see tech-debt.md)
```

## Deployment target

Not deployed yet (see `tech-debt.md`). When deploying against the production API:

1. Build with the env override:
   ```bash
   NUXT_PUBLIC_API_BASE=http://62.171.156.55:8081/api npm run generate
   ```
2. Host the static output (any static host, or a small nginx/Caddy container on the same Contabo server — coordinate ports per `../real-estate-api/docs/deployment.md` shared-server rules).
3. Backend must whitelist the frontend origin: set `FRONTEND_URL` in the API's server `.env`, then re-run `config:cache` (CORS).
