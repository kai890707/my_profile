# 系統架構規格文檔 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24

---

## 📋 概述

本文檔說明 Analytics Dashboard 功能的整體技術架構設計，包括：

- **混合彙總策略** - 為何選擇混合模式？如何平衡效能與即時性？
- **排程任務設計** - 每日彙總 Command 的設計與監控
- **查詢優化策略** - 資料庫索引、快取層設計
- **安全性架構** - Rate Limiting、權限控制、資料保護
- **擴展性設計** - 如何應對未來成長
- **監控與告警** - 如何確保系統健康

---

## 🏗️ 系統架構概覽

### 架構圖

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend (Next.js)                   │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────┐   │
│  │ Salesperson   │  │    Admin      │  │  React Query  │   │
│  │  Dashboard    │  │  Dashboard    │  │    Cache      │   │
│  └───────┬───────┘  └───────┬───────┘  └───────────────┘   │
└──────────┼──────────────────┼─────────────────────────────┘
           │                  │
           │ HTTPS/JWT        │ HTTPS/JWT
           ▼                  ▼
┌─────────────────────────────────────────────────────────────┐
│                  Backend API (Laravel 11)                   │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              API Layer (Controllers)                  │  │
│  │  ┌──────────────────┐  ┌──────────────────┐          │  │
│  │  │ Salesperson API  │  │   Admin API      │          │  │
│  │  └────────┬─────────┘  └────────┬─────────┘          │  │
│  └───────────┼────────────────────┼────────────────────┘  │
│              │                    │                        │
│  ┌───────────▼────────────────────▼────────────────────┐  │
│  │           Service Layer (Business Logic)            │  │
│  │  ┌──────────────────────────────────────┐           │  │
│  │  │  AnalyticsService                    │           │  │
│  │  │  ├─ getStats()                       │           │  │
│  │  │  ├─ getTrends()                      │           │  │
│  │  │  ├─ getRecentContacts()              │           │  │
│  │  │  ├─ getPlatformOverview()            │           │  │
│  │  │  └─ getTopSalespersons()             │           │  │
│  │  └──────────────────────────────────────┘           │  │
│  └───────────┬────────────────────┬────────────────────┘  │
│              │                    │                        │
│              │ Mixed Query        │ Cache (Optional)       │
│              ▼                    ▼                        │
│  ┌───────────────────────────────────────────────────┐    │
│  │         Data Access Layer (Models)                │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌─────────┐ │    │
│  │  │DailyAnalytics│  │ContactEvent  │  │ Redis   │ │    │
│  │  └──────┬───────┘  └──────┬───────┘  └─────────┘ │    │
│  └─────────┼──────────────────┼─────────────────────┘    │
└────────────┼──────────────────┼───────────────────────────┘
             │                  │
             ▼                  ▼
┌─────────────────────────────────────────────────────────────┐
│                    Database Layer (MySQL 8.0)               │
│  ┌───────────────────┐      ┌───────────────────┐          │
│  │ daily_analytics   │      │  contact_events   │          │
│  │ (歷史彙總數據)     │      │  (即時事件數據)    │          │
│  │                   │      │                   │          │
│  │ - salesperson_id  │      │ - salesperson_id  │          │
│  │ - date            │      │ - event_type      │          │
│  │ - profile_views   │      │ - ip_address_hash │          │
│  │ - contact_requests│      │ - created_at      │          │
│  │ - unique_visitors │      │                   │          │
│  └───────────────────┘      └───────────────────┘          │
│                                                             │
│              ┌───────────────────┐                         │
│              │ contact_requests  │                         │
│              │ (聯繫請求數據)     │                         │
│              │                   │                         │
│              │ - salesperson_id  │                         │
│              │ - customer_name   │                         │
│              │ - message         │                         │
│              │ - status          │                         │
│              │ - created_at      │                         │
│              └───────────────────┘                         │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    Scheduled Jobs (Cron)                    │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  AggregateDailyAnalytics Command                      │  │
│  │  ├─ 執行時間: 每日 02:00                               │  │
│  │  ├─ 來源: contact_events + contact_requests (昨日)    │  │
│  │  ├─ 目的地: daily_analytics 表                        │  │
│  │  └─ 監控: Log + Notification (失敗時)                 │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 混合彙總策略 (Hybrid Aggregation Strategy)

### 為什麼選擇混合模式？

**問題**: 如何平衡效能與即時性？

**選項比較**:

| 策略 | 優點 | 缺點 | 適用場景 |
|------|------|------|---------|
| **純即時查詢** | 數據完全即時 | 查詢慢（需掃描整個 contact_events 表） | 小型系統（< 10 萬筆記錄） |
| **純彙總查詢** | 查詢快速 | 數據有延遲（最多 24 小時） | 不需要即時數據的場景 |
| **混合模式** ✅ | 快速 + 即時 | 實作複雜度稍高 | 需要即時性且注重效能 |

**選擇理由**:
1. **歷史數據不會變** → 預先彙總到 `daily_analytics` 表，查詢速度快
2. **今日數據需即時** → 直接查詢 `contact_events` 表，確保即時性
3. **平衡效能與即時性** → 大部分查詢命中快速的彙總表，只有今日數據查詢即時表

### 混合模式設計

#### 資料分類

```
┌─────────────────────────────────────────────────┐
│               資料時間軸                         │
└─────────────────────────────────────────────────┘

Day -30  Day -7   Day -2   Day -1   Today
  │        │        │        │        │
  ▼        ▼        ▼        ▼        ▼
┌────────────────────────────┐ ┌──────────┐
│  daily_analytics 表        │ │contact   │
│  (歷史彙總數據)            │ │_events   │
│  - 查詢快速                │ │表        │
│  - 每日更新一次            │ │(即時數據)│
│  - 資料不可變              │ │          │
└────────────────────────────┘ └──────────┘
     ▲                             ▲
     │                             │
   昨天及更早                      今天
   (不會再變動)                   (持續變動)
```

#### 查詢流程

```php
public function getStats(int $salespersonId, string $range): array
{
    [$startDate, $endDate] = $this->calculateDateRange($range);
    $today = now()->toDateString();

    // 步驟 1: 查詢歷史彙總數據 (昨天及更早)
    $historicalStats = DailyAnalytics::forSalesperson($salespersonId)
        ->where('date', '>=', $startDate->toDateString())
        ->where('date', '<', $today)
        ->selectRaw('
            SUM(profile_views_count) as profile_views,
            SUM(contact_requests_count) as contact_requests,
            SUM(unique_visitors_count) as unique_visitors
        ')
        ->first();

    // 步驟 2: 查詢今日即時數據
    $todayProfileViews = ContactEvent::where('salesperson_id', $salespersonId)
        ->where('event_type', 'profile_view')
        ->whereDate('created_at', $today)
        ->count();

    $todayContactRequests = ContactRequest::where('salesperson_id', $salespersonId)
        ->whereDate('created_at', $today)
        ->count();

    $todayUniqueVisitors = ContactEvent::where('salesperson_id', $salespersonId)
        ->where('event_type', 'profile_view')
        ->whereDate('created_at', $today)
        ->distinct('ip_address_hash')
        ->count('ip_address_hash');

    // 步驟 3: 合併數據
    $totalProfileViews = ($historicalStats->profile_views ?? 0) + $todayProfileViews;
    $totalContactRequests = ($historicalStats->contact_requests ?? 0) + $todayContactRequests;
    $totalUniqueVisitors = ($historicalStats->unique_visitors ?? 0) + $todayUniqueVisitors;

    return [
        'profile_views' => $totalProfileViews,
        'contact_requests' => $totalContactRequests,
        'unique_visitors' => $totalUniqueVisitors,
        'conversion_rate' => $this->calculateConversionRate(
            $totalProfileViews,
            $totalContactRequests
        ),
    ];
}
```

#### 效能比較

**範例**: 查詢業務員過去 30 天數據

| 策略 | 查詢表 | 掃描記錄數 | 查詢時間 (估計) |
|------|--------|-----------|----------------|
| 純即時查詢 | contact_events | ~10,000 筆 | ~500ms |
| 混合模式 | daily_analytics (29 筆) + contact_events (今日約 50 筆) | ~80 筆 | ~50ms |

**效能提升**: 10 倍+

---

## 📅 排程任務設計 (Scheduled Jobs)

### 每日彙總 Command 設計

#### Command 類別結構

```php
<?php

namespace App\Console\Commands;

use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\DailyAnalytics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AggregateDailyAnalytics extends Command
{
    /**
     * Command signature
     */
    protected $signature = 'analytics:aggregate-daily
                            {--date= : Date to aggregate (YYYY-MM-DD), defaults to yesterday}
                            {--force : Force re-aggregation even if data exists}';

    /**
     * Command description
     */
    protected $description = 'Aggregate daily analytics from contact events and requests';

    /**
     * Execute the command
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Aggregating analytics for {$date->toDateString()}...");

        DB::beginTransaction();

        try {
            $this->aggregateForDate($date);
            DB::commit();

            $this->info('✅ Daily analytics aggregated successfully!');
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Daily analytics aggregation failed', [
                'date' => $date->toDateString(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error("❌ Aggregation failed: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Aggregate analytics for a specific date
     */
    private function aggregateForDate(Carbon $date): void
    {
        $dateString = $date->toDateString();
        $salespeople = User::where('role', 'salesperson')
            ->where('salesperson_status', 'approved')
            ->get();

        $progressBar = $this->output->createProgressBar($salespeople->count());
        $progressBar->start();

        foreach ($salespeople as $salesperson) {
            $this->aggregateForSalesperson($salesperson->id, $dateString);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Aggregate analytics for a specific salesperson and date
     */
    private function aggregateForSalesperson(int $salespersonId, string $date): void
    {
        $profileViews = ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $date)
            ->count();

        $contactRequests = ContactRequest::where('salesperson_id', $salespersonId)
            ->whereDate('created_at', $date)
            ->count();

        $uniqueVisitors = ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $date)
            ->distinct('ip_address_hash')
            ->count('ip_address_hash');

        DailyAnalytics::updateOrCreate(
            [
                'salesperson_id' => $salespersonId,
                'date' => $date,
            ],
            [
                'profile_views_count' => $profileViews,
                'contact_requests_count' => $contactRequests,
                'unique_visitors_count' => $uniqueVisitors,
            ]
        );
    }
}
```

#### 排程設定

**檔案**: `app/Console/Kernel.php`

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('analytics:aggregate-daily')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->sendOutputTo(storage_path('logs/analytics-aggregation.log'))
                 ->emailOutputOnFailure(config('app.admin_email'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

**排程參數說明**:
- `dailyAt('02:00')` - 每日凌晨 2:00 執行（避開高峰期）
- `withoutOverlapping()` - 避免重複執行（若上次任務未完成）
- `onOneServer()` - 多伺服器環境只在一台執行（需要 Redis 或資料庫鎖）
- `sendOutputTo()` - 輸出日誌到檔案
- `emailOutputOnFailure()` - 失敗時發送 Email 通知

### 錯誤處理與重試邏輯

#### 錯誤情境處理

| 錯誤情境 | 處理方式 | 降級策略 |
|---------|---------|---------|
| 資料庫連接失敗 | 記錄錯誤、發送通知、返回 exit code 1 | API 使用純即時查詢（效能較慢） |
| 部分業務員失敗 | 記錄失敗的業務員 ID、繼續處理其他業務員 | 失敗的業務員使用即時查詢 |
| 資料不一致 | 記錄警告、繼續執行 | 提供手動修復指令 |
| 執行超時 | 使用 timeout 限制、分批處理 | 下次執行時補齊 |

#### 手動重試指令

```bash
# 重新彙總昨日數據
php artisan analytics:aggregate-daily

# 彙總指定日期數據
php artisan analytics:aggregate-daily --date=2026-01-20

# 強制重新彙總（即使已存在）
php artisan analytics:aggregate-daily --date=2026-01-20 --force
```

### 執行監控

#### 監控指標

1. **執行狀態**
   - 執行成功/失敗
   - 執行時間
   - 處理業務員數量

2. **資料品質**
   - 彙總數據與即時查詢的一致性
   - 缺失的日期（若有）

3. **效能指標**
   - 平均每個業務員處理時間
   - 總執行時間

#### 監控實作

```php
// 在 Command 中加入監控
use Illuminate\Support\Facades\Cache;

public function handle(): int
{
    $startTime = microtime(true);

    try {
        // 執行彙總...

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        // 記錄成功指標
        Cache::put('analytics:last_aggregation', [
            'status' => 'success',
            'date' => $date->toDateString(),
            'execution_time_ms' => $executionTime,
            'salespeople_count' => $salespeople->count(),
            'timestamp' => now()->toIso8601String(),
        ], now()->addDays(7));

        Log::info('Daily analytics aggregation completed', [
            'date' => $date->toDateString(),
            'execution_time_ms' => $executionTime,
            'salespeople_count' => $salespeople->count(),
        ]);

        return 0;
    } catch (\Exception $e) {
        // 記錄失敗指標
        Cache::put('analytics:last_aggregation', [
            'status' => 'failed',
            'date' => $date->toDateString(),
            'error' => $e->getMessage(),
            'timestamp' => now()->toIso8601String(),
        ], now()->addDays(7));

        return 1;
    }
}
```

#### 監控 Dashboard (可選)

建立一個管理員專用的監控頁面：

```php
// GET /api/admin/analytics/health

{
  "last_aggregation": {
    "status": "success",
    "date": "2026-01-23",
    "execution_time_ms": 1234.56,
    "salespeople_count": 148,
    "timestamp": "2026-01-24T02:00:15Z"
  },
  "next_run": "2026-01-25T02:00:00Z",
  "data_consistency": {
    "status": "ok",
    "last_check": "2026-01-24T02:00:30Z"
  }
}
```

---

## 🚀 查詢優化策略 (Query Optimization)

### 資料庫索引策略

#### daily_analytics 表索引

```sql
-- 主鍵索引（自動建立）
PRIMARY KEY (id)

-- 唯一複合索引（防止重複彙總）
UNIQUE KEY unique_salesperson_date (salesperson_id, date)

-- 複合索引（業務員時間範圍查詢）
INDEX idx_salesperson_date (salesperson_id, date)

-- 單欄索引（管理員全平台查詢）
INDEX idx_date (date)
```

**索引使用範例**:

```sql
-- 使用 idx_salesperson_date 索引
SELECT SUM(profile_views_count)
FROM daily_analytics
WHERE salesperson_id = 45
  AND date BETWEEN '2026-01-01' AND '2026-01-30';

-- 使用 idx_date 索引
SELECT salesperson_id, SUM(profile_views_count) as total_views
FROM daily_analytics
WHERE date BETWEEN '2026-01-01' AND '2026-01-30'
GROUP BY salesperson_id
ORDER BY total_views DESC
LIMIT 10;
```

#### contact_events 表索引（已存在）

```sql
-- 複合索引（業務員 + 事件類型 + 時間）
INDEX idx_salesperson_type_created (salesperson_id, event_type, created_at)

-- 複合索引（事件類型 + 時間）
INDEX idx_event_type_created (event_type, created_at)

-- 單欄索引（IP 去重）
INDEX idx_ip_address_hash (ip_address_hash)
```

**索引使用範例**:

```sql
-- 使用 idx_salesperson_type_created 索引
SELECT COUNT(*)
FROM contact_events
WHERE salesperson_id = 45
  AND event_type = 'profile_view'
  AND DATE(created_at) = '2026-01-24';

-- 使用 idx_ip_address_hash 索引（配合上面的複合索引）
SELECT COUNT(DISTINCT ip_address_hash)
FROM contact_events
WHERE salesperson_id = 45
  AND event_type = 'profile_view'
  AND DATE(created_at) = '2026-01-24';
```

### 查詢優化技巧

#### 1. 使用 `selectRaw` 在資料庫層彙總

❌ **不佳實作**（在應用層彙總）:
```php
$records = DailyAnalytics::forSalesperson($salespersonId)
    ->dateRange($startDate, $endDate)
    ->get();

$totalViews = $records->sum('profile_views_count'); // 應用層彙總
```

✅ **良好實作**（在資料庫層彙總）:
```php
$stats = DailyAnalytics::forSalesperson($salespersonId)
    ->dateRange($startDate, $endDate)
    ->selectRaw('SUM(profile_views_count) as total_views')
    ->first();

$totalViews = $stats->total_views; // 資料庫已彙總
```

#### 2. 避免 N+1 查詢問題

❌ **不佳實作**（N+1 查詢）:
```php
$topSalespersons = DailyAnalytics::select('salesperson_id')
    ->groupBy('salesperson_id')
    ->orderByDesc('total_views')
    ->limit(10)
    ->get();

foreach ($topSalespersons as $record) {
    $salesperson = User::find($record->salesperson_id); // N+1 查詢
}
```

✅ **良好實作**（Eager Loading）:
```php
$topSalespersons = DailyAnalytics::select('salesperson_id')
    ->selectRaw('SUM(profile_views_count) as total_views')
    ->groupBy('salesperson_id')
    ->orderByDesc('total_views')
    ->limit(10)
    ->with('salesperson:id,name,email') // Eager Loading
    ->get();
```

#### 3. 使用 `whereDate` 搭配索引

✅ **良好實作**:
```php
ContactEvent::where('salesperson_id', $id)
    ->where('event_type', 'profile_view')
    ->whereDate('created_at', $today) // 使用 whereDate
    ->count();
```

**注意**: `whereDate()` 可能無法使用索引（因為函數），若效能有問題可改用：

```php
ContactEvent::where('salesperson_id', $id)
    ->where('event_type', 'profile_view')
    ->where('created_at', '>=', $today . ' 00:00:00')
    ->where('created_at', '<=', $today . ' 23:59:59')
    ->count();
```

### 快取層設計（可選）

#### 快取策略

| 資料類型 | 是否快取 | TTL | 理由 |
|---------|---------|-----|------|
| 歷史數據（昨天及更早） | ✅ 是 | 24 小時 | 資料不會再變動 |
| 今日數據 | ❌ 否 | - | 需要即時性 |
| 管理員平台概覽 | ✅ 是 | 5 分鐘 | 查詢成本高 |
| Top 10 業務員 | ✅ 是 | 15 分鐘 | 排名變化不頻繁 |

#### 快取實作範例

```php
use Illuminate\Support\Facades\Cache;

public function getStats(int $salespersonId, string $range): array
{
    $cacheKey = "analytics:stats:{$salespersonId}:{$range}";

    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($salespersonId, $range) {
        // 查詢邏輯...
        return $stats;
    });
}
```

**快取失效**:
- 每日彙總完成後，清除相關快取
- 新事件發生時，不清除快取（接受 5 分鐘延遲）

---

## 🔒 安全性架構 (Security Architecture)

### Rate Limiting (速率限制)

#### Laravel Rate Limiting 設定

**檔案**: `app/Http/Kernel.php`

```php
protected $middlewareGroups = [
    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

**檔案**: `app/Providers/RouteServiceProvider.php`

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    // 業務員端點: 60 requests/minute/user
    RateLimiter::for('salesperson', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()->id);
    });

    // 管理員端點: 120 requests/minute/user
    RateLimiter::for('admin', function (Request $request) {
        return Limit::perMinute(120)->by($request->user()->id);
    });
}
```

**路由套用**:

```php
// routes/api.php

Route::middleware(['auth:api', 'throttle:salesperson'])->group(function () {
    Route::get('/salesperson/analytics/stats', [SalespersonAnalyticsController::class, 'stats']);
    Route::get('/salesperson/analytics/trends', [SalespersonAnalyticsController::class, 'trends']);
});

Route::middleware(['auth:api', 'role:admin', 'throttle:admin'])->group(function () {
    Route::get('/admin/analytics/overview', [AdminAnalyticsController::class, 'overview']);
    Route::get('/admin/analytics/top-salespersons', [AdminAnalyticsController::class, 'topSalespersons']);
});
```

### 資料權限控制

#### Middleware 強制執行

```php
// app/Http/Middleware/EnsureUserRole.php

public function handle(Request $request, Closure $next, string $role)
{
    if ($request->user()->role !== $role) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'You do not have permission to access this resource',
            ],
        ], 403);
    }

    return $next($request);
}
```

#### Controller 層驗證

```php
// app/Http/Controllers/SalespersonAnalyticsController.php

public function stats(Request $request)
{
    $user = $request->user(); // 從 JWT 取得已認證使用者

    // 自動使用 JWT user_id，業務員無法查看他人數據
    $stats = $this->analyticsService->getStats($user->id, $request->input('range'));

    return response()->json(['data' => $stats]);
}
```

### 敏感資料保護

#### IP 地址保護

- 不儲存原始 IP 地址
- 使用 SHA256 雜湊 (`ip_address_hash`)
- 只用於去重統計，不用於追蹤個人

```php
// 儲存事件時
$ipHash = hash('sha256', $request->ip());

ContactEvent::create([
    'ip_address_hash' => $ipHash,
    // ...
]);
```

#### 聯繫請求加密

- `customer_email` 和 `customer_phone` 使用 Laravel Encrypted Cast

```php
// app/Models/ContactRequest.php

protected $casts = [
    'customer_email' => 'encrypted',
    'customer_phone' => 'encrypted',
];
```

---

## 📈 擴展性設計 (Scalability Design)

### 資料成長預估

**第一年**:
- 業務員數: 150 人
- 每日事件: ~1,500 筆 (平均每人 10 次瀏覽)
- 每年事件: ~550,000 筆
- `daily_analytics` 資料: 150 × 365 = 54,750 筆

**第三年**:
- 業務員數: 500 人
- 每日事件: ~5,000 筆
- 累計事件: ~2,000,000 筆
- `daily_analytics` 資料: ~550,000 筆

### 水平擴展策略

#### 資料庫讀寫分離

**當 QPS > 10,000 時**:

```
┌──────────────┐
│ Application  │
└──────┬───────┘
       │
   ┌───┴───┐
   │       │
   ▼       ▼
┌──────┐ ┌──────────┐
│Master│→│ Replica 1│ (讀)
│(寫)  │ │          │
└──────┘ └──────────┘
         ┌──────────┐
         │ Replica 2│ (讀)
         │          │
         └──────────┘
```

**Laravel 設定**:
```php
// config/database.php

'mysql' => [
    'write' => [
        'host' => env('DB_WRITE_HOST', '127.0.0.1'),
    ],
    'read' => [
        ['host' => env('DB_READ_HOST_1', '127.0.0.1')],
        ['host' => env('DB_READ_HOST_2', '127.0.0.1')],
    ],
    // ...
],
```

#### 應用層快取

**當資料庫負載 > 70% 時**:

```
┌──────────────┐
│ Application  │
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ Redis Cache  │ (歷史數據快取)
└──────┬───────┘
       │ (Cache Miss)
       ▼
┌──────────────┐
│ MySQL        │
└──────────────┘
```

### 資料清理策略

**當 `contact_events` 表 > 1000 萬筆時**:

```php
// 刪除 90 天前的詳細事件記錄（已彙總到 daily_analytics）
ContactEvent::where('created_at', '<', now()->subDays(90))->delete();
```

**保留策略**:
- `daily_analytics` - 永久保留（資料量小）
- `contact_events` - 保留 90 天（詳細事件）
- `contact_requests` - 永久保留（業務資料）

---

## 📊 監控與告警 (Monitoring & Alerting)

### 監控指標

#### 應用層指標

1. **API 效能**
   - P50/P95/P99 回應時間
   - QPS (Queries Per Second)
   - 錯誤率 (5xx)

2. **彙總任務**
   - 執行成功/失敗
   - 執行時間
   - 資料一致性

#### 資料庫層指標

1. **查詢效能**
   - Slow Query Log (> 1 秒)
   - 索引使用率
   - Table Scan 次數

2. **資源使用**
   - CPU 使用率
   - 記憶體使用率
   - 磁碟空間

### 告警設定

| 指標 | 閾值 | 動作 |
|------|------|------|
| API P95 回應時間 | > 500ms | Email 通知 |
| 彙總任務失敗 | 1 次 | Email + Slack 通知 |
| 資料不一致 | 發現不一致 | Log 警告 |
| 資料庫 CPU | > 80% | Email 通知 |
| Slow Query | > 10 次/小時 | Log 記錄 |

---

## 📚 參考資料

- **Proposal**: `../proposal.md`
- **API 規格**: `./api.md`
- **資料模型規格**: `./data-model.md`
- **業務規則規格**: `./business-rules.md`
- **Laravel Scheduling**: https://laravel.com/docs/11.x/scheduling
- **Laravel Rate Limiting**: https://laravel.com/docs/11.x/routing#rate-limiting
- **Database Optimization**: https://laravel.com/docs/11.x/queries#optimizing-queries

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**維護者**: Backend Team
