<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plan = fake()->randomElement(['free', 'pro', 'enterprise']);

        return [
            'user_id' => User::factory(),
            'plan' => $plan,
            'provider' => $plan === 'free' ? null : 'stripe',
            'provider_subscription_id' => $plan === 'free' ? null : 'sub_'.fake()->unique()->lexify('????????????'),
            'status' => fake()->randomElement(['active', 'trialing', 'canceled']),
            'project_limit' => match ($plan) {
                'free' => 1,
                'pro' => 10,
                default => null,
            },
            'api_key_limit' => match ($plan) {
                'free' => 5,
                'pro' => 100,
                default => null,
            },
            'monthly_request_limit' => match ($plan) {
                'free' => 10000,
                'pro' => 1000000,
                default => null,
            },
            'trial_ends_at' => fake()->optional()->dateTimeBetween('now', '+14 days'),
            'ends_at' => null,
        ];
    }
}
