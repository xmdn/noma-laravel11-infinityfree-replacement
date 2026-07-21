<x-layouts.auth title="New discount">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Discounts</p>
        <h1>New discount.</h1>
    </section>

    @include('admin.promotions.partials.form', [
        'action' => route('admin.promotions.store'),
        'method' => 'POST',
    ])
</x-layouts.auth>
