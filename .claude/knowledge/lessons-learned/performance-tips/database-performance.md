---
category: lessons-learned
tags: [performance, database, mysql, optimization]
priority: high
last_updated: 2026-01-14
applies_to: MySQL 8.0
related_docs: [../../backend/database.md]
---

# 資料庫效能優化技巧

## Quick Reference

記錄實際驗證有效的資料庫效能優化技巧。

---

## PT-DB-001: 索引優化

### 複合索引順序

**原則**: 將選擇性高的欄位放前面

```sql
-- ❌ 錯誤：選擇性低的在前
CREATE INDEX idx_wrong ON salespersons(status, company_id, rating);
-- status 只有 2-3 個值（active/inactive），選擇性低

-- ✅ 正確：選擇性高的在前
CREATE INDEX idx_right ON salespersons(company_id, rating, status);
-- company_id 有 1000+ 個值，選擇性高
```

### 效能數據
- 查詢時間: 800ms → 15ms (53x)
- 索引使用率: 30% → 95%

---

## PT-DB-002: 避免 SELECT *

### 實作
```sql
-- ❌ Before: 查詢所有欄位
SELECT * FROM users;  -- 返回 15 個欄位

-- ✅ After: 只查詢需要的
SELECT id, name, email FROM users;  -- 返回 3 個欄位
```

### 效能數據
- 資料傳輸量: 減少 70%
- 查詢時間: 減少 35%
- 記憶體使用: 減少 60%

---

## PT-DB-003: 批次插入

### 實作
```php
// ❌ Before: 逐筆插入
foreach ($data as $item) {
    DB::table('logs')->insert($item);  // 1000 次查詢
}

// ✅ After: 批次插入
DB::table('logs')->insert($data);  // 1 次查詢
```

### 效能數據
- 插入時間: 30s → 0.5s (60x)
- 資料庫連接: 1000 → 1

---

## PT-DB-004: 查詢結果限制

### 實作
```php
// ❌ Before: 無限制
$users = User::all();  // 可能返回 100 萬筆

// ✅ After: 分頁
$users = User::paginate(20);  // 每頁 20 筆
```

---

**已記錄**: 4 個資料庫優化技巧

**相關**: [資料庫設計](../../backend/database.md)
