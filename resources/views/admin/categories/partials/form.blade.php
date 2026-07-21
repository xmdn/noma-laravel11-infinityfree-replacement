<form method="POST" action="{{ $action }}" class="stack-form role-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <label>Name
        <input name="name" value="{{ old('name', $category->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Slug
        <input name="slug" value="{{ old('slug', $category->slug) }}" placeholder="Generated from name when empty">
        @error('slug') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Sort order
        <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" required>
        @error('sort_order') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label class="check">
        <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $category->is_active ?? true))>
        Active in storefront
    </label>
    <div class="form-actions">
        <a href="{{ route('admin.categories.index') }}">Cancel</a>
        <button class="primary-button" type="submit">Save category <span>→</span></button>
    </div>
</form>
