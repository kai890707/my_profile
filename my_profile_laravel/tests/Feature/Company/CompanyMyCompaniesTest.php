<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Create user first (needed for company created_by)
    $this->user = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Create another user
    $this->otherUser = User::create([
        'username' => 'otheruser',
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can get their own companies', function (): void {
    // Create companies for this user
    Company::create([
        'name' => 'My Company 1',
        'tax_id' => '12345678',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'My Company 2',
        'tax_id' => '87654321',
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    // Create company for other user
    Company::create([
        'name' => 'Other Company',
        'tax_id' => '11111111',
        'approval_status' => 'approved',
        'created_by' => $this->otherUser->id,
    ]);

    $response = getJson('/api/companies/my', [
        'Authorization' => "Bearer {$this->token}",
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
                        'created_by',
                    ],
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);

    $companies = $response->json('data.companies');
    expect($companies)->toHaveCount(2);
    expect($companies[0]['created_by'])->toBe($this->user->id);
    expect($companies[1]['created_by'])->toBe($this->user->id);
});

test('my companies includes all approval statuses', function (): void {
    Company::create([
        'name' => 'Approved Company',
        'tax_id' => '12345678',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'Pending Company',
        'tax_id' => '87654321',
        'approval_status' => 'pending',
        'created_by' => $this->user->id,
    ]);

    Company::create([
        'name' => 'Rejected Company',
        'tax_id' => '11111111',
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid data',
        'created_by' => $this->user->id,
    ]);

    $response = getJson('/api/companies/my', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $companies = $response->json('data.companies');
    expect($companies)->toHaveCount(3);
});

test('returns empty array when user has no companies', function (): void {
    $response = getJson('/api/companies/my', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect($response->json('data.companies'))->toBeArray();
    expect($response->json('data.companies'))->toHaveCount(0);
});

test('unauthenticated user cannot access my companies', function (): void {
    $response = getJson('/api/companies/my');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('companies are ordered by created_at desc', function (): void {
    $company1 = Company::create([
        'name' => 'First Company',
        'tax_id' => '12345678',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    sleep(1);

    $company2 = Company::create([
        'name' => 'Second Company',
        'tax_id' => '87654321',
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $response = getJson('/api/companies/my', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $companies = $response->json('data.companies');
    expect($companies[0]['name'])->toBe('Second Company');
    expect($companies[1]['name'])->toBe('First Company');
});
