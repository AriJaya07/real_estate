# API Patterns

Base URL: `http://62.171.156.55:8081/api` (production) · `http://localhost:8000/api` (local `php artisan serve`).

## Authentication

Sanctum **personal access tokens**, sent as `Authorization: Bearer <token>`. Login is by **username** (no email in the system).

```
POST /api/register   {username, password, password_confirmation}
POST /api/login      {username, password}   → {token, user}
POST /api/logout     (auth)
GET  /api/me         (auth) → current user
```

Demo credentials (seeded): `admin` / `password`, `testuser` / `password123`.

## Endpoint map

| Method & path | Auth | Notes |
|---|---|---|
| `GET /properties` | public | Published only; `?all=1` + auth returns everything. Filters: search/type/etc. |
| `GET /properties/{id}` | public | |
| `POST/PUT/DELETE /properties/*` | auth | Standard resource routes (index/show excluded). |
| `POST /properties/import` | auth | CSV, max 500 rows → `{imported, skipped:[{row, errors}]}` |
| `PATCH /properties/{id}/publish` | auth | Toggles `is_published`. |
| `GET /property-submissions` | auth | Own submissions; filters: `search`, `status`, sort; `paginate(10)`. |
| `GET /property-submissions/export` | auth | Streams CSV, same filters as index. |
| `POST /property-submissions` | auth | Creates draft/pending; may auto-advance (n8n/ClickUp). |
| `PUT /property-submissions/{id}` | auth | Own + status `draft` or `rejected` only. |
| `DELETE /property-submissions/{id}` | auth | Own + `draft`/`rejected` only. |
| `POST /property-submissions/{id}/publish` | auth | Own + status `ready` only. |
| `POST /property-submissions/sync-clickup` | auth | Polls ClickUp task statuses, advances submissions. |
| `GET /users`, `DELETE /users/{id}` | auth | Admin user management. |
| `POST /webhooks/status` | secret header | Advances pipeline status; optional `clickup_task_id`. |
| `POST /webhooks/publish` | secret header | Publishes a submission by `submission_id`. |
| `POST /webhooks/reject` | secret header | Rejects a submission. |

## Webhook authentication

Inbound webhooks require header `X-Webhook-Secret: <PUBLISH_WEBHOOK_SECRET>`. When the env var is empty the endpoints return **401** (fail closed). Callers (n8n) receive `submission_id` and `publish_callback_url` in the outbound payload.

## Response conventions

- JSON everywhere. Lists use Laravel's paginator envelope (`data`, `links`, `meta`).
- Validation errors: HTTP 422 with Laravel's standard `{message, errors: {field: [msgs]}}`.
- Auth failures: 401; ownership/status-guard violations: 403; state conflicts (publish twice, publish a draft): **409**.
- CSV export streams with `Content-Disposition: attachment`.

## Error-handling rules

- Integration failures (n8n, ClickUp) are **non-fatal**: log `Log::warning`, continue, let the fallback path or polling recover. A user's submission must never fail because an external service is down.
- Guard-clause early returns in controllers; state checks return 409 with a short message rather than throwing.

## Adding a new endpoint — checklist

1. Route in `routes/api.php` in the correct middleware group.
2. Controller method: validate → authorize (ownership!) → service/model → JSON response matching shapes above.
3. Status-guard rules if the resource participates in the pipeline.
4. Feature test (see `testing-strategy.md`).
5. Update the frontend store/composable and this file.
