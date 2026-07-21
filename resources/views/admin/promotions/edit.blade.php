<x-layouts.auth title="Edit discount">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Discounts</p>
        <h1>{{ $promotion->name }}</h1>
    </section>

    @include('admin.promotions.partials.form', [
        'action' => route('admin.promotions.update', $promotion),
        'method' => 'PATCH',
    ])
</x-layouts.auth>
