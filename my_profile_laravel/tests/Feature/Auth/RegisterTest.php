<?php

declare(strict_types=1);

use App\Models\User;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Clean up users table before each test
    User::query()->delete();
});

test('user can register with valid data', function (): void {
    $userData = [
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $response = postJson('/api/auth/register', $userData);

    $response->assertStatus(201)
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
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'username' => 'testuser',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'user',
                    'status' => 'pending',
                ],
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ],
        ]);

    expect(User::where('email', 'test@example.com')->exists())->toBeTrue();
});

test('registration fails with missing required fields', function (): void {
    $response = postJson('/api/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'username',
                'name',
                'email',
                'password',
            ],
        ])
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ]);
});

test('registration fails with invalid email format', function (): void {
    $response = postJson('/api/auth/register', [
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
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

test('registration fails with duplicate username', function (): void {
    User::create([
        'username' => 'existinguser',
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $response = postJson('/api/auth/register', [
        'username' => 'existinguser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'username',
            ],
        ]);
});

test('registration fails with duplicate email', function (): void {
    User::create([
        'username' => 'existinguser',
        'name' => 'Existing User',
        'email' => 'existing@example.com',
        'password_hash' => bcrypt('password123'),
        'role' => 'user',
        'status' => 'active',
    ]);

    $response = postJson('/api/auth/register', [
        'username' => 'newuser',
        'name' => 'Test User',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
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

test('registration fails with password mismatch', function (): void {
    $response = postJson('/api/auth/register', [
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
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

test('registration fails with short password', function (): void {
    $response = postJson('/api/auth/register', [
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
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

test('user can register as salesperson', function (): void {
    $userData = [
        'username' => 'salesperson',
        'name' => 'Sales Person',
        'email' => 'sales@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'salesperson',
    ];

    $response = postJson('/api/auth/register', $userData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'user' => [
                    'role' => 'salesperson',
                ],
            ],
        ]);
});

test('registration creates user with hashed password', function (): void {
    $password = 'password123';

    postJson('/api/auth/register', [
        'username' => 'testuser',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->password_hash)->not->toBe($password);
    expect(password_verify($password, $user->password_hash))->toBeTrue();
});
