<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;
use function Pest\Laravel\getJson;
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

test('admin can get pending approvals', function (): void {
    // Create pending companies
    Company::create([
        'name' => 'Pending Company 1',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'Pending Company 2',
        'tax_id' => '87654321',
        'industry_id' => $this->industry->id,
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    // Create approved company (should not appear)
    Company::create([
        'name' => 'Approved Company',
        'tax_id' => '11111111',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    // Create pending profiles
    SalespersonProfile::create([
        'user_id' => $this->user->id,
        'full_name' => 'Pending Profile',
        'phone' => '0912345678',
        'approval_status' => 'pending',
    ]);

    $response = getJson('/api/admin/pending-approvals', [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'companies' => [
                    '*' => [
                        'id',
                        'name',
                        'tax_id',
                        'approval_status',
                    ],
                ],
                'profiles' => [
                    '*' => [
                        'id',
                        'full_name',
                        'approval_status',
                    ],
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);

    $companies = $response->json('data.companies');
    expect($companies)->toHaveCount(2);

    $profiles = $response->json('data.profiles');
    expect($profiles)->toHaveCount(1);
});

test('pending approvals are ordered by created_at asc', function (): void {
    $company1 = Company::create([
        'name' => 'First Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    sleep(1);

    $company2 = Company::create([
        'name' => 'Second Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry->id,
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    $response = getJson('/api/admin/pending-approvals', [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200);

    $companies = $response->json('data.companies');
    expect($companies[0]['name'])->toBe('First Company');
    expect($companies[1]['name'])->toBe('Second Company');
});

test('returns empty arrays when no pending approvals', function (): void {
    $response = getJson('/api/admin/pending-approvals', [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'companies' => [],
                'profiles' => [],
            ],
        ]);
});

test('non-admin cannot access pending approvals', function (): void {
    $response = getJson('/api/admin/pending-approvals', [
        'Authorization' => "Bearer {$this->userToken}",
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden - Insufficient permissions',
        ]);
});

test('unauthenticated user cannot access pending approvals', function (): void {
    $response = getJson('/api/admin/pending-approvals');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});
