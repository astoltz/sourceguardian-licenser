<?php

namespace Tests\Feature\Api\V1;

use App\Jobs\GenerateLicenseJob;
use App\Models\Customer;
use App\Models\GeneratedLicense;
use App\Models\License;
use App\Models\Variation;
use App\Models\Version;
use App\Services\ProcessFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_licenses()
    {
        License::factory()->count(3)->create();
        $response = $this->getJson('/api/v1/licenses');
        $response->assertOk()->assertJsonCount(3);
    }

    public function test_can_create_a_license()
    {
        $customer = Customer::factory()->create();
        $variation = Variation::factory()->create();
        $version = Version::factory()->create(['project_id' => $variation->project_id]);
        $data = [
            'display_name' => 'Test License',
            'customer_id' => $customer->id,
            'variation_id' => $variation->id,
            'version_id' => $version->id,
        ];

        $response = $this->postJson('/api/v1/licenses', $data);

        $response->assertCreated();
        $response->assertJsonFragment(['display_name' => 'Test License']);
        $this->assertDatabaseHas('licenses', $data);
    }

    public function test_can_get_a_license()
    {
        $license = License::factory()->create();
        $response = $this->getJson("/api/v1/licenses/{$license->id}");
        $response->assertOk()->assertJsonFragment(['id' => $license->id]);
    }

    public function test_can_update_a_license_and_clears_cache()
    {
        $license = License::factory()->create();
        $generatedLicense = GeneratedLicense::factory()->create(['license_id' => $license->id]);

        $data = [
            'display_name' => 'Updated License Name',
        ];
        $response = $this->putJson("/api/v1/licenses/{$license->id}", $data);
        $response->assertOk();
        $this->assertDatabaseMissing('generated_licenses', ['id' => $generatedLicense->id]);
    }

    public function test_can_reset_license_cache()
    {
        $license = License::factory()->create();
        $generatedLicense = GeneratedLicense::factory()->create(['license_id' => $license->id]);

        $response = $this->postJson("/api/v1/licenses/{$license->id}/reset");

        $response->assertNoContent();
        $this->assertDatabaseMissing('generated_licenses', ['id' => $generatedLicense->id]);
    }

    public function test_download_queues_generation()
    {
        Queue::fake();
        config(['services.licenser.queue_generation' => true]);

        $license = License::factory()->create();

        $response = $this->get("/api/v1/licenses/{$license->id}/download");

        $response->assertStatus(202);
        Queue::assertPushed(GenerateLicenseJob::class);
    }

    public function test_can_download_and_generate_license()
    {
        $processMock = $this->createMock(Process::class);
        $processMock->method('run')->willReturnCallback(function () {
            // We can't easily access the command arguments here because they are passed to the constructor
            // of the Process object, which is created by the factory.
            // However, we know the controller creates a temp file.
            // Since we can't easily intercept the filename, we'll use a different strategy.
            return 0;
        });
        $processMock->method('isSuccessful')->willReturn(true);

        $processFactoryMock = $this->createMock(ProcessFactory::class);
        $processFactoryMock->method('create')->willReturnCallback(function ($command) use ($processMock) {
            // The second argument is the output file path
            $outputFile = $command[1];
            file_put_contents($outputFile, 'dummy license data');
            return $processMock;
        });

        $this->app->instance(ProcessFactory::class, $processFactoryMock);

        $license = License::factory()->create();

        $response = $this->get("/api/v1/licenses/{$license->id}/download");

        $response->assertOk();
        $this->assertDatabaseHas('generated_licenses', ['license_id' => $license->id]);
        $this->assertEquals('dummy license data', $response->getContent());
    }
}
