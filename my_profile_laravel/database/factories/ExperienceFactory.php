<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Experience::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-10 years', '-1 year');
        $endDate = fake()->optional(0.7)->dateTimeBetween($startDate, 'now');

        return [
            'user_id' => User::factory(),
            'company' => fake()->company(),
            'position' => fake()->jobTitle(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate?->format('Y-m-d'),
            'description' => fake()->optional(0.8)->paragraphs(2, true),
            'sort_order' => 0,
            'approval_status' => 'approved',
            'rejected_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the experience is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the experience is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the experience is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'rejected',
            'rejected_reason' => fake()->sentence(),
            'approved_at' => null,
        ]);
    }
}
