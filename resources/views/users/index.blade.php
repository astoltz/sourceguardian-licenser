<x-layout title="Users">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Users</h1>
        <a href="{{ route('web.users.create') }}" class="btn btn-primary">Create User</a>
    </div>
    <p class="text-muted">Users are administrators who can access this dashboard and manage licenses.</p>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td><a href="{{ route('web.users.show', $user) }}">{{ $user->name }}</a></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ route('web.users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a>
                        @if($user->id !== auth()->id())
                            <form action="{{ route('web.users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $users->links() }}
</x-layout>
