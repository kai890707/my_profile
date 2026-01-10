<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalespersonProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_user(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($user->id, $profile->user->id);
    }

    /** @test */
    public function approval_status_accessor_returns_user_salesperson_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('pending', $profile->approval_status);
    }

    /** @test */
    public function approval_status_accessor_returns_null_when_no_user(): void
    {
        $profile = new SalespersonProfile();

        $this->assertNull($profile->approval_status);
    }

    /** @test */
    public function it_has_correct_fillable_fields(): void
    {
        $fillable = (new SalespersonProfile())->getFillable();

        $expectedFields = [
            'user_id',
            'company_id',
            'full_name',
            'phone',
            'bio',
            'specialties',
            'service_regions',
        ];

        foreach ($expectedFields as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    /** @test */
    public function service_regions_is_cast_to_array(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $profile = SalespersonProfile::factory()->create([
            'user_id' => $user->id,
            'service_regions' => ['台北市', '新北市', '桃園市'],
        ]);

        $this->assertIsArray($profile->service_regions);
        $this->assertCount(3, $profile->service_regions);
        $this->assertEquals(['台北市', '新北市', '桃園市'], $profile->service_regions);
    }

    /** @test */
    public function company_id_can_be_null(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $profile = SalespersonProfile::factory()->create([
            'user_id' => $user->id,
            'company_id' => null,
        ]);

        $this->assertNull($profile->company_id);
    }
}
