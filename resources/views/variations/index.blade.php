<x-layout :title="'Variations for ' . $project->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Variations for <a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></h1>
        <a href="{{ route('web.projects.variations.create', $project) }}" class="btn btn-primary">Create Variation</a>
    </div>
    <p class="text-muted">Variations represent different editions of your project (e.g., Standard, Pro).</p>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Enabled</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($variations as $variation)
                <tr>
                    <td><a href="{{ route('web.projects.variations.show', [$project, $variation]) }}">{{ $variation->display_name }}</a></td>
                    <td>{{ $variation->enabled ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('web.projects.variations.edit', [$project, $variation]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('web.projects.variations.destroy', [$project, $variation]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No variations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $variations->links() }}
</x-layout>
