<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamMemberController extends Controller
{
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $teamMembers = $project->teamMembers()->with('user')->get();
        $pendingInvites = $project->teamInvites()->where('status', 'pending')->get();

        return view('projects.team.index', compact('project', 'teamMembers', 'pendingInvites'));
    }
}
