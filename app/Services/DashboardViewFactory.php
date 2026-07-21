<?php

namespace App\Services;

use App\Contracts\HasDashboardView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

final class DashboardViewFactory
{
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

        return view($targetedView, $data);
    }
}