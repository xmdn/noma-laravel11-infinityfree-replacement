<?php

namespace App\Domain\Tenancy;

enum ShopStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
