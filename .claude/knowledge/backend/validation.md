---
category: backend
tags: [validation, form-request, laravel]
priority: medium
last_updated: 2026-01-13
applies_to: Laravel 11
related_docs: [api-design.md, architecture.md]
---

# 驗證規範

## Quick Reference

- 驗證方式: FormRequest
- 位置: `app/Http/Requests/`
- 自動返回: 422 錯誤（驗證失敗）
- 錯誤格式: JSON with field errors
- 常用規則: required, string, integer, exists, unique

## 使用場景

**適用於**:
- 所有 API 輸入驗證
- 表單資料驗證

## 核心概念

使用 FormRequest 集中管理驗證邏輯，確保資料正確性。

## FormRequest 範例

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalespersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 授權在 Policy
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'position' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'string'], // Base64
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => '使用者 ID 為必填',
            'user_id.exists' => '使用者不存在',
            'position.max' => '職位不可超過 100 字',
        ];
    }
}
```

## 常用驗證規則

- `required`: 必填
- `nullable`: 可為 null
- `string`: 字串
- `integer`: 整數
- `email`: Email 格式
- `max:n`: 最大長度/值
- `min:n`: 最小長度/值
- `exists:table,column`: 存在於資料庫
- `unique:table,column`: 唯一值

## 最佳實踐

- [ ] 所有 Controller 使用 FormRequest
- [ ] 提供清晰的錯誤訊息
- [ ] 驗證規則放在 FormRequest
- [ ] 使用 `validated()` 獲取已驗證資料

## 相關知識

- [API 設計](./api-design.md) - API 錯誤回應
- [架構模式](./architecture.md) - Controller 使用

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
