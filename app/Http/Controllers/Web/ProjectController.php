<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * Controller for managing projects via the Web UI.
 */
class ProjectController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of projects.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $projects = Project::withCount(['versions', 'variations'])->paginate(15);
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created project in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'project_id' => 'nullable|string',
            'project_key' => 'nullable|string',
            'enabled' => 'sometimes|boolean',
            'project_constants' => 'sometimes|array',
            'project_header_texts' => 'sometimes|array',
            'project_time_servers' => 'sometimes|array',
        ]);

        $project = Project::create($validated);

        if (isset($validated['project_constants'])) {
            $this->syncHasMany($project->projectConstants(), $validated['project_constants']);
        }
        if (isset($validated['project_header_texts'])) {
            $this->syncHasMany($project->projectHeaderTexts(), $validated['project_header_texts']);
        }
        if (isset($validated['project_time_servers'])) {
            $this->syncHasMany($project->projectTimeServers(), $validated['project_time_servers']);
        }

        return redirect()->route('web.projects.show', $project)->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function show(Project $project)
    {
        $project->load(['projectConstants', 'projectTimeServers', 'projectHeaderTexts']);
        $versions = $project->versions()->paginate(5, ['*'], 'versionsPage');
        $variations = $project->variations()->paginate(5, ['*'], 'variationsPage');

        return view('projects.show', compact('project', 'versions', 'variations'));
    }

    /**
     * Show the form for editing the specified project.
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function edit(Project $project)
    {
        $project->load(['projectConstants', 'projectHeaderTexts', 'projectTimeServers']);
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified project in storage.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'project_id' => 'sometimes|string',
            'project_key' => 'sometimes|string',
            'enabled' => 'sometimes|boolean',
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

        return redirect()->route('web.projects.show', $project)->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     *
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return redirect()->route('web.projects.index')->with('success', 'Project deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
