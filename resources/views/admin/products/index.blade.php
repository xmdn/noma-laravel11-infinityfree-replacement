<x-layouts.auth title="Products">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Catalog</p>
        <h1>Products.</h1>
        <p class="lede">Manage the products shown on your public shop address.</p>
    </section>

    <div class="form-actions">
        <a href="{{ route('admin.dashboard') }}">Back</a>
        <a class="primary-button" href="{{ route('admin.products.create') }}">New product <span>+</span></a>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Shop</th><th>Categories</th><th>Price</th><th>Status</th><th>Moderation</th><th></th></tr></thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->shop?->name ?? 'Current shop' }}</td>
                        <td>{{ $product->categories->pluck('name')->join(', ') ?: 'Uncategorized' }}</td>
                        <td>{{ $product->currency }} {{ number_format($product->price_minor / 100, 2) }}</td>
                        <td>{{ ucfirst($product->status) }}</td>
                        <td>{{ $product->is_blocked ? 'Blocked' : 'Allowed' }}</td>
                        <td><a href="{{ route('admin.products.edit', $product) }}">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</x-layouts.auth>
