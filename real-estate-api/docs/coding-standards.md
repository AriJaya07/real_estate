# Coding Standards

Backend (`real-estate-api`) is Laravel 13 / PHP 8.5. Follow the Laravel Boost guidelines in `CLAUDE.md` — they are authoritative. This file summarizes the project-specific points.

## PHP / Laravel

- **Formatting:** Laravel Pint. Run `vendor/bin/pint --dirty` before committing any PHP change. Do not hand-format.
- Explicit return types and parameter type hints on all methods. Constructor property promotion. Curly braces always, even single-line bodies.
- Descriptive names: `isRegisteredForDiscounts()`, not `discount()`. Enum keys in TitleCase.
- PHPDoc over inline comments; array-shape annotations where arrays cross boundaries. Inline comments only for genuinely non-obvious logic.
- Create files with `php artisan make:*` generators (`--no-interaction`), not by hand.

## Where code goes

- **Controllers** stay thin: validate (Form Requests preferred), authorize, call a model/service, return JSON. No integration logic in controllers.
- **Services** (`app/Services/`) hold multi-step or external-facing logic (ClickUp, n8n, CSV import, publishing). Follow the existing pattern: constructor-injected config, fail-soft with `Log::warning` when an integration is unconfigured.
- **Models** hold relationships, casts, scopes. Prefer scopes for repeated query filters (e.g. published properties).

## API conventions

- Routes live in `routes/api.php`, flat, grouped by middleware. Public read endpoints first, `auth:sanctum` group below.
- Follow existing response shapes (see `api-patterns.md`). No API versioning is used — match the existing convention rather than introducing `/v1`.
- Pagination: `paginate(10)` — keep the default consistent.

## Patterns to avoid

- Don't add cookie/session-based auth paths — the system is Bearer-token only, username-based (no email column exists).
- Don't create ClickUp tasks outside `ClickUpService`, and never from both the n8n path *and* the direct path for the same submission (duplicate-task bug).
- Don't publish submissions outside `SubmissionPublisher`.
- Don't add file-upload handling for images — images are URL strings by design (see `tech-debt.md` before changing).
- Don't run `composer update` casually — dependency bumps must be deliberate (the Dockerfile PHP version is coupled to `composer.lock`).

## Frontend (for reference)

Nuxt 4 SPA in `../Real-estate/`: Pinia stores, composables for shared state (`useToast`, `useSubmissionStatus`), Tailwind v4 utility classes. TypeScript pinned to 5.9 (TS 6 breaks vue-tsc).

## Git

- Small, focused commits, imperative subject line ≤ 50 chars.
- Never commit `.env` or any file containing secrets.
