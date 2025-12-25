<x-layout :title="'Versions for ' . $project->display_name">
    <h1>Versions for <a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></h1>
    <a href="{{ route('web.projects.versions.create', $project) }}" class="btn btn-primary mb-3">Create Version</a>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Enabled</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($versions as $version)
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
            @endforeach
        </tbody>
    </table>

    {{ $versions->links() }}
</x-layout>
