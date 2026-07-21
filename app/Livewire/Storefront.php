<?php

namespace App\Livewire;

use App\Application\Cart\AddProductToCart;
use App\Application\Cart\ChangeCartQuantity;
use App\Application\Cart\GetCartSummary;
use App\Application\Catalog\BrowseProducts;
use App\Domain\Cart\CartRepository;
use App\Models\Shop;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
final class Storefront extends Component
{
    public string $query = '';

    public string $category = 'All';

    public string $sort = 'featured';

    public bool $cartOpen = false;

    public string $email = '';

    public bool $subscribed = false;

    /** @var list<string> */
    public array $favorites = [];

    public string $shopId = '';

    public string $shopName = 'NOMA';

    public string $shopSlug = '';

    public function mount(): void
    {
        $shop = Shop::current();
        abort_unless($shop instanceof Shop, 404);

        $this->shopId = $shop->id;
        $this->shopName = $shop->name;
        $this->shopSlug = $shop->slug;
    }

    #[Computed]
    public function products(): array
    {
        return app(BrowseProducts::class)->handle($this->query, $this->category, $this->sort, $this->shopId);
    }

    #[Computed]
    public function categories(): array
    {
        return collect($this->products)
            ->pluck('category')
            ->unique()
            ->prepend('All')
            ->values()
            ->all();
    }

    #[Computed]
    public function cart(): array
    {
        return app(GetCartSummary::class)->handle();
    }

    public function addToCart(string $productId): void
    {
        app(AddProductToCart::class)->handle($productId);
        unset($this->cart);
        $this->cartOpen = true;
        $this->dispatch('cart-updated');
    }

    public function updateQuantity(string $productId, int $quantity): void
    {
        app(ChangeCartQuantity::class)->handle($productId, $quantity);
        unset($this->cart);
    }

    public function removeFromCart(string $productId): void
    {
        app(CartRepository::class)->remove($productId);
        unset($this->cart);
    }

    public function clearCart(): void
    {
        app(CartRepository::class)->clear();
        unset($this->cart);
    }

    public function beginCheckout()
    {
        if ($this->cart['count'] === 0) {
            return null;
        }

        return redirect()->route('shops.checkout', ['shop' => $this->shopSlug]);
    }

    public function toggleFavorite(string $productId): void
    {
        $this->favorites = in_array($productId, $this->favorites, true)
            ? array_values(array_diff($this->favorites, [$productId]))
            : [...$this->favorites, $productId];
    }

    public function subscribe(): void
    {
        $this->validate(['email' => ['required', 'email:rfc']]);
        $this->subscribed = true;
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.storefront');
    }
}
