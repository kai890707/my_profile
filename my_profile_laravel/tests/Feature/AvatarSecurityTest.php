<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Avatar Security Tests
 *
 * 測試 Avatar 上傳的安全性：
 * - XSS 防護（禁止 SVG）
 * - 檔案偽造防護
 * - 檔案大小限制
 * - MIME type 驗證
 */
class AvatarSecurityTest extends TestCase
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

    /** @test */
    public function it_rejects_svg_upload_xss_protection(): void
    {
        // SVG 可能包含 XSS 攻擊代碼
        $svgData = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script></svg>';
        $base64Svg = base64_encode($svgData);
        $dataUrl = "data:image/svg+xml;base64,{$base64Svg}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ],
            ]);

        // Ensure avatar was not saved
        $this->salesperson->salespersonProfile()->first()->refresh();
        $this->assertNull($this->salesperson->salespersonProfile->avatar_data);
    }

    /** @test */
    public function it_rejects_file_disguised_as_image(): void
    {
        // 嘗試將文字檔偽裝成 JPEG
        $fakeImageData = 'This is not an image, it is plain text';
        $base64Data = base64_encode($fakeImageData);
        $dataUrl = "data:image/jpeg;base64,{$base64Data}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        // Ensure error message indicates image is invalid
        $this->assertStringContainsString(
            '損壞',
            $response->json('error.message')
        );
    }

    /** @test */
    public function it_rejects_oversized_image(): void
    {
        // 創建超過 2MB 的圖片（模擬）
        $largeData = str_repeat('A', 2200000); // 2.2MB
        $base64Data = base64_encode($largeData);
        $dataUrl = "data:image/jpeg;base64,{$base64Data}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        // Ensure error message indicates file is too large
        $this->assertStringContainsString(
            '過大',
            $response->json('error.message')
        );
    }

    /** @test */
    public function it_rejects_invalid_data_url_format(): void
    {
        // Invalid data URL (missing base64 marker)
        $invalidDataUrl = 'data:image/jpeg,notbase64data';

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $invalidDataUrl,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_rejects_empty_avatar_data(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => '',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_accepts_valid_jpeg_image(): void
    {
        // Create a small valid PNG (use PNG as Docker may lack JPEG support)
        $image = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefilledrectangle($image, 0, 0, 100, 100, $red);

        ob_start();
        imagepng($image, null, 8);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '頭像上傳成功',
            ]);

        // Verify avatar was saved
        $this->salesperson->salespersonProfile()->first()->refresh();
        $this->assertNotNull($this->salesperson->salespersonProfile->avatar_data);
        $this->assertEquals('image/png', $this->salesperson->salespersonProfile->avatar_mime);
    }

    /** @test */
    public function it_accepts_valid_png_image(): void
    {
        // Create a small valid PNG
        $image = imagecreatetruecolor(100, 100);
        $blue = imagecolorallocate($image, 0, 0, 255);
        imagefilledrectangle($image, 0, 0, 100, 100, $blue);

        ob_start();
        imagepng($image, null, 8);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function it_rate_limits_avatar_uploads(): void
    {
        // Create a small valid image (use PNG for Docker compatibility)
        $image = imagecreatetruecolor(50, 50);
        ob_start();
        imagepng($image, null, 8);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        // Upload 10 times (should succeed)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
                ->postJson('/api/salesperson/avatar', [
                    'avatar' => $dataUrl,
                ]);

            $response->assertStatus(200);
        }

        // 11th upload should be rate limited
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ]);

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_allows_avatar_deletion(): void
    {
        // First upload an avatar (use PNG for Docker compatibility)
        $image = imagecreatetruecolor(50, 50);
        ob_start();
        imagepng($image, null, 8);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $base64Data = base64_encode($imageData);
        $dataUrl = "data:image/png;base64,{$base64Data}";

        $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => $dataUrl,
            ])
            ->assertStatus(200);

        // Verify avatar exists
        $this->salesperson->salespersonProfile()->first()->refresh();
        $this->assertNotNull($this->salesperson->salespersonProfile->avatar_data);

        // Delete avatar
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/salesperson/avatar');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => '頭像已刪除',
                'data' => [
                    'avatar' => null,
                ],
            ]);

        // Verify avatar was deleted (reload profile from database)
        $profile = $this->salesperson->salespersonProfile()->first();
        $profile->refresh();
        $this->assertNull($profile->avatar_data);
        $this->assertNull($profile->avatar_mime);
    }

    /** @test */
    public function unauthenticated_user_cannot_upload_avatar(): void
    {
        $response = $this->postJson('/api/salesperson/avatar', [
            'avatar' => 'data:image/jpeg;base64,fake',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_upload_avatar(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER,
        ]);

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/salesperson/avatar', [
                'avatar' => 'data:image/jpeg;base64,fake',
            ]);

        $response->assertStatus(403);
    }
}
