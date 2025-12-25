<x-layout :title="'Versions for ' . $project->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Versions for <a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></h1>
        <a href="{{ route('web.projects.versions.create', $project) }}" class="btn btn-primary">Create Version</a>
    </div>
    <p class="text-muted">Versions represent specific releases of your project.</p>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Enabled</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($versions as $version)
                <tr>
                    <td><a href="{{ route('web.projects.versions.show', [$project, $version]) }}">{{ $version->display_name }}</a></td>
                    <td>{{ $version->enabled ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('web.projects.versions.edit', [$project, $version]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('web.projects.versions.destroy', [$project, $version]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No versions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $versions->links() }}
</x-layout>
