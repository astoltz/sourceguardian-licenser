<x-layout title="Create Variation">
    <h1>Create Variation for {{ $project->display_name }}</h1>

    <form action="{{ route('web.projects.variations.store', $project) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="display_name" name="display_name" required>
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" checked>
            <label class="form-check-label" for="enabled">Enabled</label>
        </div>

        <hr>

        <x-key-value-manager label="Variation Constants" name="variation_constants" />
        <x-list-manager label="Variation Header Texts" name="variation_header_texts" />

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</x-layout>
