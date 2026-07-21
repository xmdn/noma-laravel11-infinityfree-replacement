<section class="auth-card">
    <p class="eyebrow">Secure checkout</p>
    <h1>Create your account.</h1>
    <p class="lede">Register a real user account. Your current cart will be attached to this account and converted into an order.</p>

    <form wire:submit="registerAndCheckout" class="stack-form">
        <label>Full name
            <input wire:model="registerName" type="text" autocomplete="name" required autofocus>
            @error('registerName') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label>Email address
            <input wire:model="registerEmail" type="email" autocomplete="email" required>
            @error('registerEmail') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label>Password
            <input wire:model="registerPassword" type="password" autocomplete="new-password" required>
            @error('registerPassword') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label>Confirm password
            <input wire:model="registerPasswordConfirmation" type="password" autocomplete="new-password" required>
            @error('registerPasswordConfirmation') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <button class="primary-button" type="submit" wire:loading.attr="disabled" wire:target="registerAndCheckout">
            Register and checkout <span>→</span>
        </button>
    </form>

    <p class="auth-switch">
        Already registered?
        <a href="{{ route('shops.checkout.login', ['shop' => $shopSlug]) }}">Sign in</a>
    </p>
</section>
