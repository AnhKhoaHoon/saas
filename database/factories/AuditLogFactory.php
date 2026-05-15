<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'auditable_type' => Project::class,
            'auditable_id' => Project::factory(),
            'action' => fake()->randomElement([
                'project.created',
                'api_key.created',
                'api_key.revoked',
                'team_member.invited',
            ]),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'meta' => [
                'source' => fake()->randomElement(['dashboard', 'api', 'system']),
            ],
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
