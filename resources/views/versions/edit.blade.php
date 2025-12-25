<x-layout title="Edit Version">
    <h1>Edit Version for {{ $project->display_name }}</h1>

    <form action="{{ route('web.projects.versions.update', [$project, $version]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="display_name" name="display_name" value="{{ $version->display_name }}" required>
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" @if($version->enabled) checked @endif>
            <label class="form-check-label" for="enabled">Enabled</label>
        </div>

        <hr>

        <x-key-value-manager label="Version Constants" name="version_constants" :items="$version->versionConstants" />
        <x-list-manager label="Version Header Texts" name="version_header_texts" :items="$version->versionHeaderTexts" />

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</x-layout>
