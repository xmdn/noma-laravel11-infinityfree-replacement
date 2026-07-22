<?php

namespace App\Services;

use App\Contracts\HasDashboardView;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

final class DashboardViewFactory
{
    public function __construct(
        private readonly AdminDashboardModuleFactory $adminModules,
    ) {
    }

    public function makeForUser(
        HasDashboardView $user,
        string $viewName,
        array $data = [],
    ): View {
        $prefix = $user->getDashboardViewPrefix();

        $targetedView = "dashboards.{$prefix}.{$viewName}";

        if (! ViewFacade::exists($targetedView)) {
            $targetedView = "dashboards.default.{$viewName}";
        }

        if ($targetedView === "dashboards.admin.{$viewName}" && $user instanceof User) {
            $data += [
                'modules' => $this->adminModules->makeForUser($user),
            ];
        }

        return view($targetedView, $data);
    }
}
