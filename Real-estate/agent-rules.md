# Agent Rules — real-estate (frontend)

Rules for AI agents in this repo. Read `docs/` first: `architecture.md`, `domain.md`, and `tech-debt.md` (several "smells" are deliberate). Backend contract: `../real-estate-api/docs/api-patterns.md`.

## Hard constraints

1. **Do not bump TypeScript past 5.9** — TS 6 breaks vue-tsc. The pin is exact and intentional.
2. **No dependency changes without approval** (including adding a test framework — planned but not decided; see `docs/testing-strategy.md`).
3. **Never commit `.env`** or put tokens/secrets anywhere in this repo — all secrets are backend-side.
4. **Don't enable SSR** or use server-only Nuxt features — app is `ssr: false` by design; code assumes browser.
5. **Don't change the API contract from this side** — endpoint shapes are owned by the backend repo; mismatches are backend discussions, not frontend workarounds.

## Architecture invariants (keep these true)

- All HTTP through `app/services/api.ts` — no axios imports in components/pages.
- Server state in Pinia stores (`auth`, `property`, `submission`); components dispatch actions, never mutate store state directly.
- Submission status labels/colors/order come only from `composables/useSubmissionStatus.ts` — never hardcode status strings.
- Notifications only via `useToast()`.
- Guard rendering mirrors backend rules: Edit/Delete only `draft`/`rejected`, Publish only `ready`.
- Money: `CurrencyInput`, display formatted / transmit raw number.
- Auth is username-based Bearer tokens — no email fields, no cookie/session auth.
- 401 → reset auth + redirect login; 409 → toast + refresh, it's a normal outcome; 422 → map onto form fields.

## Working preferences

- `<script setup lang="ts">`, Composition API, Nuxt auto-imports (no manual component/composable imports).
- Reuse the UI kit (`PrimaryButton`, `Input`, `Modal`, `EmptyState`, `Loading`, …) before creating new base components.
- Tailwind utilities; no scoped CSS or inline styles without strong reason.
- Types for API shapes in `app/types/index.ts`.
- Run `npx vue-tsc --noEmit` after TS/Vue changes — it is the only automated gate.
- New/changed API consumption: update `docs/api-patterns.md`; new terms/UI rules: `docs/domain.md`.

## Prompt templates

Reusable task templates in `/ai-prompts/` — use the matching checklist for features, bug fixes, reviews, refactors, tests, docs.
