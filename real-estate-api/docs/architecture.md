# Architecture

## System overview

Real Estate Management System, two repos in one workspace:

- **`real-estate-api/`** (this repo) — Laravel 13 REST API, PHP 8.5, PostgreSQL, Sanctum bearer-token auth.
- **`Real-estate/`** — Nuxt 4 SPA (`ssr: false`), Pinia, Tailwind v4, Axios. Talks to this API only.

External integrations: **n8n** (AI processing pipeline, webhook-driven) and **ClickUp** (human review board). Both optional — the app degrades gracefully (logs a warning) when their env vars are empty.

```
Nuxt SPA ──Bearer token──> Laravel API ──> PostgreSQL
                               │
                ┌──────────────┼───────────────┐
                ▼                              ▼
        n8n (AI processing)            ClickUp (review board)
                └───── webhooks back ──────────┘
       POST /api/webhooks/{status,publish,reject}  (X-Webhook-Secret)
```

## Backend layout

| Layer | Location | Notes |
|---|---|---|
| Routes | `routes/api.php` | Single flat API file. Public: auth, property list/show, webhooks. Everything else behind `auth:sanctum`. |
| Controllers | `app/Http/Controllers/Api/` | `AuthController`, `PropertyController`, `PropertySubmissionController`, `UserController`, `WebhookController`. |
| Models | `app/Models/` | `User`, `Property`, `PropertySubmission`. |
| Services | `app/Services/` | `ClickUpService`, `N8nWebhookService`, `PropertyCsvImportService`, `SubmissionPublisher`. Integration + multi-step logic lives here, not in controllers. |

## Key design decisions

1. **Token auth, no sessions/cookies.** Sanctum personal access tokens sent as `Authorization: Bearer`. No stateful-domain config needed; CORS is the only cross-origin concern. Auth is **username-based** — the users table has no name/email columns.
2. **Two property tables.** `properties` = public listings (with `is_published` flag). `property_submissions` = user-submitted drafts flowing through a review pipeline. Publishing a submission *creates* a Property row (`SubmissionPublisher`), it does not mutate the submission into one.
3. **Submission pipeline (7 statuses):** `draft → pending → ai_processing → clickup_review → ready → published | rejected`. On submit, Laravel auto-advances: n8n webhook OK → `ai_processing`; n8n unavailable → direct ClickUp task creation → `clickup_review` (fallback only, prevents duplicate tasks). Inbound webhooks advance the rest.
4. **ClickUp sync is polling, not webhooks** (`POST /property-submissions/sync-clickup`), because the original dev environment (localhost) could not receive ClickUp webhooks. Submissions store `clickup_task_id`. See `tech-debt.md`.
5. **Publish logic centralized** in `App\Services\SubmissionPublisher` — used by both the user-facing publish endpoint and the webhook path. Guards: 409 on draft / not publish-ready / already published.
6. **Images are URL strings only.** No file uploads anywhere; property updates are plain JSON PUTs. The `storage:link` symlink exists but is unused.
7. **CSV import** (`POST /properties/import`) via `PropertyCsvImportService`: max 500 rows, per-row validation, returns `{imported, skipped: [{row, errors}]}`. Imported properties arrive `is_published=false` ("Pending Review").

## Data flow: submission lifecycle

1. Authed user POSTs `/property-submissions` (status `draft`) or submits directly (`pending`).
2. API notifies n8n (`N8nWebhookService`) with `submission_id` + `publish_callback_url`; on success status → `ai_processing`. If n8n is down, `ClickUpService` creates the task directly → `clickup_review`.
3. n8n/ClickUp send status webhooks (`POST /api/webhooks/status`, secret header) moving the submission along.
4. Publication: webhook `POST /api/webhooks/publish`, or the user's publish button when `ready`, or auto-publish during ClickUp sync when the task is done and `publish_ready` is set. All paths converge on `SubmissionPublisher`.
5. Users can edit/delete their own submissions only in `draft` or `rejected` status; editing a rejected one allows resubmission.

## Deployment shape

Docker Compose on a shared Contabo VPS: `app` container (nginx+fpm single image) on host port 8081, `db` container (Postgres 16) internal-only with a named volume. See `deployment.md`.
