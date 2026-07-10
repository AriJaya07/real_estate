# Local Development & Verification

How to run and manually verify the frontend on a local machine.

## Run the dev server

Point the app at a locally running API (see `real-estate-api/docs/local-development.md`):

```bash
NUXT_PUBLIC_API_BASE=http://127.0.0.1:8000/api npm run dev -- --port 3000
```

Without the override, the API base comes from `nuxt.config.ts` runtime config / `.env`.

## Checks before committing

```bash
npx vue-tsc --noEmit    # typecheck (TypeScript is pinned to 5.9 — do not upgrade; TS 6 breaks vue-tsc)
```

## Manual verification flows

- Register from the homepage — should stay on `/` logged in, navbar gains **Dashboard**.
- Open a listing while logged in — form empty when never submitted; pre-filled (and locked while in review) when a submission exists.
- Submissions page — **Sync ClickUp** button pulls latest review status (sync is manual by design; page load also syncs once).
- Publish a **Ready** submission — row gains **View listing**; status shows "Live on the website".
- Paste a non-image URL in the property form — preview shows a warning and the listing falls back to a placeholder.
