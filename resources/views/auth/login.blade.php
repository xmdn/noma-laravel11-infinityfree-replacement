<x-layouts.auth title="Sign in">
    <section class="auth-card">
        <p class="eyebrow">Customer account</p>
        <h1>Welcome back.</h1>
        <p class="lede">Sign in to manage your NOMA account.</p>

        <form method="POST" action="{{ url('/login') }}" class="stack-form">
            @csrf
            <label>Email address
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label>Password
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </label>
            <label class="check"><input name="remember" type="checkbox" value="1"> Remember me</label>
            <button class="primary-button" type="submit">Sign in <span>→</span></button>
        </form>

        <p class="auth-switch">New to NOMA? <a href="{{ route('register') }}">Create an account</a></p>
    </section>
</x-layouts.auth>
