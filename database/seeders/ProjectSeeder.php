<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\License;
use App\Models\Project;
use App\Models\Variation;
use App\Models\Version;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the specific "PHP Standalone Sample" project
        $this->createSampleProject();

        // Create 20 random projects to test pagination
        Project::factory(20)->create()->each(function ($project) {
            // Add some versions
            Version::factory(rand(3, 8))->create(['project_id' => $project->id]);

            // Add some variations
            Variation::factory(rand(3, 8))->create(['project_id' => $project->id]);
        });

        // Create 50 random customers
        Customer::factory(50)->create();

        // Create 100 random licenses distributed across customers and projects
        $customers = Customer::all();
        $projects = Project::with(['variations', 'versions'])->get();

        foreach (range(1, 100) as $i) {
            $project = $projects->random();
            // Ensure project has variations and versions
            if ($project->variations->isEmpty() || $project->versions->isEmpty()) {
                continue;
            }

            License::factory()->create([
                'customer_id' => $customers->random()->id,
                'variation_id' => $project->variations->random()->id,
                'version_id' => $project->versions->random()->id,
            ]);
        }
    }

    private function createSampleProject()
    {
        $project = Project::create([
            'display_name' => 'PHP Standalone Sample',
        ]);

        $project->projectHeaderTexts()->create([
            'data' => 'PHP Standalone Sample',
            'order' => 10,
        ]);

        $version = $project->versions()->first(); // Default version
        $version->update(['display_name' => '1.0']);

        $version->versionConstants()->create([
            'key' => 'VERSION',
            'data' => '1.0',
        ]);
        $version->versionHeaderTexts()->create([
            'data' => 'Version 1.0',
            'order' => 30,
        ]);

        $standardVariation = $project->variations()->first(); // Default variation
        $standardVariation->update(['display_name' => 'Standard Edition']);
        $standardVariation->variationHeaderTexts()->create([
            'data' => 'Standard Edition',
            'order' => 20,
        ]);
        $standardVariation->variationConstants()->create([
            'key' => 'EDITION',
            'data' => 'Standard',
        ]);

        $proVariation = $project->variations()->create([
            'display_name' => 'Pro Edition',
        ]);
        $proVariation->variationHeaderTexts()->create([
            'data' => 'Pro Edition',
            'order' => 20,
        ]);
        $proVariation->variationConstants()->create([
            'key' => 'EDITION',
            'data' => 'Pro',
        ]);

        $developerVariation = $project->variations()->create([
            'display_name' => 'Developer Edition',
        ]);
        $developerVariation->variationHeaderTexts()->create([
            'data' => 'Developer Edition',
            'order' => 20,
        ]);
        $developerVariation->variationConstants()->create([
            'key' => 'EDITION',
            'data' => 'Developer',
        ]);

        $customer = Customer::create([
            'display_name' => 'Acme Corp',
        ]);

        $customer->customerHeaderTexts()->create([
            'data' => 'Licensed to: Acme Corp',
            'order' => 40,
        ]);
        $customer->customerConstants()->create([
            'key' => 'CUSTOMER_NAME',
            'data' => 'Acme Corp',
        ]);

        License::create([
            'display_name' => 'Developer License',
            'customer_id' => $customer->id,
            'variation_id' => $developerVariation->id,
            'version_id' => $version->id,
        ]);
    }
}
