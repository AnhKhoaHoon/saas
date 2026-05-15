<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plainKey = 'kfg_'.Str::lower(Str::random(40));

        return [
            'project_id' => Project::factory(),
            'created_by' => User::factory(),
            'name' => fake()->words(2, true),
            'key_prefix' => Str::substr($plainKey, 0, 12),
            'key_hash' => hash('sha256', $plainKey),
            'status' => fake()->randomElement(['active', 'revoked', 'expired']),
            'rate_limit_per_minute' => fake()->numberBetween(60, 600),
            'quota_limit' => fake()->numberBetween(1000, 1000000),
            'requests_count' => fake()->numberBetween(0, 5000),
            'scopes' => fake()->randomElements(['read', 'write', 'billing', 'analytics'], fake()->numberBetween(1, 4)),
            'ip_whitelist' => fake()->boolean(35) ? [fake()->ipv4(), fake()->ipv4()] : null,
            'last_used_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+1 year'),
            'revoked_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => 'active',
            'revoked_at' => null,
            'expires_at' => fake()->optional()->dateTimeBetween('+1 day', '+1 year'),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => [
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);
    }
}
