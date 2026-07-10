# Testing Strategy — Frontend

Current state: **no test framework installed**. The only automated gate is type checking:

```bash
npx vue-tsc --noEmit
```

Run it before every commit that touches `.ts`/`.vue`.

## Planned stack (when tests are introduced)

- **Vitest** + `@nuxt/test-utils` + `@vue/test-utils` for unit/component tests (happy-dom).
- **Playwright** for a small e2e smoke suite against a local API with seeded data.

Adding these is a dependency change — get approval first (see `agent-rules.md`).

## What to test, priority order

1. **Composables (unit)** — cheapest, highest value:
   - `useSubmissionStatus`: meta completeness for all 7 statuses, step ordering.
   - `useFormat`: number/currency edge cases (0, large, decimals).
   - `CurrencyInput` logic: comma formatting, caret preservation, raw-number emit.
2. **Stores (unit, mocked api service)** — auth login/logout/restore; property `manageMode` param switching; submission filter/pagination state and 409-refresh behavior.
3. **Guard rendering (component)** — Edit/Delete only on `draft`/`rejected`, Publish only on `ready`; login CTA vs submission form on property detail.
4. **E2E smoke (Playwright, later)** — login → create submission → see it listed; public grid hides unpublished.

## Conventions (for when tests land)

- Mock at the `services/api.ts` boundary — components/stores never hit real HTTP in unit tests.
- Test files co-located: `foo.spec.ts` beside the unit, or `tests/` mirroring `app/` — pick one at introduction time and record it here.
- One behavior per test; name by behavior, not implementation.
- No snapshot tests for logic; snapshots only for stable presentational components, if at all.

## Coverage expectation

None enforced yet. Gate when the stack lands: new composables/store actions ship with unit tests; UI guard rules (point 3) covered before any pipeline UI change.
