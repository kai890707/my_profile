<?php

declare(strict_types=1);

use App\Models\ContactEvent;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    // Create a salesperson for testing
    $this->salesperson = User::factory()->create([
        'role' => 'salesperson',
        'email' => 'salesperson@example.com',
    ]);

    $this->salesperson->salespersonProfile()->create([
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);
});

// Test: Track profile_view event (authenticated user)
test('can track profile view event when authenticated', function (): void {
    $user = User::factory()->create(['role' => 'user']);

    $response = actingAs($user, 'api')->postJson('/api/events/track', [
        'event_type' => 'profile_view',
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => '事件已記錄',
        ]);

    assertDatabaseHas('contact_events', [
        'user_id' => $user->id,
        'salesperson_id' => $this->salesperson->id,
        'event_type' => 'profile_view',
    ]);
});

// Test: Track profile_view event (anonymous user)
test('can track profile view event when anonymous', function (): void {
    $response = postJson('/api/events/track', [
        'event_type' => 'profile_view',
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => '事件已記錄',
        ]);

    assertDatabaseHas('contact_events', [
        'user_id' => null,
        'salesperson_id' => $this->salesperson->id,
        'event_type' => 'profile_view',
    ]);
});

// Test: Track contact_form_submission event
test('can track contact form submission event', function (): void {
    $user = User::factory()->create(['role' => 'user']);

    $response = actingAs($user, 'api')->postJson('/api/events/track', [
        'event_type' => 'contact_form_submission',
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response->assertStatus(201);

    assertDatabaseHas('contact_events', [
        'user_id' => $user->id,
        'salesperson_id' => $this->salesperson->id,
        'event_type' => 'contact_form_submission',
    ]);
});

// Test: IP address is hashed
test('ip address is hashed with SHA256', function (): void {
    $response = postJson('/api/events/track', [
        'event_type' => 'profile_view',
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response->assertStatus(201);

    $event = ContactEvent::latest()->first();
    expect($event->ip_address_hash)->not->toBeEmpty();
    expect($event->ip_address_hash)->toHaveLength(64); // SHA256 produces 64 hex characters
});

// Test: Performance requirement (< 100ms)
test('track event meets performance requirement', function (): void {
    $startTime = microtime(true);

    $response = postJson('/api/events/track', [
        'event_type' => 'profile_view',
        'salesperson_id' => $this->salesperson->id,
    ]);

    $endTime = microtime(true);
    $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

    $response->assertStatus(201);
    expect($duration)->toBeLessThan(100); // P95 < 100ms
});

// Test: Invalid event_type
test('fails when event_type is invalid', function (): void {
    $response = postJson('/api/events/track', [
        'event_type' => 'invalid_event',
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
                'details',
            ],
        ]);
});

// Test: Missing salesperson_id
test('fails when salesperson_id is missing', function (): void {
    $response = postJson('/api/events/track', [
        'event_type' => 'profile_view',
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
                'details',
            ],
        ]);
});

// Test: Salesperson does not exist
test('fails when salesperson does not exist', function (): void {
    $response = postJson('/api/events/track', [
        'event_type' => 'profile_view',
        'salesperson_id' => 99999,
    ]);

    $response->assertStatus(422);
});

// Test: Get contact info for approved salesperson
test('can get contact info for approved salesperson', function (): void {
    // Update profile with contact methods
    $this->salesperson->salespersonProfile->update([
        'phone' => '0912345678',
        'email_public' => 'contact@example.com',
        'line_id' => 'my_line_id',
        'wechat_id' => 'my_wechat',
        'contact_preferences' => ['line', 'phone'],
    ]);

    $response = getJson("/api/salesperson/{$this->salesperson->id}/contact-info");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'phone' => '0912345678',
                'email_public' => 'contact@example.com',
                'line_id' => 'my_line_id',
                'wechat_id' => 'my_wechat',
                'contact_preferences' => ['line', 'phone'],
                'has_contact_methods' => true,
            ],
        ]);
});

// Test: Cannot get contact info for pending salesperson
test('cannot get contact info for pending salesperson', function (): void {
    $this->salesperson->salespersonProfile->update([
        'approval_status' => 'pending',
    ]);

    $response = getJson("/api/salesperson/{$this->salesperson->id}/contact-info");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'error' => '業務員不存在或尚未通過審核',
        ]);
});

// Test: Cannot get contact info for rejected salesperson
test('cannot get contact info for rejected salesperson', function (): void {
    $this->salesperson->salespersonProfile->update([
        'approval_status' => 'rejected',
    ]);

    $response = getJson("/api/salesperson/{$this->salesperson->id}/contact-info");

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'error' => '業務員不存在或尚未通過審核',
        ]);
});
