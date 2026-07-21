<x-layouts.auth title="New product">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Catalog</p>
        <h1>New product.</h1>
    </section>

    @include('admin.products.partials.form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
    ])
</x-layouts.auth>
