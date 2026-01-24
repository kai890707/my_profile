<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactRequest>
 */
class ContactRequestFactory extends Factory
{
    protected $model = ContactRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomId = fake()->unique()->randomNumber(5);

        return [
            'user_id' => User::factory(),
            'salesperson_id' => User::factory()->create(['role' => 'salesperson']),
            'customer_name' => 'Customer '.$randomId,
            'customer_email' => "customer{$randomId}@example.com",
            'customer_phone' => fake()->randomElement(['0912345678', '0987654321', null]),
            'message' => fake()->text(100),
            'status' => fake()->randomElement(['pending', 'contacted', 'closed']),
        ];
    }

    /**
     * Indicate the contact request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate the contact request has been contacted.
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'contacted',
        ]);
    }

    /**
     * Indicate the contact request is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}
