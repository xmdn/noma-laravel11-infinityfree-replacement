<?php

namespace App\Domain\Checkout;

use App\Domain\Shared\Money;

/**
 * Template Method for promotions that are activated by an eligibility rule.
 *
 * The base class owns the invariant that an ineligible promotion always returns
 * zero; concrete policies define only eligibility and eligible calculation.
 */
abstract readonly class AbstractConditionalPromotion implements Promotion
{
    final public function discount(Money $subtotal): Money
    {
        if (! $this->isEligible($subtotal)) {
            return Money::zero();
        }

        return $this->calculateEligibleDiscount($subtotal);
    }

    abstract protected function isEligible(Money $subtotal): bool;

    abstract protected function calculateEligibleDiscount(Money $subtotal): Money;
}
