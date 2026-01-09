<?php

declare(strict_types=1);

use App\Models\SalespersonProfile;
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

    // Create pending profile
    $this->profile = SalespersonProfile::create([
        'user_id' => $this->user->id,
        'full_name' => 'Test Profile',
        'phone' => '0912345678',
        'approval_status' => 'pending',
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

test('admin can approve profile', function (): void {
    $response = postJson("/api/admin/profiles/{$this->profile->id}/approve", [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile approved successfully',
            'data' => [
                'profile' => [
                    'id' => $this->profile->id,
                    'approval_status' => 'approved',
                ],
            ],
        ]);

    $this->profile->refresh();
    expect($this->profile->approval_status)->toBe('approved');
    expect($this->profile->approved_by)->toBe($this->admin->id);
    expect($this->profile->approved_at)->not->toBeNull();
});

test('admin can reject profile with reason', function (): void {
    $response = postJson("/api/admin/profiles/{$this->profile->id}/reject", [
        'reason' => 'Incomplete information',
    ], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Profile rejected successfully',
            'data' => [
                'profile' => [
                    'id' => $this->profile->id,
                    'approval_status' => 'rejected',
                    'rejected_reason' => 'Incomplete information',
                ],
            ],
        ]);

    $this->profile->refresh();
    expect($this->profile->approval_status)->toBe('rejected');
    expect($this->profile->rejected_reason)->toBe('Incomplete information');
});

test('reject requires reason', function (): void {
    $response = postJson("/api/admin/profiles/{$this->profile->id}/reject", [], [
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

test('cannot approve non-existent profile', function (): void {
    $response = postJson('/api/admin/profiles/99999/approve', [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Profile not found',
        ]);
});

test('cannot reject non-existent profile', function (): void {
    $response = postJson('/api/admin/profiles/99999/reject', [
        'reason' => 'Invalid',
    ], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Profile not found',
        ]);
});

test('non-admin cannot approve profile', function (): void {
    $response = postJson("/api/admin/profiles/{$this->profile->id}/approve", [], [
        'Authorization' => "Bearer {$this->userToken}",
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Forbidden - Insufficient permissions',
        ]);
});

test('non-admin cannot reject profile', function (): void {
    $response = postJson("/api/admin/profiles/{$this->profile->id}/reject", [
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

test('unauthenticated user cannot approve profile', function (): void {
    $response = postJson("/api/admin/profiles/{$this->profile->id}/approve");

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('can approve already approved profile', function (): void {
    // First approval
    $this->profile->update([
        'approval_status' => 'approved',
        'approved_by' => $this->admin->id,
        'approved_at' => now(),
    ]);

    // Second approval
    $response = postJson("/api/admin/profiles/{$this->profile->id}/approve", [], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200);
    expect($response->json('data.profile.approval_status'))->toBe('approved');
});

test('can reject rejected profile again', function (): void {
    // First rejection
    $this->profile->update([
        'approval_status' => 'rejected',
        'rejected_reason' => 'Old reason',
    ]);

    // Second rejection with new reason
    $response = postJson("/api/admin/profiles/{$this->profile->id}/reject", [
        'reason' => 'New reason',
    ], [
        'Authorization' => "Bearer {$this->adminToken}",
    ]);

    $response->assertStatus(200);

    $this->profile->refresh();
    expect($this->profile->rejected_reason)->toBe('New reason');
});
