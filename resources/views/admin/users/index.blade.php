<x-layouts.auth title="Users">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Users</p>
        <h1>User access.</h1>
    </section>

    <form method="GET" class="search-form" role="search">
        <label for="search">Search users</label>
        <div><input id="search" name="search" value="{{ $search }}" placeholder="Name or email"><button type="submit">Search</button></div>
    </form>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->join(', ') ?: 'None' }}</td>
                    <td><a href="{{ route('admin.users.edit', $user) }}">Edit access</a></td>
                </tr>
            @empty
                <tr><td colspan="4">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</x-layouts.auth>
