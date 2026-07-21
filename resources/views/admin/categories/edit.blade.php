<x-layouts.auth title="Edit category">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Catalog</p>
        <h1>{{ $category->name }}</h1>
    </section>

    @include('admin.categories.partials.form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PATCH',
        'category' => $category,
    ])

    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="form-actions">
        @csrf
        @method('DELETE')
        <button type="submit">Delete category</button>
    </form>
</x-layouts.auth>
