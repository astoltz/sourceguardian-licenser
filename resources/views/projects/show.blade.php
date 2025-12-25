<x-layout :title="$project->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ $project->display_name }}</h1>
        <div>
            <a href="{{ route('web.licenses.index', ['project_id' => $project->id]) }}" class="btn btn-info">View Licenses</a>
            <a href="{{ route('web.projects.edit', $project) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('web.projects.destroy', $project) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Details</div>
        <div class="card-body">
            <p><strong>Project ID:</strong> <code>{{ $project->project_id }}</code></p>
            <p><strong>Project Key:</strong> <code>********************</code></p>
        </div>
    </div>

    @if($project->projectConstants->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Constants</div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->projectConstants as $constant)
                        <tr>
                            <td><code>{{ $constant->key }}</code></td>
                            <td><code>{{ $constant->data }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($project->projectHeaderTexts->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Header Texts</div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Text</th>
                        <th>Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->projectHeaderTexts as $text)
                        <tr>
                            <td>{{ $text->data }}</td>
                            <td>{{ $text->order }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($project->projectTimeServers->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Time Servers</div>
            <ul class="list-group list-group-flush">
                @foreach($project->projectTimeServers as $server)
                    <li class="list-group-item">{{ $server->data }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Versions</span>
            <a href="{{ route('web.projects.versions.index', $project) }}" class="btn btn-sm btn-primary">Manage All Versions</a>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($versions as $version)
                <li class="list-group-item">
                    <a href="{{ route('web.projects.versions.show', [$project, $version]) }}">{{ $version->display_name }}</a>
                </li>
            @endforeach
        </ul>
        <div class="card-footer">
            {{ $versions->links() }}
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Variations</span>
            <a href="{{ route('web.projects.variations.index', $project) }}" class="btn btn-sm btn-primary">Manage All Variations</a>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($variations as $variation)
                <li class="list-group-item">
                    <a href="{{ route('web.projects.variations.show', [$project, $variation]) }}">{{ $variation->display_name }}</a>
                </li>
            @endforeach
        </ul>
        <div class="card-footer">
            {{ $variations->links() }}
        </div>
    </div>

    <a href="{{ route('web.projects.index') }}" class="btn btn-secondary">Back to Projects</a>
</x-layout>
