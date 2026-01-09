<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Industry;
use App\Models\SalespersonProfile;
use App\Models\User;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Create industry and company
    $this->industry = Industry::create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    // Create user first (needed for company created_by)
    $this->user = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
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
        'full_name' => 'Test User',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can delete their profile', function (): void {
    $response = deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile deleted successfully',
        ]);

    expect(SalespersonProfile::find($this->profile->id))->toBeNull();
});

test('profile is permanently deleted from database', function (): void {
    $profileId = $this->profile->id;

    deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $deletedProfile = SalespersonProfile::find($profileId);
    expect($deletedProfile)->toBeNull();

    $profileCount = SalespersonProfile::where('user_id', $this->user->id)->count();
    expect($profileCount)->toBe(0);
});

test('fails when user has no profile', function (): void {
    $this->profile->delete();

    $response = deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Profile not found',
        ]);
});

test('unauthenticated user cannot delete profile', function (): void {
    $response = deleteJson('/api/profile');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('can delete approved profile', function (): void {
    $this->profile->update([
        'approval_status' => 'approved',
        'approved_by' => 1,
        'approved_at' => now(),
    ]);

    $response = deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);
    expect(SalespersonProfile::find($this->profile->id))->toBeNull();
});

test('can delete pending profile', function (): void {
    $this->profile->update([
        'approval_status' => 'pending',
    ]);

    $response = deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);
    expect(SalespersonProfile::find($this->profile->id))->toBeNull();
});

test('can delete rejected profile', function (): void {
    $this->profile->update([
        'approval_status' => 'rejected',
        'rejected_reason' => 'Invalid data',
    ]);

    $response = deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200);
    expect(SalespersonProfile::find($this->profile->id))->toBeNull();
});

test('user can create new profile after deletion', function (): void {
    // Delete existing profile
    deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    expect(SalespersonProfile::where('user_id', $this->user->id)->exists())->toBeFalse();

    // Create new profile
    $newProfileData = [
        'company_id' => $this->company->id,
        'full_name' => 'New Profile',
        'phone' => '0987654321',
    ];

    $response = postJson('/api/profile', $newProfileData, [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(201);
    expect(SalespersonProfile::where('user_id', $this->user->id)->exists())->toBeTrue();
});

test('cannot delete another users profile', function (): void {
    // Create another user with profile
    $otherUser = User::create([
        'username' => 'otheruser',
        'name' => 'Other User',
        'email' => 'other@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    $otherProfile = SalespersonProfile::create([
        'user_id' => $otherUser->id,
        'company_id' => $this->company->id,
        'full_name' => 'Other User',
        'phone' => '0999999999',
        'approval_status' => 'approved',
    ]);

    // Try to delete with current user's token
    $response = deleteJson('/api/profile', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    // Should delete own profile, not other user's profile
    $response->assertStatus(200);
    expect(SalespersonProfile::find($this->profile->id))->toBeNull();
    expect(SalespersonProfile::find($otherProfile->id))->not->toBeNull();
});
