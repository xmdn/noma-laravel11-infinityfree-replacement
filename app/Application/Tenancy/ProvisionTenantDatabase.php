<?php

namespace App\Application\Tenancy;

use App\Models\Shop;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

final readonly class ProvisionTenantDatabase
{
    public function __construct(private CopyShopDataToTenantDatabase $copyShopData) {}

    public function handle(Shop $shop): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        if (DB::connection('landlord')->getDriverName() !== 'pgsql') {
            return;
        }

        $database = $shop->database ?: 'tenant_'.str_replace('-', '_', $shop->slug);

        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $database)) {
            throw new \InvalidArgumentException("Unsafe tenant database name for {$shop->slug}: {$database}");
        }

        $exists = DB::connection('landlord')
            ->selectOne('select 1 from pg_database where datname = ?', [$database]);

        if (! $exists) {
            DB::connection('landlord')->statement('CREATE DATABASE "'.str_replace('"', '""', $database).'"');
        }

        $shop->forceFill(['database' => $database])->save();
        $shop->makeCurrent();

        try {
            Artisan::call('migrate', ['--database' => 'tenant', '--force' => true]);
            $this->copyShopData->handle($shop);
        } finally {
            Shop::forgetCurrent();
        }
    }
}
