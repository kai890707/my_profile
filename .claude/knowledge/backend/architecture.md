---
category: backend
tags: [laravel, architecture, mvc, design-patterns]
priority: high
last_updated: 2026-01-13
applies_to: Laravel 11, PHP 8.4
related_docs: [api-design.md, database.md, ../workflow/sdd-process.md]
---

# Laravel 架構模式

## Quick Reference

- 架構: MVC + Service Layer
- Controller: 只處理 HTTP，不含業務邏輯
- Model: Eloquent ORM，只含資料操作
- Service: 業務邏輯的核心層
- 資料夾結構: `app/Http/Controllers`, `app/Models`, `app/Services`
- FormRequest: 所有驗證邏輯
- Resource: API 回應格式化
- Policy: 授權邏輯

## 使用場景

**適用於**:
- 所有 Backend 功能開發
- 需要清晰分層的業務邏輯
- 需要可測試的程式碼
- 團隊協作開發

**不適用於**:
- 極簡單的 CRUD（但仍建議遵循架構）
- 一次性腳本（使用 Artisan Command）

## 核心概念

YAMU Backend 採用 **MVC + Service Layer** 架構，這是 Laravel 的最佳實踐演進：

傳統 MVC 的問題:
- Controller 容易變得臃腫
- 業務邏輯散落各處
- 難以測試和重用

我們的解決方案:
- **Controller**: 只負責接收請求、呼叫服務、返回回應
- **Service**: 包含所有業務邏輯，可重用、可測試
- **Model**: 只處理資料庫操作和關聯
- **FormRequest**: 集中所有驗證邏輯
- **Resource**: 統一 API 回應格式

## 架構層級

```
HTTP Request
    ↓
Middleware (認證、授權、CORS)
    ↓
Route (api.php)
    ↓
Controller (處理 HTTP)
    ↓
FormRequest (驗證請求)
    ↓
Service (業務邏輯) ← 核心層
    ↓
Model (資料操作)
    ↓
Database
    ↓
Resource (格式化回應)
    ↓
HTTP Response
```

## 實例代碼

### Controller 層 (薄層)

**檔案**: `app/Http/Controllers/Api/V1/SalespersonController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalespersonRequest;
use App\Http\Requests\UpdateSalespersonRequest;
use App\Http\Resources\SalespersonResource;
use App\Services\SalespersonService;
use Illuminate\Http\JsonResponse;

class SalespersonController extends Controller
{
    public function __construct(
        private readonly SalespersonService $service
    ) {}

    /**
     * Display a listing of salespersons
     */
    public function index(): JsonResponse
    {
        $salespersons = $this->service->getAllSalespersons();
        
        return response()->json([
            'data' => SalespersonResource::collection($salespersons),
        ]);
    }

    /**
     * Store a newly created salesperson
     */
    public function store(StoreSalespersonRequest $request): JsonResponse
    {
        // 驗證已在 FormRequest 完成
        $salesperson = $this->service->createSalesperson(
            $request->validated()
        );
        
        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ], 201);
    }

    /**
     * Display the specified salesperson
     */
    public function show(int $id): JsonResponse
    {
        $salesperson = $this->service->getSalesperson($id);
        
        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ]);
    }

    /**
     * Update the specified salesperson
     */
    public function update(
        UpdateSalespersonRequest $request,
        int $id
    ): JsonResponse {
        $salesperson = $this->service->updateSalesperson(
            $id,
            $request->validated()
        );
        
        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ]);
    }

    /**
     * Remove the specified salesperson
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->deleteSalesperson($id);
        
        return response()->json(null, 204);
    }
}
```

**重點**:
- Controller 非常薄，只有 HTTP 處理
- 所有業務邏輯在 Service
- 使用 Resource 統一回應格式
- 使用依賴注入傳入 Service

### Service 層 (業務邏輯核心)

**檔案**: `app/Services/SalespersonService.php`

```php
<?php

namespace App\Services;

use App\Models\Salesperson;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalespersonService
{
    /**
     * Get all salespersons with pagination
     */
    public function getAllSalespersons(int $perPage = 15): Collection
    {
        return Salesperson::with(['user', 'company'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a single salesperson by ID
     */
    public function getSalesperson(int $id): Salesperson
    {
        return Salesperson::with(['user', 'company'])
            ->findOrFail($id);
    }

    /**
     * Create a new salesperson
     */
    public function createSalesperson(array $data): Salesperson
    {
        return DB::transaction(function () use ($data) {
            // 業務邏輯: 建立 salesperson
            $salesperson = Salesperson::create([
                'user_id' => $data['user_id'],
                'company_id' => $data['company_id'],
                'position' => $data['position'],
                'description' => $data['description'] ?? null,
            ]);

            // 業務邏輯: 處理照片上傳
            if (isset($data['photo'])) {
                $salesperson->updatePhoto($data['photo']);
            }

            // 業務邏輯: 發送通知
            $salesperson->user->notify(
                new SalespersonCreatedNotification($salesperson)
            );

            return $salesperson->load(['user', 'company']);
        });
    }

    /**
     * Update an existing salesperson
     */
    public function updateSalesperson(int $id, array $data): Salesperson
    {
        return DB::transaction(function () use ($id, $data) {
            $salesperson = Salesperson::findOrFail($id);

            // 業務邏輯: 更新欄位
            $salesperson->update([
                'position' => $data['position'] ?? $salesperson->position,
                'description' => $data['description'] ?? $salesperson->description,
            ]);

            // 業務邏輯: 更新照片（如果提供）
            if (isset($data['photo'])) {
                $salesperson->updatePhoto($data['photo']);
            }

            return $salesperson->fresh(['user', 'company']);
        });
    }

    /**
     * Delete a salesperson
     */
    public function deleteSalesperson(int $id): void
    {
        DB::transaction(function () use ($id) {
            $salesperson = Salesperson::findOrFail($id);

            // 業務邏輯: 清理關聯資料
            $salesperson->deletePhoto();
            
            // 軟刪除
            $salesperson->delete();
        });
    }

    /**
     * Search salespersons by criteria
     */
    public function searchSalespersons(array $criteria): Collection
    {
        $query = Salesperson::query();

        // 業務邏輯: 搜尋條件
        if (isset($criteria['name'])) {
            $query->whereHas('user', function ($q) use ($criteria) {
                $q->where('name', 'like', "%{$criteria['name']}%");
            });
        }

        if (isset($criteria['company_id'])) {
            $query->where('company_id', $criteria['company_id']);
        }

        if (isset($criteria['position'])) {
            $query->where('position', 'like', "%{$criteria['position']}%");
        }

        return $query->with(['user', 'company'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

**重點**:
- Service 包含所有業務邏輯
- 使用 DB Transaction 確保資料一致性
- 方法命名清晰，表達業務意圖
- 可以被多個 Controller 重用
- 易於單元測試

### Model 層 (資料操作)

**檔案**: `app/Models/Salesperson.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Salesperson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'position',
        'description',
        'photo_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Accessors
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    /**
     * Helper methods (資料操作相關)
     */
    public function updatePhoto(string $photoBase64): void
    {
        // 刪除舊照片
        $this->deletePhoto();

        // 儲存新照片
        $photo = base64_decode($photoBase64);
        $path = "salespersons/{$this->id}/photo.jpg";
        Storage::disk('public')->put($path, $photo);

        $this->update(['photo_path' => $path]);
    }

    public function deletePhoto(): void
    {
        if ($this->photo_path) {
            Storage::disk('public')->delete($this->photo_path);
            $this->update(['photo_path' => null]);
        }
    }
}
```

**重點**:
- Model 只處理資料操作和關聯
- 使用 Eloquent 特性 (Relationships, Accessors)
- Helper methods 只處理資料層面的操作
- 不包含業務邏輯決策

### FormRequest 層 (驗證)

**檔案**: `app/Http/Requests/StoreSalespersonRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalespersonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // 授權邏輯在 Policy，這裡返回 true
        return true;
    }

    /**
     * Get the validation rules
     */
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

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'user_id.required' => '使用者 ID 為必填',
            'user_id.exists' => '使用者不存在',
            'company_id.required' => '公司 ID 為必填',
            'company_id.exists' => '公司不存在',
            'position.required' => '職位為必填',
            'position.max' => '職位不可超過 100 字',
        ];
    }
}
```

**重點**:
- 集中所有驗證邏輯
- 清晰的錯誤訊息
- 自動返回 422 錯誤
- Controller 收到的資料已經驗證過

### Resource 層 (回應格式化)

**檔案**: `app/Http/Resources/SalespersonResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalespersonResource extends JsonResource
{
    /**
     * Transform the resource into an array
     */
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

**重點**:
- 統一 API 回應格式
- 只暴露必要的欄位
- 支援 Eager Loading
- 格式化日期時間

## 常見錯誤

### 錯誤 1: 在 Controller 寫業務邏輯

**錯誤示範**:
```php
class SalespersonController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // ❌ 在 Controller 寫業務邏輯
        $salesperson = Salesperson::create($request->all());
        
        if ($request->has('photo')) {
            $photo = base64_decode($request->photo);
            Storage::put("photos/{$salesperson->id}.jpg", $photo);
            $salesperson->update([
                'photo_path' => "photos/{$salesperson->id}.jpg"
            ]);
        }
        
        // 發送通知
        Mail::to($salesperson->user->email)->send(new Welcome());
        
        return response()->json($salesperson, 201);
    }
}
```

**問題**:
- Controller 臃腫難以維護
- 業務邏輯無法重用
- 難以測試
- 違反單一職責原則

**正確做法**:
```php
class SalespersonController extends Controller
{
    public function store(
        StoreSalespersonRequest $request,
        SalespersonService $service
    ): JsonResponse {
        // ✅ 委託給 Service 處理
        $salesperson = $service->createSalesperson(
            $request->validated()
        );
        
        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ], 201);
    }
}
```

### 錯誤 2: 在 Model 寫業務邏輯

**錯誤示範**:
```php
class Salesperson extends Model
{
    // ❌ 在 Model 寫業務邏輯
    public function createWithNotification(array $data): self
    {
        $salesperson = self::create($data);
        
        // 發送通知
        $salesperson->user->notify(new SalespersonCreated());
        
        // 記錄日誌
        Log::info("Salesperson created: {$salesperson->id}");
        
        return $salesperson;
    }
}
```

**問題**:
- Model 應該專注於資料操作
- 業務決策應該在 Service
- 難以模擬和測試

**正確做法**:
```php
// Model: 只處理資料
class Salesperson extends Model
{
    // 只有資料相關的方法
    public function updatePhoto(string $path): void
    {
        $this->update(['photo_path' => $path]);
    }
}

// Service: 處理業務邏輯
class SalespersonService
{
    public function createSalesperson(array $data): Salesperson
    {
        $salesperson = Salesperson::create($data);
        
        // ✅ 業務邏輯在 Service
        $salesperson->user->notify(new SalespersonCreated());
        Log::info("Salesperson created: {$salesperson->id}");
        
        return $salesperson;
    }
}
```

### 錯誤 3: 不使用 FormRequest

**錯誤示範**:
```php
public function store(Request $request): JsonResponse
{
    // ❌ 在 Controller 驗證
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'company_id' => 'required|exists:companies,id',
        'position' => 'required|string|max:100',
    ]);
    
    $salesperson = $this->service->createSalesperson(
        $request->all()
    );
    
    return response()->json($salesperson, 201);
}
```

**問題**:
- 驗證邏輯散落各處
- 無法重用
- Controller 變得臃腫

**正確做法**:
```php
// ✅ 使用 FormRequest
public function store(
    StoreSalespersonRequest $request
): JsonResponse {
    $salesperson = $this->service->createSalesperson(
        $request->validated() // 已驗證的資料
    );
    
    return response()->json([
        'data' => new SalespersonResource($salesperson),
    ], 201);
}
```

## 最佳實踐

### 實作檢查清單

開發新功能時:
- [ ] Controller 只處理 HTTP，不含業務邏輯
- [ ] 業務邏輯全部在 Service
- [ ] 使用 FormRequest 驗證所有請求
- [ ] 使用 Resource 格式化所有回應
- [ ] Model 只處理資料操作和關聯
- [ ] 複雜操作使用 DB Transaction
- [ ] 所有方法都有 PHPDoc

測試時:
- [ ] Service 有完整的 Unit Tests
- [ ] Controller 有 Feature Tests
- [ ] 測試覆蓋率 >= 80%

### 注意事項

**Controller 原則**:
- 保持薄層，每個方法不超過 10 行
- 只處理 HTTP：接收請求、呼叫服務、返回回應
- 不要直接操作 Model

**Service 原則**:
- 一個 Service 對應一個 Model（通常）
- 複雜操作使用 Transaction
- 方法命名表達業務意圖
- 可以呼叫其他 Service

**Model 原則**:
- 只定義關聯和 Accessors/Mutators
- Helper methods 只處理資料操作
- 不要包含業務邏輯決策

## 相關知識

### 前置知識
- [SDD 流程](../workflow/sdd-process.md) - 開發流程
- Laravel 基礎（Eloquent, Routing, Middleware）
- PHP 8.4 特性（Constructor Property Promotion, Readonly Properties）

### 延伸閱讀
- [API 設計](./api-design.md) - RESTful API 規範
- [資料庫設計](./database.md) - DB Schema 設計
- [測試策略](./testing.md) - 如何測試各層級

### 實作流程
1. [本文件] - 理解架構模式
2. [API 設計](./api-design.md) - 設計 API
3. [資料庫設計](./database.md) - 設計 Schema
4. 開始實作

## 決策記錄

### 當前決策 (2026-01-13)

**採用 MVC + Service Layer 的原因**:
- 原因 1: Controller 保持薄層，易於維護
- 原因 2: 業務邏輯集中在 Service，可重用可測試
- 原因 3: 符合 Laravel 社群最佳實踐
- 原因 4: 清晰的職責分離

**考慮的替代方案**:
- 方案 A (Action Pattern): 過於細碎，增加檔案數量
- 方案 B (Repository Pattern): 增加抽象層，專案規模不需要

**為什麼不使用 Repository Pattern**:
- 專案規模中小，Eloquent 已經是很好的抽象
- Repository 會增加不必要的複雜度
- Service Layer 已經足夠提供可測試性

### 歷史演進

**2026-01-13**: 確立架構模式
- 採用 MVC + Service Layer
- 定義各層職責
- 建立程式碼範例

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
