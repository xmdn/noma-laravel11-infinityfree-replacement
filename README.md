# NOMA Storefront

A production-minded Laravel 11.51 + Livewire 4.3 commerce experience. The customer-facing application is a curated design store; SOLID and design patterns live in the implementation rather than the content.

Static GitHub Pages preview: https://xmdn.github.io/noma-laravel11-infinityfree-replacement/

## Architecture

- `Domain` — immutable products and money, cart/catalog ports, promotion policies.
- `Application` — catalog browsing, cart mutation, and cart summary use cases.
- `Infrastructure` — curated catalog and session-backed cart adapters.
- `Livewire` — reactive storefront presentation boundary.

Key decisions include integer minor-unit money, repository ports, a promotion strategy, a justified Template Method base class for conditional promotions, server-side cart authority, focused application services, PHP enums, and container-based composition.

The identity foundation includes customer registration and login, tenant roles and permissions, a protected account area, and administrator-controlled role assignment. See `PROJECT_SCOPE.md` for the multi-tenant direction.

## Run

```bash
composer install
npm install
npm run build
php artisan serve
```

Run migrations and seed system roles before accepting registrations:

```bash
php artisan migrate --force
php artisan db:seed --force
```

## Docker / Codespaces

The full Laravel application can be previewed from GitHub Codespaces or any Docker host:

```bash
cp .env.example .env
# Edit APP_*, DB_*, LANDLORD_DB_*, TENANT_DB_*, RABBITMQ_*, or exposed NOMA_* ports if needed.
docker compose up --build
```

With `APP_ENV=local` or `APP_ENV=development`, registration emails are auto-verified by default so Codespaces demos do not require opening Mailpit. Set `NOMA_AUTO_VERIFY_EMAILS=false` in `.env` to test the real verification email flow.

Codespaces cannot serve arbitrary shop subdomains with a trusted certificate, so `NOMA_SHOP_URL_MODE=auto` uses path-based storefront URLs there, such as `/shops/example-shop`. Normal local and production environments keep subdomain storefront URLs.

Application:

```text
http://localhost:8083
```

Mailpit:

```text
http://localhost:8025
```

RabbitMQ management:

```text
http://localhost:15672
```

pgAdmin:

```text
http://localhost:5050
```

The Compose stack includes the Laravel app, PostgreSQL, RabbitMQ, Mailpit, pgAdmin, an nginx TLS reverse proxy, a Certbot renewal sidecar, a Vite asset build container, and the queue worker. Local service ports bind to `127.0.0.1` by default; nginx is the public entrypoint on ports 80 and 443. If an older `postgres-data` volume was created before the landlord database was added, recreate the database volume:

```bash
docker compose down -v
docker compose up --build
```

For local queue-backed email delivery, start RabbitMQ, Mailpit, and the worker:

```bash
docker compose up -d rabbitmq mailpit queue-worker
```

Mailpit inbox:

```text
http://localhost:8025
```

To create or update the initial tenant administrator, set `NOMA_ADMIN_NAME`, `NOMA_ADMIN_EMAIL`, and `NOMA_ADMIN_PASSWORD` in the deployment environment before seeding. Do not commit those values.

## nginx / Certbot

For `emitapi.us.to`, point these DNS records at the Docker host before issuing certificates:

```text
emitapi.us.to
rabbitmq.emitapi.us.to
mailpit.emitapi.us.to
db.emitapi.us.to
```

Set production values in `.env` before going public:

```dotenv
APP_URL=https://emitapi.us.to
CERTBOT_EMAIL=admin@emitapi.us.to
NOMA_PROXY_BASIC_AUTH_USER=noma-admin
NOMA_PROXY_BASIC_AUTH_PASSWORD=replace-with-a-strong-password
PGADMIN_DEFAULT_EMAIL=admin@emitapi.us.to
PGADMIN_DEFAULT_PASSWORD=replace-with-a-strong-password
```

Start nginx on HTTP first so Let's Encrypt can complete the webroot challenge:

```bash
docker compose up -d --build nginx certbot
```

Issue the first certificate:

```bash
docker compose run --rm --entrypoint certbot certbot certonly --webroot --webroot-path /var/www/certbot --email admin@emitapi.us.to --agree-tos --no-eff-email --cert-name emitapi.us.to -d emitapi.us.to -d rabbitmq.emitapi.us.to -d mailpit.emitapi.us.to -d db.emitapi.us.to
docker compose restart nginx
```

After nginx restarts, the public endpoints are:

```text
https://emitapi.us.to
https://rabbitmq.emitapi.us.to
https://mailpit.emitapi.us.to
https://db.emitapi.us.to
```

RabbitMQ, Mailpit, and pgAdmin are additionally protected by nginx basic auth. pgAdmin connects to PostgreSQL with host `postgres`, port `5432`, and the `DB_USERNAME` / `DB_PASSWORD` values from `.env`.

## Quality

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

## Free hosting

The storefront is prepared for InfinityFree's no-card PHP 8.3 hosting. Composer dependencies and Vite assets are built locally, while file sessions, synchronous queues, and a root rewrite rule accommodate shared-hosting restrictions. See `DEPLOYMENT.md`.

## GitHub Pages preview

GitHub Pages can host only the static preview in `docs/`; it cannot execute the Laravel application, Livewire requests, migrations, authentication, sessions, queues, or database-backed storefront routes.

This repository includes a GitHub Actions workflow at `.github/workflows/pages.yml`. After pushing it to GitHub, enable Pages with **Source: GitHub Actions** in the repository settings. The preview will deploy to:

```text
https://xmdn.github.io/noma-laravel11-infinityfree-replacement/
```
