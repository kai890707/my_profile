---
category: lessons-learned
tags: [backend, patterns, best-practices, laravel]
priority: high
last_updated: 2026-01-14
applies_to: Laravel 11
related_docs: [../../backend/architecture.md]
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

---

**已記錄**: 3 個成功模式

**相關**: [Backend 架構](../../backend/architecture.md)
