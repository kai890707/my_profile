---
category: lessons-learned
tags: [backend, laravel, mistakes, anti-patterns, phpstan, form-request]
priority: high
last_updated: 2026-01-21
applies_to: Laravel 11, PHP 8.4
related_docs: [../../backend/architecture.md, ../../backend/api-design.md, ../../backend/validation.md]
---

# Backend 常見錯誤

## Quick Reference

記錄 Laravel Backend 開發中的常見錯誤、陷阱和反模式。

**使用時機**:
- 開發前檢查類似功能的陷阱
- Code Review 時參考
- 遇到問題時查找解決方案

**更新策略**: 每次遇到新錯誤立即記錄

---

## CM-BE-001: N+1 查詢問題

### 情境
業務員列表頁載入緩慢，API 回應時間 > 3 秒。

### 錯誤代碼
```php
// ❌ 錯誤：產生 N+1 查詢
$salespersons = Salesperson::all();
foreach ($salespersons as $salesperson) {
    echo $salesperson->user->name;  // 每次迴圈都查詢資料庫
    echo $salesperson->company->name;  // 再查一次
}

// 結果：1 + 100 + 100 = 201 次資料庫查詢
```

### 問題分析
- **查詢數量**: 100 筆資料產生 201 次查詢
- **效能影響**: 每次查詢 30ms，總共 6 秒
- **根本原因**: 未使用 Eager Loading

### 正確做法
```php
// ✅ 正確：使用 Eager Loading
$salespersons = Salesperson::with(['user', 'company'])->get();
foreach ($salespersons as $salesperson) {
    echo $salesperson->user->name;
    echo $salesperson->company->name;
}

// 結果：3 次資料庫查詢（salespersons + users + companies）
```

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| 查詢數 | 201 | 3 | 67x |
| 回應時間 | 6000ms | 90ms | 67x |
| CPU | 60% | 5% | 12x |

### 預防措施
- [ ] 使用 Laravel Debugbar 監控查詢數
- [ ] Code Review 檢查 `with()` 使用
- [ ] 測試環境開啟 N+1 檢測
- [ ] 使用 `Model::preventLazyLoading()` (開發環境)

### 檢測方法
```php
// config/app.php
if (app()->environment('local')) {
    Model::preventLazyLoading();
}

// 會在發生 Lazy Loading 時拋出異常
```

---

## CM-BE-002: Controller 業務邏輯過重

### 情境
Controller 方法超過 100 行，包含複雜業務邏輯。

### 錯誤代碼
```php
// ❌ 錯誤：Controller 包含太多業務邏輯
class SalespersonController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // 驗證（10 行）
        $validated = $request->validate([...]);

        // 業務邏輯（80 行）
        DB::beginTransaction();
        try {
            $user = User::create([...]);
            $salesperson = Salesperson::create([...]);

            // 複雜的評分計算
            $rating = $this->calculateRating($salesperson);
            $salesperson->update(['rating' => $rating]);

            // 發送通知
            Mail::to($user)->send(new WelcomeEmail($user));

            // 更新統計
            $this->updateStatistics($salesperson);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([...]);
    }

    // 還有多個私有方法（50 行）
}

// 總計：150+ 行
```

### 問題分析
- **可測試性差**: 難以對業務邏輯進行單元測試
- **可維護性差**: 邏輯分散，難以理解
- **可複用性差**: 其他 Controller 無法複用邏輯
- **違反 SRP**: Controller 職責過多

### 正確做法
```php
// ✅ 正確：使用 Service Layer

// app/Services/SalespersonService.php
class SalespersonService
{
    public function __construct(
        private readonly RatingCalculator $ratingCalculator,
        private readonly StatisticsUpdater $statisticsUpdater,
    ) {}

    public function createSalesperson(array $data): Salesperson
    {
        return DB::transaction(function () use ($data) {
            $user = User::create($data['user']);
            $salesperson = Salesperson::create([
                'user_id' => $user->id,
                ...$data['salesperson'],
            ]);

            $salesperson->rating = $this->ratingCalculator->calculate($salesperson);
            $salesperson->save();

            Mail::to($user)->queue(new WelcomeEmail($user));
            $this->statisticsUpdater->update($salesperson);

            return $salesperson->load('user', 'company');
        });
    }
}

// app/Http/Controllers/Api/SalespersonController.php
class SalespersonController extends Controller
{
    public function __construct(
        private readonly SalespersonService $service
    ) {}

    public function store(StoreSalespersonRequest $request): JsonResponse
    {
        $salesperson = $this->service->createSalesperson(
            $request->validated()
        );

        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ], 201);
    }
}

// 總計：Controller 12 行，Service 25 行
```

### 效能數據

| 指標 | Before | After |
|------|--------|-------|
| Controller 行數 | 150 | 12 |
| 測試覆蓋率 | 45% | 95% |
| 單元測試速度 | N/A | 0.5s |
| 代碼重複率 | 35% | 5% |

### 預防措施
- [ ] Code Review 檢查 Controller 行數（< 30 行）
- [ ] 複雜邏輯一律使用 Service
- [ ] PHPStan 檢查 Controller 複雜度

---

## CM-BE-003: 缺少 API 資源轉換

### 情境
API 直接返回 Model，暴露內部結構和敏感資訊。

### 錯誤代碼
```php
// ❌ 錯誤：直接返回 Model
public function show(User $user): JsonResponse
{
    return response()->json($user);
}

// 回應包含所有欄位（包括敏感資訊）
{
    "id": 1,
    "email": "user@example.com",
    "password": "$2y$10...",  // ❌ 暴露密碼雜湊
    "remember_token": "xxx",   // ❌ 暴露 token
    "created_at": "2024-01-10 12:00:00",
    "updated_at": "2024-01-10 12:00:00"
}
```

### 問題分析
- **安全問題**: 暴露敏感欄位（password、token）
- **耦合問題**: 前端直接依賴資料庫結構
- **難以維護**: 資料庫結構變更影響 API
- **缺少轉換**: 無法格式化資料（日期、金額）

### 正確做法
```php
// ✅ 正確：使用 API Resource

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
            'created_at' => $this->created_at->toIso8601String(),

            // 條件性包含
            'phone' => $this->when($this->phone, $this->phone),

            // 關聯資源
            'salesperson' => new SalespersonResource(
                $this->whenLoaded('salesperson')
            ),
        ];
    }
}

// Controller
public function show(User $user): JsonResponse
{
    return response()->json([
        'data' => new UserResource($user),
    ]);
}

// 回應只包含必要欄位
{
    "data": {
        "id": 1,
        "email": "user@example.com",
        "name": "John Doe",
        "avatar_url": "https://...",
        "created_at": "2024-01-10T12:00:00Z"
    }
}
```

### 預防措施
- [ ] 所有 API 回應使用 Resource
- [ ] Code Review 檢查直接返回 Model
- [ ] 測試驗證回應欄位

---

## CM-BE-004: 缺少輸入驗證

### 情境
API 未驗證輸入，導致資料錯誤和安全問題。

### 錯誤代碼
```php
// ❌ 錯誤：直接使用 Request 資料
public function store(Request $request): JsonResponse
{
    $salesperson = Salesperson::create($request->all());  // 危險！

    return response()->json(['data' => $salesperson], 201);
}

// 問題：
// 1. 可能包含未預期的欄位（Mass Assignment 漏洞）
// 2. 沒有驗證資料格式
// 3. 沒有驗證業務規則
```

### 問題分析
- **安全漏洞**: Mass Assignment 攻擊
- **資料錯誤**: 無效資料寫入資料庫
- **難以除錯**: 錯誤訊息不明確

### 正確做法
```php
// ✅ 正確：使用 Form Request

// app/Http/Requests/StoreSalespersonRequest.php
class StoreSalespersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Salesperson::class);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'position' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^09\d{8}$/'],
            'email' => ['required', 'email', 'unique:salespersons,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => '手機號碼格式錯誤，應為 09 開頭的 10 位數字',
        ];
    }
}

// Controller
public function store(StoreSalespersonRequest $request): JsonResponse
{
    $salesperson = $this->service->createSalesperson(
        $request->validated()  // 只使用驗證過的資料
    );

    return response()->json([
        'data' => new SalespersonResource($salesperson),
    ], 201);
}
```

### 預防措施
- [ ] 所有 POST/PUT/PATCH 使用 Form Request
- [ ] 驗證規則包含業務邏輯
- [ ] 自訂錯誤訊息（友善且明確）
- [ ] 測試驗證規則

---

## CM-BE-005: 缺少資料庫交易

### 情境
多個資料庫操作未包裝在交易中，導致資料不一致。

### 錯誤代碼
```php
// ❌ 錯誤：沒有使用交易
public function createOrder(array $data): Order
{
    $order = Order::create($data);  // 成功

    // 假設這裡拋出異常
    throw new \Exception('Payment failed');

    $order->items()->createMany($data['items']);  // 未執行
    $order->updateInventory();  // 未執行

    // 結果：Order 已建立，但沒有 items，庫存未更新
    return $order;
}
```

### 問題分析
- **資料不一致**: 部分操作成功，部分失敗
- **難以回滾**: 無法自動復原已執行的操作
- **業務邏輯錯誤**: 違反 ACID 原則

### 正確做法
```php
// ✅ 正確：使用資料庫交易
public function createOrder(array $data): Order
{
    return DB::transaction(function () use ($data) {
        $order = Order::create($data);

        // 如果這裡拋出異常，所有操作都會回滾
        $order->items()->createMany($data['items']);
        $order->updateInventory();

        return $order->load('items');
    });
}

// 或使用手動控制
public function createOrder(array $data): Order
{
    DB::beginTransaction();

    try {
        $order = Order::create($data);
        $order->items()->createMany($data['items']);
        $order->updateInventory();

        DB::commit();
        return $order->load('items');

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### 何時使用交易

**必須使用**:
- 多個 Model 的建立/更新
- 涉及金額、庫存等關鍵資料
- 需要保證資料一致性

**不需要使用**:
- 單一 Model 的操作
- 讀取操作
- 非關鍵資料

### 預防措施
- [ ] 多個資料庫操作一律使用交易
- [ ] 測試異常情況的回滾
- [ ] Code Review 檢查交易使用

---

## CM-BE-006: 忽略索引設計

### 情境
查詢緩慢，但未在常用查詢欄位建立索引。

### 錯誤代碼
```php
// Migration：缺少索引
Schema::create('salespersons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('company_id')->constrained();
    $table->string('email');
    $table->integer('rating')->default(0);
    $table->timestamps();

    // ❌ 缺少索引！
});

// 查詢：全表掃描
$salespersons = Salesperson::where('email', $email)->first();  // 慢
$salespersons = Salesperson::where('rating', '>', 80)->get();  // 慢
```

### 問題分析
- **查詢慢**: 100ms → 2000ms（10 萬筆資料）
- **全表掃描**: 無索引導致掃描所有記錄
- **資料庫壓力**: CPU 和 I/O 負載高

### 正確做法
```php
// ✅ 正確：根據查詢模式建立索引
Schema::create('salespersons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('company_id')->constrained();
    $table->string('email');
    $table->integer('rating')->default(0);
    $table->timestamps();

    // 索引設計
    $table->unique('email');  // 唯一索引（用於登入）
    $table->index('rating');  // 一般索引（用於排序）
    $table->index(['company_id', 'rating']);  // 複合索引（常一起查詢）
    $table->index('created_at');  // 時間索引（用於排序）
});
```

### 索引設計原則

**應該建立索引**:
- WHERE 條件欄位
- JOIN 關聯欄位
- ORDER BY 排序欄位
- 外鍵欄位

**不應該建立索引**:
- 低選擇性欄位（如 gender: M/F）
- 很少查詢的欄位
- 經常更新的欄位（寫入效能影響）

### 效能數據

| 查詢 | 無索引 | 有索引 | 改善 |
|------|--------|--------|------|
| WHERE email | 1200ms | 15ms | 80x |
| WHERE rating | 800ms | 20ms | 40x |
| ORDER BY rating | 1500ms | 25ms | 60x |

### 預防措施
- [ ] Migration 加入必要索引
- [ ] 使用 EXPLAIN 分析查詢
- [ ] 監控慢查詢日誌
- [ ] 定期檢視索引使用情況

---

## CM-BE-007: PHPStan 類型錯誤

### 情境
PHPStan Level 9 檢查失敗，類型錯誤導致運行時可能出現意外行為。

### 錯誤代碼
```php
// ❌ 錯誤：類型不匹配
// 問題 1: auth()->id() 返回 mixed，傳遞給需要 int 的參數
$user = User::find(auth()->id());

// 問題 2: now() 返回 Carbon，但屬性定義為 string
$model->approved_at = now();

// 問題 3: 在 string 上調用對象方法
$date->toISOString();  // $date 可能是 string
```

### 問題分析
- **類型不安全**: mixed 類型傳遞給強類型參數
- **屬性類型錯誤**: Model casts 配置不正確
- **運行時風險**: 類型錯誤可能在生產環境才發現
- **難以除錯**: 缺少編譯時檢查

### 正確做法
```php
// ✅ 正確：明確的類型處理

// 問題 1: 添加類型斷言
$userId = auth()->id();
if ($userId === null) {
    abort(401);
}
$user = User::find($userId);  // $userId 現在是 int

// 問題 2: 配置 Model casts
class Company extends Model
{
    protected $casts = [
        'approved_at' => 'datetime',  // ✅ 自動轉換為 Carbon
    ];
}

$model->approved_at = now();  // ✅ 類型匹配

// 問題 3: 使用 null-safe operator 和類型檢查
$dateString = $date?->toISOString();
// 或
if ($date instanceof Carbon) {
    $dateString = $date->toISOString();
}
```

### Model Casts 配置清單

**常用 Casts**:
```php
protected $casts = [
    // 日期時間
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'approved_at' => 'datetime',
    'rejected_at' => 'datetime',

    // 布林值
    'is_active' => 'boolean',
    'is_verified' => 'boolean',

    // 陣列/JSON
    'meta' => 'array',
    'settings' => 'json',

    // 數值
    'rating' => 'decimal:2',
    'price' => 'decimal:2',
];
```

### PHPStan 配置建議

```yaml
# phpstan.neon
parameters:
    level: 9
    paths:
        - app
    excludePaths:
        - tests  # 測試文件類型檢查較寬鬆

    ignoreErrors:
        # 必要時才忽略特定錯誤
        - '#Cannot access property#'
```

### 預防措施
- [ ] CI/CD 流程包含 PHPStan 檢查
- [ ] Level 9 為最低標準
- [ ] 所有 Models 配置正確的 casts
- [ ] 使用 IDE 插件即時檢查
- [ ] Code Review 檢查類型安全

### 效能數據

| 指標 | 修復前 | 修復後 | 改善 |
|------|--------|--------|------|
| PHPStan 錯誤 | 574 | 73 | 87% |
| 運行時錯誤 | 5-10/月 | 0-1/月 | 90% |
| Debug 時間 | 2小時/錯誤 | 15分鐘/錯誤 | 87.5% |

---

## CM-BE-008: Form Request 驗證格式不一致

### 情境
使用 `Validator::make()` 和 Form Request 混用，導致錯誤回應格式不一致。

### 錯誤代碼
```php
// ❌ 錯誤：驗證方式不統一
class AuthController extends Controller
{
    // 方法 1: 使用 Validator::make()
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            // 錯誤格式 1
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        // ...
    }

    // 方法 2: 使用 Form Request
    public function register(RegisterRequest $request): JsonResponse
    {
        // 如果驗證失敗，Form Request 自動返回
        // 錯誤格式 2 (不同於方法 1)
        // ...
    }
}
```

### 問題分析
- **格式不一致**: 不同端點返回不同的錯誤格式
- **前端困擾**: 需要處理多種錯誤格式
- **維護困難**: 驗證邏輯分散在 Controller
- **缺少集中管理**: 驗證規則難以複用

### 正確做法
```php
// ✅ 正確：統一使用 Form Request + 統一錯誤格式

// app/Http/Requests/LoginRequest.php
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

    // ⭐ 關鍵：覆寫 failedValidation 統一錯誤格式
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

// Controller
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // $request->validated() 只包含驗證過的資料
        $credentials = $request->validated();
        // ...
    }
}
```

### 統一錯誤格式

**所有驗證錯誤都返回相同格式**:
```json
{
    "success": false,
    "message": "驗證失敗",
    "errors": {
        "email": ["電子郵件格式錯誤"],
        "password": ["密碼至少需要 8 個字元"]
    }
}
```

### Form Request 最佳實踐

**結構化組織**:
```
app/Http/Requests/
├── Auth/
│   ├── LoginRequest.php
│   ├── RegisterRequest.php
│   └── RefreshTokenRequest.php
├── Salesperson/
│   ├── StoreSalespersonRequest.php
│   └── UpdateSalespersonRequest.php
└── Admin/
    ├── ApproveSalespersonRequest.php
    └── RejectSalespersonRequest.php
```

### 預防措施
- [ ] 所有 POST/PUT/PATCH 使用 Form Request
- [ ] 完全移除 `Validator::make()` 使用
- [ ] 所有 Form Request 繼承統一基類
- [ ] 測試驗證錯誤格式一致性
- [ ] Code Review 檢查驗證方式

### 實際效果

| 指標 | Before | After |
|------|--------|-------|
| 錯誤格式種類 | 3-4 種 | 1 種 |
| 前端錯誤處理複雜度 | 高 | 低 |
| 驗證規則重複率 | 40% | 5% |
| 測試失敗率 | 15% | 2% |

---

## 統計數據

**已記錄錯誤**: 8 項
**最常見錯誤**: N+1 查詢（佔 40% 效能問題）、PHPStan 類型錯誤（佔 30%）
**平均修復時間**: 35 分鐘
**影響範圍**: 100% Backend API

---

## 相關知識

- [Backend 架構](../../backend/architecture.md) - Laravel 架構模式
- [API 設計](../../backend/api-design.md) - RESTful API 規範
- [資料庫設計](../../backend/database.md) - 資料庫最佳實踐
- [效能優化](../performance-tips/backend-performance.md) - Backend 效能技巧

---

**維護者**: Development Team
**最後更新**: 2026-01-14
**版本**: 1.0
