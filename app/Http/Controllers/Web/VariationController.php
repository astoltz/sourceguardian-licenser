<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Variation;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * Controller for managing variations via the Web UI.
 */
class VariationController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of variations for a project.
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function index(Project $project)
    {
        $variations = $project->variations()->paginate(15);
        return view('variations.index', compact('project', 'variations'));
    }

    /**
     * Show the form for creating a new variation.
     *
     * @param Project $project
     * @return \Illuminate\View\View
     */
    public function create(Project $project)
    {
        return view('variations.create', compact('project'));
    }

    /**
     * Store a newly created variation in storage.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'enabled' => 'boolean',
            'variation_constants' => 'sometimes|array',
            'variation_header_texts' => 'sometimes|array',
        ]);

        $variation = $project->variations()->create($validated);

        if (isset($validated['variation_constants'])) {
            $this->syncHasMany($variation->variationConstants(), $validated['variation_constants']);
        }
        if (isset($validated['variation_header_texts'])) {
            $this->syncHasMany($variation->variationHeaderTexts(), $validated['variation_header_texts']);
        }

        return redirect()->route('web.projects.variations.show', [$project, $variation])->with('success', 'Variation created successfully.');
    }

    /**
     * Display the specified variation.
     *
     * @param Project $project
     * @param Variation $variation
     * @return \Illuminate\View\View
     */
    public function show(Project $project, Variation $variation)
    {
        $variation->load(['variationConstants', 'variationHeaderTexts']);
        return view('variations.show', compact('project', 'variation'));
    }

    /**
     * Show the form for editing the specified variation.
     *
     * @param Project $project
     * @param Variation $variation
     * @return \Illuminate\View\View
     */
    public function edit(Project $project, Variation $variation)
    {
        $variation->load(['variationConstants', 'variationHeaderTexts']);
        return view('variations.edit', compact('project', 'variation'));
    }

    /**
     * Update the specified variation in storage.
     *
     * @param Request $request
     * @param Project $project
     * @param Variation $variation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project, Variation $variation)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'enabled' => 'boolean',
            'variation_constants' => 'sometimes|array',
            'variation_header_texts' => 'sometimes|array',
        ]);

        $variation->update($validated);

        if (isset($validated['variation_constants'])) {
            $this->syncHasMany($variation->variationConstants(), $validated['variation_constants']);
        }
        if (isset($validated['variation_header_texts'])) {
            $this->syncHasMany($variation->variationHeaderTexts(), $validated['variation_header_texts']);
        }

        return redirect()->route('web.projects.variations.show', [$project, $variation])->with('success', 'Variation updated successfully.');
    }

    /**
     * Remove the specified variation from storage.
     *
     * @param Project $project
     * @param Variation $variation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project, Variation $variation)
    {
        try {
            $variation->delete();
            return redirect()->route('web.projects.variations.index', $project)->with('success', 'Variation deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
