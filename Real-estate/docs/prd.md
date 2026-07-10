# Product Requirements — Frontend

Product-level requirements are shared with the backend ([`../real-estate-api/docs/prd.md`](../real-estate-api/docs/prd.md)). This file covers the UI-specific requirements.

## Screens & acceptance criteria

### `/` — Public landing
- Search bar + property grid of **published** properties only; no auth required.
- Card → `/properties/[id]`.

### `/properties/[id]` — Public detail
- Full property info. Authenticated visitor sees the **submission form**; anonymous visitor sees a **login CTA** instead.

### `/login`, `/register` (guest-only)
- Username + password (no email fields). Errors surfaced per-field (422 mapping). Successful login → dashboard; authed users redirected away by `guest` middleware.

### `/dashboard` (auth) — admin area, sidebar layout
- **Overview** (`index`): entry point after login.
- **Properties**: manage mode listing (published + unpublished "Pending Review" from CSV imports), publish toggle, create/edit/delete, **CSV import** with template download and per-row error report.
- **Submissions**: the core screen —
  - Filter bar (search, status), sort, pagination (10/page).
  - Auto ClickUp **sync on load** + manual Sync button.
  - Details modal with **pipeline stepper** (7 statuses, meta from `useSubmissionStatus`).
  - Edit modal + delete confirm — enabled only for `draft`/`rejected` rows.
  - **Publish** button on `ready` rows (own submissions).
  - CSV export honoring current filters (blob download).
  - Publish-status badges.
- **Users**: list + delete.

## UX rules

- Every mutation gives toast feedback (success and failure).
- Empty lists render `EmptyState`, loading renders `Loading` — no blank screens.
- Money fields use `CurrencyInput`: live comma formatting while typing, caret preserved, raw number submitted.
- 409 responses are normal business outcomes — show the message, refresh the affected row/list, never treat as a crash.
- Status names, colors, and step order come from one source (`useSubmissionStatus`) so the stepper, badges, and filters never disagree.

## Out of scope (current)

Image uploads (URL strings only), email flows, role-based UI (all authed users see the dashboard), i18n, SSR/SEO.
