<?php

namespace App\Http\Controllers;

use App\Services\DashboardViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    // public function __invoke(Request $request): View
    // {
    //     return view('dashboard', [
    //         'user' => $request->user()->load(['roles', 'shop', 'shopOnboarding']),
    //     ]);
    // }
    public function __invoke(Request $request, DashboardViewFactory $views)
    {
        return $views->makeForUser(
            $request->user()->loadMissing('roles.permissions'),
            'index',
            [
                'user' => $request->user(),
                // 'stats' => $this->getStats(),
            ],
        );
    }
}
