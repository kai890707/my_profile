---
category: lessons-learned
tags: [database, mysql, migration, performance]
priority: high
last_updated: 2026-01-14
applies_to: MySQL 8.0
related_docs: [../../backend/database.md]
---

# 資料庫常見錯誤

## Quick Reference

記錄 MySQL 資料庫設計和使用中的常見錯誤。

**目標**: 避免資料庫效能問題、資料完整性問題和設計缺陷。

---

## CM-DB-001: 缺少外鍵約束

### 情境
資料不一致，孤兒記錄產生。

### 錯誤代碼
```php
// ❌ 錯誤：沒有外鍵約束
Schema::create('salespersons', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');  // 只是數字，沒有約束
    $table->unsignedBigInteger('company_id');
    $table->timestamps();
});

// 問題：user 被刪除後，salesperson 記錄仍存在（孤兒記錄）
```

### 正確做法
```php
// ✅ 正確：使用外鍵約束
Schema::create('salespersons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
          ->constrained()
          ->onDelete('cascade');  // 刪除 user 時自動刪除 salesperson
    $table->foreignId('company_id')
          ->constrained()
          ->onDelete('restrict');  // 防止刪除有業務員的公司
    $table->timestamps();
});
```

### 預防措施
- [ ] 所有關聯都使用外鍵
- [ ] 明確定義 onDelete 行為
- [ ] 測試刪除行為

---

## CM-DB-002: 使用 SELECT *

### 錯誤代碼
```php
// ❌ 錯誤：查詢所有欄位
$users = DB::table('users')->select('*')->get();
```

### 正確做法
```php
// ✅ 正確：只查詢需要的欄位
$users = DB::table('users')
    ->select(['id', 'name', 'email'])
    ->get();
```

### 效能數據
- 資料傳輸量: 減少 60%
- 查詢時間: 降低 30%

---

## CM-DB-003: 忽略 Migration 回滾

### 錯誤代碼
```php
// ❌ 錯誤：down() 方法為空
public function down(): void
{
    //
}
```

### 正確做法
```php
// ✅ 正確：完整的回滾邏輯
public function down(): void
{
    Schema::dropIfExists('salespersons');
}
```

---

**已記錄**: 3 項資料庫常見錯誤

**相關**: [資料庫設計](../../backend/database.md)
