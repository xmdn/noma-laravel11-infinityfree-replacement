<x-layouts.auth title="Edit user access">
    <section class="page-heading compact-heading">
        <p class="eyebrow">Administration / Users</p>
        <h1>{{ $managedUser->name }}</h1>
        <p class="lede">{{ $managedUser->email }}</p>
    </section>

    <form method="POST" action="{{ route('admin.users.update', $managedUser) }}" class="role-form">
        @csrf
        @method('PUT')
        <fieldset>
            <legend>Assigned roles</legend>
            @foreach ($roles as $role)
                <label class="role-option">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($managedUser->roles->contains($role))>
                    <span><strong>{{ $role->name }}</strong><small>{{ $role->slug }}</small></span>
                </label>
            @endforeach
        </fieldset>
        @error('roles') <p class="field-error">{{ $message }}</p> @enderror
        @error('roles.*') <p class="field-error">{{ $message }}</p> @enderror
        <div class="form-actions"><a href="{{ route('admin.users.index') }}">Cancel</a><button class="primary-button" type="submit">Save roles <span>→</span></button></div>
    </form>
</x-layouts.auth>
