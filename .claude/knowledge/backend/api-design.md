---
category: backend
tags: [api, rest, openapi, http]
priority: high
last_updated: 2026-01-13
applies_to: Laravel 11, OpenAPI 3.1
related_docs: [architecture.md, validation.md, error-handling.md]
---

# API 設計規範

## Quick Reference

- API 版本: v1 (URL: `/api/v1`)
- 認證方式: JWT Bearer Token
- 回應格式: JSON
- 日期格式: ISO 8601
- 分頁: Query參數 `page`, `per_page`
- 錯誤格式: RFC 7807 Problem Details
- 文檔: OpenAPI 3.1 (Swagger UI)

## 使用場景

**適用於**:
- 所有 RESTful API 設計
- Frontend 與 Backend 通信
- 第三方系統整合
- Mobile App API

**不適用於**:
- GraphQL API（專案未使用）
- WebSocket 長連接（使用其他機制）

## 核心概念

YAMU Backend API 遵循 RESTful 設計原則，提供清晰、一致、可預測的 API 介面。

**REST 核心原則**:
1. **資源導向**: URL 表示資源，不是動作
2. **HTTP 方法**: 使用標準方法表達操作意圖
3. **無狀態**: 每個請求包含完整的認證和上下文
4. **統一介面**: 一致的 URL 結構和回應格式

## API 設計規範

### URL 命名規範

**基本格式**:
```
/api/{version}/{resource}
/api/{version}/{resource}/{id}
/api/{version}/{resource}/{id}/{sub-resource}
```

**規則**:
- 使用小寫字母
- 使用複數形式表示集合
- 使用連字號 `-` 分隔單字
- 避免動詞，使用名詞表示資源

**正確範例**:
```
GET  /api/v1/salespersons          # 獲取業務員列表
GET  /api/v1/salespersons/123      # 獲取特定業務員
GET  /api/v1/salespersons/123/reviews  # 獲取業務員的評論
POST /api/v1/auth/login            # 登入（auth 是例外）
```

**錯誤範例**:
```
❌ GET /api/v1/getSalespersons       # 不要用動詞
❌ GET /api/v1/salesperson/123       # 使用複數形式
❌ GET /api/v1/salesPersons          # 使用小寫
❌ GET /api/v1/salesperson_list      # 使用連字號而非底線
```

### HTTP 方法使用

| 方法 | 用途 | 範例 | 冪等性 |
|------|------|------|-------|
| GET | 獲取資源 | `GET /salespersons` | ✅ 是 |
| POST | 建立資源 | `POST /salespersons` | ❌ 否 |
| PUT | 完整更新 | `PUT /salespersons/123` | ✅ 是 |
| PATCH | 部分更新 | `PATCH /salespersons/123` | ❌ 否 |
| DELETE | 刪除資源 | `DELETE /salespersons/123` | ✅ 是 |

### 狀態碼使用

**成功回應**:
- `200 OK` - GET, PUT, PATCH 成功
- `201 Created` - POST 建立成功
- `204 No Content` - DELETE 成功

**客戶端錯誤**:
- `400 Bad Request` - 請求格式錯誤
- `401 Unauthorized` - 未認證
- `403 Forbidden` - 無權限
- `404 Not Found` - 資源不存在
- `422 Unprocessable Entity` - 驗證失敗

**伺服器錯誤**:
- `500 Internal Server Error` - 伺服器錯誤

### 請求格式

**Headers**:
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {access_token}
```

**Query Parameters** (用於 GET):
```
GET /api/v1/salespersons?page=1&per_page=15&sort=created_at&order=desc
```

**Request Body** (用於 POST/PUT/PATCH):
```json
{
  "user_id": 1,
  "company_id": 2,
  "position": "Senior Sales",
  "description": "Experienced salesperson"
}
```

### 回應格式

**成功回應 - 單一資源**:
```json
{
  "data": {
    "id": 123,
    "user": {
      "id": 1,
      "name": "John Doe"
    },
    "company": {
      "id": 2,
      "name": "ABC Corp"
    },
    "position": "Senior Sales",
    "created_at": "2026-01-13T10:00:00Z"
  }
}
```

**成功回應 - 集合**:
```json
{
  "data": [
    {
      "id": 123,
      "position": "Senior Sales"
    },
    {
      "id": 124,
      "position": "Junior Sales"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  },
  "links": {
    "first": "/api/v1/salespersons?page=1",
    "last": "/api/v1/salespersons?page=3",
    "next": "/api/v1/salespersons?page=2",
    "prev": null
  }
}
```

**錯誤回應** (RFC 7807):
```json
{
  "type": "https://yamu.com/errors/validation-failed",
  "title": "Validation Failed",
  "status": 422,
  "detail": "The given data was invalid.",
  "errors": {
    "user_id": [
      "The user id field is required."
    ],
    "position": [
      "The position must not be greater than 100 characters."
    ]
  }
}
```

## 實例代碼

### 標準 CRUD API

**檔案**: `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\V1\SalespersonController;

Route::prefix('v1')->group(function () {
    Route::middleware('auth:api')->group(function () {
        // Salesperson CRUD
        Route::apiResource('salespersons', SalespersonController::class);
        
        // 自定義端點
        Route::get('salespersons/{id}/reviews', [SalespersonController::class, 'reviews']);
        Route::post('salespersons/{id}/favorite', [SalespersonController::class, 'favorite']);
    });
});
```

### Controller 實作

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalespersonRequest;
use App\Http\Resources\SalespersonResource;
use App\Services\SalespersonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalespersonController extends Controller
{
    public function __construct(
        private readonly SalespersonService $service
    ) {}

    /**
     * @OA\Get(
     *     path="/api/v1/salespersons",
     *     tags={"Salespersons"},
     *     summary="Get list of salespersons",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Salesperson"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $salespersons = $this->service->getAllSalespersons($perPage);
        
        return response()->json([
            'data' => SalespersonResource::collection($salespersons),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/salespersons",
     *     tags={"Salespersons"},
     *     summary="Create a new salesperson",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSalespersonRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Salesperson created",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Salesperson")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StoreSalespersonRequest $request): JsonResponse
    {
        $salesperson = $this->service->createSalesperson(
            $request->validated()
        );
        
        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/salespersons/{id}",
     *     tags={"Salespersons"},
     *     summary="Get salesperson by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Salesperson")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Salesperson not found"
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $salesperson = $this->service->getSalesperson($id);
        
        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ]);
    }
}
```

### 分頁實作

```php
<?php

namespace App\Services;

use App\Models\Salesperson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalespersonService
{
    public function getAllSalespersons(
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Salesperson::with(['user', 'company']);
        
        // 應用篩選
        if (!empty($filters['position'])) {
            $query->where('position', 'like', "%{$filters['position']}%");
        }
        
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        
        // 排序
        $query->orderBy('created_at', 'desc');
        
        // 分頁
        return $query->paginate($perPage);
    }
}
```

### Resource 實作

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Salesperson",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=123),
 *     @OA\Property(property="position", type="string", example="Senior Sales"),
 *     @OA\Property(property="description", type="string", example="Experienced salesperson"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class SalespersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'position' => $this->position,
            'description' => $this->description,
            'photo_url' => $this->photo_url,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

## 常見錯誤

### 錯誤 1: URL 使用動詞

**錯誤示範**:
```
❌ POST /api/v1/createSalesperson
❌ GET  /api/v1/getSalespersonList
❌ POST /api/v1/deleteSalesperson/123
```

**正確做法**:
```
✅ POST   /api/v1/salespersons
✅ GET    /api/v1/salespersons
✅ DELETE /api/v1/salespersons/123
```

### 錯誤 2: 不一致的回應格式

**錯誤示範**:
```json
// 端點 A
{
  "salesperson": { "id": 123 }
}

// 端點 B
{
  "data": { "id": 123 }
}

// 端點 C
{ "id": 123 }
```

**正確做法**:
```json
// 所有端點統一使用 "data" 包裝
{
  "data": { "id": 123 }
}
```

### 錯誤 3: 錯誤的狀態碼

**錯誤示範**:
```php
// 資源不存在但返回 200
public function show($id): JsonResponse
{
    $salesperson = Salesperson::find($id);
    
    if (!$salesperson) {
        return response()->json([
            'error' => 'Not found'
        ], 200); // ❌ 應該是 404
    }
    
    return response()->json($salesperson);
}
```

**正確做法**:
```php
public function show($id): JsonResponse
{
    $salesperson = Salesperson::findOrFail($id); // ✅ 自動拋出 404
    
    return response()->json([
        'data' => new SalespersonResource($salesperson),
    ]);
}
```

## 最佳實踐

### 實作檢查清單

設計 API 時:
- [ ] URL 使用名詞，不用動詞
- [ ] 使用正確的 HTTP 方法
- [ ] 返回正確的狀態碼
- [ ] 回應格式統一（使用 Resource）
- [ ] 包含完整的 OpenAPI 文檔註解
- [ ] 實作分頁（集合端點）
- [ ] 實作篩選和排序（GET 端點）
- [ ] 錯誤回應符合 RFC 7807

安全性:
- [ ] 所有端點都需要認證（除了公開端點）
- [ ] 使用 Policy 進行授權檢查
- [ ] 輸入驗證（使用 FormRequest）
- [ ] 避免過度暴露資料（使用 Resource）

### 注意事項

**版本管理**:
- 在 URL 中包含版本號 `/api/v1`
- 不要輕易破壞向下兼容
- 新版本可以與舊版本共存

**效能考量**:
- 使用 Eager Loading 避免 N+1 問題
- 大列表必須分頁
- 考慮使用快取機制

**文檔**:
- 使用 OpenAPI 註解
- 提供範例請求和回應
- 說明所有可能的錯誤情況

## 相關知識

### 前置知識
- [架構模式](./architecture.md) - Controller 和 Resource 使用
- [驗證規範](./validation.md) - FormRequest 詳細說明
- HTTP 協議基礎

### 延伸閱讀
- [錯誤處理](./error-handling.md) - 統一錯誤回應
- [測試策略](./testing.md) - API 測試方法
- OpenAPI 3.1 規範

### 實作流程
1. [本文件] - 設計 API
2. [架構模式](./architecture.md) - 實作 Controller
3. [驗證規範](./validation.md) - 實作驗證
4. [測試策略](./testing.md) - 撰寫測試

## 決策記錄

### 當前決策 (2026-01-13)

**採用 RESTful 設計的原因**:
- 原因 1: 標準化，易於理解和使用
- 原因 2: 前端開發友好
- 原因 3: 工具支援完善（Swagger, Postman）
- 原因 4: 符合 HTTP 語意

**為什麼不使用 GraphQL**:
- 專案規模不需要 GraphQL 的靈活性
- REST 對團隊更熟悉
- 減少學習成本

**採用 OpenAPI 3.1 的原因**:
- 自動生成 API 文檔
- 與前端團隊溝通的標準
- 可用於自動化測試

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
