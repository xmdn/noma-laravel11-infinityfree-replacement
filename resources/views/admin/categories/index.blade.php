<x-layouts.auth title="Categories">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Catalog</p>
        <h1>Categories.</h1>
        <p class="lede">Organize the public catalog for your shop.</p>
    </section>

    <div class="form-actions">
        <a href="{{ route('admin.dashboard') }}">Back</a>
        <a class="primary-button" href="{{ route('admin.categories.create') }}">New category <span>+</span></a>
    </div>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Sort</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                        <td>{{ $category->is_active ? 'Active' : 'Hidden' }}</td>
                        <td>{{ $category->sort_order }}</td>
                        <td><a href="{{ route('admin.categories.edit', $category) }}">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $categories->links() }}
</x-layouts.auth>
