---
category: backend
tags: [database, mysql, migrations, eloquent]
priority: high
last_updated: 2026-01-13
applies_to: MySQL 8.0, Laravel 11
related_docs: [architecture.md, api-design.md]
---

# 資料庫設計

## Quick Reference

- 資料庫: MySQL 8.0
- ORM: Eloquent
- Migration: Laravel Migrations
- 命名規範: snake_case, 複數表名
- 主鍵: `id` BIGINT UNSIGNED AUTO_INCREMENT
- 時間戳: `created_at`, `updated_at`, `deleted_at`
- 外鍵: `{resource}_id` BIGINT UNSIGNED
- 索引: 查詢欄位必須加索引

## 使用場景

**適用於**:
- 所有資料表設計
- Migration 撰寫
- 資料庫優化

**不適用於**:
- NoSQL 資料（專案使用 MySQL）

## 核心概念

資料庫設計遵循正規化原則，確保資料一致性和完整性。使用 Laravel Migrations 管理 Schema 變更。

## 表設計規範

### 命名規範

- 表名: 小寫複數名詞 `salespersons`, `companies`
- 欄位: snake_case `user_id`, `created_at`
- 索引: `idx_{table}_{column}` 或 `uk_{table}_{columns}` (unique)
- 外鍵: `fk_{table}_{ref_table}`

### 標準欄位

每個表必須包含:
```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL  -- 如果需要軟刪除
```

### Migration 範例

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salespersons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('position', 100);
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index('user_id');
            $table->index('company_id');
            $table->index('created_at');
            
            // 唯一約束
            $table->unique(['user_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salespersons');
    }
};
```

## 關聯設計

### 一對多關聯

```php
// Company has many Salespersons
class Company extends Model
{
    public function salespersons(): HasMany
    {
        return $this->hasMany(Salesperson::class);
    }
}

class Salesperson extends Model
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

### 多對多關聯

```php
// User favorites many Salespersons
class User extends Model
{
    public function favoriteSalespersons(): BelongsToMany
    {
        return $this->belongsToMany(Salesperson::class, 'user_favorites')
            ->withTimestamps();
    }
}
```

## 最佳實踐

- [ ] 所有外鍵加索引
- [ ] 查詢欄位加索引
- [ ] 使用軟刪除而非真實刪除
- [ ] Migration 可回滾
- [ ] 使用 Transaction 確保一致性

## 相關知識

- [架構模式](./architecture.md) - Model 使用
- [API 設計](./api-design.md) - 資料結構
- MySQL 效能優化

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
