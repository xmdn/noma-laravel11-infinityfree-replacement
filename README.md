# NOMA Storefront

A production-minded Laravel 13 + Livewire 4 commerce experience. The customer-facing application is a curated design store; SOLID and design patterns live in the implementation rather than the content.

## Architecture

- `Domain` — immutable products and money, cart/catalog ports, promotion policies.
- `Application` — catalog browsing, cart mutation, and cart summary use cases.
- `Infrastructure` — curated catalog and session-backed cart adapters.
- `Livewire` — reactive storefront presentation boundary.

Key decisions include integer minor-unit money, repository ports, a promotion strategy, a justified Template Method base class for conditional promotions, server-side cart authority, focused application services, PHP enums, and container-based composition.

## Run

```bash
composer install
npm install
npm run build
php artisan serve
```

## Quality

```bash
php artisan test
./vendor/bin/pint --test
npm run build
```

## Free hosting

The storefront is prepared for InfinityFree's no-card PHP 8.3 hosting. Composer dependencies and Vite assets are built locally, while file sessions, synchronous queues, and a root rewrite rule accommodate shared-hosting restrictions. See `DEPLOYMENT.md`.
