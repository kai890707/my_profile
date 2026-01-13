---
category: backend
tags: [error-handling, exceptions, laravel]
priority: medium
last_updated: 2026-01-13
applies_to: Laravel 11
related_docs: [api-design.md]
---

# 錯誤處理

## Quick Reference

- 錯誤格式: RFC 7807 Problem Details
- 狀態碼: 4xx (客戶端錯誤), 5xx (伺服器錯誤)
- Exception Handler: `app/Exceptions/Handler.php`
- 自動處理: ModelNotFoundException → 404

## 使用場景

**適用於**:
- API 錯誤回應
- 異常處理

## 核心概念

統一錯誤回應格式，提供清晰的錯誤資訊。

## 錯誤回應格式

```json
{
  "type": "https://yamu.com/errors/not-found",
  "title": "Resource Not Found",
  "status": 404,
  "detail": "Salesperson with ID 999 not found.",
  "instance": "/api/v1/salespersons/999"
}
```

## Exception Handler

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->wantsJson()) {
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'type' => 'https://yamu.com/errors/not-found',
                    'title' => 'Resource Not Found',
                    'status' => 404,
                    'detail' => 'The requested resource was not found.',
                ], 404);
            }
        }

        return parent::render($request, $e);
    }
}
```

## 常見錯誤處理

- `ModelNotFoundException`: 404 Not Found
- `ValidationException`: 422 Unprocessable Entity
- `AuthenticationException`: 401 Unauthorized
- `AuthorizationException`: 403 Forbidden

## 最佳實踐

- [ ] 使用標準 HTTP 狀態碼
- [ ] 提供清晰的錯誤訊息
- [ ] 不洩漏敏感資訊
- [ ] 記錄伺服器錯誤到 Log

## 相關知識

- [API 設計](./api-design.md) - API 錯誤規範

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
