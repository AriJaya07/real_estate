# Product Requirements

## Goal

Real Estate Management System: a public property-listing site plus an authenticated dashboard where users submit properties that flow through an AI + human review pipeline (n8n + ClickUp) before publication.

## Users

- **Visitor** — browses published properties, no account.
- **Registered user** — submits properties, tracks their submissions through the pipeline, publishes when approved.
- **Admin** — manages all properties (including unpublished), imports CSVs, manages users, monitors submissions. (Note: there is no role column yet — "admin" is currently just a seeded user; see `tech-debt.md`.)

## User stories & acceptance criteria

### Public browsing
- As a visitor I can search and browse **published** properties on the landing page and open a property detail page.
  - Unpublished properties never appear publicly. `?all=1` requires auth.
- On a property page, an authenticated user sees a submission form; a visitor sees a login CTA.

### Auth
- Register/login with **username + password** (no email). Bearer token returned on login; protected pages/endpoints require it.

### Property submissions (core flow)
- As a user I can create a submission (draft or submit immediately).
- Statuses: `draft → pending → ai_processing → clickup_review → ready → published | rejected`.
- Acceptance:
  - Submitting triggers the n8n webhook; if n8n is down, a ClickUp task is created directly (never both).
  - I can edit/delete my submission only while `draft` or `rejected`; editing a rejected one lets me resubmit.
  - When status is `ready` I see a **Publish** button; publishing creates a public Property and stamps `published_at`.
  - I can filter (search/status), sort, paginate (10/page), and export my submissions to CSV.
  - The submissions page auto-syncs ClickUp state on load, with a manual Sync button.

### Review pipeline (operators)
- ClickUp task per submission under review; moving the task to done/closed advances the submission (`ready`, or auto-publish when `publish_ready`).
- n8n can advance/reject/publish via secret-protected webhooks.

### Admin
- Manage all properties (`manageMode`), toggle `is_published` (CSV imports arrive unpublished = "Pending Review").
- Bulk CSV import (columns: title, location, price, type, image, description; ≤500 rows) with a downloadable template and per-row error report.
- View users, delete users.

## Out of scope (current)

- Image/file uploads (images are URLs).
- Email flows (no email addresses exist).
- Roles/permissions beyond auth (single implicit admin).
- Payments, favorites, messaging.

## Non-functional

- API responds under HTTP on the shared VPS (HTTPS planned — see `tech-debt.md`).
- External-service outages must never block user actions (fail-soft integrations).
- Demo data seeded for evaluation: `admin`/`password`, `testuser`/`password123`.
