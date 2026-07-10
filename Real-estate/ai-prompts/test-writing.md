# Prompt Template — Test Writing (Frontend)

**Note:** no test framework is installed yet (see `docs/testing-strategy.md`). Introducing Vitest/@nuxt/test-utils is a dependency change → needs approval first. Until then the only automated check is `npx vue-tsc --noEmit`.

## Context to provide the agent

- What to cover (composable, store action, component guard rendering).
- `docs/testing-strategy.md` (conventions + planned stack) and `docs/domain.md` (UI invariants being asserted).

## Prompt skeleton

```
Read docs/testing-strategy.md first. If the test stack is not yet installed, propose the
exact devDependencies and config to add and STOP for approval before installing.

Write tests for: <unit/behavior>

Cover:
- <happy path>
- <edge cases>
- <guard/invariant cases from docs/domain.md>

Rules:
- Mock at the services/api.ts boundary — no real HTTP in unit tests.
- Stores tested with fresh Pinia per test; composables tested in isolation.
- One behavior per test, named by behavior.
- Follow the file-location convention recorded in docs/testing-strategy.md
  (record it there if this is the first test).
- Finish with the passing test-run output.
```

## Expected output

Test files + passing run output. If stack installation was needed: the approved dependency diff and config, recorded in `docs/testing-strategy.md`.

## Example

> Cover `useSubmissionStatus`: every one of the 7 statuses has label/color/step meta; step indexes are strictly ordered draft→published; unknown status falls back safely (no throw).
