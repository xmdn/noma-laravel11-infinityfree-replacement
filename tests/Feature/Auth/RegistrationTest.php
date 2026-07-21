<?php

namespace Tests\Feature\Auth;

use App\Domain\Identity\SystemRole;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->post(route('register'), [
            'name' => 'Mina Reed',
            'email' => 'mina@example.com',
            'password' => 'considered-password',
            'password_confirmation' => 'considered-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'mina@example.com']);
        $this->assertTrue($this->app['auth']->user()->hasRole(SystemRole::Customer->value));
    }

    public function test_registration_validates_email_and_password_confirmation(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->post(route('register'), [
            'name' => 'Mina Reed',
            'email' => 'not-an-email',
            'password' => 'considered-password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }
}
