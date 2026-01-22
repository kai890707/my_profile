# Backend 資料模型規格：Avatar 儲存與管理

**功能**: Avatar 資料儲存結構與管理策略
**日期**: 2026-01-21
**版本**: 1.0
**狀態**: Specification

---

## 1. 概述

### 1.1 資料儲存策略

**當前策略**: Base64 data URL 儲存（保持不變）

**理由**:
1. ✅ 簡化架構（無需獨立的檔案服務）
2. ✅ 資料與業務邏輯集中管理
3. ✅ 備份和恢復簡單
4. ✅ 符合當前規模需求（< 1000 業務員）

**未來考量**:
- 當業務員數量 > 5000 時，考慮 S3/CDN
- 當平均 Avatar 大小 > 500KB 時，考慮獨立儲存

### 1.2 資料大小估算

**假設**:
- 業務員數量：1000 人
- 平均 Avatar 大小（壓縮後）：300KB
- Base64 編碼膨脹率：~133%

**計算**:
```
資料庫空間 = 1000 × 300KB × 1.33 = 399MB
預留 50% 成長空間 = 399MB × 1.5 ≈ 600MB
```

**結論**: MEDIUMBLOB (16MB limit) 足夠使用

---

## 2. 資料表結構

### 2.1 salesperson_profiles 表

**現有欄位（不變）**:

| 欄位名 | 型別 | 約束 | 索引 | 說明 |
|-------|------|------|-----|------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | PRIMARY | 主鍵 |
| user_id | BIGINT UNSIGNED | NOT NULL | INDEX, FK | 關聯使用者 |
| company_id | BIGINT UNSIGNED | NULLABLE | INDEX, FK | 關聯公司 |
| full_name | VARCHAR(200) | NOT NULL | - | 業務員全名 |
| phone | VARCHAR(20) | NULLABLE | - | 聯絡電話 |
| bio | TEXT | NULLABLE | - | 個人簡介 |
| specialties | TEXT | NULLABLE | - | 專長領域 |
| service_regions | JSON | NULLABLE | - | 服務區域 |
| approval_status | ENUM | NOT NULL | INDEX | 審核狀態 |
| created_at | TIMESTAMP | NOT NULL | - | 建立時間 |
| updated_at | TIMESTAMP | NOT NULL | - | 更新時間 |

**Avatar 相關欄位（現有，不變）**:

| 欄位名 | 型別 | 約束 | 索引 | 說明 |
|-------|------|------|-----|------|
| **avatar_data** | **MEDIUMBLOB** | **NULLABLE** | **-** | **Base64 解碼後的二進制資料** |
| **avatar_mime** | **VARCHAR(50)** | **NULLABLE** | **-** | **MIME type (image/jpeg, image/png)** |
| **avatar_size** | **INT UNSIGNED** | **NULLABLE** | **-** | **解碼後的檔案大小 (bytes)** |

### 2.2 欄位詳細說明

#### avatar_data (MEDIUMBLOB)

**用途**: 儲存 Base64 解碼後的圖片二進制資料

**特性**:
- 型別：MEDIUMBLOB
- 最大容量：16MB (16,777,215 bytes)
- 實際使用：< 2MB (驗證限制)
- 儲存格式：二進制資料（非 Base64 文字）

**為什麼用 MEDIUMBLOB 而非 TEXT?**
1. 空間效率：二進制儲存比 Base64 文字小 25%
2. 資料完整性：避免字符集轉換問題
3. 效能：讀取和寫入更快

**資料流程**:
```
Frontend 上傳:
data:image/jpeg;base64,/9j/4AAQSkZJRg...
         ↓ (Backend 處理)
提取 Base64 部分: /9j/4AAQSkZJRg...
         ↓ (Base64 解碼)
二進制資料: \xFF\xD8\xFF\xE0... (JPEG bytes)
         ↓ (儲存到資料庫)
avatar_data: MEDIUMBLOB (binary)

Frontend 讀取:
         ↓ (從資料庫讀取)
二進制資料: \xFF\xD8\xFF\xE0...
         ↓ (Base64 編碼)
Base64 字串: /9j/4AAQSkZJRg...
         ↓ (組合 data URL)
data:image/jpeg;base64,/9j/4AAQSkZJRg...
```

#### avatar_mime (VARCHAR(50))

**用途**: 儲存圖片的 MIME type

**允許值**:
```
'image/jpeg'
'image/png'
'image/webp'
'image/gif'
NULL (無 Avatar)
```

**為什麼需要單獨儲存?**
1. 快速判斷圖片類型（無需解析 BLOB）
2. 組合 data URL 時使用
3. API 回應時包含

**驗證規則**:
```sql
CHECK (avatar_mime IN ('image/jpeg', 'image/png', 'image/webp', 'image/gif') OR avatar_mime IS NULL)
```

#### avatar_size (INT UNSIGNED)

**用途**: 儲存解碼後的檔案大小（bytes）

**範圍**: 0 ~ 4,294,967,295 bytes (4GB)

**實際範圍**: 0 ~ 2,097,152 bytes (2MB) - 驗證限制

**為什麼需要儲存?**
1. 監控資料庫空間使用
2. 統計分析（平均 Avatar 大小）
3. 前端顯示檔案大小（可選）

**更新時機**:
- 每次更新 avatar_data 時同步更新
- 如果 avatar_data 設為 NULL，avatar_size 也設為 NULL

---

## 3. 資料一致性保證

### 3.1 完整性約束

#### 三個欄位的一致性

**規則**: avatar_data, avatar_mime, avatar_size 必須同時存在或同時為 NULL

**實作方式**:

**Laravel Model 層**:
```php
// app/Models/SalespersonProfile.php

protected static function booted(): void
{
    static::saving(function (SalespersonProfile $profile) {
        // 確保 avatar 欄位一致性
        if ($profile->avatar_data === null) {
            $profile->avatar_mime = null;
            $profile->avatar_size = null;
        } elseif ($profile->avatar_mime === null || $profile->avatar_size === null) {
            throw new \InvalidArgumentException(
                'avatar_data, avatar_mime, avatar_size must all be set or all be null'
            );
        }
    });
}
```

**資料庫層** (MySQL Trigger - 可選):
```sql
CREATE TRIGGER check_avatar_consistency
BEFORE INSERT ON salesperson_profiles
FOR EACH ROW
BEGIN
    IF (NEW.avatar_data IS NULL AND (NEW.avatar_mime IS NOT NULL OR NEW.avatar_size IS NOT NULL))
    OR (NEW.avatar_data IS NOT NULL AND (NEW.avatar_mime IS NULL OR NEW.avatar_size IS NULL))
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Avatar fields must all be NULL or all be NOT NULL';
    END IF;
END;
```

### 3.2 資料庫交易保證

**更新 Avatar 時的交易邊界**:

```php
// app/Http/Controllers/Api/SalespersonController.php

DB::transaction(function () use ($profile, $avatarData, $avatarMime, $avatarSize) {
    $profile->update([
        'avatar_data' => $avatarData,
        'avatar_mime' => $avatarMime,
        'avatar_size' => $avatarSize,
        // 其他欄位...
    ]);

    // 記錄審核日誌（如果 approval_status 改變）
    if ($profile->isDirty('approval_status')) {
        ApprovalLog::create([...]);
    }
});
```

---

## 4. 索引策略

### 4.1 現有索引（不變）

```sql
-- 主鍵索引（自動建立）
PRIMARY KEY (id)

-- 查詢索引
INDEX idx_user_id (user_id)
INDEX idx_company_id (company_id)
INDEX idx_approval_status (approval_status)

-- 外鍵索引（自動建立）
FOREIGN KEY (user_id) REFERENCES users(id)
FOREIGN KEY (company_id) REFERENCES companies(id)
```

### 4.2 Avatar 相關欄位是否需要索引?

**avatar_data (MEDIUMBLOB)**: ❌ 不建議索引
- BLOB 欄位無法建立索引（MySQL 限制）
- 不會用於查詢條件

**avatar_mime (VARCHAR(50))**: ❌ 不建議索引
- 選擇性低（只有 4 個值）
- 不常用於查詢條件
- 如果未來需要統計，使用全表掃描即可

**avatar_size (INT UNSIGNED)**: ❌ 不建議索引
- 不常用於查詢條件
- 主要用於統計分析，不需要索引

**結論**: Avatar 相關欄位不需要建立索引

---

## 5. 查詢效能

### 5.1 常見查詢模式

#### 查詢 1: 取得單一業務員完整資料（含 Avatar）

**SQL**:
```sql
SELECT id, user_id, full_name, phone, bio, specialties, service_regions,
       avatar_data, avatar_mime, avatar_size, approval_status,
       created_at, updated_at
FROM salesperson_profiles
WHERE user_id = ?
```

**效能**:
- 索引使用：idx_user_id
- 預估時間：< 10ms (單筆查詢)
- 資料量：~300KB (含 Avatar BLOB)

#### 查詢 2: 取得業務員列表（含 Avatar）

**SQL**:
```sql
SELECT sp.id, sp.full_name, sp.phone, sp.avatar_data, sp.avatar_mime,
       c.name as company_name
FROM salesperson_profiles sp
LEFT JOIN companies c ON sp.company_id = c.id
WHERE sp.approval_status = 'approved'
ORDER BY sp.created_at DESC
LIMIT 20 OFFSET 0
```

**效能**:
- 索引使用：idx_approval_status
- 預估時間：< 50ms (20 筆 + Avatar BLOB)
- 資料量：~6MB (20 × 300KB)

**優化建議**:
- 使用 Eager Loading（Laravel）
- 考慮分頁（已實作）
- 考慮 Avatar 延遲載入（未來）

#### 查詢 3: 統計 Avatar 使用情況

**SQL**:
```sql
-- 有 Avatar 的業務員數量
SELECT COUNT(*) as with_avatar
FROM salesperson_profiles
WHERE avatar_data IS NOT NULL;

-- 平均 Avatar 大小
SELECT AVG(avatar_size) as avg_size, MAX(avatar_size) as max_size
FROM salesperson_profiles
WHERE avatar_size IS NOT NULL;
```

**效能**:
- 全表掃描（無索引）
- 預估時間：< 100ms (1000 筆)
- 頻率：低（僅用於監控報表）

### 5.2 N+1 查詢問題

**問題場景**: 取得業務員列表時，每個業務員都查詢一次 Avatar

**錯誤寫法**:
```php
// ❌ N+1 查詢問題
$profiles = SalespersonProfile::where('approval_status', 'approved')->get();

foreach ($profiles as $profile) {
    // 每次都查詢資料庫
    $avatar = $profile->avatar; // Triggers separate query
}
```

**正確寫法**:
```php
// ✅ 使用 Eager Loading
$profiles = SalespersonProfile::where('approval_status', 'approved')
    ->with('company') // 預載入關聯
    ->get();

// Avatar 欄位已包含在主查詢中，無需額外查詢
```

---

## 6. 資料遷移策略

### 6.1 現有 Migration（不變）

**檔案**: `2026_01_09_132424_create_salesperson_profiles_table.php`

```php
// 已存在，無需修改
Schema::create('salesperson_profiles', function (Blueprint $table): void {
    // ... 其他欄位

    // Avatar 欄位（已存在）
    $table->string('avatar_mime', 50)->nullable();
    $table->unsignedInteger('avatar_size')->nullable();

    // ... 索引和外鍵
});

// MEDIUMBLOB 欄位（已存在）
DB::statement('ALTER TABLE salesperson_profiles ADD COLUMN avatar_data MEDIUMBLOB NULL AFTER service_regions');
```

### 6.2 資料驗證 Migration（新增 - 可選）

**目的**: 驗證和清理現有的無效 Avatar 資料

**檔案**: `2026_01_21_validate_existing_avatars.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SalespersonProfile;

return new class extends Migration
{
    /**
     * 驗證並清理無效的 Avatar 資料
     */
    public function up(): void
    {
        $profiles = SalespersonProfile::whereNotNull('avatar_data')->get();

        foreach ($profiles as $profile) {
            try {
                // 驗證 MIME type
                if (!in_array($profile->avatar_mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
                    $this->clearInvalidAvatar($profile, 'Invalid MIME type');
                    continue;
                }

                // 驗證檔案大小
                if ($profile->avatar_size > 2097152) { // 2MB
                    $this->clearInvalidAvatar($profile, 'File too large');
                    continue;
                }

                // 驗證圖片內容
                $image = imagecreatefromstring($profile->avatar_data);
                if ($image === false) {
                    $this->clearInvalidAvatar($profile, 'Invalid image content');
                    continue;
                }
                imagedestroy($image);

            } catch (\Exception $e) {
                $this->clearInvalidAvatar($profile, $e->getMessage());
            }
        }
    }

    private function clearInvalidAvatar(SalespersonProfile $profile, string $reason): void
    {
        Log::warning("Clearing invalid avatar for profile {$profile->id}: {$reason}");

        $profile->update([
            'avatar_data' => null,
            'avatar_mime' => null,
            'avatar_size' => null,
        ]);
    }

    public function down(): void
    {
        // 無法回滾（已刪除的資料無法恢復）
    }
};
```

**執行時機**: 在部署新驗證規則之前執行

---

## 7. 備份與恢復策略

### 7.1 備份策略

**完整備份**:
```bash
# 備份整個資料庫（含 BLOB）
mysqldump -u user -p --single-transaction --routines --triggers my_profile > backup.sql

# 備份大小估算: ~600MB (含 Avatar BLOB)
```

**僅備份結構**:
```bash
# 不含 Avatar 資料（節省空間）
mysqldump -u user -p --no-data my_profile > schema.sql
```

**選擇性備份** (不含 Avatar):
```bash
# 備份除了 avatar_data 以外的所有資料
mysqldump -u user -p \
  --ignore-table=my_profile.salesperson_profiles \
  my_profile > backup_without_avatars.sql

# 僅備份 profiles（不含 BLOB 欄位）
mysqldump -u user -p my_profile salesperson_profiles \
  --where="1=1" \
  --ignore-columns=avatar_data > profiles_without_avatars.sql
```

### 7.2 恢復策略

**完整恢復**:
```bash
mysql -u user -p my_profile < backup.sql
```

**部分恢復** (僅 Avatar):
```sql
-- 從備份中恢復特定業務員的 Avatar
UPDATE salesperson_profiles
SET avatar_data = (SELECT avatar_data FROM backup_table WHERE id = ?),
    avatar_mime = (SELECT avatar_mime FROM backup_table WHERE id = ?),
    avatar_size = (SELECT avatar_size FROM backup_table WHERE id = ?)
WHERE id = ?;
```

---

## 8. 監控與維護

### 8.1 資料庫空間監控

**查詢當前使用空間**:
```sql
-- 查詢表大小
SELECT
  table_name AS 'Table',
  ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'my_profile'
  AND table_name = 'salesperson_profiles';

-- 查詢 Avatar 佔用空間
SELECT
  COUNT(*) as total_avatars,
  ROUND(SUM(avatar_size) / 1024 / 1024, 2) as total_size_mb,
  ROUND(AVG(avatar_size) / 1024, 2) as avg_size_kb,
  MAX(avatar_size) as max_size_bytes
FROM salesperson_profiles
WHERE avatar_data IS NOT NULL;
```

**預期結果**:
```
total_avatars: 850
total_size_mb: 255.50
avg_size_kb: 307.84
max_size_bytes: 524288 (512KB)
```

### 8.2 效能監控

**慢查詢監控**:
```sql
-- 啟用慢查詢日誌
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 0.5; -- 超過 0.5 秒視為慢查詢

-- 查詢慢查詢
SELECT * FROM mysql.slow_log
WHERE sql_text LIKE '%salesperson_profiles%'
ORDER BY query_time DESC
LIMIT 10;
```

### 8.3 定期維護任務

**優化表**:
```sql
-- 每月執行一次
OPTIMIZE TABLE salesperson_profiles;
```

**清理無效資料**:
```sql
-- 清理孤立的 Avatar（對應的 user 已刪除）
DELETE FROM salesperson_profiles
WHERE user_id NOT IN (SELECT id FROM users);
```

---

## 9. 未來擴展考量

### 9.1 從 BLOB 遷移到 S3 的策略（未來可能）

**觸發條件**:
- 業務員數量 > 5000
- Avatar 總大小 > 5GB
- 需要 CDN 加速

**遷移步驟**:
```
1. 新增 avatar_url 欄位（VARCHAR(500)）
2. 逐步將 BLOB 資料上傳到 S3
3. 更新 avatar_url 為 S3 URL
4. 應用程式同時支援 BLOB 和 URL（雙讀）
5. 完成遷移後，刪除 avatar_data 欄位
```

**相容性設計**:
```php
// app/Models/SalespersonProfile.php

public function getAvatarAttribute(): ?string
{
    // 優先使用 S3 URL
    if ($this->avatar_url) {
        return $this->avatar_url;
    }

    // Fallback 到 BLOB
    if ($this->avatar_data && $this->avatar_mime) {
        return "data:{$this->avatar_mime};base64," . base64_encode($this->avatar_data);
    }

    return null;
}
```

### 9.2 圖片格式優化（未來可能）

**考慮支援 AVIF**:
- 壓縮率比 WebP 更好（~30%）
- 但瀏覽器相容性較差（2024 年約 80%）
- 需要 Imagick 支援（PHP GD 不支援）

**實作方式**:
```php
// 檢測瀏覽器支援
if (Request::header('Accept')->contains('image/avif')) {
    // 轉換為 AVIF
    $avifData = Imagick::convertToAVIF($jpegData);
}
```

---

## 10. 測試資料

### 10.1 Seed 資料

**測試用 Avatar**:

```php
// database/seeders/SalespersonProfileSeeder.php

use Illuminate\Support\Facades\File;

public function run(): void
{
    // 載入測試圖片
    $testImage = File::get(base_path('tests/fixtures/avatar.jpg'));
    $base64 = base64_encode($testImage);
    $size = strlen($testImage);

    SalespersonProfile::factory()->create([
        'avatar_data' => $testImage,
        'avatar_mime' => 'image/jpeg',
        'avatar_size' => $size,
    ]);
}
```

### 10.2 Factory 定義

```php
// database/factories/SalespersonProfileFactory.php

public function definition(): array
{
    return [
        'user_id' => User::factory(),
        'full_name' => $this->faker->name(),
        'phone' => $this->faker->phoneNumber(),
        'bio' => $this->faker->paragraph(),
        'specialties' => $this->faker->words(3, true),
        'service_regions' => ['台北市', '新北市'],
        'avatar_data' => null, // 預設無 Avatar
        'avatar_mime' => null,
        'avatar_size' => null,
        'approval_status' => 'approved',
    ];
}

// 帶 Avatar 的 Factory
public function withAvatar(): static
{
    return $this->state(function (array $attributes) {
        $testImage = File::get(base_path('tests/fixtures/avatar.jpg'));
        return [
            'avatar_data' => $testImage,
            'avatar_mime' => 'image/jpeg',
            'avatar_size' => strlen($testImage),
        ];
    });
}
```

---

## 11. 驗收標準

### 11.1 資料完整性

- [ ] avatar_data, avatar_mime, avatar_size 同時為 NULL 或同時有值
- [ ] avatar_mime 只能是允許的 4 個值
- [ ] avatar_size 與實際 BLOB 大小一致
- [ ] 外鍵約束正確（不會有孤立記錄）

### 11.2 效能標準

- [ ] 單筆查詢（含 Avatar）< 10ms
- [ ] 列表查詢（20 筆含 Avatar）< 50ms
- [ ] 更新 Avatar < 100ms (資料庫操作)

### 11.3 容量標準

- [ ] MEDIUMBLOB 可儲存 2MB 檔案
- [ ] 1000 筆業務員資料佔用 < 1GB 資料庫空間
- [ ] 備份檔案 < 1GB (gzip 壓縮後)

---

**撰寫日期**: 2026-01-21
**作者**: Senior Software Architect
**審核者**: Database Administrator
**版本**: 1.0
