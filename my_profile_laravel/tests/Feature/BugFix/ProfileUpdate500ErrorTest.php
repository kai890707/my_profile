<?php

declare(strict_types=1);

namespace Tests\Feature\BugFix;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression Test: Fix Profile Update 500 Error
 *
 * Bug: PUT /api/salesperson/profile returns 500 error when profile has avatar
 * Root Cause: Binary avatar_data from BLOB column not base64-encoded for JSON
 * Fix: Use AvatarService::getAvatarUrl() helper to properly encode binary data
 *
 * Date: 2026-01-22
 * Related: openspec/changes/20260122-fix-profile-update-500-error/
 */
class ProfileUpdate500ErrorTest extends TestCase
{
    use RefreshDatabase;

    protected User $salesperson;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create approved salesperson with avatar containing binary data
        $this->salesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        // Create profile with avatar as binary data (simulating BLOB storage)
        $image = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, 100, 100, $red);

        ob_start();
        imagepng($image, null, 8);
        $binaryData = ob_get_clean();
        imagedestroy($image);

        $this->salesperson->salespersonProfile()->create([
            'full_name' => 'Test Salesperson',
            'bio' => 'Test bio',
            'years_of_experience' => 5,
            'avatar_data' => $binaryData,
            'avatar_mime' => 'image/png',
        ]);

        // Get JWT token
        $this->token = auth()->login($this->salesperson);
    }

    /** @test */
    public function profile_update_does_not_cause_500_error_with_existing_avatar(): void
    {
        // This was returning 500 error before the fix
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/salesperson/profile', [
                'bio' => 'Updated bio with avatar present',
            ]);

        // Should return 200, not 500
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '個人資料已更新',
            ])
            ->assertJsonStructure([
                'success',
                'profile' => [
                    'avatar', // Should be present and properly formatted
                    'bio',
                ],
            ]);

        // Verify avatar is a valid data URL
        $avatar = $response->json('profile.avatar');
        $this->assertIsString($avatar);
        $this->assertStringStartsWith('data:image/png;base64,', $avatar);

        // Verify the base64 part is valid
        $base64 = substr($avatar, strlen('data:image/png;base64,'));
        $this->assertNotEmpty($base64);
        $decoded = base64_decode($base64, true);
        $this->assertNotFalse($decoded, 'Avatar base64 data should be valid');
    }

    /** @test */
    public function salesperson_search_does_not_cause_500_error_with_avatars(): void
    {
        // Also affected: GET /api/salespeople with avatars
        $response = $this->getJson('/api/salespeople');

        // Should return 200, not 500
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'full_name',
                            'avatar', // Should be properly formatted
                        ],
                    ],
                ],
            ]);

        // Verify avatar format in search results
        $salespeople = $response->json('data.data');
        if (! empty($salespeople)) {
            $firstSalesperson = $salespeople[0];
            if ($firstSalesperson['avatar'] !== null) {
                $this->assertStringStartsWith('data:image/', $firstSalesperson['avatar']);
                $this->assertStringContainsString('base64,', $firstSalesperson['avatar']);
            }
        }
    }

    /** @test */
    public function profile_update_with_binary_avatar_and_other_fields(): void
    {
        // Update multiple fields while avatar exists
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/salesperson/profile', [
                'bio' => 'Completely new bio',
                'phone' => '0912345678',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'bio' => 'Completely new bio',
                'phone' => '0912345678',
            ]);

        // Avatar should still be present and valid
        $avatar = $response->json('profile.avatar');
        $this->assertIsString($avatar);
        $this->assertStringStartsWith('data:image/png;base64,', $avatar);
    }

    /** @test */
    public function profile_update_preserves_avatar_when_not_updating_avatar(): void
    {
        // Get original avatar
        $originalProfile = $this->salesperson->salespersonProfile;
        $originalAvatarData = $originalProfile->avatar_data;

        // Update profile without touching avatar field
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/salesperson/profile', [
                'bio' => 'New bio without avatar change',
            ]);

        $response->assertStatus(200);

        // Verify avatar data in database is unchanged
        $originalProfile->refresh();
        $this->assertEquals($originalAvatarData, $originalProfile->avatar_data);

        // Verify avatar is still returned in response
        $avatar = $response->json('profile.avatar');
        $this->assertNotNull($avatar);
        $this->assertStringStartsWith('data:image/png;base64,', $avatar);
    }

    /** @test */
    public function profile_with_large_binary_avatar_encodes_correctly(): void
    {
        // Create a larger avatar (closer to 2MB limit)
        $largeImage = imagecreatetruecolor(400, 400);
        $blue = imagecolorallocate($largeImage, 0, 0, 255);
        imagefilledrectangle($largeImage, 0, 0, 400, 400, $blue);

        ob_start();
        imagepng($largeImage, null, 0); // No compression for larger size
        $largeBinaryData = ob_get_clean();
        imagedestroy($largeImage);

        // Update profile with large avatar
        $this->salesperson->salespersonProfile->update([
            'avatar_data' => $largeBinaryData,
            'avatar_mime' => 'image/png',
        ]);

        // Should still work without 500 error
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/salesperson/profile', [
                'bio' => 'Updated with large avatar',
            ]);

        $response->assertStatus(200);

        $avatar = $response->json('profile.avatar');
        $this->assertIsString($avatar);
        $this->assertStringStartsWith('data:image/png;base64,', $avatar);

        // Verify the base64 decoding works for large data
        $base64 = substr($avatar, strlen('data:image/png;base64,'));
        $decoded = base64_decode($base64, true);
        $this->assertNotFalse($decoded);
        $this->assertEquals($largeBinaryData, $decoded, 'Decoded avatar should match original binary data');
    }
}
