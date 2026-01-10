<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\SalespersonProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_role_constants(): void
    {
        $this->assertEquals('user', User::ROLE_USER);
        $this->assertEquals('salesperson', User::ROLE_SALESPERSON);
        $this->assertEquals('admin', User::ROLE_ADMIN);
    }

    /** @test */
    public function it_has_correct_status_constants(): void
    {
        $this->assertEquals('pending', User::STATUS_PENDING);
        $this->assertEquals('approved', User::STATUS_APPROVED);
        $this->assertEquals('rejected', User::STATUS_REJECTED);
    }

    /** @test */
    public function is_user_returns_true_for_user_role(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isSalesperson());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function is_salesperson_returns_true_for_salesperson_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $this->assertTrue($user->isSalesperson());
        $this->assertFalse($user->isUser());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function is_admin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isUser());
        $this->assertFalse($user->isSalesperson());
    }

    /** @test */
    public function is_approved_salesperson_returns_true_only_for_approved_salesperson(): void
    {
        $approvedSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $pendingSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $regularUser = User::factory()->create(['role' => User::ROLE_USER]);

        $this->assertTrue($approvedSalesperson->isApprovedSalesperson());
        $this->assertFalse($pendingSalesperson->isApprovedSalesperson());
        $this->assertFalse($regularUser->isApprovedSalesperson());
    }

    /** @test */
    public function is_pending_salesperson_returns_true_only_for_pending_salesperson(): void
    {
        $pendingSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $approvedSalesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        $this->assertTrue($pendingSalesperson->isPendingSalesperson());
        $this->assertFalse($approvedSalesperson->isPendingSalesperson());
    }

    /** @test */
    public function can_reapply_returns_true_when_waiting_period_is_over(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => User::STATUS_REJECTED,
            'can_reapply_at' => now()->subDay(),
        ]);

        $this->assertTrue($user->canReapply());
    }

    /** @test */
    public function can_reapply_returns_false_when_waiting_period_is_not_over(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => User::STATUS_REJECTED,
            'can_reapply_at' => now()->addDays(3),
        ]);

        $this->assertFalse($user->canReapply());
    }

    /** @test */
    public function can_reapply_returns_true_when_no_waiting_period_set(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => User::STATUS_REJECTED,
            'can_reapply_at' => null,
        ]);

        $this->assertTrue($user->canReapply());
    }

    /** @test */
    public function upgrade_to_salesperson_updates_user_role_and_creates_profile(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $profileData = [
            'full_name' => 'John Doe',
            'phone' => '0912345678',
            'bio' => 'Experienced salesperson',
            'specialties' => 'Insurance',
        ];

        $user->upgradeToSalesperson($profileData);

        $this->assertEquals(User::ROLE_SALESPERSON, $user->role);
        $this->assertEquals(User::STATUS_PENDING, $user->salesperson_status);
        $this->assertNotNull($user->salesperson_applied_at);
        $this->assertDatabaseHas('salesperson_profiles', [
            'user_id' => $user->id,
            'full_name' => 'John Doe',
            'phone' => '0912345678',
        ]);
    }

    /** @test */
    public function approve_salesperson_updates_status_and_timestamps(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $user->approveSalesperson($admin->id);

        $this->assertEquals(User::STATUS_APPROVED, $user->salesperson_status);
        $this->assertNotNull($user->salesperson_approved_at);
    }

    /** @test */
    public function reject_salesperson_downgrades_to_user_and_sets_waiting_period(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $reason = 'Insufficient experience';
        $reapplyDays = 7;

        $user->rejectSalesperson($reason, $reapplyDays);

        $this->assertEquals(User::ROLE_USER, $user->role);
        $this->assertEquals(User::STATUS_REJECTED, $user->salesperson_status);
        $this->assertEquals($reason, $user->rejection_reason);
        $this->assertNotNull($user->can_reapply_at);
        $this->assertTrue($user->can_reapply_at->isFuture());
    }

    /** @test */
    public function reject_salesperson_allows_immediate_reapply_when_no_waiting_period(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $user->rejectSalesperson('Need more documents', 0);

        $this->assertEquals(User::ROLE_USER, $user->role);
        $this->assertEquals(User::STATUS_REJECTED, $user->salesperson_status);
        $this->assertNull($user->can_reapply_at);
        $this->assertTrue($user->canReapply());
    }
}
