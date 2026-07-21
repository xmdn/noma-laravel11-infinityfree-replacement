<?php

namespace Tests\Feature\Admin;

use App\Domain\Identity\SystemRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $administrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->administrator = User::factory()->create();
        $this->administrator->roles()->attach($this->role(SystemRole::Administrator));
    }

    public function test_administrator_can_assign_roles(): void
    {
        $user = User::factory()->create();
        $support = $this->role(SystemRole::Support);

        $this->actingAs($this->administrator)
            ->put(route('admin.users.update', $user), ['roles' => [$support->id]])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue($user->fresh()->hasRole(SystemRole::Support->value));
    }

    public function test_administrator_cannot_remove_their_own_admin_role(): void
    {
        $this->actingAs($this->administrator)
            ->from(route('admin.users.edit', $this->administrator))
            ->put(route('admin.users.update', $this->administrator), ['roles' => []])
            ->assertRedirect(route('admin.users.edit', $this->administrator))
            ->assertSessionHasErrors('roles');

        $this->assertTrue($this->administrator->fresh()->hasRole(SystemRole::Administrator->value));
    }

    public function test_owner_role_cannot_be_granted_through_generic_role_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->administrator)
            ->put(route('admin.users.update', $user), ['roles' => [$this->role(SystemRole::Owner)->id]])
            ->assertSessionHasErrors('roles');

        $this->assertFalse($user->fresh()->hasRole(SystemRole::Owner->value));
    }

    private function role(SystemRole $role): Role
    {
        return Role::query()->where('slug', $role->value)->firstOrFail();
    }
}
