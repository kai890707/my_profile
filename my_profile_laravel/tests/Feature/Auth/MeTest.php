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

test('authenticated user can get their profile', function (): void {
    $response = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'user' => [
                    'id',
                    'username',
                    'name',
                    'email',
                    'role',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'username' => 'testuser',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'user',
                    'status' => 'active',
                ],
            ],
        ]);
});

test('unauthenticated user cannot access profile', function (): void {
    $response = getJson('/api/auth/me');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token not provided',
        ]);
});

test('user with invalid token cannot access profile', function (): void {
    $response = getJson('/api/auth/me', [
        'Authorization' => 'Bearer invalid_token_here',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Token is invalid',
        ]);
});

test('profile endpoint does not expose sensitive data', function (): void {
    $response = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $userData = $response->json('data.user');

    expect($userData)->not->toHaveKey('password_hash');
    expect($userData)->not->toHaveKey('remember_token');
    expect($userData)->not->toHaveKey('deleted_at');
});

test('admin user can access their profile', function (): void {
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

    $response = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$adminToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'role' => 'admin',
                ],
            ],
        ]);
});

test('salesperson user can access their profile', function (): void {
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

    $response = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$salesToken}",
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'role' => 'salesperson',
                ],
            ],
        ]);
});

test('profile includes timestamps', function (): void {
    $response = getJson('/api/auth/me', [
        'Authorization' => "Bearer {$this->token}",
    ]);

    $userData = $response->json('data.user');

    expect($userData)->toHaveKey('created_at');
    expect($userData)->toHaveKey('updated_at');
    expect($userData['created_at'])->not->toBeNull();
    expect($userData['updated_at'])->not->toBeNull();
});
