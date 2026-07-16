<?php

namespace App\Livewire;

use App\Application\Cart\AddProductToCart;
use App\Application\Cart\ChangeCartQuantity;
use App\Application\Cart\GetCartSummary;
use App\Application\Catalog\BrowseProducts;
use App\Domain\Cart\CartRepository;
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

    #[Computed]
    public function products(): array
    {
        return app(BrowseProducts::class)->handle($this->query, $this->category, $this->sort);
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
