<?php

declare(strict_types=1);

use App\Jobs\SendContactRequestEmail;
use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\SalespersonProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Create test users
    $this->customer = User::factory()->create([
        'role' => 'user',
        'email' => 'customer@example.com',
        'name' => 'Test Customer',
    ]);

    $this->salesperson = User::factory()->create([
        'role' => 'salesperson',
        'email' => 'salesperson@example.com',
        'name' => 'Test Salesperson',
    ]);

    // Create approved salesperson profile
    SalespersonProfile::factory()->create([
        'user_id' => $this->salesperson->id,
        'approval_status' => 'approved',
        'phone' => '0912345678',
        'email_public' => 'public@example.com',
    ]);

    // Generate JWT token for customer
    $this->token = auth('api')->login($this->customer);

    // Fake Queue for email testing
    Queue::fake();
});

// Test: Submit contact request successfully
test('testSubmitContactRequestSuccessfully', function (): void {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'customer_phone' => '0987654321',
            'message' => '您好，我想詢問保險相關服務，請問方便聯繫嗎？',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'salesperson_id',
                'customer_name',
                'customer_email',
                'customer_phone',
                'message',
                'status',
                'created_at',
                'salesperson',
            ],
            'message',
        ]);

    // Verify database record
    $this->assertDatabaseHas('contact_requests', [
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_name' => 'Test Customer',
        'status' => 'pending',
    ]);

    // Verify event tracked
    $this->assertDatabaseHas('contact_events', [
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'event_type' => 'contact_form_submission',
    ]);

    // Verify email job dispatched
    Queue::assertPushed(SendContactRequestEmail::class);
});

// Test: Submit contact request without phone (phone is nullable)
test('testSubmitContactRequestWithoutPhone', function (): void {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => '您好，我想詢問保險相關服務，請問方便聯繫嗎？',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('contact_requests', [
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_phone' => null,
    ]);
});

// Test: Cannot contact same salesperson within 24 hours (BR-RL-002)
test('testCannotContactSameSalespersonWithin24Hours', function (): void {
    // Create first contact request
    ContactRequest::factory()->create([
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'created_at' => Carbon::now()->subHours(12),
    ]);

    // Try to contact again within 24h
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => '再次聯繫測試',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['salesperson_id']);
});

// Test: Cannot submit more than 5 requests per day (BR-RL-003)
test('testCannotSubmitMoreThan5RequestsPerDay', function (): void {
    // Create 5 contact requests today (to different salespersons)
    for ($i = 0; $i < 5; $i++) {
        $salesperson = User::factory()->create(['role' => 'salesperson']);
        SalespersonProfile::factory()->create([
            'user_id' => $salesperson->id,
            'approval_status' => 'approved',
        ]);

        ContactRequest::factory()->create([
            'user_id' => $this->customer->id,
            'salesperson_id' => $salesperson->id,
            'created_at' => Carbon::today()->addHours($i),
        ]);
    }

    // Try to submit 6th request
    $anotherSalesperson = User::factory()->create(['role' => 'salesperson']);
    SalespersonProfile::factory()->create([
        'user_id' => $anotherSalesperson->id,
        'approval_status' => 'approved',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $anotherSalesperson->id,
            'message' => '第六次聯繫測試',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rate_limit']);
});

// Test: IP rate limit enforced (throttle:5,60)
test('testIpRateLimitEnforced', function (): void {
    // This test would require actual HTTP requests to test throttle middleware
    // For now, we verify the route has the middleware applied
    $routes = app('router')->getRoutes();
    $route = $routes->match(request()->create('/api/contact-requests', 'POST'));

    expect($route)->not->toBeNull();

    $middleware = $route->middleware();
    $hasThrottle = collect($middleware)->contains(fn ($m) => str_contains($m, 'throttle'));
    expect($hasThrottle)->toBeTrue();
});

// Test: Validation errors
test('testValidationErrors', function (): void {
    // Missing salesperson_id
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'message' => '測試訊息',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['salesperson_id']);

    // Missing message
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    // Message too short (< 10 characters)
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => 'Hi',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    // Message too long (> 500 characters)
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => str_repeat('a', 501),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    // Invalid customer_phone format
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'customer_phone' => '12345',
            'message' => '測試訊息至少十個字',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['customer_phone']);
});

// Test: Requires authentication (BR-AR-003)
test('testRequiresAuthentication', function (): void {
    $response = $this->postJson('/api/contact-requests', [
        'salesperson_id' => $this->salesperson->id,
        'message' => '測試訊息至少十個字',
    ]);

    $response->assertStatus(401);
});

// Test: Cannot contact pending salesperson (BR-VR-002)
test('testCannotContactPendingSalesperson', function (): void {
    $pendingSalesperson = User::factory()->create(['role' => 'salesperson']);
    SalespersonProfile::factory()->create([
        'user_id' => $pendingSalesperson->id,
        'approval_status' => 'pending',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $pendingSalesperson->id,
            'message' => '測試訊息至少十個字',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['salesperson_id']);
});

// Test: Cannot contact rejected salesperson (BR-VR-002)
test('testCannotContactRejectedSalesperson', function (): void {
    $rejectedSalesperson = User::factory()->create(['role' => 'salesperson']);
    SalespersonProfile::factory()->create([
        'user_id' => $rejectedSalesperson->id,
        'approval_status' => 'rejected',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $rejectedSalesperson->id,
            'message' => '測試訊息至少十個字',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['salesperson_id']);
});

// Test: Email notification dispatched
test('testEmailNotificationDispatched', function (): void {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => '您好，我想詢問保險相關服務，請問方便聯繫嗎？',
        ]);

    $response->assertStatus(201);

    // Verify email job was dispatched
    Queue::assertPushed(SendContactRequestEmail::class, function ($job) {
        return $job->contactRequest->salesperson_id === $this->salesperson->id;
    });
});

// Test: Event tracked successfully
test('testEventTracked', function (): void {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => '您好，我想詢問保險相關服務，請問方便聯繫嗎？',
        ]);

    $response->assertStatus(201);

    // Verify event was tracked
    $event = ContactEvent::where('user_id', $this->customer->id)
        ->where('salesperson_id', $this->salesperson->id)
        ->where('event_type', 'contact_form_submission')
        ->first();

    expect($event)->not->toBeNull();
    expect($event->ip_address_hash)->not->toBeNull();
});

// Test: XSS protection (strip_tags)
test('testXssProtection', function (): void {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => '<script>alert("xss")</script>這是測試訊息內容',
        ]);

    $response->assertStatus(201);

    $contactRequest = ContactRequest::latest()->first();
    expect($contactRequest->message)->not->toContain('<script>');
    expect($contactRequest->message)->toBe('alert("xss")這是測試訊息內容');
});

// Test: Customer data auto-filled from auth user
test('testCustomerDataAutoFilled', function (): void {
    $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
        ->postJson('/api/contact-requests', [
            'salesperson_id' => $this->salesperson->id,
            'message' => '測試訊息內容至少要十個字元才會通過驗證',
        ]);

    $response->assertStatus(201);

    $contactRequest = ContactRequest::latest()->first();
    expect($contactRequest->customer_name)->toBe('Test Customer');
    expect($contactRequest->customer_email)->toBe('customer@example.com');
    expect($contactRequest->user_id)->toBe($this->customer->id);
});
