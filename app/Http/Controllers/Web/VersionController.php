<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Version;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * Controller for managing versions via the Web UI.
 */
class VersionController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of versions for a project.
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function index(Project $project)
    {
        $versions = $project->versions()->paginate(15);
        return view('versions.index', compact('project', 'versions'));
    }

    /**
     * Show the form for creating a new version.
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function create(Project $project)
    {
        return view('versions.create', compact('project'));
    }

    /**
     * Store a newly created version in storage.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'enabled' => 'sometimes|boolean',
            'version_constants' => 'sometimes|array',
            'version_header_texts' => 'sometimes|array',
        ]);

        $version = $project->versions()->create($validated);

        if (isset($validated['version_constants'])) {
            $this->syncHasMany($version->versionConstants(), $validated['version_constants']);
        }
        if (isset($validated['version_header_texts'])) {
            $this->syncHasMany($version->versionHeaderTexts(), $validated['version_header_texts']);
        }

        return redirect()->route('web.projects.versions.show', [$project, $version])->with('success', 'Version created successfully.');
    }

    /**
     * Display the specified version.
     *
     * @param Project $project
     * @param Version $version
     * @return \Illuminate\View\View
     */
    public function show(Project $project, Version $version)
    {
        $version->load(['versionConstants', 'versionHeaderTexts']);
        return view('versions.show', compact('project', 'version'));
    }

    /**
     * Show the form for editing the specified version.
     *
     * @param Project $project
     * @param Version $version
     * @return \Illuminate\View\View
     */
    public function edit(Project $project, Version $version)
    {
        $version->load(['versionConstants', 'versionHeaderTexts']);
        return view('versions.edit', compact('project', 'version'));
    }

    /**
     * Update the specified version in storage.
     *
     * @param Request $request
     * @param Project $project
     * @param Version $version
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project, Version $version)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
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

        return redirect()->route('web.projects.versions.show', [$project, $version])->with('success', 'Version updated successfully.');
    }

    /**
     * Remove the specified version from storage.
     *
     * @param Project $project
     * @param Version $version
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project, Version $version)
    {
        try {
            $version->delete();
            return redirect()->route('web.projects.versions.index', $project)->with('success', 'Version deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
