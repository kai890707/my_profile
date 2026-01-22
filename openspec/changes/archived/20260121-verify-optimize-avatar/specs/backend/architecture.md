# Backend 架構設計：Avatar 服務層設計

**功能**: Avatar 處理的技術架構與服務設計
**日期**: 2026-01-21
**版本**: 1.0
**狀態**: Specification

---

## 1. 架構概述

### 1.1 設計原則

本架構遵循以下設計原則：

1. **單一職責原則 (SRP)**
   - Controller 只處理 HTTP 請求/回應
   - Service 處理業務邏輯（驗證、壓縮）
   - Repository 處理資料存取（可選）

2. **依賴反轉原則 (DIP)**
   - 高層模組（Controller）依賴抽象（Service Interface）
   - 低層模組（Service）實作抽象

3. **開閉原則 (OCP)**
   - 對擴展開放（新增其他圖片處理策略）
   - 對修改封閉（不改動現有驗證邏輯）

### 1.2 分層架構

```
┌─────────────────────────────────────────┐
│  HTTP Layer (Controller)                │
│  - 接收 HTTP 請求                        │
│  - 使用 FormRequest 驗證                 │
│  - 調用 Service                         │
│  - 回傳 JSON 回應                        │
└──────────────┬──────────────────────────┘
               │ depends on
               ▼
┌─────────────────────────────────────────┐
│  Service Layer (Business Logic)         │
│  - AvatarService: 圖片驗證與處理         │
│  - ImageProcessingService: 圖片壓縮      │
└──────────────┬──────────────────────────┘
               │ depends on
               ▼
┌─────────────────────────────────────────┐
│  Data Layer (Model)                     │
│  - SalespersonProfile: Eloquent Model   │
│  - 資料庫互動                            │
└─────────────────────────────────────────┘
```

---

## 2. 組件設計

### 2.1 Controller Layer

#### SalespersonController

**職責**: 處理 HTTP 請求，協調 Service 和 Model

**位置**: `app/Http/Controllers/Api/SalespersonController.php`

**方法簽名**:
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSalespersonProfileRequest;
use App\Services\AvatarService;
use Illuminate\Http\JsonResponse;

class SalespersonController extends Controller
{
    public function __construct(
        private readonly AvatarService $avatarService
    ) {}

    /**
     * Update salesperson profile including avatar
     *
     * PUT /api/salesperson/profile
     */
    public function updateProfile(UpdateSalespersonProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!$user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => '僅業務員可更新個人資料',
            ], 403);
        }

        $profile = $user->salespersonProfile;
        $data = $request->validated();

        // 處理 Avatar（如果提供）
        if (isset($data['avatar'])) {
            try {
                $avatarData = $this->avatarService->processAvatar($data['avatar']);
                $data = array_merge($data, $avatarData);
            } catch (\InvalidArgumentException $e) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AVATAR_VALIDATION_FAILED',
                        'message' => $e->getMessage(),
                    ],
                ], 422);
            } catch (\Exception $e) {
                Log::error('Avatar processing failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'PROCESSING_FAILED',
                        'message' => '圖片處理失敗，請稍後再試',
                    ],
                ], 500);
            }
        }

        // 更新 Profile
        $profile->update($data);

        return response()->json([
            'success' => true,
            'profile' => $profile,
            'message' => '個人資料已更新',
        ]);
    }
}
```

**設計決策**:
- ✅ 使用依賴注入（Constructor Injection）
- ✅ 使用 FormRequest 進行初步驗證
- ✅ 錯誤處理分層（Validation Error 422, Processing Error 500）
- ✅ 記錄關鍵錯誤日誌

---

### 2.2 Service Layer

#### AvatarService

**職責**: Avatar 驗證、處理和儲存邏輯

**位置**: `app/Services/AvatarService.php`（新增）

**完整實作**:
```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AvatarService
{
    // 常數定義
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private const MAX_FILE_SIZE = 2097152; // 2MB
    private const TARGET_WIDTH = 400;
    private const TARGET_HEIGHT = 400;
    private const COMPRESSION_QUALITY = 80;

    public function __construct(
        private readonly ImageProcessingService $imageProcessor
    ) {}

    /**
     * Process avatar upload
     *
     * @param string $dataUrl The data URL (e.g., data:image/jpeg;base64,...)
     * @return array{avatar_data: string, avatar_mime: string, avatar_size: int}
     * @throws \InvalidArgumentException If validation fails
     * @throws \Exception If processing fails
     */
    public function processAvatar(string $dataUrl): array
    {
        // 1. 驗證 data URL 格式
        $this->validateDataUrlFormat($dataUrl);

        // 2. 提取 MIME type 和 Base64 資料
        [$mimeType, $base64Data] = $this->parseDataUrl($dataUrl);

        // 3. 驗證 MIME type
        $this->validateMimeType($mimeType);

        // 4. 解碼 Base64
        $decoded = $this->decodeBase64($base64Data);

        // 5. 驗證檔案大小
        $this->validateFileSize($decoded);

        // 6. 驗證圖片內容
        $this->validateImageContent($decoded);

        // 7. 處理圖片（壓縮、調整大小）
        $processed = $this->imageProcessor->processImage($decoded, $mimeType);

        // 8. 回傳處理後的資料
        return [
            'avatar_data' => $processed['data'],
            'avatar_mime' => $processed['mime'],
            'avatar_size' => $processed['size'],
        ];
    }

    /**
     * Validate data URL format
     */
    private function validateDataUrlFormat(string $dataUrl): void
    {
        if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $dataUrl)) {
            throw new \InvalidArgumentException(
                '不支援的圖片格式，請選擇 JPG、PNG、WebP 或 GIF 圖片'
            );
        }
    }

    /**
     * Parse data URL to extract MIME type and Base64 data
     *
     * @return array{0: string, 1: string} [mimeType, base64Data]
     */
    private function parseDataUrl(string $dataUrl): array
    {
        if (!preg_match('/^data:(image\/[a-z]+);base64,(.+)$/', $dataUrl, $matches)) {
            throw new \InvalidArgumentException('圖片資料格式錯誤');
        }

        return [$matches[1], $matches[2]];
    }

    /**
     * Validate MIME type against whitelist
     */
    private function validateMimeType(string $mimeType): void
    {
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException(
                "不支援的圖片格式 ({$mimeType})，請選擇 JPG、PNG、WebP 或 GIF"
            );
        }
    }

    /**
     * Decode Base64 data
     */
    private function decodeBase64(string $base64Data): string
    {
        $decoded = base64_decode($base64Data, true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('圖片資料格式錯誤，無法解碼');
        }

        return $decoded;
    }

    /**
     * Validate file size
     */
    private function validateFileSize(string $data): void
    {
        $size = strlen($data);

        if ($size > self::MAX_FILE_SIZE) {
            $sizeMB = round($size / 1024 / 1024, 1);
            throw new \InvalidArgumentException(
                "圖片檔案過大（{$sizeMB} MB），請選擇小於 2MB 的圖片"
            );
        }
    }

    /**
     * Validate image content using GD
     */
    private function validateImageContent(string $data): void
    {
        try {
            $image = imagecreatefromstring($data);

            if ($image === false) {
                throw new \InvalidArgumentException(
                    '圖片檔案損壞或無效，請選擇其他圖片'
                );
            }

            // 驗證圖片尺寸
            $width = imagesx($image);
            $height = imagesy($image);

            if ($width === false || $height === false || $width === 0 || $height === 0) {
                imagedestroy($image);
                throw new \InvalidArgumentException('圖片檔案無效');
            }

            imagedestroy($image);

        } catch (\Exception $e) {
            if ($e instanceof \InvalidArgumentException) {
                throw $e;
            }

            throw new \InvalidArgumentException('圖片驗證失敗：' . $e->getMessage());
        }
    }

    /**
     * Clear avatar data
     *
     * @return array{avatar_data: null, avatar_mime: null, avatar_size: null}
     */
    public function clearAvatar(): array
    {
        return [
            'avatar_data' => null,
            'avatar_mime' => null,
            'avatar_size' => null,
        ];
    }
}
```

**設計特點**:
- ✅ 單一職責：只負責 Avatar 驗證和協調
- ✅ 依賴注入：依賴 ImageProcessingService 處理圖片
- ✅ 型別安全：使用 PHPDoc 和 strict_types
- ✅ 錯誤處理：統一使用 InvalidArgumentException
- ✅ 可測試性：所有方法都可獨立測試

#### ImageProcessingService

**職責**: 圖片壓縮、調整大小、格式轉換

**位置**: `app/Services/ImageProcessingService.php`（新增）

**完整實作**:
```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ImageProcessingService
{
    private const TARGET_WIDTH = 400;
    private const TARGET_HEIGHT = 400;
    private const COMPRESSION_QUALITY = 80;
    private const COMPRESSION_THRESHOLD = 512 * 1024; // 512KB

    /**
     * Process image: resize, compress, and optimize
     *
     * @param string $imageData Binary image data
     * @param string $mimeType Original MIME type
     * @return array{data: string, mime: string, size: int, width: int, height: int}
     * @throws \Exception If processing fails
     */
    public function processImage(string $imageData, string $mimeType): array
    {
        $originalSize = strlen($imageData);

        // 1. 建立 GD 圖片資源
        $image = imagecreatefromstring($imageData);

        if ($image === false) {
            throw new \Exception('Failed to create image resource');
        }

        try {
            // 2. 取得原始尺寸
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // 3. 計算新尺寸（如需要）
            [$newWidth, $newHeight] = $this->calculateNewDimensions(
                $originalWidth,
                $originalHeight
            );

            // 4. 調整大小（如需要）
            if ($newWidth !== $originalWidth || $newHeight !== $originalHeight) {
                $image = $this->resizeImage($image, $newWidth, $newHeight, $mimeType);
            }

            // 5. 壓縮圖片
            $compressed = $this->compressImage($image, $mimeType, $originalSize);

            // 6. 記錄處理結果
            $this->logProcessingResult(
                $originalSize,
                strlen($compressed),
                $originalWidth,
                $originalHeight,
                $newWidth,
                $newHeight
            );

            return [
                'data' => $compressed,
                'mime' => $mimeType, // 保留原始格式
                'size' => strlen($compressed),
                'width' => $newWidth,
                'height' => $newHeight,
            ];

        } finally {
            imagedestroy($image);
        }
    }

    /**
     * Calculate new dimensions while maintaining aspect ratio
     *
     * @return array{0: int, 1: int} [newWidth, newHeight]
     */
    private function calculateNewDimensions(int $width, int $height): array
    {
        if ($width <= self::TARGET_WIDTH && $height <= self::TARGET_HEIGHT) {
            return [$width, $height]; // 不需要調整
        }

        // 計算縮放比例（取最小值以確保不超出限制）
        $scale = min(
            self::TARGET_WIDTH / $width,
            self::TARGET_HEIGHT / $height
        );

        return [
            (int)round($width * $scale),
            (int)round($height * $scale),
        ];
    }

    /**
     * Resize image
     */
    private function resizeImage(
        \GdImage $image,
        int $newWidth,
        int $newHeight,
        string $mimeType
    ): \GdImage {
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($resized === false) {
            throw new \Exception('Failed to create resized image');
        }

        // 處理透明背景（PNG/GIF）
        if (in_array($mimeType, ['image/png', 'image/gif'], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // 高品質重新取樣
        imagecopyresampled(
            $resized,
            $image,
            0, 0, 0, 0,
            $newWidth,
            $newHeight,
            imagesx($image),
            imagesy($image)
        );

        return $resized;
    }

    /**
     * Compress image based on MIME type
     */
    private function compressImage(
        \GdImage $image,
        string $mimeType,
        int $originalSize
    ): string {
        ob_start();

        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($image, null, self::COMPRESSION_QUALITY);
                break;

            case 'image/png':
                // PNG compression level: 0-9 (0=no compression, 9=max)
                // 8 is a good balance between size and speed
                imagepng($image, null, 8);
                break;

            case 'image/webp':
                imagewebp($image, null, self::COMPRESSION_QUALITY);
                break;

            case 'image/gif':
                imagegif($image);
                break;

            default:
                imagejpeg($image, null, self::COMPRESSION_QUALITY);
        }

        $compressed = ob_get_clean();

        // 如果壓縮後更大（罕見），使用原始品質
        if (strlen($compressed) > $originalSize) {
            ob_start();
            imagejpeg($image, null, 90); // 提高品質
            $compressed = ob_get_clean();
        }

        return $compressed;
    }

    /**
     * Log processing result for monitoring
     */
    private function logProcessingResult(
        int $originalSize,
        int $compressedSize,
        int $originalWidth,
        int $originalHeight,
        int $newWidth,
        int $newHeight
    ): void {
        $compressionRatio = $originalSize > 0
            ? round((1 - $compressedSize / $originalSize) * 100, 2)
            : 0;

        Log::info('Avatar processed', [
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'compression_ratio' => "{$compressionRatio}%",
            'original_dimensions' => "{$originalWidth}x{$originalHeight}",
            'new_dimensions' => "{$newWidth}x{$newHeight}",
        ]);
    }
}
```

**設計特點**:
- ✅ 分離關注點：只負責圖片處理
- ✅ 保持寬高比：不裁切、不變形
- ✅ 支援透明背景：PNG/GIF 透明度保留
- ✅ 智能壓縮：根據 MIME type 選擇策略
- ✅ 監控友善：記錄處理結果

---

### 2.3 Request Layer

#### UpdateSalespersonProfileRequest

**職責**: 初步驗證請求參數（格式層級）

**位置**: `app/Http/Requests/UpdateSalespersonProfileRequest.php`

**增強後的實作**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalespersonProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 授權在 Controller 處理
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'service_regions' => ['nullable', 'array'],
            'service_regions.*' => ['string', 'max:100'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'avatar' => [
                'nullable',
                'string',
                'max:3145728', // 3MB Base64 (2MB * 1.33 + buffer)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => '請輸入業務員全名',
            'phone.required' => '請輸入聯絡電話',
            'phone.max' => '聯絡電話不得超過 20 個字元',
            'bio.max' => '個人簡介不得超過 1000 個字元',
            'specialties.max' => '專長領域不得超過 500 個字元',
            'service_regions.array' => '服務區域格式錯誤',
            'company_id.exists' => '選擇的公司不存在',
            'avatar.string' => 'Avatar 格式錯誤',
            'avatar.max' => 'Avatar 檔案過大',
        ];
    }
}
```

**設計決策**:
- ✅ 只做基礎驗證（格式、長度）
- ✅ 深度驗證（內容、安全性）留給 Service
- ✅ 避免在 FormRequest 中驗證 Base64 內容（太重）

---

## 3. 流程設計

### 3.1 Avatar 更新流程圖

```
┌──────────────────────────────────────────────────────┐
│ 1. Frontend 發送 PUT /api/salesperson/profile       │
│    Body: { avatar: "data:image/jpeg;base64,..." }   │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│ 2. UpdateSalespersonProfileRequest 驗證              │
│    - 基礎格式驗證                                     │
│    - 長度限制檢查                                     │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│ 3. SalespersonController::updateProfile()           │
│    - 檢查使用者權限                                   │
│    - 調用 AvatarService                              │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│ 4. AvatarService::processAvatar()                   │
│    ├─ 驗證 data URL 格式                             │
│    ├─ 驗證 MIME type 白名單                          │
│    ├─ Base64 解碼                                    │
│    ├─ 驗證檔案大小 (< 2MB)                           │
│    └─ 驗證圖片內容 (GD)                              │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│ 5. ImageProcessingService::processImage()           │
│    ├─ 計算新尺寸 (保持寬高比)                         │
│    ├─ 調整圖片大小 (如需要)                           │
│    ├─ 壓縮圖片 (JPEG: 80%, PNG: level 8)            │
│    └─ 記錄處理結果                                    │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│ 6. Controller 更新 SalespersonProfile               │
│    - avatar_data = 二進制資料                         │
│    - avatar_mime = MIME type                         │
│    - avatar_size = 檔案大小                           │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│ 7. 回傳 JSON 回應                                    │
│    { success: true, profile: {...} }                │
└──────────────────────────────────────────────────────┘
```

### 3.2 錯誤處理流程

```
任何階段發生錯誤
     │
     ├─ InvalidArgumentException (驗證失敗)
     │  └─> 422 Unprocessable Entity
     │      { error: { code: "VALIDATION_ERROR", message: "..." } }
     │
     ├─ Exception (處理失敗)
     │  └─> 500 Internal Server Error
     │      { error: { code: "PROCESSING_FAILED", message: "..." } }
     │
     └─ 權限檢查失敗
        └─> 403 Forbidden
            { error: "僅業務員可更新個人資料" }
```

---

## 4. 依賴注入配置

### 4.1 Service Provider 註冊

**位置**: `app/Providers/AppServiceProvider.php`

```php
<?php

namespace App\Providers;

use App\Services\AvatarService;
use App\Services\ImageProcessingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 註冊 Service 為 Singleton（效能優化）
        $this->app->singleton(ImageProcessingService::class);
        $this->app->singleton(AvatarService::class);
    }
}
```

**為什麼使用 Singleton?**
- Service 無狀態，可重複使用
- 避免重複實例化（效能優化）
- 單元測試時可輕易 Mock

---

## 5. 測試架構

### 5.1 測試金字塔

```
        ┌──────────┐
        │   E2E    │  10% - Playwright (Frontend)
        │  Tests   │
        └──────────┘
       ┌────────────┐
       │  Feature   │  30% - 測試 API 端點
       │   Tests    │
       └────────────┘
      ┌──────────────┐
      │     Unit     │  60% - 測試 Service 邏輯
      │    Tests     │
      └──────────────┘
```

### 5.2 單元測試結構

**檔案**: `tests/Unit/Services/AvatarServiceTest.php`

```php
<?php

use App\Services\AvatarService;
use App\Services\ImageProcessingService;

describe('AvatarService', function () {
    beforeEach(function () {
        $this->imageProcessor = Mockery::mock(ImageProcessingService::class);
        $this->service = new AvatarService($this->imageProcessor);
    });

    describe('Data URL Validation', function () {
        it('accepts valid JPEG data URL', function () {
            $dataUrl = 'data:image/jpeg;base64,' . base64_encode('fake jpeg');
            // ... test logic
        });

        it('rejects invalid format', function () {
            expect(fn() => $this->service->processAvatar('invalid'))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('MIME Type Validation', function () {
        // ... tests
    });

    describe('File Size Validation', function () {
        // ... tests
    });
});
```

### 5.3 Feature 測試結構

**檔案**: `tests/Feature/SalespersonAvatarTest.php`

```php
<?php

use App\Models\User;
use App\Models\SalespersonProfile;

describe('Salesperson Avatar API', function () {
    beforeEach(function () {
        $this->salesperson = User::factory()->salesperson()->create();
        $this->profile = SalespersonProfile::factory()->create([
            'user_id' => $this->salesperson->id,
        ]);
    });

    it('allows salesperson to update avatar', function () {
        $avatar = generateValidAvatar(); // Helper function

        $response = $this->actingAs($this->salesperson)
            ->putJson('/api/salesperson/profile', [
                'avatar' => $avatar,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('salesperson_profiles', [
            'id' => $this->profile->id,
        ]);

        $profile = $this->profile->fresh();
        expect($profile->avatar_data)->not->toBeNull();
        expect($profile->avatar_mime)->toBe('image/jpeg');
    });

    it('rejects oversized avatar', function () {
        $largeAvatar = generateLargeAvatar(3 * 1024 * 1024); // 3MB

        $response = $this->actingAs($this->salesperson)
            ->putJson('/api/salesperson/profile', [
                'avatar' => $largeAvatar,
            ]);

        $response->assertStatus(422);
    });
});
```

---

## 6. 效能優化策略

### 6.1 記憶體優化

**問題**: 處理大圖片可能消耗大量記憶體

**解決方案**:
```php
// 在處理前釋放舊資源
if (isset($oldImage)) {
    imagedestroy($oldImage);
}

// 處理完立即釋放
try {
    $result = $this->processImage($image);
    return $result;
} finally {
    imagedestroy($image); // 確保釋放
}

// PHP 配置
ini_set('memory_limit', '256M'); // 臨時提高限制
```

### 6.2 並發優化

**策略**: 使用 Queue 處理大量上傳（未來可選）

```php
// 如果未來需要非同步處理
dispatch(new ProcessAvatarJob($profile, $avatarData));

// Job
class ProcessAvatarJob implements ShouldQueue
{
    public function handle(AvatarService $service): void
    {
        $processed = $service->processAvatar($this->avatarData);
        $this->profile->update($processed);
    }
}
```

**目前**: 同步處理足夠（< 500ms）

---

## 7. 監控與日誌

### 7.1 關鍵指標監控

**監控內容**:
```php
// 處理時間監控
$start = microtime(true);
$result = $service->processAvatar($avatar);
$duration = (microtime(true) - $start) * 1000;

Log::info('Avatar processing completed', [
    'duration_ms' => $duration,
    'original_size' => $originalSize,
    'compressed_size' => $result['size'],
    'user_id' => $user->id,
]);

// 告警條件
if ($duration > 1000) { // > 1s
    Log::warning('Slow avatar processing', [
        'duration_ms' => $duration,
        'user_id' => $user->id,
    ]);
}
```

### 7.2 錯誤日誌

**日誌等級**:
```php
// INFO: 正常處理
Log::info('Avatar uploaded successfully', [...]);

// WARNING: 驗證失敗（預期內）
Log::warning('Avatar validation failed', [
    'error' => 'File too large',
    'user_id' => $user->id,
]);

// ERROR: 處理失敗（非預期）
Log::error('Avatar processing failed', [
    'error' => $e->getMessage(),
    'stack_trace' => $e->getTraceAsString(),
    'user_id' => $user->id,
]);
```

---

## 8. 未來擴展設計

### 8.1 策略模式（未來可選）

**場景**: 如果需要支援多種圖片處理策略

```php
interface ImageProcessingStrategy
{
    public function process(string $imageData, string $mimeType): array;
}

class GDImageProcessor implements ImageProcessingStrategy { /* ... */ }
class ImagickImageProcessor implements ImageProcessingStrategy { /* ... */ }

// Service 依賴抽象
class ImageProcessingService
{
    public function __construct(
        private readonly ImageProcessingStrategy $strategy
    ) {}
}
```

### 8.2 事件驅動（未來可選）

**場景**: 如果需要在 Avatar 更新後觸發其他操作

```php
// 發布事件
event(new AvatarUpdated($profile));

// 監聽器
class UpdateUserAvatarCache
{
    public function handle(AvatarUpdated $event): void
    {
        Cache::put("avatar:{$event->profile->user_id}", $event->profile->avatar);
    }
}
```

---

## 9. 驗收標準

### 9.1 程式碼品質

- [ ] PHPStan Level 9 通過
- [ ] 所有方法有型別宣告
- [ ] 所有 public 方法有 PHPDoc
- [ ] 複雜度 <= 10 (每個方法)

### 9.2 測試覆蓋

- [ ] AvatarService 單元測試覆蓋率 >= 95%
- [ ] ImageProcessingService 單元測試覆蓋率 >= 90%
- [ ] Feature 測試覆蓋所有 API 端點

### 9.3 效能標準

- [ ] Avatar 處理時間 < 400ms (P95)
- [ ] 記憶體使用 < 128MB (單次請求)
- [ ] 並發處理 >= 20 req/s

---

**撰寫日期**: 2026-01-21
**作者**: Senior Software Architect
**審核者**: Tech Lead
**版本**: 1.0
