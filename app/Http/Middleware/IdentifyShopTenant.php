<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Symfony\Component\HttpFoundation\Response;

final class IdentifyShopTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Shop::checkCurrent()) {
            $tenant = $this->shopFromRoute($request);

            if (! $tenant instanceof IsTenant) {
                $tenantFinder = app(config('multitenancy.tenant_finder'));
                $tenant = $tenantFinder->findForRequest($request);
            }

            if ($tenant instanceof IsTenant) {
                $tenant->makeCurrent();
            }
        }

        abort_unless(Shop::checkCurrent(), 404);

        return $next($request);
    }

    private function shopFromRoute(Request $request): ?Shop
    {
        $shop = $request->route('shop');

        if ($shop instanceof Shop) {
            return $shop->is_accessible ? $shop : null;
        }

        if (! is_string($shop) || $shop === '') {
            return null;
        }

        return Shop::query()
            ->where('slug', $shop)
            ->where('is_accessible', true)
            ->first();
    }
}
