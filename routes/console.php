<?php

use App\Application\Tenancy\ProvisionTenantDatabase;
use App\Models\Shop;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tenants:provision-database {shopSlug?}', function (?string $shopSlug = null): int {
    $shops = Shop::query()
        ->when($shopSlug, fn ($query) => $query->where('slug', $shopSlug))
        ->get();

    if ($shops->isEmpty()) {
        $this->error('No matching shops found.');

        return self::FAILURE;
    }

    foreach ($shops as $shop) {
        app(ProvisionTenantDatabase::class)->handle($shop);
        $this->info("Tenant database ready for {$shop->slug}: {$shop->fresh()->database}");
    }

    return self::SUCCESS;
})->purpose('Create PostgreSQL databases for shops and migrate each tenant database');
