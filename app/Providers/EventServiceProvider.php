<?php

namespace App\Providers;

use App\Listeners\ConvertVerifiedUserCartToOrder;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Verified::class => [
            ConvertVerifiedUserCartToOrder::class,
        ],
    ];
}