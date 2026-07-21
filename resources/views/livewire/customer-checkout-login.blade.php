<section class="auth-card">
    <p class="eyebrow">Secure checkout</p>
    <h1>Sign in to continue.</h1>
    <p class="lede">Login to your user account. Your current cart will be attached to this account and converted into an order.</p>

    <form wire:submit="loginWithPassword" class="stack-form">
        <label>Email address
            <input wire:model="loginEmail" type="email" autocomplete="email" required autofocus>
            @error('loginEmail') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label>Password
            <input wire:model="loginPassword" type="password" autocomplete="current-password" required>
            @error('loginPassword') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label class="check">
            <input wire:model="remember" type="checkbox">
            Remember me
        </label>

        <button class="primary-button" type="submit" wire:loading.attr="disabled" wire:target="loginWithPassword">
            Login and checkout <span>→</span>
        </button>
    </form>

    <p class="auth-switch">
        New customer?
        <a href="{{ route('shops.checkout.register', ['shop' => $shopSlug]) }}">Create an account</a>
    </p>
</section>
