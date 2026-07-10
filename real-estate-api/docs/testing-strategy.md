# Testing Strategy

Framework: **Pest 4** (`pestphp/pest`), PHPUnit 12 underneath. Current state: only the scaffold `ExampleTest` exists — everything below is the convention for tests we add.

## Commands

```bash
php artisan test --compact                     # full suite
php artisan test --compact --filter=name      # single test
php artisan make:test --pest SomeFeatureTest   # new feature test (no directory prefix in name)
php artisan make:test --pest --unit SomeTest   # unit test
```

## What to test, in priority order

1. **Pipeline rules (feature tests)** — the highest-risk logic:
   - Status transitions: submit → `pending`/`ai_processing`; fallback path → `clickup_review`.
   - Guards: edit/delete only in `draft`/`rejected`; publish only when `ready` (owner) — expect 403/409 otherwise.
   - `SubmissionPublisher` idempotency: publishing twice → 409, exactly one Property created, `published_at`/`published_property_id` stamped.
2. **Webhook auth** — wrong/missing `X-Webhook-Secret` → 401; empty configured secret → 401 (fail closed).
3. **Auth** — register/login by username, token grants access, logout revokes.
4. **Public vs private visibility** — unpublished properties hidden from public index; `?all=1` requires auth.
5. **CSV import** — valid rows imported, invalid rows reported in `skipped` with row numbers, >500 rows rejected.
6. **Unit tests** only for isolated logic (e.g. CSV row validation) — most tests should be feature tests hitting HTTP endpoints.

## Conventions

- Feature tests hit real HTTP routes with `RefreshDatabase`; assert status codes, JSON shape, and database state.
- Use model **factories** (check for custom states first) — never hand-insert rows.
- **Mock external services** (`ClickUpService`, `N8nWebhookService`) at the container level; tests must never call real ClickUp/n8n. Use `Http::fake()` for their HTTP layers.
- One behavior per test; name with `it('rejects publish when submission is draft')` style.
- Do not delete tests without approval.

## Coverage expectation

No numeric target yet. Gate: every new endpoint or pipeline rule lands **with** feature tests for its happy path + its guard failures. Bug fixes land with a regression test.
