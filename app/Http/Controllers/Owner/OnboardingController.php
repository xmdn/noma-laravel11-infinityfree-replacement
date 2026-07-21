<?php

namespace App\Http\Controllers\Owner;

use App\Application\Tenancy\ProvisionOwnerShop;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OnboardingController extends Controller
{
    public function retry(Request $request, ProvisionOwnerShop $provisionOwnerShop): RedirectResponse
    {
        $shop = $provisionOwnerShop->handle($request->user());

        return redirect()->route('dashboard')->with(
            'status',
            $shop ? 'Your shop is ready.' : 'No shop onboarding is associated with this account.',
        );
    }
}
