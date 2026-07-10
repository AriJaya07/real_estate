# Tech Debt — Frontend

Check here before "fixing" odd-looking things — some are deliberate.

## High

1. **No tests at all.** No Vitest/@nuxt/test-utils, no component or e2e tests. Only gate is `vue-tsc` type checking. See `testing-strategy.md` for the plan.
2. **Not deployed.** Only the API is on the Contabo server; the frontend runs locally. When deploying: build with `NUXT_PUBLIC_API_BASE=http://62.171.156.55:8081/api`, and the API's CORS (`FRONTEND_URL`) must be updated. Options: static hosting (`nuxt generate`) or a container next to the API.
3. **API over plain HTTP** once deployed (`:8081`, no TLS) — tokens visible on the wire. Blocked on backend TLS work (see backend `tech-debt.md`).

## Medium

4. **TypeScript pinned to 5.9** — TS 6 removed the `./lib/tsc` export that vue-tsc needs. Do not bump until vue-tsc supports TS 6. `package.json` pins exact `"5.9"`.
5. **Token storage & 401 handling** — auth persistence is client-side (SPA); a hard 401 mid-session must cleanly reset the store and redirect (verify interceptor coverage before extending auth features).
6. **No role-based UI** — every authenticated user sees the whole dashboard including Users. Mirrors missing backend roles; add together with the backend role layer.
7. **ClickUp state via polling** — submissions page syncs on load + manual button; no live updates. Backend may move to real webhooks (server is public now); frontend would still need polling or SSE/websockets for live badge updates.

## Low / deliberate

8. **SPA only (`ssr: false`)** — deliberate: internal tool, token auth, no SEO need. Don't enable SSR casually; code assumes browser-only (auth-init plugin, blob downloads).
9. **API base default hardcoded** in `nuxt.config.ts` (`http://localhost:8000/api`) — convenient for dev; env override exists. Fine, but remember it when builds mysteriously hit localhost.
10. **Images are URL strings** — no upload UI by design (backend has no upload endpoint).
11. **Module-level toast state** — `useToast` uses module singleton, not Pinia. Deliberate simplicity; don't migrate without reason.
