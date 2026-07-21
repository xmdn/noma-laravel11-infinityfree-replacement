<?php

namespace App\Listeners;

use App\Application\Checkout\ConvertCartToOrder as ConvertCartToOrderAction;
use App\Models\Cart;
use Illuminate\Auth\Events\Verified;

final class ConvertVerifiedUserCartToOrder
{
    public function __construct(
        private readonly ConvertCartToOrderAction $convertCartToOrder,
    ) {}

    public function handle(Verified $event): void
    {
        $cart = Cart::query()
            ->where('session_id', session()->getId())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('items')
            ->first();

        if ($cart instanceof Cart) {
            ($this->convertCartToOrder)($cart, $event->user);
        }
    }
}
