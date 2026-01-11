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

    // Login to get tokens
    $loginResponse = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $this->accessToken = $loginResponse->json('data.access_token');
    $this->refreshToken = $loginResponse->json('data.refresh_token');
});

test('user can refresh token with valid refresh token', function (): void {
    $response = postJson('/api/auth/refresh', [
        'refresh_token' => $this->refreshToken,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ],
        ]);
});

test('refresh returns new access token', function (): void {
    $response = postJson('/api/auth/refresh', [
        'refresh_token' => $this->refreshToken,
    ]);

    $newAccessToken = $response->json('data.access_token');

    expect($newAccessToken)->not->toBeNull();
    expect($newAccessToken)->toBeString();
    expect($newAccessToken)->not->toBe($this->accessToken);
});

test('refresh returns new refresh token', function (): void {
    $response = postJson('/api/auth/refresh', [
        'refresh_token' => $this->refreshToken,
    ]);

    $newRefreshToken = $response->json('data.refresh_token');

    expect($newRefreshToken)->not->toBeNull();
    expect($newRefreshToken)->toBeString();
    expect($newRefreshToken)->not->toBe($this->refreshToken);
});

test('refresh fails with missing refresh token', function (): void {
    $response = postJson('/api/auth/refresh', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'refresh_token',
            ],
        ])
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ]);
});

test('refresh fails with invalid refresh token', function (): void {
    $response = postJson('/api/auth/refresh', [
        'refresh_token' => 'invalid_refresh_token_here',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid refresh token',
        ]);
});

test('refresh fails with empty refresh token', function (): void {
    $response = postJson('/api/auth/refresh', [
        'refresh_token' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'refresh_token',
            ],
        ]);
});

test('new access token can be used for authentication', function (): void {
    $refreshResponse = postJson('/api/auth/refresh', [
        'refresh_token' => $this->refreshToken,
    ]);

    $newAccessToken = $refreshResponse->json('data.access_token');

    $meResponse = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$newAccessToken}",
    ]);

    $meResponse->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'email' => 'test@example.com',
                ],
            ],
        ]);
});

test('refresh token can be used multiple times before expiration', function (): void {
    // First refresh
    $firstRefresh = postJson('/api/auth/refresh', [
        'refresh_token' => $this->refreshToken,
    ]);

    $firstRefresh->assertStatus(200);
    $newRefreshToken = $firstRefresh->json('data.refresh_token');

    // Second refresh with new refresh token
    $secondRefresh = postJson('/api/auth/refresh', [
        'refresh_token' => $newRefreshToken,
    ]);

    $secondRefresh->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'refresh_token',
            ],
        ]);
});

test('refresh maintains user role in new token', function (): void {
    // Create admin user
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

    $adminRefreshToken = $loginResponse->json('data.refresh_token');

    $refreshResponse = postJson('/api/auth/refresh', [
        'refresh_token' => $adminRefreshToken,
    ]);

    $newAccessToken = $refreshResponse->json('data.access_token');

    // Verify new token maintains admin role
    $meResponse = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$newAccessToken}",
    ]);

    $meResponse->assertJson([
        'data' => [
            'user' => [
                'role' => 'admin',
            ],
        ],
    ]);
});
