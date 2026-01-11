<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalespersonControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_user_can_upgrade_to_salesperson(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/salesperson/upgrade', [
                'full_name' => 'John Doe',
                'phone' => '0912345678',
                'bio' => 'Experienced salesperson',
                'specialties' => 'Insurance',
                'service_regions' => ['台北市'],
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals(User::ROLE_SALESPERSON, $user->role);
        $this->assertEquals(User::STATUS_PENDING, $user->salesperson_status);
        $this->assertDatabaseHas('salesperson_profiles', [
            'user_id' => $user->id,
            'full_name' => 'John Doe',
        ]);
    }

    /** @test */
    public function salesperson_cannot_upgrade_again(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/salesperson/upgrade', [
                'full_name' => 'John Doe',
                'phone' => '0912345678',
            ]);

        $response->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function it_validates_upgrade_request(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/salesperson/upgrade', [
                'full_name' => '',
                'phone' => 'invalid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['full_name', 'phone']);
    }

    /** @test */
    public function it_can_get_salesperson_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'role' => User::ROLE_SALESPERSON,
                    'salesperson_status' => User::STATUS_PENDING,
                ],
            ]);
    }

    /** @test */
    public function approved_salesperson_can_update_profile(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        $profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/salesperson/profile', [
                'full_name' => 'Updated Name',
                'phone' => '0987654321',
                'bio' => 'Updated bio',
            ]);

        $response->assertStatus(200);

        $profile->refresh();
        $this->assertEquals('Updated Name', $profile->full_name);
        $this->assertEquals('0987654321', $profile->phone);
    }

    /** @test */
    public function it_can_list_approved_salespeople_publicly(): void
    {
        // Create approved salespeople
        $approved1 = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $approved1->id]);

        $approved2 = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $approved2->id]);

        // Create pending salesperson (should not appear)
        $pending = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_PENDING,
        ]);
        SalespersonProfile::factory()->create(['user_id' => $pending->id]);

        $response = $this->getJson('/api/salespeople');

        $response->assertStatus(200);

        // Paginated response has data nested in data.data
        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    /**
     * ========================================
     * Approval Status API Tests (Test 3.1-3.5)
     * ========================================
     */

    /** @test */
    public function salesperson_can_retrieve_aggregated_approval_status(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $token = auth()->login($user);

        // 建立 Profile
        $profile = SalespersonProfile::factory()->create([
            'user_id' => $user->id,
            'approval_status' => 'approved',
        ]);

        // 建立 Company
        $company = \App\Models\Company::factory()->create(['approval_status' => 'approved']);
        $profile->update(['company_id' => $company->id]);

        // 建立 Certifications
        \App\Models\Certification::factory()->create([
            'user_id' => $user->id,
            'name' => 'Cert 1',
            'approval_status' => 'approved',
        ]);
        \App\Models\Certification::factory()->create([
            'user_id' => $user->id,
            'name' => 'Cert 2',
            'approval_status' => 'pending',
        ]);

        // 建立 Experiences
        \App\Models\Experience::factory()->create([
            'user_id' => $user->id,
            'company' => 'Company A',
            'approval_status' => 'approved',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/approval-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'profile_status' => 'approved',
                    'company_status' => 'approved',
                ],
            ])
            ->assertJsonCount(2, 'data.certifications')
            ->assertJsonCount(1, 'data.experiences')
            ->assertJsonStructure([
                'data' => [
                    'certifications' => [
                        '*' => ['id', 'name', 'approval_status', 'rejected_reason'],
                    ],
                    'experiences' => [
                        '*' => ['id', 'company', 'position', 'approval_status', 'rejected_reason'],
                    ],
                ],
            ]);
    }

    /** @test */
    public function approval_status_returns_pending_when_no_profile_exists(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $token = auth()->login($user);
        // 不建立 profile

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/approval-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'profile_status' => 'pending',
                    'company_status' => null,
                ],
            ])
            ->assertJsonCount(0, 'data.certifications')
            ->assertJsonCount(0, 'data.experiences');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_approval_status(): void
    {
        $response = $this->getJson('/api/salesperson/approval-status');

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_access_approval_status(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/approval-status');

        $response->assertStatus(403);
    }

    /** @test */
    public function approval_status_uses_eager_loading_to_avoid_n_plus_1(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $token = auth()->login($user);

        $profile = SalespersonProfile::factory()->create(['user_id' => $user->id]);
        $company = \App\Models\Company::factory()->create();
        $profile->update(['company_id' => $company->id]);

        \App\Models\Certification::factory()->count(5)->create(['user_id' => $user->id]);
        \App\Models\Experience::factory()->count(5)->create(['user_id' => $user->id]);

        // 啟用查詢日誌
        \DB::enableQueryLog();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/approval-status');

        $queries = \DB::getQueryLog();

        $response->assertStatus(200);

        // 查詢數應該 <= 6（避免 N+1）
        // Expected queries: auth user, profile, company, certifications, experiences, + 1 for transaction/settings
        $this->assertLessThanOrEqual(6, count($queries));
    }

    /**
     * ========================================
     * Salesperson Status API Tests (Test 4.1-4.4)
     * ========================================
     */

    /** @test */
    public function salesperson_can_retrieve_status_with_new_fields(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
            'salesperson_applied_at' => now()->subDays(30),
            'salesperson_approved_at' => now()->subDays(25),
            'rejection_reason' => null,
            'can_reapply_at' => null,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'role',
                    'salesperson_status',
                    'salesperson_applied_at',
                    'salesperson_approved_at',
                    'rejection_reason',
                    'can_reapply',
                    'can_reapply_at',
                    'days_until_reapply',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'role' => User::ROLE_SALESPERSON,
                    'salesperson_status' => User::STATUS_APPROVED,
                    'can_reapply' => false,
                    'days_until_reapply' => null,
                ],
            ]);
    }

    /** @test */
    public function regular_user_can_retrieve_status(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => null,
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'role' => User::ROLE_USER,
                    'salesperson_status' => null,
                    'can_reapply' => false,
                ],
            ]);
    }

    /** @test */
    public function rejected_user_can_retrieve_reapply_information(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => User::STATUS_REJECTED,
            'rejection_reason' => '資料不完整',
            'can_reapply_at' => now()->addDays(10),
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'salesperson_status' => User::STATUS_REJECTED,
                    'rejection_reason' => '資料不完整',
                    'can_reapply' => false, // 未到日期
                    'days_until_reapply' => 10,
                ],
            ]);
    }

    /** @test */
    public function status_shows_correct_days_until_reapply_for_expired_date(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
            'salesperson_status' => User::STATUS_REJECTED,
            'can_reapply_at' => now()->subDays(5), // 5 天前
        ]);
        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/salesperson/status');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertTrue($data['can_reapply']); // 已過期，可重新申請
        $this->assertEquals(0, $data['days_until_reapply']); // Should be 0 or negative
    }
}
