# Domain Glossary & Business Rules

## Entities

| Entity | Table | What it is |
|---|---|---|
| **User** | `users` | Account with **username** + password only (name/email columns removed). Owns submissions. |
| **Property** | `properties` | A public listing. Has `is_published` (default `true`; CSV imports arrive `false`). Image = URL string. |
| **PropertySubmission** | `property_submissions` | A user-submitted property going through review. Carries `status`, `clickup_task_id`, `publish_ready`, `published_at`, `published_property_id`. |

Relationship: `User 1—N PropertySubmission`; a published `PropertySubmission 1—1 Property` (via `published_property_id`). A Property created by publish is a **copy** of the submission's data, not a live link.

## Glossary

- **Pipeline** — the submission status flow: `draft → pending → ai_processing → clickup_review → ready → published | rejected`.
- **draft** — user is still editing; not sent anywhere. Editable/deletable by owner.
- **pending** — submitted, waiting for handoff to processing.
- **ai_processing** — n8n accepted the webhook; AI enrichment in progress.
- **clickup_review** — a human reviews the ClickUp task. Reached via n8n, or directly when n8n was down (fallback).
- **ready** — approved; owner may publish.
- **published** — a public Property exists for it. Terminal.
- **rejected** — declined. Owner may edit and resubmit. Only other editable state.
- **publish_ready** — flag meaning "auto-publish when the ClickUp task completes" (vs. requiring the user's manual publish).
- **Publish** — create a Property from a submission (`SubmissionPublisher`), stamp `published_at` + `published_property_id`.
- **Pending Review (properties)** — a Property with `is_published=false` (typical for CSV imports); hidden from the public list.
- **Sync (ClickUp)** — polling ClickUp task `status.type` for each active submission: `open/custom → clickup_review`; `done/closed → ready` (or auto-publish if `publish_ready`).
- **manageMode** — frontend flag: fetch properties with `?all=1` (auth) to include unpublished ones.

## Business rules (enforced in code — keep them true)

1. Owners may edit/delete a submission **only** in `draft` or `rejected`.
2. Manual publish requires: owner + status `ready`. Webhook publish is allowed from any pipeline status except guards: not a draft, not already published, publish-ready checks — violations return 409.
3. One ClickUp task per submission — direct creation is a **fallback** when n8n is unreachable, never in addition to it.
4. Webhook endpoints require `X-Webhook-Secret` matching `PUBLISH_WEBHOOK_SECRET`; empty secret ⇒ endpoint refuses (401).
5. Public property list shows `is_published=true` only.
6. Integration failures are non-fatal (log + continue); user actions never hard-fail on n8n/ClickUp being down.
7. Prices are stored as raw numbers; formatting (US-style commas) is frontend-only (`CurrencyInput`).
