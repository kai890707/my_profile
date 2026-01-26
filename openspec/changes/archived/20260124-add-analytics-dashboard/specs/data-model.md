# 資料模型規格文檔 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24

---

## 📋 概述

本文檔定義 Analytics Dashboard 功能所需的資料庫設計，包括：

- **新增資料表** (1 張) - `daily_analytics` 每日數據彙總表
- **現有資料表** (3 張) - `contact_events`, `contact_requests`, `users`
- **索引設計** - 查詢優化策略
- **Migration 程式碼** - 完整的資料庫遷移檔案
- **Model 類別** - 完整的 Eloquent Model

---

## 🗂️ 資料表架構概覽

### Entity Relationship Diagram (ERD)

```
┌──────────────────┐
│     users        │
│ (既有資料表)      │
│ ─────────────    │
│ id (PK)          │◄────┐
│ role             │     │
│ status           │     │
│ ...              │     │
└──────────────────┘     │
                          │
                          │
┌──────────────────────┐ │  ┌───────────────────────┐
│ daily_analytics      │ │  │  contact_events       │
│ (新增資料表)          │ │  │  (既有資料表)          │
│ ─────────────────    │ │  │  ─────────────────    │
│ id (PK)              │ │  │  id (PK)              │
│ salesperson_id (FK)  │─┘  │  salesperson_id (FK)  │─┐
│ date                 │    │  event_type           │ │
│ profile_views_count  │    │  ip_address_hash      │ │
│ contact_requests_    │    │  created_at           │ │
│   count              │    │  ...                  │ │
│ unique_visitors_     │    └───────────────────────┘ │
│   count              │                              │
│ created_at           │                              │
│ updated_at           │    ┌───────────────────────┐ │
└──────────────────────┘    │  contact_requests     │ │
                            │  (既有資料表)          │ │
                            │  ─────────────────    │ │
                            │  id (PK)              │ │
                            │  salesperson_id (FK)  │─┘
                            │  customer_name        │
                            │  customer_email       │
                            │  status               │
                            │  created_at           │
                            │  ...                  │
                            └───────────────────────┘
```

### 資料流向

```
資料收集 (即時)
├── contact_events
│   ├── event_type = 'profile_view' → 瀏覽事件
│   └── event_type = 'contact_form_submission' → 提交事件
│
├── contact_requests
│   └── 聯繫請求詳細資料
│
每日彙總 (定時任務)
├── 執行時間: 每日 02:00
├── 來源: contact_events + contact_requests (昨日數據)
└── 目的地: daily_analytics (預先彙總，加速查詢)

資料查詢 (混合策略)
├── 歷史數據 (昨天及更早) → daily_analytics 表
├── 今日數據 (當天) → contact_events + contact_requests 表
└── 合併邏輯 → API 層
```

---

## 📊 新增資料表: daily_analytics

### 用途

每日彙總業務員的數據，包含瀏覽數、聯繫數、獨立訪客數。

**設計目標**:
- 加速歷史數據查詢 (避免每次都掃描整個 contact_events 表)
- 減少資料庫負載
- 支援趨勢分析 (日/週/月)

### 欄位定義

| 欄位名 | 型別 | 約束 | 索引 | 預設值 | 說明 |
|--------|------|------|------|--------|------|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | PRIMARY | - | 主鍵 |
| `salesperson_id` | BIGINT UNSIGNED | NOT NULL, FK | UNIQUE (複合), INDEX (複合) | - | 業務員 ID (關聯到 users.id) |
| `date` | DATE | NOT NULL | UNIQUE (複合), INDEX (複合) | - | 統計日期 (YYYY-MM-DD) |
| `profile_views_count` | INT UNSIGNED | NOT NULL | - | 0 | 當日檔案瀏覽次數 |
| `contact_requests_count` | INT UNSIGNED | NOT NULL | - | 0 | 當日聯繫請求次數 |
| `unique_visitors_count` | INT UNSIGNED | NOT NULL | - | 0 | 當日獨立訪客數 (基於 IP hash 去重) |
| `created_at` | TIMESTAMP | NOT NULL | - | CURRENT_TIMESTAMP | 建立時間 |
| `updated_at` | TIMESTAMP | NOT NULL | - | CURRENT_TIMESTAMP ON UPDATE | 更新時間 |

### 索引設計

#### PRIMARY KEY (自動建立)
```sql
PRIMARY KEY (id)
```

#### UNIQUE 複合索引 (防止重複彙總)
```sql
UNIQUE KEY unique_salesperson_date (salesperson_id, date)
```
- **用途**: 確保每個業務員每天只有一筆記錄
- **查詢範例**: `SELECT * FROM daily_analytics WHERE salesperson_id = ? AND date = ?`

#### 複合索引 (加速時間範圍查詢)
```sql
INDEX idx_salesperson_date (salesperson_id, date)
```
- **用途**: 查詢特定業務員的時間範圍數據
- **查詢範例**: `SELECT * FROM daily_analytics WHERE salesperson_id = ? AND date BETWEEN ? AND ?`

#### 單欄索引 (加速日期查詢)
```sql
INDEX idx_date (date)
```
- **用途**: 管理員查詢特定日期的所有業務員數據
- **查詢範例**: `SELECT * FROM daily_analytics WHERE date = ?`

#### 外鍵約束
```sql
FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
```
- **用途**: 當業務員被刪除時，自動刪除相關的彙總數據
- **級聯操作**: ON DELETE CASCADE

### CREATE TABLE SQL

```sql
CREATE TABLE daily_analytics (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    salesperson_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    profile_views_count INT UNSIGNED NOT NULL DEFAULT 0,
    contact_requests_count INT UNSIGNED NOT NULL DEFAULT 0,
    unique_visitors_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- 唯一約束: 每個業務員每天只有一筆記錄
    UNIQUE KEY unique_salesperson_date (salesperson_id, date),

    -- 複合索引: 業務員時間範圍查詢
    INDEX idx_salesperson_date (salesperson_id, date),

    -- 單欄索引: 日期查詢
    INDEX idx_date (date),

    -- 外鍵約束
    FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Laravel Migration 程式碼

**檔案位置**: `database/migrations/2026_01_24_100000_create_daily_analytics_table.php`

```php
<?php

declare(strict_types=1);

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
        Schema::create('daily_analytics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('salesperson_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('profile_views_count')->default(0);
            $table->unsignedInteger('contact_requests_count')->default(0);
            $table->unsignedInteger('unique_visitors_count')->default(0);
            $table->timestamps();

            // 唯一約束: 每個業務員每天只有一筆記錄
            $table->unique(['salesperson_id', 'date'], 'unique_salesperson_date');

            // 複合索引: 業務員時間範圍查詢
            $table->index(['salesperson_id', 'date'], 'idx_salesperson_date');

            // 單欄索引: 日期查詢
            $table->index('date', 'idx_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_analytics');
    }
};
```

### Eloquent Model 程式碼

**檔案位置**: `app/Models/DailyAnalytics.php`

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DailyAnalytics Model
 *
 * @property int $id
 * @property int $salesperson_id
 * @property string $date
 * @property int $profile_views_count
 * @property int $contact_requests_count
 * @property int $unique_visitors_count
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read User $salesperson
 */
class DailyAnalytics extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'daily_analytics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'salesperson_id',
        'date',
        'profile_views_count',
        'contact_requests_count',
        'unique_visitors_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'salesperson_id' => 'integer',
        'date' => 'date',
        'profile_views_count' => 'integer',
        'contact_requests_count' => 'integer',
        'unique_visitors_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the salesperson that owns this analytics record.
     *
     * @return BelongsTo<User, $this>
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * Scope a query to filter by salesperson.
     *
     * @param \Illuminate\Database\Eloquent\Builder<DailyAnalytics> $query
     * @param int $salespersonId
     * @return \Illuminate\Database\Eloquent\Builder<DailyAnalytics>
     */
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder<DailyAnalytics> $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder<DailyAnalytics>
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Get aggregated stats for a salesperson in a date range.
     *
     * @param int $salespersonId
     * @param string $startDate
     * @param string $endDate
     * @return object{profile_views: int, contact_requests: int, unique_visitors: int}|null
     */
    public static function getAggregatedStats(
        int $salespersonId,
        string $startDate,
        string $endDate
    ): ?object {
        return self::forSalesperson($salespersonId)
            ->dateRange($startDate, $endDate)
            ->selectRaw('
                SUM(profile_views_count) as profile_views,
                SUM(contact_requests_count) as contact_requests,
                SUM(unique_visitors_count) as unique_visitors
            ')
            ->first();
    }

    /**
     * Get daily trends for a salesperson in a date range.
     *
     * @param int $salespersonId
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function getDailyTrends(
        int $salespersonId,
        string $startDate,
        string $endDate
    ): \Illuminate\Support\Collection {
        return self::forSalesperson($salespersonId)
            ->dateRange($startDate, $endDate)
            ->select([
                'date',
                'profile_views_count as profile_views',
                'contact_requests_count as contact_requests',
                'unique_visitors_count as unique_visitors',
            ])
            ->orderBy('date', 'asc')
            ->get();
    }
}
```

### Model Factory 程式碼

**檔案位置**: `database/factories/DailyAnalyticsFactory.php`

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DailyAnalytics;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyAnalytics>
 */
class DailyAnalyticsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = DailyAnalytics::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'salesperson_id' => User::factory()->create(['role' => 'salesperson']),
            'date' => $this->faker->date(),
            'profile_views_count' => $this->faker->numberBetween(0, 100),
            'contact_requests_count' => $this->faker->numberBetween(0, 10),
            'unique_visitors_count' => $this->faker->numberBetween(0, 80),
        ];
    }

    /**
     * Indicate that the analytics is for today.
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'date' => now()->toDateString(),
            ];
        });
    }

    /**
     * Indicate that the analytics is for yesterday.
     */
    public function yesterday(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'date' => now()->subDay()->toDateString(),
            ];
        });
    }

    /**
     * Indicate that the analytics has no activity.
     */
    public function noActivity(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'profile_views_count' => 0,
                'contact_requests_count' => 0,
                'unique_visitors_count' => 0,
            ];
        });
    }

    /**
     * Indicate that the analytics has high activity.
     */
    public function highActivity(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'profile_views_count' => $this->faker->numberBetween(100, 500),
                'contact_requests_count' => $this->faker->numberBetween(10, 50),
                'unique_visitors_count' => $this->faker->numberBetween(80, 400),
            ];
        });
    }
}
```

### 資料量估算

**第一年預估**:
- 業務員數量: 150 人
- 每日記錄: 150 筆
- 每年記錄: 150 × 365 = 54,750 筆
- 資料大小: ~5 MB (每筆約 100 bytes)

**第三年預估**:
- 業務員數量: 500 人
- 每年記錄: 500 × 365 = 182,500 筆
- 累計記錄: 54,750 + 182,500 + 182,500 = 419,750 筆
- 資料大小: ~40 MB

**效能影響**:
- 查詢效能: 優秀 (有索引，資料量小)
- 寫入效能: 優秀 (每日只寫入一次)
- 儲存空間: 小 (3 年約 40 MB)

---

## 📋 現有資料表: contact_events

### 用途

記錄所有事件（檔案瀏覽、聯繫表單提交），用於即時查詢和彙總來源。

### 相關欄位 (已存在)

| 欄位名 | 型別 | 說明 |
|--------|------|------|
| `id` | BIGINT UNSIGNED | 主鍵 |
| `user_id` | BIGINT UNSIGNED | 使用者 ID (可為 null，匿名瀏覽) |
| `salesperson_id` | BIGINT UNSIGNED | 業務員 ID |
| `event_type` | ENUM | 事件類型 ('profile_view', 'contact_form_submission') |
| `ip_address_hash` | CHAR(64) | IP 地址 SHA256 雜湊 |
| `user_agent` | STRING | User Agent |
| `created_at` | TIMESTAMP | 建立時間 |

### 現有索引 (已存在)

```sql
INDEX idx_salesperson_type_created (salesperson_id, event_type, created_at)
INDEX idx_user_type_created (user_id, event_type, created_at)
INDEX idx_event_type_created (event_type, created_at)
INDEX idx_ip_address_hash (ip_address_hash)
```

### Analytics 使用方式

**查詢今日瀏覽數**:
```sql
SELECT COUNT(*)
FROM contact_events
WHERE salesperson_id = ?
  AND event_type = 'profile_view'
  AND DATE(created_at) = CURDATE();
```

**查詢今日獨立訪客數**:
```sql
SELECT COUNT(DISTINCT ip_address_hash)
FROM contact_events
WHERE salesperson_id = ?
  AND event_type = 'profile_view'
  AND DATE(created_at) = CURDATE();
```

---

## 📋 現有資料表: contact_requests

### 用途

儲存聯繫請求詳細資料（客戶姓名、Email、訊息等）。

### 相關欄位 (已存在)

| 欄位名 | 型別 | 說明 |
|--------|------|------|
| `id` | BIGINT UNSIGNED | 主鍵 |
| `user_id` | BIGINT UNSIGNED | 使用者 ID (可為 null) |
| `salesperson_id` | BIGINT UNSIGNED | 業務員 ID |
| `customer_name` | VARCHAR(100) | 客戶姓名 |
| `customer_email` | TEXT | 客戶 Email (加密) |
| `customer_phone` | TEXT | 客戶電話 (加密) |
| `message` | TEXT | 聯繫訊息 |
| `status` | ENUM | 狀態 ('pending', 'contacted', 'closed') |
| `created_at` | TIMESTAMP | 建立時間 |

### 現有索引 (已存在)

```sql
INDEX idx_salesperson_status_created (salesperson_id, status, created_at)
INDEX idx_user_salesperson_created (user_id, salesperson_id, created_at)
INDEX idx_created_at (created_at)
```

### Analytics 使用方式

**查詢今日聯繫請求數**:
```sql
SELECT COUNT(*)
FROM contact_requests
WHERE salesperson_id = ?
  AND DATE(created_at) = CURDATE();
```

**查詢最近 10 筆聯繫請求**:
```sql
SELECT *
FROM contact_requests
WHERE salesperson_id = ?
ORDER BY created_at DESC
LIMIT 10;
```

---

## 🔍 查詢範例

### 業務員統計 (混合查詢)

```php
// 1. 查詢歷史彙總數據 (昨天及更早)
$historicalStats = DailyAnalytics::forSalesperson($salespersonId)
    ->dateRange($startDate, $yesterday)
    ->selectRaw('
        SUM(profile_views_count) as profile_views,
        SUM(contact_requests_count) as contact_requests,
        SUM(unique_visitors_count) as unique_visitors
    ')
    ->first();

// 2. 查詢今日即時數據
$todayViews = ContactEvent::where('salesperson_id', $salespersonId)
    ->where('event_type', 'profile_view')
    ->whereDate('created_at', now())
    ->count();

$todayContacts = ContactRequest::where('salesperson_id', $salespersonId)
    ->whereDate('created_at', now())
    ->count();

$todayVisitors = ContactEvent::where('salesperson_id', $salespersonId)
    ->where('event_type', 'profile_view')
    ->whereDate('created_at', now())
    ->distinct('ip_address_hash')
    ->count('ip_address_hash');

// 3. 合併數據
$totalViews = ($historicalStats->profile_views ?? 0) + $todayViews;
$totalContacts = ($historicalStats->contact_requests ?? 0) + $todayContacts;
$totalVisitors = ($historicalStats->unique_visitors ?? 0) + $todayVisitors;
```

### 平台整體統計

```php
// 平台總瀏覽數 (過去 30 天)
$platformStats = DailyAnalytics::dateRange($startDate, $yesterday)
    ->selectRaw('
        SUM(profile_views_count) as total_views,
        SUM(contact_requests_count) as total_contacts,
        SUM(unique_visitors_count) as total_visitors
    ')
    ->first();

// 加上今日即時數據
$todayPlatformViews = ContactEvent::where('event_type', 'profile_view')
    ->whereDate('created_at', now())
    ->count();

$todayPlatformContacts = ContactRequest::whereDate('created_at', now())
    ->count();
```

### Top 10 業務員

```php
// 查詢過去 30 天的 Top 10 業務員
$topSalespersons = DailyAnalytics::dateRange($startDate, $yesterday)
    ->select('salesperson_id')
    ->selectRaw('SUM(profile_views_count) as total_views')
    ->selectRaw('SUM(contact_requests_count) as total_contacts')
    ->selectRaw('SUM(unique_visitors_count) as total_visitors')
    ->groupBy('salesperson_id')
    ->orderByDesc('total_views')
    ->limit(10)
    ->with('salesperson:id,name,email')
    ->get();

// 加上今日即時數據 (需額外查詢合併)
```

---

## 📊 資料完整性與一致性

### UNIQUE 約束

- `(salesperson_id, date)` - 確保每個業務員每天只有一筆記錄

### FOREIGN KEY 約束

- `salesperson_id → users.id` (ON DELETE CASCADE)
  - 刪除業務員時，自動刪除相關彙總數據

### 資料一致性檢查

**每日彙總後驗證**:
```php
// 驗證昨日彙總數據是否與即時查詢一致
$aggregatedCount = DailyAnalytics::where('salesperson_id', $id)
    ->where('date', $yesterday)
    ->value('profile_views_count');

$actualCount = ContactEvent::where('salesperson_id', $id)
    ->where('event_type', 'profile_view')
    ->whereDate('created_at', $yesterday)
    ->count();

if ($aggregatedCount !== $actualCount) {
    // 記錄不一致錯誤，發送告警
    Log::error('Daily analytics data inconsistency', [
        'salesperson_id' => $id,
        'date' => $yesterday,
        'aggregated' => $aggregatedCount,
        'actual' => $actualCount,
    ]);
}
```

---

## 🔒 安全性考量

### IP 地址保護

- IP 地址使用 SHA256 雜湊儲存 (`contact_events.ip_address_hash`)
- 不儲存原始 IP 地址
- 只用於去重統計，不用於追蹤個人

### 敏感資料加密

- `contact_requests.customer_email` - 使用 Laravel Encrypted Cast
- `contact_requests.customer_phone` - 使用 Laravel Encrypted Cast

### 資料存取控制

- 業務員只能查詢自己的 `salesperson_id` 記錄
- 管理員可以查詢所有記錄
- 使用 Policy 和 Middleware 強制執行

---

## 📈 效能優化

### 索引策略

- 複合索引 `(salesperson_id, date)` 支援時間範圍查詢
- 單欄索引 `date` 支援管理員全平台查詢
- 避免無索引的全表掃描

### 查詢優化

- 使用 `SUM()` 在資料庫層彙總，減少資料傳輸
- 使用 `whereDate()` 搭配索引快速篩選
- 避免 N+1 查詢 (使用 `with()` Eager Loading)

### 資料清理策略

**保留政策**:
- 保留 90 天的詳細數據
- 90 天後可考慮刪除 `contact_events` 表的歷史記錄
- `daily_analytics` 表永久保留 (資料量小)

**清理 Command** (可選):
```php
// 刪除 90 天前的 contact_events 記錄
ContactEvent::where('created_at', '<', now()->subDays(90))->delete();
```

---

## 🧪 測試資料

### 建立測試資料

```php
use App\Models\DailyAnalytics;
use App\Models\User;

// 建立業務員
$salesperson = User::factory()->create(['role' => 'salesperson']);

// 建立過去 30 天的彙總數據
for ($i = 1; $i <= 30; $i++) {
    DailyAnalytics::factory()->create([
        'salesperson_id' => $salesperson->id,
        'date' => now()->subDays($i)->toDateString(),
        'profile_views_count' => rand(10, 100),
        'contact_requests_count' => rand(1, 10),
        'unique_visitors_count' => rand(8, 80),
    ]);
}
```

---

## 📚 參考資料

- **Proposal**: `../proposal.md`
- **API 規格**: `./api.md`
- **業務規則規格**: `./business-rules.md`
- **系統架構規格**: `./architecture.md`
- **Laravel Migrations**: https://laravel.com/docs/11.x/migrations
- **Eloquent Relationships**: https://laravel.com/docs/11.x/eloquent-relationships

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**維護者**: Backend Team
