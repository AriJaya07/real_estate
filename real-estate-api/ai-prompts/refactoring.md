# Prompt Template — Refactoring

## Context to provide the agent

- What smells / why the refactor is wanted (duplication, fat controller, unclear naming…).
- Scope boundary: which files/areas are in and out of scope.
- `docs/tech-debt.md` — the refactor may already be listed, or the "smell" may be deliberate.

## Prompt skeleton

```
Read agent-rules.md and docs/tech-debt.md first — do not "fix" documented deliberate quirks
(username-only auth, URL-string images, polling sync, Laravel 13).

Refactor: <what and why, one line>

Scope: <files/dirs in scope>. Out of scope: <explicitly excluded>.

Rules:
- Behavior-preserving: no endpoint shapes, status codes, or business rules may change.
- Run the test suite before AND after; identical results required. If coverage is missing
  for the code being moved, add characterization tests FIRST.
- Keep moves aligned with architecture: multi-step/external logic into app/Services/,
  controllers stay thin.
- Small reviewable commits, one concern each.
- vendor/bin/pint --dirty at the end.
```

## Expected output

1. Before/after design sketch (a few lines).
2. Characterization tests added (if any).
3. The refactor in small commits.
4. Proof: test run output before and after.

## Example

> Refactor: `PropertySubmissionController::store()` is 120 lines mixing validation, n8n/ClickUp handoff, and status logic. Extract the handoff+status advance into a `SubmissionDispatcher` service; controller keeps validate → dispatch → respond.
