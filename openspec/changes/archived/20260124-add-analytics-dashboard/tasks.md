# Implementation Tasks: Analytics Dashboard

**Feature**: Analytics Dashboard
**Date**: 2026-01-24
**Total Tasks**: 48
**Estimated Time**: 5 days

---

## 📋 任務總覽

### 統計

| 階段 | 任務數 | 預估時間 |
|------|--------|---------|
| **Phase 1: Backend - Database** | 4 | 2 小時 |
| **Phase 2: Backend - API** | 14 | 8 小時 |
| **Phase 3: Backend - Jobs & Tests** | 6 | 6 小時 |
| **Phase 4: Frontend - TypeScript & API** | 8 | 4 小時 |
| **Phase 5: Frontend - Components** | 8 | 6 小時 |
| **Phase 6: Frontend - Pages** | 4 | 4 小時 |
| **Phase 7: Integration & Testing** | 4 | 4 小時 |
| **總計** | **48** | **34 小時 (約 5 天)** |

---

## 🗄️ Phase 1: Backend - Database (4 tasks, 2h)

### Task 1.1: Create daily_analytics migration
**檔案**: `my_profile_laravel/database/migrations/YYYY_MM_DD_XXXXXX_create_daily_analytics_table.php`

**實作內容**:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salesperson_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('profile_views_count')->default(0);
            $table->unsignedInteger('contact_requests_count')->default(0);
            $table->unsignedInteger('unique_visitors_count')->default(0);
            $table->timestamps();

            // Indexes
            $table->unique(['salesperson_id', 'date'], 'unique_salesperson_date');
            $table->index('date', 'idx_date');
            $table->index(['salesperson_id', 'date'], 'idx_salesperson_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_analytics');
    }
};
```

**驗收標準**:
- [ ] Migration 檔案已建立
- [ ] 執行 `php artisan migrate` 成功
- [ ] 資料表結構符合規格
- [ ] 所有索引已建立

---

### Task 1.2: Create DailyAnalytics model
**檔案**: `my_profile_laravel/app/Models/DailyAnalytics.php`

**實作內容**:
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyAnalytics extends Model
{
    use HasFactory;

    protected $table = 'daily_analytics';

    protected $fillable = [
        'salesperson_id',
        'date',
        'profile_views_count',
        'contact_requests_count',
        'unique_visitors_count',
    ];

    protected $casts = [
        'date' => 'date',
        'profile_views_count' => 'integer',
        'contact_requests_count' => 'integer',
        'unique_visitors_count' => 'integer',
    ];

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    // Scopes
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
```

**驗收標準**:
- [ ] Model 類別已建立
- [ ] Fillable 欄位已定義
- [ ] Casts 已設定
- [ ] Relationship 已定義
- [ ] Scopes 已實作

---

### Task 1.3: Create DailyAnalyticsFactory
**檔案**: `my_profile_laravel/database/factories/DailyAnalyticsFactory.php`

**實作內容**:
```php
<?php

namespace Database\Factories;

use App\Models\DailyAnalytics;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DailyAnalyticsFactory extends Factory
{
    protected $model = DailyAnalytics::class;

    public function definition(): array
    {
        return [
            'salesperson_id' => User::where('role', 'salesperson')->inRandomOrder()->first()->id,
            'date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'profile_views_count' => fake()->numberBetween(0, 500),
            'contact_requests_count' => fake()->numberBetween(0, 50),
            'unique_visitors_count' => fake()->numberBetween(0, 300),
        ];
    }
}
```

**驗收標準**:
- [ ] Factory 已建立
- [ ] 可產生測試數據
- [ ] 測試: `DailyAnalytics::factory()->create()`

---

### Task 1.4: Update ContactEvent model (if needed)
**檔案**: `my_profile_laravel/app/Models/ContactEvent.php`

**檢查內容**:
- [ ] `salesperson_id` 欄位存在
- [ ] `event_type` 欄位存在 (profile_view, contact_form_submission)
- [ ] `ip_address_hash` 欄位存在（SHA256）
- [ ] 索引已優化

---

## 🔌 Phase 2: Backend - API (14 tasks, 8h)

### Task 2.1: Create DashboardController
**檔案**: `my_profile_laravel/app/Http/Controllers/Api/DashboardController.php`

**實作內容**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // 業務員端點
    public function salespersonStats(Request $request): JsonResponse
    {
        // Task 2.3 實作
    }

    public function salespersonTrends(Request $request): JsonResponse
    {
        // Task 2.5 實作
    }

    public function recentContacts(Request $request): JsonResponse
    {
        // Task 2.7 實作
    }

    // 管理員端點
    public function adminOverview(Request $request): JsonResponse
    {
        // Task 2.9 實作
    }

    public function topSalespersons(Request $request): JsonResponse
    {
        // Task 2.11 實作
    }

    public function adminActivity(Request $request): JsonResponse
    {
        // Task 2.13 實作
    }

    public function adminTrends(Request $request): JsonResponse
    {
        // Task 2.14 實作
    }
}
```

---

### Task 2.2: Add Dashboard routes
**檔案**: `my_profile_laravel/routes/api.php`

**實作內容**:
```php
use App\Http\Controllers\Api\DashboardController;

// 業務員 Dashboard
Route::middleware(['auth:api', 'role:salesperson'])->group(function () {
    Route::get('/dashboard/salesperson/stats', [DashboardController::class, 'salespersonStats']);
    Route::get('/dashboard/salesperson/trends', [DashboardController::class, 'salespersonTrends']);
    Route::get('/dashboard/salesperson/recent-contacts', [DashboardController::class, 'recentContacts']);
});

// 管理員 Dashboard
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/dashboard/admin/overview', [DashboardController::class, 'adminOverview']);
    Route::get('/dashboard/admin/top-salespersons', [DashboardController::class, 'topSalespersons']);
    Route::get('/dashboard/admin/activity', [DashboardController::class, 'adminActivity']);
    Route::get('/dashboard/admin/trends', [DashboardController::class, 'adminTrends']);
});
```

---

### Task 2.3: Implement salespersonStats() method
**檔案**: `DashboardController.php`

**實作混合查詢邏輯** (歷史 + 今日):
```php
public function salespersonStats(Request $request): JsonResponse
{
    $user = $request->user();
    $range = $request->input('range', '7days'); // today, 7days, 30days

    [$startDate, $endDate] = $this->calculateDateRange($range);
    $today = Carbon::today();

    // 1. 查詢歷史彙總數據（昨天及更早）
    $historical = DailyAnalytics::forSalesperson($user->id)
        ->dateRange($startDate, $today->copy()->subDay())
        ->selectRaw('
            SUM(profile_views_count) as profile_views,
            SUM(contact_requests_count) as contact_requests
        ')
        ->first();

    // 2. 查詢今日即時數據
    $today Stats = [
        'profile_views' => ContactEvent::where('salesperson_id', $user->id)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->count(),
        'contact_requests' => ContactRequest::where('salesperson_id', $user->id)
            ->whereDate('created_at', $today)
            ->count(),
    ];

    // 3. 合併數據
    $totalViews = ($historical->profile_views ?? 0) + $todayStats['profile_views'];
    $totalContacts = ($historical->contact_requests ?? 0) + $todayStats['contact_requests'];

    // 4. 計算增長率（與上一期比較）
    $growthRate = $this->calculateGrowthRate($user->id, $range);

    return response()->json([
        'success' => true,
        'data' => [
            'profile_views' => $totalViews,
            'contact_requests' => $totalContacts,
            'growth_rate' => $growthRate,
            'period' => $range,
        ],
    ]);
}
```

**驗收標準**:
- [ ] 混合查詢邏輯正確
- [ ] 增長率計算正確
- [ ] P95 < 500ms

---

### Task 2.4: Implement calculateDateRange() helper
**檔案**: `DashboardController.php`

---

### Task 2.5: Implement salespersonTrends() method
**檔案**: `DashboardController.php`

**返回每日趨勢數據陣列**

---

### Task 2.6: Implement calculateGrowthRate() helper
**檔案**: `DashboardController.php`

---

### Task 2.7: Implement recentContacts() method
**檔案**: `DashboardController.php`

**返回最新 10 筆聯繫記錄**

---

### Task 2.8: Create DashboardService (Optional)
**檔案**: `my_profile_laravel/app/Services/DashboardService.php`

將業務邏輯抽取到 Service 層（可選）

---

### Task 2.9: Implement adminOverview() method
**檔案**: `DashboardController.php`

**返回平台 KPI**:
- total_salespersons
- total_profile_views
- total_contact_requests
- platform_conversion_rate

---

### Task 2.10: Implement adminOverview() method (continued)
**增加平台轉換率計算**

---

### Task 2.11: Implement topSalespersons() method
**檔案**: `DashboardController.php`

**返回 Top 10 業務員**（按瀏覽數降序）

---

### Task 2.12: Add conversion rate calculation
**在 Top 10 查詢中加入轉換率計算**

---

### Task 2.13: Implement adminActivity() method
**檔案**: `DashboardController.php`

**返回活躍度統計**:
- active_salespersons (過去 7 天有瀏覽)
- inactive_salespersons
- total_salespersons
- activity_rate

---

### Task 2.14: Implement adminTrends() method
**檔案**: `DashboardController.php`

**返回過去 30 天平台趨勢**

---

## ⏰ Phase 3: Backend - Jobs & Tests (6 tasks, 6h)

### Task 3.1: Create AggregateDailyAnalytics command
**檔案**: `my_profile_laravel/app/Console/Commands/AggregateDailyAnalytics.php`

**實作每日彙總邏輯**:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\DailyAnalytics;
use Carbon\Carbon;

class AggregateDailyAnalytics extends Command
{
    protected $signature = 'analytics:aggregate-daily {--date=}';
    protected $description = 'Aggregate daily analytics for all salespersons';

    public function handle()
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Aggregating analytics for {$date->toDateString()}...");

        $salespeople = User::where('role', 'salesperson')->get();

        foreach ($salespeople as $salesperson) {
            $this->aggregateForSalesperson($salesperson, $date);
        }

        $this->info('Aggregation completed!');
    }

    private function aggregateForSalesperson(User $salesperson, Carbon $date): void
    {
        // ... 實作
    }
}
```

**驗收標準**:
- [ ] Command 已建立
- [ ] 可手動執行: `php artisan analytics:aggregate-daily`
- [ ] 可指定日期: `php artisan analytics:aggregate-daily --date=2026-01-23`
- [ ] 使用 updateOrCreate 避免重複

---

### Task 3.2: Add daily schedule
**檔案**: `my_profile_laravel/app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('analytics:aggregate-daily')
             ->dailyAt('02:00')
             ->withoutOverlapping()
             ->onOneServer();
}
```

---

### Task 3.3: Write DashboardController Feature Tests
**檔案**: `my_profile_laravel/tests/Feature/Api/DashboardControllerTest.php`

**測試案例**:
- `test_salesperson_can_get_stats_for_7days()`
- `test_salesperson_can_get_trends()`
- `test_salesperson_can_get_recent_contacts()`
- `test_admin_can_get_overview()`
- `test_admin_can_get_top_salespersons()`
- `test_admin_can_get_activity()`
- `test_admin_can_get_trends()`
- `test_unauthorized_user_cannot_access_dashboard_endpoints()`
- `test_salesperson_cannot_access_admin_endpoints()`

**驗收標準**:
- [ ] 所有測試通過
- [ ] 覆蓋率 >= 95%

---

### Task 3.4: Write AggregateDailyAnalytics Unit Tests
**檔案**: `my_profile_laravel/tests/Unit/Commands/AggregateDailyAnalyticsTest.php`

---

### Task 3.5: Write DailyAnalytics Model Tests
**檔案**: `my_profile_laravel/tests/Unit/Models/DailyAnalyticsTest.php`

---

### Task 3.6: Run PHPStan & Laravel Pint
**命令**:
```bash
composer analyse
composer format
```

---

## 🎨 Phase 4: Frontend - TypeScript & API (8 tasks, 4h)

### Task 4.1: Create TypeScript types
**檔案**: `frontend/types/dashboard.ts`

**實作所有類型定義** (參考 api-integration.md)

---

### Task 4.2: Create API client functions
**檔案**: `frontend/lib/api/dashboard.ts`

**實作 7 個 API 函數** (參考 api-integration.md)

---

### Task 4.3: Create React Query hooks
**檔案**: `frontend/hooks/useDashboard.ts`

**實作 7 個 Hooks** (參考 api-integration.md)

---

### Task 4.4: Update Axios interceptor
**檔案**: `frontend/lib/api/axios.ts`

**確保錯誤處理完整**

---

### Task 4.5: Update QueryClient config
**檔案**: `frontend/lib/query/queryClient.ts`

**配置快取策略**

---

### Task 4.6: Write API client tests
**檔案**: `frontend/__tests__/api/dashboard.test.ts`

---

### Task 4.7: Write React Query hooks tests
**檔案**: `frontend/__tests__/hooks/useDashboard.test.ts`

---

### Task 4.8: TypeScript type check
**命令**: `npm run typecheck`

---

## 🧩 Phase 5: Frontend - Components (8 tasks, 6h)

### Task 5.1: Create StatCard component
**檔案**: `frontend/components/dashboard/StatCard.tsx`

**參考**: components.md

---

### Task 5.2: Create TrendBadge component
**檔案**: `frontend/components/dashboard/TrendBadge.tsx`

---

### Task 5.3: Create EmptyState component
**檔案**: `frontend/components/dashboard/EmptyState.tsx`

---

### Task 5.4: Create ErrorFallback component
**檔案**: `frontend/components/dashboard/ErrorFallback.tsx`

---

### Task 5.5: Create LineChart component
**檔案**: `frontend/components/dashboard/charts/LineChart.tsx`

**使用 Recharts**

---

### Task 5.6: Create DualLineChart component
**檔案**: `frontend/components/dashboard/charts/DualLineChart.tsx`

---

### Task 5.7: Create ContactList component
**檔案**: `frontend/components/dashboard/salesperson/ContactList.tsx`

---

### Task 5.8: Create TopSalespersons & ActivityCard components
**檔案**:
- `frontend/components/dashboard/admin/TopSalespersons.tsx`
- `frontend/components/dashboard/admin/ActivityCard.tsx`

---

## 📄 Phase 6: Frontend - Pages (4 tasks, 4h)

### Task 6.1: Create Salesperson Analytics page
**檔案**: `frontend/app/(dashboard)/dashboard/analytics/page.tsx`

**參考**: pages.md

---

### Task 6.2: Create Salesperson Dashboard components
**檔案**:
- `frontend/app/(dashboard)/dashboard/analytics/components/AnalyticsDashboard.tsx`
- `frontend/app/(dashboard)/dashboard/analytics/components/DashboardSkeleton.tsx`

---

### Task 6.3: Create Admin Analytics page
**檔案**: `frontend/app/(admin)/admin/dashboard/analytics/page.tsx`

---

### Task 6.4: Create Admin Dashboard components
**檔案**:
- `frontend/app/(admin)/admin/dashboard/analytics/components/AdminAnalyticsDashboard.tsx`
- `frontend/app/(admin)/admin/dashboard/analytics/components/AdminDashboardSkeleton.tsx`

---

## 🧪 Phase 7: Integration & Testing (4 tasks, 4h)

### Task 7.1: Update Navigation (Sidebar)
**檔案**:
- `frontend/components/layout/SalespersonNav.tsx`
- `frontend/components/layout/AdminNav.tsx`

**新增「數據分析」連結**

---

### Task 7.2: Update Middleware (if needed)
**檔案**: `frontend/middleware.ts`

**確保認證守衛正確**

---

### Task 7.3: E2E tests with Playwright
**檔案**: `frontend/__tests__/e2e/dashboard.spec.ts`

**測試案例**:
- 業務員進入 Dashboard 載入數據
- 切換時間範圍 Tab
- 管理員進入 Dashboard 載入數據
- 未登入用戶重定向到登入頁

---

### Task 7.4: Integration testing
**測試流程**:
1. Backend 啟動
2. Frontend 啟動
3. 手動測試所有功能
4. 驗收標準檢查

---

## ✅ 驗收標準總檢查清單

### Backend 檢查

- [ ] 所有 7 個 API 端點實作完成
- [ ] Migration 已執行
- [ ] Model 已建立並測試
- [ ] AggregateDailyAnalytics Command 已實作
- [ ] Daily Schedule 已配置
- [ ] Feature Tests >= 95% 覆蓋率
- [ ] PHPStan Level 9 無錯誤
- [ ] Laravel Pint 格式化完成
- [ ] API 回應時間 P95 < 500ms

### Frontend 檢查

- [ ] 所有 TypeScript 類型已定義
- [ ] 所有 API Client 函數已實作
- [ ] 所有 React Query Hooks 已實作
- [ ] 所有 UI 組件已建立 (10 個)
- [ ] 業務員 Dashboard 頁面已完成
- [ ] 管理員 Dashboard 頁面已完成
- [ ] Navigation 已更新
- [ ] Middleware 已配置
- [ ] TypeScript 嚴格模式無錯誤
- [ ] ESLint 無警告
- [ ] E2E 測試通過

### 整合測試

- [ ] Backend + Frontend 整合測試通過
- [ ] 業務員可查看自己的數據
- [ ] 管理員可查看平台數據
- [ ] 時間範圍切換正常
- [ ] 響應式設計測試通過 (Desktop/Tablet/Mobile)
- [ ] 效能測試通過 (LCP < 2s)

---

**Total**: 48 tasks
**Estimated**: 34 hours (~5 days)
**Priority**: High (MVP Feature 2)

---

**Version**: 1.0
**Last Updated**: 2026-01-24
