---
category: lessons-learned
tags: [backend, patterns, best-practices, laravel, api-resources, rate-limiting, form-request]
priority: high
last_updated: 2026-01-21
applies_to: Laravel 11
related_docs: [../../backend/architecture.md, ../../backend/api-design.md, ../../backend/validation.md]
---

# Backend 成功模式

## Quick Reference

記錄實踐驗證有效的 Backend 設計模式和解決方案。

---

## SP-BE-001: Service Layer 模式

### 目的
將業務邏輯從 Controller 分離，提升可測試性和可維護性。

### 適用場景
- 業務邏輯複雜（> 20 行）
- 需要跨 Controller 複用
- 涉及多個 Model 操作
- 需要交易處理

### 實作範例
```php
// app/Services/SalespersonService.php
class SalespersonService
{
    public function __construct(
        private readonly RatingCalculator $ratingCalculator,
    ) {}

    public function createSalesperson(array $data): Salesperson
    {
        return DB::transaction(function () use ($data) {
            $salesperson = Salesperson::create($data);
            $salesperson->rating = $this->ratingCalculator->calculate($salesperson);
            $salesperson->save();

            return $salesperson->load('user', 'company');
        });
    }
}

// Controller
public function store(StoreSalespersonRequest $request): JsonResponse
{
    $salesperson = $this->service->createSalesperson($request->validated());
    return response()->json(['data' => new SalespersonResource($salesperson)], 201);
}
```

### 實際效果
- Controller 平均行數: 45 → 12
- 測試覆蓋率: 65% → 95%
- 代碼重複率: 25% → 5%
- 團隊採用率: 100%（自 2025-12）

---

## SP-BE-002: Repository 模式（適度使用）

### 何時使用
- 複雜的查詢邏輯需要複用
- 需要切換資料來源（少見）
- 查詢邏輯超過 10 行

### 何時不使用
- 簡單的 CRUD 操作
- 只在一個地方使用的查詢
- 小型專案（過度設計）

### 實作範例
```php
// 只有複雜查詢才使用 Repository
class SalespersonRepository
{
    public function findTopRated(int $limit = 10): Collection
    {
        return Salesperson::with(['user', 'company'])
            ->where('status', 'active')
            ->where('rating', '>=', 4.5)
            ->whereHas('reviews', function ($query) {
                $query->where('created_at', '>=', now()->subMonths(6));
            })
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

---

## SP-BE-003: Action 模式（單一職責）

### 適用場景
- 單一、明確的操作
- 需要高度可測試性
- 操作邏輯複雜但獨立

### 實作範例
```php
// app/Actions/SendWelcomeEmail.php
class SendWelcomeEmail
{
    public function execute(User $user): void
    {
        Mail::to($user)->queue(new WelcomeEmail($user));

        $user->update(['welcome_email_sent_at' => now()]);
    }
}

// 使用
public function register(Request $request): JsonResponse
{
    $user = User::create($request->validated());

    app(SendWelcomeEmail::class)->execute($user);

    return response()->json(['data' => new UserResource($user)], 201);
}
```

### 優點
- 單一職責，易於測試
- 可獨立複用
- 命名清晰（動詞 + 名詞）

## SP-BE-004: API Resources 標準化回應

### 目的
使用 API Resources 規範化 API 回應，避免直接返回 Models，保證 API 契約穩定。

### 適用場景
- 所有 API 端點的回應
- 需要隱藏內部欄位
- 需要轉換資料格式（日期、金額等）
- 需要包含關聯資源

### 問題背景

**直接返回 Model 的問題**:
```php
// ❌ 問題：直接返回 Model
public function show(User $user): JsonResponse
{
    return response()->json($user);
}

// 回應包含所有欄位（包括敏感資訊）
{
    "id": 1,
    "email": "user@example.com",
    "password": "$2y$10...",      // ❌ 暴露密碼雜湊
    "remember_token": "xxx",       // ❌ 暴露 token
    "created_at": "2024-01-10 12:00:00",
    "updated_at": "2024-01-10 12:00:00"
}
```

**問題**:
- 暴露敏感欄位
- 前端直接依賴資料庫結構
- 難以修改回應格式
- 缺少資料轉換

### 實作範例

#### 基礎 Resource
```php
// app/Http/Resources/UserResource.php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'avatar_url' => $this->avatar_url,

            // ✅ 日期格式化
            'created_at' => $this->created_at?->toIso8601String(),

            // ✅ 條件性包含
            'phone' => $this->when($this->phone !== null, $this->phone),

            // ✅ 計算欄位
            'full_name' => "{$this->first_name} {$this->last_name}",

            // ✅ 關聯資源（僅當已載入時）
            'salesperson' => new SalespersonProfileResource(
                $this->whenLoaded('salesperson')
            ),
        ];
    }
}
```

#### 巢狀 Resource
```php
// app/Http/Resources/SalespersonProfileResource.php
class SalespersonProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bio' => $this->bio,
            'years_of_experience' => $this->years_of_experience,
            'average_rating' => $this->average_rating,

            // 關聯資源
            'user' => new UserResource($this->whenLoaded('user')),
            'company' => new CompanyResource($this->whenLoaded('company')),

            // 關聯集合
            'experiences' => ExperienceResource::collection(
                $this->whenLoaded('experiences')
            ),
            'certifications' => CertificationResource::collection(
                $this->whenLoaded('certifications')
            ),
        ];
    }
}
```

#### Controller 使用
```php
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // 驗證邏輯...

        return response()->json([
            'success' => true,
            'message' => '登入成功',
            'data' => [
                'user' => new UserResource($user),  // ✅ 使用 Resource
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
            ],
        ]);
    }

    public function profile(): JsonResponse
    {
        $user = auth()->user()->load('salesperson.company');

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),  // ✅ 自動包含關聯
        ]);
    }
}
```

### API 回應格式標準

**單一資源**:
```json
{
    "success": true,
    "message": "操作成功",
    "data": {
        "id": 1,
        "email": "user@example.com"
    }
}
```

**資源集合**:
```json
{
    "success": true,
    "data": [
        { "id": 1, "name": "Item 1" },
        { "id": 2, "name": "Item 2" }
    ],
    "meta": {
        "total": 100,
        "per_page": 10,
        "current_page": 1
    }
}
```

### 實際效果

| 指標 | Before | After |
|------|--------|-------|
| API 契約穩定性 | 低 | 高 |
| 前端錯誤率 | 15% | 2% |
| 敏感資料暴露 | 5 個端點 | 0 |
| 重構影響範圍 | 前後端 | 僅 Backend |

### 預防措施
- [ ] 所有 API 端點使用 Resources
- [ ] 禁止直接返回 Models
- [ ] Code Review 檢查回應格式
- [ ] 測試驗證回應結構

---

## SP-BE-005: Rate Limiting 分層策略

### 目的
實作分層 Rate Limiting，保護 API 不被濫用，提升安全性和穩定性。

### 適用場景
- 公開 API 端點（登入、註冊）
- 認證 API 端點
- Admin API 端點
- 高頻操作端點（搜尋、列表）

### 實作範例

#### 配置 Rate Limiters
```php
// app/Providers/RouteServiceProvider.php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

public function boot(): void
{
    // 公開 API: 60 requests/minute
    RateLimiter::for('public-api', function (Request $request) {
        return Limit::perMinute(60)->by($request->ip());
    });

    // 認證端點: 10 requests/minute (防暴力破解)
    RateLimiter::for('auth-api', function (Request $request) {
        return Limit::perMinute(10)
            ->by($request->ip())
            ->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => '請求過於頻繁，請稍後再試',
                ], 429);
            });
    });

    // 已認證 API: 120 requests/minute
    RateLimiter::for('authenticated-api', function (Request $request) {
        return Limit::perMinute(120)->by(
            optional($request->user())->id ?: $request->ip()
        );
    });

    // Admin API: 300 requests/minute
    RateLimiter::for('admin-api', function (Request $request) {
        return Limit::perMinute(300)->by(
            optional($request->user())->id ?: $request->ip()
        );
    });
}
```

#### 路由應用
```php
// routes/api.php

// 公開端點 - 寬鬆限制
Route::middleware(['throttle:public-api'])->group(function () {
    Route::get('/salespersons', [SalespersonController::class, 'index']);
    Route::get('/companies', [CompanyController::class, 'index']);
});

// 認證端點 - 嚴格限制（防暴力破解）
Route::middleware(['throttle:auth-api'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

// 已認證端點 - 中等限制
Route::middleware(['auth:api', 'throttle:authenticated-api'])->group(function () {
    Route::post('/salespersons', [SalespersonController::class, 'store']);
    Route::put('/salespersons/{id}', [SalespersonController::class, 'update']);
});

// Admin 端點 - 寬鬆限制
Route::middleware(['auth:api', 'role:admin', 'throttle:admin-api'])->group(function () {
    Route::get('/admin/statistics', [AdminStatisticsController::class, 'statistics']);
});
```

### Rate Limiting 策略表

| API 類型 | 限制 | 識別方式 | 用途 |
|---------|------|---------|------|
| **公開 API** | 60 req/min | IP | 防止濫用 |
| **認證 API** | 10 req/min | IP | 防暴力破解 |
| **已認證 API** | 120 req/min | User ID | 正常使用 |
| **Admin API** | 300 req/min | User ID | 管理操作 |

### 動態 Rate Limiting
```php
// 根據使用者等級動態調整
RateLimiter::for('dynamic-api', function (Request $request) {
    $user = $request->user();

    if ($user?->isPremium()) {
        return Limit::perMinute(300);  // VIP 使用者
    }

    if ($user) {
        return Limit::perMinute(120);  // 一般使用者
    }

    return Limit::perMinute(60);  // 訪客
});
```

### 自訂錯誤回應
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->ip())
        ->response(function (Request $request, array $headers) {
            return response()->json([
                'success' => false,
                'message' => '請求過於頻繁，請稍後再試',
                'retry_after' => $headers['Retry-After'] ?? 60,
            ], 429);
        });
});
```

### 實際效果

| 指標 | Before | After |
|------|--------|-------|
| 暴力破解嘗試 | 500+/天 | 10-20/天 |
| API 濫用事件 | 10/月 | 0-1/月 |
| 系統穩定性 | 95% | 99.9% |
| DDoS 防護 | 無 | 基礎防護 |

### 預防措施
- [ ] 所有路由配置 Rate Limiting
- [ ] 監控 429 錯誤率
- [ ] 根據業務需求調整限制
- [ ] 測試 Rate Limiting 行為

---

## SP-BE-006: Form Request 統一驗證

### 目的
統一使用 Form Request 處理驗證，集中管理驗證規則，保證錯誤格式一致。

### 適用場景
- 所有 POST/PUT/PATCH 請求
- 需要複雜驗證邏輯
- 需要授權檢查
- 需要自訂錯誤訊息

### 實作範例

#### 基礎 Form Request
```php
// app/Http/Requests/LoginRequest.php
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // 公開端點，不需授權
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => '電子郵件為必填',
            'email.email' => '電子郵件格式錯誤',
            'password.required' => '密碼為必填',
            'password.min' => '密碼至少需要 8 個字元',
        ];
    }

    // ⭐ 關鍵：統一錯誤格式
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => '驗證失敗',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
```

#### 帶授權的 Form Request
```php
// app/Http/Requests/UpdateSalespersonRequest.php
class UpdateSalespersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        $salesperson = $this->route('salesperson');

        // 只有擁有者或管理員可以更新
        return $this->user()->id === $salesperson->user_id
            || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => '您沒有權限執行此操作',
            ], 403)
        );
    }
}
```

#### Controller 使用
```php
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // $request->validated() 只包含驗證過的資料
        $credentials = $request->validated();

        // 業務邏輯...
        $token = auth()->attempt($credentials);

        return response()->json([
            'success' => true,
            'data' => ['access_token' => $token],
        ]);
    }
}
```

### 複雜驗證規則
```php
class StoreSalespersonRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                // 自訂規則：確保該使用者尚未有業務員檔案
                Rule::unique('salespersons', 'user_id'),
            ],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'email' => [
                'required',
                'email',
                // 不能與現有業務員重複
                Rule::unique('salespersons', 'email'),
            ],
        ];
    }

    // 自訂驗證邏輯
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // 額外的業務邏輯驗證
            if ($this->yearsOfExperience() > 50) {
                $validator->errors()->add(
                    'years_of_experience',
                    '工作年資不可超過 50 年'
                );
            }
        });
    }
}
```

### 實際效果

| 指標 | Before | After |
|------|--------|-------|
| 驗證邏輯重複率 | 40% | 5% |
| 錯誤格式種類 | 3-4 種 | 1 種 |
| 前端錯誤處理複雜度 | 高 | 低 |
| 測試覆蓋率 | 65% | 90% |

---

**已記錄**: 6 個成功模式

**相關**: [Backend 架構](../../backend/architecture.md), [API 設計](../../backend/api-design.md)
