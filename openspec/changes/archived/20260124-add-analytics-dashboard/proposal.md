# Proposal: 數據追蹤 Dashboard 功能

**Feature**: Analytics Dashboard
**Date**: 2026-01-24
**Author**: OpenSpec SDD
**Status**: Proposal
**Timeline**: 5 days

---

## 📋 Why - 為什麼需要這個功能

### 問題陳述

YAMU 平台的核心價值主張是「幫助業務員獲得更多客戶」，但目前缺乏數據支持來驗證這個假設：

**業務員痛點**:
- ❓ 不知道自己的檔案曝光情況
- ❓ 不知道有多少潛在客戶查看了檔案
- ❓ 無法追蹤聯繫請求的來源和趨勢
- ❓ 無法衡量檔案的吸引力和效果

**平台管理痛點**:
- ❓ 不知道平台的實際使用情況
- ❓ 無法識別熱門業務員和低活躍業務員
- ❓ 缺乏數據支持來優化平台策略
- ❓ 無法追蹤平台成長趨勢

### MVP 驗證目標

透過數據追蹤 Dashboard，我們要驗證：

1. **業務員參與度**: 業務員是否會主動查看數據？（目標: 80% 業務員每週至少查看 1 次）
2. **數據驅動優化**: 業務員是否會根據數據調整檔案？（觀察瀏覽數低的業務員是否會更新檔案）
3. **平台價值**: 平台是否真的帶來客戶？（追蹤聯繫轉換率）

---

## 🎯 What - 功能描述

### 核心價值

**對業務員**:
「讓業務員清楚看到自己檔案的曝光度和吸引力，幫助他們優化檔案以獲得更多客戶。」

**對管理員**:
「提供平台整體數據洞察，幫助管理員了解平台健康度、識別問題並制定策略。」

### 功能概覽

#### 業務員 Dashboard

一個簡潔、易懂的數據儀表板，展示：

**核心 KPI 卡片** (3 個大數字):
1. **總瀏覽數** - 檔案被查看的總次數
2. **總聯繫數** - 收到的聯繫請求總數
3. **趨勢變化** - 與上個時段相比的增長率

**趨勢圖** (折線圖):
- 過去 N 天的瀏覽數和聯繫數趨勢
- 支援三個時間範圍: 今日 / 過去 7 天 / 過去 30 天

**最近聯繫列表**:
- 最新 10 筆聯繫請求
- 顯示: 客戶姓名、訊息預覽、時間、狀態（pending/contacted/closed）
- 可點擊查看詳情

#### 管理員 Dashboard

**平台 KPI 概覽** (4 個大數字):
1. **總業務員數** - 平台註冊的業務員總數
2. **總瀏覽數** - 所有業務員檔案的總瀏覽數
3. **總聯繫數** - 平台產生的總聯繫請求數
4. **平台轉換率** - 總聯繫數 / 總瀏覽數

**熱門業務員 Top 10**:
- 按瀏覽數排序的前 10 名業務員
- 顯示: 業務員姓名、瀏覽數、聯繫數、轉換率

**業務員活躍度**:
- 活躍業務員數（過去 7 天有瀏覽的）
- 低活躍業務員數（0 瀏覽）
- 活躍率百分比

**平台成長趨勢圖**:
- 日/週/月 瀏覽數和聯繫數趨勢
- 新增業務員趨勢

---

## 📐 Scope - 範圍定義

### ✅ In Scope (本次實作)

**Backend API**:
- [x] `GET /api/dashboard/salesperson/stats` - 業務員統計數據 API
- [x] `GET /api/dashboard/salesperson/trends` - 業務員趨勢數據 API
- [x] `GET /api/dashboard/salesperson/recent-contacts` - 最近聯繫列表 API
- [x] `GET /api/dashboard/admin/overview` - 管理員平台概覽 API
- [x] `GET /api/dashboard/admin/top-salespeople` - 熱門業務員 Top 10 API
- [x] `GET /api/dashboard/admin/activity` - 業務員活躍度 API
- [x] `GET /api/dashboard/admin/trends` - 平台趨勢數據 API
- [x] Daily Analytics Aggregation Job (每日彙總任務)

**Frontend UI**:
- [x] 業務員 Dashboard 頁面 (`/dashboard/analytics`)
  - KPI 卡片組件 (3 個)
  - 趨勢圖組件 (Recharts 折線圖)
  - 時間範圍 Tab (今日/7天/30天)
  - 最近聯繫列表組件
- [x] 管理員 Dashboard 頁面 (`/admin/dashboard/analytics`)
  - 平台 KPI 卡片組件 (4 個)
  - 熱門業務員列表組件
  - 活躍度卡片組件
  - 平台趨勢圖組件

**資料庫**:
- [x] 新增 `daily_analytics` 資料表（每日彙總數據）
- [x] 利用現有 `contact_events` 資料表（今日即時數據）
- [x] 利用現有 `contact_requests` 資料表（聯繫列表）

**數據收集**:
- [x] 基於現有 `contact_events` 事件追蹤
- [x] 事件類型: `profile_view`, `contact_form_submission`

### ❌ Out of Scope (明確不做)

**UI/UX 複雜功能**:
- ❌ 即時更新（WebSocket 推送）
- ❌ 複雜圖表（圓餅圖、熱力圖、雷達圖）
- ❌ 自定義日期範圍選擇器（只提供預設 Tab）
- ❌ 數據匯出功能（CSV/Excel/PDF）
- ❌ Dashboard 自定義配置（拖曳組件、自選指標）

**進階數據分析**:
- ❌ 轉換漏斗分析（詳細的多步驟轉換）
- ❌ 客戶行為分析（訪問時長、跳出率、頁面熱力圖）
- ❌ A/B Testing 分析
- ❌ 預測分析（機器學習預測趨勢）
- ❌ 同業比較（與其他業務員比較）
- ❌ 排名系統（在所有業務員中的排名）

**通知與提醒**:
- ❌ 數據異常提醒（瀏覽數驟降）
- ❌ 週報 Email（每週數據總結）
- ❌ 達成目標通知

**效能優化**:
- ❌ 實時數據流處理（Kafka/Redis Streams）
- ❌ 複雜的數據倉庫（OLAP）
- ❌ 大數據分析平台整合

---

## 👥 User Stories

### 業務員端

**Story 1: 查看檔案曝光情況**
```
As a 業務員
I want to 查看我的檔案瀏覽數和趨勢
So that 我可以知道檔案的曝光度如何

Acceptance Criteria:
- 進入 Dashboard 可以立即看到總瀏覽數（大數字呈現）
- 可以查看過去 7 天和 30 天的瀏覽趨勢
- 趨勢圖清楚顯示每日瀏覽數變化
- 顯示與上個時段相比的增長率（+5% / -3%）
```

**Story 2: 追蹤聯繫轉換**
```
As a 業務員
I want to 查看我收到的聯繫請求數量
So that 我可以評估檔案的吸引力和轉換效果

Acceptance Criteria:
- 可以看到總聯繫數
- 可以查看聯繫數趨勢（過去 7 天 / 30 天）
- 可以看到最近 10 筆聯繫請求
- 每筆聯繫顯示: 客戶姓名、時間、狀態
```

**Story 3: 調整時間範圍**
```
As a 業務員
I want to 切換不同的時間範圍查看數據
So that 我可以分析短期和長期的趨勢

Acceptance Criteria:
- 有三個 Tab: 今日 / 過去 7 天 / 過去 30 天
- 點擊 Tab 立即更新所有數據和圖表
- 載入時間 < 2 秒
```

### 管理員端

**Story 4: 查看平台整體健康度**
```
As a 管理員
I want to 查看平台的整體數據
So that 我可以了解平台的運作情況和成長

Acceptance Criteria:
- 可以看到總業務員數、總瀏覽數、總聯繫數
- 可以看到平台整體轉換率
- 可以查看平台成長趨勢圖（過去 30 天）
```

**Story 5: 識別熱門業務員**
```
As a 管理員
I want to 查看瀏覽數最高的業務員
So that 我可以研究他們的檔案特點並推廣最佳實踐

Acceptance Criteria:
- 可以看到 Top 10 業務員列表
- 按瀏覽數排序
- 顯示每個業務員的瀏覽數、聯繫數、轉換率
```

**Story 6: 監控業務員活躍度**
```
As a 管理員
I want to 查看業務員的活躍情況
So that 我可以識別需要幫助的低活躍業務員

Acceptance Criteria:
- 可以看到活躍業務員數（過去 7 天有瀏覽）
- 可以看到低活躍業務員數（0 瀏覽）
- 顯示活躍率百分比
```

---

## ✅ Success Criteria - 驗收標準

### 功能性標準

#### 業務員 Dashboard

- [x] **KPI 顯示**:
  - 總瀏覽數正確（與 contact_events 中 profile_view 數量一致）
  - 總聯繫數正確（與 contact_requests 數量一致）
  - 增長率計算正確（與上個時段比較）

- [x] **趨勢圖**:
  - 可以切換三個時間範圍（今日/7天/30天）
  - 每個 Tab 顯示對應時間範圍的數據
  - 折線圖清楚顯示日期和數值
  - 圖表使用 Recharts 實作

- [x] **最近聯繫列表**:
  - 顯示最新 10 筆聯繫請求
  - 按時間倒序排列
  - 顯示客戶姓名、訊息預覽（前 50 字）、時間、狀態
  - 點擊可查看完整聯繫詳情

#### 管理員 Dashboard

- [x] **平台 KPI**:
  - 總業務員數正確
  - 總瀏覽數、總聯繫數正確
  - 平台轉換率計算正確（聯繫數 / 瀏覽數）

- [x] **熱門業務員 Top 10**:
  - 按瀏覽數降序排列
  - 顯示業務員姓名、瀏覽數、聯繫數、轉換率
  - 點擊可跳轉到業務員檔案

- [x] **活躍度分析**:
  - 活躍業務員數計算正確（過去 7 天有 profile_view 事件）
  - 低活躍業務員數計算正確（0 瀏覽）
  - 活躍率 = 活躍數 / 總業務員數

- [x] **趨勢圖**:
  - 顯示過去 30 天的瀏覽數和聯繫數趨勢
  - 使用雙軸折線圖（如果差距大）

### 非功能性標準

#### 效能要求

- [x] **API 回應時間**:
  - P50 < 200ms
  - P95 < 500ms
  - P99 < 1000ms

- [x] **併發處理**:
  - 支援 >= 10 concurrent requests/s
  - 無明顯效能衰退

- [x] **前端載入時間**:
  - 首次載入（LCP）< 2 秒
  - Tab 切換響應 < 300ms

#### 數據準確性

- [x] **100% 準確性**:
  - 統計數據與實際事件記錄完全一致
  - 每日彙總數據與當日即時查詢結果一致（允許誤差 < 1%）

- [x] **數據一致性**:
  - 業務員 Dashboard 數據 = 該業務員在管理員 Dashboard 中的數據
  - 管理員總計數據 = 所有業務員數據的總和

#### 可用性

- [x] **響應式設計**:
  - 支援 Desktop (1920x1080)
  - 支援 Tablet (768x1024)
  - 支援 Mobile (375x667) - 卡片堆疊排列

- [x] **錯誤處理**:
  - 無數據時顯示友善提示（「尚無瀏覽記錄」）
  - API 失敗時顯示錯誤訊息和重試按鈕
  - Loading 狀態清楚顯示

#### 安全性

- [x] **權限控制**:
  - 業務員只能查看自己的數據（嘗試查看他人返回 403）
  - 管理員可以查看所有數據
  - 一般用戶無法訪問 Dashboard（返回 403）

---

## 🔧 Technical Approach - 技術方案

### 混合資料彙總策略

為了平衡效能和開發時間，採用 **混合模式**：

#### 架構設計

```
┌─────────────────────────────────────────────┐
│  Dashboard 數據來源                          │
└─────────────────────────────────────────────┘

歷史數據（昨天及更早）
├── 資料來源: daily_analytics 表（預先彙總）
├── 更新頻率: 每日凌晨 2:00 執行彙總 Job
├── 資料結構:
│   - salesperson_id
│   - date (YYYY-MM-DD)
│   - profile_views_count
│   - contact_requests_count
│   - unique_visitors_count
└── 優點: 查詢快速，適合歷史趨勢分析

今日數據（當天 00:00 ~ 現在）
├── 資料來源: contact_events + contact_requests（即時查詢）
├── 更新頻率: 即時
├── 查詢方式:
│   - WHERE event_type = 'profile_view' AND DATE(created_at) = CURDATE()
│   - COUNT(*) GROUP BY salesperson_id
└── 優點: 數據即時，無延遲

合併邏輯（API 層）
├── 查詢歷史彙總數據（昨天及更早）
├── 查詢今日即時數據
├── 在 API 層合併兩者
└── 返回完整數據給前端
```

#### 資料表設計: daily_analytics

```sql
CREATE TABLE daily_analytics (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    salesperson_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    profile_views_count INT UNSIGNED DEFAULT 0,
    contact_requests_count INT UNSIGNED DEFAULT 0,
    unique_visitors_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_salesperson_date (salesperson_id, date),
    INDEX idx_date (date),
    INDEX idx_salesperson_date (salesperson_id, date),
    FOREIGN KEY (salesperson_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**欄位說明**:
- `salesperson_id`: 業務員 ID
- `date`: 統計日期（YYYY-MM-DD）
- `profile_views_count`: 當日檔案瀏覽次數
- `contact_requests_count`: 當日聯繫請求次數
- `unique_visitors_count`: 當日獨立訪客數（基於 IP hash 去重）

#### 每日彙總 Job

```php
// app/Console/Commands/AggregateD​ailyAnalytics.php

class AggregateDailyAnalytics extends Command
{
    protected $signature = 'analytics:aggregate-daily';

    public function handle()
    {
        $yesterday = Carbon::yesterday();

        // 彙總每個業務員的昨日數據
        $salespeople = User::where('role', 'salesperson')->get();

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
                ->count();

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

        $this->info('Daily analytics aggregated successfully!');
    }
}
```

**排程設定** (`app/Console/Kernel.php`):
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('analytics:aggregate-daily')
             ->dailyAt('02:00'); // 每日凌晨 2:00 執行
}
```

#### API 實作範例

```php
// app/Http/Controllers/Api/DashboardController.php

public function salespersonStats(Request $request)
{
    $user = $request->user();
    $timeRange = $request->input('range', '7days'); // today, 7days, 30days

    // 計算日期範圍
    [$startDate, $endDate] = $this->calculateDateRange($timeRange);
    $today = Carbon::today();

    // 1. 查詢歷史彙總數據（昨天及更早）
    $historicalStats = DailyAnalytics::where('salesperson_id', $user->id)
        ->where('date', '>=', $startDate)
        ->where('date', '<', $today)
        ->selectRaw('
            SUM(profile_views_count) as profile_views,
            SUM(contact_requests_count) as contact_requests
        ')
        ->first();

    // 2. 查詢今日即時數據
    $todayStats = [
        'profile_views' => ContactEvent::where('salesperson_id', $user->id)
            ->where('event_type', 'profile_view')
            ->whereDate('created_at', $today)
            ->count(),
        'contact_requests' => ContactRequest::where('salesperson_id', $user->id)
            ->whereDate('created_at', $today)
            ->count(),
    ];

    // 3. 合併數據
    $totalProfileViews = ($historicalStats->profile_views ?? 0) + $todayStats['profile_views'];
    $totalContactRequests = ($historicalStats->contact_requests ?? 0) + $todayStats['contact_requests'];

    return response()->json([
        'success' => true,
        'data' => [
            'profile_views' => $totalProfileViews,
            'contact_requests' => $totalContactRequests,
            'range' => $timeRange,
        ],
    ]);
}
```

### 效能優化

#### 資料庫索引

```sql
-- contact_events 表（已存在）
INDEX idx_salesperson_type_created (salesperson_id, event_type, created_at)
INDEX idx_event_type_created (event_type, created_at)
INDEX idx_ip_address_hash (ip_address_hash)

-- daily_analytics 表（新增）
UNIQUE KEY unique_salesperson_date (salesperson_id, date)
INDEX idx_date (date)
INDEX idx_salesperson_date (salesperson_id, date)
```

#### Query 優化

- 使用 `selectRaw('SUM(...)')` 在資料庫層彙總，減少資料傳輸
- 使用 `whereDate()` 搭配索引快速篩選日期
- 使用 `distinct('ip_address_hash')` 計算獨立訪客

#### Caching 策略

- 歷史數據（昨天及更早）可快取 24 小時（因為不會再變動）
- 今日數據不快取（即時數據）
- 使用 Laravel Cache Tags:
  ```php
  Cache::tags(['analytics', "salesperson:{$id}"])->remember(...);
  ```

---

## 📅 Timeline - 開發時間表

### 總計: 5 個工作天

#### Day 1-2: Backend 開發（2 天）

**Day 1**:
- [x] 建立 `daily_analytics` migration 和 model
- [x] 實作每日彙總 Command (`AggregateDailyAnalytics`)
- [x] 設定 Schedule 排程
- [x] 實作業務員統計 API (`GET /api/dashboard/salesperson/stats`)
- [x] 實作業務員趨勢 API (`GET /api/dashboard/salesperson/trends`)
- [x] 實作最近聯繫列表 API (`GET /api/dashboard/salesperson/recent-contacts`)

**Day 2**:
- [x] 實作管理員平台概覽 API (`GET /api/dashboard/admin/overview`)
- [x] 實作熱門業務員 API (`GET /api/dashboard/admin/top-salespeople`)
- [x] 實作活躍度 API (`GET /api/dashboard/admin/activity`)
- [x] 實作平台趨勢 API (`GET /api/dashboard/admin/trends`)
- [x] 撰寫 Feature Tests（API 測試）
- [x] PHPStan 靜態分析
- [x] Laravel Pint 格式化

#### Day 3-4: Frontend 開發（2 天）

**Day 3**:
- [x] 建立 API Client 函數（`lib/api/dashboard.ts`）
- [x] 建立 React Query Hooks（`hooks/useDashboard.ts`）
- [x] 建立 TypeScript Types
- [x] 實作業務員 Dashboard 頁面
  - KPI 卡片組件（3 個）
  - 時間範圍 Tab 組件
  - 趨勢圖組件（Recharts 折線圖）
- [x] 實作響應式設計（Desktop/Tablet/Mobile）

**Day 4**:
- [x] 實作最近聯繫列表組件
- [x] 實作管理員 Dashboard 頁面
  - 平台 KPI 卡片組件（4 個）
  - 熱門業務員列表組件
  - 活躍度卡片組件
  - 平台趨勢圖組件
- [x] 錯誤處理與 Loading 狀態
- [x] TypeScript 類型檢查
- [x] ESLint 檢查

#### Day 5: 測試與優化（1 天）

- [x] 後端 Feature Tests 完整覆蓋（>= 95%）
- [x] 前端 E2E 測試（Playwright）
  - 測試業務員 Dashboard 載入
  - 測試時間範圍切換
  - 測試管理員 Dashboard 載入
- [x] 效能測試
  - API 回應時間測試
  - 前端載入時間測試
- [x] 數據準確性驗證
- [x] 文檔更新
  - API 文檔（OpenAPI）
  - 使用者指南

---

## 📊 Key Metrics - 關鍵指標

### MVP 成功指標

**使用率**:
- 80% 業務員每週至少查看 Dashboard 1 次
- 管理員每天至少查看 Dashboard 1 次

**效能**:
- API P95 回應時間 < 500ms
- 前端 LCP < 2 秒
- 零 API 錯誤率（5xx）

**數據準確性**:
- 100% 統計數據準確（與實際事件一致）

### 追蹤指標

**業務員行為**:
- Dashboard 訪問頻率（次/週）
- 平均停留時間
- 最常查看的時間範圍（今日/7天/30天）

**數據趨勢**:
- 平台整體瀏覽數成長率
- 平台整體聯繫數成長率
- 平台整體轉換率變化

---

## 🚨 Risks & Mitigations - 風險與應對

### Risk 1: 每日彙總 Job 失敗

**影響**: 歷史數據不準確，Dashboard 顯示錯誤

**應對**:
- 設定 Job 失敗通知（Email/Slack）
- 實作重試機制（3 次重試）
- 提供手動執行指令: `php artisan analytics:aggregate-daily --date=2026-01-23`
- 失敗時降級到即時查詢（效能較差但數據正確）

### Risk 2: 大量歷史數據查詢效能問題

**影響**: API 回應時間超過 500ms

**應對**:
- 使用資料庫索引優化查詢
- 實作查詢快取（歷史數據快取 24 小時）
- 限制時間範圍（最多查詢 90 天）
- 考慮分頁載入（如果數據量大）

### Risk 3: 即時數據與彙總數據不一致

**影響**: 用戶發現數據前後不一致，失去信任

**應對**:
- 設定每日數據一致性檢查
- 彙總前驗證今日數據是否已完整
- 提供數據重新計算機制
- 在 UI 標註「數據更新時間」

### Risk 4: 業務員隱私疑慮

**影響**: 業務員擔心數據被濫用

**應對**:
- 明確說明數據用途（僅用於 Dashboard）
- 只有管理員可以看到所有業務員數據
- 不公開排名（避免負面競爭）
- 提供數據使用隱私政策

---

## 📝 Dependencies - 相依性

### 已完成的功能（可直接使用）

- ✅ 事件追蹤系統（`contact_events` 表）
- ✅ 聯繫請求系統（`contact_requests` 表）
- ✅ 業務員檔案系統（`salesperson_profiles` 表）
- ✅ 認證與授權系統（JWT + Role-based）

### 需要的技術棧（已具備）

**Backend**:
- ✅ Laravel 11
- ✅ MySQL 8.0
- ✅ Laravel Scheduler (Cron Jobs)
- ✅ Pest Testing Framework

**Frontend**:
- ✅ Next.js 15
- ✅ React 19
- ✅ Recharts 2.x（圖表庫）
- ✅ React Query 5.x（數據管理）
- ✅ Tailwind CSS（樣式）

---

## 🎯 Next Steps - 下一步

一旦此 Proposal 獲得批准，將進入：

1. **Step 2: Write Specifications**
   - API 規格（7 個端點）
   - 資料庫 Schema（daily_analytics 表）
   - 業務規則（彙總邏輯、計算公式）

2. **Step 3: Break Down Tasks**
   - 拆解為 40-50 個可執行任務
   - 明確每個任務的檔案和方法

3. **Step 4: Validate Specs**
   - 使用 spec-validation.md 完整檢查
   - 確認 100% 通過後進入實作

4. **Step 5-6: AUTO-RUN Implement & Archive**
   - 自動實作所有任務
   - 歸檔到 OpenSpec 規範庫

---

**Estimated Effort**: 5 個工作天
**Priority**: High（MVP 第二優先功能）
**Complexity**: Medium
**Risk Level**: Low

**Approval Required**: ✋ 等待用戶確認
