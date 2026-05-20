<?php

namespace App\Http\Controllers;

use App\Actions\CreateProjectAction;
use App\Http\Requests\CreateProjectRequest;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = $request->user()->projects()
            ->withCount(['apiKeys', 'teamMembers', 'usageLogs'])
            ->latest()
            ->get();

        return view('projects.index', [
            'projects' => $projects,
            'subscription' => $request->user()->subscriptions()->latest()->first(),
        ]);
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(CreateProjectRequest $request, CreateProjectAction $action): RedirectResponse
    {
        $project = $action->execute($request->user(), $request->payload());

        return redirect()
            ->route('projects.show', $project)
            ->with('status', "Project {$project->name} created successfully.");
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        $project->loadCount(['apiKeys', 'usageLogs', 'teamMembers']);
        
        $apiKeys = $project->apiKeys()->latest()->limit(5)->get();
        $usageLogs = $project->usageLogs()->with('apiKey')->latest('occurred_at')->limit(10)->get();

        return view('projects.show', [
            'project' => $project,
            'apiKeys' => $apiKeys,
            'usageLogs' => $usageLogs,
        ]);
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'timezone' => ['required', 'string', 'timezone'],
        ]);

        $project->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'settings' => array_merge($project->settings ?? [], [
                'timezone' => $validated['timezone']
            ]),
        ]);

        return redirect()->route('projects.show', $project)->with('status', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('status', 'Project deleted successfully.');
    }
}
