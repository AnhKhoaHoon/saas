<?php

namespace App\Http\Controllers;

use App\Actions\CreateProjectAction;
use App\Http\Requests\CreateProjectRequest;
use App\Models\ApiKey;
use App\Models\UsageLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $projects = $user->projects()
            ->with(['owner', 'apiKeys' => fn ($query) => $query->latest()->limit(5)])
            ->withCount(['apiKeys', 'teamMembers', 'usageLogs'])
            ->latest()
            ->get();

        $subscription = $user->subscriptions()->latest()->first();
        $projectIds = $projects->pluck('id');

        $usageLogsQuery = UsageLog::query()
            ->with(['project', 'apiKey'])
            ->whereIn('project_id', $projectIds)
            ->latest('occurred_at');

        if ($request->filled('usage_project_id')) {
            $usageLogsQuery->where('project_id', (int) $request->integer('usage_project_id'));
        }

        if ($request->filled('usage_api_key_id')) {
            $usageLogsQuery->where('api_key_id', (int) $request->integer('usage_api_key_id'));
        }

        if ($request->filled('usage_method')) {
            $usageLogsQuery->where('method', strtoupper((string) $request->input('usage_method')));
        }

        if ($request->filled('usage_status_code')) {
            $usageLogsQuery->where('status_code', (int) $request->integer('usage_status_code'));
        }

        // Keep dashboard payload bounded so filters stay fast and the page remains usable without dedicated pagination UI yet.
        $usageLogs = $usageLogsQuery->limit(20)->get();

        $usageApiKeys = ApiKey::query()
            ->whereIn('project_id', $projectIds)
            ->orderBy('name')
            ->get(['id', 'project_id', 'name', 'key_prefix']);

        return view('home', [
            'projects' => $projects,
            'subscription' => $subscription,
            'usageLogs' => $usageLogs,
            'usageApiKeys' => $usageApiKeys,
        ]);
    }

    public function store(CreateProjectRequest $request, CreateProjectAction $action): RedirectResponse
    {
        $project = $action->execute($request->user(), $request->payload());

        return redirect()
            ->route('home')
            ->with('status', "Project {$project->name} created successfully.");
    }
}
