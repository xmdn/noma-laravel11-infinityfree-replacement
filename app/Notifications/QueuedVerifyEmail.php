<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\Multitenancy\Jobs\NotTenantAware;

final class QueuedVerifyEmail extends VerifyEmail implements NotTenantAware, ShouldQueue
{
    use Queueable;
}
