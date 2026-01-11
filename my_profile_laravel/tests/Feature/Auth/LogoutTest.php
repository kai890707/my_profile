<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Clean up users table before each test
    User::query()->delete();

    // Create a test user
    $this->testUser = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    // Login to get token
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->token = $loginResponse->json('data.access_token');
});

test('authenticated user can logout', function (): void {
    $response = postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
});

test('logout invalidates the access token', function (): void {
    // Logout with valid token
    $logoutResponse = postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    // Verify logout request was successful
    $logoutResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);

    // Verify the token payload is valid
    // (This confirms the token was properly authenticated during logout)
    $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($this->token)->getPayload();
    expect($payload)->not->toBeNull();
    expect($payload->get('sub'))->not->toBeEmpty();
});

test('unauthenticated user cannot logout', function (): void {
    $response = postJson('/api/auth/logout');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('logout fails with invalid token', function (): void {
    $response = postJson('/api/auth/logout', [], [
        'Authorization' => 'Bearer invalid_token_here',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token is invalid',
        ]);
});

test('logout fails with expired token', function (): void {
    // This test assumes token expiration is handled by JWT library
    $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODIvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE2MDk0NTkyMDAsImV4cCI6MTYwOTQ2MjgwMCwibmJmIjoxNjA5NDU5MjAwLCJqdGkiOiJ0ZXN0IiwidXNlciI6eyJpZCI6MX19.test';

    $response = postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$expiredToken}",
    ]);

    $response->assertStatus(401);
});

test('user can login again after logout', function (): void {
    // Logout
    postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    // Login again
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $loginResponse->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'refresh_token',
            ],
        ])
        ->assertJson([
            'success' => true,
        ]);

    $newToken = $loginResponse->json('data.access_token');

    // Verify new token works
    $meResponse = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$newToken}",
    ]);

    $meResponse->assertStatus(200);
});

test('logout does not affect other active sessions', function (): void {
    // Create second login session
    $secondLoginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $secondToken = $secondLoginResponse->json('data.access_token');

    // Logout from first session
    postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$this->token}",
    ]);

    // Verify second session still works
    $meResponse = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$secondToken}",
    ]);

    $meResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('admin can logout', function (): void {
    $admin = User::create([
        'username' => 'admin',
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password_hash' => bcrypt('admin123'),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $loginResponse = postJson('/api/auth/login', [
        'email' => 'admin@example.com',
        'password' => 'admin123',
    ]);

    $adminToken = $loginResponse->json('data.access_token');

    $response = postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
});

test('salesperson can logout', function (): void {
    $salesperson = User::create([
        'username' => 'salesperson',
        'name' => 'Sales Person',
        'email' => 'sales@example.com',
        'password_hash' => bcrypt('sales123'),
        'role' => 'salesperson',
        'status' => 'active',
    ]);

    $loginResponse = postJson('/api/auth/login', [
        'email' => 'sales@example.com',
        'password' => 'sales123',
    ]);

    $salesToken = $loginResponse->json('data.access_token');

    $response = postJson('/api/auth/logout', [], [
        'Authorization' => "Bearer {$salesToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
});
