<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\User;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Create admin user
    $this->admin = User::create([
        'username' => 'admin',
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    // Create regular user
    $this->user = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Create industry
    $this->industry = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    // Create pending company
    $this->company = Company::create([
        'name' => 'Test Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    // Login as admin to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $this->adminToken = $loginResponse->json('data.access_token');

    // Login as user to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->userToken = $loginResponse->json('data.access_token');
});

test('admin can approve company', function (): void {
    $response = postJson("/api/admin/companies/{$this->company->id}/approve", [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Company approved successfully',
            'data' => [
                'company' => [
                    'id' => $this->company->id,
                    'approval_status' => 'approved',
                ],
            ],
        ]);

    $this->company->refresh();
    expect($this->company->approval_status)->toBe('approved');
    expect($this->company->approved_by)->toBe($this->admin->id);
    expect($this->company->approved_at)->not->toBeNull();
});

test('admin can reject company with reason', function (): void {
    $response = postJson("/api/admin/companies/{$this->company->id}/reject", [
        'reason' => 'Invalid tax ID',
    ], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Company rejected successfully',
            'data' => [
                'company' => [
                    'id' => $this->company->id,
                    'approval_status' => 'rejected',
                    'rejected_reason' => 'Invalid tax ID',
                ],
            ],
        ]);

    $this->company->refresh();
    expect($this->company->approval_status)->toBe('rejected');
    expect($this->company->rejected_reason)->toBe('Invalid tax ID');
});

test('reject requires reason', function (): void {
    $response = postJson("/api/admin/companies/{$this->company->id}/reject", [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'reason',
            ],
        ]);
});

test('cannot approve non-existent company', function (): void {
    $response = postJson('/api/admin/companies/99999/approve', [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Company not found',
        ]);
});

test('cannot reject non-existent company', function (): void {
    $response = postJson('/api/admin/companies/99999/reject', [
        'reason' => 'Invalid',
    ], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Company not found',
        ]);
});

test('non-admin cannot approve company', function (): void {
    $response = postJson("/api/admin/companies/{$this->company->id}/approve", [], [
        'Authorization' => "Bearer {$this->userToken}",
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden - Insufficient permissions',
        ]);
});

test('non-admin cannot reject company', function (): void {
    $response = postJson("/api/admin/companies/{$this->company->id}/reject", [
        'reason' => 'Invalid',
    ], [
        'Authorization' => "Bearer {$this->userToken}",
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden - Insufficient permissions',
        ]);
});

test('unauthenticated user cannot approve company', function (): void {
    $response = postJson("/api/admin/companies/{$this->company->id}/approve");

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('can approve already approved company', function (): void {
    // First approval
    $this->company->update([
        'approval_status' => 'approved',
        'approved_by' => $this->admin->id,
        'approved_at' => now(),
    ]);

    // Second approval
    $response = postJson("/api/admin/companies/{$this->company->id}/approve", [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200);
    expect($response->json('data.company.approval_status'))->toBe('approved');
});

test('can reject rejected company again', function (): void {
    // First rejection
    $this->company->update([
        'approval_status' => 'rejected',
        'rejected_reason' => 'Old reason',
    ]);

    // Second rejection with new reason
    $response = postJson("/api/admin/companies/{$this->company->id}/reject", [
        'reason' => 'New reason',
    ], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->rejected_reason)->toBe('New reason');
});
