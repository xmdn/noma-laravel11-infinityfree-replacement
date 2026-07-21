<?php

namespace App\Domain\Tenancy;

enum OnboardingStatus: string
{
    case AwaitingEmailVerification = 'awaiting_email_verification';
    case Provisioned = 'provisioned';
    case Failed = 'failed';
}
