<?php

namespace App\Application\Cart;

use App\Domain\Cart\CartRepository;
use App\Domain\Catalog\ProductRepository;
use App\Domain\Checkout\Promotion;
use App\Domain\Shared\Money;

final readonly class GetCartSummary
{
    public function __construct(
        private ProductRepository $products,
        private CartRepository $cart,
        private Promotion $promotion,
    ) {}

    /** @return array<string, mixed> */
    public function handle(): array
    {
        $lines = [];
        $subtotal = Money::zero();
        $count = 0;

        $quantities = $this->cart->quantities();
        $products = $this->products->findMany(array_keys($quantities));

        foreach ($quantities as $productId => $quantity) {
            $product = $products[$productId] ?? null;

            if ($product === null) {
                continue;
            }

            $lineTotal = $product->price->multiply($quantity);
            $subtotal = $subtotal->add($lineTotal);
            $count += $quantity;
            $lines[] = [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'price' => $product->price->formatted(),
                'quantity' => $quantity,
                'line_total' => $lineTotal->formatted(),
            ];
        }

        $discount = $this->promotion->discount($subtotal);
        $shipping = $subtotal->cents === 0 || $subtotal->cents >= 20000 ? Money::zero() : new Money(1800);
        $total = $subtotal->subtract($discount)->add($shipping);

        return [
            'lines' => $lines,
            'count' => $count,
            'subtotal' => $subtotal->formatted(),
            'subtotal_cents' => $subtotal->cents,
            'discount' => $discount->formatted(),
            'discount_cents' => $discount->cents,
            'promotion' => $this->promotion->label(),
            'shipping' => $shipping->cents === 0 ? 'Free' : $shipping->formatted(),
            'total' => $total->formatted(),
            'free_shipping_remaining' => max(0, 20000 - $subtotal->cents),
        ];
    }
}
