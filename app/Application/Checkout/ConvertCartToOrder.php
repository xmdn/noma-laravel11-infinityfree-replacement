<?php

namespace App\Application\Checkout;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ConvertCartToOrder
{
    public function __invoke(Cart $cart, User $user): ?string
    {
        return DB::transaction(function () use ($cart, $user): ?string {
            /** @var Cart|null $cart */
            $cart = Cart::query()
                ->whereKey($cart->getKey())
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->with('items')
                ->lockForUpdate()
                ->first();

            if ($cart === null || $cart->items->isEmpty()) {
                return null;
            }

            $existingOrderId = DB::table('orders')
                ->where('cart_id', $cart->getKey())
                ->value('id');

            if (is_string($existingOrderId)) {
                $cart->update([
                    'user_id' => $user->getKey(),
                    'status' => 'converted',
                ]);

                return $existingOrderId;
            }

            $subtotalMinor = (int) $cart->items->sum(
                fn ($item): int => (int) $item->unit_price_minor * (int) $item->quantity,
            );
            $discountMinor = (int) $cart->items->sum('discount_minor');
            $totalMinor = (int) $cart->items->sum('total_minor');
            $orderId = (string) Str::ulid();

            DB::table('orders')->insert([
                'id' => $orderId,
                'number' => $this->nextOrderNumber(),
                'user_id' => $user->getKey(),
                'cart_id' => $cart->getKey(),
                'status' => 'awaiting_approval',
                'currency' => $cart->currency,
                'subtotal_minor' => $subtotalMinor,
                'discount_minor' => $discountMinor,
                'shipping_minor' => 0,
                'total_minor' => $totalMinor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                DB::table('order_items')->insert([
                    'id' => (string) Str::ulid(),
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'sku' => data_get($item->price_snapshot, 'sku'),
                    'quantity' => $item->quantity,
                    'unit_price_minor' => $item->unit_price_minor,
                    'discount_minor' => $item->discount_minor,
                    'total_minor' => $item->total_minor,
                ]);
            }

            $cart->update([
                'user_id' => $user->getKey(),
                'status' => 'converted',
            ]);

            return $orderId;
        });
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'NOMA-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (DB::table('orders')->where('number', $number)->exists());

        return $number;
    }
}
