<?php

namespace App\Domain\Checkout;

use App\Domain\Shared\Money;

final readonly class ThresholdPromotion extends AbstractConditionalPromotion
{
    public function __construct(
        private int $thresholdCents,
        private int $discountPercent,
    ) {}

    protected function isEligible(Money $subtotal): bool
    {
        return $subtotal->cents >= $this->thresholdCents;
    }

    protected function calculateEligibleDiscount(Money $subtotal): Money
    {
        return new Money((int) round($subtotal->cents * $this->discountPercent / 100));
    }

    public function label(): string
    {
        return "Studio edit · {$this->discountPercent}%";
    }
}
