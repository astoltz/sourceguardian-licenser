<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\GeneratedLicense;
use App\Models\License;
use App\Models\Project;
use App\Models\Variation;
use App\Models\Version;
use App\Services\LicenseInspectorService;
use App\Services\LicenseService;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Controller for managing licenses via the Web UI.
 */
class LicenseController extends Controller
{
    use SyncsHasMany;

    public function __construct(
        private LicenseService $licenseService,
        private LicenseInspectorService $inspectorService
    ) {}

    /**
     * Display a listing of licenses with filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = License::with(['customer', 'variation.project', 'version']);

        $projectId = $request->input('project_id');
        $customerId = $request->input('customer_id');
        $variationId = $request->input('variation_id');
        $versionId = $request->input('version_id');

        if ($projectId) {
            $query->whereHas('variation.project', fn($q) => $q->where('id', $projectId));
        }
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        if ($variationId) {
            $query->where('variation_id', $variationId);
        }
        if ($versionId) {
            $query->where('version_id', $versionId);
        }

        $licenses = $query->paginate(15)->withQueryString();

        $projects = Project::all();
        $customers = Customer::all();
        // Only load variations and versions if a project is selected to keep the UI clean
        $variations = $projectId ? Variation::where('project_id', $projectId)->get() : collect();
        $versions = $projectId ? Version::where('project_id', $projectId)->get() : collect();

        return view('licenses.index', compact('licenses', 'projects', 'customers', 'variations', 'versions'));
    }

    /**
     * Show the form for creating a new license.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $projects = Project::all();
        return view('licenses.create', compact('projects'));
    }

    /**
     * Get validation rules for license creation/update.
     *
     * @param License|null $license
     * @return array
     */
    private function getValidationRules($license = null)
    {
        return [
            'display_name' => 'nullable|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'variation_id' => 'required|exists:variations,id',
            'version_id' => [
                'required',
                'exists:versions,id',
                function ($attribute, $value, $fail) {
                    // Ensure the selected version and variation belong to the same project
                    $version = Version::find($value);
                    $variation = Variation::find(request()->variation_id);
                    if ($version && $variation && $version->project_id !== $variation->project_id) {
                        $fail('The selected version and variation must belong to the same project.');
                    }
                },
            ],
            'enabled' => 'boolean',
            'expiration_date' => 'nullable|date',
            'bind_domain_ignore_cli' => 'boolean',
            'bind_ip_ignore_cli' => 'boolean',
            'license_domains' => 'sometimes|array',
            'license_ips' => 'sometimes|array',
            'license_macs' => 'sometimes|array',
            'license_machine_ids' => 'sometimes|array',
            'license_constants' => 'sometimes|array',
            'license_header_texts' => 'sometimes|array',
        ];
    }

    /**
     * Store a newly created license in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());
        $validated = $this->prepareCheckboxData($request, $validated);

        $license = License::create($validated);
        $this->syncRelations($license, $validated);

        return redirect()->route('web.licenses.show', $license)->with('success', 'License created successfully.');
    }

    /**
     * Display the specified license.
     *
     * @param License $license
     * @return \Illuminate\View\View
     */
    public function show(License $license)
    {
        $license->load(['customer', 'variation.project', 'version.project', 'licenseConstants', 'licenseHeaderTexts', 'licenseDomains', 'licenseIps', 'licenseMacs', 'licenseMachineIds', 'generatedLicenses']);

        // Inspect the license to get the effective configuration (merged from all levels)
        $inspection = $this->inspectorService->inspect($license);

        return view('licenses.show', [
            'license' => $license,
            'effectiveConstants' => $inspection['constants'],
            'effectiveHeaderTexts' => $inspection['headerTexts'],
        ]);
    }

    /**
     * Show the form for editing the specified license.
     *
     * @param License $license
     * @return \Illuminate\View\View
     */
    public function edit(License $license)
    {
        $license->load(['licenseConstants', 'licenseHeaderTexts', 'licenseDomains', 'licenseIps', 'licenseMacs', 'licenseMachineIds', 'customer', 'variation.project', 'version']);
        $projects = Project::all();

        return view('licenses.edit', compact('license', 'projects'));
    }

    /**
     * Update the specified license in storage.
     *
     * @param Request $request
     * @param License $license
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, License $license)
    {
        $validated = $request->validate($this->getValidationRules($license));
        $validated = $this->prepareCheckboxData($request, $validated);

        $license->update($validated);
        $this->syncRelations($license, $validated);

        // Clear cached licenses on update to ensure the next download generates a fresh file
        $license->generatedLicenses()->delete();

        return redirect()->route('web.licenses.show', $license)->with('success', 'License updated successfully.');
    }

    /**
     * Prepare checkbox data for validation/storage.
     *
     * @param Request $request
     * @param array $validated
     * @return array
     */
    private function prepareCheckboxData(Request $request, array $validated): array
    {
        $validated['bind_domain_ignore_cli'] = $request->has('bind_domain_ignore_cli');
        $validated['bind_ip_ignore_cli'] = $request->has('bind_ip_ignore_cli');
        $validated['enabled'] = $request->has('enabled');
        return $validated;
    }

    /**
     * Sync related models (domains, IPs, MACs, machine IDs, constants, header texts).
     *
     * @param License $license
     * @param array $validated
     */
    private function syncRelations(License $license, array $validated): void
    {
        if (isset($validated['license_domains'])) {
            $this->syncHasMany($license->licenseDomains(), $validated['license_domains']);
        }
        if (isset($validated['license_ips'])) {
            $this->syncHasMany($license->licenseIps(), $validated['license_ips']);
        }
        if (isset($validated['license_macs'])) {
            $this->syncHasMany($license->licenseMacs(), $validated['license_macs']);
        }
        if (isset($validated['license_machine_ids'])) {
            $this->syncHasMany($license->licenseMachineIds(), $validated['license_machine_ids']);
        }
        if (isset($validated['license_constants'])) {
            $this->syncHasMany($license->licenseConstants(), $validated['license_constants']);
        }
        if (isset($validated['license_header_texts'])) {
            $this->syncHasMany($license->licenseHeaderTexts(), $validated['license_header_texts']);
        }
    }

    /**
     * Remove the specified license from storage.
     *
     * @param License $license
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(License $license)
    {
        try {
            $license->delete();
            return redirect()->route('web.licenses.index')->with('success', 'License deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Clear the cached generated license file.
     *
     * @param License $license
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(License $license)
    {
        $license->generatedLicenses()->delete();
        return redirect()->route('web.licenses.show', $license)->with('success', 'License cache cleared successfully.');
    }

    /**
     * Generate (if needed) and download the license file.
     *
     * @param Request $request
     * @param License $license
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function download(Request $request, License $license)
    {
        $version = $license->version;
        $generatedLicense = $license->generatedLicenses()->first();

        if (!$generatedLicense) {
            try {
                $generatedLicense = $this->licenseService->generate($license, $version);
            } catch (\Throwable $e) {
                return back()->with('error', 'License generation failed: ' . $e->getMessage());
            }
        }

        return $this->downloadGenerated($generatedLicense, $request);
    }

    /**
     * Download an existing generated license file.
     *
     * @param GeneratedLicense $generatedLicense
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadGenerated(GeneratedLicense $generatedLicense, Request $request)
    {
        $generatedLicense->update(['downloaded_at' => now(), 'downloaded_ip' => $request->ip()]);

        $license = $generatedLicense->license;
        $version = $generatedLicense->version;
        $project = $version->project;

        // Determine filename: Version override > Project default > Default
        $filename = $version->override_license_filename ?? $project->license_filename ?? 'license.lic';

        return response($generatedLicense->data)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
