<?php

namespace Tests\Feature\Admin;

use App\Domain\Identity\SystemRole;
use App\Domain\Tenancy\OnboardingStatus;
use App\Domain\Tenancy\ShopStatus;
use App\Models\CatalogCategory;
use App\Models\CatalogProduct;
use App\Models\Role;
use App\Models\Shop;
use App\Models\ShopOnboarding;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_owner_can_manage_shop_catalog_and_public_storefront_is_shop_scoped(): void
    {
        [$owner, $shop] = $this->ownerWithShop('still-house');
        [, $otherShop] = $this->ownerWithShop('north-yard');

        $this->actingAs($owner)->post(route('admin.categories.store'), [
            'name' => 'Lighting',
            'slug' => 'lighting',
            'is_active' => '1',
            'sort_order' => 10,
        ])->assertRedirect(route('admin.categories.index'));

        $category = CatalogCategory::query()->where('shop_id', $shop->id)->firstOrFail();

        $this->post(route('admin.products.store'), [
            'name' => 'Arc Table Lamp',
            'slug' => 'arc-table-lamp',
            'description' => 'A sculptural pool of warm light.',
            'price_minor' => 18900,
            'currency' => 'USD',
            'primary_image_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c',
            'colors' => '#171814, #d2cec0',
            'is_featured' => '1',
            'is_new' => '1',
            'status' => 'active',
            'category_ids' => [$category->id],
        ])->assertRedirect(route('admin.products.index'));

        CatalogProduct::query()->create([
            'shop_id' => $otherShop->id,
            'slug' => 'private-chair',
            'name' => 'Private Chair',
            'description' => 'Another shop product.',
            'price_minor' => 42000,
            'currency' => 'USD',
            'primary_image_url' => 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c',
            'colors' => ['#171814'],
            'status' => 'active',
            'published_at' => now(),
        ]);

        $this->get($shop->publicUrl())
            ->assertOk()
            ->assertSee('Arc Table Lamp')
            ->assertSee('Lighting')
            ->assertDontSee('Private Chair');
    }

    /** @return array{User, Shop} */
    private function ownerWithShop(string $slug): array
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

        return [$owner, $shop];
    }
}
