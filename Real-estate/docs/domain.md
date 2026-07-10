# Domain Glossary — Frontend

Canonical domain model lives in [`../real-estate-api/docs/domain.md`](../real-estate-api/docs/domain.md) (entities, pipeline, business rules). This file adds the frontend-specific vocabulary.

## Shared essentials (summary)

- **Property** — public listing; `is_published=false` = "Pending Review" (hidden from public grid).
- **PropertySubmission** — user-submitted property in the review pipeline.
- **Pipeline** — `draft → pending → ai_processing → clickup_review → ready → published | rejected`. Editable/deletable states for owners: `draft`, `rejected`. Publishable state: `ready`.
- Auth is **username-based**; no email exists in the system.

## Frontend terms

| Term | Meaning |
|---|---|
| **manageMode** | `property` store flag → fetch `/properties?all=1` (auth) so admins see unpublished rows too. Public grid always fetches without it. |
| **Pipeline stepper** | Visual step indicator in the submission details modal; step order/labels/colors from `useSubmissionStatus`. |
| **Status meta** | The single source of truth object in `composables/useSubmissionStatus.ts`: per-status label, color, step index. All badges/filters/steppers read it. |
| **Sync** | Submissions-page action calling `POST /property-submissions/sync-clickup`; runs automatically on page load, plus manual button. |
| **Publish button** | Shown on own rows with status `ready`; may 409 if auto-publish won the race — refresh on 409. |
| **Pending Review badge** | Property with `is_published=false` in dashboard properties list. |
| **Toast** | Global notification via `useToast()`; single `<Toast />` in `app.vue`. |
| **CurrencyInput** | Money field: displays live US-style comma formatting, emits raw number. Display formatting never reaches the API. |

## UI invariants (keep true)

1. Status strings never hardcoded in templates — always `useSubmissionStatus`.
2. Edit/Delete controls render only for `draft`/`rejected` rows; Publish only for `ready` — mirror of backend guards, so a user never clicks into a guaranteed 403/409.
3. Public pages must work with zero auth state (no token reads outside guarded paths).
4. Prices display formatted, transmit raw.
