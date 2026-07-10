# Prompt Template — Code Review

## Context to provide the agent

- The diff / PR branch to review.
- `agent-rules.md`, `docs/coding-standards.md`, `docs/api-patterns.md`, `docs/domain.md`.

## Prompt skeleton

```
Read agent-rules.md and docs/coding-standards.md, then review this diff/PR: <ref>

Check, in priority order:
1. Correctness — bugs, broken status guards (docs/domain.md rules 1–7), missing ownership checks, 409/403 paths.
2. Security — secrets in code, webhook secret handling, mass assignment, auth bypass.
3. Architecture invariants — publishing outside SubmissionPublisher, ClickUp calls outside ClickUpService, hard-fail on integration errors.
4. Tests — do new endpoints/rules ship with feature tests for happy path + guard failures?
5. Style — Pint-clean, thin controllers, conventions in docs/coding-standards.md.

Report format, one line per finding:
<file:line> — <problem> — <suggested fix>
Order by severity. Say "no findings" for clean areas; do not pad.
Verify each finding against the actual code before reporting (no speculative issues).
```

## Expected output

Ranked findings list, each with location, concrete problem, and fix. A finding must describe a real failure scenario, not a hypothetical preference.

## Example finding

> `app/Http/Controllers/Api/PropertySubmissionController.php:84` — update() checks status but not ownership; any authed user can edit another user's rejected submission — add `$this->authorize()`/owner check before the status guard, plus a 403 feature test.
