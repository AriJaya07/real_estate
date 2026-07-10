# Architecture — Frontend

## Overview

**DreamRue Estate** frontend: Nuxt 4 **SPA** (`ssr: false`), Vue 3, Pinia, Tailwind CSS v4, Axios. Consumes the Laravel API in the sibling repo `../real-estate-api/` (its docs: `../real-estate-api/docs/`). No server-side rendering, no Nuxt server routes — pure client app; the API is the only backend.

```
Browser ── Nuxt SPA (this repo) ──Axios + Bearer token──> Laravel API ──> PostgreSQL
```

## Directory layout (Nuxt 4 `app/` dir)

| Path | Contents |
|---|---|
| `app/pages/` | Routes: `/` (public landing: search + grid), `/properties/[id]` (public detail), `/login`, `/register`, `/dashboard{,/properties,/submissions,/users}` (authed admin area) |
| `app/layouts/` | `default` (public, Navbar), `auth` (login/register), `admin` (dashboard sidebar) |
| `app/middleware/` | `auth.ts` (guards dashboard), `guest.ts` (guards login/register) |
| `app/stores/` | Pinia: `auth.ts`, `property.ts`, `submission.ts` |
| `app/composables/` | `useToast`, `useSubmissionStatus` (central status meta), `useFormat` |
| `app/services/api.ts` + `app/plugins/api.ts` | Axios instance wired to `runtimeConfig.public.apiBase`, attaches Bearer token |
| `app/plugins/auth-init.ts` | Restores auth session on app boot |
| `app/components/` | UI kit (buttons, inputs, Modal, Toast…) + domain components (PropertyCard/Grid/Form, CurrencyInput, SearchBar) |
| `app/types/index.ts` | Shared TS types mirroring API resources |

## Key design decisions

1. **SPA mode** (`ssr: false`) — token-based auth in a browser store; no server session, no SSR hydration concerns. SEO not a requirement (internal system).
2. **Single Axios service** (`services/api.ts`) — all HTTP goes through it; interceptor adds `Authorization: Bearer`. Components never call axios directly.
3. **Pinia stores own server state.** Pages dispatch store actions; stores call the api service; components read store state. `property` store has `manageMode` flag → fetches with `?all=1` to include unpublished.
4. **Status metadata centralized** in `composables/useSubmissionStatus.ts` — labels/colors/step order for the 7-status pipeline. Never hardcode status strings in components.
5. **Toast system**: `useToast()` composable with module-level state; single `<Toast />` mounted in `app.vue`.
6. **CurrencyInput** live-formats with US-style commas while typing (caret-preserving); emits/stores the raw number — formatting is display-only.
7. **API base** default hardcoded in `nuxt.config.ts` (`http://localhost:8000/api`); `NUXT_PUBLIC_API_BASE` env overrides at build/runtime.

## Route access model

- Public: `/`, `/properties/[id]` (submission form shows only when authed, else login CTA).
- `guest` middleware: `/login`, `/register` (redirect away when authed).
- `auth` middleware: everything under `/dashboard`. No separate `/admin` routes.

## Data-flow example: submissions page

`pages/dashboard/submissions.vue` → on load dispatches `submission` store: fetch list (filters/pagination) + auto `sync-clickup` → renders rows with pipeline stepper (meta from `useSubmissionStatus`) → actions (edit/delete when draft/rejected, Publish when ready, CSV export via blob download) call store actions → toast on result.
