<?php

namespace Tests\Feature\Owner;

use App\Domain\Identity\PermissionName;
use App\Domain\Identity\SystemRole;
use App\Domain\Tenancy\OnboardingStatus;
use App\Models\Shop;
use App\Models\ShopOnboarding;
use App\Models\User;
use App\Notifications\QueuedVerifyEmail;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OwnerOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_business_owner_can_register_and_receives_verification_email(): void
    {
        Notification::fake();

        $this->post(route('owner.register'), [
            'name' => 'Avery Stone',
            'email' => 'avery@example.com',
            'shop_name' => 'Still House',
            'shop_slug' => 'Still House',
            'password' => 'considered-password',
            'password_confirmation' => 'considered-password',
        ])->assertRedirect(route('verification.notice'));

        $owner = User::query()->where('email', 'avery@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($owner);
        $this->assertFalse($owner->hasVerifiedEmail());
        $this->assertTrue($owner->hasRole(SystemRole::Owner->value));
        $this->assertTrue($owner->hasPermission(PermissionName::ManageShopSettings->value));
        $this->assertDatabaseHas('shop_onboardings', [
            'user_id' => $owner->id,
            'shop_name' => 'Still House',
            'shop_slug' => 'still-house',
            'status' => OnboardingStatus::AwaitingEmailVerification->value,
        ]);
        $this->assertDatabaseCount('shops', 0);
        Notification::assertSentTo($owner, QueuedVerifyEmail::class);
    }

    public function test_shop_is_created_only_after_owner_verifies_email(): void
    {
        Notification::fake();
        $owner = $this->registerOwner();

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('verification.notice'));
        $this->assertDatabaseCount('shops', 0);

        $response = $this->actingAs($owner)->get($this->verificationUrl($owner));
        $bridgeUrl = $this->assertRedirectToTenantBridge($response, 'still-house');
        $this->get($bridgeUrl)->assertRedirect('/dashboard');

        $owner->refresh();
        $this->assertTrue($owner->hasVerifiedEmail());
        $this->assertNotNull($owner->shop_id);
        $this->assertDatabaseHas('shops', [
            'owner_id' => $owner->id,
            'name' => 'Still House',
            'slug' => 'still-house',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('shop_onboardings', [
            'user_id' => $owner->id,
            'status' => OnboardingStatus::Provisioned->value,
        ]);
    }

    public function test_owner_can_verify_from_signed_email_link_without_existing_session(): void
    {
        Notification::fake();
        $owner = $this->registerOwner();

        $this->app['auth']->logout();
        $this->assertGuest();

        $response = $this->get($this->verificationUrl($owner));
        $bridgeUrl = $this->assertRedirectToTenantBridge($response, 'still-house');
        $this->get($bridgeUrl)->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($owner);
        $this->assertTrue($owner->fresh()->hasVerifiedEmail());
        $this->assertDatabaseHas('shops', [
            'owner_id' => $owner->id,
            'slug' => 'still-house',
            'status' => 'active',
        ]);
    }

    public function test_shop_provisioning_is_idempotent(): void
    {
        Notification::fake();
        $owner = $this->registerOwner();

        $bridgeUrl = $this->assertRedirectToTenantBridge(
            $this->actingAs($owner)->get($this->verificationUrl($owner)),
            'still-house',
        );
        $this->get($bridgeUrl)->assertRedirect('/dashboard');
        $this->post(route('owner.onboarding.retry'))->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('shops', 1);
        $this->assertSame($owner->id, Shop::query()->sole()->owner_id);
    }

    public function test_owner_login_on_platform_enters_their_shop_session(): void
    {
        Notification::fake();
        $owner = $this->registerOwner();
        $bridgeUrl = $this->assertRedirectToTenantBridge(
            $this->actingAs($owner)->get($this->verificationUrl($owner)),
            'still-house',
        );
        $this->get($bridgeUrl);
        $this->post(url('/logout'));

        $response = $this->post(config('app.url').'/login', [
            'email' => $owner->email,
            'password' => 'considered-password',
        ]);

        $bridgeUrl = $this->assertRedirectToTenantBridge($response, 'still-house');
        $this->get($bridgeUrl)->assertRedirect('/dashboard');

        $this->get('http://still-house.localhost/dashboard')
            ->assertOk()
            ->assertSee('Still House');
    }

    public function test_verified_owner_can_update_shop_settings(): void
    {
        Notification::fake();
        $owner = $this->registerOwner();
        $this->actingAs($owner)->get($this->verificationUrl($owner));

        $this->get(route('admin.shop.settings.edit'))
            ->assertOk()
            ->assertSee('Still House');

        $this->patch(route('admin.shop.settings.update'), [
            'name' => 'Still House London',
            'currency' => 'GBP',
            'timezone' => 'Europe/London',
        ])->assertRedirect(route('admin.shop.settings.edit'));

        $this->assertDatabaseHas('shops', [
            'owner_id' => $owner->id,
            'name' => 'Still House London',
            'currency' => 'GBP',
            'timezone' => 'Europe/London',
        ]);
    }

    public function test_owner_registration_enforces_one_reserved_shop_slug(): void
    {
        Notification::fake();
        ShopOnboarding::query()->create([
            'user_id' => User::factory()->create()->id,
            'shop_name' => 'Still House',
            'shop_slug' => 'still-house',
            'status' => OnboardingStatus::AwaitingEmailVerification,
        ]);

        $this->post(route('owner.register'), [
            'name' => 'Another Owner',
            'email' => 'another@example.com',
            'shop_name' => 'Still House Again',
            'shop_slug' => 'still-house',
            'password' => 'considered-password',
            'password_confirmation' => 'considered-password',
        ])->assertSessionHasErrors('shop_slug');

        $this->assertDatabaseMissing('users', ['email' => 'another@example.com']);
    }

    private function registerOwner(): User
    {
        $this->post(route('owner.register'), [
            'name' => 'Avery Stone',
            'email' => 'avery@example.com',
            'shop_name' => 'Still House',
            'shop_slug' => 'still-house',
            'password' => 'considered-password',
            'password_confirmation' => 'considered-password',
        ]);

        return User::query()->where('email', 'avery@example.com')->firstOrFail();
    }

    private function verificationUrl(User $owner): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $owner->getKey(),
            'hash' => sha1($owner->getEmailForVerification()),
        ]);
    }

    private function assertRedirectToTenantBridge(TestResponse $response, string $shopSlug): string
    {
        $location = $response->assertRedirect()->headers->get('Location');

        $this->assertIsString($location);
        $this->assertMatchesRegularExpression("#^http://{$shopSlug}\.localhost(?::\d+)?/auth/bridge/#", $location);

        return $location;
    }
}
