<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\SalespersonProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_salesperson_applications(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $token = auth()->login($admin);

        // Create pending applications
        $pending1 = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $pending1->id]);

        $pending2 = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $pending2->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/salesperson-applications');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.data'); // Paginated response
    }

    /** @test */
    public function non_admin_cannot_view_applications(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/salesperson-applications');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_approve_salesperson(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $token = auth()->login($admin);

        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/salesperson-applications/{$applicant->id}/approve");

        $response->assertStatus(200);

        $applicant->refresh();
        $this->assertEquals(User::STATUS_APPROVED, $applicant->salesperson_status);
        $this->assertNotNull($applicant->salesperson_approved_at);
    }

    /** @test */
    public function admin_can_reject_salesperson(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $token = auth()->login($admin);

        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/salesperson-applications/{$applicant->id}/reject", [
                'rejection_reason' => 'Insufficient experience',
                'reapply_days' => 7,
            ]);

        $response->assertStatus(200);

        $applicant->refresh();
        $this->assertEquals(User::ROLE_USER, $applicant->role);
        $this->assertEquals(User::STATUS_REJECTED, $applicant->salesperson_status);
        $this->assertEquals('Insufficient experience', $applicant->rejection_reason);
        $this->assertNotNull($applicant->can_reapply_at);
    }

    /** @test */
    public function reject_validation_requires_rejection_reason(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $token = auth()->login($admin);

        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/salesperson-applications/{$applicant->id}/reject", [
                'rejection_reason' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function admin_can_reject_with_immediate_reapply(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $token = auth()->login($admin);

        $applicant = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/salesperson-applications/{$applicant->id}/reject", [
                'rejection_reason' => 'Please resubmit documents',
                'reapply_days' => 0,
            ]);

        $response->assertStatus(200);

        $applicant->refresh();
        $this->assertNull($applicant->can_reapply_at);
        $this->assertTrue($applicant->canReapply());
    }
}
