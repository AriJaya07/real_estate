# Agent Rules — real-estate-api

Rules for AI agents working in this repo. Read `docs/` first: `architecture.md` for the system, `domain.md` for terms and business rules, `tech-debt.md` before "fixing" anything odd-looking.

## Hard constraints

1. **Never commit or print secrets.** `.env` holds live ClickUp/n8n/webhook credentials. Docs and examples use placeholders only.
2. **Never run destructive DB commands** (`migrate:fresh`, `migrate:refresh`, dropping tables) against any non-local environment. On the server, also never `docker compose down -v` or `docker system prune` — the VPS hosts other production projects (see `docs/deployment.md`).
3. **Don't change dependencies without approval** — no casual `composer update`/`require`. Dockerfile PHP version and `composer.lock` are coupled.
4. **Don't delete tests without approval.**
5. **Follow Laravel Boost guidelines** in `CLAUDE.md` (generators, Pint, Pest, conventions) — they override defaults.

## Architecture invariants (keep these true)

- Auth = Sanctum Bearer tokens, **username-based, no email**. Don't reintroduce email/name columns or session auth.
- All submission publishing goes through `App\Services\SubmissionPublisher`.
- One ClickUp task per submission; direct ClickUp creation is only the n8n-down fallback.
- Integrations fail soft (`Log::warning`) — a user action must never hard-fail because n8n/ClickUp is down.
- Webhooks are protected by `X-Webhook-Secret` and fail closed when unconfigured.
- Property images are URL strings; no file-upload code.
- Status guards: edit/delete own submissions only in `draft`/`rejected`; manual publish only when `ready`.

## Working preferences

- Controllers thin; multi-step/external logic in `app/Services/`.
- `php artisan make:*` for new files; Form Requests for validation; feature tests (Pest) over unit tests.
- Run `vendor/bin/pint --dirty` after touching PHP.
- New/changed endpoints: update `docs/api-patterns.md`; schema changes: update `docs/domain.md`.
- Deployment questions: follow `docs/deployment.md` exactly — especially the shared-server safety rules.

## Prompt templates

Reusable task templates live in `/ai-prompts/`. Use the matching template's checklist when starting feature work, bug fixes, reviews, refactors, tests, or docs.
