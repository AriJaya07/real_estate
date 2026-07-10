# Prompt Template — Documentation (Frontend)

## Context to provide the agent

- What changed or is undocumented.
- Which doc owns it (map below) — update existing docs, never create parallel ones.

## Doc ownership map

| Content | Lives in |
|---|---|
| App structure, layers, data flow | `docs/architecture.md` |
| How this app consumes the API, error handling | `docs/api-patterns.md` |
| UI terms, invariants, status meta | `docs/domain.md` |
| Screens, UX rules, acceptance criteria | `docs/prd.md` |
| Setup, env vars, build/deploy commands | `docs/environment.md` |
| Debt, deliberate quirks | `docs/tech-debt.md` |
| Test conventions | `docs/testing-strategy.md` |
| Agent constraints | `agent-rules.md` |
| API contract itself | backend repo: `../real-estate-api/docs/api-patterns.md` — do not duplicate here |

## Prompt skeleton

```
Update project docs for: <change/topic>

Rules:
- Verify against actual code/config before writing (real file paths, composable names,
  env var names) — docs describe what IS.
- Update the owning doc per the map; cross-link instead of duplicating. API contract
  details belong in the backend repo's docs.
- No secrets/tokens in any doc.
- Match existing tone: terse, tables for enumerable facts, prose for reasoning.
- If code and docs disagree, flag the discrepancy — don't silently pick one.
```

## Expected output

Doc diffs only + one-line summary per file of what changed.

## Example

> Document the new archived-submissions toggle: prd.md (submissions screen bullet), domain.md (term "archived"), api-patterns.md (consumption of `?archived=1`).
