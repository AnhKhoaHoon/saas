<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\TeamInvite;
use App\Models\TeamMember;
use App\Models\UsageLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'KeyForge Owner',
            'email' => 'owner@keyforge.test',
            'is_admin' => true,
        ]);

        // Gán role Spatie global để demo admin có permission admin.access.
        $owner->assignRole('platform_admin');

        $teamUsers = User::factory(4)->create();

        Subscription::factory()->create([
            'user_id' => $owner->id,
            'plan' => 'pro',
            'provider' => 'stripe',
            'provider_subscription_id' => 'sub_keyforge_owner_demo',
            'status' => 'active',
            'project_limit' => 10,
            'api_key_limit' => 100,
            'monthly_request_limit' => 1000000,
        ]);

        $projects = Project::factory(3)->create([
            'user_id' => $owner->id,
            'status' => 'active',
        ]);

        foreach ($projects as $index => $project) {
            TeamMember::create([
                'project_id' => $project->id,
                'user_id' => $owner->id,
                'invited_by' => $owner->id,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            $projectMembers = $teamUsers->random(fake()->numberBetween(2, 3));

            foreach ($projectMembers as $member) {
                TeamMember::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $member->id,
                    ],
                    [
                        'invited_by' => $owner->id,
                        'role' => fake()->randomElement(['admin', 'member', 'viewer']),
                        'joined_at' => now()->subDays(fake()->numberBetween(1, 30)),
                    ]
                );
            }

            TeamInvite::factory()->create([
                'project_id' => $project->id,
                'invited_by' => $owner->id,
                'email' => 'invite'.($index + 1).'@keyforge.test',
                'role' => 'viewer',
            ]);

            $apiKeys = ApiKey::factory(fake()->numberBetween(2, 4))
                ->active()
                ->create([
                    'project_id' => $project->id,
                    'created_by' => $owner->id,
                ]);

            foreach ($apiKeys as $apiKey) {
                UsageLog::factory(fake()->numberBetween(12, 20))
                    ->create([
                        'project_id' => $project->id,
                        'api_key_id' => $apiKey->id,
                    ]);

                AuditLog::factory()->create([
                    'user_id' => $owner->id,
                    'project_id' => $project->id,
                    'auditable_type' => ApiKey::class,
                    'auditable_id' => $apiKey->id,
                    'action' => 'api_key.created',
                    'description' => "API key {$apiKey->name} was created for {$project->name}.",
                ]);
            }
            // owner@keyforge.test
            // password
            AuditLog::factory()->create([
                'user_id' => $owner->id,
                'project_id' => $project->id,
                'auditable_type' => Project::class,
                'auditable_id' => $project->id,
                'action' => 'project.created',
                'description' => "Project {$project->name} was provisioned for the owner workspace.",
            ]);
        }
    }
}
