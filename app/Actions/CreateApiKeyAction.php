<?php

namespace App\Actions;

use App\Actions\Results\CreateApiKeyResult;
use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use App\Support\PlanCatalog;
use App\Support\ProjectPermission;
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
        $this->ensureApiKeyLimitAllowsCreation($project);

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
    private function ensureApiKeyLimitAllowsCreation(Project $project): void
    {
        // Lấy giới hạn plan của owner project.
        $limits = PlanCatalog::limitsFor($project->owner);

        // Null nghĩa là plan không giới hạn API key.
        if ($limits['api_key_limit'] === null) {
            // Cho phép tạo API key.
            return;
        }

        // Đếm tổng API key trên tất cả project của owner.
        $currentApiKeys = $project->owner
            // Lấy các project của owner.
            ->projects()
            // Đếm tất cả API key thuộc các project đó.
            ->withCount('apiKeys')
            // Lấy danh sách count.
            ->get()
            // Cộng tổng api_keys_count.
            ->sum('api_keys_count');

        // Nếu đã chạm giới hạn thì chặn tạo API key mới.
        if ($currentApiKeys >= $limits['api_key_limit']) {
            // Ném AuthorizationException để cả web/API action đều bị chặn rõ ràng.
            throw new AuthorizationException('Your current plan has reached the API key limit.');
        }
    }

    /**
     * @throws AuthorizationException
     */
    protected function ensureActorCanManageProject(User $actor, Project $project): void
    {
        // Kiểm tra quyền tạo API key bằng Spatie permission api_keys.create.
        $canCreateApiKey = app(ProjectPermission::class)->userCan($actor, $project, 'api_keys.create');

        // Nếu role hiện tại không có quyền tạo key thì ném lỗi authorization.
        if (! $canCreateApiKey) {
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
