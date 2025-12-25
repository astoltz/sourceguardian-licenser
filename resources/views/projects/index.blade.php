<x-layout title="Projects">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Projects</h1>
        <a href="{{ route('web.projects.create') }}" class="btn btn-primary">Create Project</a>
    </div>
    <p class="text-muted">Projects are the top-level containers for your applications. Each project has its own unique ID and Key for license generation.</p>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Versions</th>
                <th>Variations</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td><a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></td>
                    <td><a href="{{ route('web.projects.versions.index', $project) }}">{{ $project->versions_count }}</a></td>
                    <td><a href="{{ route('web.projects.variations.index', $project) }}">{{ $project->variations_count }}</a></td>
                    <td>
                        <a href="{{ route('web.projects.edit', $project) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('web.projects.destroy', $project) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No projects found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $projects->links() }}
</x-layout>
