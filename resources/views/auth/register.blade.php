<x-layouts.auth title="Create account">
    <section class="auth-card">
        <p class="eyebrow">Customer account</p>
        <h1>Join NOMA.</h1>
        <p class="lede">Create an account for a more considered shopping experience.</p>

        <form method="POST" action="{{ route('register') }}" class="stack-form">
            @csrf
            <label>Name
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required autofocus>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Email address
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Password
                <input id="password" name="password" type="password" autocomplete="new-password" required>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Confirm password
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </label>
            <button class="primary-button" type="submit">Create account <span>→</span></button>
        </form>

        <p class="auth-switch">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
        <p class="auth-switch">Opening a business? <a href="{{ route('owner.register') }}">Create your shop</a></p>
    </section>
</x-layouts.auth>
