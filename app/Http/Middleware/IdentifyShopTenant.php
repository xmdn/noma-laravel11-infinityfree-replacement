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
            $tenantFinder = app(config('multitenancy.tenant_finder'));
            $tenant = $tenantFinder->findForRequest($request);

            if ($tenant instanceof IsTenant) {
                $tenant->makeCurrent();
            }
        }

        abort_unless(Shop::checkCurrent(), 404);

        return $next($request);
    }
}
