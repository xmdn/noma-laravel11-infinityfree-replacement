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

For local queue-backed email delivery, start RabbitMQ, Mailpit, and the worker:

```bash
docker compose up -d rabbitmq mailpit queue-worker
```

Mailpit inbox:

```text
http://localhost:8025
```

To create or update the initial tenant administrator, set `NOMA_ADMIN_NAME`, `NOMA_ADMIN_EMAIL`, and `NOMA_ADMIN_PASSWORD` in the deployment environment before seeding. Do not commit those values.

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
