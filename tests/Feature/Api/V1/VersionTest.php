<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\Version;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_versions_for_a_project()
    {
        $project = Project::factory()->create();
        Version::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->getJson("/api/v1/projects/{$project->id}/versions");

        $response->assertOk();
        $response->assertJsonCount(3); // 2 created + 1 default
    }

    public function test_can_create_a_version_for_a_project()
    {
        $project = Project::factory()->create();
        $data = [
            'display_name' => 'Test Version',
        ];

        $response = $this->postJson("/api/v1/projects/{$project->id}/versions", $data);

        $response->assertCreated();
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('versions', $data);
    }

    public function test_can_get_a_version()
    {
        $project = Project::factory()->create();
        $version = $project->versions()->first();

        $response = $this->getJson("/api/v1/projects/{$project->id}/versions/{$version->id}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $version->id]);
    }

    public function test_can_update_a_version()
    {
        $project = Project::factory()->create();
        $version = $project->versions()->first();

        $data = [
            'display_name' => 'Updated Version Name',
        ];

        $response = $this->putJson("/api/v1/projects/{$project->id}/versions/{$version->id}", $data);

        $response->assertOk();
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas('versions', $data);
    }

    public function test_can_sync_version_constants()
    {
        $project = Project::factory()->create();
        $version = $project->versions()->first();
        $existingConstant = $version->versionConstants()->create(['key' => 'existing', 'data' => 'data']);

        $data = [
            'version_constants' => [
                ['id' => $existingConstant->id, 'key' => 'existing', 'data' => 'updated data'],
                ['key' => 'new', 'data' => 'new data'],
            ],
        ];

        $response = $this->putJson("/api/v1/projects/{$project->id}/versions/{$version->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('version_constants', ['id' => $existingConstant->id, 'data' => 'updated data']);
        $this->assertDatabaseHas('version_constants', ['key' => 'new', 'data' => 'new data']);
    }

    public function test_can_sync_version_header_texts()
    {
        $project = Project::factory()->create();
        $version = $project->versions()->first();
        $existingText = $version->versionHeaderTexts()->create(['data' => 'existing text']);

        $data = [
            'version_header_texts' => [
                ['id' => $existingText->id, 'data' => 'updated text'],
                ['data' => 'new text'],
            ],
        ];

        $response = $this->putJson("/api/v1/projects/{$project->id}/versions/{$version->id}", $data);

        $response->assertOk();
        $this->assertDatabaseHas('version_header_texts', ['id' => $existingText->id, 'data' => 'updated text']);
        $this->assertDatabaseHas('version_header_texts', ['data' => 'new text']);
    }

    public function test_can_delete_a_version()
    {
        $project = Project::factory()->create();
        $version1 = $project->versions()->create(['display_name' => 'Version 1']);
        $version2 = $project->versions()->create(['display_name' => 'Version 2']);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}/versions/{$version1->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('versions', ['id' => $version1->id]);
    }

    public function test_cannot_delete_the_last_version_of_a_project()
    {
        $project = Project::factory()->create();
        $version = $project->versions()->first();

        $response = $this->deleteJson("/api/v1/projects/{$project->id}/versions/{$version->id}");

        $response->assertStatus(400);
        $this->assertDatabaseHas('versions', ['id' => $version->id]);
    }
}
