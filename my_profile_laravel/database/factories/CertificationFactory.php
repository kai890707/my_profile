<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Certification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-5 years', 'now');
        $expiryDate = fake()->optional(0.6)->dateTimeBetween($issueDate, '+5 years');

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'PMP Certification',
                'Google Analytics Certified',
                'AWS Solutions Architect',
                'Microsoft Certified Professional',
                'CompTIA Security+',
                'Certified ScrumMaster',
                'CISSP',
                'Oracle Certified Professional',
            ]),
            'issuer' => fake()->company(),
            'issue_date' => $issueDate->format('Y-m-d'),
            'expiry_date' => $expiryDate?->format('Y-m-d'),
            'description' => fake()->optional(0.7)->sentence(),
            'file_data' => null,
            'file_mime' => null,
            'file_size' => null,
            'approval_status' => 'pending',
            'rejected_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the certification has a file.
     */
    public function withFile(): static
    {
        $content = fake()->text(1000);

        return $this->state(fn (array $attributes) => [
            'file_data' => $content,
            'file_mime' => 'application/pdf',
            'file_size' => strlen($content),
        ]);
    }

    /**
     * Indicate that the certification is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the certification is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the certification is rejected.
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
