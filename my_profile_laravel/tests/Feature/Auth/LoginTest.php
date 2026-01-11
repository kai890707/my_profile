<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Clean up users table before each test
    User::query()->delete();

    // Create a test user for login tests
    $this->testUser = User::create([
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);
});

test('user can login with valid credentials', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'username',
                    'name',
                    'email',
                    'role',
                    'status',
                ],
                'access_token',
                'refresh_token',
                'token_type',
                'expires_in',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'username' => 'testuser',
                    'email' => 'test@example.com',
                ],
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ],
        ]);
});

test('login fails with invalid email', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'wrong@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('login fails with invalid password', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('login fails with missing email', function (): void {
    $response = postJson('/api/auth/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'email',
            ],
        ])
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ]);
});

test('login fails with missing password', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'password',
            ],
        ]);
});

test('login fails with invalid email format', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'invalid-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'email',
            ],
        ]);
});

test('login fails for pending user', function (): void {
    $pendingUser = User::create([
        'username' => 'pending',
        'name' => 'Pending User',
        'email' => 'pending@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'pending',
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'pending@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('login fails for inactive user', function (): void {
    $inactiveUser = User::create([
        'username' => 'inactive',
        'name' => 'Inactive User',
        'email' => 'inactive@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'inactive',
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'inactive@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('login returns valid jwt token', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $token = $response->json('data.access_token');

    expect($token)->not->toBeNull();
    expect($token)->toBeString();
    expect(strlen($token))->toBeGreaterThan(0);
});

test('login returns refresh token', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $refreshToken = $response->json('data.refresh_token');

    expect($refreshToken)->not->toBeNull();
    expect($refreshToken)->toBeString();
    expect(strlen($refreshToken))->toBeGreaterThan(0);
});
