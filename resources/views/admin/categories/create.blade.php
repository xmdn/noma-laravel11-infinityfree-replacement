<x-layouts.auth title="New category">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Catalog</p>
        <h1>New category.</h1>
    </section>

    @include('admin.categories.partials.form', [
        'action' => route('admin.categories.store'),
        'method' => 'POST',
        'category' => $category,
    ])
</x-layouts.auth>
