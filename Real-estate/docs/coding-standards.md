# Coding Standards — Frontend

Nuxt 4 / Vue 3 `<script setup lang="ts">` / TypeScript / Tailwind v4 / Pinia.

## TypeScript

- **TS pinned to 5.9** — do not bump to 6 (breaks vue-tsc; see `tech-debt.md`).
- Shared API-shape types live in `app/types/index.ts` — extend there, don't redeclare interfaces in components.
- Type check: `npx vue-tsc --noEmit`.

## Vue / Nuxt conventions

- `<script setup lang="ts">` everywhere; Composition API only, no Options API.
- Components: PascalCase single-word-safe names in `app/components/`, auto-imported — no manual import statements for components/composables (Nuxt auto-import).
- Pages own routing concerns (middleware, layout via `definePageMeta`); components stay route-agnostic.
- Props typed with `defineProps<{...}>()`; emits with `defineEmits<{...}>()`.

## State & data rules

- **All HTTP through `app/services/api.ts`** — never import axios in a component or page.
- **Server state lives in Pinia stores** (`auth`, `property`, `submission`); components read store state, dispatch actions. Local UI state (modals open, form drafts) stays in components.
- **Never hardcode submission status strings/colors** — use `useSubmissionStatus()`.
- Notifications only via `useToast()` — no ad-hoc alert/banner components.
- Formatting helpers via `useFormat()`; money input via `CurrencyInput` (stores raw number).

## Styling

- Tailwind v4 utilities (configured through `@tailwindcss/vite`, entry `app/assets/css/main.css`). No scoped CSS unless a utility genuinely can't express it; no inline `style=`.
- Reuse the existing UI kit (`PrimaryButton`, `SecondaryButton`, `Input`, `Select`, `Textarea`, `Modal`, `EmptyState`, `Loading`, `Switch`) before creating new base components.

## Patterns to avoid

- No SSR-only APIs (`useAsyncData` server options, nitro routes) — app is `ssr: false`; guard any `window`/`localStorage` use for app-boot timing instead.
- No direct mutation of store state from components — go through actions.
- No new date/number formatting one-offs — extend `useFormat`.
- Don't duplicate API response shapes inline — `app/types/index.ts`.

## Git

Small focused commits, imperative subject ≤50 chars. Never commit `.env`.
