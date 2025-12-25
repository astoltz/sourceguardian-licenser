<x-layout :title="$variation->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ $variation->display_name }}</h1>
        <div>
            <a href="{{ route('web.licenses.index', ['variation_id' => $variation->id]) }}" class="btn btn-info">View Licenses</a>
            <a href="{{ route('web.projects.variations.edit', [$project, $variation]) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('web.projects.variations.destroy', [$project, $variation]) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>
    <p>Project: <a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></p>

    @if($variation->variationConstants->isNotEmpty())
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
                    @foreach($variation->variationConstants as $constant)
                        <tr>
                            <td><code>{{ $constant->key }}</code></td>
                            <td><code>{{ $constant->data }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($variation->variationHeaderTexts->isNotEmpty())
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
                    @foreach($variation->variationHeaderTexts as $text)
                        <tr>
                            <td>{{ $text->data }}</td>
                            <td>{{ $text->order }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('web.projects.variations.index', $project) }}" class="btn btn-secondary">Back to Variations</a>
</x-layout>
