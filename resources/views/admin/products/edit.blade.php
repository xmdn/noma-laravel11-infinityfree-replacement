<x-layouts.auth title="Edit product">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Catalog</p>
        <h1>{{ $product->name }}</h1>
    </section>

    @if (auth()->user()->hasRole(\App\Domain\Identity\SystemRole::Administrator->value))
        <div class="summary-grid">
            <article><span>SHOP</span><strong>{{ $product->shop?->name ?? 'Unknown' }}</strong></article>
            <article><span>STATUS</span><strong>{{ $product->is_blocked ? 'Blocked' : 'Allowed' }}</strong></article>
        </div>

        <form method="POST" action="{{ route('admin.products.moderation.update', $product) }}" class="stack-form role-form">
            @csrf
            @method('PATCH')
            @if ($product->is_blocked)
                <input type="hidden" name="is_blocked" value="0">
                <button class="primary-button" type="submit">Restore product <span>→</span></button>
            @else
                <input type="hidden" name="is_blocked" value="1">
                <label>Reason
                    <input name="blocked_reason" value="Blocked by platform administrator.">
                </label>
                <button class="primary-button" type="submit">Block product <span>→</span></button>
            @endif
        </form>
    @else
        @include('admin.products.partials.form', [
            'action' => route('admin.products.update', $product),
            'method' => 'PATCH',
        ])

        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="form-actions">
            @csrf
            @method('DELETE')
            <button type="submit">Delete product</button>
        </form>
    @endif
</x-layouts.auth>
