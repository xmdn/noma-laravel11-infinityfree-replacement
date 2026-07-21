<?php

namespace Tests\Feature;

use App\Domain\Identity\SystemRole;
use App\Domain\Tenancy\OnboardingStatus;
use App\Domain\Tenancy\ShopStatus;
use App\Livewire\CustomerCheckout;
use App\Models\Cart;
use App\Models\Role;
use App\Models\Shop;
use App\Models\ShopOnboarding;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutCustomerAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_checkout_registration_creates_user_and_attaches_active_cart(): void
    {
        $shop = $this->shop();
        $shop->makeCurrent();

        $cart = Cart::query()->create([
            'session_id' => session()->getId(),
            'status' => 'active',
            'currency' => 'USD',
            'expires_at' => now()->addMinutes(2),
        ]);
        $cart->items()->create([
            'product_id' => null,
            'product_slug' => 'linen-chair',
            'product_name' => 'Linen Chair',
            'quantity' => 2,
            'unit_price_minor' => 1500,
            'discount_minor' => 200,
            'total_minor' => 2800,
            'price_snapshot' => ['price_minor' => 1500, 'discount_minor' => 200],
        ]);

        Livewire::test(CustomerCheckout::class)
            ->set('mode', 'register')
            ->set('registerName', 'Ada Customer')
            ->set('registerEmail', 'ada@example.test')
            ->set('registerPassword', 'password')
            ->set('registerPasswordConfirmation', 'password')
            ->call('registerAndCheckout')
            ->assertHasNoErrors()
            ->assertRedirect(route('shops.checkout.summary', ['shop' => $shop->slug]));

        $user = User::query()->where('email', 'ada@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole(SystemRole::Customer->value));
        $this->assertSame($user->id, $cart->fresh()->user_id);
        $this->assertSame('converted', $cart->fresh()->status);
        $this->assertSame($user->id, session('checkout_user_id'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'cart_id' => $cart->id,
            'status' => 'awaiting_approval',
            'subtotal_minor' => 3000,
            'discount_minor' => 200,
            'total_minor' => 2800,
        ]);
        $this->assertSame(1, DB::table('order_items')->where('product_name', 'Linen Chair')->count());

        $this->get(route('shops.checkout.register', ['shop' => $shop->slug]))
            ->assertRedirect(route('shops.checkout.summary', ['shop' => $shop->slug]));

        $this->get(route('shops.checkout.summary', ['shop' => $shop->slug]))
            ->assertOk()
            ->assertSee('Order summary')
            ->assertSee('Linen Chair');
    }

    public function test_authenticated_user_skips_checkout_auth_and_sees_order_summary(): void
    {
        $shop = $this->shop('north-yard');
        $shop->makeCurrent();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach(Role::query()->where('slug', SystemRole::Customer->value)->firstOrFail());

        $cart = Cart::query()->create([
            'session_id' => session()->getId(),
            'status' => 'active',
            'currency' => 'USD',
            'expires_at' => now()->addMinutes(2),
        ]);
        $cart->items()->create([
            'product_id' => null,
            'product_slug' => 'oak-table',
            'product_name' => 'Oak Table',
            'quantity' => 1,
            'unit_price_minor' => 4200,
            'discount_minor' => 0,
            'total_minor' => 4200,
            'price_snapshot' => ['price_minor' => 4200],
        ]);

        Livewire::actingAs($user)
            ->test(CustomerCheckout::class)
            ->assertRedirect(route('shops.checkout.summary', ['shop' => $shop->slug]));

        $this->assertSame('converted', $cart->fresh()->status);
        $this->assertSame($user->id, $cart->fresh()->user_id);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'cart_id' => $cart->id,
            'total_minor' => 4200,
        ]);
    }

    public function test_password_login_from_checkout_keeps_pre_login_cart(): void
    {
        $shop = $this->shop('west-room');
        $shop->makeCurrent();

        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach(Role::query()->where('slug', SystemRole::Customer->value)->firstOrFail());

        $cart = Cart::query()->create([
            'session_id' => session()->getId(),
            'status' => 'active',
            'currency' => 'USD',
            'expires_at' => now()->addMinutes(2),
        ]);
        $cart->items()->create([
            'product_id' => null,
            'product_slug' => 'stone-bowl',
            'product_name' => 'Stone Bowl',
            'quantity' => 1,
            'unit_price_minor' => 2400,
            'discount_minor' => 0,
            'total_minor' => 2400,
            'price_snapshot' => ['price_minor' => 2400],
        ]);

        Livewire::test(CustomerCheckout::class)
            ->set('mode', 'login')
            ->set('loginEmail', 'customer@example.test')
            ->set('loginPassword', 'password')
            ->call('loginWithPassword')
            ->assertHasNoErrors()
            ->assertRedirect(route('shops.checkout.summary', ['shop' => $shop->slug]));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('converted', $cart->fresh()->status);
        $this->assertSame($user->id, $cart->fresh()->user_id);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'cart_id' => $cart->id,
            'total_minor' => 2400,
        ]);
    }

    public function test_guest_checkout_entry_redirects_to_checkout_login(): void
    {
        $shop = $this->shop('south-room');
        $shop->makeCurrent();

        $this->get(route('shops.checkout', ['shop' => $shop->slug]))
            ->assertRedirect(route('shops.checkout.login', ['shop' => $shop->slug]));

        $this->get(route('shops.checkout.login', ['shop' => $shop->slug]))
            ->assertOk()
            ->assertSee('Sign in to continue.')
            ->assertSee(route('shops.checkout.register', ['shop' => $shop->slug]), false);

        $this->get(route('shops.checkout.register', ['shop' => $shop->slug]))
            ->assertOk()
            ->assertSee('Create your account.')
            ->assertSee(route('shops.checkout.login', ['shop' => $shop->slug]), false);
    }

    public function test_authenticated_checkout_entry_redirects_to_summary(): void
    {
        $shop = $this->shop('east-room');
        $shop->makeCurrent();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->attach(Role::query()->where('slug', SystemRole::Customer->value)->firstOrFail());

        $this->actingAs($user)
            ->get(route('shops.checkout', ['shop' => $shop->slug]))
            ->assertRedirect(route('shops.checkout.summary', ['shop' => $shop->slug]));
    }

    private function shop(string $slug = 'still-house'): Shop
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->roles()->attach(Role::query()->where('slug', SystemRole::Owner->value)->firstOrFail());

        $shop = Shop::query()->create([
            'owner_id' => $owner->id,
            'onboarding_id' => ShopOnboarding::query()->create([
                'user_id' => $owner->id,
                'shop_name' => str($slug)->replace('-', ' ')->title()->toString(),
                'shop_slug' => $slug,
                'status' => OnboardingStatus::Provisioned,
                'completed_at' => now(),
            ])->id,
            'name' => str($slug)->replace('-', ' ')->title()->toString(),
            'slug' => $slug,
            'domain' => $slug.'.localhost',
            'database' => 'tenant_'.str_replace('-', '_', $slug),
            'status' => ShopStatus::Active,
            'is_accessible' => true,
        ]);

        $owner->forceFill(['shop_id' => $shop->id])->save();

        return $shop;
    }
}
