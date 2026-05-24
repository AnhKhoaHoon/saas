<?php

namespace App\Actions;

use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\ProjectPermission;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class RevokeApiKeyAction
{
    /**
     * Revoke an API key without deleting historical records tied to it.
     *
     * @throws AuthorizationException
     */
    public function execute(User $actor, ApiKey $apiKey): ApiKey
    {
        $this->ensureActorCanManageApiKey($actor, $apiKey);

        return DB::transaction(function () use ($actor, $apiKey) {
            if ($apiKey->status !== 'revoked') {
                // Mark the key as revoked instead of deleting it so usage history and audits remain intact.
                $apiKey->forceFill([
                    'status' => 'revoked',
                    'revoked_at' => now(),
                ])->save();

                // Record the revoke event immediately so investigations can trace who disabled a key and when.
                AuditLog::create([
                    'user_id' => $actor->id,
                    'project_id' => $apiKey->project_id,
                    'auditable_type' => ApiKey::class,
                    'auditable_id' => $apiKey->id,
                    'action' => 'api_key.revoked',
                    'description' => "API key {$apiKey->name} was revoked.",
                    'meta' => [
                        'key_prefix' => $apiKey->key_prefix,
                    ],
                    'occurred_at' => now(),
                ]);
            }

            return $apiKey->fresh();
        });
    }

    /**
     * @throws AuthorizationException
     */
    protected function ensureActorCanManageApiKey(User $actor, ApiKey $apiKey): void
    {
        // Kiểm tra quyền revoke API key bằng Spatie permission api_keys.revoke.
        $canRevokeApiKey = app(ProjectPermission::class)->userCan($actor, $apiKey->project, 'api_keys.revoke');

        // Nếu role hiện tại không có quyền revoke key thì ném lỗi authorization.
        if (! $canRevokeApiKey) {
            throw new AuthorizationException('You are not allowed to revoke API keys for this project.');
        }
    }
}
