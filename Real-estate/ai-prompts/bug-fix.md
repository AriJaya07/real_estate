# Prompt Template — Bug Fix (Frontend)

## Context to provide the agent

- Wrong behavior vs expected, exact console/network errors (quote verbatim).
- Repro steps: route, auth state (which demo user), actions clicked.
- Whether the API response is correct (check the network tab first — frontend bug or backend bug?).

## Prompt skeleton

```
Read agent-rules.md first. Check docs/tech-debt.md — the "bug" may be a documented quirk
(TS 5.9 pin, SPA-only behavior, polling sync, hardcoded API base default).

Bug: <one-line summary>

Observed:
<exact behavior + console/network errors, verbatim>

Expected:
<correct behavior>

Repro:
1. <route + auth state>
2. <action>

Task:
1. Reproduce first; confirm whether the API response is correct (network tab) —
   if the API is wrong, stop and report it as a backend issue instead of patching the UI.
2. Find root cause; explain in one paragraph before fixing.
3. Smallest fix at the right layer (service / store / component — not a template patch
   over a store bug).
4. Verify the fix in the running app AND with npx vue-tsc --noEmit.
```

## Expected output

Root-cause explanation → minimal diff → how it was verified (steps + vue-tsc). Backend-fault findings reported, not worked around.

## Example

> Bug: Publish button visible on rejected rows.
> Observed: row status `rejected` renders Publish; clicking gives 409 toast.
> Expected: Publish only on `ready` rows (docs/domain.md UI invariant 2).
