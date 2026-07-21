<x-layouts.auth title="Shop settings">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Shop</p>
        <h1>{{ $shop->name }}</h1>
        <p class="lede">Configure the foundational identity and operating defaults for this shop.</p>
    </section>

    <form method="POST" action="{{ route('admin.shop.settings.update') }}" class="stack-form role-form">
        @csrf
        @method('PATCH')
        <label>Shop name
            <input id="name" name="name" value="{{ old('name', $shop->name) }}" required>
            @error('name') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>Shop address
            <input id="slug" name="slug" value="{{ old('slug', $shop->slug) }}" required>
            <small class="field-hint">Your storefront is available at {{ url('/shops') }}/{{ old('slug', $shop->slug) }}.</small>
            @error('slug') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>Store currency
            <input id="currency" name="currency" value="{{ old('currency', $shop->currency) }}" maxlength="3" required>
            @error('currency') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <label>Timezone
            <select id="timezone" name="timezone" required>
                @foreach ($timezones as $timezone)
                    <option value="{{ $timezone }}" @selected(old('timezone', $shop->timezone) === $timezone)>{{ $timezone }}</option>
                @endforeach
            </select>
            @error('timezone') <span class="field-error">{{ $message }}</span> @enderror
        </label>
        <button class="primary-button" type="submit">Save settings <span>→</span></button>
    </form>
</x-layouts.auth>
