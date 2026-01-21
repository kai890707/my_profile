# Proposal: Backend 代碼品質全面修復

## 1. 背景與目標

### 業務背景

當前 Backend 代碼存在多個品質問題，影響長期維護性和可靠性：

1. **PHPStan Level 9 錯誤**: 574 個類型安全錯誤，主要集中在測試文件和 Controllers
2. **Controller 臃腫**: AdminController 1,059 行，違反單一職責原則
3. **驗證不一致**: 6 個 Controllers 混用 `Validator::make()` 和 Form Request
4. **API 回應不規範**: 直接返回 Models，缺少 API Resources 層
5. **代碼風格不統一**: 部分代碼未通過 Laravel Pint 檢查
6. **缺少保護機制**: 無 Rate Limiting 和 Cache 策略

這些問題會導致：
- 類型錯誤風險增加，運行時可能出現意外錯誤
- 代碼難以維護和擴展
- API 契約不穩定，容易破壞前端
- 效能和安全性隱患

### 目標使用者

- **開發團隊**: 提升代碼品質和開發體驗
- **前端開發者**: 確保 API 契約穩定不變
- **系統維護者**: 降低維護成本

### 成功指標

**代碼品質指標** (定量):
- PHPStan Level 9: 0 errors
- Laravel Pint: 100% 通過
- Test Coverage: >= 95% (保持不降低)
- Cyclomatic Complexity: <= 10 (所有方法)

**API 穩定性指標** (關鍵):
- 前端整合測試: 100% 通過
- API 契約: 0 breaking changes
- 端點數量: 31 個 (不變)

**效能指標**:
- API 回應時間: 保持不變 (< 200ms P95)
- 資料庫查詢: 保持不變或優化

## 2. 功能描述

### 核心目標

這是一個 **純後端重構任務**，目標是提升代碼品質，同時 **100% 保證前端 API 契約不變**。

### 重構流程

```
階段 1: PHPStan 錯誤修復 (最高優先級)
  ├─ 修復 Controllers 類型錯誤 (16 個)
  ├─ 修復 Tests 類型錯誤 (558 個)
  └─ 修復其他文件類型錯誤

階段 2: 代碼風格統一
  ├─ Laravel Pint 自動修復
  └─ 手動修復不可自動處理的問題

階段 3: 驗證層重構
  ├─ 統一使用 Form Request
  ├─ 移除所有 Validator::make()
  └─ 保證驗證規則不變

階段 4: API 回應規範化
  ├─ 實作 API Resources
  ├─ 替換直接返回 Models
  └─ 保證回應格式不變

階段 5: Controller 拆分
  ├─ AdminController 拆分為 4 個 Controllers
  ├─ 職責劃分清晰
  └─ 路由保持不變

階段 6: 效能與安全增強
  ├─ 實作 Rate Limiting
  ├─ 實作 Cache 策略
  └─ 不影響現有功能

階段 7: 全面測試驗證
  ├─ Backend 單元測試
  ├─ Backend 整合測試
  ├─ Frontend E2E 測試
  └─ 效能測試
```

### 使用情境範例

**情境 1: 前端開發者使用 API**

- **Before**:
  - API 回應包含 Models 的所有屬性（包含內部字段）
  - 類型錯誤可能導致運行時異常
  - 無 Rate Limiting 保護

- **After**:
  - API 回應通過 Resources 規範化，僅返回必要字段
  - 類型安全，PHPStan Level 9 保證
  - Rate Limiting 保護 API 不被濫用
  - **API 端點、請求/回應格式完全不變**

**情境 2: 後端開發者維護代碼**

- **Before**:
  - AdminController 1,059 行，難以定位功能
  - 驗證邏輯分散在 Controller
  - 代碼風格不一致

- **After**:
  - 4 個專職 Controllers，職責清晰
  - 驗證集中在 Form Requests
  - 代碼風格統一，易於閱讀

## 3. 功能範圍

### In Scope（本次實作）

#### 階段 1: PHPStan 錯誤修復 (P0)
- ✅ 修復 AdminController 16 個類型錯誤
- ✅ 修復 AuthController 5 個類型錯誤
- ✅ 修復 CertificationController 類型錯誤
- ✅ 修復 CompanyController 類型錯誤
- ✅ 修復 SalespersonController 類型錯誤
- ✅ 修復 Tests 558 個類型錯誤
- ✅ 確保 PHPStan Level 9 通過 (0 errors)

#### 階段 2: Laravel Pint 代碼風格 (P0)
- ✅ 執行 `./vendor/bin/pint` 自動修復
- ✅ 檢查並手動修復不可自動處理的風格問題
- ✅ 確保 100% 通過 Pint 檢查

#### 階段 3: Form Request 統一 (P1)
- ✅ AdminController: 統一驗證方式
  - `rejectSalesperson()`: 創建 `RejectSalespersonRequest`
  - 其他需要驗證的端點
- ✅ AuthController: 統一驗證方式
  - 已有 `RegisterRequest`, `LoginRequest` 等
  - 移除 `Validator::make()` 使用
- ✅ CertificationController: 統一驗證方式
- ✅ CompanyController: 統一驗證方式
- ✅ ExperienceController: 統一驗證方式
- ✅ SalespersonController: 統一驗證方式

#### 階段 4: API Resources 實作 (P1)
- ✅ 創建 API Resources:
  - `UserResource`
  - `SalespersonResource`
  - `CompanyResource`
  - `ExperienceResource`
  - `CertificationResource`
  - `StatisticsResource`
- ✅ 替換所有直接返回 Models 的地方
- ✅ 保證回應格式與原有一致 (關鍵!)

#### 階段 5: AdminController 拆分 (P2)
- ✅ 拆分為 4 個 Controllers:
  1. `AdminStatisticsController`: 統計相關 (3 個端點)
     - `GET /admin/statistics`
     - `GET /admin/activity-logs`
     - `GET /admin/salesperson-statistics`
  2. `AdminSalespersonController`: 業務員審核 (3 個端點)
     - `GET /admin/pending-salespersons`
     - `POST /admin/approve-salesperson/{id}`
     - `POST /admin/reject-salesperson/{id}`
  3. `AdminContentController`: 內容審核 (8 個端點)
     - Companies: approve/reject
     - Experiences: approve/reject
     - Certifications: approve/reject
  4. `AdminUserController`: 使用者管理 (3 個端點)
     - `GET /admin/users`
     - `PUT /admin/users/{id}`
     - `DELETE /admin/users/{id}`
- ✅ 保持路由不變 (路由僅更新 Controller 參照)
- ✅ 移動共用邏輯到 Services

#### 階段 6: Rate Limiting & Cache (P2)
- ✅ 實作 Rate Limiting:
  - 公開 API: 60 requests/min
  - 認證 API: 120 requests/min
  - Admin API: 300 requests/min
- ✅ 實作 Cache 策略:
  - 統計資料: 5 分鐘
  - 業務員列表: 1 分鐘
  - 公司列表: 1 分鐘
- ✅ 配置 config/cache.php

#### 階段 7: 測試驗證 (P0)
- ✅ Backend 測試:
  - 執行所有 Feature Tests (應該全部通過)
  - 執行所有 Unit Tests (應該全部通過)
  - 測試覆蓋率 >= 95%
- ✅ 前端整合測試:
  - 使用 Playwright 執行所有 E2E 測試
  - 確保 API 契約不變
- ✅ 效能測試:
  - API 回應時間不變
  - 資料庫查詢不變或優化

### Out of Scope（不在範圍內）

- ❌ **新增功能**: 不新增任何功能
  - 原因: 純重構任務
- ❌ **修改 API 契約**: 不修改端點、請求/回應格式
  - 原因: 避免破壞前端
- ❌ **資料庫 Schema 變更**: 不修改資料表結構
  - 原因: 無需變更
- ❌ **前端代碼修改**: 不修改 frontend 代碼
  - 原因: Backend only
- ❌ **大規模架構重構**: 不引入新架構模式
  - 原因: 範圍過大，風險高

## 4. 詳細需求

### 4.1 PHPStan 錯誤修復需求

#### FR-001: 修復 AdminController 類型錯誤
**描述**: 修復 AdminController 16 個 PHPStan Level 9 錯誤
**優先級**: Must Have (P0)

**錯誤類型**:
1. `mixed` 類型傳遞給強類型參數
2. 屬性類型不匹配 (如 `approved_at` 接受 `Carbon` vs `string`)
3. 在 `string` 上調用對象方法 (如 `toISOString()`)
4. 錯誤的類型轉換 (如 `mixed` to `int`)

**解決方案**:
- 添加類型斷言和驗證
- 修復屬性類型定義 (Model casts)
- 確保類型安全的轉換

**驗收標準**:
- [ ] AdminController: 0 PHPStan errors
- [ ] 所有方法有明確的參數和返回類型
- [ ] 不使用 `mixed` type (除非絕對必要)

#### FR-002: 修復 Tests 類型錯誤
**描述**: 修復 558 個測試文件中的類型錯誤
**優先級**: Must Have (P0)

**常見錯誤**:
- 測試中未定義返回類型
- 使用 `mixed` 類型
- 類型斷言缺失

**解決方案**:
- 為所有測試方法添加 `: void` 返回類型
- 添加必要的類型提示
- 使用 PHPStan 的 `@var` 註解

**驗收標準**:
- [ ] 所有 Feature Tests: 0 PHPStan errors
- [ ] 所有 Unit Tests: 0 PHPStan errors
- [ ] 測試仍然 100% 通過

### 4.2 Laravel Pint 需求

#### FR-003: 代碼風格統一
**描述**: 使用 Laravel Pint 統一代碼風格
**優先級**: Must Have (P0)

**執行步驟**:
1. 執行 `./vendor/bin/pint` 自動修復
2. 檢查修復結果
3. 手動處理不可自動修復的問題
4. 再次執行驗證

**驗收標準**:
- [ ] `./vendor/bin/pint --test` 100% 通過
- [ ] 無手動風格修復遺留

### 4.3 Form Request 需求

#### FR-004: 統一驗證方式
**描述**: 所有 Controllers 使用 Form Request 驗證
**優先級**: Should Have (P1)

**需要創建的 Form Requests**:

**AdminController**:
- `RejectSalespersonRequest` (已存在)
- `ApproveCompanyRequest`
- `RejectCompanyRequest`
- `ApproveExperienceRequest`
- `RejectExperienceRequest`
- `ApproveCertificationRequest`
- `RejectCertificationRequest`
- `UpdateUserRequest`

**其他 Controllers**:
- 檢查並補充缺失的 Form Requests

**驗收標準**:
- [ ] 所有 Controllers 不使用 `Validator::make()`
- [ ] 所有驗證規則移至 Form Requests
- [ ] 驗證錯誤回應格式不變
- [ ] 所有測試仍然通過

### 4.4 API Resources 需求

#### FR-005: 實作 API Resources
**描述**: 使用 API Resources 規範化 API 回應
**優先級**: Should Have (P1)

**需要創建的 Resources**:

```php
// app/Http/Resources/UserResource.php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'role' => $this->role,
            'avatar' => $this->avatar,
            'created_at' => $this->created_at,
            // ... 根據現有回應格式
        ];
    }
}
```

**其他 Resources**:
- `SalespersonResource`
- `CompanyResource`
- `ExperienceResource`
- `CertificationResource`
- `StatisticsResource`

**關鍵要求**:
- 回應格式必須與原有完全一致
- 不可添加新字段 (除非前端需要)
- 不可移除現有字段
- 日期格式保持一致

**驗收標準**:
- [ ] 所有 API 端點使用 Resources
- [ ] 前端測試 100% 通過 (API 契約不變)
- [ ] 不直接返回 Models

### 4.5 Controller 拆分需求

#### FR-006: AdminController 拆分
**描述**: 將 1,059 行的 AdminController 拆分為 4 個專職 Controllers
**優先級**: Should Have (P2)

**拆分結構**:

```
AdminController (1059 lines)
├─ AdminStatisticsController
│  ├─ statistics() - GET /admin/statistics
│  ├─ activityLogs() - GET /admin/activity-logs
│  └─ salespersonStatistics() - GET /admin/salesperson-statistics
│
├─ AdminSalespersonController
│  ├─ pendingSalespersons() - GET /admin/pending-salespersons
│  ├─ approveSalesperson() - POST /admin/approve-salesperson/{id}
│  └─ rejectSalesperson() - POST /admin/reject-salesperson/{id}
│
├─ AdminContentController
│  ├─ pendingCompanies() - GET /admin/pending-companies
│  ├─ approveCompany() - POST /admin/approve-company/{id}
│  ├─ rejectCompany() - POST /admin/reject-company/{id}
│  ├─ pendingExperiences() - GET /admin/pending-experiences
│  ├─ approveExperience() - POST /admin/approve-experience/{id}
│  ├─ rejectExperience() - POST /admin/reject-experience/{id}
│  ├─ pendingCertifications() - GET /admin/pending-certifications
│  ├─ approveCertification() - POST /admin/approve-certification/{id}
│  └─ rejectCertification() - POST /admin/reject-certification/{id}
│
└─ AdminUserController
   ├─ users() - GET /admin/users
   ├─ updateUser() - PUT /admin/users/{id}
   └─ deleteUser() - DELETE /admin/users/{id}
```

**路由更新**:
```php
// routes/api.php - 更新 Controller 參照
Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->group(function () {
    // Statistics
    Route::get('/statistics', [AdminStatisticsController::class, 'statistics']);
    Route::get('/activity-logs', [AdminStatisticsController::class, 'activityLogs']);

    // Salesperson Management
    Route::get('/pending-salespersons', [AdminSalespersonController::class, 'pendingSalespersons']);
    Route::post('/approve-salesperson/{id}', [AdminSalespersonController::class, 'approveSalesperson']);

    // ... 其他路由
});
```

**驗收標準**:
- [ ] AdminController 刪除 (所有方法已遷移)
- [ ] 4 個新 Controllers 創建
- [ ] 所有路由更新
- [ ] 所有測試更新 Controller 參照
- [ ] 測試 100% 通過
- [ ] API 端點 URL 不變

### 4.6 Rate Limiting 需求

#### FR-007: 實作 API Rate Limiting
**描述**: 為不同 API 類型配置不同的 Rate Limiting
**優先級**: Should Have (P2)

**Rate Limiting 策略**:

| API 類型 | 限制 | 適用端點 |
|---------|------|---------|
| 公開 API | 60 req/min | `/api/auth/login`, `/api/auth/register` |
| 認證 API | 120 req/min | 需要 `auth:api` 的端點 |
| Admin API | 300 req/min | `/api/admin/*` |

**實作方式**:
```php
// app/Providers/RouteServiceProvider.php
RateLimiter::for('public-api', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip());
});

RateLimiter::for('authenticated-api', function (Request $request) {
    return Limit::perMinute(120)->by(optional($request->user())->id ?: $request->ip());
});

RateLimiter::for('admin-api', function (Request $request) {
    return Limit::perMinute(300)->by(optional($request->user())->id ?: $request->ip());
});

// routes/api.php
Route::middleware(['throttle:public-api'])->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
});
```

**驗收標準**:
- [ ] Rate Limiting 配置生效
- [ ] 超過限制返回 429 錯誤
- [ ] 不同 API 類型使用不同限制
- [ ] 測試驗證 Rate Limiting 行為

### 4.7 Cache 需求

#### FR-008: 實作 Cache 策略
**描述**: 為查詢密集的 API 實作 Cache
**優先級**: Could Have (P2)

**Cache 策略**:

| 端點 | Cache TTL | Cache Key |
|------|-----------|-----------|
| `GET /admin/statistics` | 5 分鐘 | `admin:statistics` |
| `GET /salespersons` | 1 分鐘 | `salespersons:list:{page}:{filters}` |
| `GET /companies` | 1 分鐘 | `companies:list:{page}:{filters}` |

**實作方式**:
```php
// AdminStatisticsController
public function statistics(): JsonResponse
{
    $stats = Cache::remember('admin:statistics', 300, function () {
        return [
            'total_salespeople' => User::salespersons()->count(),
            'active_salespeople' => User::salespersons()->active()->count(),
            // ...
        ];
    });

    return response()->json([
        'success' => true,
        'data' => $stats
    ]);
}
```

**Cache 清除**:
- 當相關資料變更時，清除對應 Cache
- 使用 Model Events (e.g., `created`, `updated`, `deleted`)

**驗收標準**:
- [ ] 統計 API 使用 Cache
- [ ] 列表 API 使用 Cache
- [ ] 資料變更時 Cache 正確清除
- [ ] 不影響資料即時性

## 5. 邊界情境處理

### 異常情況處理

| 情境 | 系統行為 | 錯誤訊息 |
|-----|---------|---------|
| PHPStan 錯誤未全部修復 | 拒絕合併 PR | "PHPStan Level 9 檢查未通過" |
| Pint 檢查失敗 | 拒絕合併 PR | "代碼風格檢查未通過" |
| 測試失敗 | 回滾變更 | "測試失敗，請修復" |
| API 契約變更 (Breaking) | 拒絕變更 | "API 契約不可變更" |
| 效能下降 | 優化或回滾 | "效能不符合要求" |
| Rate Limit 超過 | 返回 429 | "Too Many Requests" |

### Edge Cases

#### Edge Case 1: Form Request 驗證規則變更
- **情境**: 統一為 Form Request 時，驗證規則意外變更
- **預期行為**:
  - 嚴格比對原有驗證規則
  - 測試驗證所有邊界條件
  - 不可改變驗證邏輯
- **錯誤處理**: 如果測試失敗，回滾變更並重新檢查

#### Edge Case 2: API Resource 回應格式不一致
- **情境**: Resource 返回的 JSON 格式與原有不同
- **預期行為**:
  - 使用快照測試比對回應格式
  - 逐欄位檢查
  - 前端 E2E 測試驗證
- **錯誤處理**: 修正 Resource 直到格式完全一致

#### Edge Case 3: Controller 拆分後路由失效
- **情境**: 路由更新後，部分端點無法訪問
- **預期行為**:
  - 測試所有端點可訪問性
  - 檢查 Middleware 是否正確應用
  - 驗證授權邏輯不變
- **錯誤處理**: 修正路由配置

#### Edge Case 4: Cache 導致資料不同步
- **情境**: Cache TTL 過長，資料變更後仍返回舊資料
- **預期行為**:
  - 測試資料變更後 Cache 清除
  - 確保即時性要求高的資料不使用 Cache
- **錯誤處理**: 調整 TTL 或清除策略

## 6. 技術考量

### 技術限制

1. **PHPStan Level 9 嚴格性**
   - 最嚴格的類型檢查
   - 不允許 `mixed` type
   - 需要明確的類型提示和斷言

2. **Laravel Pint 規則**
   - 基於 Laravel 官方風格指南
   - 部分規則可能與現有代碼衝突
   - 需要人工審查自動修復結果

3. **API 契約約束**
   - 不可變更任何 API 端點 URL
   - 不可變更請求/回應格式
   - 不可變更 HTTP 方法

### 效能考量

**預期效能影響**:

| 變更 | 效能影響 | 說明 |
|------|---------|------|
| API Resources | +5-10ms | 額外的資料轉換層 |
| Form Request | +2-5ms | 額外的驗證處理 |
| Rate Limiting | +1-2ms | Middleware 檢查 |
| Cache | -50-200ms | 快取命中時大幅提升 |

**總體效能**: 預期保持不變或略有提升（Cache 效果）

**效能測試**:
- 使用 Laravel Telescope 監控
- 對比重構前後的 P95 回應時間
- 確保不超過 +10% 的效能損失

### 安全性考量

1. **Type Safety 提升**
   - PHPStan Level 9 減少運行時錯誤
   - 類型安全降低注入攻擊風險

2. **Rate Limiting 保護**
   - 防止 API 濫用
   - 降低 DDoS 風險

3. **驗證層統一**
   - 集中管理驗證規則
   - 減少驗證遺漏風險

### 第三方整合

- 無第三方服務整合
- 僅使用 Laravel 內建功能

## 7. 驗收標準

### 功能驗收

#### Phase 1: PHPStan & Pint
- [ ] `./vendor/bin/phpstan analyse`: 0 errors
- [ ] `./vendor/bin/pint --test`: 100% 通過
- [ ] 所有檔案無 `mixed` type (除非必要)

#### Phase 2: Form Request 統一
- [ ] 所有 Controllers 不使用 `Validator::make()`
- [ ] 6 個 Controllers 驗證全部使用 Form Request
- [ ] 驗證錯誤回應格式不變

#### Phase 3: API Resources
- [ ] 31 個端點全部使用 Resources
- [ ] 不直接返回 Models
- [ ] 回應格式與原有 100% 一致

#### Phase 4: Controller 拆分
- [ ] AdminController 刪除
- [ ] 4 個新 Controllers 創建
- [ ] 所有路由更新且可訪問
- [ ] 測試全部更新

#### Phase 5: Rate Limiting & Cache
- [ ] Rate Limiting 配置生效
- [ ] Cache 策略實作
- [ ] 超過限制返回 429

### 非功能驗收

#### 測試驗收
- [ ] Backend Tests: 201 個測試全部通過
- [ ] Test Coverage: >= 95% (不降低)
- [ ] Frontend E2E: 100% 通過 (API 契約不變)

#### 效能驗收
- [ ] API P95 回應時間: <= +10% (理想: 不變)
- [ ] 資料庫查詢數: 保持不變或減少
- [ ] 無 N+1 查詢問題

#### 代碼品質驗收
- [ ] PHPStan Level 9: 0 errors
- [ ] Cyclomatic Complexity: <= 10 (所有方法)
- [ ] Method Length: <= 100 lines
- [ ] No code duplication (DRY)

#### API 契約驗收 (最關鍵)
- [ ] 31 個端點 URL 不變
- [ ] 請求格式不變 (參數、驗證規則)
- [ ] 回應格式不變 (JSON 結構、欄位名稱)
- [ ] HTTP 狀態碼不變
- [ ] 錯誤回應格式不變

## 8. 風險與依賴

### 潛在風險

#### 風險 1: API 契約破壞 (機率: 中, 影響: 高)
- **描述**: API Resource 實作時意外改變回應格式，導致前端錯誤
- **緩解措施**:
  1. 使用快照測試 (Snapshot Testing) 比對回應
  2. 執行完整的前端 E2E 測試
  3. 手動檢查 API 文檔和實際回應
  4. 分階段實作，每個 Resource 實作後立即測試

#### 風險 2: 測試失敗 (機率: 中, 影響: 中)
- **描述**: 重構過程中測試失敗，需要花時間修復
- **緩解措施**:
  1. 先執行測試確保基準通過
  2. 小步快跑，每個變更立即執行測試
  3. 使用 Git 分支隔離變更
  4. 準備回滾計畫

#### 風險 3: 效能下降 (機率: 低, 影響: 中)
- **描述**: API Resources 和 Form Requests 導致效能下降
- **緩解措施**:
  1. 使用 Laravel Telescope 監控效能
  2. 對比重構前後的效能數據
  3. 必要時使用 Cache 優化
  4. 設定效能閾值 (+10% 為上限)

#### 風險 4: Controller 拆分邏輯錯誤 (機率: 低, 影響: 高)
- **描述**: 拆分 AdminController 時，邏輯遺漏或錯誤
- **緩解措施**:
  1. 逐個方法遷移，不一次性拆分
  2. 每遷移一個方法立即測試
  3. 保留原 AdminController 直到全部測試通過
  4. Code Review 檢查邏輯完整性

### 依賴項目

- **無外部依賴**: 純內部重構
- **工具依賴**:
  - PHPStan (已安裝)
  - Laravel Pint (已安裝)
  - Pest (已安裝)
  - Playwright (Frontend, 已安裝)

## 9. 實施計劃

### 總體時程 (建議)

| 階段 | 時間 | 產出 |
|-----|------|------|
| Phase 1: PHPStan 修復 | 2-3 小時 | 0 errors |
| Phase 2: Pint 修復 | 30 分鐘 | 100% 通過 |
| Phase 3: Form Request | 1-2 小時 | 統一驗證 |
| Phase 4: API Resources | 2-3 小時 | 規範化回應 |
| Phase 5: Controller 拆分 | 2-3 小時 | 4 個新 Controllers |
| Phase 6: Rate Limiting & Cache | 1 小時 | 配置完成 |
| Phase 7: 測試驗證 | 1 小時 | 全部通過 |
| **總計** | **10-13 小時** | **高品質代碼** |

### 執行順序 (嚴格遵守)

```
1. PHPStan 修復 (P0) ✅
   ├─ Controllers
   ├─ Tests
   └─ 驗證: phpstan analyse = 0 errors

2. Laravel Pint (P0) ✅
   ├─ ./vendor/bin/pint
   └─ 驗證: pint --test = 100%

3. Form Request 統一 (P1) ✅
   ├─ 創建 Form Requests
   ├─ 替換 Validator::make()
   └─ 驗證: 測試通過

4. API Resources (P1) ✅
   ├─ 創建 Resources
   ├─ 替換直接返回 Models
   └─ 驗證: 前端測試通過

5. Controller 拆分 (P2) ✅
   ├─ 創建新 Controllers
   ├─ 遷移方法
   ├─ 更新路由
   └─ 驗證: 測試通過

6. Rate Limiting & Cache (P2) ✅
   ├─ 配置 Rate Limiter
   ├─ 實作 Cache
   └─ 驗證: 功能正常

7. 全面測試 (P0) ✅
   ├─ Backend 測試
   ├─ Frontend E2E
   ├─ 效能測試
   └─ 驗證: 全部通過
```

### Checkpoint 驗證

每個階段完成後必須驗證:

**Checkpoint 1** (Phase 1-2 後):
- [ ] PHPStan: 0 errors
- [ ] Pint: 100%
- [ ] Tests: 全部通過

**Checkpoint 2** (Phase 3-4 後):
- [ ] Form Requests: 統一完成
- [ ] API Resources: 實作完成
- [ ] Frontend E2E: 100% 通過

**Checkpoint 3** (Phase 5-6 後):
- [ ] Controllers: 拆分完成
- [ ] Rate Limiting: 配置生效
- [ ] Cache: 實作完成

**Final Checkpoint** (Phase 7):
- [ ] 所有驗收標準通過
- [ ] API 契約 100% 不變
- [ ] 效能不下降

## 10. 成功指標總結

### 定量指標

| 指標 | 目標 | 驗證方式 |
|------|------|---------|
| PHPStan Errors | 0 | `./vendor/bin/phpstan analyse` |
| Pint 通過率 | 100% | `./vendor/bin/pint --test` |
| Test Coverage | >= 95% | `composer test:coverage` |
| Tests 通過 | 201/201 | `composer test` |
| Frontend E2E | 100% | `npx playwright test` |
| API 端點 | 31 個 (不變) | 路由清單 |
| AdminController | 0 行 (刪除) | 文件不存在 |
| New Controllers | 4 個 | 文件存在 |
| Complexity | <= 10 | PHPStan |
| API 回應時間 | <= +10% | Telescope |

### 定性指標

- [ ] 代碼易於維護 (Controllers <= 300 行)
- [ ] 驗證邏輯清晰 (統一 Form Requests)
- [ ] API 回應規範 (統一 Resources)
- [ ] 錯誤處理完整 (類型安全)
- [ ] 文檔更新完整 (API Docs)

## 11. 後續改進計畫

### 未來可優化項目 (Out of Scope)

1. **實作 Repository Pattern** (可選)
   - 進一步分離資料訪問層
   - 提升可測試性

2. **實作 Service Layer Pattern** (可選)
   - 將複雜業務邏輯從 Controllers 移出
   - 提升代碼重用性

3. **實作 Event Sourcing** (長期)
   - 追蹤所有狀態變更
   - 提升審計能力

4. **實作 Queue Jobs** (長期)
   - 異步處理耗時操作
   - 提升回應速度

5. **API Versioning** (長期)
   - 支援多版本 API
   - 平滑升級路徑

---

## 附錄

### A. PHPStan 錯誤範例

**AdminController.php:298**
```php
// ❌ Before
$user->rejectSalesperson($request->input('reason'));

// ✅ After
$reason = (string) $request->validated('reason');
$user->rejectSalesperson($reason);
```

### B. Form Request 範例

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectSalespersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 由 Middleware 處理
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => '拒絕原因為必填',
            'reason.max' => '拒絕原因不可超過 500 字元',
        ];
    }
}
```

### C. API Resource 範例

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalespersonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'bio' => $this->bio,
            'years_of_experience' => $this->years_of_experience,
            'status' => $this->status,
            'average_rating' => $this->average_rating,
            'total_reviews' => $this->total_reviews,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
```

### D. 測試快照範例

```php
<?php

use function Pest\Laravel\getJson;

it('returns salesperson resource with correct structure', function () {
    $salesperson = Salesperson::factory()->create();

    $response = getJson("/api/salespersons/{$salesperson->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user' => ['id', 'email', 'name'],
                'company' => ['id', 'name'],
                'bio',
                'years_of_experience',
                'status',
                'average_rating',
                'total_reviews',
                'created_at',
                'updated_at',
            ],
        ]);

    // Snapshot 測試: 比對完整 JSON 結構
    expect($response->json('data'))->toMatchSnapshot();
});
```

---

**維護者**: Development Team
**文件版本**: 1.0
**建立日期**: 2026-01-20
**最後更新**: 2026-01-20
