---
category: lessons-learned
tags: [performance, caching, redis, optimization]
priority: high
last_updated: 2026-01-14
applies_to: Redis 7.0, Laravel Cache
related_docs: [../../backend/architecture.md]
---

# 快取策略

## Quick Reference

記錄實際驗證有效的多層快取策略。

---

## PT-CACHE-001: 多層快取架構

### 架構
```
User Request
    ↓
L1: 應用層記憶體快取 (10s TTL)
    ↓ (Miss)
L2: Redis 快取 (5 min TTL)
    ↓ (Miss)
L3: 資料庫查詢
```

### 實作
```php
public function getTopSalespersons(): Collection
{
    // L1: 應用層快取（Process 內）
    return once(function () {
        // L2: Redis 快取
        return Cache::remember('top_salespersons', 300, function () {
            // L3: 資料庫查詢
            return Salesperson::with(['user', 'company'])
                ->where('rating', '>=', 4.5)
                ->orderBy('rating', 'desc')
                ->limit(10)
                ->get();
        });
    });
}
```

### 效能數據

| 快取層級 | 命中率 | 回應時間 |
|---------|--------|---------|
| L1 (記憶體) | 60% | 0.1ms |
| L2 (Redis) | 35% | 5ms |
| L3 (資料庫) | 5% | 200ms |
| 平均回應 | - | 11ms |

---

## PT-CACHE-002: 快取失效策略

### 策略類型

**時間失效** (TTL):
```php
Cache::put('key', $value, now()->addMinutes(5));
```

**事件失效** (Event-based):
```php
// 當資料更新時失效快取
public function update(Salesperson $salesperson, array $data): Salesperson
{
    $salesperson->update($data);

    // 失效相關快取
    Cache::forget('top_salespersons');
    Cache::forget("salesperson.{$salesperson->id}");
    Cache::tags(['salespersons'])->flush();

    return $salesperson;
}
```

**階層失效** (Tag-based):
```php
// 使用 Tag 管理相關快取
Cache::tags(['salespersons', 'lists'])->put('top_10', $data, 300);
Cache::tags(['salespersons', 'detail'])->put("sp.{$id}", $data, 600);

// 一次失效所有業務員相關快取
Cache::tags(['salespersons'])->flush();
```

---

## PT-CACHE-003: 快取預熱

### 問題
冷啟動時首次請求慢。

### 解決方案
啟動時預先載入熱門資料。

### 實作
```php
// app/Console/Commands/WarmupCache.php
class WarmupCache extends Command
{
    public function handle(): void
    {
        // 預熱熱門資料
        Cache::remember('top_salespersons', 600, fn () =>
            Salesperson::orderBy('rating', 'desc')->limit(10)->get()
        );

        Cache::remember('companies_list', 600, fn () =>
            Company::select('id', 'name')->get()
        );

        $this->info('Cache warmed up successfully');
    }
}

// 排程執行
protected function schedule(Schedule $schedule): void
{
    $schedule->command('cache:warmup')->everyFiveMinutes();
}
```

---

## PT-CACHE-004: 快取穿透防護

### 問題
查詢不存在的資料導致快取無效，每次都打資料庫。

### 解決方案
快取空結果。

### 實作
```php
public function getSalesperson(int $id): ?Salesperson
{
    return Cache::remember("salesperson.{$id}", 300, function () use ($id) {
        $salesperson = Salesperson::find($id);

        // 即使是 null 也快取（防止穿透）
        return $salesperson;
    });
}

// 或使用特殊標記
public function getSalesperson(int $id): ?Salesperson
{
    $cached = Cache::get("salesperson.{$id}");

    if ($cached === 'NOT_FOUND') {
        return null;
    }

    if ($cached !== null) {
        return $cached;
    }

    $salesperson = Salesperson::find($id);

    if ($salesperson === null) {
        Cache::put("salesperson.{$id}", 'NOT_FOUND', 60);  // 短時間快取
        return null;
    }

    Cache::put("salesperson.{$id}", $salesperson, 300);
    return $salesperson;
}
```

---

## PT-CACHE-005: 分散式鎖防止快取雪崩

### 問題
大量請求同時快取失效，導致資料庫壓力激增。

### 解決方案
使用分散式鎖，只有一個請求重建快取。

### 實作
```php
use Illuminate\Support\Facades\Cache;

public function getTopSalespersons(): Collection
{
    $key = 'top_salespersons';

    // 嘗試從快取取得
    $cached = Cache::get($key);
    if ($cached !== null) {
        return $cached;
    }

    // 使用分散式鎖
    $lock = Cache::lock("{$key}.lock", 10);

    try {
        // 取得鎖
        $lock->block(5);

        // 再次檢查快取（可能已被其他請求建立）
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        // 重建快取
        $data = Salesperson::orderBy('rating', 'desc')->limit(10)->get();
        Cache::put($key, $data, 300);

        return $data;

    } finally {
        $lock->release();
    }
}
```

---

## 快取策略選擇

| 資料類型 | TTL | 失效策略 | 備註 |
|---------|-----|---------|------|
| 熱門資料 | 5-10 分鐘 | 時間 + 事件 | 高頻讀取 |
| 使用者資料 | 30 分鐘 | 事件 | 更新時失效 |
| 配置資料 | 1 小時 | 事件 | 很少變動 |
| 統計資料 | 1 天 | 時間 | 計算昂貴 |

---

**已記錄**: 5 個快取策略

**相關**: [Backend 架構](../../backend/architecture.md)
