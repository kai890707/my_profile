<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use en_US locale for faker to avoid transliteration issues
        $faker = \Faker\Factory::create('en_US');

        return [
            'name' => $faker->name(),
            'username' => $faker->unique()->numerify('user####'),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'user',
            'status' => 'active', // Important: Users must be active to pass JWT middleware check
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is a regular user (default state).
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
            'salesperson_status' => null,
        ]);
    }

    /**
     * Indicate that the user is a pending salesperson.
     */
    public function pendingSalesperson(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'salesperson',
            'salesperson_status' => 'pending',
            'salesperson_applied_at' => now(),
        ])->afterCreating(function ($user) {
            $user->salespersonProfile()->create([
                'full_name' => fake()->name(),
                'phone' => fake()->numerify('09########'),
            ]);
        });
    }

    /**
     * Indicate that the user is an approved salesperson.
     */
    public function approvedSalesperson(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'salesperson',
            'salesperson_status' => 'approved',
            'salesperson_applied_at' => now()->subDays(7),
            'salesperson_approved_at' => now(),
        ])->afterCreating(function ($user) {
            $user->salespersonProfile()->create([
                'full_name' => fake()->name(),
                'phone' => fake()->numerify('09########'),
            ]);
        });
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'salesperson_status' => null,
        ]);
    }
}
