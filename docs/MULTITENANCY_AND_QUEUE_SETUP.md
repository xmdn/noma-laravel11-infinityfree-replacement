# Multitenancy and Queue Setup

## Local tenant subdomains

Tenant storefronts use subdomains:

- Platform: `http://localhost:8083`
- Tenant: `http://{shop-slug}.localhost:8083`

If Windows does not resolve wildcard localhost subdomains, add explicit hosts entries for local shops:

```text
127.0.0.1 exmpl.localhost
```

## Tenant databases

Spatie Laravel Multitenancy switches the `tenant` database connection based on the current shop. The application is responsible for creating PostgreSQL databases.

Provision one shop:

```powershell
php artisan tenants:provision-database exmpl
```

Provision all shops:

```powershell
php artisan tenants:provision-database
```

The tenant database name is stored on `shops.database`, for example `tenant_exmpl`.

## RabbitMQ

Start RabbitMQ with the management UI:

```powershell
docker compose up -d rabbitmq mailpit queue-worker
```

Management UI:

```text
http://localhost:15672
```

Laravel now uses the RabbitMQ queue driver for queued mail and other async jobs.

Mail delivery for local development goes through Mailpit:

```text
http://localhost:8025
```

The `queue-worker` service consumes the `emails` queue from RabbitMQ and delivers messages to Mailpit automatically. If you only start `rabbitmq`, jobs will accumulate in RabbitMQ but nothing will appear in Mailpit until a consumer is running.
