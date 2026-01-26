# 業務規則規格文檔 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24

---

## 📋 概述

本文檔定義 Analytics Dashboard 功能的所有業務邏輯規則，包括：

- **資料彙總規則** - 每日彙總執行邏輯
- **查詢規則** - 時間範圍處理、混合查詢邏輯
- **計算規則** - 增長率、轉換率計算公式
- **權限規則** - 資料存取控制
- **驗證規則** - 輸入驗證
- **錯誤處理規則** - 異常情境處理

每條規則都包含：規則編號、描述、實作方式、測試案例。

---

## 🔄 資料彙總規則 (Daily Aggregation Rules)

### BR-001: 每日彙總執行時機

**描述**: 每日凌晨 2:00 執行彙總任務，彙總昨日數據到 `daily_analytics` 表。

**實作方式**: Laravel Scheduler

**Cron 表達式**: `0 2 * * *` (每日 02:00)

**執行邏輯**:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('analytics:aggregate-daily')
             ->dailyAt('02:00')
             ->withoutOverlapping()
             ->onOneServer();
}
```

**測試案例**:
```php
test('daily aggregation command is scheduled at 2:00 AM', function () {
    $this->artisan('schedule:list')
        ->expectsOutput('analytics:aggregate-daily')
        ->expectsOutput('2:00');
});
```

---

### BR-002: 彙總計算邏輯

**描述**: 彙總昨日每個業務員的數據，計算瀏覽數、聯繫數、獨立訪客數。

**計算公式**:

1. **瀏覽數** (`profile_views_count`):
   ```sql
   SELECT COUNT(*)
   FROM contact_events
   WHERE salesperson_id = ?
     AND event_type = 'profile_view'
     AND DATE(created_at) = YESTERDAY
   ```

2. **聯繫數** (`contact_requests_count`):
   ```sql
   SELECT COUNT(*)
   FROM contact_requests
   WHERE salesperson_id = ?
     AND DATE(created_at) = YESTERDAY
   ```

3. **獨立訪客數** (`unique_visitors_count`):
   ```sql
   SELECT COUNT(DISTINCT ip_address_hash)
   FROM contact_events
   WHERE salesperson_id = ?
     AND event_type = 'profile_view'
     AND DATE(created_at) = YESTERDAY
   ```

**實作方式**: Artisan Command

```php
// app/Console/Commands/AggregateDailyAnalytics.php

$yesterday = Carbon::yesterday()->toDateString();
$salespeople = User::where('role', 'salesperson')
    ->where('salesperson_status', 'approved')
    ->get();

foreach ($salespeople as $salesperson) {
    $profileViews = ContactEvent::where('salesperson_id', $salesperson->id)
        ->where('event_type', 'profile_view')
        ->whereDate('created_at', $yesterday)
        ->count();

    $contactRequests = ContactRequest::where('salesperson_id', $salesperson->id)
        ->whereDate('created_at', $yesterday)
        ->count();

    $uniqueVisitors = ContactEvent::where('salesperson_id', $salesperson->id)
        ->where('event_type', 'profile_view')
        ->whereDate('created_at', $yesterday)
        ->distinct('ip_address_hash')
        ->count('ip_address_hash');

    DailyAnalytics::updateOrCreate(
        [
            'salesperson_id' => $salesperson->id,
            'date' => $yesterday,
        ],
        [
            'profile_views_count' => $profileViews,
            'contact_requests_count' => $contactRequests,
            'unique_visitors_count' => $uniqueVisitors,
        ]
    );
}
```

**測試案例**:
```php
test('daily aggregation calculates correct stats for yesterday', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);

    // 建立昨日數據
    ContactEvent::factory()->count(10)->create([
        'salesperson_id' => $salesperson->id,
        'event_type' => 'profile_view',
        'created_at' => now()->subDay(),
    ]);

    ContactRequest::factory()->count(3)->create([
        'salesperson_id' => $salesperson->id,
        'created_at' => now()->subDay(),
    ]);

    // 執行彙總
    $this->artisan('analytics:aggregate-daily')->assertExitCode(0);

    // 驗證結果
    $analytics = DailyAnalytics::where('salesperson_id', $salesperson->id)
        ->where('date', now()->subDay()->toDateString())
        ->first();

    expect($analytics->profile_views_count)->toBe(10);
    expect($analytics->contact_requests_count)->toBe(3);
});
```

---

### BR-003: 重複執行處理 (UPSERT 邏輯)

**描述**: 若彙總任務重複執行（如手動觸發），使用 `updateOrCreate` 避免重複記錄。

**實作方式**: Eloquent `updateOrCreate`

```php
DailyAnalytics::updateOrCreate(
    [
        'salesperson_id' => $salesperson->id,
        'date' => $yesterday,
    ],
    [
        'profile_views_count' => $profileViews,
        'contact_requests_count' => $contactRequests,
        'unique_visitors_count' => $uniqueVisitors,
    ]
);
```

**資料庫保證**: `UNIQUE KEY (salesperson_id, date)` 確保唯一性

**測試案例**:
```php
test('daily aggregation can run multiple times without duplicates', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);

    // 執行兩次彙總
    $this->artisan('analytics:aggregate-daily')->assertExitCode(0);
    $this->artisan('analytics:aggregate-daily')->assertExitCode(0);

    // 驗證只有一筆記錄
    $count = DailyAnalytics::where('salesperson_id', $salesperson->id)
        ->where('date', now()->subDay()->toDateString())
        ->count();

    expect($count)->toBe(1);
});
```

---

### BR-004: 手動觸發彙總

**描述**: 提供手動執行彙總的指令，支援指定日期。

**Command 簽名**: `php artisan analytics:aggregate-daily {--date=}`

**實作方式**:
```php
// app/Console/Commands/AggregateDailyAnalytics.php

protected $signature = 'analytics:aggregate-daily {--date= : Date to aggregate (YYYY-MM-DD)}';

public function handle()
{
    $date = $this->option('date')
        ? Carbon::parse($this->option('date'))
        : Carbon::yesterday();

    // 執行彙總邏輯...
}
```

**使用範例**:
```bash
# 彙總昨日數據
php artisan analytics:aggregate-daily

# 彙總指定日期數據
php artisan analytics:aggregate-daily --date=2026-01-20
```

**測試案例**:
```php
test('can manually aggregate specific date', function () {
    $this->artisan('analytics:aggregate-daily', ['--date' => '2026-01-20'])
        ->assertExitCode(0);

    $exists = DailyAnalytics::whereDate('date', '2026-01-20')->exists();
    expect($exists)->toBeTrue();
});
```

---

## 🔍 查詢規則 (Query Rules)

### BR-005: 時間範圍處理

**描述**: 支援三種時間範圍 (`today`, `7days`, `30days`)，計算對應的開始日期和結束日期。

**計算邏輯**:

| 範圍 | 開始日期 | 結束日期 | 說明 |
|------|---------|---------|------|
| `today` | 今日 00:00 | 現在 | 只包含今日數據 |
| `7days` | 7 天前 00:00 | 現在 | 包含今日在內的 7 天 |
| `30days` | 30 天前 00:00 | 現在 | 包含今日在內的 30 天 |

**實作方式**:
```php
private function calculateDateRange(string $range): array
{
    $endDate = now();

    $startDate = match ($range) {
        'today' => now()->startOfDay(),
        '7days' => now()->subDays(6)->startOfDay(), // 今日 + 過去 6 天 = 7 天
        '30days' => now()->subDays(29)->startOfDay(), // 今日 + 過去 29 天 = 30 天
        default => now()->subDays(6)->startOfDay(),
    };

    return [$startDate, $endDate];
}
```

**測試案例**:
```php
test('today range returns today only', function () {
    Carbon::setTestNow('2026-01-24 15:30:00');

    [$start, $end] = calculateDateRange('today');

    expect($start->toDateString())->toBe('2026-01-24');
    expect($end->toDateString())->toBe('2026-01-24');
});

test('7days range returns last 7 days including today', function () {
    Carbon::setTestNow('2026-01-24 15:30:00');

    [$start, $end] = calculateDateRange('7days');

    expect($start->toDateString())->toBe('2026-01-18'); // 7 天前
    expect($end->toDateString())->toBe('2026-01-24');
});
```

---

### BR-006: 混合查詢策略

**描述**: 歷史數據查詢 `daily_analytics` 表，今日數據查詢 `contact_events` 表，在 API 層合併。

**實作方式**:

```php
public function getStats(int $salespersonId, string $range): array
{
    [$startDate, $endDate] = $this->calculateDateRange($range);
    $today = now()->toDateString();

    // 1. 查詢歷史彙總數據 (昨天及更早)
    $historicalStats = DailyAnalytics::forSalesperson($salespersonId)
        ->where('date', '>=', $startDate->toDateString())
        ->where('date', '<', $today)
        ->selectRaw('
            SUM(profile_views_count) as profile_views,
            SUM(contact_requests_count) as contact_requests,
            SUM(unique_visitors_count) as unique_visitors
        ')
        ->first();

    // 2. 查詢今日即時數據
    $todayStats = [
        'profile_views' => ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->count(),
        'contact_requests' => ContactRequest::where('salesperson_id', $salespersonId)
            ->whereDate('created_at', $today)
            ->count(),
        'unique_visitors' => ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->distinct('ip_address_hash')
            ->count('ip_address_hash'),
    ];

    // 3. 合併數據
    return [
        'profile_views' => ($historicalStats->profile_views ?? 0) + $todayStats['profile_views'],
        'contact_requests' => ($historicalStats->contact_requests ?? 0) + $todayStats['contact_requests'],
        'unique_visitors' => ($historicalStats->unique_visitors ?? 0) + $todayStats['unique_visitors'],
    ];
}
```

**測試案例**:
```php
test('mixed query strategy combines historical and today data', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);

    // 歷史數據 (昨日)
    DailyAnalytics::factory()->create([
        'salesperson_id' => $salesperson->id,
        'date' => now()->subDay()->toDateString(),
        'profile_views_count' => 50,
    ]);

    // 今日數據
    ContactEvent::factory()->count(10)->create([
        'salesperson_id' => $salesperson->id,
        'event_type' => 'profile_view',
        'created_at' => now(),
    ]);

    // 查詢 7 天數據
    $stats = $this->getStats($salesperson->id, '7days');

    expect($stats['profile_views'])->toBe(60); // 50 + 10
});
```

---

### BR-007: 趨勢數據填充零值

**描述**: 若某日無數據，該日數值填充為 0（不跳過該日期），確保圖表連續。

**實作方式**:

```php
public function getTrends(int $salespersonId, string $range): array
{
    [$startDate, $endDate] = $this->calculateDateRange($range);
    $today = now()->toDateString();

    // 1. 查詢歷史彙總數據
    $historicalTrends = DailyAnalytics::forSalesperson($salespersonId)
        ->where('date', '>=', $startDate->toDateString())
        ->where('date', '<', $today)
        ->get()
        ->keyBy('date');

    // 2. 查詢今日即時數據
    $todayData = [
        'date' => $today,
        'profile_views' => ContactEvent::where('salesperson_id', $salespersonId)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->count(),
        // ...
    ];

    // 3. 填充所有日期
    $trends = [];
    $currentDate = $startDate->copy();

    while ($currentDate->lte($endDate)) {
        $dateString = $currentDate->toDateString();

        if ($dateString === $today) {
            $trends[] = $todayData;
        } elseif (isset($historicalTrends[$dateString])) {
            $trends[] = [
                'date' => $dateString,
                'profile_views' => $historicalTrends[$dateString]->profile_views_count,
                // ...
            ];
        } else {
            // 填充零值
            $trends[] = [
                'date' => $dateString,
                'profile_views' => 0,
                'contact_requests' => 0,
                'unique_visitors' => 0,
            ];
        }

        $currentDate->addDay();
    }

    return $trends;
}
```

**測試案例**:
```php
test('trends fill missing dates with zero values', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);

    // 只建立部分日期的數據 (第 1 天和第 3 天)
    DailyAnalytics::factory()->create([
        'salesperson_id' => $salesperson->id,
        'date' => now()->subDays(6)->toDateString(),
        'profile_views_count' => 10,
    ]);

    DailyAnalytics::factory()->create([
        'salesperson_id' => $salesperson->id,
        'date' => now()->subDays(4)->toDateString(),
        'profile_views_count' => 20,
    ]);

    // 查詢 7 天趨勢
    $trends = $this->getTrends($salesperson->id, '7days');

    expect($trends)->toHaveCount(7); // 包含所有 7 天
    expect($trends[1]['profile_views'])->toBe(0); // 第 2 天無數據，填充 0
});
```

---

## 🧮 計算規則 (Calculation Rules)

### BR-008: 轉換率計算

**描述**: 轉換率 = (聯繫數 / 瀏覽數) × 100

**公式**:
```
conversion_rate = (contact_requests / profile_views) × 100
```

**特殊情況**:
- 若 `profile_views = 0`，轉換率為 `0.00`（避免除以零錯誤）

**實作方式**:
```php
$conversionRate = $profileViews > 0
    ? round(($contactRequests / $profileViews) * 100, 2)
    : 0.00;
```

**測試案例**:
```php
test('conversion rate is calculated correctly', function () {
    $rate = calculateConversionRate(100, 8); // 100 瀏覽, 8 聯繫
    expect($rate)->toBe(8.00);
});

test('conversion rate is zero when no views', function () {
    $rate = calculateConversionRate(0, 5);
    expect($rate)->toBe(0.00);
});
```

---

### BR-009: 增長率計算

**描述**: 增長率 = ((當前 - 上個時段) / 上個時段) × 100

**公式**:
```
growth_percent = ((current - previous) / previous) × 100
```

**特殊情況**:
- 若 `previous = 0`，增長率為 `null`（避免除以零，且無法定義增長）

**實作方式**:
```php
$growthPercent = $previous > 0
    ? round((($current - $previous) / $previous) * 100, 2)
    : null;
```

**測試案例**:
```php
test('growth rate is calculated correctly for positive growth', function () {
    $growth = calculateGrowthRate(120, 100); // 從 100 增長到 120
    expect($growth)->toBe(20.00); // 增長 20%
});

test('growth rate is calculated correctly for negative growth', function () {
    $growth = calculateGrowthRate(80, 100); // 從 100 下降到 80
    expect($growth)->toBe(-20.00); // 下降 20%
});

test('growth rate is null when previous is zero', function () {
    $growth = calculateGrowthRate(50, 0);
    expect($growth)->toBeNull();
});
```

---

### BR-010: 上個時段計算

**描述**: 根據查詢範圍，計算對應的上個時段。

**計算邏輯**:

| 當前範圍 | 上個時段 | 計算方式 |
|---------|---------|---------|
| `today` | 昨天 | 前 1 天 |
| `7days` | 前 7 天 | day -14 to day -8 |
| `30days` | 前 30 天 | day -60 to day -31 |

**實作方式**:
```php
private function calculatePreviousPeriod(string $range): array
{
    return match ($range) {
        'today' => [
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay(),
        ],
        '7days' => [
            now()->subDays(14)->startOfDay(),
            now()->subDays(8)->startOfDay(),
        ],
        '30days' => [
            now()->subDays(60)->startOfDay(),
            now()->subDays(31)->startOfDay(),
        ],
        default => [
            now()->subDays(14)->startOfDay(),
            now()->subDays(8)->startOfDay(),
        ],
    };
}
```

**測試案例**:
```php
test('previous period for today is yesterday', function () {
    Carbon::setTestNow('2026-01-24');

    [$start, $end] = calculatePreviousPeriod('today');

    expect($start->toDateString())->toBe('2026-01-23');
    expect($end->toDateString())->toBe('2026-01-23');
});

test('previous period for 7days is day -14 to day -8', function () {
    Carbon::setTestNow('2026-01-24');

    [$start, $end] = calculatePreviousPeriod('7days');

    expect($start->toDateString())->toBe('2026-01-10'); // 14 天前
    expect($end->toDateString())->toBe('2026-01-16'); // 8 天前
});
```

---

## 🔐 權限規則 (Authorization Rules)

### BR-011: 業務員只能查看自己的數據

**描述**: 業務員呼叫 API 時，自動從 JWT Token 取得 `user_id`，只能查詢自己的數據。

**實作方式**: Middleware + Controller

```php
// Controller
public function salespersonStats(Request $request)
{
    $user = $request->user(); // 從 JWT 取得已認證使用者

    // 只查詢自己的數據
    $stats = $this->analyticsService->getStats($user->id, $request->input('range'));

    return response()->json(['data' => $stats]);
}
```

**禁止行為**:
- 業務員不能在 URL 或 Query Parameter 指定其他業務員 ID
- 若嘗試查看他人數據，返回 403 Forbidden

**測試案例**:
```php
test('salesperson can only view their own analytics', function () {
    $salesperson1 = User::factory()->create(['role' => 'salesperson']);
    $salesperson2 = User::factory()->create(['role' => 'salesperson']);

    // salesperson1 登入
    $token = auth()->login($salesperson1);

    // 嘗試查詢自己的數據 → 成功
    $response = $this->withToken($token)
        ->getJson('/api/salesperson/analytics/stats');

    $response->assertStatus(200);

    // 無法透過任何方式查看 salesperson2 的數據 (系統自動使用 JWT user_id)
});
```

---

### BR-012: 管理員可以查看所有數據

**描述**: 管理員端點只有 `admin` 角色可以存取，可以查看所有業務員的數據。

**實作方式**: Middleware

```php
// routes/api.php
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/analytics/overview', [AdminAnalyticsController::class, 'overview']);
    Route::get('/admin/analytics/top-salespersons', [AdminAnalyticsController::class, 'topSalespersons']);
    // ...
});
```

**測試案例**:
```php
test('non-admin cannot access admin analytics endpoints', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);
    $token = auth()->login($salesperson);

    $response = $this->withToken($token)
        ->getJson('/api/admin/analytics/overview');

    $response->assertStatus(403);
});

test('admin can access admin analytics endpoints', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = auth()->login($admin);

    $response = $this->withToken($token)
        ->getJson('/api/admin/analytics/overview');

    $response->assertStatus(200);
});
```

---

## ✅ 驗證規則 (Validation Rules)

### BR-013: Range 參數驗證

**描述**: `range` 參數只接受 `today`, `7days`, `30days` 三種值。

**驗證規則**:
```php
$request->validate([
    'range' => ['nullable', 'string', 'in:today,7days,30days'],
]);
```

**錯誤回應**:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The selected range is invalid.",
    "details": {
      "range": ["The selected range is invalid."]
    }
  }
}
```

**測試案例**:
```php
test('range parameter accepts valid values', function () {
    $response = $this->getJson('/api/salesperson/analytics/stats?range=7days');
    $response->assertStatus(200);
});

test('range parameter rejects invalid values', function () {
    $response = $this->getJson('/api/salesperson/analytics/stats?range=invalid');
    $response->assertStatus(400)
        ->assertJsonPath('error.code', 'VALIDATION_ERROR');
});
```

---

## ❌ 錯誤處理規則 (Error Handling Rules)

### BR-014: 無數據時返回零值

**描述**: 若業務員尚無任何數據，API 返回零值（不返回錯誤）。

**實作方式**:
```php
$stats = [
    'profile_views' => $result->profile_views ?? 0,
    'contact_requests' => $result->contact_requests ?? 0,
    'unique_visitors' => $result->unique_visitors ?? 0,
    'conversion_rate' => 0.00,
];
```

**測試案例**:
```php
test('returns zero values when no data exists', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);
    $token = auth()->login($salesperson);

    $response = $this->withToken($token)
        ->getJson('/api/salesperson/analytics/stats');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'profile_views' => 0,
                'contact_requests' => 0,
                'unique_visitors' => 0,
            ],
        ]);
});
```

---

### BR-015: 彙總任務失敗處理

**描述**: 若每日彙總任務失敗，記錄錯誤日誌並發送通知。

**實作方式**:
```php
// app/Console/Commands/AggregateDailyAnalytics.php

public function handle()
{
    try {
        // 執行彙總邏輯...

        $this->info('Daily analytics aggregated successfully!');
    } catch (\Exception $e) {
        Log::error('Daily analytics aggregation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        // 發送通知給管理員
        Notification::route('mail', config('app.admin_email'))
            ->notify(new DailyAggregationFailed($e));

        $this->error('Aggregation failed: ' . $e->getMessage());
        return 1; // Exit code 1 表示失敗
    }
}
```

**降級策略**:
- 若彙總失敗，前端 API 仍可運作（使用即時查詢，效能較慢但數據正確）

**測試案例**:
```php
test('aggregation failure is logged and notified', function () {
    Log::shouldReceive('error')->once();
    Notification::fake();

    // 模擬資料庫連接失敗
    DB::shouldReceive('table')->andThrow(new \Exception('Database error'));

    $this->artisan('analytics:aggregate-daily')
        ->assertExitCode(1);

    Notification::assertSentTo(
        [new AnonymousNotifiable],
        DailyAggregationFailed::class
    );
});
```

---

### BR-016: 資料一致性檢查

**描述**: 彙總後驗證數據是否與即時查詢一致，若不一致記錄錯誤。

**實作方式**:
```php
// 彙總後驗證
$aggregatedCount = DailyAnalytics::where('salesperson_id', $id)
    ->where('date', $yesterday)
    ->value('profile_views_count');

$actualCount = ContactEvent::where('salesperson_id', $id)
    ->where('event_type', 'profile_view')
    ->whereDate('created_at', $yesterday)
    ->count();

if ($aggregatedCount !== $actualCount) {
    Log::warning('Data inconsistency detected', [
        'salesperson_id' => $id,
        'date' => $yesterday,
        'aggregated' => $aggregatedCount,
        'actual' => $actualCount,
        'difference' => abs($aggregatedCount - $actualCount),
    ]);
}
```

**測試案例**:
```php
test('data consistency check detects inconsistency', function () {
    Log::shouldReceive('warning')->once();

    // 建立不一致的數據 (手動修改彙總結果)
    // ...

    $this->artisan('analytics:verify-consistency')
        ->assertExitCode(0);
});
```

---

## 📊 特殊情境規則

### BR-017: 業務員被刪除處理

**描述**: 業務員被刪除時，`daily_analytics` 記錄也會被刪除 (ON DELETE CASCADE)。

**實作方式**: 外鍵約束

```sql
FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
```

**測試案例**:
```php
test('daily analytics are deleted when salesperson is deleted', function () {
    $salesperson = User::factory()->create(['role' => 'salesperson']);

    DailyAnalytics::factory()->create([
        'salesperson_id' => $salesperson->id,
    ]);

    // 刪除業務員
    $salesperson->delete();

    // 驗證彙總數據也被刪除
    $exists = DailyAnalytics::where('salesperson_id', $salesperson->id)->exists();
    expect($exists)->toBeFalse();
});
```

---

### BR-018: 只統計 Approved 業務員

**描述**: 彙總和查詢只包含 `salesperson_status = 'approved'` 的業務員。

**實作方式**:
```php
// 彙總時
$salespeople = User::where('role', 'salesperson')
    ->where('salesperson_status', 'approved')
    ->get();

// 管理員平台概覽
$totalSalespersons = User::where('role', 'salesperson')
    ->where('salesperson_status', 'approved')
    ->count();
```

**測試案例**:
```php
test('only approved salespersons are included in aggregation', function () {
    $approved = User::factory()->create([
        'role' => 'salesperson',
        'salesperson_status' => 'approved',
    ]);

    $pending = User::factory()->create([
        'role' => 'salesperson',
        'salesperson_status' => 'pending',
    ]);

    $this->artisan('analytics:aggregate-daily')->assertExitCode(0);

    // 只有 approved 業務員有彙總數據
    expect(DailyAnalytics::where('salesperson_id', $approved->id)->exists())->toBeTrue();
    expect(DailyAnalytics::where('salesperson_id', $pending->id)->exists())->toBeFalse();
});
```

---

## 📝 業務規則總覽表

| 編號 | 規則名稱 | 類型 | 實作方式 | HTTP 狀態碼 |
|------|---------|------|---------|------------|
| BR-001 | 每日彙總執行時機 | 彙總 | Laravel Scheduler | - |
| BR-002 | 彙總計算邏輯 | 彙總 | Artisan Command | - |
| BR-003 | 重複執行處理 | 彙總 | updateOrCreate | - |
| BR-004 | 手動觸發彙總 | 彙總 | Artisan Command | - |
| BR-005 | 時間範圍處理 | 查詢 | 應用層計算 | - |
| BR-006 | 混合查詢策略 | 查詢 | 應用層合併 | - |
| BR-007 | 趨勢數據填充零值 | 查詢 | 應用層處理 | - |
| BR-008 | 轉換率計算 | 計算 | 應用層公式 | - |
| BR-009 | 增長率計算 | 計算 | 應用層公式 | - |
| BR-010 | 上個時段計算 | 計算 | 應用層計算 | - |
| BR-011 | 業務員數據權限 | 權限 | Middleware | 403 |
| BR-012 | 管理員數據權限 | 權限 | Middleware | 403 |
| BR-013 | Range 參數驗證 | 驗證 | Form Request | 400 |
| BR-014 | 無數據返回零值 | 錯誤處理 | 應用層處理 | 200 |
| BR-015 | 彙總任務失敗處理 | 錯誤處理 | Try-Catch | - |
| BR-016 | 資料一致性檢查 | 錯誤處理 | 應用層驗證 | - |
| BR-017 | 業務員刪除處理 | 特殊情境 | 外鍵約束 | - |
| BR-018 | 只統計 Approved | 特殊情境 | 應用層過濾 | - |

---

## 📚 參考資料

- **Proposal**: `../proposal.md`
- **API 規格**: `./api.md`
- **資料模型規格**: `./data-model.md`
- **系統架構規格**: `./architecture.md`
- **Laravel Validation**: https://laravel.com/docs/11.x/validation
- **Laravel Scheduler**: https://laravel.com/docs/11.x/scheduling

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**維護者**: Backend Team
