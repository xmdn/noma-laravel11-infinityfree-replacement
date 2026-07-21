<?php

namespace App\Infrastructure\Cart;

use App\Domain\Cart\CartRepository;
use App\Models\Cart;
use App\Models\CatalogProduct;

final class DatabaseCartRepository implements CartRepository
{
    public function quantities(): array
    {
        return $this->activeCart()
            ?->items()
            ->pluck('quantity', 'product_id')
            ->all() ?? [];
    }

    public function put(string $productId, int $quantity): void
    {
        $product = CatalogProduct::query()
            ->whereKey($productId)
            ->where('status', 'active')
            ->where('is_blocked', false)
            ->whereNotNull('published_at')
            ->firstOrFail();

        $cart = $this->activeOrCreateCart();

        $quantity = min(10, max(1, $quantity));
        $unit = $product->price_minor;
        $discount = 0;

        $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            [
                'product_slug' => $product->slug,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price_minor' => $unit,
                'discount_minor' => $discount,
                'total_minor' => ($unit * $quantity) - $discount,
                'price_snapshot' => [
                    'price_minor' => $unit,
                    'discount_minor' => $discount,
                ],
            ],
        );

        $cart->update(['expires_at' => now()->addMinutes($this->cartTtlMinutes())]);
    }

    public function remove(string $productId): void
    {
        $this->activeCart()?->items()->where('product_id', $productId)->delete();
    }

    public function clear(): void
    {
        $this->activeCart()?->update(['status' => 'converted']);
    }

    private function activeCart(): ?Cart
    {
        return Cart::query()
            ->where('session_id', session()->getId())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('items')
            ->first();
    }

    private function activeOrCreateCart(): Cart
    {
        return $this->activeCart() ?? Cart::query()->create([
            'session_id' => session()->getId(),
            'status' => 'active',
            'currency' => 'USD',
            'expires_at' => now()->addMinutes($this->cartTtlMinutes()),
        ]);
    }

    private function cartTtlMinutes(): int
    {
        return max(1, (int) config('noma.cart_ttl_minutes', 2));
    }
}
