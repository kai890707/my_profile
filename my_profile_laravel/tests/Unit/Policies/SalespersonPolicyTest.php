<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\SalespersonPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalespersonPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SalespersonPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new SalespersonPolicy;
    }

    /** @test */
    public function view_dashboard_allows_salesperson_with_any_status(): void
    {
        $pendingSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $approvedSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $this->assertTrue($this->policy->viewDashboard($pendingSalesperson));
        $this->assertTrue($this->policy->viewDashboard($approvedSalesperson));
    }

    /** @test */
    public function view_dashboard_denies_regular_user(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->assertFalse($this->policy->viewDashboard($user));
    }

    /** @test */
    public function view_dashboard_allows_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->assertTrue($this->policy->viewDashboard($admin));
    }

    /** @test */
    public function create_company_allows_only_approved_salesperson(): void
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

        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->assertTrue($this->policy->createCompany($approvedSalesperson));
        $this->assertFalse($this->policy->createCompany($pendingSalesperson));
        $this->assertFalse($this->policy->createCompany($rejectedSalesperson));
        $this->assertFalse($this->policy->createCompany($user));
    }

    /** @test */
    public function create_rating_allows_only_approved_salesperson(): void
    {
        $approvedSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $pendingSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $this->assertTrue($this->policy->createRating($approvedSalesperson));
        $this->assertFalse($this->policy->createRating($pendingSalesperson));
    }

    /** @test */
    public function can_be_searched_allows_only_approved_salesperson(): void
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

        $this->assertTrue($this->policy->canBeSearched($approvedSalesperson));
        $this->assertFalse($this->policy->canBeSearched($pendingSalesperson));
        $this->assertFalse($this->policy->canBeSearched($rejectedSalesperson));
    }
}
