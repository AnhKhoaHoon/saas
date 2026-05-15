<?php

namespace Tests\Feature;

use App\Actions\CreateApiKeyAction;
use App\Actions\CreateProjectAction;
use App\Actions\Results\CreateApiKeyResult;
use App\Models\ApiKey;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateApiKeyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_api_key_hash_and_audit_log_without_persisting_plaintext_secret(): void
    {
        $owner = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, [
            'name' => 'Key Platform',
        ]);

        $result = app(CreateApiKeyAction::class)->execute($owner, $project, [
            'name' => 'Primary Server Key',
            'rate_limit_per_minute' => 120,
            'quota_limit' => 100000,
            'scopes' => ['read', 'write'],
            'ip_whitelist' => ['127.0.0.1'],
        ]);

        $this->assertInstanceOf(CreateApiKeyResult::class, $result);
        $this->assertStringStartsWith('kfg_live_', $result->plainTextKey);
        $this->assertInstanceOf(ApiKey::class, $result->apiKey);
        $this->assertSame(hash('sha256', $result->plainTextKey), $result->apiKey->key_hash);
        $this->assertNotSame($result->plainTextKey, $result->apiKey->key_hash);

        $this->assertDatabaseHas('api_keys', [
            'id' => $result->apiKey->id,
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'name' => 'Primary Server Key',
            'key_hash' => hash('sha256', $result->plainTextKey),
        ]);

        $this->assertDatabaseMissing('api_keys', [
            'key_hash' => $result->plainTextKey,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'action' => 'api_key.created',
            'auditable_type' => ApiKey::class,
            'auditable_id' => $result->apiKey->id,
        ]);
    }

    public function test_project_member_can_create_api_key(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        TeamMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'invited_by' => $owner->id,
            'role' => 'owner',
        ]);

        TeamMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'invited_by' => $owner->id,
            'role' => 'member',
        ]);

        $result = app(CreateApiKeyAction::class)->execute($member, $project, [
            'name' => 'Worker Key',
        ]);

        $this->assertSame($member->id, $result->apiKey->created_by);
    }

    public function test_non_member_cannot_create_api_key(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, [
            'name' => 'Billing Project',
        ]);

        $this->expectException(AuthorizationException::class);

        app(CreateApiKeyAction::class)->execute($outsider, $project, [
            'name' => 'Forbidden Key',
        ]);
    }
}
