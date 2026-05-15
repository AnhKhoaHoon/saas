<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UsageLog>
 */
class UsageLogFactory extends Factory
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
            'api_key_id' => ApiKey::factory(),
            'request_id' => (string) Str::uuid(),
            'endpoint' => fake()->randomElement([
                '/v1/projects',
                '/v1/api-keys',
                '/v1/usage',
                '/v1/billing/summary',
            ]),
            'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'status_code' => fake()->randomElement([200, 201, 204, 400, 401, 403, 404, 422, 429, 500]),
            'response_time_ms' => fake()->numberBetween(20, 3000),
            'response_size_bytes' => fake()->numberBetween(256, 524288),
            'units' => fake()->numberBetween(1, 25),
            'ip_address' => fake()->ipv4(),
            'meta' => [
                'environment' => fake()->randomElement(['sandbox', 'production']),
                'region' => fake()->randomElement(['ap-southeast-1', 'us-east-1', 'eu-west-1']),
            ],
            'occurred_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status_code' => fake()->randomElement([200, 201, 204]),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status_code' => fake()->randomElement([400, 401, 403, 404, 422, 429, 500]),
        ]);
    }
}
