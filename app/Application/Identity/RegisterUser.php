<?php

namespace App\Application\Identity;

use App\Domain\Identity\SystemRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

final class RegisterUser
{
    /** @param array{name: string, email: string, password: string} $attributes */
    public function handle(array $attributes): User
    {
        $user = DB::transaction(function () use ($attributes): User {
            $user = User::query()->create($attributes);
            $customerRole = Role::query()->where('slug', SystemRole::Customer->value)->firstOrFail();

            $user->roles()->attach($customerRole);

            return $user;
        });

        event(new Registered($user));

        return $user;
    }
}
