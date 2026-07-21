<x-layouts.auth title="Verify your email">
    <section class="auth-card">
        <p class="eyebrow">One last step</p>
        <h1>Check your inbox.</h1>
        <p class="lede">We sent a secure verification link to {{ auth()->user()->email }}. For business owners, your shop is created only after that link is opened.</p>

        @if (session('status') === 'verification-link-sent')
            <p class="flash" role="status">A new verification link has been sent.</p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="stack-form">
            @csrf
            <button class="primary-button" type="submit">Resend verification email <span>→</span></button>
        </form>
    </section>
</x-layouts.auth>
