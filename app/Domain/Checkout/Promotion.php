<?php

namespace App\Domain\Checkout;

use App\Domain\Shared\Money;

interface Promotion
{
    public function discount(Money $subtotal): Money;

    public function label(): string;
}
