<?php

namespace App\Application\Identity;

use App\Domain\Identity\SystemRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class AssignRolesToUser
{
    /** @param list<int> $roleIds */
    public function handle(User $user, array $roleIds, User $actor): void
    {
        $ownerId = Role::query()->where('slug', SystemRole::Owner->value)->value('id');
        $administratorId = Role::query()
            ->where('slug', SystemRole::Administrator->value)
            ->value('id');

        if ($user->is($actor) && $administratorId && ! in_array($administratorId, $roleIds, true)) {
            throw ValidationException::withMessages([
                'roles' => 'You cannot remove your own administrator role.',
            ]);
        }

        if ($ownerId && in_array($ownerId, $roleIds, true) && ! $user->hasRole(SystemRole::Owner->value)) {
            throw ValidationException::withMessages([
                'roles' => 'The owner role is granted only through verified shop onboarding.',
            ]);
        }

        if ($ownerId && $user->hasRole(SystemRole::Owner->value)) {
            $roleIds[] = $ownerId;
        }

        if ($user->hasRole(SystemRole::Administrator->value)
            && $administratorId
            && ! in_array($administratorId, $roleIds, true)
            && Role::query()->findOrFail($administratorId)->users()->count() === 1) {
            throw ValidationException::withMessages([
                'roles' => 'The last administrator role cannot be removed.',
            ]);
        }

        $user->roles()->sync(array_values(array_unique($roleIds)));
    }
}
