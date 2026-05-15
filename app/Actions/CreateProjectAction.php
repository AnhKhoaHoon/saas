<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateProjectAction
{
    /**
     * Create a new project for the given owner and bootstrap its ownership records.
     *
     * @param  array<string, mixed>  $input
     */
    public function execute(User $owner, array $input): Project
    {
        return DB::transaction(function () use ($owner, $input) {
            // Build a unique slug before insert so owner-scoped project URLs remain predictable.
            $slug = $this->generateUniqueSlug($owner, (string) $input['name']);

            $project = Project::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $owner->id,
                'name' => $input['name'],
                'slug' => $slug,
                'description' => $input['description'] ?? null,
                'status' => $input['status'] ?? 'active',
                'settings' => $input['settings'] ?? null,
            ]);

            // Mirror the owner into team_members so all authorization can read from one membership table later.
            TeamMember::create([
                'project_id' => $project->id,
                'user_id' => $owner->id,
                'invited_by' => $owner->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            // Audit creation now so future admin screens and investigations have a reliable event trail.
            AuditLog::create([
                'user_id' => $owner->id,
                'project_id' => $project->id,
                'auditable_type' => Project::class,
                'auditable_id' => $project->id,
                'action' => 'project.created',
                'description' => "Project {$project->name} was created.",
                'meta' => [
                    'slug' => $project->slug,
                    'status' => $project->status,
                ],
                'occurred_at' => now(),
            ]);

            return $project;
        });
    }

    protected function generateUniqueSlug(User $owner, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'project';
        $counter = 2;

        while (
            Project::query()
                ->where('user_id', $owner->id)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
