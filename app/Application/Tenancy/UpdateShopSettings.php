<?php

namespace App\Application\Tenancy;

use App\Models\Shop;

final class UpdateShopSettings
{
    /** @param array{name: string, slug: string, domain: string, database: string, currency: string, timezone: string} $attributes */
    public function handle(Shop $shop, array $attributes): Shop
    {
        $shop->update($attributes);

        return $shop->refresh();
    }
}
