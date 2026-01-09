<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
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

    // Create another user
    $this->otherUser = User::create([
        'username' => 'otheruser',
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    // Create industry
    $this->industry = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    // Create company for user
    $this->company = Company::create([
        'name' => 'Original Company',
        'tax_id' => '12345678',
        'industry_id' => $this->industry->id,
        'address' => 'Original Address',
        'phone' => '0912345678',
        'approval_status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
        'created_by' => $this->user->id,
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can update their own company', function (): void {
    $updateData = [
        'name' => 'Updated Company',
        'address' => 'Updated Address',
        'phone' => '0987654321',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Company updated successfully',
            'data' => [
                'company' => [
                    'name' => 'Updated Company',
                    'address' => 'Updated Address',
                    'phone' => '0987654321',
                ],
            ],
        ]);

    $this->company->refresh();
    expect($this->company->name)->toBe('Updated Company');
    expect($this->company->address)->toBe('Updated Address');
});

test('update resets approval status to pending', function (): void {
    expect($this->company->approval_status)->toBe('approved');

    $updateData = [
        'name' => 'Updated Company',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->approval_status)->toBe('pending');
    expect($this->company->approved_by)->toBeNull();
    expect($this->company->approved_at)->toBeNull();
});

test('can update industry', function (): void {
    $newIndustry = Industry::create([
        'name' => 'Finance',
        'slug' => 'finance',
    ]);

    $updateData = [
        'industry_id' => $newIndustry->id,
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->industry_id)->toBe($newIndustry->id);
});

test('can update tax_id to different value', function (): void {
    $updateData = [
        'tax_id' => '87654321',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->tax_id)->toBe('87654321');
});

test('partial update works correctly', function (): void {
    $originalAddress = $this->company->address;
    $originalPhone = $this->company->phone;

    $updateData = [
        'name' => 'Only Name Updated',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->name)->toBe('Only Name Updated');
    expect($this->company->address)->toBe($originalAddress);
    expect($this->company->phone)->toBe($originalPhone);
});

test('cannot update to duplicate tax_id', function (): void {
    // Create another company with different tax_id
    Company::create([
        'name' => 'Other Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->user->id,
    ]);

    $updateData = [
        'tax_id' => '87654321',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                'tax_id',
            ],
        ]);
});

test('cannot update other users company', function (): void {
    $otherCompany = Company::create([
        'name' => 'Other Company',
        'tax_id' => '87654321',
        'industry_id' => $this->industry->id,
        'approval_status' => 'approved',
        'created_by' => $this->otherUser->id,
    ]);

    $updateData = [
        'name' => 'Hacked Company',
    ];

    $response = putJson("/api/companies/{$otherCompany->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden - You can only update your own companies',
        ]);
});

test('fails when company does not exist', function (): void {
    $updateData = [
        'name' => 'Updated Company',
    ];

    $response = putJson('/api/companies/99999', $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Company not found',
        ]);
});

test('unauthenticated user cannot update company', function (): void {
    $updateData = [
        'name' => 'Updated Company',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('rejected reason is cleared on update', function (): void {
    $this->company->update([
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid data',
    ]);

    $updateData = [
        'name' => 'Corrected Company',
    ];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->approval_status)->toBe('pending');
    expect($this->company->rejected_reason)->toBeNull();
});

test('empty update does not change approval status', function (): void {
    $updateData = [];

    $response = putJson("/api/companies/{$this->company->id}", $updateData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);

    $this->company->refresh();
    expect($this->company->approval_status)->toBe('approved');
});
