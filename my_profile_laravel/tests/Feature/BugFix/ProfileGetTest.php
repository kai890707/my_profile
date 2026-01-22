<?php

declare(strict_types=1);

namespace Tests\Feature\BugFix;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Fix GET /api/salesperson/profile 500 Error
 *
 * Bug: GET endpoints returning 500 error with binary avatar
 * Fix: Use formatProfile() helper in SalespersonProfileController
 */
class ProfileGetTest extends TestCase
{
    use RefreshDatabase;

    protected User $salesperson;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create approved salesperson with avatar
        $this->salesperson = User::factory()->create([
            'role' => User::ROLE_SALESPERSON,
            'salesperson_status' => User::STATUS_APPROVED,
        ]);

        // Create profile with binary avatar data
        $image = imagecreatetruecolor(100, 100);
        ob_start();
        imagepng($image, null, 8);
        $binaryData = ob_get_clean();
        imagedestroy($image);

        $this->salesperson->salespersonProfile()->create([
            'full_name' => 'Test User',
            'bio' => 'Test bio',
            'avatar_data' => $binaryData,
            'avatar_mime' => 'image/png',
        ]);

        $this->token = auth()->login($this->salesperson);
    }

    /** @test */
    public function get_profile_does_not_cause_500_error_with_avatar(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/salesperson/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'profile' => [
                        'id',
                        'full_name',
                        'avatar',
                    ],
                ],
            ]);

        // Verify avatar is a valid data URL
        $avatar = $response->json('data.profile.avatar');
        $this->assertIsString($avatar);
        $this->assertStringStartsWith('data:image/png;base64,', $avatar);
    }

    /** @test */
    public function profile_with_large_binary_avatar(): void
    {
        // Create a larger avatar
        $largeImage = imagecreatetruecolor(400, 400);
        ob_start();
        imagepng($largeImage, null, 0); // No compression
        $largeBinaryData = ob_get_clean();
        imagedestroy($largeImage);

        // Update profile with large avatar
        $this->salesperson->salespersonProfile->update([
            'avatar_data' => $largeBinaryData,
        ]);

        // Should work without 500 error
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/salesperson/profile');

        $response->assertStatus(200);

        $avatar = $response->json('data.profile.avatar');
        $this->assertIsString($avatar);
        $this->assertStringStartsWith('data:image/png;base64,', $avatar);
    }

    /** @test */
    public function profile_without_avatar_returns_null(): void
    {
        // Remove avatar
        $this->salesperson->salespersonProfile->update([
            'avatar_data' => null,
            'avatar_mime' => null,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/salesperson/profile');

        $response->assertStatus(200);

        $avatar = $response->json('data.profile.avatar');
        $this->assertNull($avatar);
    }
}
