<?php

namespace App\Application\Cart;

use App\Domain\Cart\CartRepository;
use App\Domain\Catalog\ProductRepository;
use DomainException;

final readonly class AddProductToCart
{
    public function __construct(
        private ProductRepository $products,
        private CartRepository $cart,
    ) {}

    public function handle(string $productId): void
    {
        if ($this->products->find($productId) === null) {
            throw new DomainException('Product does not exist.');
        }

        $quantity = ($this->cart->quantities()[$productId] ?? 0) + 1;
        $this->cart->put($productId, $quantity);
    }
}
