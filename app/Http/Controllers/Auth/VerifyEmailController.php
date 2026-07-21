<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\SystemRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

final class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->load(['roles', 'shop']);

        if ($user->shop && ! $user->hasRole(SystemRole::Administrator->value)) {
            return redirect()->away(URL::temporarySignedRoute('shops.auth.bridge', now()->addMinutes(2), [
                'shop' => $user->shop->slug,
                'user' => $user,
                'redirect' => '/dashboard',
            ]));
        }

        return redirect()->route('dashboard')->with('status', 'Email verified. Your shop is ready.');
    }
}
