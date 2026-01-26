<?php

declare(strict_types=1);

use App\Models\ContactRequest;
use App\Models\Rating;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

beforeEach(function (): void {
    // Create user with account age > 7 days to bypass new user restriction
    $this->user = User::factory()->create([
        'role' => 'user',
        'created_at' => now()->subDays(30),
    ]);
    $this->salesperson = User::factory()->create(['role' => 'salesperson']);

    // Generate JWT tokens
    $this->userToken = JWTAuth::fromUser($this->user);
    $this->salespersonToken = JWTAuth::fromUser($this->salesperson);

    // Create contacted/completed contact request (status: contacted or closed)
    $this->contactRequest = ContactRequest::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'status' => 'contacted',
    ]);
});

test('user can submit rating with valid data', function (): void {
    $response = $this->withToken($this->userToken)
        ->postJson('/api/ratings', [
            'salesperson_id' => $this->salesperson->id,
            'contact_request_id' => $this->contactRequest->id,
            'rating' => 4.5,
            'comment' => 'Great service and very professional!',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'rating',
                'comment',
                'status',
                'created_at',
            ],
        ]);

    $this->assertDatabaseHas('ratings', [
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'rating' => 4.5,
    ]);
});

test('user cannot rate without completed contact', function (): void {
    $otherSalesperson = User::factory()->create(['role' => 'salesperson']);

    $response = $this->withToken($this->userToken)
        ->postJson('/api/ratings', [
            'salesperson_id' => $otherSalesperson->id,
            'contact_request_id' => $this->contactRequest->id,
            'rating' => 5.0,
            'comment' => 'This should fail',
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'NO_COMPLETED_CONTACT',
            ],
        ]);
});

test('user cannot rate same salesperson twice', function (): void {
    // First rating
    Rating::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
    ]);

    // Try to rate again
    $response = $this->withToken($this->userToken)
        ->postJson('/api/ratings', [
            'salesperson_id' => $this->salesperson->id,
            'contact_request_id' => $this->contactRequest->id,
            'rating' => 5.0,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'ALREADY_RATED',
            ],
        ]);
});

test('new user cannot rate within 7 days', function (): void {
    $newUser = User::factory()->create([
        'role' => 'user',
        'created_at' => now()->subDays(5), // Only 5 days old
    ]);

    $newUserToken = JWTAuth::fromUser($newUser);

    ContactRequest::factory()->create([
        'user_id' => $newUser->id,
        'salesperson_id' => $this->salesperson->id,
        'status' => 'contacted',
    ]);

    $response = $this->withToken($newUserToken)
        ->postJson('/api/ratings', [
            'salesperson_id' => $this->salesperson->id,
            'contact_request_id' => $this->contactRequest->id,
            'rating' => 5.0,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'NEW_USER_RESTRICTION',
            ],
        ]);
});

test('user can update rating within 7 days', function (): void {
    $rating = Rating::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'rating' => 3.0,
        'comment' => 'Original comment',
        'edit_count' => 0,
    ]);

    $response = $this->withToken($this->userToken)
        ->putJson("/api/ratings/{$rating->id}", [
            'rating' => 4.5,
            'comment' => 'Updated comment after further consideration',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.rating', 4.5)
        ->assertJsonPath('data.edit_count', 1);
});

test('user cannot update rating after 7 days', function (): void {
    $rating = Rating::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'created_at' => now()->subDays(8),
        'edit_count' => 0,
    ]);

    $response = $this->withToken($this->userToken)
        ->putJson("/api/ratings/{$rating->id}", [
            'rating' => 5.0,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'EDIT_EXPIRED',
            ],
        ]);
});

test('user cannot update rating more than once', function (): void {
    $rating = Rating::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'edit_count' => 1, // Already edited once
    ]);

    $response = $this->withToken($this->userToken)
        ->putJson("/api/ratings/{$rating->id}", [
            'rating' => 5.0,
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'EDIT_LIMIT_REACHED',
            ],
        ]);
});

test('salesperson can reply to rating', function (): void {
    $rating = Rating::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'comment' => 'Great service!',
        'reply' => null,
    ]);

    $response = $this->withToken($this->salespersonToken)
        ->postJson("/api/ratings/{$rating->id}/reply", [
            'reply' => 'Thank you for your kind words! It was a pleasure working with you.',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.reply', 'Thank you for your kind words! It was a pleasure working with you.');

    $this->assertDatabaseHas('ratings', [
        'id' => $rating->id,
    ]);

    $rating->refresh();
    expect($rating->reply)->not->toBeNull();
    expect($rating->replied_at)->not->toBeNull();
});

test('salesperson cannot reply twice', function (): void {
    $rating = Rating::factory()->withReply()->create([
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response = $this->withToken($this->salespersonToken)
        ->postJson("/api/ratings/{$rating->id}/reply", [
            'reply' => 'Another reply',
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'error' => [
                'code' => 'ALREADY_REPLIED',
            ],
        ]);
});

test('user can report rating', function (): void {
    $rating = Rating::factory()->create([
        'salesperson_id' => $this->salesperson->id,
    ]);

    $response = $this->withToken($this->userToken)
        ->postJson("/api/ratings/{$rating->id}/report", [
            'reason' => 'false_info',
            'description' => 'This rating contains false information about the salesperson.',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('rating_reports', [
        'rating_id' => $rating->id,
        'reporter_user_id' => $this->user->id,
        'reason' => 'false_info',
    ]);
});

test('get rating stats returns correct data', function (): void {
    // Create multiple ratings
    Rating::factory()->fiveStar()->create(['salesperson_id' => $this->salesperson->id]);
    Rating::factory()->fiveStar()->create(['salesperson_id' => $this->salesperson->id]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 4.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 3.0]);

    $response = $this->getJson("/api/salespersons/{$this->salesperson->id}/rating-stats");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'salesperson_id',
                'average_rating',
                'total_ratings',
                'rating_distribution',
                'rating_percentage',
            ],
        ])
        ->assertJsonPath('data.total_ratings', 4);
});

test('get ratings list returns paginated results', function (): void {
    // Create 15 ratings from different users
    foreach (range(1, 15) as $i) {
        $ratingUser = User::factory()->create([
            'role' => 'user',
            'created_at' => now()->subDays(30),
        ]);

        $contactRequest = ContactRequest::factory()->create([
            'user_id' => $ratingUser->id,
            'salesperson_id' => $this->salesperson->id,
            'status' => 'contacted',
        ]);

        Rating::factory()->create([
            'user_id' => $ratingUser->id,
            'salesperson_id' => $this->salesperson->id,
            'contact_request_id' => $contactRequest->id,
        ]);
    }

    $response = $this->getJson("/api/salespersons/{$this->salesperson->id}/ratings");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta' => [
                'current_page',
                'per_page',
                'total',
            ],
        ]);

    expect($response->json('data'))->toHaveCount(10); // Default pagination
});

test('ratings can be sorted by latest, highest, lowest', function (): void {
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 5.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 3.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 4.0]);

    // Sort by highest
    $response = $this->getJson("/api/salespersons/{$this->salesperson->id}/ratings?sort=highest");
    $response->assertStatus(200);
    expect($response->json('data.0.rating'))->toEqual(5.0);

    // Sort by lowest
    $response = $this->getJson("/api/salespersons/{$this->salesperson->id}/ratings?sort=lowest");
    $response->assertStatus(200);
    expect($response->json('data.0.rating'))->toEqual(3.0);
});

test('admin can moderate rating', function (): void {
    $admin = User::factory()->create(['role' => 'admin']);
    $adminToken = JWTAuth::fromUser($admin);

    $rating = Rating::factory()->create([
        'status' => Rating::STATUS_PENDING,
    ]);

    $response = $this->withToken($adminToken)
        ->postJson("/api/admin/ratings/{$rating->id}/moderate", [
            'action' => 'approve',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('ratings', [
        'id' => $rating->id,
        'status' => Rating::STATUS_APPROVED,
    ]);
});

test('non-admin cannot moderate rating', function (): void {
    $rating = Rating::factory()->create();

    $response = $this->withToken($this->userToken)
        ->postJson("/api/admin/ratings/{$rating->id}/moderate", [
            'action' => 'approve',
        ]);

    $response->assertStatus(403);
});
