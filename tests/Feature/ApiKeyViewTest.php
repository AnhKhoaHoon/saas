<?php

namespace Tests\Feature;

use App\Actions\CreateProjectAction;
use App\Models\ApiKey;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_member_can_view_api_key_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, ['name' => 'Gateway']);
        TeamMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'invited_by' => $owner->id,
            'role' => 'viewer',
        ]);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'name' => 'Primary Key',
        ]);

        $response = $this->actingAs($member)->get("/projects/{$project->id}/api-keys");

        $response->assertOk();
        $response->assertSee('Primary Key');
        $response->assertSee($apiKey->key_prefix);
    }

    public function test_project_member_can_view_api_key_detail(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, ['name' => 'Gateway']);
        TeamMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'invited_by' => $owner->id,
            'role' => 'viewer',
        ]);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'name' => 'Primary Key',
            'scopes' => ['read', 'write'],
        ]);

        $response = $this->actingAs($member)->get("/projects/{$project->id}/api-keys/{$apiKey->id}");

        $response->assertOk();
        $response->assertSee('Primary Key');
        $response->assertSee('read, write');
    }

    public function test_non_member_cannot_view_api_key_pages(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, ['name' => 'Gateway']);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
        ]);

        $this->actingAs($outsider)->get("/projects/{$project->id}/api-keys")->assertForbidden();
        $this->actingAs($outsider)->get("/projects/{$project->id}/api-keys/{$apiKey->id}")->assertForbidden();
    }
}
