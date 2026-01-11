<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalespersonProfile>
 */
class SalespersonProfileFactory extends Factory
{
    protected $model = SalespersonProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => null,
            'full_name' => fake()->name(),
            'phone' => fake()->numerify('09########'),
            'bio' => fake()->optional(0.7)->paragraphs(2, true),
            'specialties' => fake()->optional(0.8)->randomElement([
                '壽險', '產險', '投資理財', '退休規劃', '保險規劃',
            ]),
            'service_regions' => fake()->optional(0.6)->randomElements(
                ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市'],
                fake()->numberBetween(1, 3)
            ),
            'approval_status' => 'pending',
            'rejected_reason' => null,
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the profile is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the profile is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
            'rejected_reason' => null,
        ]);
    }

    /**
     * Indicate that the profile is rejected.
     */
    public function rejected(?string $reason = null): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'rejected',
            'rejected_reason' => $reason ?? fake()->sentence(),
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Associate with a company.
     */
    public function withCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => Company::factory(),
        ]);
    }
}
