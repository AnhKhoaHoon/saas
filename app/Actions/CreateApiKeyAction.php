<?php

namespace App\Actions;

use App\Actions\Results\CreateApiKeyResult;
use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateApiKeyAction
{
    /**
     * Create a new API key and return the plaintext secret exactly once to the caller.
     *
     * @param  array<string, mixed>  $input
     *
     * @throws AuthorizationException
     */
    public function execute(User $actor, Project $project, array $input): CreateApiKeyResult
    {
        $this->ensureActorCanManageProject($actor, $project);

        return DB::transaction(function () use ($actor, $project, $input) {
            // Generate the raw secret in application memory only; the database should never persist this value.
            $plainTextKey = $this->generatePlainTextKey();

            $apiKey = ApiKey::create([
                'project_id' => $project->id,
                'created_by' => $actor->id,
                'name' => $input['name'],
                // Store a short prefix so support/debug screens can identify the key without exposing the secret.
                'key_prefix' => Str::substr($plainTextKey, 0, 12),
                'key_hash' => hash('sha256', $plainTextKey),
                'status' => $input['status'] ?? 'active',
                'rate_limit_per_minute' => $input['rate_limit_per_minute'] ?? 60,
                'quota_limit' => $input['quota_limit'] ?? null,
                'requests_count' => 0,
                'scopes' => $input['scopes'] ?? ['read'],
                'ip_whitelist' => $input['ip_whitelist'] ?? null,
                'expires_at' => $input['expires_at'] ?? null,
            ]);

            // Write the event immediately so key creation is traceable even if the raw secret is shown only once.
            AuditLog::create([
                'user_id' => $actor->id,
                'project_id' => $project->id,
                'auditable_type' => ApiKey::class,
                'auditable_id' => $apiKey->id,
                'action' => 'api_key.created',
                'description' => "API key {$apiKey->name} was created for project {$project->name}.",
                'meta' => [
                    'key_prefix' => $apiKey->key_prefix,
                    'scopes' => $apiKey->scopes,
                    'rate_limit_per_minute' => $apiKey->rate_limit_per_minute,
                ],
                'occurred_at' => now(),
            ]);

            return new CreateApiKeyResult($apiKey, $plainTextKey);
        });
    }

    /**
     * @throws AuthorizationException
     */
    protected function ensureActorCanManageProject(User $actor, Project $project): void
    {
        $isProjectMember = $project->teamMembers()
            ->where('user_id', $actor->id)
            ->whereIn('role', ['owner', 'admin', 'member'])
            ->exists();

        if (! $isProjectMember) {
            throw new AuthorizationException('You are not allowed to create API keys for this project.');
        }
    }

    protected function generatePlainTextKey(): string
    {
        do {
            // Prefixing the secret makes keys easier to classify later across environments and providers.
            $plainTextKey = 'kfg_live_'.Str::lower(Str::random(40));
            $hash = hash('sha256', $plainTextKey);
        } while (ApiKey::query()->where('key_hash', $hash)->exists());

        return $plainTextKey;
    }
}
