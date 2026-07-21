<x-layouts.auth title="Create your shop">
    <section class="auth-card">
        <p class="eyebrow">Business owner</p>
        <h1>Open your shop.</h1>
        <p class="lede">Create your owner account. We will create one shop after you confirm your email address.</p>

        <form method="POST" action="{{ route('owner.register') }}" class="stack-form">
            @csrf
            <label>Your name
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Business email
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Shop name
                <input id="shop_name" name="shop_name" type="text" value="{{ old('shop_name') }}" required>
                @error('shop_name') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Shop address
                <input id="shop_slug" name="shop_slug" type="text" value="{{ old('shop_slug') }}" placeholder="your-shop" required>
                <small class="field-hint">This reserves your unique shop identifier.</small>
                @error('shop_slug') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Password
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Confirm password
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </label>
            <button class="primary-button" type="submit">Create owner account <span>→</span></button>
        </form>

        <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
    </section>
</x-layouts.auth>
