<x-layout title="Edit Project">
    <h1>Edit Project</h1>

    <form action="{{ route('web.projects.update', $project) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="display_name" name="display_name" value="{{ $project->display_name }}" required>
            <div class="form-text">A human-readable name for the project.</div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project ID</label>
                    <input type="text" class="form-control" id="project_id" name="project_id" value="{{ $project->project_id }}">
                    <div class="form-text">The unique ID for this project.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="project_key" class="form-label">Project Key</label>
                    <input type="text" class="form-control" id="project_key" name="project_key" value="{{ $project->project_key }}">
                    <div class="form-text">The secret key for this project.</div>
                </div>
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" @if($project->enabled) checked @endif>
            <label class="form-check-label" for="enabled">Enabled</label>
        </div>

        <hr>

        <x-key-value-manager label="Project Constants" name="project_constants" :items="$project->projectConstants" />
        <x-list-manager label="Project Header Texts" name="project_header_texts" :items="$project->projectHeaderTexts" />
        <x-simple-list-manager label="Time Servers" name="project_time_servers" :items="$project->projectTimeServers" />

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</x-layout>
