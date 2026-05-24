<?php

namespace Tests\Feature;

use App\Actions\CreateProjectAction;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_owner_can_create_api_key_from_dashboard(): void
    {
        $user = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($user, [
            'name' => 'Gateway',
        ]);

        $response = $this->actingAs($user)->post("/projects/{$project->id}/api-keys", [
            'name' => 'Primary Key',
            'rate_limit_per_minute' => 120,
            'quota_limit' => 50000,
            'scopes' => 'read,write',
            'ip_whitelist' => '127.0.0.1,10.0.0.5',
        ]);

        $response->assertRedirect(route('projects.api-keys.index', $project));
        $response->assertSessionHas('new_api_key');

        $this->assertDatabaseHas('api_keys', [
            'project_id' => $project->id,
            'created_by' => $user->id,
            'name' => 'Primary Key',
            'rate_limit_per_minute' => 120,
            'quota_limit' => 50000,
        ]);
    }

    public function test_api_key_creation_requires_valid_name(): void
    {
        $user = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($user, [
            'name' => 'Gateway',
        ]);

        $response = $this->actingAs($user)->post("/projects/{$project->id}/api-keys", [
            'name' => 'ab',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_project_owner_can_revoke_api_key_from_dashboard(): void
    {
        $user = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($user, [
            'name' => 'Gateway',
        ]);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/projects/{$project->id}/api-keys/{$apiKey->id}");

        $response->assertRedirect(route('projects.api-keys.index', $project));
        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'status' => 'revoked',
        ]);
    }
}
