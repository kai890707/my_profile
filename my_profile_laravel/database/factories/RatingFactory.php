<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContactRequest;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rating::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'salesperson_id' => User::factory(),
            'contact_request_id' => ContactRequest::factory(),
            'rating' => fake()->randomElement([1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0]),
            'comment' => fake()->optional(0.7)->realText(200),
            'reply' => null,
            'replied_at' => null,
            'status' => Rating::STATUS_APPROVED,
            'is_hidden' => false,
            'edited_at' => null,
            'edit_count' => 0,
            'ip_address' => fake()->ipv4(),
        ];
    }

    /**
     * Indicate that the rating is 5 stars.
     */
    public function fiveStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5.0,
        ]);
    }

    /**
     * Indicate that the rating is 1 star.
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1.0,
        ]);
    }

    /**
     * Indicate that the rating has a reply.
     */
    public function withReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'reply' => fake()->realText(150),
            'replied_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the rating is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Rating::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the rating is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidden' => true,
        ]);
    }

    /**
     * Indicate that the rating has been edited.
     */
    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'edited_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'edit_count' => 1,
        ]);
    }
}
