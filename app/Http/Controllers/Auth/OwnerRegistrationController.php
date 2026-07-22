<?php

namespace App\Http\Controllers\Auth;

use App\Application\Tenancy\RegisterOwner;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

final class OwnerRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-owner');
    }

    public function store(Request $request, RegisterOwner $registerOwner): RedirectResponse
    {
        $request->merge(['shop_slug' => Str::slug((string) $request->input('shop_slug'))]);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'shop_name' => ['required', 'string', 'max:120'],
            'shop_slug' => ['required', 'string', 'min:3', 'max:80', 'alpha_dash:ascii', 'unique:shop_onboardings,shop_slug', 'unique:shops,slug'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $registerOwner->handle($attributes);
        Auth::login($user);
        $request->session()->regenerate();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('verification.notice');
    }
}
