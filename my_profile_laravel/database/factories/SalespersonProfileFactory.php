<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalespersonProfile>
 */
class SalespersonProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = SalespersonProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => null,
            'full_name' => fake()->name(),
            'phone' => fake()->numerify('09########'),
            'bio' => fake()->optional()->paragraph(),
            'specialties' => fake()->optional()->sentence(),
            'service_regions' => [],
        ];
    }
}
