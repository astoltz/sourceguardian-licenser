<?php

namespace App\Services;

use App\Models\License;
use App\Models\Version;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Service responsible for generating SourceGuardian license files.
 *
 * This service handles the merging of configuration data from various sources
 * (Project, Version, Variation, Customer, License) and executing the `licgen`
 * command-line tool to produce the binary license file.
 */
class LicenseService
{
    public function __construct(private ProcessFactory $processFactory) {}

    /**
     * Generate a license file and store it in the database.
     *
     * @param License $license The license configuration to use.
     * @param Version $version The specific version to generate the license for.
     * @return \App\Models\GeneratedLicense The created generated license record.
     * @throws \RuntimeException If the licgen command fails.
     */
    public function generate(License $license, Version $version)
    {
        $licenseData = $this->runLicgen($license, $version);

        return $license->generatedLicenses()->create([
            'version_id' => $version->id,
            'data' => $licenseData,
        ]);
    }

    /**
     * Execute the `licgen` command to generate the binary license data.
     *
     * @param License $license
     * @param Version $version
     * @return string The binary license data.
     * @throws \RuntimeException If the licgen command fails.
     */
    public function runLicgen(License $license, Version $version)
    {
        $variation = $license->variation;
        $project = $variation->project;
        $customer = $license->customer;

        // Determine Project ID and Key (Version override > Project default)
        $projectId = $version->override_project_id ?? $project->project_id;
        $projectKey = $version->override_project_key ?? $project->project_key;

        // Merge constants from all levels, with License level taking highest priority.
        $mergedConstants = $this->mergeData(
            $project->projectConstants,
            $version->versionConstants,
            $variation->variationConstants,
            $customer->customerConstants,
            $license->licenseConstants
        );

        // Merge header texts from all levels, sorted by their order.
        $mergedHeaderTexts = $this->mergeData(
            $project->projectHeaderTexts,
            $version->versionHeaderTexts,
            $variation->variationHeaderTexts,
            $customer->customerHeaderTexts,
            $license->licenseHeaderTexts
        );

        // Create temporary files for output and header text input
        $tempFile = tempnam(sys_get_temp_dir(), 'lic');
        $tempTextFile = tempnam(sys_get_temp_dir(), 'txt');

        $licgenPath = env('LICGEN_PATH', 'licgen');
        $command = [$licgenPath, $tempFile];

        // Add standard arguments
        $this->addCommandArgument($command, '--expire', $license->expiration_date);
        $this->addCommandArgument($command, '--projid', $projectId);
        $this->addCommandArgument($command, '--projkey', $projectKey);

        // Add boolean flags
        if ($license->bind_domain_ignore_cli) {
            $command[] = '--domain-ignore-cli';
        }
        if ($license->bind_ip_ignore_cli) {
            $command[] = '--ip-ignore-cli';
        }

        // Add multi-value arguments (Domains, IPs, MACs, Machine IDs)
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

        // Add merged constants
        foreach ($mergedConstants as $const) {
            $this->addCommandArgument($command, '--const', "\"{$const->key}={$const->data}\"");
        }

        // Add merged header texts via the temporary file
        $headerTextContent = $mergedHeaderTexts->pluck('data')->join(PHP_EOL);
        if (!empty($headerTextContent)) {
            file_put_contents($tempTextFile, $headerTextContent);
            $command[] = '--text';
            $command[] = '@' . $tempTextFile;
        }

        // Add time servers from the project
        foreach ($project->projectTimeServers as $server) {
            $this->addCommandArgument($command, '--time-server', $server->data);
        }

        Log::info('Executing licgen command: ' . implode(' ', $command));

        $process = $this->processFactory->create($command);
        $process->run();

        // Clean up text file immediately
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

    /**
     * Helper to add a command argument if the value is present.
     */
    private function addCommandArgument(&$command, $key, $value)
    {
        if ($value) {
            $command[] = $key;
            $command[] = $value instanceof \DateTime ? $value->format('d/m/Y') : $value;
        }
    }

    /**
     * Merge collections of data (constants or header texts).
     * Items later in the argument list override earlier ones based on key or order.
     */
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
