<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;
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

test('authenticated user can create profile', function (): void {
    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
        'bio' => 'Experienced salesperson',
        'specialties' => 'Technology sales',
        'service_regions' => [1, 2, 3],
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
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
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Profile created successfully',
            'data' => [
                'profile' => [
                    'user_id' => $this->user->id,
                    'full_name' => 'Test Salesperson',
                    'phone' => '0912345678',
                    'approval_status' => 'pending',
                ],
            ],
        ]);

    expect(SalespersonProfile::where('user_id', $this->user->id)->exists())->toBeTrue();
});

test('profile is created with pending status', function (): void {
    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201);

    $profile = SalespersonProfile::where('user_id', $this->user->id)->first();
    expect($profile->approval_status)->toBe('pending');
});

test('fails with missing required fields', function (): void {
    $response = postJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'full_name',
                'phone',
            ],
        ]);
});

test('fails when user already has profile', function (): void {
    // Create existing profile
    SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Existing Profile',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);

    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'New Profile',
        'phone' => '0987654321',
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(409)
        ->assertJson([
            'success' => false,
            'message' => 'Profile already exists',
        ]);
});

test('can create profile without company', function (): void {
    $profileData = [
        'full_name' => 'Freelance Salesperson',
        'phone' => '0912345678',
        'bio' => 'Independent consultant',
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'profile' => [
                    'company_id' => null,
                ],
            ],
        ]);
});

test('can create profile with avatar', function (): void {
    $avatarData = base64_encode('fake image data');

    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
        'avatar' => $avatarData,
        'avatar_mime' => 'image/jpeg',
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201);

    $profile = SalespersonProfile::where('user_id', $this->user->id)->first();
    expect($profile->avatar_data)->not->toBeNull();
    expect($profile->avatar_mime)->toBe('image/jpeg');
    expect($profile->avatar_size)->toBeGreaterThan(0);
});

test('service regions are stored as json', function (): void {
    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
        'service_regions' => [1, 2, 3, 4],
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201);

    $profile = SalespersonProfile::where('user_id', $this->user->id)->first();
    expect($profile->service_regions)->toBeArray();
    expect($profile->service_regions)->toBe([1, 2, 3, 4]);
});

test('unauthenticated user cannot create profile', function (): void {
    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
    ];

    $response = postJson('/api/profile', $profileData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('fails with invalid phone format', function (): void {
    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => 'Test Salesperson',
        'phone' => 'invalid phone',
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'phone',
            ],
        ]);
});

test('full name cannot exceed 200 characters', function (): void {
    $profileData = [
        'company_id' => $this->company->id,
        'full_name' => str_repeat('a', 201),
        'phone' => '0912345678',
    ];

    $response = postJson('/api/profile', $profileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'full_name',
            ],
        ]);
});
