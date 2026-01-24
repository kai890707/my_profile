# 資料模型規格文檔

**功能**: 聯繫機制功能（Contact Mechanism）
**版本**: 1.0
**最後更新**: 2026-01-23

---

## 概述

本文檔定義「聯繫機制功能」所需的資料庫結構，包含：
1. 擴充 `salesperson_profiles` 資料表（新增聯繫方式欄位）
2. 新增 `contact_requests` 資料表（聯繫請求記錄）
3. 新增 `contact_events` 資料表（事件追蹤）

---

## 架構決策

### ADR-001: 選擇擴充現有資料表而非建立獨立資料表

**狀態**: 已接受

**背景**:
業務員聯繫方式可以選擇：
1. 擴充 `salesperson_profiles` 資料表（新增欄位）
2. 建立獨立的 `contact_methods` 資料表

**決策**: 採用方案 1（擴充現有資料表）

**理由**:
- 業務員聯繫方式與個人檔案緊密相關，經常一起查詢
- 每個業務員僅有一組聯繫方式（MVP 階段不支援多組）
- 避免 JOIN 查詢，提升效能
- 簡化 API 回應結構

**後果**:
- 優點: 查詢效能好、資料結構簡單
- 缺點: 未來若需支援多組聯繫方式需要重構

**備註**: 當需要支援多組聯繫方式時（Phase 3），重新評估並建立獨立資料表

---

### ADR-002: IP 位址 Hash 儲存（隱私保護）

**狀態**: 已接受

**背景**:
追蹤事件需要記錄 IP 位址，但直接儲存明文 IP 位址有隱私疑慮

**決策**: 使用 SHA256 hash 後儲存

**理由**:
- 符合 GDPR/PDPA 隱私保護要求
- 仍可追蹤同一 IP 的行為模式（相同 IP 產生相同 hash）
- 無法從 hash 反推出原始 IP（不可逆）

**實作**:
```php
$ipHash = hash('sha256', $request->ip());
```

---

### ADR-003: 客戶個資加密儲存

**狀態**: 已接受

**背景**:
`contact_requests` 資料表需要儲存客戶的 Email 和電話

**決策**: 使用 Laravel Encryption 加密儲存

**理由**:
- 保護客戶隱私
- 防止資料庫洩漏時個資外洩
- Laravel 內建支援，實作簡單

**實作**:
```php
protected $casts = [
    'customer_email' => 'encrypted',
    'customer_phone' => 'encrypted',
];
```

---

## 實體關係圖（ERD）

```
┌─────────────────┐         ┌─────────────────────┐
│     users       │         │ salesperson_profiles│
│─────────────────│◄───1:1──┤─────────────────────│
│ id (PK)         │         │ id (PK)             │
│ email           │         │ user_id (FK)        │
│ name            │         │ bio                 │
│ role            │         │ phone               │ ← 新增
│                 │         │ email_public        │ ← 新增
│                 │         │ line_id             │ ← 新增
│                 │         │ wechat_id           │ ← 新增
│                 │         │ contact_preferences │ ← 新增
└─────────────────┘         └─────────────────────┘
        │                            │
        │                            │
        │                            │
        └──────┬─────────────────────┘
               │
               │ 1:N
               │
        ┌──────▼──────────────┐
        │ contact_requests    │
        │─────────────────────│
        │ id (PK)             │
        │ user_id (FK)        │ → users.id (客戶)
        │ salesperson_id (FK) │ → users.id (業務員)
        │ customer_name       │
        │ customer_email      │ (加密)
        │ customer_phone      │ (加密)
        │ message             │
        │ status              │
        │ created_at          │
        └─────────────────────┘

        ┌──────────────────────┐
        │ contact_events       │
        │──────────────────────│
        │ id (PK)              │
        │ user_id (FK)         │ → users.id (可為 NULL)
        │ salesperson_id (FK)  │ → users.id
        │ event_type           │ (enum)
        │ ip_address_hash      │ (SHA256)
        │ user_agent           │
        │ created_at           │
        └──────────────────────┘
```

---

## 資料表設計

### 1. salesperson_profiles 資料表擴充

#### 1.1 擴充說明

在現有 `salesperson_profiles` 資料表新增聯繫方式欄位

#### 1.2 新增欄位定義

| 欄位名 | 型別 | 長度 | NULL | 預設值 | 說明 |
|-------|------|------|------|-------|------|
| phone | VARCHAR | 20 | YES | NULL | 聯繫電話 |
| email_public | VARCHAR | 255 | YES | NULL | 公開 Email（區別於帳號 Email） |
| line_id | VARCHAR | 50 | YES | NULL | LINE ID |
| wechat_id | VARCHAR | 50 | YES | NULL | WeChat ID |
| contact_preferences | JSON | - | YES | NULL | 聯繫偏好順序（陣列） |

#### 1.3 欄位約束

- 所有欄位都是 `nullable`（業務員可選填）
- 至少提供 1 種聯繫方式（應用層驗證，非 DB 約束）

#### 1.4 索引設計

**新增索引**:
```sql
-- 加速「有提供電話的業務員」查詢（未來可能需要）
CREATE INDEX idx_phone ON salesperson_profiles(phone);

-- 加速「有提供 Email 的業務員」查詢（未來可能需要）
CREATE INDEX idx_email_public ON salesperson_profiles(email_public);
```

**索引考量**:
- `phone` 和 `email_public` 建立索引是為了未來可能的篩選需求
- LINE/WeChat ID 不建立索引（暫無篩選需求）
- 索引不會影響寫入效能（業務員更新聯繫方式頻率低）

#### 1.5 Migration 程式碼

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // 新增聯繫方式欄位
            $table->string('phone', 20)->nullable()->after('avatar_mime');
            $table->string('email_public', 255)->nullable()->after('phone');
            $table->string('line_id', 50)->nullable()->after('email_public');
            $table->string('wechat_id', 50)->nullable()->after('line_id');
            $table->json('contact_preferences')->nullable()->after('wechat_id');

            // 新增索引
            $table->index('phone');
            $table->index('email_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salesperson_profiles', function (Blueprint $table): void {
            // 刪除索引
            $table->dropIndex(['phone']);
            $table->dropIndex(['email_public']);

            // 刪除欄位
            $table->dropColumn([
                'phone',
                'email_public',
                'line_id',
                'wechat_id',
                'contact_preferences',
            ]);
        });
    }
};
```

**Migration 檔名**: `2026_01_23_XXXXXX_add_contact_fields_to_salesperson_profiles.php`

#### 1.6 Model 擴充

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalespersonProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'specialties',
        'years_of_experience',
        'approval_status',
        'avatar_data',
        'avatar_mime',
        'phone',              // 新增
        'email_public',       // 新增
        'line_id',            // 新增
        'wechat_id',          // 新增
        'contact_preferences', // 新增
    ];

    protected $casts = [
        'years_of_experience' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'contact_preferences' => 'array', // JSON 轉陣列
    ];

    /**
     * 檢查是否有至少一種聯繫方式
     */
    public function hasContactMethods(): bool
    {
        return !empty($this->phone) ||
               !empty($this->email_public) ||
               !empty($this->line_id) ||
               !empty($this->wechat_id);
    }

    /**
     * 取得所有聯繫方式（排除空值）
     */
    public function getAvailableContactMethods(): array
    {
        $methods = [];

        if ($this->phone) {
            $methods['phone'] = $this->phone;
        }
        if ($this->email_public) {
            $methods['email_public'] = $this->email_public;
        }
        if ($this->line_id) {
            $methods['line_id'] = $this->line_id;
        }
        if ($this->wechat_id) {
            $methods['wechat_id'] = $this->wechat_id;
        }

        return $methods;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

#### 1.7 資料範例

```sql
-- 業務員 A (提供所有聯繫方式)
INSERT INTO salesperson_profiles (user_id, phone, email_public, line_id, wechat_id, contact_preferences)
VALUES (
    2,
    '0912-345-678',
    'salesperson@example.com',
    'my_line_id',
    'my_wechat',
    '["line", "phone", "email", "wechat"]'
);

-- 業務員 B (僅提供電話和 Email)
INSERT INTO salesperson_profiles (user_id, phone, email_public, line_id, wechat_id, contact_preferences)
VALUES (
    3,
    '0987-654-321',
    'another@example.com',
    NULL,
    NULL,
    '["phone", "email"]'
);

-- 業務員 C (尚未提供聯繫方式)
INSERT INTO salesperson_profiles (user_id, phone, email_public, line_id, wechat_id, contact_preferences)
VALUES (
    4,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);
```

---

### 2. contact_requests 資料表

#### 2.1 資料表用途

儲存客戶提交的聯繫請求記錄

#### 2.2 欄位定義

| 欄位名 | 型別 | 長度 | NULL | 預設值 | 索引 | 說明 |
|-------|------|------|------|-------|------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PK | 主鍵 |
| user_id | BIGINT UNSIGNED | - | YES | NULL | FK, INDEX | 客戶 ID（關聯 users.id） |
| salesperson_id | BIGINT UNSIGNED | - | NO | - | FK, INDEX | 業務員 ID（關聯 users.id） |
| customer_name | VARCHAR | 100 | NO | - | - | 客戶姓名（自動填入） |
| customer_email | VARCHAR | 255 | NO | - | - | 客戶 Email（加密儲存） |
| customer_phone | VARCHAR | 20 | YES | NULL | - | 客戶電話（加密儲存） |
| message | TEXT | - | NO | - | - | 訊息內容 |
| status | ENUM | - | NO | 'pending' | INDEX | 狀態（pending, contacted, closed） |
| created_at | TIMESTAMP | - | NO | CURRENT_TIMESTAMP | INDEX | 建立時間 |
| updated_at | TIMESTAMP | - | NO | CURRENT_TIMESTAMP | - | 更新時間 |

#### 2.3 欄位約束

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
```

**欄位約束說明**:
- `user_id`: 允許 NULL（未來可能支援刪除用戶但保留記錄）
- `salesperson_id`: 不允許 NULL（業務員被刪除時，聯繫記錄也刪除）
- `customer_email`: 使用 Laravel Encryption 加密
- `customer_phone`: 使用 Laravel Encryption 加密

#### 2.4 索引設計

**索引策略**:
```sql
-- 複合索引：支援頻率限制查詢（24h 內同業務員）
CREATE INDEX idx_user_salesperson_created
ON contact_requests(user_id, salesperson_id, created_at);

-- 索引：業務員查詢自己的聯繫請求
CREATE INDEX idx_salesperson_status_created
ON contact_requests(salesperson_id, status, created_at);

-- 索引：按建立時間排序
CREATE INDEX idx_created_at ON contact_requests(created_at);
```

**索引使用場景**:
1. `idx_user_salesperson_created`: 檢查 24h 內是否已聯繫過
   ```sql
   SELECT * FROM contact_requests
   WHERE user_id = ? AND salesperson_id = ? AND created_at >= ?
   ```

2. `idx_salesperson_status_created`: 業務員查詢未回覆的請求
   ```sql
   SELECT * FROM contact_requests
   WHERE salesperson_id = ? AND status = 'pending'
   ORDER BY created_at DESC
   ```

3. `idx_created_at`: Admin 查詢所有請求（按時間排序）
   ```sql
   SELECT * FROM contact_requests
   ORDER BY created_at DESC
   LIMIT 20 OFFSET 0
   ```

#### 2.5 SQL Schema

```sql
CREATE TABLE contact_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    salesperson_id BIGINT UNSIGNED NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'contacted', 'closed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user_salesperson_created (user_id, salesperson_id, created_at),
    INDEX idx_salesperson_status_created (salesperson_id, status, created_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2.6 Migration 程式碼

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('salesperson_id')->constrained('users')->onDelete('cascade');
            $table->string('customer_name', 100);
            $table->string('customer_email'); // 將使用 Encryption cast
            $table->string('customer_phone', 20)->nullable(); // 將使用 Encryption cast
            $table->text('message');
            $table->enum('status', ['pending', 'contacted', 'closed'])->default('pending');
            $table->timestamps();

            // 索引
            $table->index(['user_id', 'salesperson_id', 'created_at'], 'idx_user_salesperson_created');
            $table->index(['salesperson_id', 'status', 'created_at'], 'idx_salesperson_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
```

**Migration 檔名**: `2026_01_23_XXXXXX_create_contact_requests_table.php`

#### 2.7 Model 類別

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactRequest extends Model
{
    protected $fillable = [
        'user_id',
        'salesperson_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'message',
        'status',
    ];

    protected $casts = [
        'customer_email' => 'encrypted', // Laravel Encryption
        'customer_phone' => 'encrypted', // Laravel Encryption
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 客戶關聯
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 業務員關聯
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Scope: 篩選特定業務員
     */
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope: 篩選特定狀態
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: 按時間排序
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
```

#### 2.8 資料範例

```sql
-- 客戶 A 聯繫業務員 B
INSERT INTO contact_requests (
    user_id,
    salesperson_id,
    customer_name,
    customer_email,
    customer_phone,
    message,
    status
) VALUES (
    5, -- 客戶 ID
    2, -- 業務員 ID
    '王小明',
    'customer@example.com', -- 實際儲存時會加密
    '0912-345-678',         -- 實際儲存時會加密
    '您好，我想詢問保險相關服務，請問方便聯繫嗎？',
    'pending'
);

-- 客戶 B 聯繫業務員 C（無提供電話）
INSERT INTO contact_requests (
    user_id,
    salesperson_id,
    customer_name,
    customer_email,
    customer_phone,
    message,
    status
) VALUES (
    6,
    3,
    '李小華',
    'another@example.com',
    NULL,
    '想了解醫療險的相關資訊',
    'pending'
);
```

---

### 3. contact_events 資料表

#### 3.1 資料表用途

追蹤客戶與業務員的互動事件，用於數據分析

#### 3.2 欄位定義

| 欄位名 | 型別 | 長度 | NULL | 預設值 | 索引 | 說明 |
|-------|------|------|------|-------|------|------|
| id | BIGINT UNSIGNED | - | NO | AUTO_INCREMENT | PK | 主鍵 |
| user_id | BIGINT UNSIGNED | - | YES | NULL | FK, INDEX | 用戶 ID（未登入為 NULL） |
| salesperson_id | BIGINT UNSIGNED | - | NO | - | FK, INDEX | 業務員 ID |
| event_type | ENUM | - | NO | - | INDEX | 事件類型 |
| ip_address_hash | CHAR | 64 | NO | - | INDEX | IP 位址 Hash (SHA256) |
| user_agent | VARCHAR | 255 | YES | NULL | - | User Agent |
| created_at | TIMESTAMP | - | NO | CURRENT_TIMESTAMP | INDEX | 建立時間 |

**特殊說明**:
- 此資料表僅有 `created_at`，不需要 `updated_at`（事件一旦建立不會更新）

#### 3.3 欄位約束

**Foreign Keys**:
```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
```

**ENUM Values**:
- `event_type`: `'profile_view'`, `'contact_form_submission'`

#### 3.4 索引設計

**索引策略**:
```sql
-- 複合索引：業務員查詢特定事件類型的統計
CREATE INDEX idx_salesperson_type_created
ON contact_events(salesperson_id, event_type, created_at);

-- 複合索引：用戶查詢自己的行為記錄
CREATE INDEX idx_user_type_created
ON contact_events(user_id, event_type, created_at);

-- 索引：按事件類型和時間統計
CREATE INDEX idx_event_type_created
ON contact_events(event_type, created_at);

-- 索引：IP Hash（去重和頻率分析）
CREATE INDEX idx_ip_hash ON contact_events(ip_address_hash);
```

**索引使用場景**:
1. `idx_salesperson_type_created`: 統計業務員檔案瀏覽量
   ```sql
   SELECT COUNT(*) FROM contact_events
   WHERE salesperson_id = ? AND event_type = 'profile_view'
   AND created_at >= ?
   ```

2. `idx_event_type_created`: 全站事件統計
   ```sql
   SELECT DATE(created_at) as date, COUNT(*) as count
   FROM contact_events
   WHERE event_type = 'profile_view'
   GROUP BY DATE(created_at)
   ```

3. `idx_ip_hash`: 去重計算獨立訪客
   ```sql
   SELECT COUNT(DISTINCT ip_address_hash) as unique_visitors
   FROM contact_events
   WHERE salesperson_id = ? AND event_type = 'profile_view'
   ```

#### 3.5 SQL Schema

```sql
CREATE TABLE contact_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    salesperson_id BIGINT UNSIGNED NOT NULL,
    event_type ENUM('profile_view', 'contact_form_submission') NOT NULL,
    ip_address_hash CHAR(64) NOT NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_salesperson_type_created (salesperson_id, event_type, created_at),
    INDEX idx_user_type_created (user_id, event_type, created_at),
    INDEX idx_event_type_created (event_type, created_at),
    INDEX idx_ip_hash (ip_address_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3.6 Migration 程式碼

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('salesperson_id')->constrained('users')->onDelete('cascade');
            $table->enum('event_type', ['profile_view', 'contact_form_submission']);
            $table->char('ip_address_hash', 64); // SHA256 hash = 64 字元
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // 索引
            $table->index(['salesperson_id', 'event_type', 'created_at'], 'idx_salesperson_type_created');
            $table->index(['user_id', 'event_type', 'created_at'], 'idx_user_type_created');
            $table->index(['event_type', 'created_at'], 'idx_event_type_created');
            $table->index('ip_address_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_events');
    }
};
```

**Migration 檔名**: `2026_01_23_XXXXXX_create_contact_events_table.php`

#### 3.7 Model 類別

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactEvent extends Model
{
    const UPDATED_AT = null; // 不需要 updated_at

    protected $fillable = [
        'user_id',
        'salesperson_id',
        'event_type',
        'ip_address_hash',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * 用戶關聯
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 業務員關聯
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Scope: 篩選特定業務員
     */
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope: 篩選特定事件類型
     */
    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope: 時間範圍篩選
     */
    public function scopeBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * 建立事件（靜態方法）
     */
    public static function track(
        int $salespersonId,
        string $eventType,
        ?int $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'salesperson_id' => $salespersonId,
            'event_type' => $eventType,
            'ip_address_hash' => $ipAddress ? hash('sha256', $ipAddress) : '',
            'user_agent' => $userAgent,
        ]);
    }
}
```

#### 3.8 資料範例

```sql
-- 客戶 A 查看業務員 B 的檔案
INSERT INTO contact_events (
    user_id,
    salesperson_id,
    event_type,
    ip_address_hash,
    user_agent
) VALUES (
    5,
    2,
    'profile_view',
    '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', -- SHA256 hash
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
);

-- 未登入用戶查看業務員 C 的檔案
INSERT INTO contact_events (
    user_id,
    salesperson_id,
    event_type,
    ip_address_hash,
    user_agent
) VALUES (
    NULL, -- 未登入
    3,
    'profile_view',
    'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3',
    'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)'
);

-- 客戶 A 提交聯繫表單
INSERT INTO contact_events (
    user_id,
    salesperson_id,
    event_type,
    ip_address_hash,
    user_agent
) VALUES (
    5,
    2,
    'contact_form_submission',
    '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
);
```

---

## 資料量估算

### 第 1 年預估

| 資料表 | 預估筆數 | 成長率 | 資料大小 |
|-------|---------|-------|---------|
| `salesperson_profiles` (擴充) | +5 欄位 x 100 業務員 | - | ~10 KB |
| `contact_requests` | 10 請求/週 x 52 週 = 520 筆 | 20%/月 | ~200 KB |
| `contact_events` | 1000 事件/週 x 52 週 = 52,000 筆 | 30%/月 | ~10 MB |

### 5 年預估

| 資料表 | 預估筆數 | 資料大小 |
|-------|---------|---------|
| `salesperson_profiles` | +5 欄位 x 500 業務員 | ~50 KB |
| `contact_requests` | ~50,000 筆 | ~20 MB |
| `contact_events` | ~5,000,000 筆 | ~1 GB |

**結論**: 資料量在可接受範圍內，暫不需要分片

---

## 效能考量

### 1. 查詢最佳化

**避免 N+1 問題**:
```php
// ❌ 不好（N+1 問題）
$requests = ContactRequest::all();
foreach ($requests as $request) {
    echo $request->salesperson->name; // 每次都查詢一次
}

// ✅ 好（使用 Eager Loading）
$requests = ContactRequest::with(['salesperson', 'user'])->get();
foreach ($requests as $request) {
    echo $request->salesperson->name;
}
```

### 2. 索引使用驗證

**使用 EXPLAIN 驗證索引**:
```sql
EXPLAIN SELECT * FROM contact_requests
WHERE user_id = 5 AND salesperson_id = 2 AND created_at >= '2026-01-22';
```

**預期結果**: `type = ref`, `key = idx_user_salesperson_created`

### 3. 快取策略

**業務員聯繫方式快取**:
```php
// 快取 5 分鐘
$contactInfo = Cache::remember(
    "salesperson.{$id}.contact_info",
    300,
    fn() => SalespersonProfile::find($id)->only([
        'phone', 'email_public', 'line_id', 'wechat_id', 'contact_preferences'
    ])
);
```

---

## 安全性考量

### 1. 資料加密

**Customer Email/Phone 加密**:
```php
protected $casts = [
    'customer_email' => 'encrypted',
    'customer_phone' => 'encrypted',
];
```

**查詢時自動解密**:
```php
$request = ContactRequest::find(1);
echo $request->customer_email; // 自動解密
```

### 2. IP 位址 Hash

**SHA256 Hash**:
```php
$ipHash = hash('sha256', $request->ip());
```

**不可逆**: 無法從 hash 反推原始 IP

### 3. 軟刪除考量

**目前不使用軟刪除**:
- `contact_requests`: 使用 Foreign Key `ON DELETE CASCADE`（業務員刪除時，請求也刪除）
- 未來 Phase 2 可考慮軟刪除（保留歷史記錄供審計）

---

## Migration 執行順序

執行 Migration 的正確順序：

```bash
# 1. 擴充 salesperson_profiles
php artisan migrate --path=/database/migrations/2026_01_23_XXXXXX_add_contact_fields_to_salesperson_profiles.php

# 2. 建立 contact_requests 資料表
php artisan migrate --path=/database/migrations/2026_01_23_XXXXXX_create_contact_requests_table.php

# 3. 建立 contact_events 資料表
php artisan migrate --path=/database/migrations/2026_01_23_XXXXXX_create_contact_events_table.php
```

**回滾順序**（相反）:
```bash
php artisan migrate:rollback --step=3
```

---

## 測試資料 Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalespersonProfile;
use App\Models\ContactRequest;
use App\Models\ContactEvent;
use App\Models\User;

class ContactMechanismSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 更新業務員聯繫方式
        $salesperson = User::where('role', 'salesperson')->first();
        $salesperson->salespersonProfile->update([
            'phone' => '0912-345-678',
            'email_public' => 'salesperson@example.com',
            'line_id' => 'my_line_id',
            'wechat_id' => 'my_wechat',
            'contact_preferences' => ['line', 'phone', 'email', 'wechat'],
        ]);

        // 2. 建立聯繫請求
        $customer = User::where('role', 'user')->first();
        ContactRequest::create([
            'user_id' => $customer->id,
            'salesperson_id' => $salesperson->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '0987-654-321',
            'message' => '您好，我想詢問保險相關服務，請問方便聯繫嗎？',
            'status' => 'pending',
        ]);

        // 3. 建立追蹤事件
        ContactEvent::track(
            salespersonId: $salesperson->id,
            eventType: 'profile_view',
            userId: $customer->id,
            ipAddress: '127.0.0.1',
            userAgent: 'Mozilla/5.0'
        );

        ContactEvent::track(
            salespersonId: $salesperson->id,
            eventType: 'contact_form_submission',
            userId: $customer->id,
            ipAddress: '127.0.0.1',
            userAgent: 'Mozilla/5.0'
        );
    }
}
```

---

## Changelog

| 版本 | 日期 | 變更內容 |
|------|------|---------|
| 1.0 | 2026-01-23 | 初版資料模型規格 |

---

**下一步**: 參考 `business-rules.md` 了解業務規則
