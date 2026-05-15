<?php

namespace Tests\Feature;

use App\Actions\CreateProjectAction;
use App\Actions\RevokeApiKeyAction;
use App\Models\ApiKey;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeApiKeyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_revokes_api_key_and_writes_audit_log(): void
    {
        $owner = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, ['name' => 'Core API']);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
        ]);

        $revokedKey = app(RevokeApiKeyAction::class)->execute($owner, $apiKey);

        $this->assertSame('revoked', $revokedKey->status);
        $this->assertNotNull($revokedKey->revoked_at);

        $this->assertDatabaseHas('audit_logs', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'action' => 'api_key.revoked',
            'auditable_type' => ApiKey::class,
            'auditable_id' => $apiKey->id,
        ]);
    }

    public function test_non_member_cannot_revoke_api_key(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();
        $project = app(CreateProjectAction::class)->execute($owner, ['name' => 'Core API']);
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
        ]);

        $this->expectException(AuthorizationException::class);

        app(RevokeApiKeyAction::class)->execute($outsider, $apiKey);
    }
}
