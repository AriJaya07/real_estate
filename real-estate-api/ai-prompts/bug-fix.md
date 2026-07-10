# Prompt Template — Bug Fix

## Context to provide the agent

- Exact error message / wrong behavior, and the expected behavior.
- Reproduction steps (request + payload + auth state), environment (local / production container).
- Relevant log excerpts (`storage/logs/laravel.log`, `docker compose logs app`).

## Prompt skeleton

```
Read agent-rules.md first. Check docs/tech-debt.md — the "bug" may be a documented deliberate quirk.

Bug: <one-line summary>

Observed:
<exact error output / wrong response, quoted verbatim>

Expected:
<correct behavior>

Repro:
1. <step>
2. <step>

Task:
1. Reproduce first (test or curl) — confirm you see the same failure before changing code.
2. Find root cause; explain it in one paragraph before fixing.
3. Fix with the smallest change that addresses the cause (not the symptom).
4. Add a regression test that fails without the fix and passes with it.
5. Run php artisan test --compact and vendor/bin/pint --dirty.
```

## Expected output

Root-cause explanation → minimal diff → regression test → test-run output. If the root cause is a documented quirk or upstream issue, report that instead of patching around it.

## Example

> Bug: publishing a submission twice creates two Property rows.
> Observed: second `POST /property-submissions/5/publish` returns 200 and inserts a duplicate.
> Expected: 409, one Property.
