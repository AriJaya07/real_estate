# API Patterns — Frontend Consumption

The API contract is owned by the backend — full endpoint map in [`../real-estate-api/docs/api-patterns.md`](../real-estate-api/docs/api-patterns.md). This file covers how **this app** consumes it.

## Client setup

- Base URL from `runtimeConfig.public.apiBase` (`NUXT_PUBLIC_API_BASE` env; default `http://localhost:8000/api`).
- Single Axios instance in `app/services/api.ts`, provided via `app/plugins/api.ts`.
- Auth: Sanctum **Bearer token** attached by interceptor; token + user held in the `auth` store, restored on boot by `plugins/auth-init.ts`. Login is **username + password** (no email anywhere).

## Request/response handling rules

- Lists are Laravel paginator envelopes: read `data`, `meta` (page 10/row default). Keep pagination state in the store.
- Expected error statuses and required handling:
  - **401** — token invalid/expired: clear auth store, redirect to `/login`.
  - **403** — not owner: toast error, no redirect.
  - **409** — state conflict (publish twice, edit non-draft): toast the API message — these are normal outcomes, not bugs.
  - **422** — validation: map `errors: {field: [msgs]}` onto form field errors; never show raw JSON.
- Toast every mutation result (success + failure) via `useToast()`.
- Integration-degraded responses: backend never fails a submission because ClickUp/n8n is down — don't add frontend retries for that.

## Domain-specific calls

- **Public property list**: plain `GET /properties`; admin "manage mode" adds `?all=1` (requires auth) to include unpublished.
- **Submissions page**: on-load auto `POST /property-submissions/sync-clickup`, then fetch list with `search`/`status`/sort/page params kept in store.
- **CSV export**: `GET /property-submissions/export` with current filters, `responseType: 'blob'`, trigger browser download.
- **CSV import** (admin): multipart POST `/properties/import`; render per-row errors from `skipped: [{row, errors}]`.
- **Publish** (owner, status `ready`): `POST /property-submissions/{id}/publish`; expect 409 when raced by auto-publish — refresh list on 409.

## Adding a new API call — checklist

1. Confirm/extend the endpoint in backend docs first.
2. Type the response in `app/types/index.ts`.
3. Method in `app/services/api.ts`; action in the owning Pinia store.
4. Handle 401/403/409/422 per the table above; toast results.
5. Update this file if the consumption pattern is non-obvious.
