<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Industry;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyServiceTest extends TestCase
{
    use RefreshDatabase;

    private CompanyService $companyService;
    private User $user;
    private Industry $industry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyService = new CompanyService();

        $this->user = User::create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        $this->industry = Industry::create([
            'name' => 'Technology',
            'slug' => 'technology',
        ]);
    }

    public function test_get_all_returns_only_approved_companies(): void
    {
        Company::create([
            'name' => 'Approved Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        Company::create([
            'name' => 'Pending Company',
            'tax_id' => '87654321',
            'industry_id' => $this->industry->id,
            'approval_status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $result = $this->companyService->getAll();

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Approved Company', $result->items()[0]->name);
    }

    public function test_get_all_filters_by_industry(): void
    {
        $industry2 = Industry::create([
            'name' => 'Finance',
            'slug' => 'finance',
        ]);

        Company::create([
            'name' => 'Tech Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        Company::create([
            'name' => 'Finance Company',
            'tax_id' => '87654321',
            'industry_id' => $industry2->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $result = $this->companyService->getAll(['industry_id' => $this->industry->id]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Tech Company', $result->items()[0]->name);
    }

    public function test_get_all_filters_by_search(): void
    {
        Company::create([
            'name' => 'ABC Technology Inc',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        Company::create([
            'name' => 'XYZ Finance Corp',
            'tax_id' => '87654321',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $result = $this->companyService->getAll(['search' => 'Technology']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('ABC Technology Inc', $result->items()[0]->name);
    }

    public function test_get_by_id_returns_company(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $result = $this->companyService->getById($company->id);

        $this->assertNotNull($result);
        $this->assertEquals($company->id, $result->id);
        $this->assertEquals('Test Company', $result->name);
    }

    public function test_get_by_id_returns_null_for_nonexistent(): void
    {
        $result = $this->companyService->getById(99999);

        $this->assertNull($result);
    }

    public function test_get_by_creator_returns_user_companies(): void
    {
        $otherUser = User::create([
            'username' => 'otheruser',
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'salesperson',
            'status' => 'active',
        ]);

        Company::create([
            'name' => 'My Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        Company::create([
            'name' => 'Other Company',
            'tax_id' => '87654321',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $otherUser->id,
        ]);

        $result = $this->companyService->getByCreator($this->user);

        $this->assertCount(1, $result);
        $this->assertEquals('My Company', $result[0]->name);
    }

    public function test_create_saves_company_with_pending_status(): void
    {
        $data = [
            'name' => 'New Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'address' => '123 Test St',
            'phone' => '0912345678',
        ];

        $company = $this->companyService->create($this->user, $data);

        $this->assertNotNull($company);
        $this->assertEquals('New Company', $company->name);
        $this->assertEquals('12345678', $company->tax_id);
        $this->assertEquals('pending', $company->approval_status);
        $this->assertEquals($this->user->id, $company->created_by);
    }

    public function test_update_resets_approval_status(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('approved', $company->approval_status);

        $updatedCompany = $this->companyService->update($company, ['name' => 'Updated Company']);

        $this->assertEquals('Updated Company', $updatedCompany->name);
        $this->assertEquals('pending', $updatedCompany->approval_status);
        $this->assertNull($updatedCompany->approved_by);
        $this->assertNull($updatedCompany->approved_at);
    }

    public function test_delete_removes_company(): void
    {
        $company = Company::create([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $result = $this->companyService->delete($company);

        $this->assertTrue($result);
        $this->assertNull(Company::find($company->id));
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

        $company = Company::create([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $approvedCompany = $this->companyService->approve($company, $admin);

        $this->assertEquals('approved', $approvedCompany->approval_status);
        $this->assertEquals($admin->id, $approvedCompany->approved_by);
        $this->assertNotNull($approvedCompany->approved_at);
        $this->assertNull($approvedCompany->rejected_reason);
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

        $company = Company::create([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $rejectedCompany = $this->companyService->reject($company, $admin, 'Invalid tax ID');

        $this->assertEquals('rejected', $rejectedCompany->approval_status);
        $this->assertEquals('Invalid tax ID', $rejectedCompany->rejected_reason);
        $this->assertNull($rejectedCompany->approved_by);
        $this->assertNull($rejectedCompany->approved_at);
    }

    public function test_get_pending_approvals_returns_pending_companies(): void
    {
        Company::create([
            'name' => 'Pending 1',
            'tax_id' => '12345678',
            'industry_id' => $this->industry->id,
            'approval_status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        Company::create([
            'name' => 'Pending 2',
            'tax_id' => '87654321',
            'industry_id' => $this->industry->id,
            'approval_status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        Company::create([
            'name' => 'Approved',
            'tax_id' => '11111111',
            'industry_id' => $this->industry->id,
            'approval_status' => 'approved',
            'created_by' => $this->user->id,
        ]);

        $result = $this->companyService->getPendingApprovals();

        $this->assertCount(2, $result);
    }
}
