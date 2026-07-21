<x-layouts.auth title="Order summary">
    <section class="auth-card">
        <p class="eyebrow">Secure checkout</p>
        <h1>Order summary</h1>
        <p class="lede">Review your order before the next payment and fulfillment step.</p>

        <div class="summary-list">
            <div>
                <span>Order</span>
                <strong>{{ $order->number }}</strong>
            </div>
            <div>
                <span>Status</span>
                <strong>{{ str($order->status)->replace('_', ' ')->title() }}</strong>
            </div>
        </div>

        <div class="order-lines">
            @foreach ($items as $item)
                <article>
                    <div>
                        <strong>{{ $item->product_name }}</strong>
                        <span>Qty {{ $item->quantity }}</span>
                    </div>
                    <span>{{ number_format($item->total_minor / 100, 2) }} {{ $order->currency }}</span>
                </article>
            @endforeach
        </div>

        <div class="summary-list">
            <div>
                <span>Subtotal</span>
                <strong>{{ number_format($order->subtotal_minor / 100, 2) }} {{ $order->currency }}</strong>
            </div>
            @if ($order->discount_minor > 0)
                <div>
                    <span>Discount</span>
                    <strong>-{{ number_format($order->discount_minor / 100, 2) }} {{ $order->currency }}</strong>
                </div>
            @endif
            <div>
                <span>Shipping</span>
                <strong>{{ $order->shipping_minor > 0 ? number_format($order->shipping_minor / 100, 2).' '.$order->currency : 'Free' }}</strong>
            </div>
            <div>
                <span>Total</span>
                <strong>{{ number_format($order->total_minor / 100, 2) }} {{ $order->currency }}</strong>
            </div>
        </div>

        <p class="auth-switch">
            <a href="{{ route('shops.show', ['shop' => $shop->slug]) }}">Back to shop</a>
        </p>
    </section>
</x-layouts.auth>
