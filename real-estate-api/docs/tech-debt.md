# Tech Debt & Known Workarounds

Ranked roughly by risk. Check here before "fixing" something that looks odd — several items are deliberate.

## High

1. **No real test suite.** `tests/Feature` contains only the scaffold `ExampleTest`. The pipeline (status guards, publish idempotency, webhook auth) is untested. Highest-value next investment — see `testing-strategy.md`.
2. **Production runs plain HTTP on `:8081`.** Tokens and the webhook secret travel unencrypted. Plan: reuse the server's existing Caddy for TLS + domain, then close 8081. See `deployment.md`.
3. **Seeded demo credentials in production** (`admin`/`password`). Rotate/remove before real users.
4. **No roles/authorization layer.** "Admin" is a convention, not a role column — every authenticated user can hit user-management and import endpoints. Needs a role/policy layer before multi-tenant use.

## Medium

5. **ClickUp sync is polling, not webhooks** (deliberate: built on localhost which ClickUp can't reach). The server is now public, so ClickUp webhooks are feasible — replacing/complementing `sync-clickup` polling is now possible. Keep the polling fallback.
6. **Manual rsync deploys.** No CI, no atomic releases, no rollback beyond git. Candidate: GitHub Actions → ssh → compose up.
7. **`QUEUE_CONNECTION=database` but no worker runs** in the prod container. Anything queued will sit forever. Either run `php artisan queue:work` as a second process/container or keep everything synchronous (current behavior).
8. **Laravel 13, not 12.** The installer delivered 13; kept intentionally. Don't "downgrade to match the original ask."

## Low / deliberate quirks

9. **Images are URL strings only** — no uploads by design; `storage:link` symlink exists but is unused. Adding uploads means multipart handling, validation, and disk config — a feature, not a fix.
10. **Auth is username-only; users table stripped** of name/email/password_reset_tokens. Password reset is impossible by design (no email). Don't reintroduce email casually — migrations and seeders assume its absence.
11. **Frontend TypeScript pinned to 5.9** — TS 6 removed the `./lib/tsc` export that vue-tsc needs. Don't bump until vue-tsc supports it.
12. **Dockerfile PHP version is coupled to `composer.lock`** (built on PHP 8.5; Symfony v8.1 needs ≥8.4). Bumping either side alone breaks the build. Bump together, deliberately.
13. **No API versioning** — flat `/api/*`. Fine at this scale; revisit if external consumers appear.
14. **`config:cache` in prod** means `.env` edits are inert until re-cached — an operational footgun documented in `deployment.md`.
