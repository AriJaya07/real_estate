# Prompt Template — Feature Development (Frontend)

## Context to provide the agent

- User story / acceptance criteria (link `docs/prd.md`).
- `agent-rules.md`, `docs/architecture.md`, `docs/domain.md`.
- If the feature needs new API calls: the backend endpoint spec (`../real-estate-api/docs/api-patterns.md`).

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
- All HTTP via app/services/api.ts; server state via the owning Pinia store.
- Status meta from useSubmissionStatus; toasts via useToast; reuse the UI kit.
- Type new API shapes in app/types/index.ts.
- Handle 401/403/409/422 per docs/api-patterns.md.
- Finish with a clean `npx vue-tsc --noEmit`.
- Update docs/api-patterns.md (consumption) and docs/domain.md (new terms) if applicable.

Deliverable: working UI + type check passing + doc updates.
```

## Expected output

1. Short plan first: pages/components/store/service methods to touch.
2. Types → service method → store action → page/component wiring, in that order.
3. Guard rendering consistent with backend rules (no buttons that guarantee 403/409).
4. `vue-tsc` output.

## Example

> Feature: archived-submissions view. Toggle on the submissions page showing archived rows (`?archived=1`); archive action on rejected rows with confirm modal; toast on result; stepper/badges unchanged.
