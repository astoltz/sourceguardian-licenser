<?php

namespace App\Jobs;

use App\Http\Controllers\Api\V1\LicenseController;
use App\Models\License;
use App\Models\Version;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLicenseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public License $license,
        public Version $version
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $controller = new LicenseController();
        $licenseData = $controller->runLicgen($this->license, $this->version);

        $this->license->generatedLicenses()->create([
            'version_id' => $this->version->id,
            'data' => $licenseData,
        ]);
    }
}
