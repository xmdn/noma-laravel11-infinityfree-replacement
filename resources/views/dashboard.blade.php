<x-layouts.auth title="Your account">
    <section class="page-heading">
        <p class="eyebrow">Account</p>
        <h1>Hello, {{ $user->name }}.</h1>
        @if (! $user->hasVerifiedEmail())
            <p class="lede">Verify your email to finish securing your account{{ $user->shopOnboarding ? ' and create your shop' : '' }}.</p>
            <p><a href="{{ url('/email/verify') }}">Continue email verification →</a></p>
        @elseif ($user->shop)
            <p class="lede">Your shop is active. Commerce workspaces will be released in the order shown in the platform roadmap.</p>
        @else
            <p class="lede">Your account is ready. Order history and saved addresses can be added here next.</p>
        @endif
    </section>

    <div class="summary-grid">
        <article><span>EMAIL</span><strong>{{ $user->email }}</strong></article>
        <article><span>ACCESS</span><strong>{{ $user->roles->pluck('name')->join(', ') ?: 'Customer' }}</strong></article>
        @if ($user->shop)
            <article><span>SHOP</span><strong>{{ $user->shop->name }}</strong><a href="{{ url('/admin') }}">Open administration →</a></article>
            <article><span>SHOP STATUS</span><strong>{{ ucfirst($user->shop->status->value) }}</strong></article>
        @elseif ($user->shopOnboarding && $user->hasVerifiedEmail())
            <article>
                <span>SHOP ONBOARDING</span>
                <strong>{{ str($user->shopOnboarding->status->value)->replace('_', ' ')->title() }}</strong>
                <form method="POST" action="{{ url('/owner/onboarding/retry') }}">@csrf<button type="submit">Retry provisioning</button></form>
            </article>
        @endif
    </div>
</x-layouts.auth>
