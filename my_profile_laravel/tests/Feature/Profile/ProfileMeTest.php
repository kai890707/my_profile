<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
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

    // Create industry and company
    $this->industry = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $this->company = Company::create([
        'name' => 'Test Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can get their profile', function (): void {
    $profile = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Test User',
        'phone' => '0912345678',
        'bio' => 'Test bio',
        'approval_status' => 'approved',
    ]);

    $response = getJson('/api/profile', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'profile' => [
                    'id',
                    'user_id',
                    'company_id',
                    'full_name',
                    'phone',
                    'bio',
                    'approval_status',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'user_id' => $this->user->id,
                    'full_name' => 'Test User',
                ],
            ],
        ]);
});

test('returns 404 when user has no profile', function (): void {
    $response = getJson('/api/profile', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Profile not found',
        ]);
});

test('unauthenticated user cannot access profile', function (): void {
    $response = getJson('/api/profile');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('profile includes relationships', function (): void {
    SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Test User',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);

    $response = getJson('/api/profile', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $profile = $response->json('data.profile');
    expect($profile['user'])->not->toBeNull();
    expect($profile['company'])->not->toBeNull();
});

test('can get pending profile', function (): void {
    SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Test User',
        'phone' => '0912345678',
        'approval_status' => 'pending',
    ]);

    $response = getJson('/api/profile', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'profile' => [
                    'approval_status' => 'pending',
                ],
            ],
        ]);
});

test('can get rejected profile', function (): void {
    SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Test User',
        'phone' => '0912345678',
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid information',
    ]);

    $response = getJson('/api/profile', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'profile' => [
                    'approval_status' => 'rejected',
                    'rejected_reason' => 'Invalid information',
                ],
            ],
        ]);
});
