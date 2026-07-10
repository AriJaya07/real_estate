# Prompt Template — Documentation

## Context to provide the agent

- What changed or what is undocumented (feature, endpoint, ops procedure, decision).
- Which doc owns it (see map below) — update existing docs, don't create parallel ones.

## Doc ownership map

| Content | Lives in |
|---|---|
| System design, components, data flow | `docs/architecture.md` |
| Endpoints, auth, response shapes | `docs/api-patterns.md` |
| Terms, entities, business rules | `docs/domain.md` |
| Deploy/update/ops on the VPS | `docs/deployment.md` |
| Env vars, local setup, third parties | `docs/environment.md` |
| Requirements, user stories | `docs/prd.md` |
| Known debt, quirks, workarounds | `docs/tech-debt.md` |
| Test conventions | `docs/testing-strategy.md` |
| Agent constraints | `agent-rules.md` |

## Prompt skeleton

```
Update project docs for: <change/topic>

Rules:
- Verify against the actual code/config before writing — docs must describe what IS,
  not what was planned. Quote real route paths, env var names, status codes.
- Update the owning doc per the map in ai-prompts/documentation.md; cross-link instead
  of duplicating content.
- Placeholders for all secrets (<CLICKUP_API_TOKEN>) — never real values.
- Match the existing tone: terse, tables for enumerable facts, prose for reasoning.
- If code and docs disagree, flag the discrepancy instead of silently picking one.
```

## Expected output

Doc diffs only (no new top-level files unless the map has no owner), plus a one-line summary per file of what changed.

## Example

> Update docs for the new archive-submission endpoint: add the route to api-patterns.md's endpoint map, add "archived" to domain.md's glossary + business rules, note the new `archived_at` column.
