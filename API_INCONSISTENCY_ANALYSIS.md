# 前後端 API 不一致問題分析報告

**生成日期**: 2026-01-11
**專案**: YAMU 業務員推廣系統
**架構**: Laravel 11 (Backend) + Next.js 15 (Frontend)

---

## 執行摘要

經過系統性分析前後端 API 調用和定義，發現**主要問題集中在以下幾個 API 端點缺失和回應格式不一致**。共識別出 **8 個關鍵問題**，其中 **3 個為 Critical 等級**（導致功能無法運作），**5 個為 High 等級**（影響資料正確性）。

---

## 發現的問題

### 問題 1: 缺少 `/salesperson/profile` API 端點（GET）

**影響頁面**:
- `/dashboard/profile` - 個人檔案管理頁面
- 任何使用 `useProfile()` Hook 的組件

**前端預期**:
```typescript
// frontend/lib/api/salesperson.ts (Line 51-54)
export async function getProfile(): Promise<ApiResponse<SalespersonProfile>> {
  const response = await apiClient.get<ApiResponse<SalespersonProfile>>('/salesperson/profile');
  return response.data;
}
```

**後端現況**:
- ❌ **不存在** `/salesperson/profile` 端點
- ✅ 存在 `/profile` 端點（在 `SalespersonProfileController::me`）
- 路由定義在 `api.php` Line 88-93

**問題類型**: **Missing API Endpoint**

**嚴重程度**: **Critical** 🔴

**根本原因**:
前端期待 `/salesperson/profile`，但後端只有 `/profile`。這是路由前綴不一致的問題。

**修復方案**:

**方案 A（推薦）- 修改後端路由**:
```php
// routes/api.php
Route::middleware('jwt.auth')->prefix('salesperson')->group(function (): void {
    Route::get('/profile', [SalespersonProfileController::class, 'me']);  // 新增
    Route::put('/profile', [SalespersonController::class, 'updateProfile']);  // 已存在
});
```

**方案 B - 修改前端 API 調用**:
```typescript
// frontend/lib/api/salesperson.ts
export async function getProfile(): Promise<ApiResponse<SalespersonProfile>> {
  const response = await apiClient.get<ApiResponse<SalespersonProfile>>('/profile');  // 改為 /profile
  return response.data;
}
```

**建議**: 採用**方案 A**，統一使用 `/salesperson/*` 前綴，語義更清晰。

---

### 問題 2: 缺少 `/salesperson/experiences` API 端點（GET/POST/PUT/DELETE）

**影響頁面**:
- `/dashboard/experiences` - 工作經驗管理頁面

**前端預期**:
```typescript
// frontend/lib/api/salesperson.ts (Line 93-163)
GET    /salesperson/experiences       // 取得經驗列表
POST   /salesperson/experiences       // 新增經驗
PUT    /salesperson/experiences/:id   // 更新經驗
DELETE /salesperson/experiences/:id   // 刪除經驗
```

**後端現況**:
- ❌ **完全不存在** `/salesperson/experiences` 相關端點
- 後端路由檔案 `api.php` 中沒有任何 experiences 相關路由

**問題類型**: **Missing API**

**嚴重程度**: **Critical** 🔴

**修復方案**:

需要建立完整的 Experiences API：

```php
// routes/api.php
Route::middleware('jwt.auth')->prefix('salesperson')->group(function (): void {
    Route::get('/experiences', [ExperienceController::class, 'index']);
    Route::post('/experiences', [ExperienceController::class, 'store']);
    Route::put('/experiences/{id}', [ExperienceController::class, 'update']);
    Route::delete('/experiences/{id}', [ExperienceController::class, 'destroy']);
});
```

並需要建立對應的 Controller:

```php
// app/Http/Controllers/Api/ExperienceController.php
class ExperienceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $experiences = $user->experiences;
        return response()->json(['success' => true, 'data' => $experiences]);
    }

    public function store(Request $request): JsonResponse { /* ... */ }
    public function update(Request $request, int $id): JsonResponse { /* ... */ }
    public function destroy(int $id): JsonResponse { /* ... */ }
}
```

---

### 問題 3: 缺少 `/salesperson/certifications` API 端點（GET/POST/DELETE）

**影響頁面**:
- `/dashboard/certifications` - 證照管理頁面

**前端預期**:
```typescript
// frontend/lib/api/salesperson.ts (Line 135-163)
GET    /salesperson/certifications       // 取得證照列表
POST   /salesperson/certifications       // 上傳證照
DELETE /salesperson/certifications/:id   // 刪除證照
```

**後端現況**:
- ❌ **完全不存在** `/salesperson/certifications` 相關端點

**問題類型**: **Missing API**

**嚴重程度**: **Critical** 🔴

**修復方案**:

需要建立完整的 Certifications API（與 Experiences 類似）。

---

### 問題 4: 缺少 `/salesperson/approval-status` API 端點

**影響頁面**:
- `/dashboard/approval-status` - 審核狀態查詢頁面

**前端預期**:
```typescript
// frontend/lib/api/salesperson.ts (Line 170-173)
export async function getApprovalStatus(): Promise<ApiResponse<ApprovalStatusData>> {
  const response = await apiClient.get<ApiResponse<ApprovalStatusData>>('/salesperson/approval-status');
  return response.data;
}
```

**前端期待的回應格式**:
```typescript
interface ApprovalStatusData {
  profile_status: ApprovalStatus;
  company_status: ApprovalStatus | null;
  certifications: Array<{
    id: number;
    name: string;
    approval_status: ApprovalStatus;
    rejected_reason: string | null;
  }>;
  experiences: Array<{
    id: number;
    company: string;
    position: string;
    approval_status: ApprovalStatus;
    rejected_reason: string | null;
  }>;
}
```

**後端現況**:
- ❌ **不存在** 此端點

**問題類型**: **Missing API**

**嚴重程度**: **High** 🟡

**修復方案**:

建立新的 API 端點：

```php
// routes/api.php
Route::middleware('jwt.auth')->prefix('salesperson')->group(function (): void {
    Route::get('/approval-status', [SalespersonController::class, 'approvalStatus']);
});

// SalespersonController.php
public function approvalStatus(Request $request): JsonResponse
{
    $user = $request->user();

    return response()->json([
        'success' => true,
        'data' => [
            'profile_status' => $user->salespersonProfile?->approval_status ?? 'pending',
            'company_status' => $user->salespersonProfile?->company?->approval_status ?? null,
            'certifications' => $user->certifications->map(fn($cert) => [
                'id' => $cert->id,
                'name' => $cert->name,
                'approval_status' => $cert->approval_status,
                'rejected_reason' => $cert->rejected_reason,
            ]),
            'experiences' => $user->experiences->map(fn($exp) => [
                'id' => $exp->id,
                'company' => $exp->company,
                'position' => $exp->position,
                'approval_status' => $exp->approval_status,
                'rejected_reason' => $exp->rejected_reason,
            ]),
        ],
    ]);
}
```

---

### 問題 5: 缺少 `/salesperson/company` API 端點（POST）

**影響頁面**:
- 任何需要儲存公司資訊的頁面

**前端預期**:
```typescript
// frontend/lib/api/salesperson.ts (Line 84-86)
export async function saveCompany(data: SaveCompanyRequest): Promise<ApiResponse<Company>> {
  const response = await apiClient.post<ApiResponse<Company>>('/salesperson/company', data);
  return response.data;
}
```

**後端現況**:
- ❌ **不存在** `/salesperson/company` 端點
- ✅ 存在 `POST /companies` 端點（通用的公司建立 API）

**問題類型**: **Missing API / Endpoint Mismatch**

**嚴重程度**: **High** 🟡

**修復方案**:

**方案 A（推薦）- 修改前端調用**:
```typescript
export async function saveCompany(data: SaveCompanyRequest): Promise<ApiResponse<Company>> {
  const response = await apiClient.post<ApiResponse<Company>>('/companies', data);
  return response.data;
}
```

**方案 B - 建立新端點（如果需要業務員專屬邏輯）**:
```php
Route::middleware('jwt.auth')->prefix('salesperson')->group(function (): void {
    Route::post('/company', [SalespersonController::class, 'saveCompany']);
});
```

**建議**: 採用**方案 A**，直接使用現有的 `/companies` 端點。

---

### 問題 6: `/search/salespersons/:id` API 端點不存在

**影響頁面**:
- `/salesperson/[id]` - 業務員詳細資料頁面

**前端預期**:
```typescript
// frontend/lib/api/search.ts (Line 26-31)
export async function getSalespersonDetail(id: number): Promise<SalespersonProfile> {
  const response = await apiClient.get<ApiResponse<SalespersonProfile>>(
    `/search/salespersons/${id}`
  );
  return response.data.data!;
}
```

**後端現況**:
- ❌ **不存在** `/search/salespersons/:id` 端點
- ✅ 存在 `GET /profiles/:id` 端點（在 `SalespersonProfileController::show`）

**問題類型**: **Endpoint Mismatch**

**嚴重程度**: **High** 🟡

**修復方案**:

**方案 A（推薦）- 修改前端調用**:
```typescript
export async function getSalespersonDetail(id: number): Promise<SalespersonProfile> {
  const response = await apiClient.get<ApiResponse<SalespersonProfile>>(
    `/profiles/${id}`  // 改為 /profiles/:id
  );
  return response.data.data!;
}
```

**方案 B - 建立新路由別名**:
```php
Route::get('/search/salespersons/{id}', [SalespersonProfileController::class, 'show']);
```

**建議**: 採用**方案 A**，使用現有的 RESTful 端點。

---

### 問題 7: API 回應格式不一致

**影響範圍**: 全域

**前端預期的標準格式**:
```typescript
interface ApiResponse<T = any> {
  status: 'success' | 'error';  // ❌ 錯誤：前端定義有誤
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}
```

**後端實際回應格式**:
```json
{
  "success": true,  // ✅ 使用 success (boolean)
  "message": "...",
  "data": {...}
}
```

**問題類型**: **Format Mismatch**

**嚴重程度**: **High** 🟡

**根本原因**:
前端 TypeScript 類型定義錯誤，定義了 `status: 'success' | 'error'`，但後端實際使用 `success: boolean`。

**修復方案**:

修正前端類型定義：

```typescript
// frontend/types/api.ts
export interface ApiResponse<T = any> {
  success: boolean;  // ✅ 修正為 boolean
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}
```

**影響評估**:
- 需要檢查所有使用 `response.status` 的地方
- 改為使用 `response.success`

---

### 問題 8: `/salesperson/status` API 回應格式不完整

**影響頁面**:
- 任何使用 `useSalespersonStatus()` 的組件

**前端預期**:
```typescript
// frontend/lib/api/salesperson.ts (Line 16-25)
interface SalespersonStatusResponse {
  role: 'user' | 'salesperson' | 'admin';
  salesperson_status: 'pending' | 'approved' | 'rejected' | null;
  salesperson_applied_at: string | null;
  salesperson_approved_at: string | null;
  rejection_reason: string | null;
  can_reapply: boolean;
  can_reapply_at: string | null;
  days_until_reapply: number | null;
}
```

**後端實際回應**:
```php
// SalespersonController::status (Line 78-98)
return response()->json([
    'success' => true,
    'is_salesperson' => true,
    'status' => $user->salesperson_status,
    'applied_at' => $user->salesperson_applied_at,
    'approved_at' => $user->salesperson_approved_at,
    'rejection_reason' => $user->rejection_reason,
    'can_reapply_at' => $user->can_reapply_at,
    'can_reapply' => $user->canReapply(),
]);
```

**問題類型**: **Format Mismatch / Missing Fields**

**嚴重程度**: **High** 🟡

**差異點**:
1. ❌ 缺少 `role` 欄位
2. ❌ 缺少 `days_until_reapply` 欄位
3. ❌ 欄位命名不一致：`status` vs `salesperson_status`

**修復方案**:

**方案 A（推薦）- 修改後端回應**:
```php
public function status(): JsonResponse
{
    $user = auth()->user();

    if (!$user || !$user->isSalesperson()) {
        return response()->json([
            'success' => true,
            'data' => [
                'role' => $user?->role ?? 'user',
                'salesperson_status' => null,
                'salesperson_applied_at' => null,
                'salesperson_approved_at' => null,
                'rejection_reason' => null,
                'can_reapply' => false,
                'can_reapply_at' => null,
                'days_until_reapply' => null,
            ],
        ]);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'role' => $user->role,
            'salesperson_status' => $user->salesperson_status,
            'salesperson_applied_at' => $user->salesperson_applied_at,
            'salesperson_approved_at' => $user->salesperson_approved_at,
            'rejection_reason' => $user->rejection_reason,
            'can_reapply' => $user->canReapply(),
            'can_reapply_at' => $user->can_reapply_at,
            'days_until_reapply' => $user->can_reapply_at
                ? now()->diffInDays($user->can_reapply_at, false)
                : null,
        ],
    ]);
}
```

**方案 B - 修改前端類型定義**（不推薦，因為前端定義更合理）

---

## 問題統計

| 嚴重程度 | 數量 | 問題編號 |
|---------|------|---------|
| Critical 🔴 | 3 | #1, #2, #3 |
| High 🟡 | 5 | #4, #5, #6, #7, #8 |
| **總計** | **8** | - |

---

## 受影響的前端頁面列表

| 頁面路徑 | 影響的問題 | 狀態 |
|---------|-----------|------|
| `/dashboard/profile` | #1 | ❌ 無法載入個人檔案 |
| `/dashboard/experiences` | #2 | ❌ 無法管理工作經驗 |
| `/dashboard/certifications` | #3 | ❌ 無法管理證照 |
| `/dashboard/approval-status` | #4 | ❌ 無法查看審核狀態 |
| `/salesperson/[id]` | #6 | ❌ 無法查看業務員詳情 |
| `/salesperson/upgrade` | #5, #8 | ⚠️ 部分功能異常 |
| `/search` | #6 | ⚠️ 點擊卡片無法跳轉 |
| **所有頁面** | #7 | ⚠️ 潛在類型錯誤 |

---

## 需要修復的 API 端點清單

### 需要新增的端點

| HTTP Method | 端點 | Controller | 優先級 |
|------------|------|-----------|--------|
| GET | `/salesperson/profile` | SalespersonProfileController::me | P0 🔴 |
| GET | `/salesperson/experiences` | ExperienceController::index | P0 🔴 |
| POST | `/salesperson/experiences` | ExperienceController::store | P0 🔴 |
| PUT | `/salesperson/experiences/:id` | ExperienceController::update | P0 🔴 |
| DELETE | `/salesperson/experiences/:id` | ExperienceController::destroy | P0 🔴 |
| GET | `/salesperson/certifications` | CertificationController::index | P0 🔴 |
| POST | `/salesperson/certifications` | CertificationController::store | P0 🔴 |
| DELETE | `/salesperson/certifications/:id` | CertificationController::destroy | P0 🔴 |
| GET | `/salesperson/approval-status` | SalespersonController::approvalStatus | P1 🟡 |

### 需要修改的端點

| HTTP Method | 端點 | 需要修改 | 優先級 |
|------------|------|---------|--------|
| GET | `/salesperson/status` | 調整回應格式，新增欄位 | P1 🟡 |
| **所有端點** | 統一回應格式 | `success: boolean` | P1 🟡 |

---

## 建議的修復優先順序

### Phase 1 - Critical（立即修復）🔴

**目標**: 讓核心功能可以運作

1. **新增 Experiences API** (#2)
   - 建立 `ExperienceController`
   - 新增 CRUD 端點
   - 預估時間: 4 小時

2. **新增 Certifications API** (#3)
   - 建立 `CertificationController`
   - 新增 CRUD 端點
   - 預估時間: 4 小時

3. **修正 `/salesperson/profile` 路由** (#1)
   - 在 `api.php` 新增路由別名
   - 預估時間: 30 分鐘

**Phase 1 預估總時間**: 8.5 小時

### Phase 2 - High（高優先級）🟡

**目標**: 完善功能和資料一致性

4. **新增 `/salesperson/approval-status` API** (#4)
   - 實作 `approvalStatus` 方法
   - 預估時間: 2 小時

5. **修正 `/salesperson/status` 回應格式** (#8)
   - 調整回應結構
   - 新增缺少的欄位
   - 預估時間: 1 小時

6. **統一 API 回應格式** (#7)
   - 修正前端 TypeScript 類型定義
   - 檢查所有 API 調用
   - 預估時間: 2 小時

7. **修正搜尋詳情端點** (#6)
   - 修改前端調用，使用 `/profiles/:id`
   - 預估時間: 30 分鐘

8. **統一公司 API 端點** (#5)
   - 修改前端使用 `/companies`
   - 預估時間: 30 分鐘

**Phase 2 預估總時間**: 6 小時

**總預估修復時間**: **14.5 小時**

---

## 需要用戶確認的決策點

### 決策 1: API 路由前綴策略

**問題**: 目前前後端使用不同的路由前綴

**選項**:

**A. 統一使用 `/salesperson/*` 前綴**（推薦）
- 優點: 語義清晰，業務員相關功能集中
- 缺點: 需要修改後端路由

**B. 統一使用 RESTful 路由（`/profiles`, `/experiences`）**
- 優點: 符合 RESTful 規範，資源導向
- 缺點: 需要修改前端 API 調用

**建議**: 採用選項 **A**，因為前端已經大量使用 `/salesperson/*`，改後端成本較低。

### 決策 2: Experiences 和 Certifications 的資料模型

**問題**: 後端缺少這兩個功能的完整實作

**需要確認**:
1. 資料表結構是否已存在？（`experiences`, `certifications`）
2. Model 是否已建立？
3. 是否需要審核機制？（approval_status 欄位）
4. 是否需要軟刪除？（soft deletes）

### 決策 3: API 版本控制

**問題**: 目前無版本控制，未來可能需要

**建議**:
- 短期: 直接修復現有端點
- 長期: 考慮引入 `/api/v1/` 版本控制

---

## 技術風險評估

### 風險 1: 資料庫 Schema 不完整

**描述**: Experiences 和 Certifications 的資料表可能不存在或欄位不完整

**機率**: 中

**影響**: 高

**緩解措施**:
1. 檢查資料庫 Migrations
2. 確認所有必要欄位已存在
3. 如有缺失，建立新的 Migration

### 風險 2: 前端快取問題

**描述**: 修改 API 回應格式後，前端快取可能導致類型錯誤

**機率**: 高

**影響**: 中

**緩解措施**:
1. 清除 React Query 快取
2. 重新整理頁面
3. 考慮使用版本號強制更新

### 風險 3: 測試覆蓋不足

**描述**: 修改後可能破壞現有功能

**機率**: 中

**影響**: 高

**緩解措施**:
1. 執行所有後端測試 (`composer test`)
2. 執行前端測試 (`npm test`)
3. 手動測試所有受影響頁面

---

## 建議的開發流程

### Step 1: 確認資料庫狀態

```bash
# 檢查資料表是否存在
cd my_profile_laravel
docker exec -it my_profile_laravel_app php artisan migrate:status
```

### Step 2: 建立缺少的 API（Phase 1）

```bash
# 使用 OpenSpec Commands
/implement 新增 Experiences CRUD API
/implement 新增 Certifications CRUD API
/implement 修正 Salesperson Profile API 路由
```

### Step 3: 修正回應格式（Phase 2）

```bash
/implement 統一 API 回應格式
/implement 新增 Approval Status API
```

### Step 4: 測試驗證

```bash
# Backend
cd my_profile_laravel
docker exec -it my_profile_laravel_app composer test

# Frontend
cd frontend
npm test
```

### Step 5: 手動測試

測試清單：
- [ ] 個人檔案頁面載入正常
- [ ] 工作經驗 CRUD 功能正常
- [ ] 證照 CRUD 功能正常
- [ ] 審核狀態查詢正常
- [ ] 業務員詳情頁面正常
- [ ] 搜尋功能正常

---

## 附錄

### A. 完整的 API 端點對照表

| 前端調用 | 後端實際 | 狀態 | 修復建議 |
|---------|---------|------|---------|
| GET `/salesperson/profile` | ❌ 不存在 | 🔴 | 新增路由 |
| GET `/salesperson/experiences` | ❌ 不存在 | 🔴 | 建立 Controller |
| POST `/salesperson/experiences` | ❌ 不存在 | 🔴 | 建立 Controller |
| PUT `/salesperson/experiences/:id` | ❌ 不存在 | 🔴 | 建立 Controller |
| DELETE `/salesperson/experiences/:id` | ❌ 不存在 | 🔴 | 建立 Controller |
| GET `/salesperson/certifications` | ❌ 不存在 | 🔴 | 建立 Controller |
| POST `/salesperson/certifications` | ❌ 不存在 | 🔴 | 建立 Controller |
| DELETE `/salesperson/certifications/:id` | ❌ 不存在 | 🔴 | 建立 Controller |
| GET `/salesperson/approval-status` | ❌ 不存在 | 🟡 | 新增方法 |
| GET `/salesperson/status` | ✅ 存在 | 🟡 | 調整格式 |
| POST `/salesperson/company` | `/companies` 存在 | 🟡 | 修改前端 |
| GET `/search/salespersons/:id` | `/profiles/:id` 存在 | 🟡 | 修改前端 |
| GET `/salespeople` | ✅ 存在 | ✅ | 無需修改 |
| POST `/auth/register` | ✅ 存在 | ✅ | 無需修改 |
| POST `/auth/login` | ✅ 存在 | ✅ | 無需修改 |
| GET `/auth/me` | ✅ 存在 | ✅ | 無需修改 |

### B. 相關檔案路徑

**前端 API 客戶端**:
- `/Users/kai/KAA/my_profile/frontend/lib/api/auth.ts`
- `/Users/kai/KAA/my_profile/frontend/lib/api/salesperson.ts`
- `/Users/kai/KAA/my_profile/frontend/lib/api/search.ts`
- `/Users/kai/KAA/my_profile/frontend/lib/api/admin.ts`
- `/Users/kai/KAA/my_profile/frontend/lib/api/companies.ts`

**前端 Hooks**:
- `/Users/kai/KAA/my_profile/frontend/hooks/useAuth.ts`
- `/Users/kai/KAA/my_profile/frontend/hooks/useSalesperson.ts`
- `/Users/kai/KAA/my_profile/frontend/hooks/useAdmin.ts`

**後端路由**:
- `/Users/kai/KAA/my_profile/my_profile_laravel/routes/api.php`

**後端 Controllers**:
- `/Users/kai/KAA/my_profile/my_profile_laravel/app/Http/Controllers/Api/AuthController.php`
- `/Users/kai/KAA/my_profile/my_profile_laravel/app/Http/Controllers/Api/SalespersonController.php`
- `/Users/kai/KAA/my_profile/my_profile_laravel/app/Http/Controllers/Api/SalespersonProfileController.php`
- `/Users/kai/KAA/my_profile/my_profile_laravel/app/Http/Controllers/Api/AdminController.php`

---

## 結論

前後端 API 不一致問題主要集中在：

1. **缺少關鍵 API 端點**（Experiences, Certifications, Approval Status）
2. **路由前綴不一致**（`/salesperson/*` vs `/profile`）
3. **回應格式定義錯誤**（TypeScript 類型定義）

**建議立即修復 Phase 1 的 Critical 問題**，讓核心功能可以正常運作。Phase 2 的問題可以分批處理。

總修復時間約 **14.5 小時**，建議使用 OpenSpec Commands 來加速開發流程。

---

**報告生成者**: Claude Code (Product Manager Agent)
**下一步行動**: 等待用戶確認修復方案，然後開始實作
