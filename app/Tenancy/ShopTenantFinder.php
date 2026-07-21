<?php

namespace App\Tenancy;

use App\Models\Shop;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

final class ShopTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = strtolower($request->getHost());
        $centralHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost');

        if ($host === $centralHost || str_starts_with($host, 'www.')) {
            return null;
        }

        $slug = str($host)->before('.'.$centralHost)->toString();

        return Shop::query()
            ->where('is_accessible', true)
            ->where(function ($query) use ($host, $slug): void {
                $query->where('domain', $host)
                    ->orWhere('slug', $slug);
            })
            ->first();
    }
}
