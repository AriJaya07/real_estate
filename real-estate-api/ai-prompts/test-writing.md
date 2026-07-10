# Prompt Template — Test Writing

## Context to provide the agent

- What behavior to cover (endpoint, service, rule) and why it matters.
- `docs/testing-strategy.md` (conventions) and `docs/domain.md` (the rules being asserted).

## Prompt skeleton

```
Read docs/testing-strategy.md and docs/domain.md first.

Write Pest feature tests for: <endpoint/behavior>

Cover:
- Happy path: <expected success behavior>
- Guard failures: <each 401/403/409/422 case>
- <edge cases: pagination, filters, empty states…>

Rules:
- php artisan make:test --pest <Name>Test (no directory prefix in the name).
- RefreshDatabase; model factories only (check for custom states first); never hand-insert rows.
- Mock ClickUpService / N8nWebhookService or Http::fake() — never call real services.
- Assert status code + JSON shape + database state, not just "assertOk".
- One behavior per test, it('...') naming.
- Finish with a passing run: php artisan test --compact --filter=<Name>
```

## Expected output

Test file(s) + the passing test-run output. Each acceptance point above maps to at least one named test.

## Example

> Cover `POST /api/property-submissions/{id}/publish`:
> happy path (owner + ready → 200, Property created, published_at stamped);
> 403 non-owner; 409 status ≠ ready; 409 already published (no duplicate Property row);
> 401 unauthenticated.
