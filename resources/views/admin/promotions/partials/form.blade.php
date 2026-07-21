@php
    $promotionTimezone = $shop->timezone ?? config('app.timezone');
    $startsAtValue = $promotion->starts_at
        ? $promotion->starts_at->timezone($promotionTimezone)->format('Y-m-d\TH:i')
        : now($promotionTimezone)->format('Y-m-d\TH:i');
    $endsAtValue = $promotion->ends_at
        ? $promotion->ends_at->timezone($promotionTimezone)->format('Y-m-d\TH:i')
        : null;
@endphp

<form method="POST" action="{{ $action }}" class="stack-form role-form">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <label>Name
        <input name="name" value="{{ old('name', $promotion->name) }}" required>
        @error('name') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Code
        <input name="code" value="{{ old('code', $promotion->code) }}" placeholder="Leave empty for automatic discount">
        @error('code') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Type
        <select name="type" required>
            <option value="percentage" @selected(old('type', $promotion->type) === 'percentage')>Percentage</option>
            <option value="fixed_amount" @selected(old('type', $promotion->type) === 'fixed_amount')>Fixed amount</option>
        </select>
    </label>
    <label>Value
        <input name="value" type="number" min="1" value="{{ old('value', $promotion->value) }}" required>
        @error('value') <span class="field-error">{{ $message }}</span> @enderror
    </label>
    <label>Minimum subtotal in cents
        <input name="minimum_subtotal_minor" type="number" min="0" value="{{ old('minimum_subtotal_minor', $promotion->minimum_subtotal_minor ?? 0) }}" required>
    </label>
    <label>Maximum discount in cents
        <input name="maximum_discount_minor" type="number" min="0" value="{{ old('maximum_discount_minor', $promotion->maximum_discount_minor) }}">
    </label>
    <label>Priority
        <input name="priority" type="number" min="0" value="{{ old('priority', $promotion->priority ?? 100) }}" required>
    </label>
    <label>Stacking
        <select name="stacking_mode" required>
            @foreach (['best_price', 'exclusive', 'stackable'] as $mode)
                <option value="{{ $mode }}" @selected(old('stacking_mode', $promotion->stacking_mode) === $mode)>{{ str($mode)->headline() }}</option>
            @endforeach
        </select>
    </label>
    <label>Starts at
        <input name="starts_at" type="datetime-local" value="{{ old('starts_at', $startsAtValue) }}" required>
    </label>
    <label>Ends at
        <input name="ends_at" type="datetime-local" value="{{ old('ends_at', $endsAtValue) }}">
    </label>
    <label class="check"><input name="is_active" type="checkbox" value="1" @checked(old('is_active', $promotion->is_active ?? true))> Active</label>

    <div class="form-actions">
        <a href="{{ route('admin.promotions.index') }}">Cancel</a>
        <button class="primary-button" type="submit">Save discount <span>→</span></button>
    </div>
</form>
