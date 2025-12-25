<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\Variation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_variations_for_a_project()
    {
        $project = Project::factory()->create();
        Variation::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/v1/projects/{$project->id}/variations");

        $response->assertOk();
        $response->assertJsonCount(3); // 2 created + 1 default
    }

    public function test_can_create_a_variation_for_a_project()
    {
        $project = Project::factory()->create();
        $data = [
            'display_name' => 'Test Variation',
        ];

        $response = $this->postJson("/api/v1/projects/{$project->id}/variations", $data);

        $response->assertCreated();
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('variations', $data);
    }

    public function test_can_get_a_variation()
    {
        $project = Project::factory()->create();
        $variation = $project->variations()->first();

        $response = $this->getJson("/api/v1/projects/{$project->id}/variations/{$variation->id}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $variation->id]);
    }

    public function test_can_update_a_variation()
    {
        $project = Project::factory()->create();
        $variation = $project->variations()->first();

        $data = [
            'display_name' => 'Updated Variation Name',
        ];

        $response = $this->putJson("/api/v1/projects/{$project->id}/variations/{$variation->id}", $data);

        $response->assertOk();
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('variations', $data);
    }

    public function test_can_sync_variation_constants()
    {
        $project = Project::factory()->create();
        $variation = $project->variations()->first();
        $existingConstant = $variation->variationConstants()->create(['key' => 'existing', 'data' => 'data']);

        $data = [
            'variation_constants' => [
                ['id' => $existingConstant->id, 'key' => 'existing', 'data' => 'updated data'],
                ['key' => 'new', 'data' => 'new data'],
            ],
        ];

        $response = $this->putJson("/api/v1/projects/{$project->id}/variations/{$variation->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('variation_constants', ['id' => $existingConstant->id, 'data' => 'updated data']);
        $this->assertDatabaseHas('variation_constants', ['key' => 'new', 'data' => 'new data']);
    }

    public function test_can_sync_variation_header_texts()
    {
        $project = Project::factory()->create();
        $variation = $project->variations()->first();
        $existingText = $variation->variationHeaderTexts()->create(['data' => 'existing text']);

        $data = [
            'variation_header_texts' => [
                ['id' => $existingText->id, 'data' => 'updated text'],
                ['data' => 'new text'],
            ],
        ];

        $response = $this->putJson("/api/v1/projects/{$project->id}/variations/{$variation->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('variation_header_texts', ['id' => $existingText->id, 'data' => 'updated text']);
        $this->assertDatabaseHas('variation_header_texts', ['data' => 'new text']);
    }

    public function test_can_delete_a_variation()
    {
        $project = Project::factory()->create();
        $variation1 = $project->variations()->create(['display_name' => 'Variation 1']);
        $variation2 = $project->variations()->create(['display_name' => 'Variation 2']);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}/variations/{$variation1->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('variations', ['id' => $variation1->id]);
    }

    public function test_cannot_delete_the_last_variation_of_a_project()
    {
        $project = Project::factory()->create();
        $variation = $project->variations()->first();

        $response = $this->deleteJson("/api/v1/projects/{$project->id}/variations/{$variation->id}");

        $response->assertStatus(400);
        $this->assertDatabaseHas('variations', ['id' => $variation->id]);
    }
}
