<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Variation;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;

/**
 * API Controller for managing variations.
 */
class VariationController extends Controller
{
    use SyncsHasMany;

    /**
     * Display a listing of variations for a project.
     *
     * @param Project $project
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Project $project)
    {
        return $project->variations()->with(['variationConstants', 'variationHeaderTexts'])->paginate(15);
    }

    /**
     * Store a newly created variation in storage.
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

        $variation = $project->variations()->create($validated);

        return response()->json($variation, 201);
    }

    /**
     * Display the specified variation.
     *
     * @param Project $project
     * @param Variation $variation
     * @return Variation
     */
    public function show(Project $project, Variation $variation)
    {
        return $variation->load([
            'project',
            'variationConstants',
            'variationHeaderTexts'
        ]);
    }

    /**
     * Update the specified variation in storage.
     *
     * @param Request $request
     * @param Project $project
     * @param Variation $variation
     * @return Variation
     */
    public function update(Request $request, Project $project, Variation $variation)
    {
        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
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

        return $variation->load(['variationConstants', 'variationHeaderTexts']);
    }

    /**
     * Remove the specified variation from storage.
     *
     * @param Project $project
     * @param Variation $variation
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy(Project $project, Variation $variation)
    {
        try {
            $variation->delete();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        return response()->noContent();
    }
}
