<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ValidateApiKeyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_with_valid_api_key_is_accepted_and_usage_is_logged(): void
    {
        RateLimiter::clear('api-key-rate:1');

        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);
        TeamMember::factory()->create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'invited_by' => $owner->id,
            'role' => 'owner',
        ]);

        $plainTextKey = 'kfg_live_validmiddlewarekey0000000000000000';
        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($plainTextKey, 0, 12),
            'key_hash' => hash('sha256', $plainTextKey),
            'requests_count' => 0,
            'rate_limit_per_minute' => 60,
            'quota_limit' => 100,
            'ip_whitelist' => ['127.0.0.1'],
        ]);

        $response = $this->withHeader('X-API-Key', $plainTextKey)->getJson('/api/ping');

        $response->assertOk();
        $response->assertJsonPath('api_key.id', $apiKey->id);
        $response->assertJsonPath('project.id', $project->id);
        $response->assertHeader('X-RateLimit-Limit', '60');
        $response->assertHeader('X-RateLimit-Remaining', '59');

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'requests_count' => 1,
        ]);
        $this->assertDatabaseHas('usage_logs', [
            'api_key_id' => $apiKey->id,
            'project_id' => $project->id,
            'endpoint' => '/api/ping',
            'method' => 'GET',
            'status_code' => 200,
        ]);
    }

    public function test_request_without_api_key_is_rejected(): void
    {
        $this->getJson('/api/ping')
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'API key is required.',
            ]);
    }

    public function test_revoked_api_key_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);
        $plainTextKey = 'kfg_live_revokedmiddlewarekey0000000000000';

        ApiKey::factory()->revoked()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($plainTextKey, 0, 12),
            'key_hash' => hash('sha256', $plainTextKey),
        ]);

        $this->withHeader('X-API-Key', $plainTextKey)
            ->getJson('/api/ping')
            ->assertForbidden()
            ->assertJson([
                'message' => 'API key is revoked or inactive.',
            ]);
    }

    public function test_quota_exhausted_api_key_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);
        $plainTextKey = 'kfg_live_quotamiddlewarekey000000000000000';

        ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($plainTextKey, 0, 12),
            'key_hash' => hash('sha256', $plainTextKey),
            'requests_count' => 10,
            'quota_limit' => 10,
        ]);

        $this->withHeader('X-API-Key', $plainTextKey)
            ->getJson('/api/ping')
            ->assertStatus(429)
            ->assertJson([
                'message' => 'API key quota has been exhausted.',
            ]);
    }

    public function test_ip_whitelist_is_enforced(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);
        $plainTextKey = 'kfg_live_ipmiddlewarekey000000000000000000';

        ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($plainTextKey, 0, 12),
            'key_hash' => hash('sha256', $plainTextKey),
            'ip_whitelist' => ['10.0.0.1'],
        ]);

        $this->withHeader('X-API-Key', $plainTextKey)
            ->getJson('/api/ping')
            ->assertForbidden()
            ->assertJson([
                'message' => 'IP address is not allowed for this API key.',
            ]);
    }

    public function test_rate_limit_is_enforced_per_api_key(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);
        $plainTextKey = 'kfg_live_ratelimitmiddlewarekey000000000000';

        $apiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($plainTextKey, 0, 12),
            'key_hash' => hash('sha256', $plainTextKey),
            'rate_limit_per_minute' => 2,
        ]);

        RateLimiter::clear("api-key-rate:{$apiKey->id}");

        $this->withHeader('X-API-Key', $plainTextKey)->getJson('/api/ping')->assertOk();
        $this->withHeader('X-API-Key', $plainTextKey)->getJson('/api/ping')->assertOk();
        $this->withHeader('X-API-Key', $plainTextKey)
            ->getJson('/api/ping')
            ->assertStatus(429)
            ->assertJson([
                'message' => 'API key rate limit exceeded.',
            ]);
    }

    public function test_rate_limit_bucket_is_isolated_per_api_key(): void
    {
        $owner = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $firstPlainTextKey = 'kfg_live_firstlimitmiddlewarekey000000000';
        $secondPlainTextKey = 'kfg_live_secondlimitmiddlewarekey00000000';

        $firstApiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($firstPlainTextKey, 0, 12),
            'key_hash' => hash('sha256', $firstPlainTextKey),
            'rate_limit_per_minute' => 1,
            'ip_whitelist' => null,
        ]);

        $secondApiKey = ApiKey::factory()->active()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'key_prefix' => substr($secondPlainTextKey, 0, 12),
            'key_hash' => hash('sha256', $secondPlainTextKey),
            'rate_limit_per_minute' => 1,
            'ip_whitelist' => null,
        ]);

        RateLimiter::clear("api-key-rate:{$firstApiKey->id}");
        RateLimiter::clear("api-key-rate:{$secondApiKey->id}");

        $this->withHeader('X-API-Key', $firstPlainTextKey)->getJson('/api/ping')->assertOk();
        $this->withHeader('X-API-Key', $firstPlainTextKey)->getJson('/api/ping')->assertStatus(429);

        $this->withHeader('X-API-Key', $secondPlainTextKey)->getJson('/api/ping')->assertOk();
    }
}
