<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\SystemRole;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

final class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], (bool) ($credentials['remember'] ?? false))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user()->load(['roles', 'shop']);
        $tenant = Shop::current();

        if ($tenant instanceof Shop && ! $this->userBelongsToTenant($user, $tenant) && ! $user->hasRole(SystemRole::Administrator->value)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        if (! $tenant instanceof Shop && $this->shouldEnterShopAfterLogin($user)) {
            return redirect()->away($this->tenantBridgeUrl($user));
        }

        return redirect()->intended(url('/dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $redirectTo = Shop::checkCurrent() ? url('/') : route('home');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($redirectTo);
    }

    private function shouldEnterShopAfterLogin(User $user): bool
    {
        return $user->shop instanceof Shop
            && ! $user->hasRole(SystemRole::Administrator->value);
    }

    private function userBelongsToTenant(User $user, Shop $tenant): bool
    {
        return (string) $user->shop_id === (string) $tenant->id
            || (string) $tenant->owner_id === (string) $user->id;
    }

    private function tenantBridgeUrl(User $user): string
    {
        /** @var Shop $shop */
        $shop = $user->shop;

        return URL::temporarySignedRoute('shops.auth.bridge', now()->addMinutes(2), [
            'shop' => $shop->slug,
            'user' => $user,
            'redirect' => '/dashboard',
        ]);
    }
}
