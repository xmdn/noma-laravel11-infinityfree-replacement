<?php

namespace Database\Seeders;

use App\Domain\Identity\SystemRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $email = env('NOMA_ADMIN_EMAIL');
        $password = env('NOMA_ADMIN_PASSWORD');

        if ($email && $password) {
            $administrator = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => env('NOMA_ADMIN_NAME', 'NOMA Administrator'),
                    'password' => $password,
                    'email_verified_at' => now(),
                ],
            );
            $administrator->roles()->syncWithoutDetaching(
                [Role::query()->where('slug', SystemRole::Administrator->value)->firstOrFail()->getKey()],
            );
        }
    }
}
