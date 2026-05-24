<?php

namespace Tests\Feature;

use App\Actions\ChangeSubscriptionPlanAction;
use App\Actions\CreateProjectAction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_project_owner_membership_and_audit_log(): void
    {
        $owner = User::factory()->create();

        $project = app(CreateProjectAction::class)->execute($owner, [
            'name' => 'Primary Workspace',
            'description' => 'Main production workspace.',
            'settings' => [
                'timezone' => 'Asia/Saigon',
            ],
        ]);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $owner->id,
            'name' => 'Primary Workspace',
            'slug' => 'primary-workspace',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('team_members', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'action' => 'project.created',
            'auditable_type' => Project::class,
            'auditable_id' => $project->id,
        ]);
    }

    public function test_it_generates_incremented_slug_when_owner_already_has_same_project_name(): void
    {
        $owner = User::factory()->create();
        app(ChangeSubscriptionPlanAction::class)->execute($owner, 'pro');

        Project::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Primary Workspace',
            'slug' => 'primary-workspace',
        ]);

        $project = app(CreateProjectAction::class)->execute($owner, [
            'name' => 'Primary Workspace',
        ]);

        $this->assertSame('primary-workspace-2', $project->slug);
    }
}
