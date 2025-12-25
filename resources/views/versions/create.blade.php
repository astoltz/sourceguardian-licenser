<x-layout title="Create Version">
    <h1>Create Version for {{ $project->display_name }}</h1>

    <form action="{{ route('web.projects.versions.store', $project) }}" method="POST">
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
        <h5>Overrides</h5>
        <p class="text-muted">These fields are optional. If set, they will override the main project settings when generating a license for this version.</p>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="override_project_id" class="form-label">Override Project ID</label>
                    <input type="text" class="form-control" id="override_project_id" name="override_project_id" placeholder="Leave blank to use project default">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="override_project_key" class="form-label">Override Project Key</label>
                    <input type="text" class="form-control" id="override_project_key" name="override_project_key" placeholder="Leave blank to use project default">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="override_license_filename" class="form-label">Override License Filename</label>
            <input type="text" class="form-control" id="override_license_filename" name="override_license_filename" placeholder="Leave blank to use project default">
        </div>

        <hr>

        <x-key-value-manager label="Version Constants" name="version_constants" />
        <x-list-manager label="Version Header Texts" name="version_header_texts" />

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</x-layout>
