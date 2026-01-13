---
category: lessons-learned
tags: [api, integration, rest, error-handling]
priority: medium
last_updated: 2026-01-14
applies_to: REST API
related_docs: [../../backend/api-design.md, ../../frontend/api-integration.md]
---

# API 整合常見錯誤

## Quick Reference

記錄 API 設計和整合中的常見錯誤。

---

## CM-API-001: 不一致的錯誤回應格式

### 錯誤代碼
```php
// ❌ 錯誤：不同端點使用不同格式
// Endpoint 1
return response()->json(['error' => 'Not found'], 404);

// Endpoint 2
return response()->json(['message' => 'User not found'], 404);

// Endpoint 3
return response()->json([
    'status' => 'error',
    'data' => null,
    'error' => 'Resource not found'
], 404);
```

### 正確做法
```php
// ✅ 正確：統一的 RFC 7807 格式
return response()->json([
    'error' => [
        'code' => 'RESOURCE_NOT_FOUND',
        'message' => '找不到指定資源',
        'details' => [
            'resource' => 'Salesperson',
            'id' => $id,
        ],
    ],
    'meta' => [
        'timestamp' => now()->toIso8601String(),
        'request_id' => request()->id(),
    ],
], 404);
```

---

## CM-API-002: 缺少 API 版本控制

### 問題
API 變更導致舊版本前端崩潰。

### 正確做法
```
✅ 使用 URL 版本控制
/api/v1/users
/api/v2/users

✅ 保持至少兩個版本同時運行
✅ 提前 3 個月公告廢棄
```

---

## CM-API-003: 過度依賴 HTTP 狀態碼

### 錯誤做法
```
使用 HTTP 狀態碼表達所有業務邏輯錯誤
```

### 正確做法
```json
{
  "error": {
    "code": "INSUFFICIENT_BALANCE",
    "message": "餘額不足"
  }
}
```

HTTP 狀態碼只表達 HTTP 層級的狀態，業務錯誤使用 error code。

---

**已記錄**: 3 項 API 整合錯誤

**相關**: [API 設計](../../backend/api-design.md)
