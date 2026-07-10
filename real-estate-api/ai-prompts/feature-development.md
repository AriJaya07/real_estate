# Prompt Template — Feature Development

## Context to provide the agent

- The user story / acceptance criteria (link `docs/prd.md` section if it exists there).
- Relevant docs: `docs/architecture.md`, `docs/domain.md`, `docs/api-patterns.md`, `agent-rules.md`.
- Any affected endpoints, models, or pipeline statuses.

## Prompt skeleton

```
Read agent-rules.md, docs/architecture.md and docs/domain.md first.

Feature: <one-line summary>

User story:
<as a ..., I want ..., so that ...>

Acceptance criteria:
- <criterion 1>
- <criterion 2>

Constraints:
- Follow docs/api-patterns.md for the endpoint shape and status codes.
- Respect the pipeline/status guards in docs/domain.md.
- Land feature tests (Pest) for the happy path and each guard failure, per docs/testing-strategy.md.
- Run vendor/bin/pint --dirty before finishing.
- Update docs/api-patterns.md (and domain.md if entities/rules changed).

Deliverable: working code + tests passing (php artisan test --compact) + doc updates.
```

## Expected output from the agent

1. Short plan (files to touch) before coding.
2. Migration (if schema changes) via `php artisan make:migration`.
3. Route + controller + service changes.
4. Feature tests covering acceptance criteria and guards.
5. Doc diffs.

## Example

> Feature: allow users to archive a rejected submission.
> Acceptance: owner-only; only status `rejected`; archived submissions excluded from the default index but included with `?archived=1`; 403 for non-owners, 409 for wrong status. Tests for all four.
