<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\CompanyPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPolicyTest extends TestCase
{
    use RefreshDatabase;

    private CompanyPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CompanyPolicy();
    }

    /** @test */
    public function create_allows_only_approved_salesperson(): void
    {
        $approvedSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $pendingSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $rejectedSalesperson = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => User::STATUS_REJECTED,
        ]);

        $regularUser = User::factory()->create(['role' => User::ROLE_USER]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->assertTrue($this->policy->create($approvedSalesperson));
        $this->assertFalse($this->policy->create($pendingSalesperson));
        $this->assertFalse($this->policy->create($rejectedSalesperson));
        $this->assertFalse($this->policy->create($regularUser));
        $this->assertFalse($this->policy->create($admin));
    }
}
