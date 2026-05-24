<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\UsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_create_project_from_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'name' => 'Billing Core',
            'description' => 'Production billing workspace',
            'timezone' => 'Asia/Saigon',
        ]);

        $project = Project::where('user_id', $user->id)
            ->where('slug', 'billing-core')
            ->firstOrFail();

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Billing Core',
            'slug' => 'billing-core',
        ]);
    }

    public function test_project_creation_requires_valid_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'name' => 'ab',
            'timezone' => 'Asia/Saigon',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_dashboard_shows_usage_logs_for_owned_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Gateway',
        ]);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'name' => 'Primary Key',
        ]);

        UsageLog::factory()->create([
            'project_id' => $project->id,
            'api_key_id' => $apiKey->id,
            'endpoint' => '/api/ping',
            'method' => 'GET',
            'status_code' => 200,
        ]);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Recent Usage Logs')
            ->assertSee('/api/ping')
            ->assertSee('Primary Key');
    }

    public function test_dashboard_usage_log_filter_can_scope_by_method_and_status_code(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $user->id,
        ]);

        UsageLog::factory()->create([
            'project_id' => $project->id,
            'api_key_id' => $apiKey->id,
            'endpoint' => '/api/accepted',
            'method' => 'GET',
            'status_code' => 200,
        ]);

        UsageLog::factory()->create([
            'project_id' => $project->id,
            'api_key_id' => $apiKey->id,
            'endpoint' => '/api/rejected',
            'method' => 'POST',
            'status_code' => 429,
        ]);

        $this->actingAs($user)
            ->get(route('projects.usage-logs.index', [
                'project' => $project,
                'method' => 'POST',
                'status_code' => 429,
            ]))
            ->assertOk()
            ->assertSee('/api/rejected')
            ->assertDontSee('/api/accepted');
    }
}
