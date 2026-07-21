<x-layouts.auth title="Discounts">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Discounts</p>
        <h1>Discounts.</h1>
        <p class="lede">Create owner-managed promotions for this shop.</p>
    </section>

    <div class="form-actions">
        <a href="{{ route('admin.dashboard') }}">Back</a>
        <a class="primary-button" href="{{ route('admin.promotions.create') }}">New discount <span>+</span></a>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Code</th><th>Type</th><th>Value</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($promotions as $promotion)
                    <tr>
                        <td>{{ $promotion->name }}</td>
                        <td>{{ $promotion->code ?: 'Automatic' }}</td>
                        <td>{{ str($promotion->type)->headline() }}</td>
                        <td>{{ $promotion->type === 'percentage' ? $promotion->value.'%' : number_format($promotion->value / 100, 2) }}</td>
                        <td>{{ $promotion->is_active ? 'Active' : 'Paused' }}</td>
                        <td><a href="{{ route('admin.promotions.edit', $promotion) }}">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $promotions->links() }}
</x-layouts.auth>
