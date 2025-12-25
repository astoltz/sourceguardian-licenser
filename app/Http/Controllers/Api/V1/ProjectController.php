<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * API Controller for managing projects.
 */
class ProjectController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of projects.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        return Project::with(['projectConstants', 'projectTimeServers', 'projectHeaderTexts'])->paginate(15);
    }

    /**
     * Search for projects by name or ID.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        return Project::where('display_name', 'like', "%{$query}%")
            ->orWhere('id', 'like', "%{$query}%")
            ->limit(10)
            ->get();
    }

    /**
     * Store a newly created project in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'project_id' => 'nullable|string',
            'project_key' => 'nullable|string',
            'enabled' => 'boolean',
        ]);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    /**
     * Display the specified project.
     *
     * @param Project $project
     * @return Project
     */
    public function show(Project $project)
    {
        return $project->load([
            'versions',
            'variations',
            'projectConstants',
            'projectTimeServers',
            'projectHeaderTexts'
        ]);
    }

    /**
     * Update the specified project in storage.
     *
     * @param Request $request
     * @param Project $project
     * @return Project
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'project_id' => 'sometimes|string',
            'project_key' => 'sometimes|string',
            'enabled' => 'boolean',
            'project_constants' => 'sometimes|array',
            'project_header_texts' => 'sometimes|array',
            'project_time_servers' => 'sometimes|array',
        ]);

        $project->update($validated);

        if (isset($validated['project_constants'])) {
            $this->syncHasMany($project->projectConstants(), $validated['project_constants']);
        }

        if (isset($validated['project_header_texts'])) {
            $this->syncHasMany($project->projectHeaderTexts(), $validated['project_header_texts']);
        }

        if (isset($validated['project_time_servers'])) {
            $this->syncHasMany($project->projectTimeServers(), $validated['project_time_servers']);
        }

        return $project->load(['projectConstants', 'projectHeaderTexts', 'projectTimeServers']);
    }

    /**
     * Remove the specified project from storage.
     *
     * @param Project $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->noContent();
    }
}
