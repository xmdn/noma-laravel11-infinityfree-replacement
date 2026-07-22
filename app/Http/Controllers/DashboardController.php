<?php

namespace App\Http\Controllers;

use App\Services\DashboardViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardViewFactory $views): View
    {
        $user = $request->user()->loadMissing(['roles.permissions', 'shop', 'shopOnboarding']);

        return $views->makeForUser(
            $user,
            'index',
            [
                'user' => $user,
            ],
        );
    }
}
