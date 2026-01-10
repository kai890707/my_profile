<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'tax_id' => fake()->unique()->numerify('########'),
            'is_personal' => false,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the company is a personal studio.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_id' => null,
            'is_personal' => true,
        ]);
    }

    /**
     * Indicate that the company is a registered company with tax ID.
     */
    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_id' => fake()->unique()->numerify('########'),
            'is_personal' => false,
        ]);
    }
}
