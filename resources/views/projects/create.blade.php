<x-layout title="Create Project">
    <h1>Create Project</h1>
    <p class="text-muted">Create a new project to group your licensed applications.</p>

    <form action="{{ route('web.projects.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="display_name" name="display_name" required>
            <div class="form-text">A human-readable name for the project (e.g., "My Awesome App").</div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project ID</label>
                    <input type="text" class="form-control" id="project_id" name="project_id" placeholder="Leave blank to auto-generate">
                    <div class="form-text">The unique ID for this project, used by SourceGuardian. Must match the `--projid` used during encoding.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="project_key" class="form-label">Project Key</label>
                    <input type="text" class="form-control" id="project_key" name="project_key" placeholder="Leave blank to auto-generate">
                    <div class="form-text">The secret key for this project, used by SourceGuardian. Must match the `--projkey` used during encoding.</div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="license_filename" class="form-label">Default License Filename</label>
            <input type="text" class="form-control" id="license_filename" name="license_filename" placeholder="license.lic">
            <div class="form-text">The default filename for downloaded license files.</div>
        </div>

        <div class="mb-3 form-check">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" checked>
            <label class="form-check-label" for="enabled">Enabled</label>
        </div>

        <hr>

        <x-key-value-manager label="Project Constants" name="project_constants" />
        <x-list-manager label="Project Header Texts" name="project_header_texts" />
        <x-simple-list-manager label="Time Servers" name="project_time_servers" />

        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</x-layout>
