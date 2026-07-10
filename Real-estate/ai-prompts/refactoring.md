# Prompt Template — Refactoring (Frontend)

## Context to provide the agent

- The smell and motivation (duplicated template logic, fat page component, store doing UI work…).
- Scope boundary: files in and out of scope.
- `docs/tech-debt.md` — the smell may be deliberate (module-level toast state, SPA-only patterns, hardcoded API base default).

## Prompt skeleton

```
Read agent-rules.md and docs/tech-debt.md first — do not "fix" documented deliberate
quirks (TS 5.9 pin, ssr:false, useToast singleton, URL-string images).

Refactor: <what and why, one line>

Scope: <files in scope>. Out of scope: <excluded>.

Rules:
- Behavior-preserving: no visible UI change, no API-call changes, no store contract changes
  outside scope.
- Respect layer boundaries: HTTP in services/api.ts, server state in stores, UI state in
  components; shared logic into composables, repeated markup into components.
- No new dependencies.
- Gate: npx vue-tsc --noEmit clean before AND after; manually verify the affected screens
  in the running app (npm run dev) since no test suite exists.
- Small reviewable commits, one concern each.
```

## Expected output

1. Before/after sketch (which layer owns what).
2. The refactor in small commits.
3. Verification note: vue-tsc output + which screens were manually exercised.

## Example

> Refactor: submissions page is 400+ lines mixing table, filters, three modals, and export logic. Extract `SubmissionTable`, `SubmissionFilters`, `SubmissionDetailsModal` components; page keeps store wiring only. No visual change.
