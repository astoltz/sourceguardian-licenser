<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateLicenseJob;
use App\Models\GeneratedLicense;
use App\Models\License;
use App\Models\Variation;
use App\Models\Version;
use App\Services\LicenseInspectorService;
use App\Services\ProcessFactory;
use App\Traits\SyncsHasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for managing licenses.
 */
class LicenseController extends Controller
{
    use SyncsHasMany;

    public function __construct(
        private ProcessFactory $processFactory,
        private LicenseInspectorService $inspectorService
    ) {}

    /**
     * Display a listing of licenses.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $licenses = License::with([
            'customer', 'variation', 'version', 'licenseConstants', 'licenseHeaderTexts',
            'licenseDomains', 'licenseIps', 'licenseMacs', 'licenseMachineIds', 'generatedLicenses'
        ])->get();

        $licenses->each(function ($license) {
            $license->generatedLicenses->each->makeHidden('data');
        });

        return $licenses;
    }

    /**
     * Get validation rules for license creation/update.
     *
     * @return array
     */
    private function getValidationRules()
    {
        return [
            'display_name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'variation_id' => 'required|exists:variations,id',
            'version_id' => [
                'required',
                'exists:versions,id',
                function ($attribute, $value, $fail) {
                    $version = Version::find($value);
                    $variation = Variation::find(request()->variation_id);
                    if ($version && $variation && $version->project_id !== $variation->project_id) {
                        $fail('The selected version and variation must belong to the same project.');
                    }
                },
            ],
            'enabled' => 'sometimes|boolean',
            'expiration_date' => 'nullable|date',
            'bind_domain_ignore_cli' => 'sometimes|boolean',
            'bind_ip_ignore_cli' => 'sometimes|boolean',
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        $license = License::create($validated);
        $this->syncRelations($license, $validated);

        return response()->json($license->load(['licenseDomains', 'licenseIps', 'licenseMacs', 'licenseMachineIds', 'licenseConstants', 'licenseHeaderTexts']), 201);
    }

    /**
     * Display the specified license.
     *
     * @param License $license
     * @return License
     */
    public function show(License $license)
    {
        $license->load([
            'customer', 'variation.project', 'version.project', 'licenseConstants', 'licenseHeaderTexts',
            'licenseDomains', 'licenseIps', 'licenseMacs', 'licenseMachineIds', 'generatedLicenses'
        ]);

        $license->generatedLicenses->each->makeHidden('data');

        $inspection = $this->inspectorService->inspect($license);

        // Append effective configuration to the response
        $license->effective_constants = $inspection['constants'];
        $license->effective_header_texts = $inspection['headerTexts'];

        return $license;
    }

    /**
     * Update the specified license in storage.
     *
     * @param Request $request
     * @param License $license
     * @return License
     */
    public function update(Request $request, License $license)
    {
        $validated = $request->validate($this->getValidationRules());

        $license->update($validated);
        $this->syncRelations($license, $validated);

        $license->generatedLicenses()->delete();

        return $license->load(['licenseDomains', 'licenseIps', 'licenseMacs', 'licenseMachineIds', 'licenseConstants', 'licenseHeaderTexts']);
    }

    /**
     * Sync related models (domains, IPs, MACs, constants, header texts).
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
     * Clear the cached generated license file.
     *
     * @param License $license
     * @return \Illuminate\Http\Response
     */
    public function reset(License $license)
    {
        $license->generatedLicenses()->delete();
        return response()->noContent();
    }

    /**
     * Generate (if needed) and download the license file.
     *
     * @param Request $request
     * @param License $license
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request, License $license)
    {
        $version = $license->version;

        $generatedLicense = $license->generatedLicenses()->first();

        if (!$generatedLicense) {
            if (config('services.licenser.queue_generation')) {
                GenerateLicenseJob::dispatch($license, $version);
                return response()->json(['message' => 'License generation queued.'], 202);
            } else {
                try {
                    $licenseData = $this->runLicgen($license, $version);
                    $generatedLicense = $license->generatedLicenses()->create([
                        'version_id' => $version->id,
                        'data' => $licenseData,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('License generation failed: ' . $e->getMessage());
                    return response()->json(['message' => 'License generation failed.', 'error' => $e->getMessage()], 500);
                }
            }
        }

        if (!$generatedLicense) {
             return response()->json(['message' => 'License is being generated. Please try again later.'], 202);
        }

        $generatedLicense->update([
            'downloaded_at' => now(),
            'downloaded_ip' => $request->ip(),
        ]);

        return response($generatedLicense->data)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="license.lic"');
    }

    /**
     * Execute the `licgen` command to generate the binary license data.
     *
     * @param License $license
     * @param Version $version
     * @return string
     * @throws \RuntimeException
     */
    public function runLicgen(License $license, Version $version)
    {
        $variation = $license->variation;
        $project = $variation->project;
        $customer = $license->customer;

        $mergedConstants = $this->mergeData(
            $project->projectConstants,
            $version->versionConstants,
            $variation->variationConstants,
            $customer->customerConstants,
            $license->licenseConstants
        );

        $mergedHeaderTexts = $this->mergeData(
            $project->projectHeaderTexts,
            $version->versionHeaderTexts,
            $variation->variationHeaderTexts,
            $customer->customerHeaderTexts,
            $license->licenseHeaderTexts
        );

        $tempFile = tempnam(sys_get_temp_dir(), 'lic');
        $tempTextFile = tempnam(sys_get_temp_dir(), 'txt');

        $licgenPath = env('LICGEN_PATH', 'licgen');
        $command = [$licgenPath, $tempFile];

        $this->addCommandArgument($command, '--expire', $license->expiration_date);
        $this->addCommandArgument($command, '--projid', $project->project_id);
        $this->addCommandArgument($command, '--projkey', $project->project_key);

        if ($license->bind_domain_ignore_cli) {
            $command[] = '--domain-ignore-cli';
        }
        if ($license->bind_ip_ignore_cli) {
            $command[] = '--ip-ignore-cli';
        }

        foreach ($license->licenseDomains as $item) {
            $this->addCommandArgument($command, '--domain', $item->domain);
        }
        foreach ($license->licenseIps as $item) {
            // Handle CIDR
            if (strpos($item->ip, '/') !== false) {
                $this->addCommandArgument($command, '--ip', $this->cidrToIpMask($item->ip));
            } else {
                $this->addCommandArgument($command, '--ip', $item->ip);
            }
        }
        foreach ($license->licenseMacs as $item) {
            $this->addCommandArgument($command, '--mac', $item->mac);
        }
        foreach ($license->licenseMachineIds as $item) {
            $this->addCommandArgument($command, '--machine-id', $item->machine_id);
        }

        foreach ($mergedConstants as $const) {
            $this->addCommandArgument($command, '--const', "\"{$const->key}={$const->data}\"");
        }

        $headerTextContent = $mergedHeaderTexts->pluck('data')->join(PHP_EOL);
        if (!empty($headerTextContent)) {
            file_put_contents($tempTextFile, $headerTextContent);
            $command[] = '--text';
            $command[] = '@' . $tempTextFile;
        }

        foreach ($project->projectTimeServers as $server) {
            $this->addCommandArgument($command, '--time-server', $server->data);
        }

        Log::info('Executing licgen command: ' . implode(' ', $command));

        $process = $this->processFactory->create($command);
        $process->run();

        if (file_exists($tempTextFile)) {
            unlink($tempTextFile);
        }

        if (!$process->isSuccessful()) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            $errorOutput = $process->getErrorOutput();
            Log::error('licgen failed: ' . $errorOutput);
            throw new \RuntimeException('License generation failed: ' . $errorOutput);
        }

        $licenseData = file_get_contents($tempFile);
        unlink($tempFile);

        return $licenseData;
    }

    private function addCommandArgument(&$command, $key, $value)
    {
        if ($value) {
            $command[] = $key;
            $command[] = $value instanceof \DateTime ? $value->format('d/m/Y') : $value;
        }
    }

    private function mergeData(...$collections)
    {
        $merged = collect();
        foreach ($collections as $collection) {
            foreach ($collection as $item) {
                $item = is_array($item) ? (object)$item : $item;
                $key = $item->key ?? $item->order;
                $merged[$key] = $item;
            }
        }
        return $merged->sortBy('order')->values();
    }

    private function cidrToIpMask($cidr) {
        list($ip, $mask) = explode('/', $cidr);
        $mask = (int)$mask;
        $mask = long2ip(-1 << (32 - $mask));
        return "$ip/$mask";
    }
}
