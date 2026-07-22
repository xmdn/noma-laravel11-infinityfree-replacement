<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardModuleFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, AdminDashboardModuleFactory $modules): View
    {
        return view('admin.dashboard', [
            'modules' => $modules->makeForUser($request->user()->loadMissing(['roles.permissions', 'shop'])),
        ]);
    }
}
