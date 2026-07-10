# Local Development & Verification

How to run and manually verify the API on a local machine.

## Environment gotcha: `.env` points at production

The committed `.env` targets the docker-compose deployment (`DB_HOST=db`, prod `APP_URL`). **Do not edit it for local runs.** Override per command instead:

```bash
DB_HOST=127.0.0.1 DB_USERNAME=arijaya DB_PASSWORD= php artisan migrate
DB_HOST=127.0.0.1 DB_USERNAME=arijaya DB_PASSWORD= APP_URL=http://127.0.0.1:8000 \
  php artisan serve --host=127.0.0.1 --port=8000
```

Local Postgres: database `real_estate`, user `arijaya`, no password.

## Tests

Tests run on in-memory SQLite (configured in `phpunit.xml`) — no overrides needed:

```bash
php artisan test --compact
vendor/bin/pint --dirty        # format before committing
```

## Live integration warning

`.env` contains a **real ClickUp token and n8n webhook URL**:

- Creating a submission with `status: "pending"` creates a **real ClickUp task**.
- `POST /api/property-submissions/sync-clickup` and `php artisan clickup:sync` update the local database from real ClickUp state.

For manual verification use `status: "draft"` and clean up created rows afterwards.

## Driving the API by hand

```bash
# Register — returns { user, token } with HTTP 201 (auto-login)
curl -s -X POST http://127.0.0.1:8000/api/register \
  -H 'Content-Type: application/json' \
  -d '{"username":"u1","password":"password123"}'

# Authenticated requests
curl -s http://127.0.0.1:8000/api/me -H "Authorization: Bearer <token>"
```

Demo login: `admin` / `password`. Flows worth exercising: register → me, property CRUD, `PATCH /api/properties/{id}/publish`, draft submission, `GET /api/property-submissions?related_property_id={id}`.
