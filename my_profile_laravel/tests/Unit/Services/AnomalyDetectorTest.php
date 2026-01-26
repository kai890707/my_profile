<?php

declare(strict_types=1);

use App\Models\Rating;
use App\Models\User;
use App\Services\AnomalyDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->detector = new AnomalyDetector();
    $this->salesperson = User::factory()->create(['role' => 'salesperson']);
});

test('detector identifies mass high ratings', function (): void {
    // Create 5 five-star ratings within 1 hour
    Rating::factory()->count(5)->create([
        'salesperson_id' => $this->salesperson->id,
        'rating' => 5.0,
        'created_at' => now()->subMinutes(30),
    ]);

    $newRating = Rating::factory()->make([
        'salesperson_id' => $this->salesperson->id,
        'rating' => 5.0,
        'created_at' => now(),
    ]);

    expect($this->detector->detect($newRating))->toBeTrue();
    expect($this->detector->getAnomalyReasons($newRating))
        ->toContain('mass_high_ratings');
});

test('detector ignores non-five-star ratings for mass detection', function (): void {
    // Create 5 four-star ratings
    Rating::factory()->count(5)->create([
        'salesperson_id' => $this->salesperson->id,
        'rating' => 4.0,
        'created_at' => now()->subMinutes(30),
    ]);

    $newRating = Rating::factory()->make([
        'salesperson_id' => $this->salesperson->id,
        'rating' => 4.0,
    ]);

    expect($this->detector->detect($newRating))->toBeFalse();
});

test('detector identifies same IP multiple ratings', function (): void {
    $ipAddress = '192.168.1.100';

    // Create recent rating from same IP
    Rating::factory()->create([
        'ip_address' => $ipAddress,
        'created_at' => now()->subHours(12),
    ]);

    $newRating = Rating::factory()->make([
        'ip_address' => $ipAddress,
    ]);
    $newRating->save();

    expect($this->detector->detect($newRating))->toBeTrue();
    expect($this->detector->getAnomalyReasons($newRating))
        ->toContain('same_ip_multiple_ratings');
});

test('detector identifies similar comments', function (): void {
    // Create rating with specific comment
    Rating::factory()->create([
        'salesperson_id' => $this->salesperson->id,
        'comment' => '服務態度非常好，專業度高，推薦！',
        'created_at' => now()->subDays(3),
    ]);

    $newRating = Rating::factory()->make([
        'salesperson_id' => $this->salesperson->id,
        'comment' => '服務態度非常好，專業度很高，推薦！', // Very similar
    ]);
    $newRating->save();

    expect($this->detector->detect($newRating))->toBeTrue();
    expect($this->detector->getAnomalyReasons($newRating))
        ->toContain('similar_comments');
});

test('detector does not flag ratings without comment', function (): void {
    $newRating = Rating::factory()->make([
        'salesperson_id' => $this->salesperson->id,
        'comment' => null,
    ]);
    $newRating->save();

    // Even with similar conditions, no comment means no similarity detection
    $reasons = $this->detector->getAnomalyReasons($newRating);
    expect($reasons)->not->toContain('similar_comments');
});
