<?php

namespace App\Services;

use App\Models\License;

/**
 * Service to inspect the effective configuration of a license.
 *
 * This service traverses the hierarchy (Project -> Version -> Variation -> Customer -> License)
 * to determine the final, merged set of constants and header texts that will be
 * applied during license generation.
 */
class LicenseInspectorService
{
    /**
     * Inspect a license and return its effective configuration.
     *
     * @param License $license
     * @return array
     */
    public function inspect(License $license)
    {
        $variation = $license->variation;
        $project = $variation->project;
        $customer = $license->customer;
        $version = $license->version;

        $constants = $this->gatherConstants($project, $version, $variation, $customer, $license);
        $headerTexts = $this->gatherHeaderTexts($project, $version, $variation, $customer, $license);

        return [
            'constants' => $constants,
            'headerTexts' => $headerTexts,
        ];
    }

    /**
     * Gather and merge constants from all levels.
     *
     * @return \Illuminate\Support\Collection
     */
    private function gatherConstants($project, $version, $variation, $customer, $license)
    {
        $all = collect();

        // The `put` method will automatically handle overrides based on the key.
        // The last one in wins for a given key, establishing the priority.
        foreach ($project->projectConstants as $item) {
            $all->put($item->key, ['key' => $item->key, 'value' => $item->data, 'source' => 'Project']);
        }
        foreach ($version->versionConstants as $item) {
            $all->put($item->key, ['key' => $item->key, 'value' => $item->data, 'source' => 'Version']);
        }
        foreach ($variation->variationConstants as $item) {
            $all->put($item->key, ['key' => $item->key, 'value' => $item->data, 'source' => 'Variation']);
        }
        foreach ($customer->customerConstants as $item) {
            $all->put($item->key, ['key' => $item->key, 'value' => $item->data, 'source' => 'Customer']);
        }
        foreach ($license->licenseConstants as $item) {
            $all->put($item->key, ['key' => $item->key, 'value' => $item->data, 'source' => 'License']);
        }

        return $all->sortBy(fn($item) => strtolower($item['key']))->values();
    }

    /**
     * Gather and merge header texts from all levels.
     *
     * @return \Illuminate\Support\Collection
     */
    private function gatherHeaderTexts($project, $version, $variation, $customer, $license)
    {
        $all = collect();

        // Use `put` with the order as the key to handle overrides.
        $add = function ($items, $source) use (&$all) {
            foreach ($items as $item) {
                $all->put($item->order, ['value' => $item->data, 'order' => $item->order, 'source' => $source]);
            }
        };

        $add($project->projectHeaderTexts, 'Project');
        $add($version->versionHeaderTexts, 'Version');
        $add($variation->variationHeaderTexts, 'Variation');
        $add($customer->customerHeaderTexts, 'Customer');
        $add($license->licenseHeaderTexts, 'License');

        return $all->sortBy('order')->values();
    }
}
