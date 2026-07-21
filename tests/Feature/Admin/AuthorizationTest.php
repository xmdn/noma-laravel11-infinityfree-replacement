<?php

namespace Tests\Feature\Admin;

use App\Domain\Identity\SystemRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_administration(): void
    {
        $customer = User::factory()->create();
        $customer->roles()->attach($this->role(SystemRole::Customer));

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_manager_can_access_admin_but_not_user_management(): void
    {
        $manager = User::factory()->create();
        $manager->roles()->attach($this->role(SystemRole::CatalogManager));

        $this->actingAs($manager)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('CATALOG')
            ->assertDontSee('Manage users');
        $this->actingAs($manager)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('admin.shop.settings.edit'))->assertForbidden();
    }

    public function test_administrator_can_access_user_management(): void
    {
        $administrator = User::factory()->create();
        $administrator->roles()->attach($this->role(SystemRole::Administrator));

        $this->actingAs($administrator)->get(route('admin.users.index'))->assertOk();
    }

    private function role(SystemRole $role): Role
    {
        return Role::query()->where('slug', $role->value)->firstOrFail();
    }
}
