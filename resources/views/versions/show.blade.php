<x-layout :title="$version->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ $version->display_name }}</h1>
        <div>
            <a href="{{ route('web.licenses.index', ['version_id' => $version->id]) }}" class="btn btn-info">View Licenses</a>
            <a href="{{ route('web.projects.versions.edit', [$project, $version]) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('web.projects.versions.destroy', [$project, $version]) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>
    <p>Project: <a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></p>

    @if($version->versionConstants->isNotEmpty())
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
                    @foreach($version->versionConstants as $constant)
                        <tr>
                            <td><code>{{ $constant->key }}</code></td>
                            <td><code>{{ $constant->data }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($version->versionHeaderTexts->isNotEmpty())
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
                    @foreach($version->versionHeaderTexts as $text)
                        <tr>
                            <td>{{ $text->data }}</td>
                            <td>{{ $text->order }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('web.projects.versions.index', $project) }}" class="btn btn-secondary">Back to Versions</a>
</x-layout>
