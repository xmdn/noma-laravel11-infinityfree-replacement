<?php

namespace App\Application\Cart;

use App\Domain\Cart\CartRepository;

final readonly class ChangeCartQuantity
{
    public function __construct(private CartRepository $cart) {}

    public function handle(string $productId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->cart->remove($productId);

            return;
        }

        $this->cart->put($productId, $quantity);
    }
}
