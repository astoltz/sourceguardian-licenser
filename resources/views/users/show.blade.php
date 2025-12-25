<x-layout :title="$user->name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ $user->name }}</h1>
        <div>
            <a href="{{ route('web.users.edit', $user) }}" class="btn btn-warning">Edit</a>
            @if($user->id !== auth()->id())
                <form action="{{ route('web.users.destroy', $user) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            @endif
        </div>
    </div>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Joined:</strong> {{ $user->created_at->format('Y-m-d') }}</p>

    <a href="{{ route('web.users.index') }}" class="btn btn-secondary">Back to Users</a>
</x-layout>
