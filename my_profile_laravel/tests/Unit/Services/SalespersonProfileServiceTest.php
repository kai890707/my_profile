<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;
use App\Services\SalespersonProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalespersonProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private SalespersonProfileService $profileService;

    private User $user;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profileService = new SalespersonProfileService;

        $this->user = User::create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        $industry = Industry::create([
            'name' => 'Technology',
            'slug' => 'technology',
        ]);

        $this->company = Company::create([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'industry_id' => $industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_get_all_returns_only_approved_profiles(): void
    {
        $user2 = User::create([
            'username' => 'user2',
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Approved Profile',
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);

        SalespersonProfile::create([
            'user_id' => $user2->id,
            'full_name' => 'Pending Profile',
            'phone' => '0987654321',
            'approval_status' => 'pending',
        ]);

        $result = $this->profileService->getAll();

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Approved Profile', $result->items()[0]->full_name);
    }

    public function test_get_all_filters_by_company(): void
    {
        $company2 = Company::create([
            'name' => 'Company 2',
            'tax_id' => '87654321',
            'industry_id' => $this->company->industry_id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $user2 = User::create([
            'username' => 'user2',
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'full_name' => 'Profile 1',
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);

        SalespersonProfile::create([
            'user_id' => $user2->id,
            'company_id' => $company2->id,
            'full_name' => 'Profile 2',
            'phone' => '0987654321',
            'approval_status' => 'approved',
        ]);

        $result = $this->profileService->getAll(['company_id' => $this->company->id]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Profile 1', $result->items()[0]->full_name);
    }

    public function test_get_all_filters_by_search(): void
    {
        $user2 = User::create([
            'username' => 'user2',
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'John Developer',
            'phone' => '0912345678',
            'specialties' => 'Laravel, PHP',
            'approval_status' => 'approved',
        ]);

        SalespersonProfile::create([
            'user_id' => $user2->id,
            'full_name' => 'Jane Designer',
            'phone' => '0987654321',
            'specialties' => 'UI/UX Design',
            'approval_status' => 'approved',
        ]);

        $result = $this->profileService->getAll(['search' => 'Laravel']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('John Developer', $result->items()[0]->full_name);
    }

    public function test_get_by_id_returns_profile(): void
    {
        $profile = SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Test Profile',
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);

        $result = $this->profileService->getById($profile->id);

        $this->assertNotNull($result);
        $this->assertEquals($profile->id, $result->id);
        $this->assertEquals('Test Profile', $result->full_name);
    }

    public function test_get_by_id_returns_null_for_nonexistent(): void
    {
        $result = $this->profileService->getById(99999);

        $this->assertNull($result);
    }

    public function test_get_by_user_id_returns_profile(): void
    {
        SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Test Profile',
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);

        $result = $this->profileService->getByUserId($this->user->id);

        $this->assertNotNull($result);
        $this->assertEquals($this->user->id, $result->user_id);
    }

    public function test_create_saves_profile_with_pending_status(): void
    {
        $data = [
            'full_name' => 'New Profile',
            'phone' => '0912345678',
            'bio' => 'Test bio',
            'specialties' => 'Testing',
            'service_regions' => [1, 2, 3],
        ];

        $profile = $this->profileService->create($this->user, $data);

        $this->assertNotNull($profile);
        $this->assertEquals('New Profile', $profile->full_name);
        $this->assertEquals('0912345678', $profile->phone);
        $this->assertEquals('pending', $profile->approval_status);
        $this->assertEquals($this->user->id, $profile->user_id);
        $this->assertEquals([1, 2, 3], $profile->service_regions);
    }

    public function test_create_with_avatar_stores_avatar_data(): void
    {
        $avatarData = base64_encode('fake image data');

        $data = [
            'full_name' => 'New Profile',
            'phone' => '0912345678',
            'avatar' => $avatarData,
            'avatar_mime' => 'image/jpeg',
        ];

        $profile = $this->profileService->create($this->user, $data);

        $this->assertNotNull($profile->avatar_data);
        $this->assertEquals('image/jpeg', $profile->avatar_mime);
        $this->assertGreaterThan(0, $profile->avatar_size);
    }

    public function test_update_resets_approval_status(): void
    {
        $profile = SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Original Profile',
            'phone' => '0912345678',
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
        ]);

        $this->assertEquals('approved', $profile->approval_status);

        $updatedProfile = $this->profileService->update($profile, ['full_name' => 'Updated Profile']);

        $this->assertEquals('Updated Profile', $updatedProfile->full_name);
        $this->assertEquals('pending', $updatedProfile->approval_status);
        $this->assertNull($updatedProfile->approved_by);
        $this->assertNull($updatedProfile->approved_at);
    }

    public function test_delete_removes_profile(): void
    {
        $profile = SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Test Profile',
            'phone' => '0912345678',
            'approval_status' => 'approved',
        ]);

        $result = $this->profileService->delete($profile);

        $this->assertTrue($result);
        $this->assertNull(SalespersonProfile::find($profile->id));
    }

    public function test_approve_sets_approval_status(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $profile = SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Test Profile',
            'phone' => '0912345678',
            'approval_status' => 'pending',
        ]);

        $approvedProfile = $this->profileService->approve($profile, $admin);

        $this->assertEquals('approved', $approvedProfile->approval_status);
        $this->assertEquals($admin->id, $approvedProfile->approved_by);
        $this->assertNotNull($approvedProfile->approved_at);
        $this->assertNull($approvedProfile->rejected_reason);
    }

    public function test_reject_sets_rejection_status_and_reason(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $profile = SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Test Profile',
            'phone' => '0912345678',
            'approval_status' => 'pending',
        ]);

        $rejectedProfile = $this->profileService->reject($profile, $admin, 'Incomplete information');

        $this->assertEquals('rejected', $rejectedProfile->approval_status);
        $this->assertEquals('Incomplete information', $rejectedProfile->rejected_reason);
        $this->assertNull($rejectedProfile->approved_by);
        $this->assertNull($rejectedProfile->approved_at);
    }

    public function test_get_pending_approvals_returns_pending_profiles(): void
    {
        $user2 = User::create([
            'username' => 'user2',
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $this->user->id,
            'full_name' => 'Pending 1',
            'phone' => '0912345678',
            'approval_status' => 'pending',
        ]);

        SalespersonProfile::create([
            'user_id' => $user2->id,
            'full_name' => 'Pending 2',
            'phone' => '0987654321',
            'approval_status' => 'pending',
        ]);

        $user3 = User::create([
            'username' => 'user3',
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        SalespersonProfile::create([
            'user_id' => $user3->id,
            'full_name' => 'Approved',
            'phone' => '0911111111',
            'approval_status' => 'approved',
        ]);

        $result = $this->profileService->getPendingApprovals();

        $this->assertCount(2, $result);
    }
}
