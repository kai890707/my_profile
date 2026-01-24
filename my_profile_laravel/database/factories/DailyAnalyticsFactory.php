<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DailyAnalytics;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyAnalytics>
 */
class DailyAnalyticsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DailyAnalytics::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'salesperson_id' => User::where('role', 'salesperson')->inRandomOrder()->first()?->id
                ?? User::factory()->create(['role' => 'salesperson'])->id,
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'profile_views_count' => fake()->numberBetween(0, 100),
            'contact_requests_count' => fake()->numberBetween(0, 10),
            'unique_visitors_count' => fake()->numberBetween(0, 80),
        ];
    }

    /**
     * Indicate that the analytics is for today.
     */
    public function today(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'date' => now()->toDateString(),
            ];
        });
    }

    /**
     * Indicate that the analytics is for yesterday.
     */
    public function yesterday(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'date' => now()->subDay()->toDateString(),
            ];
        });
    }

    /**
     * Indicate that the analytics has no activity.
     */
    public function noActivity(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'profile_views_count' => 0,
                'contact_requests_count' => 0,
                'unique_visitors_count' => 0,
            ];
        });
    }

    /**
     * Indicate that the analytics has high activity.
     */
    public function highActivity(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'profile_views_count' => fake()->numberBetween(100, 500),
                'contact_requests_count' => fake()->numberBetween(10, 50),
                'unique_visitors_count' => fake()->numberBetween(80, 400),
            ];
        });
    }
}
