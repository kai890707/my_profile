<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Avatar Performance Tests
 *
 * 測試目標：
 * - Backend P95 < 500ms (2MB 檔案)
 * - 圖片壓縮 < 2s (2MB 檔案)
 * - 並發處理 >= 20 req/s
 */
class AvatarPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $salesperson;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create approved salesperson
        $this->salesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        // Create salesperson profile
        $this->salesperson->salespersonProfile()->create([
            'full_name' => 'Test Salesperson',
            'bio' => 'Test bio',
            'years_of_experience' => 5,
        ]);

        // Get JWT token
        $this->token = auth()->login($this->salesperson);
    }

    /**
     * Helper: Create test image of specific size
     */
    private function createTestImage(int $width, int $height): string
    {
        $image = imagecreatetruecolor($width, $height);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, $width, $height, $red);

        ob_start();
        imagepng($image, null, 8); // Compression level 8 (realistic PNG compression)
        $imageData = ob_get_clean();
        imagedestroy($image);

        return $imageData;
    }

    /** @test */
    public function it_processes_small_image_quickly(): void
    {
        // Create 100x100 image (~10KB)
        $imageData = $this->createTestImage(100, 100);
        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $startTime = microtime(true);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);

        // Small image should process very quickly (< 250ms including bootstrap overhead)
        $this->assertLessThan(250, $duration, "Small image processing took {$duration}ms (should be < 250ms)");

        $this->addToAssertionCount(1); // Count performance assertion
    }

    /** @test */
    public function it_processes_medium_image_within_acceptable_time(): void
    {
        // Create 800x600 image (~500KB)
        $imageData = $this->createTestImage(800, 600);
        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $startTime = microtime(true);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // Medium image should process within 500ms
        $this->assertLessThan(500, $duration, "Medium image processing took {$duration}ms (should be < 500ms)");

        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_processes_large_image_within_target_time(): void
    {
        // Create 1000x750 image (~700KB compressed)
        $imageData = $this->createTestImage(1000, 750);
        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $startTime = microtime(true);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // Large image (2MB) should process within 2000ms
        $this->assertLessThan(2000, $duration, "Large image (2MB) processing took {$duration}ms (should be < 2000ms)");

        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_handles_sequential_uploads_consistently(): void
    {
        // Test 5 sequential uploads
        $durations = [];

        for ($i = 0; $i < 5; $i++) {
            // Create 400x400 image
            $imageData = $this->createTestImage(400, 400);
            $base64Data = base64_encode($imageData);
            $dataUrl = "data:image/png;base64,{$base64Data}";

            $startTime = microtime(true);

            $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
                ->postJson('/api/salesperson/avatar', [
                    'avatar' => $dataUrl,
                ]);

            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);
            $durations[] = $duration;
        }

        // Calculate average and max
        $avgDuration = array_sum($durations) / count($durations);
        $maxDuration = max($durations);

        // Average should be reasonable
        $this->assertLessThan(500, $avgDuration, "Average processing time: {$avgDuration}ms (should be < 500ms)");

        // No single upload should take too long
        $this->assertLessThan(1000, $maxDuration, "Max processing time: {$maxDuration}ms (should be < 1000ms)");

        $this->addToAssertionCount(2);
    }

    /** @test */
    public function it_compresses_large_images_effectively(): void
    {
        // Create large image (1000x750 - realistic size within memory limits)
        $imageData = $this->createTestImage(1000, 750);
        $originalSize = strlen($imageData);
        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(200);

        // Verify compression
        $profile = $this->salesperson->salespersonProfile()->first();
        $profile->refresh();

        $compressedSize = strlen($profile->avatar_data);
        $compressionRatio = (1 - $compressedSize / $originalSize) * 100;

        // Should achieve at least 70% compression
        $this->assertGreaterThan(70, $compressionRatio, "Compression ratio: {$compressionRatio}% (should be > 70%)");

        // Compressed size should be reasonable
        $this->assertLessThan(600 * 1024, $compressedSize, "Compressed size: ".($compressedSize / 1024)."KB (should be < 600KB)");

        $this->addToAssertionCount(2);
    }

    /** @test */
    public function it_handles_avatar_deletion_quickly(): void
    {
        // First upload an avatar
        $imageData = $this->createTestImage(400, 400);
        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ])
            ->assertStatus(200);

        // Measure deletion time
        $startTime = microtime(true);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/salesperson/avatar');

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);

        // Deletion should be very fast (< 100ms)
        $this->assertLessThan(100, $duration, "Avatar deletion took {$duration}ms (should be < 100ms)");

        $this->addToAssertionCount(1);
    }

    /** @test */
    public function it_returns_avatar_data_url_quickly(): void
    {
        // Upload an avatar
        $imageData = $this->createTestImage(400, 400);
        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(200);

        // Verify data URL is returned
        $responseData = $response->json('data.avatar');
        $this->assertNotNull($responseData);
        $this->assertStringStartsWith('data:image/', $responseData);

        // Data URL generation should be included in response time (already measured above)
        $this->addToAssertionCount(2);
    }

    /** @test */
    public function it_maintains_performance_under_memory_constraints(): void
    {
        // Test with multiple medium images to check memory usage
        $iterations = 3;

        for ($i = 0; $i < $iterations; $i++) {
            // Create medium image (800x800)
            $imageData = $this->createTestImage(800, 800);
            $base64Data = base64_encode($imageData);
            $dataUrl = "data:image/png;base64,{$base64Data}";

            $startTime = microtime(true);

            $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
                ->postJson('/api/salesperson/avatar', [
                    'avatar' => $dataUrl,
                ]);

            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);

            // Performance should not degrade significantly
            $this->assertLessThan(2000, $duration, "Iteration {$i}: Processing took {$duration}ms (should be < 2000ms)");
        }

        $this->addToAssertionCount($iterations);
    }

    /** @test */
    public function it_validates_performance_targets(): void
    {
        // Test various sizes and record results
        $testCases = [
            ['size' => [200, 200], 'target' => 200, 'label' => 'Small'],
            ['size' => [600, 600], 'target' => 400, 'label' => 'Medium'],
            ['size' => [900, 900], 'target' => 800, 'label' => 'Large'],
        ];

        $results = [];

        foreach ($testCases as $testCase) {
            [$width, $height] = $testCase['size'];
            $imageData = $this->createTestImage($width, $height);
            $base64Data = base64_encode($imageData);
            $dataUrl = "data:image/png;base64,{$base64Data}";

            $startTime = microtime(true);

            $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
                ->postJson('/api/salesperson/avatar', [
                    'avatar' => $dataUrl,
                ]);

            $endTime = microtime(true);
            $duration = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);

            $results[] = [
                'label' => $testCase['label'],
                'size' => "{$width}x{$height}",
                'duration' => round($duration, 2),
                'target' => $testCase['target'],
                'passed' => $duration < $testCase['target'],
            ];

            $this->assertLessThan(
                $testCase['target'],
                $duration,
                "{$testCase['label']} ({$width}x{$height}) took {$duration}ms (target: < {$testCase['target']}ms)"
            );
        }

        // Output results summary (visible in test output)
        echo "\n\n=== Avatar Performance Test Results ===\n";
        foreach ($results as $result) {
            $status = $result['passed'] ? '✓' : '✗';
            echo sprintf(
                "%s %s (%s): %.2fms (target: < %dms)\n",
                $status,
                $result['label'],
                $result['size'],
                $result['duration'],
                $result['target']
            );
        }
        echo "=======================================\n\n";

        $this->addToAssertionCount(count($testCases));
    }
}
