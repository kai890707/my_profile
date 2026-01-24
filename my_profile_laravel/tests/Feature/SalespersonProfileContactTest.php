<?php

declare(strict_types=1);

use App\Models\SalespersonProfile;
use App\Models\User;

beforeEach(function () {
    // Create an approved salesperson for testing
    $this->salesperson = User::factory()->create([
        'role' => User::ROLE_SALESPERSON,
        'salesperson_status' => User::STATUS_APPROVED,
    ]);

    $this->profile = SalespersonProfile::factory()->create([
        'user_id' => $this->salesperson->id,
        'approval_status' => 'approved',
        'phone' => null,
        'email_public' => null,
        'line_id' => null,
        'wechat_id' => null,
        'contact_preferences' => null,
    ]);

    // Login as salesperson and get JWT token
    $this->token = auth()->login($this->salesperson);
});

/**
 * Test 1: Update contact methods successfully
 */
test('salesperson can update contact methods successfully', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '0912345678',
            'email_public' => 'contact@example.com',
            'line_id' => 'my_line_id',
            'wechat_id' => 'my_wechat',
            'contact_preferences' => ['line', 'phone', 'email', 'wechat'],
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'phone',
                'email_public',
                'line_id',
                'wechat_id',
                'contact_preferences',
                'has_contact_methods',
            ],
            'message',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'phone' => '0912345678',
                'email_public' => 'contact@example.com',
                'line_id' => 'my_line_id',
                'wechat_id' => 'my_wechat',
                'has_contact_methods' => true,
            ],
            'message' => '聯繫方式已更新',
        ]);

    // Verify database was updated
    $this->assertDatabaseHas('salesperson_profiles', [
        'id' => $this->profile->id,
        'phone' => '0912345678',
        'email_public' => 'contact@example.com',
        'line_id' => 'my_line_id',
        'wechat_id' => 'my_wechat',
    ]);
});

/**
 * Test 2: Require at least one contact method
 */
test('update requires at least one contact method', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => null,
            'email_public' => null,
            'line_id' => null,
            'wechat_id' => null,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

/**
 * Test 3: Invalid phone format
 */
test('update fails with invalid phone format', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '12345678', // Invalid: missing area code
            'email_public' => 'contact@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['phone'])
        ->assertJsonFragment([
            'phone' => ['電話格式不正確（手機: 0912345678 或市話: 02-12345678）'],
        ]);
});

/**
 * Test 4: Invalid email format
 */
test('update fails with invalid email format', function () {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '0912345678',
            'email_public' => 'invalid-email',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email_public'])
        ->assertJsonFragment([
            'email_public' => ['Email 格式不正確'],
        ]);
});

/**
 * Test 5: Invalid LINE ID format
 */
test('update fails with invalid line id', function () {
    // Test too short
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '0912345678',
            'line_id' => 'ab', // Less than 3 characters
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['line_id'])
        ->assertJsonFragment([
            'line_id' => ['LINE ID 最少 3 個字元'],
        ]);

    // Test invalid characters
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '0912345678',
            'line_id' => 'line@123', // Contains @
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['line_id'])
        ->assertJsonFragment([
            'line_id' => ['LINE ID 格式不正確（僅允許英數字和底線）'],
        ]);

    // Test too long
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '0912345678',
            'line_id' => 'this_is_a_very_long_line_id_123', // More than 20 characters
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['line_id'])
        ->assertJsonFragment([
            'line_id' => ['LINE ID 最多 20 個字元'],
        ]);
});

/**
 * Test 6: Requires salesperson role
 */
test('update requires salesperson role', function () {
    // Create a regular user
    $regularUser = User::factory()->create([
        'role' => User::ROLE_USER,
    ]);

    $regularToken = auth()->login($regularUser);

    $response = $this->withHeader('Authorization', 'Bearer '.$regularToken)
        ->putJson('/api/salesperson/profile/contact', [
            'phone' => '0912345678',
        ]);

    $response->assertStatus(403);
});

/**
 * Test 7: Requires authentication
 */
test('update requires authentication', function () {
    // No Authorization header
    $response = $this->putJson('/api/salesperson/profile/contact', [
        'phone' => '0912345678',
    ]);

    $response->assertStatus(401);
});
