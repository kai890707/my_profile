---
category: backend
tags: [testing, pest, phpstan, laravel]
priority: high
last_updated: 2026-01-13
applies_to: Laravel 11, Pest 3.x, PHPStan Level 9
related_docs: [architecture.md, ../workflow/sdd-process.md]
---

# 測試策略

## Quick Reference

- 測試框架: Pest 3.x
- 靜態分析: PHPStan Level 9
- 程式碼風格: Laravel Pint
- 覆蓋率目標: >= 80%
- 測試類型: Feature Tests (API), Unit Tests (邏輯)

## 使用場景

**適用於**:
- 所有功能開發
- Bug 修復驗證
- 重構安全網

## 核心概念

測試是品質保證的基礎，確保程式碼按預期運作。

## Feature Test 範例

**檔案**: `tests/Feature/SalespersonTest.php`

```php
<?php

use App\Models\User;
use App\Models\Company;
use App\Models\Salesperson;

test('can create salesperson', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/v1/salespersons', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'position' => 'Senior Sales',
        ]);
    
    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'position', 'created_at']
        ]);
    
    $this->assertDatabaseHas('salespersons', [
        'user_id' => $user->id,
        'position' => 'Senior Sales',
    ]);
});

test('cannot create salesperson without authentication', function () {
    $response = $this->postJson('/api/v1/salespersons', [
        'user_id' => 1,
        'company_id' => 1,
        'position' => 'Sales',
    ]);
    
    $response->assertStatus(401);
});

test('validates required fields', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/v1/salespersons', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['user_id', 'company_id', 'position']);
});
```

## Unit Test 範例

**檔案**: `tests/Unit/SalespersonServiceTest.php`

```php
<?php

use App\Services\SalespersonService;
use App\Models\Salesperson;
use App\Models\User;
use App\Models\Company;

test('service can create salesperson', function () {
    $service = new SalespersonService();
    $user = User::factory()->create();
    $company = Company::factory()->create();
    
    $salesperson = $service->createSalesperson([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'position' => 'Manager',
    ]);
    
    expect($salesperson)
        ->toBeInstanceOf(Salesperson::class)
        ->position->toBe('Manager');
});
```

## 執行測試

```bash
# 所有測試
composer test

# 特定測試
php artisan test --filter=SalespersonTest

# 測試覆蓋率
composer test:coverage

# 靜態分析
composer analyse

# 程式碼風格
composer format
```

## 測試策略

### Feature Tests (API 測試)
- 測試完整的 HTTP 請求流程
- 測試認證和授權
- 測試驗證邏輯
- 測試資料庫操作

### Unit Tests (單元測試)
- 測試 Service 方法
- 測試業務邏輯
- 測試 Model 方法
- 測試 Helper 函數

## 最佳實踐

- [ ] 每個功能都有 Feature Test
- [ ] 複雜邏輯有 Unit Test
- [ ] 測試覆蓋率 >= 80%
- [ ] 使用 Factory 產生測試資料
- [ ] 測試命名清晰易懂
- [ ] 使用 RefreshDatabase trait

## 相關知識

- [SDD 流程](../workflow/sdd-process.md) - 測試在開發流程中的角色
- [架構模式](./architecture.md) - 如何測試各層級

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
