<x-layout :title="'Variations for ' . $project->display_name">
    <h1>Variations for <a href="{{ route('web.projects.show', $project) }}">{{ $project->display_name }}</a></h1>
    <a href="{{ route('web.projects.variations.create', $project) }}" class="btn btn-primary mb-3">Create Variation</a>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Enabled</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($variations as $variation)
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
            @endforeach
        </tbody>
    </table>

    {{ $variations->links() }}
</x-layout>
