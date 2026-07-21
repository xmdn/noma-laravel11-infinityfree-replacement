<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Symfony\Component\HttpFoundation\Response;

final class IdentifyShopTenantIfPresent
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantFinder = app(config('multitenancy.tenant_finder'));
        $tenant = $tenantFinder->findForRequest($request);

        if ($tenant instanceof IsTenant && (! Shop::checkCurrent() || Shop::current()?->getKey() !== $tenant->getKey())) {
            $tenant->makeCurrent();
        }

        if (! $tenant instanceof IsTenant && Shop::checkCurrent()) {
            Shop::forgetCurrent();
        }

        return $next($request);
    }
}
