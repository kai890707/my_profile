<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

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

    // Create profile
    $this->profile = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'company_id' => $this->company->id,
        'full_name' => 'Original Name',
        'phone' => '0912345678',
        'bio' => 'Original bio',
        'approval_status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can update their profile', function (): void {
    $updateData = [
        'full_name' => 'Updated Name',
        'phone' => '0987654321',
        'bio' => 'Updated bio',
        'specialties' => 'New specialties',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => [
                    'full_name' => 'Updated Name',
                    'phone' => '0987654321',
                    'bio' => 'Updated bio',
                ],
            ],
        ]);

    $this->profile->refresh();
    expect($this->profile->full_name)->toBe('Updated Name');
    expect($this->profile->phone)->toBe('0987654321');
});

test('update resets approval status to pending', function (): void {
    expect($this->profile->approval_status)->toBe('approved');

    $updateData = [
        'full_name' => 'Updated Name',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->approval_status)->toBe('pending');
    expect($this->profile->approved_by)->toBeNull();
    expect($this->profile->approved_at)->toBeNull();
});

test('can update company', function (): void {
    $newCompany = Company::create([
        'name' => 'New Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $updateData = [
        'company_id' => $newCompany->id,
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->company_id)->toBe($newCompany->id);
});

test('can update service regions', function (): void {
    $updateData = [
        'service_regions' => [5, 6, 7],
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->service_regions)->toBe([5, 6, 7]);
});

test('can update avatar', function (): void {
    $newAvatarData = base64_encode('new fake image data');

    $updateData = [
        'avatar' => $newAvatarData,
        'avatar_mime' => 'image/png',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->avatar_data)->not->toBeNull();
    expect($this->profile->avatar_mime)->toBe('image/png');
});

test('partial update works correctly', function (): void {
    $originalPhone = $this->profile->phone;
    $originalBio = $this->profile->bio;

    $updateData = [
        'full_name' => 'Only Name Updated',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->full_name)->toBe('Only Name Updated');
    expect($this->profile->phone)->toBe($originalPhone);
    expect($this->profile->bio)->toBe($originalBio);
});

test('fails when user has no profile', function (): void {
    $this->profile->delete();

    $updateData = [
        'full_name' => 'Updated Name',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Profile not found',
        ]);
});

test('unauthenticated user cannot update profile', function (): void {
    $updateData = [
        'full_name' => 'Updated Name',
    ];

    $response = putJson('/api/profile', $updateData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('fails with invalid phone format', function (): void {
    $updateData = [
        'phone' => 'invalid phone',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'phone',
            ],
        ]);
});

test('rejected reason is cleared on update', function (): void {
    $this->profile->update([
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid data',
    ]);

    $updateData = [
        'full_name' => 'Corrected Name',
    ];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->approval_status)->toBe('pending');
    expect($this->profile->rejected_reason)->toBeNull();
});

test('empty update does not change approval status', function (): void {
    $updateData = [];

    $response = putJson('/api/profile', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->approval_status)->toBe('approved');
});
