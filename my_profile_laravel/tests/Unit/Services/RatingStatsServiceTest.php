<?php

declare(strict_types=1);

use App\Models\Rating;
use App\Models\User;
use App\Services\RatingStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new RatingStatsService();
    $this->salesperson = User::factory()->create(['role' => 'salesperson']);
});

test('service calculates average rating correctly', function (): void {
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 5.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 4.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 3.0]);

    $stats = $this->service->getStats($this->salesperson->id);

    expect($stats['average_rating'])->toBe(4.0);
    expect($stats['total_ratings'])->toBe(3);
});

test('service returns zero for salesperson with no ratings', function (): void {
    $stats = $this->service->getStats($this->salesperson->id);

    expect($stats['average_rating'])->toBe(0.0);
    expect($stats['total_ratings'])->toBe(0);
});

test('service calculates rating distribution correctly', function (): void {
    Rating::factory()->count(2)->create(['salesperson_id' => $this->salesperson->id, 'rating' => 5.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 4.0]);
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 3.5]);

    $stats = $this->service->getStats($this->salesperson->id);

    expect($stats['rating_distribution'])->toHaveKey('5');
    expect($stats['rating_distribution'])->toHaveKey('4');
    expect($stats['rating_distribution'])->toHaveKey('3.5');
});

test('service caches stats correctly', function (): void {
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 5.0]);

    // First call - cache miss
    $stats1 = $this->service->getStats($this->salesperson->id);

    // Second call - should hit cache
    $stats2 = $this->service->getStats($this->salesperson->id);

    expect($stats1)->toBe($stats2);

    // Verify cache was used
    expect(Cache::has("rating_stats:salesperson:{$this->salesperson->id}"))->toBeTrue();
});

test('service clears cache correctly', function (): void {
    $this->service->getStats($this->salesperson->id);

    expect(Cache::has("rating_stats:salesperson:{$this->salesperson->id}"))->toBeTrue();

    $this->service->clearCache($this->salesperson->id);

    expect(Cache::has("rating_stats:salesperson:{$this->salesperson->id}"))->toBeFalse();
});

test('service recalculates stats after cache clear', function (): void {
    // Initial stats with 1 rating
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 3.0]);
    $stats1 = $this->service->getStats($this->salesperson->id);
    expect($stats1['total_ratings'])->toBe(1);

    // Add another rating
    Rating::factory()->create(['salesperson_id' => $this->salesperson->id, 'rating' => 5.0]);

    // Recalculate
    $stats2 = $this->service->recalculate($this->salesperson->id);

    expect($stats2['total_ratings'])->toBe(2);
    expect($stats2['average_rating'])->toBe(4.0);
});

test('service only includes public ratings', function (): void {
    // Public ratings
    Rating::factory()->count(2)->create([
        'salesperson_id' => $this->salesperson->id,
        'status' => Rating::STATUS_APPROVED,
        'is_hidden' => false,
    ]);

    // Hidden rating
    Rating::factory()->create([
        'salesperson_id' => $this->salesperson->id,
        'status' => Rating::STATUS_APPROVED,
        'is_hidden' => true,
    ]);

    // Pending rating
    Rating::factory()->create([
        'salesperson_id' => $this->salesperson->id,
        'status' => Rating::STATUS_PENDING,
        'is_hidden' => false,
    ]);

    $stats = $this->service->getStats($this->salesperson->id);

    // Should only count the 2 public ratings
    expect($stats['total_ratings'])->toBe(2);
});
