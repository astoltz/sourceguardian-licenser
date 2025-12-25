<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Version;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * API Controller for managing versions.
 */
class VersionController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of versions for a project.
     *
     * @param Project $project
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Project $project)
    {
        return $project->versions()->with(['versionConstants', 'versionHeaderTexts'])->paginate(15);
    }

    /**
     * Store a newly created version in storage.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'enabled' => 'boolean',
        ]);

        $version = $project->versions()->create($validated);

        return response()->json($version, 201);
    }

    /**
     * Display the specified version.
     *
     * @param Project $project
     * @param Version $version
     * @return Version
     */
    public function show(Project $project, Version $version)
    {
        return $version->load([
            'project',
            'versionConstants',
            'versionHeaderTexts'
        ]);
    }

    /**
     * Update the specified version in storage.
     *
     * @param Request $request
     * @param Project $project
     * @param Version $version
     * @return Version
     */
    public function update(Request $request, Project $project, Version $version)
    {
        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'enabled' => 'boolean',
            'version_constants' => 'sometimes|array',
            'version_header_texts' => 'sometimes|array',
        ]);

        $version->update($validated);

        if (isset($validated['version_constants'])) {
            $this->syncHasMany($version->versionConstants(), $validated['version_constants']);
        }

        if (isset($validated['version_header_texts'])) {
            $this->syncHasMany($version->versionHeaderTexts(), $validated['version_header_texts']);
        }

        return $version->load(['versionConstants', 'versionHeaderTexts']);
    }

    /**
     * Remove the specified version from storage.
     *
     * @param Project $project
     * @param Version $version
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy(Project $project, Version $version)
    {
        try {
            $version->delete();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->noContent();
    }
}
