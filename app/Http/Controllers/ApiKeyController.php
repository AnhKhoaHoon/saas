<?php

namespace App\Http\Controllers;

use App\Actions\CreateApiKeyAction;
use App\Actions\RevokeApiKeyAction;
use App\Http\Requests\CreateApiKeyRequest;
use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use App\Support\ProjectPermission;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ApiKeyController extends Controller
{
    public function index(Project $project): View
    {
        // Chỉ cho user có permission api_keys.view xem danh sách API key.
        abort_unless($this->canAccessProject(request()->user(), $project, 'api_keys.view'), 403);

        $apiKeys = $project->apiKeys()
            ->with(['creator'])
            ->withCount(['usageLogs'])
            ->latest()
            ->get();

        return view('api-keys.index', [
            'project' => $project->load('owner'),
            'apiKeys' => $apiKeys,
        ]);
    }

    public function show(Project $project, ApiKey $apiKey): View
    {
        abort_unless($apiKey->project_id === $project->id, 404);
        // Chỉ cho user có permission api_keys.view xem chi tiết API key.
        abort_unless($this->canAccessProject(request()->user(), $project, 'api_keys.view'), 403);

        return view('api-keys.show', [
            'project' => $project->load('owner'),
            'apiKey' => $apiKey->load(['creator'])->loadCount(['usageLogs']),
        ]);
    }

    public function create(Project $project): View
    {
        // Chỉ cho user có permission api_keys.create mở form tạo API key.
        abort_unless($this->canAccessProject(request()->user(), $project, 'api_keys.create'), 403);

        return view('api-keys.create', [
            'project' => $project,
        ]);
    }

    public function store(CreateApiKeyRequest $request, Project $project, CreateApiKeyAction $action): RedirectResponse
    {
        $result = $action->execute($request->user(), $project, $request->payload());

        return redirect()
            ->route('projects.api-keys.index', $project)
            ->with('status', "API key {$result->apiKey->name} created successfully.")
            // Flash the raw secret once so the dashboard can show it immediately after creation and then forget it.
            ->with('new_api_key', [
                'project_name' => $project->name,
                'name' => $result->apiKey->name,
                'plain_text_key' => $result->plainTextKey,
                'key_prefix' => $result->apiKey->key_prefix,
            ]);
    }

    public function destroy(Project $project, ApiKey $apiKey, RevokeApiKeyAction $action): RedirectResponse
    {
        abort_unless($apiKey->project_id === $project->id, 404);

        $apiKey = $action->execute(request()->user(), $apiKey);

        return redirect()
            ->route('projects.api-keys.index', $project)
            ->with('status', "API key {$apiKey->name} revoked successfully.");
    }

    protected function canAccessProject(User $user, Project $project, string $permission): bool
    {
        // Delegate việc kiểm tra role-permission theo project cho service RBAC.
        return app(ProjectPermission::class)->userCan($user, $project, $permission);
    }
}
