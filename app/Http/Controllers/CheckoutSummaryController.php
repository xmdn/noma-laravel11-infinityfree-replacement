<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CheckoutSummaryController
{
    public function __invoke(Request $request)
    {
        $shop = Shop::current();
        abort_unless($shop instanceof Shop, 404);

        $userId = auth()->id() ?? session('checkout_user_id');
        abort_unless($userId, 403);

        $orderId = session('checkout_order_id');

        $order = DB::table('orders')
            ->when($orderId, fn ($query) => $query->where('id', $orderId))
            ->where('user_id', $userId)
            ->latest('created_at')
            ->first();

        if ($order === null) {
            return redirect()
                ->route('shops.show', ['shop' => $shop->slug])
                ->with('status', 'Your cart is empty.');
        }

        $items = DB::table('order_items')
            ->where('order_id', $order->id)
            ->orderBy('product_name')
            ->get();

        return view('checkout.summary', [
            'shop' => $shop,
            'order' => $order,
            'items' => $items,
        ]);
    }
}
