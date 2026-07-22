<?php

namespace App\Application\Tenancy;

use App\Domain\Identity\SystemRole;
use App\Domain\Tenancy\OnboardingStatus;
use App\Models\Role;
use App\Models\ShopOnboarding;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;

final class RegisterOwner
{
    /** @param array{name: string, email: string, password: string, shop_name: string, shop_slug: string} $attributes */
    public function handle(array $attributes): User
    {
        $user = DB::transaction(function () use ($attributes): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
            ]);

            $ownerRole = Role::query()->where('slug', SystemRole::Owner->value)->firstOrFail();
            $user->roles()->attach($ownerRole);

            ShopOnboarding::query()->create([
                'user_id' => $user->id,
                'shop_name' => $attributes['shop_name'],
                'shop_slug' => $attributes['shop_slug'],
                'status' => OnboardingStatus::AwaitingEmailVerification,
            ]);

            return $user;
        });

        if (config('noma.auto_verify_emails') && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        event(new Registered($user));

        return $user;
    }
}
