# Prompt Template — Code Review (Frontend)

## Context to provide the agent

- The diff / PR branch.
- `agent-rules.md`, `docs/coding-standards.md`, `docs/domain.md` (UI invariants), `docs/api-patterns.md`.

## Prompt skeleton

```
Read agent-rules.md and docs/coding-standards.md, then review this diff/PR: <ref>

Check, in priority order:
1. Correctness — broken reactivity, race conditions on async store actions, wrong
   pagination/filter state, blob-download handling.
2. Invariant violations — axios outside services/api.ts, hardcoded status strings,
   direct store mutation from components, guard buttons that guarantee 403/409,
   secrets or tokens in code.
3. Error handling — 401/403/409/422 handled per docs/api-patterns.md; every mutation
   toasts; no raw error JSON shown to users.
4. Types — new API shapes in app/types/index.ts, no `any` creep, vue-tsc clean.
5. Style — auto-imports (no manual component imports), UI-kit reuse, Tailwind over
   scoped CSS, conventions in docs/coding-standards.md.

Report format, one line per finding:
<file:line> — <problem> — <suggested fix>
Order by severity. Verify each finding against the actual code before reporting.
```

## Expected output

Ranked findings with location, concrete problem, fix. No speculative or preference-only findings; "no findings" is a valid result.

## Example finding

> `app/pages/dashboard/submissions.vue:112` — status badge color computed from a hardcoded map duplicating useSubmissionStatus — colors will drift from the stepper; read meta from useSubmissionStatus() instead.
