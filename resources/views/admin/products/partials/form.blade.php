<form method="POST" action="{{ $action }}" class="stack-form role-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <label>Name
        <input name="name" value="{{ old('name', $product->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Slug
        <input name="slug" value="{{ old('slug', $product->slug) }}" placeholder="Generated from name when empty">
        @error('slug') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Description
        <input name="description" value="{{ old('description', $product->description) }}" required>
        @error('description') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Price in cents
        <input name="price_minor" type="number" min="0" value="{{ old('price_minor', $product->price_minor ?? 0) }}" required>
        @error('price_minor') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Currency
        <input name="currency" maxlength="3" value="{{ old('currency', $product->currency ?? 'USD') }}" required>
        @error('currency') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Primary image URL
        <input name="primary_image_url" value="{{ old('primary_image_url', $product->primary_image_url) }}" required>
        @error('primary_image_url') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Color swatches
        <input name="colors" value="{{ old('colors', implode(', ', $product->colors ?? [])) }}" placeholder="#171814, #d2cec0">
        @error('colors') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Status
        <select name="status" required>
            @foreach (['draft', 'active', 'archived'] as $status)
                <option value="{{ $status }}" @selected(old('status', $product->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @error('status') <span class="field-error">{{ $message }}</span> @enderror
    </label>

    <fieldset>
        <legend>Categories</legend>
        @forelse ($categories as $category)
            <label class="role-option">
                <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, old('category_ids', $selectedCategories), true))>
                <span><strong>{{ $category->name }}</strong><small>{{ $category->slug }}</small></span>
            </label>
        @empty
            <p class="lede">Create categories before assigning products.</p>
        @endforelse
    </fieldset>

    <label class="check"><input name="is_featured" type="checkbox" value="1" @checked(old('is_featured', $product->is_featured ?? false))> Featured product</label>
    <label class="check"><input name="is_new" type="checkbox" value="1" @checked(old('is_new', $product->is_new ?? false))> New arrival badge</label>

    <div class="form-actions">
        <a href="{{ route('admin.products.index') }}">Cancel</a>
        <button class="primary-button" type="submit">Save product <span>→</span></button>
    </div>
</form>
