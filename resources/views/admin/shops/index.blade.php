<x-layouts.auth title="Shops">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Platform / Shops</p>
        <h1>Shops.</h1>
        <p class="lede">Platform administrators can suspend storefront access without deleting tenant data.</p>
    </section>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Shop</th><th>Owner</th><th>Domain</th><th>Products</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($shops as $shop)
                    <tr>
                        <td>{{ $shop->name }}</td>
                        <td>{{ $shop->owner?->email ?? 'Missing owner' }}</td>
                        <td><a href="{{ $shop->publicUrl() }}">{{ $shop->domain }}</a></td>
                        <td>{{ $shop->products_count }}</td>
                        <td>{{ $shop->is_accessible ? 'Accessible' : 'Suspended' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.shops.update', $shop) }}">
                                @csrf
                                @method('PATCH')
                                @if ($shop->is_accessible)
                                    <input type="hidden" name="is_accessible" value="0">
                                    <input type="hidden" name="blocked_reason" value="Suspended by platform administrator.">
                                    <button type="submit">Suspend</button>
                                @else
                                    <input type="hidden" name="is_accessible" value="1">
                                    <button type="submit">Restore</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $shops->links() }}
</x-layouts.auth>
