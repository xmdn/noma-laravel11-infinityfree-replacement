<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class TenantSessionBridgeController extends Controller
{
    public function __invoke(Request $request, string $shop, User $user): RedirectResponse
    {
        $tenant = Shop::current();

        abort_unless($tenant instanceof Shop && $tenant->slug === $shop, 404);
        abort_unless((string) $user->shop_id === (string) $tenant->id || (string) $tenant->owner_id === (string) $user->id, 403);

        Auth::login($user);
        $request->session()->regenerate();

        $redirect = (string) $request->query('redirect', '/dashboard');

        if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/dashboard';
        }

        return redirect()->to($redirect);
    }
}
