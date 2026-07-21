<?php

namespace App\Contracts;

interface HasDashboardView
{
    public function getDashboardViewPrefix(): string;
}
