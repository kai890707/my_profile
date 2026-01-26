<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Rating;
use App\Models\RatingReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatingReport>
 */
class RatingReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RatingReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating_id' => Rating::factory(),
            'reporter_user_id' => User::factory(),
            'reason' => fake()->randomElement([
                RatingReport::REASON_FALSE_INFO,
                RatingReport::REASON_MALICIOUS,
                RatingReport::REASON_SENSITIVE,
                RatingReport::REASON_SPAM,
                RatingReport::REASON_OTHER,
            ]),
            'description' => fake()->optional(0.8)->realText(100),
            'status' => RatingReport::STATUS_PENDING,
            'reviewed_by_admin_id' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that the report is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RatingReport::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the report is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RatingReport::STATUS_APPROVED,
            'reviewed_by_admin_id' => User::factory(),
            'reviewed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the report is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RatingReport::STATUS_REJECTED,
            'reviewed_by_admin_id' => User::factory(),
            'reviewed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }
}
