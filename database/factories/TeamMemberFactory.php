<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'invited_by' => User::factory(),
            'role' => fake()->randomElement(['owner', 'admin', 'member', 'viewer']),
            'joined_at' => fake()->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
