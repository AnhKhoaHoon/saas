<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\UsageLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyforge_domain_models_are_related_correctly(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $project = Project::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $owner->id,
            'name' => 'Primary Project',
            'slug' => 'primary-project',
            'status' => 'active',
        ]);

        $apiKey = ApiKey::create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'name' => 'Server Key',
            'key_prefix' => 'kfg_live',
            'key_hash' => hash('sha256', 'secret-key'),
            'status' => 'active',
            'rate_limit_per_minute' => 120,
            'quota_limit' => 100000,
        ]);

        $usageLog = UsageLog::create([
            'project_id' => $project->id,
            'api_key_id' => $apiKey->id,
            'request_id' => (string) Str::uuid(),
            'endpoint' => '/v1/events',
            'method' => 'POST',
            'status_code' => 200,
            'units' => 1,
            'occurred_at' => now(),
        ]);

        $teamMember = TeamMember::create([
            'project_id' => $project->id,
            'user_id' => $member->id,
            'invited_by' => $owner->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $teamInvite = TeamInvite::create([
            'project_id' => $project->id,
            'invited_by' => $owner->id,
            'email' => 'invitee@example.com',
            'role' => 'viewer',
            'token' => Str::random(40),
            'expires_at' => now()->addDays(7),
        ]);

        $subscription = Subscription::create([
            'user_id' => $owner->id,
            'plan' => 'pro',
            'status' => 'active',
            'project_limit' => 10,
            'api_key_limit' => 100,
            'monthly_request_limit' => 1000000,
        ]);

        $auditLog = AuditLog::create([
            'user_id' => $owner->id,
            'project_id' => $project->id,
            'auditable_type' => Project::class,
            'auditable_id' => $project->id,
            'action' => 'project.created',
            'occurred_at' => now(),
        ]);

        $this->assertTrue($project->owner->is($owner));
        $this->assertTrue($owner->projects->contains($project));
        $this->assertTrue($project->apiKeys->contains($apiKey));
        $this->assertTrue($apiKey->project->is($project));
        $this->assertTrue($apiKey->creator->is($owner));
        $this->assertTrue($project->usageLogs->contains($usageLog));
        $this->assertTrue($usageLog->apiKey->is($apiKey));
        $this->assertTrue($project->teamMembers->contains($teamMember));
        $this->assertTrue($teamMember->user->is($member));
        $this->assertTrue($project->teamInvites->contains($teamInvite));
        $this->assertTrue($teamInvite->inviter->is($owner));
        $this->assertTrue($owner->subscriptions->contains($subscription));
        $this->assertTrue($project->auditLogs->contains($auditLog));
        $this->assertTrue($auditLog->auditable->is($project));
    }
}
