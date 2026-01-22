# Backend 業務規則規格：Avatar 驗證與處理

**功能**: Avatar 業務邏輯與驗證規則
**日期**: 2026-01-21
**版本**: 1.0
**狀態**: Specification

---

## 1. 概述

本文件定義 Avatar 功能的所有業務規則，包括驗證規則、處理邏輯、權限控制和邊界情況處理。

---

## 2. 驗證規則 (Validation Rules)

### 2.1 Avatar 格式驗證

#### VR-001: Data URL 格式驗證

**規則描述**: Avatar 必須是有效的 data URL 格式

**格式定義**:
```
data:{mime_type};base64,{base64_encoded_data}
```

**正則表達式**:
```regex
/^data:image\/(jpeg|png|webp|gif);base64,[A-Za-z0-9+\/]+=*$/
```

**驗證邏輯**:
```php
if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $avatar)) {
    return '不支援的圖片格式，請選擇 JPG、PNG、WebP 或 GIF 圖片';
}
```

**錯誤情況**:

| 輸入 | 結果 | 錯誤訊息 |
|-----|------|---------|
| `data:image/jpeg;base64,/9j/...` | ✅ 通過 | - |
| `data:image/svg+xml;base64,...` | ❌ 失敗 | 不支援的圖片格式 |
| `data:application/pdf;base64,...` | ❌ 失敗 | 不支援的圖片格式 |
| `http://example.com/avatar.jpg` | ❌ 失敗 | 不支援的圖片格式 |
| `/9j/4AAQSkZJRg...` (無 data: 前綴) | ❌ 失敗 | 不支援的圖片格式 |

#### VR-002: MIME Type 白名單驗證

**規則描述**: 只接受特定的圖片格式

**白名單**:
```php
const ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif',
];
```

**驗證邏輯**:
```php
preg_match('/^data:(image\/[a-z]+);base64,/', $avatar, $matches);
$mimeType = $matches[1] ?? null;

if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
    return "不支援的圖片格式 ({$mimeType})，請選擇 JPG、PNG、WebP 或 GIF";
}
```

**為什麼禁止 SVG?**
- ⚠️ **安全風險**: SVG 可包含 JavaScript，存在 XSS 攻擊風險
- ⚠️ **不是點陣圖**: SVG 是向量圖，不符合 Avatar 使用場景
- ⚠️ **難以驗證內容**: 無法使用 GD/Imagick 驗證

**測試案例**:
```php
// tests/Unit/AvatarValidationTest.php

it('accepts JPEG images', function () {
    $avatar = 'data:image/jpeg;base64,' . base64_encode('fake jpeg');
    expect(isValidMimeType($avatar))->toBeTrue();
});

it('rejects SVG images', function () {
    $avatar = 'data:image/svg+xml;base64,' . base64_encode('<svg></svg>');
    expect(isValidMimeType($avatar))->toBeFalse();
});
```

#### VR-003: Base64 編碼驗證

**規則描述**: Base64 資料必須可正確解碼

**驗證邏輯**:
```php
// 提取 Base64 部分
$base64Data = explode(',', $avatar)[1] ?? null;

if ($base64Data === null) {
    return '圖片資料格式錯誤';
}

// 嘗試解碼
$decoded = base64_decode($base64Data, true); // strict mode

if ($decoded === false) {
    return '圖片資料格式錯誤，無法解碼';
}
```

**為什麼使用 strict mode?**
- 嚴格驗證 Base64 格式
- 防止部分解碼成功但資料損壞

**錯誤情況**:

| Base64 資料 | 結果 | 原因 |
|------------|------|------|
| `/9j/4AAQSkZJRg==` | ✅ 通過 | 有效的 Base64 |
| `!!!invalid!!!` | ❌ 失敗 | 非 Base64 字元 |
| `data:image/jpeg` (無逗號) | ❌ 失敗 | 格式錯誤 |
| 空字串 | ❌ 失敗 | 缺少資料 |

#### VR-004: 檔案大小驗證

**規則描述**: 解碼後的檔案大小不得超過 2MB

**驗證邏輯**:
```php
const MAX_FILE_SIZE = 2097152; // 2MB in bytes

$fileSize = strlen($decoded);

if ($fileSize > self::MAX_FILE_SIZE) {
    $sizeMB = round($fileSize / 1024 / 1024, 1);
    return "圖片檔案過大（{$sizeMB} MB），請選擇小於 2MB 的圖片";
}
```

**為什麼限制 2MB?**
1. **資料庫效能**: MEDIUMBLOB 可容納 16MB，但 2MB 是合理的 Avatar 大小
2. **網路傳輸**: 2MB 的 Base64 編碼後約 2.7MB，在 4G 網路下約 3-5 秒
3. **記憶體使用**: 處理 2MB 圖片需要約 8-10MB 記憶體（含壓縮）

**檔案大小估算**:
```
原始圖片大小 → Base64 編碼 (約 133%) → 網路傳輸大小

範例：
- 1.5 MB JPEG → 2.0 MB Base64 → 2.0 MB 傳輸
- 2.0 MB PNG → 2.7 MB Base64 → 2.7 MB 傳輸
- 3.0 MB BMP → 4.0 MB Base64 → 4.0 MB 傳輸 ❌ 超過限制
```

**測試案例**:
```php
it('accepts files under 2MB', function () {
    $image = generateTestImage(1.5 * 1024 * 1024); // 1.5MB
    $avatar = 'data:image/jpeg;base64,' . base64_encode($image);

    expect(isValidFileSize($avatar))->toBeTrue();
});

it('rejects files over 2MB', function () {
    $image = generateTestImage(3 * 1024 * 1024); // 3MB
    $avatar = 'data:image/jpeg;base64,' . base64_encode($image);

    expect(isValidFileSize($avatar))->toBeFalse();
});
```

#### VR-005: 圖片內容驗證

**規則描述**: 必須是有效的圖片檔案（防止檔案偽造）

**驗證邏輯**:
```php
try {
    // 使用 GD 嘗試解析圖片
    $image = imagecreatefromstring($decoded);

    if ($image === false) {
        return '圖片檔案損壞或無效，請選擇其他圖片';
    }

    // 取得圖片尺寸（順便驗證是否為有效圖片）
    $width = imagesx($image);
    $height = imagesy($image);

    if ($width === false || $height === false || $width === 0 || $height === 0) {
        imagedestroy($image);
        return '圖片檔案無效';
    }

    // 清理資源
    imagedestroy($image);

} catch (\Exception $e) {
    return '圖片驗證失敗：' . $e->getMessage();
}
```

**為什麼需要內容驗證?**
1. **防止檔案偽造**: 惡意使用者可能將 .txt 檔案改名為 .jpg
2. **確保可用性**: 損壞的圖片無法顯示
3. **安全性**: GD 重新編碼可清除可能的惡意程式碼

**測試案例**:
```php
it('accepts valid JPEG images', function () {
    $validJpeg = file_get_contents(__DIR__ . '/fixtures/avatar.jpg');
    $avatar = 'data:image/jpeg;base64,' . base64_encode($validJpeg);

    expect(isValidImageContent($avatar))->toBeTrue();
});

it('rejects text files disguised as images', function () {
    $fakeImage = 'This is a text file, not an image';
    $avatar = 'data:image/jpeg;base64,' . base64_encode($fakeImage);

    expect(isValidImageContent($avatar))->toBeFalse();
});

it('rejects corrupted images', function () {
    $corruptedJpeg = "\xFF\xD8\xFF\xE0" . random_bytes(100); // Invalid JPEG
    $avatar = 'data:image/jpeg;base64,' . base64_encode($corruptedJpeg);

    expect(isValidImageContent($avatar))->toBeFalse();
});
```

---

## 3. 業務邏輯規則 (Business Logic Rules)

### 3.1 圖片處理規則

#### BL-001: 圖片壓縮規則

**觸發條件**: 圖片尺寸超過 400x400 或檔案大小超過 500KB

**壓縮策略**:

**策略 1: 尺寸壓縮**
```php
const TARGET_WIDTH = 400;
const TARGET_HEIGHT = 400;

if ($width > self::TARGET_WIDTH || $height > self::TARGET_HEIGHT) {
    // 計算縮放比例（保持寬高比）
    $scale = min(
        self::TARGET_WIDTH / $width,
        self::TARGET_HEIGHT / $height
    );

    $newWidth = (int)($width * $scale);
    $newHeight = (int)($height * $scale);

    // 建立新圖片
    $resized = imagecreatetruecolor($newWidth, $newHeight);

    // 處理透明背景（PNG/GIF）
    if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // 重新取樣（高品質縮放）
    imagecopyresampled(
        $resized, $image,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $width, $height
    );
}
```

**策略 2: 品質壓縮**
```php
const QUALITY = 80; // 80% 品質

// 轉換為 JPEG（最佳壓縮效果）
ob_start();
imagejpeg($resized, null, self::QUALITY);
$compressed = ob_get_clean();

// 如果壓縮後仍過大，降低品質
if (strlen($compressed) > 500 * 1024) { // > 500KB
    ob_start();
    imagejpeg($resized, null, 70); // 降低到 70%
    $compressed = ob_get_clean();
}
```

**壓縮結果記錄**:
```php
Log::info('Avatar compressed', [
    'profile_id' => $profile->id,
    'original_size' => $originalSize,
    'compressed_size' => strlen($compressed),
    'compression_ratio' => round((1 - strlen($compressed) / $originalSize) * 100, 2) . '%',
    'original_dimensions' => "{$width}x{$height}",
    'new_dimensions' => "{$newWidth}x{$newHeight}",
]);
```

**預期壓縮效果**:

| 原始圖片 | 壓縮後 | 壓縮率 | 尺寸 |
|---------|--------|--------|------|
| 2MB, 2048x1536 | 300KB | 85% | 400x300 |
| 1.5MB, 1200x900 | 250KB | 83% | 400x300 |
| 800KB, 800x600 | 200KB | 75% | 400x300 |
| 500KB, 500x500 | 180KB | 64% | 400x400 |

#### BL-002: 格式統一規則

**規則描述**: 所有 Avatar 統一轉換為 JPEG 格式（可選）

**理由**:
1. ✅ JPEG 壓縮率最好
2. ✅ 瀏覽器相容性 100%
3. ⚠️ 但會失去透明背景（PNG/GIF 特性）

**實作方式**:
```php
// 選項 1: 統一轉換為 JPEG（簡化管理）
$mimeType = 'image/jpeg';
ob_start();
imagejpeg($image, null, 80);
$output = ob_get_clean();

// 選項 2: 保留原始格式（保留特性）
switch ($mimeType) {
    case 'image/png':
        imagepng($image, null, 8); // 壓縮等級 0-9
        break;
    case 'image/webp':
        imagewebp($image, null, 80);
        break;
    case 'image/gif':
        imagegif($image);
        break;
    default: // jpeg
        imagejpeg($image, null, 80);
}
```

**決策**: 保留原始格式（選項 2）
- 原因：尊重使用者選擇，保留透明背景特性
- 權衡：增加少量複雜度，但使用者體驗更好

#### BL-003: 圖片方向校正 (EXIF Orientation)

**問題**: 某些手機拍攝的照片包含 EXIF 方向資訊，可能導致顯示時旋轉錯誤

**解決方案**:
```php
// 讀取 EXIF 方向
$exif = exif_read_data($decoded);
$orientation = $exif['Orientation'] ?? 1;

// 根據方向旋轉圖片
switch ($orientation) {
    case 3:
        $image = imagerotate($image, 180, 0);
        break;
    case 6:
        $image = imagerotate($image, -90, 0);
        break;
    case 8:
        $image = imagerotate($image, 90, 0);
        break;
}
```

**注意**: 需要 PHP EXIF 擴展

### 3.2 權限規則

#### BL-004: 更新權限規則

**規則描述**: 只有業務員可以更新自己的 Avatar

**檢查邏輯**:
```php
// 1. 檢查是否已認證
if (!auth()->check()) {
    return response()->json([
        'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Authentication required']
    ], 401);
}

// 2. 檢查是否為業務員
$user = auth()->user();
if (!$user->isSalesperson()) {
    return response()->json([
        'error' => '僅業務員可更新個人資料'
    ], 403);
}

// 3. 確認更新的是自己的 Profile
$profile = $user->salespersonProfile;
if (!$profile) {
    return response()->json([
        'error' => '業務員個人資料不存在'
    ], 404);
}
```

**權限矩陣**:

| 角色 | 檢視 Avatar | 更新自己的 Avatar | 更新他人的 Avatar |
|------|-----------|-----------------|-----------------|
| 一般使用者 | ✅（搜尋頁面） | N/A | ❌ |
| 業務員 | ✅ | ✅ | ❌ |
| 管理員 | ✅ | ✅ | ⚠️（未實作，Out of Scope） |

#### BL-005: Rate Limiting 規則

**規則描述**: 限制 Avatar 更新頻率，防止濫用

**限制**:
```php
// routes/api.php
Route::middleware(['auth:api', 'throttle:10,1']) // 10 次/分鐘
    ->put('/salesperson/profile', [SalespersonController::class, 'updateProfile']);
```

**為什麼需要 Rate Limiting?**
1. 防止自動化腳本濫用
2. 減少伺服器負載（圖片處理消耗資源）
3. 防止惡意攻擊（如 DoS）

**回應**:
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "請求過於頻繁，請稍後再試",
    "retry_after": 45
  }
}
```

### 3.3 資料一致性規則

#### BL-006: Avatar 欄位原子性更新

**規則描述**: avatar_data, avatar_mime, avatar_size 必須同時更新或同時為 NULL

**實作**:
```php
// app/Models/SalespersonProfile.php

protected static function booted(): void
{
    static::saving(function (SalespersonProfile $profile) {
        // 檢查 avatar 欄位一致性
        $hasData = $profile->avatar_data !== null;
        $hasMime = $profile->avatar_mime !== null;
        $hasSize = $profile->avatar_size !== null;

        if ($hasData !== $hasMime || $hasMime !== $hasSize) {
            throw new \InvalidArgumentException(
                'avatar_data, avatar_mime, avatar_size must all be set or all be null'
            );
        }
    });
}
```

**測試案例**:
```php
it('ensures avatar fields consistency', function () {
    $profile = SalespersonProfile::factory()->create();

    // ❌ 不允許只設定 avatar_data
    expect(fn() => $profile->update([
        'avatar_data' => 'binary data',
        'avatar_mime' => null,
        'avatar_size' => null,
    ]))->toThrow(\InvalidArgumentException::class);

    // ✅ 允許同時設定所有欄位
    $profile->update([
        'avatar_data' => 'binary data',
        'avatar_mime' => 'image/jpeg',
        'avatar_size' => 123456,
    ]);

    expect($profile->avatar_data)->toBe('binary data');
});
```

#### BL-007: 清除 Avatar 規則

**規則描述**: 當使用者刪除 Avatar 時，清除所有相關欄位

**實作**:
```php
// 清除 Avatar
$profile->update([
    'avatar_data' => null,
    'avatar_mime' => null,
    'avatar_size' => null,
]);

// 或使用 Helper Method
public function clearAvatar(): void
{
    $this->update([
        'avatar_data' => null,
        'avatar_mime' => null,
        'avatar_size' => null,
    ]);
}
```

---

## 4. 邊界情況處理 (Edge Cases)

### 4.1 特殊圖片格式處理

#### EC-001: 動畫 GIF 處理

**情況**: 使用者上傳動畫 GIF

**處理方式**: 接受並保留第一幀
```php
// GD 會自動只保留第一幀
$image = imagecreatefromstring($decoded); // 只有第一幀
imagegif($image); // 輸出靜態 GIF
```

**使用者體驗**: 提示「動畫 GIF 將轉換為靜態圖片」（可選）

#### EC-002: 透明背景 PNG 處理

**情況**: PNG 圖片有透明背景

**處理方式**: 保留透明度
```php
if ($mimeType === 'image/png') {
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
    imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
}

imagepng($resized, null, 8); // 保留透明度
```

#### EC-003: 極小圖片處理

**情況**: 使用者上傳非常小的圖片（如 50x50）

**處理方式**: 不放大，保持原始尺寸
```php
if ($width < self::TARGET_WIDTH && $height < self::TARGET_HEIGHT) {
    // 不需要壓縮，直接使用原圖
    return $originalImage;
}
```

**原因**: 放大會導致模糊

#### EC-004: 非正方形圖片處理

**情況**: 使用者上傳長方形圖片（如 1600x900）

**處理方式**: 保持寬高比，不裁切
```php
// 計算縮放比例（取最小值以確保不超出限制）
$scale = min(
    self::TARGET_WIDTH / $width,
    self::TARGET_HEIGHT / $height
);

$newWidth = (int)($width * $scale);
$newHeight = (int)($height * $scale);

// 結果: 1600x900 → 400x225 (保持 16:9 比例)
```

**Frontend 顯示**: 使用 `object-fit: cover` 或 `object-fit: contain` 確保顯示正常

### 4.2 錯誤恢復處理

#### EC-005: 圖片處理失敗

**情況**: imagecreatefromstring() 或壓縮過程失敗

**處理方式**:
```php
try {
    $image = imagecreatefromstring($decoded);

    if ($image === false) {
        throw new \Exception('Failed to create image from string');
    }

    // 處理圖片...
    $compressed = $this->compressImage($image);

    imagedestroy($image);

    return $compressed;

} catch (\Exception $e) {
    Log::error('Avatar processing failed', [
        'error' => $e->getMessage(),
        'user_id' => $user->id,
        'file_size' => strlen($decoded),
    ]);

    return response()->json([
        'success' => false,
        'error' => [
            'code' => 'PROCESSING_FAILED',
            'message' => '圖片處理失敗，請稍後再試',
        ]
    ], 500);
}
```

#### EC-006: 記憶體不足處理

**情況**: 處理大圖片時記憶體不足

**預防措施**:
```php
// php.ini 配置
memory_limit = 256M // 確保足夠記憶體
max_execution_time = 30 // 限制執行時間
```

**錯誤處理**:
```php
try {
    ini_set('memory_limit', '256M'); // 臨時提高限制

    $image = imagecreatefromstring($decoded);

    // ... 處理邏輯

} catch (\Error $e) {
    if (str_contains($e->getMessage(), 'Allowed memory size')) {
        return response()->json([
            'error' => [
                'code' => 'MEMORY_EXCEEDED',
                'message' => '圖片過大，無法處理，請壓縮後再試',
            ]
        ], 413);
    }

    throw $e; // 其他錯誤重新拋出
}
```

### 4.3 相容性處理

#### EC-007: 舊資料相容性

**情況**: 現有資料庫中的 Avatar 可能未經嚴格驗證

**處理方式**: 讀取時容錯，更新時驗證
```php
// 讀取現有資料（不驗證）
public function getAvatarAttribute(): ?string
{
    if ($this->avatar_data && $this->avatar_mime) {
        return "data:{$this->avatar_mime};base64," . base64_encode($this->avatar_data);
    }
    return null;
}

// 更新時套用新驗證規則
public function updateAvatar(string $dataUrl): void
{
    // 執行所有驗證規則 (VR-001 ~ VR-005)
    $this->validateAvatar($dataUrl);

    // 處理並儲存
    // ...
}
```

#### EC-008: 不同瀏覽器相容性

**情況**: 不同瀏覽器上傳的 data URL 格式可能有微小差異

**處理方式**: 寬鬆解析，嚴格驗證
```php
// 移除可能的換行符和空白
$avatar = preg_replace('/\s+/', '', $avatar);

// 驗證格式
if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,/', $avatar)) {
    return '格式錯誤';
}
```

---

## 5. 測試需求

### 5.1 單元測試

**檔案**: `tests/Unit/Services/AvatarValidationServiceTest.php`

**測試案例**:
```php
describe('Avatar Validation', function () {
    it('validates data URL format', function () {
        // VR-001
    });

    it('validates MIME type whitelist', function () {
        // VR-002
    });

    it('validates Base64 encoding', function () {
        // VR-003
    });

    it('validates file size limit', function () {
        // VR-004
    });

    it('validates image content', function () {
        // VR-005
    });
});

describe('Image Processing', function () {
    it('compresses large images', function () {
        // BL-001
    });

    it('preserves aspect ratio', function () {
        // BL-001
    });

    it('handles transparent PNG', function () {
        // EC-002
    });

    it('handles animated GIF', function () {
        // EC-001
    });
});
```

### 5.2 Feature 測試

**檔案**: `tests/Feature/SalespersonAvatarTest.php`

**測試覆蓋率目標**: >= 95%

**關鍵測試案例**:
- [ ] 上傳有效 Avatar（JPEG, PNG, WebP, GIF）
- [ ] 拒絕無效格式（SVG, PDF）
- [ ] 拒絕超大檔案（> 2MB）
- [ ] 拒絕損壞圖片
- [ ] 拒絕偽造圖片
- [ ] 圖片自動壓縮
- [ ] 權限檢查（非業務員無法更新）
- [ ] Rate Limiting 驗證
- [ ] 清除 Avatar
- [ ] 資料一致性驗證

---

## 6. 驗收標準

### 6.1 功能驗收

- [ ] 所有驗證規則 (VR-001 ~ VR-005) 實作並測試通過
- [ ] 圖片壓縮功能正常（尺寸 <= 400x400, 品質 80%）
- [ ] 權限控制正確（只有業務員可更新自己的 Avatar）
- [ ] Rate Limiting 正常運作（10 次/分鐘）
- [ ] 資料一致性保證（三個欄位同時更新）

### 6.2 安全驗收

- [ ] 拒絕 SVG 上傳（防止 XSS）
- [ ] 防止檔案偽造（內容驗證）
- [ ] 防止資源耗盡（檔案大小限制）
- [ ] Rate Limiting 防止濫用

### 6.3 效能驗收

- [ ] 圖片驗證 < 100ms
- [ ] 圖片壓縮 < 200ms
- [ ] 總處理時間 < 400ms (P95)

---

**撰寫日期**: 2026-01-21
**作者**: Senior Software Architect
**審核者**: Backend Team Lead
**版本**: 1.0
