---
category: lessons-learned
tags: [performance, backend, optimization, caching]
priority: high
last_updated: 2026-01-14
applies_to: Laravel 11
related_docs: [../../backend/architecture.md]
---

# Backend 效能優化技巧

## Quick Reference

記錄實際驗證有效的 Backend 效能優化技巧。

---

## PT-BE-001: 資料庫查詢結果快取

### 問題
首頁 API 回應時間 500ms，但資料每 5 分鐘才更新一次。

### 解決方案
使用 Redis 快取查詢結果。

### 實作
```php
// Before
public function index(): JsonResponse
{
    $salespersons = Salesperson::with('user', 'company')
        ->orderBy('rating', 'desc')
        ->limit(10)
        ->get();

    return response()->json(['data' => $salespersons]);
}

// After
public function index(): JsonResponse
{
    $salespersons = Cache::remember(
        'top_salespersons',
        now()->addMinutes(5),
        fn () => Salesperson::with('user', 'company')
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get()
    );

    return response()->json(['data' => $salespersons]);
}
```

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| 回應時間 (P50) | 500ms | 15ms | 33x |
| 回應時間 (P95) | 800ms | 20ms | 40x |
| QPS | 20 | 500 | 25x |
| CPU | 45% | 8% | 5.6x |

### 適用場景
- 查詢結果變化不頻繁
- 讀取 >> 寫入
- 查詢成本高（複雜 JOIN、聚合）
- 可容忍短暫不一致

---

## PT-BE-002: Eager Loading 避免 N+1

### 效能數據

| 操作 | Before | After | 改善 |
|------|--------|-------|------|
| 查詢數 | 201 | 3 | 67x |
| 回應時間 | 6000ms | 90ms | 67x |
| 記憶體 | 120MB | 15MB | 8x |

### 實作
```php
// 使用 with() 預載入關聯
$salespersons = Salesperson::with(['user', 'company', 'reviews'])
    ->get();
```

---

## PT-BE-003: 資料庫索引優化

### 問題
搜尋 API 回應時間 2 秒（10 萬筆資料）。

### 解決方案
根據查詢模式建立複合索引。

### 實作
```php
// Migration
Schema::table('salespersons', function (Blueprint $table) {
    // 複合索引（常一起查詢的欄位）
    $table->index(['company_id', 'rating', 'created_at']);
});
```

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| 查詢時間 | 2000ms | 25ms | 80x |
| 全表掃描 | 是 | 否 | - |
| 索引使用 | 無 | 有 | - |

---

## PT-BE-004: 批次處理

### 問題
發送 1000 封歡迎信需要 30 分鐘。

### 解決方案
使用 Queue 批次處理。

### 實作
```php
// Before - 同步處理
foreach ($users as $user) {
    Mail::to($user)->send(new WelcomeEmail($user));  // 阻塞
}

// After - 非同步批次
foreach ($users as $user) {
    Mail::to($user)->queue(new WelcomeEmail($user));  // 非阻塞
}

// 或使用 Batch
Bus::batch(
    collect($users)->map(fn ($user) => new SendWelcomeEmail($user))
)->dispatch();
```

### 效能數據
- 處理時間: 30 分鐘 → 2 分鐘 (15x)
- API 回應: 阻塞 30 分鐘 → 立即回應

---

**已記錄**: 4 個效能優化技巧

**相關**: [Backend 架構](../../backend/architecture.md)
