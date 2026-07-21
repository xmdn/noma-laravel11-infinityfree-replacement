<?php

namespace App\Livewire;

use App\Application\Checkout\ConvertCartToOrder;
use App\Application\Identity\RegisterUser;
use App\Domain\Identity\SystemRole;
use App\Models\Cart;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Secure checkout')]
final class CustomerCheckout extends Component
{
    public string $mode = 'login';

    public string $loginEmail = '';

    public string $loginPassword = '';

    public bool $remember = false;

    public string $registerName = '';

    public string $registerEmail = '';

    public string $registerPassword = '';

    public string $registerPasswordConfirmation = '';

    public string $shopSlug = '';

    public function mount(string $mode = 'login')
    {
        $shop = Shop::current();
        abort_unless($shop instanceof Shop, 404);

        $this->mode = $mode === 'register' ? 'register' : 'login';
        $this->shopSlug = $shop->slug;

        if (auth()->check()) {
            $this->completeCheckoutFor(auth()->user());

            return redirect()->route('shops.checkout.summary', ['shop' => $this->shopSlug]);
        }
    }

    public function loginWithPassword()
    {
        $cartSessionId = session()->getId();

        $credentials = $this->validate([
            'loginEmail' => ['required', 'string', 'lowercase', 'email'],
            'loginPassword' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        if (! Auth::attempt([
            'email' => $credentials['loginEmail'],
            'password' => $credentials['loginPassword'],
        ], $this->remember)) {
            throw ValidationException::withMessages([
                'loginEmail' => __('auth.failed'),
            ]);
        }

        session()->regenerate();

        /** @var User $user */
        $user = auth()->user()->load('roles.permissions');

        if (! $this->isCheckoutCustomer($user)) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            throw ValidationException::withMessages([
                'loginEmail' => 'Use the standard user login for this account.',
            ]);
        }

        session()->put('checkout_user_id', $user->getKey());
        session()->put('checkout_user_access_expires_at', now()->addMinutes($this->accessTtlMinutes())->timestamp);

        $this->completeCheckoutFor($user, $cartSessionId);

        return redirect()->route('shops.checkout.summary', ['shop' => $this->shopSlug]);
    }

    public function registerAndCheckout()
    {
        $cartSessionId = session()->getId();

        $attributes = $this->validate([
            'registerName' => ['required', 'string', 'max:255'],
            'registerEmail' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'registerPassword' => ['required', Password::defaults(), 'same:registerPasswordConfirmation'],
            'registerPasswordConfirmation' => ['required', 'string'],
        ]);

        $user = app(RegisterUser::class)->handle([
            'name' => $attributes['registerName'],
            'email' => $attributes['registerEmail'],
            'password' => $attributes['registerPassword'],
        ]);

        Auth::login($user);
        session()->regenerate();

        session()->put('checkout_user_id', $user->getKey());
        session()->put('checkout_user_access_expires_at', now()->addMinutes($this->accessTtlMinutes())->timestamp);

        $this->completeCheckoutFor($user, $cartSessionId);

        return redirect()->route('shops.checkout.summary', ['shop' => $this->shopSlug]);
    }

    public function render()
    {
        return view('livewire.customer-checkout-'.$this->mode);
    }

    private function isCheckoutCustomer(User $user): bool
    {
        return $user->hasRole(SystemRole::Customer->value)
            && ! $user->hasRole(SystemRole::Owner->value)
            && ! $user->hasRole(SystemRole::Administrator->value)
            && ! $user->hasRole(SystemRole::CatalogManager->value)
            && ! $user->hasRole(SystemRole::OrderManager->value)
            && ! $user->hasRole(SystemRole::Support->value);
    }

    private function completeCheckoutFor(User $user, ?string $sessionId = null): void
    {
        $cart = Cart::query()
            ->where('session_id', $sessionId ?? session()->getId())
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('items')
            ->first();

        if (! $cart instanceof Cart) {
            return;
        }

        $orderId = app(ConvertCartToOrder::class)($cart, $user);

        if (is_string($orderId)) {
            session()->put('checkout_order_id', $orderId);
        }
    }

    private function accessTtlMinutes(): int
    {
        return max(1, (int) config('noma.customer_access_ttl_minutes', 3));
    }
}
