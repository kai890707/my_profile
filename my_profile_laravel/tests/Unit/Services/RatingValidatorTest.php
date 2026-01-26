<?php

declare(strict_types=1);

use App\Models\ContactRequest;
use App\Models\Rating;
use App\Models\User;
use App\Services\RatingValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->validator = new RatingValidator();
    $this->user = User::factory()->create([
        'role' => 'user',
        'created_at' => now()->subDays(30), // Old enough user
    ]);
    $this->salesperson = User::factory()->create(['role' => 'salesperson']);
});

test('validator allows rating when all conditions are met', function (): void {
    // Create completed contact
    ContactRequest::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'status' => 'contacted',
    ]);

    $canRate = $this->validator->canUserRate($this->user, $this->salesperson->id);

    expect($canRate)->toBeTrue();
});

test('validator rejects new user', function (): void {
    $newUser = User::factory()->create([
        'created_at' => now()->subDays(5), // Only 5 days old
    ]);

    ContactRequest::factory()->create([
        'user_id' => $newUser->id,
        'salesperson_id' => $this->salesperson->id,
        'status' => 'contacted',
    ]);

    $canRate = $this->validator->canUserRate($newUser, $this->salesperson->id);

    expect($canRate)->toBeFalse();
    expect($this->validator->getIneligibleReason($newUser, $this->salesperson->id))
        ->toBe('new_user_restriction');
});

test('validator rejects user without completed contact', function (): void {
    $canRate = $this->validator->canUserRate($this->user, $this->salesperson->id);

    expect($canRate)->toBeFalse();
    expect($this->validator->getIneligibleReason($this->user, $this->salesperson->id))
        ->toBe('no_completed_contact');
});

test('validator rejects duplicate rating', function (): void {
    ContactRequest::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
        'status' => 'contacted',
    ]);

    // Create existing rating
    Rating::factory()->create([
        'user_id' => $this->user->id,
        'salesperson_id' => $this->salesperson->id,
    ]);

    $canRate = $this->validator->canUserRate($this->user, $this->salesperson->id);

    expect($canRate)->toBeFalse();
    expect($this->validator->getIneligibleReason($this->user, $this->salesperson->id))
        ->toBe('already_rated');
});

test('validator checks IP limit correctly', function (): void {
    $ipAddress = '192.168.1.100';

    // First check - should pass
    expect($this->validator->checkIpLimit($ipAddress))->toBeTrue();

    // Create recent rating from same IP
    Rating::factory()->create([
        'ip_address' => $ipAddress,
        'created_at' => now()->subHours(12), // Within 24 hours
    ]);

    // Second check - should fail
    expect($this->validator->checkIpLimit($ipAddress))->toBeFalse();
});

test('validator identifies new user correctly', function (): void {
    $newUser = User::factory()->create([
        'created_at' => now()->subDays(3),
    ]);

    $oldUser = User::factory()->create([
        'created_at' => now()->subDays(10),
    ]);

    expect($this->validator->isNewUser($newUser))->toBeTrue();
    expect($this->validator->isNewUser($oldUser))->toBeFalse();
});
