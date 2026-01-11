<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;

use function Pest\Laravel\getJson;

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

    // Create approved profile
    $this->profile = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Test User',
        'phone' => '0912345678',
        'bio' => 'Test bio',
        'specialties' => 'Testing, Development',
        'service_regions' => [1, 2, 3],
        'approval_status' => 'approved',
    ]);
});

test('can get single profile by id', function (): void {
    $response = getJson("/api/profiles/{$this->profile->id}");

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
                    'specialties',
                    'service_regions',
                    'approval_status',
                    'created_at',
                    'updated_at',
                    'user',
                    'company',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $this->profile->id,
                    'full_name' => 'Test User',
                    'phone' => '0912345678',
                ],
            ],
        ]);
});

test('profile includes user relationship', function (): void {
    $response = getJson("/api/profiles/{$this->profile->id}");

    $response->assertStatus(200);

    $profile = $response->json('data.profile');
    expect($profile['user'])->not->toBeNull();
    expect($profile['user']['email'])->toBe('test@example.com');
});

test('profile includes company relationship', function (): void {
    $response = getJson("/api/profiles/{$this->profile->id}");

    $response->assertStatus(200);

    $profile = $response->json('data.profile');
    expect($profile['company'])->not->toBeNull();
    expect($profile['company']['name'])->toBe('Test Company');
});

test('returns 404 for non-existent profile', function (): void {
    $response = getJson('/api/profiles/99999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Profile not found',
        ]);
});

test('can access pending profile by id', function (): void {
    $pendingProfile = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Pending User',
        'phone' => '0987654321',
        'approval_status' => 'pending',
    ]);

    $response = getJson("/api/profiles/{$pendingProfile->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $pendingProfile->id,
                    'approval_status' => 'pending',
                ],
            ],
        ]);
});

test('can access rejected profile by id', function (): void {
    $rejectedProfile = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Rejected User',
        'phone' => '0987654321',
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid information',
    ]);

    $response = getJson("/api/profiles/{$rejectedProfile->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $rejectedProfile->id,
                    'approval_status' => 'rejected',
                ],
            ],
        ]);
});

test('profile without company returns null company', function (): void {
    $profileWithoutCompany = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => null,
        'full_name' => 'Freelancer',
        'phone' => '0987654321',
        'approval_status' => 'approved',
    ]);

    $response = getJson("/api/profiles/{$profileWithoutCompany->id}");

    $response->assertStatus(200);

    $profile = $response->json('data.profile');
    expect($profile['company_id'])->toBeNull();
    expect($profile['company'])->toBeNull();
});

test('service regions are decoded correctly', function (): void {
    $response = getJson("/api/profiles/{$this->profile->id}");

    $response->assertStatus(200);

    $profile = $response->json('data.profile');
    expect($profile['service_regions'])->toBeArray();
    expect($profile['service_regions'])->toBe([1, 2, 3]);
});
