<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContactEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactEvent>
 */
class ContactEventFactory extends Factory
{
    protected $model = ContactEvent::class;

    public function definition(): array
    {
        return [
            'salesperson_id' => User::factory(),
            'user_id' => null,
            'event_type' => fake()->randomElement(['profile_view', 'contact_form_submission']),
            'ip_address_hash' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function profileView(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'profile_view',
        ]);
    }

    public function contactInfoReveal(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'contact_info_reveal',
        ]);
    }

    public function withUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
        ]);
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
