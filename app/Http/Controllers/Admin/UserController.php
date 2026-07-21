<?php

namespace App\Http\Controllers\Admin;

use App\Application\Identity\AssignRolesToUser;
use App\Domain\Identity\SystemRole;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $actor = $request->user();
        $users = User::query()
            ->with('roles')
            ->when(! $actor->hasRole(SystemRole::Administrator->value), fn ($query) => $query->where('shop_id', $actor->shop_id))
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"),
            ))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function edit(Request $request, User $user): View
    {
        abort_unless($this->canManageUser($request->user(), $user), 404);

        return view('admin.users.edit', [
            'managedUser' => $user->load('roles'),
            'roles' => Role::query()
                ->when(! $request->user()->hasRole(SystemRole::Administrator->value), fn ($query) => $query->whereNotIn('slug', [
                    SystemRole::Owner->value,
                    SystemRole::Administrator->value,
                ]))
                ->where('slug', '!=', SystemRole::Owner->value)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, User $user, AssignRolesToUser $assignRoles): RedirectResponse
    {
        abort_unless($this->canManageUser($request->user(), $user), 404);

        $validated = $request->validate([
            'roles' => ['present', 'array'],
            'roles.*' => ['integer', 'distinct', Rule::exists('roles', 'id')],
        ]);

        $assignRoles->handle($user, $validated['roles'], $request->user());

        return redirect()->route('admin.users.index')->with('status', "Roles updated for {$user->name}.");
    }

    private function canManageUser(User $actor, User $user): bool
    {
        return $actor->hasRole(SystemRole::Administrator->value)
            || ($actor->shop_id !== null && $actor->shop_id === $user->shop_id);
    }
}
