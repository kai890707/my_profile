# Proposal: Fix Frontend-Backend API Inconsistency

**Date**: 2026-01-11
**Status**: Proposed
**Priority**: P0 Critical 🔴
**Type**: Bug Fix + Feature Addition

---

## Executive Summary

由於先前的後端 API 更動,導致前端頁面使用的 API 端點出現多處不一致。經過系統性分析,發現 **8 個關鍵問題**,其中 **3 個為 Critical 等級**（導致核心功能無法運作）,**5 個為 High 等級**（影響資料正確性和使用者體驗）。

**影響範圍**:
- ❌ 個人檔案管理頁面完全無法載入
- ❌ 工作經驗管理功能完全失效
- ❌ 證照管理功能完全失效
- ⚠️ 審核狀態查詢異常
- ⚠️ 業務員詳情頁面無法正常顯示
- ⚠️ API 回應格式類型定義錯誤

**預估修復時間**: 14.5 小時（分兩個階段執行）

---

## Problem Statement

### Why - 為什麼需要修復

**核心問題**:
前後端 API 契約（Contract）不一致,導致多個關鍵業務功能無法正常運作。

**問題根源**:
1. **路由前綴不一致**: 前端期待 `/salesperson/*`,後端部分使用 `/profile`, `/profiles/*`
2. **API 端點缺失**: Experiences 和 Certifications 的完整 CRUD API 在後端不存在
3. **回應格式不一致**: 後端使用 `success: boolean`,但前端 TypeScript 定義為 `status: 'success' | 'error'`
4. **回應欄位缺失**: 某些 API 缺少前端需要的欄位（如 `role`, `days_until_reapply`）

**業務影響**:
- 業務員無法管理個人檔案 → 影響平台核心功能
- 無法新增/編輯工作經驗 → 影響檔案完整性和信任度
- 無法上傳證照 → 影響業務員認證和評級
- 使用者體驗差 → 可能導致使用者流失

**技術影響**:
- 前端大量 API 調用失敗（404, 500 錯誤）
- React Query 快取失效
- TypeScript 類型檢查錯誤
- 潛在的資料不同步問題

---

## What - 解決方案概述

### 核心目標

**目標 1: 恢復關鍵功能**
- 修復 `/salesperson/profile` 端點使個人檔案頁面可正常載入
- 建立完整的 Experiences CRUD API
- 建立完整的 Certifications CRUD API

**目標 2: 統一 API 契約**
- 統一使用 `/salesperson/*` 路由前綴
- 統一 API 回應格式為 `{ success: boolean, message: string, data?: T }`
- 確保前後端的資料結構完全一致

**目標 3: 完善缺失功能**
- 新增 Approval Status 聚合查詢 API
- 補齊 Salesperson Status API 的缺失欄位
- 修正前端使用錯誤端點的問題

### 解決方案架構

採用「**優先修復後端**」策略:

```
修復策略:
├── Phase 1: Critical Issues（後端新增/修改）
│   ├── 新增 ExperienceController + CRUD API
│   ├── 新增 CertificationController + CRUD API
│   └── 新增 /salesperson/profile 路由別名
│
└── Phase 2: High Priority（前後端協同修改）
    ├── 新增 Approval Status 聚合 API（後端）
    ├── 修正 Salesperson Status 回應格式（後端）
    ├── 修正 TypeScript 類型定義（前端）
    ├── 調整 API 調用端點（前端）
    └── 統一回應格式（前後端）
```

---

## Scope

### In Scope（本次實作）

**Phase 1 - Critical Fixes** 🔴

1. **新增 Experiences API**
   - ✅ GET `/salesperson/experiences` - 取得經驗列表
   - ✅ POST `/salesperson/experiences` - 新增經驗
   - ✅ PUT `/salesperson/experiences/:id` - 更新經驗
   - ✅ DELETE `/salesperson/experiences/:id` - 刪除經驗
   - ✅ 建立 `ExperienceController`
   - ✅ 建立 Form Requests 驗證
   - ✅ 建立 API Resource
   - ✅ 撰寫 Feature Tests

2. **新增 Certifications API**
   - ✅ GET `/salesperson/certifications` - 取得證照列表
   - ✅ POST `/salesperson/certifications` - 上傳證照
   - ✅ DELETE `/salesperson/certifications/:id` - 刪除證照
   - ✅ 建立 `CertificationController`
   - ✅ 建立 Form Requests 驗證
   - ✅ 建立 API Resource
   - ✅ 支援檔案上傳（證照圖片）
   - ✅ 撰寫 Feature Tests

3. **修正 Profile API 路由**
   - ✅ 新增 `/salesperson/profile` 路由別名
   - ✅ 指向現有的 `SalespersonProfileController::me` 方法

**Phase 2 - High Priority Fixes** 🟡

4. **新增 Approval Status API**
   - ✅ GET `/salesperson/approval-status` - 聚合查詢所有審核狀態
   - ✅ 回應包含: profile, company, certifications, experiences 的審核狀態
   - ✅ 撰寫測試

5. **修正 Salesperson Status API**
   - ✅ 調整回應格式,新增 `role` 欄位
   - ✅ 新增 `days_until_reapply` 計算欄位
   - ✅ 統一欄位命名（`salesperson_status` 而非 `status`）

6. **修正前端 API 類型定義**
   - ✅ 修正 `ApiResponse<T>` 的 `status` → `success: boolean`
   - ✅ 檢查所有使用 `response.status` 的程式碼
   - ✅ 改為使用 `response.success`

7. **修正前端 API 調用端點**
   - ✅ `/search/salespersons/:id` → `/profiles/:id`
   - ✅ `/salesperson/company` → `/companies`

8. **撰寫完整的規範文件**
   - ✅ API 端點規格（Request/Response 範例）
   - ✅ 資料模型規格（Migration, Model, Relationships）
   - ✅ 業務規則定義（驗證邏輯、錯誤處理）
   - ✅ 測試規格（測試案例清單）

### Out of Scope（本次不做）

- ❌ API 版本控制（`/api/v1/`）- 長期規劃
- ❌ 重構所有路由為完全 RESTful - 會影響太多現有程式碼
- ❌ 修改資料庫 Schema（假設 experiences, certifications 資料表已存在）
- ❌ 新增 Experiences/Certifications 的審核工作流程（假設已有審核機制）
- ❌ 前端 E2E 測試（先確保 API 正常）
- ❌ 效能優化（N+1 查詢優化等）- 後續處理
- ❌ 日誌和監控增強 - 後續處理

---

## Success Criteria（驗收標準）

### 功能驗收

**Phase 1 驗收標準**:

1. **Experiences API**
   - [ ] GET `/salesperson/experiences` 回傳該業務員的所有經驗列表
   - [ ] POST `/salesperson/experiences` 可成功新增經驗（需通過驗證）
   - [ ] PUT `/salesperson/experiences/:id` 可成功更新經驗
   - [ ] DELETE `/salesperson/experiences/:id` 可成功刪除經驗
   - [ ] 只能操作自己的經驗（Authorization）
   - [ ] 所有 API 回應格式為 `{ success: boolean, message: string, data?: T }`

2. **Certifications API**
   - [ ] GET `/salesperson/certifications` 回傳該業務員的所有證照列表
   - [ ] POST `/salesperson/certifications` 可成功上傳證照（含檔案）
   - [ ] DELETE `/salesperson/certifications/:id` 可成功刪除證照
   - [ ] 只能操作自己的證照（Authorization）
   - [ ] 檔案上傳正確儲存（使用 Laravel Storage）

3. **Profile API**
   - [ ] GET `/salesperson/profile` 正確回傳個人檔案
   - [ ] 前端 `/dashboard/profile` 頁面可正常載入

**Phase 2 驗收標準**:

4. **Approval Status API**
   - [ ] GET `/salesperson/approval-status` 正確聚合所有審核狀態
   - [ ] 回應包含 profile, company, certifications, experiences 的完整審核資訊

5. **Salesperson Status API**
   - [ ] 回應包含所有前端需要的欄位（role, days_until_reapply 等）
   - [ ] 欄位命名與前端 TypeScript 定義一致

6. **前端 TypeScript 類型**
   - [ ] `ApiResponse<T>` 定義正確（`success: boolean`）
   - [ ] 所有 API 調用無 TypeScript 錯誤
   - [ ] `npm run typecheck` 通過

7. **前端 API 調用**
   - [ ] 所有 API 調用使用正確的端點
   - [ ] 無 404 或 500 錯誤
   - [ ] React Query 快取正常運作

### 技術驗收

**後端測試**:
- [ ] 所有新增的 Feature Tests 通過
- [ ] 測試覆蓋率 > 80%
- [ ] `composer test` 全部通過
- [ ] `composer analyse` (PHPStan Level 9) 通過
- [ ] `composer lint` (Pint) 通過

**前端測試**:
- [ ] TypeScript 編譯無錯誤
- [ ] `npm run typecheck` 通過
- [ ] `npm run build` 成功

**整合測試**:
- [ ] 所有受影響的頁面手動測試通過:
  - `/dashboard/profile` - 載入正常
  - `/dashboard/experiences` - CRUD 功能正常
  - `/dashboard/certifications` - CRUD 功能正常
  - `/dashboard/approval-status` - 顯示正確
  - `/salesperson/[id]` - 詳情頁正常
  - `/search` - 搜尋和跳轉正常

**效能驗收**:
- [ ] API 回應時間 < 200ms（不含檔案上傳）
- [ ] 無 N+1 查詢問題（使用 `with()` eager loading）

---

## Proposed Solutions（建議方案）

### 方案比較

針對路由不一致問題,有三種可能的修復策略:

#### 方案 A: 優先修改後端（推薦）✅

**策略**: 在後端新增 `/salesperson/*` 路由別名,指向現有的 Controller

**優點**:
- ✅ 前端改動最小（只需修改 2 個端點）
- ✅ 向前相容,不破壞現有功能
- ✅ 語義清晰,業務員相關 API 集中
- ✅ 符合前端已建立的 API 契約

**缺點**:
- ⚠️ 後端有重複路由（但可接受）
- ⚠️ 不完全符合 RESTful 規範

**修復範圍**:
- 後端: 新增 8 個路由,建立 2 個 Controller,修改 1 個回應格式
- 前端: 修改 2 個 API 調用端點,修正 1 個類型定義

#### 方案 B: 優先修改前端

**策略**: 前端改用後端現有的 RESTful 端點

**優點**:
- ✅ 符合 RESTful 規範
- ✅ 不需新增後端路由

**缺點**:
- ❌ 前端需大量修改（10+ 個檔案）
- ❌ 可能破壞現有功能
- ❌ 語義不清晰（`/profiles` vs `/salesperson/profile`）

**修復範圍**:
- 後端: 仍需新增 Experiences 和 Certifications API
- 前端: 修改 10+ 個 API 調用,修改所有使用這些 API 的 Hooks 和 Components

#### 方案 C: 引入 API 版本控制

**策略**: 建立 `/api/v2/` 路由,重新設計 API 架構

**優點**:
- ✅ 不破壞現有系統
- ✅ 長期維護更容易

**缺點**:
- ❌ 短期內工作量過大（需重寫大量程式碼）
- ❌ 需要維護兩套 API
- ❌ 過度設計（當前問題不需要如此大的重構）

### 推薦方案: **方案 A**

**理由**:
1. **最小化變更範圍** - 前端已大量使用 `/salesperson/*`,修改後端成本較低
2. **向前相容** - 不破壞任何現有功能
3. **快速修復** - 可以在 1-2 天內完成所有修復
4. **業務價值優先** - 快速恢復功能比完美的架構更重要

**實施計畫**:
詳見下方 "Implementation Plan"

---

## Alternatives Considered（替代方案）

### 替代方案 1: 不修復,前端直接處理錯誤

**描述**: 在前端加入錯誤處理邏輯,當 API 不存在時顯示友善訊息

**優點**:
- 快速（幾小時內完成）
- 不需修改後端

**缺點**:
- ❌ 功能仍然無法使用
- ❌ 不解決根本問題
- ❌ 使用者體驗差

**結論**: **不可行** - 這不是修復,只是掩蓋問題

### 替代方案 2: 完全重構前後端 API 架構

**描述**: 引入 OpenAPI 3.1 規範,使用程式碼生成器自動產生前後端程式碼

**優點**:
- 長期維護容易
- API 契約保證一致
- 類型安全

**缺點**:
- ❌ 需要數週時間
- ❌ 需要學習新工具
- ❌ 過度設計（殺雞用牛刀）

**結論**: **可作為長期規劃** - 但當前優先快速修復

### 替代方案 3: 使用 API Gateway 做路由轉換

**描述**: 在 Nginx 或 API Gateway 層做路由重寫,不修改程式碼

**優點**:
- 不需修改應用層程式碼
- 靈活

**缺點**:
- ❌ 增加系統複雜度
- ❌ 仍需建立缺失的 API（Experiences, Certifications）
- ❌ 維護困難

**結論**: **不適用** - 無法解決「API 不存在」的核心問題

---

## Implementation Plan（實施計畫）

### Phase 1: Critical Fixes（預估 8.5 小時）

**目標**: 恢復核心功能,讓頁面可以正常運作

#### Task 1.1: 建立 Experiences API（預估 4 小時）

**步驟**:
1. 確認資料表結構（假設 `experiences` 資料表已存在）
2. 建立 `Experience` Model（如果不存在）
3. 建立 `ExperienceController`
4. 建立 Form Requests（`StoreExperienceRequest`, `UpdateExperienceRequest`）
5. 建立 `ExperienceResource`
6. 新增路由
7. 撰寫 Feature Tests
8. 手動測試

**檔案清單**:
- `app/Models/Experience.php`（如需建立）
- `app/Http/Controllers/Api/ExperienceController.php`
- `app/Http/Requests/StoreExperienceRequest.php`
- `app/Http/Requests/UpdateExperienceRequest.php`
- `app/Http/Resources/ExperienceResource.php`
- `routes/api.php`（新增路由）
- `tests/Feature/Api/ExperienceControllerTest.php`

#### Task 1.2: 建立 Certifications API（預估 4 小時）

**步驟**:
1. 確認資料表結構（假設 `certifications` 資料表已存在）
2. 建立 `Certification` Model（如果不存在）
3. 建立 `CertificationController`
4. 建立 Form Requests（`StoreCertificationRequest`）
5. 建立 `CertificationResource`
6. 新增路由
7. 處理檔案上傳邏輯（使用 Laravel Storage）
8. 撰寫 Feature Tests
9. 手動測試

**檔案清單**:
- `app/Models/Certification.php`（如需建立）
- `app/Http/Controllers/Api/CertificationController.php`
- `app/Http/Requests/StoreCertificationRequest.php`
- `app/Http/Resources/CertificationResource.php`
- `routes/api.php`（新增路由）
- `tests/Feature/Api/CertificationControllerTest.php`

#### Task 1.3: 修正 Profile API 路由（預估 30 分鐘）

**步驟**:
1. 在 `routes/api.php` 新增路由別名
2. 測試驗證

**修改檔案**:
- `routes/api.php`

```php
// 新增路由
Route::middleware('jwt.auth')->prefix('salesperson')->group(function (): void {
    Route::get('/profile', [SalespersonProfileController::class, 'me']);
    // ... 其他路由
});
```

### Phase 2: High Priority Fixes（預估 6 小時）

**目標**: 完善功能,統一 API 契約

#### Task 2.1: 新增 Approval Status API（預估 2 小時）

**步驟**:
1. 在 `SalespersonController` 新增 `approvalStatus` 方法
2. 實作聚合邏輯（查詢 profile, company, certifications, experiences）
3. 新增路由
4. 撰寫測試

**修改檔案**:
- `app/Http/Controllers/Api/SalespersonController.php`
- `routes/api.php`
- `tests/Feature/Api/SalespersonControllerTest.php`

#### Task 2.2: 修正 Salesperson Status API（預估 1 小時）

**步驟**:
1. 修改 `SalespersonController::status` 方法
2. 新增 `role` 欄位
3. 新增 `days_until_reapply` 計算欄位
4. 統一欄位命名
5. 更新測試

**修改檔案**:
- `app/Http/Controllers/Api/SalespersonController.php`
- `tests/Feature/Api/SalespersonControllerTest.php`

#### Task 2.3: 修正前端 TypeScript 類型定義（預估 1 小時）

**步驟**:
1. 修正 `frontend/types/api.ts` 的 `ApiResponse<T>` 介面
2. 搜尋所有使用 `response.status` 的地方
3. 改為使用 `response.success`
4. 執行 TypeScript 類型檢查

**修改檔案**:
- `frontend/types/api.ts`
- `frontend/lib/api/*.ts`（可能需要調整）
- `frontend/hooks/*.ts`（可能需要調整）

#### Task 2.4: 修正前端 API 調用端點（預估 1 小時）

**步驟**:
1. 修改 `frontend/lib/api/search.ts` 的 `getSalespersonDetail`
   - `/search/salespersons/:id` → `/profiles/:id`
2. 修改 `frontend/lib/api/salesperson.ts` 的 `saveCompany`
   - `/salesperson/company` → `/companies`
3. 測試所有受影響的頁面

**修改檔案**:
- `frontend/lib/api/search.ts`
- `frontend/lib/api/salesperson.ts`

#### Task 2.5: 撰寫規範文件（預估 1 小時）

**步驟**:
1. 撰寫 API 端點規格（`specs/api.md`）
2. 撰寫資料模型規格（`specs/data-model.md`）
3. 撰寫業務規則定義（`specs/business-rules.md`）
4. 撰寫測試規格（`specs/tests.md`）

**產出檔案**:
- `openspec/changes/fix-frontend-backend-api-inconsistency/specs/api.md`
- `openspec/changes/fix-frontend-backend-api-inconsistency/specs/data-model.md`
- `openspec/changes/fix-frontend-backend-api-inconsistency/specs/business-rules.md`
- `openspec/changes/fix-frontend-backend-api-inconsistency/specs/tests.md`

### Phase 3: Testing & Validation（預估 2 小時）

#### Task 3.1: 自動化測試（預估 1 小時）

**步驟**:
1. 執行所有後端測試
2. 執行前端類型檢查
3. 修復任何失敗的測試

**指令**:
```bash
# Backend
cd my_profile_laravel
docker exec -it my_profile_laravel_app composer test
docker exec -it my_profile_laravel_app composer analyse
docker exec -it my_profile_laravel_app composer lint

# Frontend
cd frontend
npm run typecheck
npm run build
```

#### Task 3.2: 手動測試（預估 1 小時）

**測試清單**:
- [ ] 登入系統
- [ ] 訪問 `/dashboard/profile` - 檢查載入正常
- [ ] 訪問 `/dashboard/experiences` - 測試 CRUD 功能
- [ ] 訪問 `/dashboard/certifications` - 測試上傳和刪除
- [ ] 訪問 `/dashboard/approval-status` - 檢查資料正確
- [ ] 訪問 `/search` - 搜尋業務員
- [ ] 點擊業務員卡片 → 訪問 `/salesperson/[id]` - 檢查詳情頁
- [ ] 檢查瀏覽器 Console 無錯誤
- [ ] 檢查 Network Tab 無 404/500 錯誤

---

## Dependencies（相依性）

### 技術相依性

**後端**:
- Laravel 11.x
- PHP 8.3+
- MySQL 8.x
- JWT Authentication (tymon/jwt-auth)
- Laravel Storage (檔案上傳)

**前端**:
- Next.js 15
- React 19
- TypeScript 5.x
- React Query (資料快取)
- Axios (HTTP 客戶端)

### 資料庫相依性

**假設前提** ⚠️:
1. `experiences` 資料表已存在
2. `certifications` 資料表已存在
3. 資料表包含以下欄位:
   - `user_id` (外鍵到 users)
   - `approval_status` (enum: pending, approved, rejected)
   - `rejected_reason` (nullable)
   - 其他業務欄位（company, position, start_date 等）

**如果資料表不存在** ⚠️:
- 需要先建立 Migrations
- 預估額外時間: +2 小時

### 團隊相依性

- **需要**: 資深後端工程師（熟悉 Laravel）
- **需要**: 前端工程師（熟悉 TypeScript 和 React Query）
- **需要**: QA 測試人員（手動測試）

---

## Risks & Mitigations（風險與緩解）

### 風險 1: 資料庫 Schema 不完整

**描述**: Experiences 和 Certifications 的資料表可能不存在或欄位不完整

**機率**: 中（50%）

**影響**: 高 - 會阻塞整個修復流程

**緩解措施**:
1. **Step 0**: 在開始實作前,先執行 `php artisan migrate:status` 檢查資料表
2. 如果資料表不存在,先建立 Migrations（使用 `/implement` 建立 Migration spec）
3. 如果欄位不完整,建立新的 Migration 補充欄位

### 風險 2: 前端快取問題

**描述**: 修改 API 回應格式後,React Query 快取可能導致類型錯誤

**機率**: 高（80%）

**影響**: 中 - 影響使用者體驗,但可快速修復

**緩解措施**:
1. 在測試時先清除瀏覽器快取
2. 在 React Query 配置中設定適當的 `staleTime`
3. 考慮在部署時增加 API 版本號（query string）強制更新

### 風險 3: 測試覆蓋不足

**描述**: 修改後可能破壞現有功能

**機率**: 中（40%）

**影響**: 高 - 可能導致生產環境故障

**緩解措施**:
1. 所有新增的 API 必須撰寫 Feature Tests
2. 修改現有 API 時,必須更新相關測試
3. 執行完整的測試套件（`composer test`）
4. 手動測試所有受影響的頁面
5. 考慮在 Staging 環境先部署測試

### 風險 4: 檔案上傳安全性

**描述**: Certifications 支援檔案上傳,可能有安全漏洞

**機率**: 低（20%）

**影響**: 高 - 可能導致惡意檔案上傳

**緩解措施**:
1. 驗證檔案類型（只允許圖片: jpg, png, pdf）
2. 限制檔案大小（<= 2MB）
3. 使用 Laravel Storage 的 `putFile()` 方法（自動生成安全檔名）
4. 檔案儲存在 `storage/app/certifications/`,不直接對外公開
5. 提供認證後的下載端點

### 風險 5: Authorization 邏輯錯誤

**描述**: 業務員可能可以修改/刪除其他人的資料

**機率**: 中（30%）

**影響**: 高 - 資料安全問題

**緩解措施**:
1. 所有 API 必須檢查 `$request->user()->id === $resource->user_id`
2. 使用 Laravel Policy 統一管理授權邏輯
3. 撰寫測試案例驗證授權檢查（嘗試操作其他人的資料）

### 風險 6: N+1 查詢問題

**描述**: Approval Status API 需要聚合多個資料表,可能產生 N+1 查詢

**機率**: 高（70%）

**影響**: 中 - 效能問題

**緩解措施**:
1. 使用 Eloquent `with()` eager loading
2. 使用 Laravel Debugbar 或 Telescope 監控查詢數量
3. 如果查詢過多,考慮使用快取（Redis）

---

## Success Metrics（成功指標）

### 功能指標

- **API 可用性**: 所有 8 個新增/修改的 API 端點 100% 可用
- **功能恢復率**: 受影響的 6 個頁面 100% 恢復正常
- **錯誤率**: API 錯誤率（404, 500）從當前 ~30% 降至 0%

### 技術指標

- **測試覆蓋率**: 新增程式碼測試覆蓋率 >= 80%
- **測試通過率**: 100% 測試通過（`composer test`）
- **靜態分析**: PHPStan Level 9 零錯誤
- **類型檢查**: TypeScript 零錯誤（`npm run typecheck`）

### 效能指標

- **API 回應時間**: 所有 API < 200ms（P95）
- **資料庫查詢數**: 單一 API 請求 < 10 queries（使用 eager loading）

### 業務指標

- **使用者滿意度**: 修復後無使用者抱怨（觀察 1 週）
- **頁面流失率**: `/dashboard/*` 頁面流失率恢復到正常水平

---

## Timeline（時程規劃）

### 建議執行順序

```
Day 1 (8 hours):
├── Morning (4h): Phase 1 - Task 1.1 (Experiences API)
└── Afternoon (4h): Phase 1 - Task 1.2 (Certifications API)

Day 2 (6.5 hours):
├── Morning (0.5h): Phase 1 - Task 1.3 (Profile API 路由)
├── Morning (2h): Phase 2 - Task 2.1 (Approval Status API)
├── Afternoon (1h): Phase 2 - Task 2.2 (Salesperson Status API)
├── Afternoon (1h): Phase 2 - Task 2.3 (前端類型定義)
├── Afternoon (1h): Phase 2 - Task 2.4 (前端 API 調用)
└── Evening (1h): Phase 2 - Task 2.5 (撰寫規範文件)

Day 3 (2 hours):
├── Morning (1h): Phase 3 - Task 3.1 (自動化測試)
└── Morning (1h): Phase 3 - Task 3.2 (手動測試)

Total: 16.5 hours (含緩衝時間)
```

### 里程碑

- **Milestone 1** (Day 1 完成): 核心 CRUD API 完成,頁面可載入
- **Milestone 2** (Day 2 完成): 所有 API 修復完成,前端調用正確
- **Milestone 3** (Day 3 完成): 所有測試通過,可以部署

---

## Questions for Stakeholders（需要確認的問題）

### ✅ 已確認的決策

#### 決策 1: 資料表結構確認

**狀態**: ✅ 已確認

**結果**:
- `experiences` 表已存在 (Migration: 2026_01_09_132427_create_experiences_table)
- `certifications` 表已存在 (Migration: 2026_01_09_132426_create_certifications_table)
- 兩個表都有完整的欄位,包含 `approval_status`, `rejected_reason` 等

**影響**: ✅ 不需要建立 Migrations,預估時間維持 14.5 小時

#### 決策 2: Experiences 審核機制

**狀態**: ✅ 已確認

**結果**: 不需要審核（新增的經驗 `approval_status = 'approved'`）

**影響**:
- `ExperienceController::store` 將自動設定 `approval_status = 'approved'`
- 簡化業務流程,使用者新增經驗後立即可見

**注意**: Migration 預設值為 `approved`,與需求一致

#### 決策 3: Certifications 檔案儲存

**狀態**: ✅ 已確認

**結果**: 儲存於資料庫 (MEDIUMBLOB `file_data` 欄位)

**影響**:
- 使用資料庫 BLOB 儲存檔案內容
- 需要實作 Base64 編碼/解碼邏輯
- 前端上傳時需轉換為 Base64
- 支援最大 16MB 檔案 (MEDIUMBLOB 限制)

**注意**: Migration 已包含 `file_data MEDIUMBLOB`,`file_mime`, `file_size` 欄位

#### 決策 4: API 版本控制

**狀態**: ✅ 已確認

**結果**: 暫不引入版本控制,直接修復現有端點

**影響**: 快速修復,不增加額外複雜度

#### 決策 5: 部署策略

**狀態**: ✅ 已確認

**結果**: 本地開發,暫不部署

**影響**: 專注於本地測試和驗證

---

## Next Steps（下一步）

**待用戶確認**:
1. ✅ 確認本 Proposal 的修復方案
2. ✅ 回答「Questions for Stakeholders」中的決策點
3. ✅ 確認時程安排（是否可接受 2-3 天完成）

**確認後立即執行**:
1. **Step 2**: 撰寫詳細的技術規格（API, Data Model, Business Rules）
2. **Step 3**: 拆解實作任務（tasks.md）
3. **Step 4**: 驗證規格完整性
4. **Step 5**: 🤖 AUTO-RUN 模式自動實作
5. **Step 6**: 歸檔到規範庫

---

## References（參考資料）

- [API Inconsistency Analysis Report](../../../API_INCONSISTENCY_ANALYSIS.md)
- [Frontend API Client Code](../../../frontend/lib/api/)
- [Backend API Routes](../../../my_profile_laravel/routes/api.php)
- [Backend Controllers](../../../my_profile_laravel/app/Http/Controllers/Api/)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Next.js API Routes](https://nextjs.org/docs/app/building-your-application/routing/route-handlers)

---

**Proposal Status**: ✅ Ready for Review

**待用戶確認後進入 Step 2: Write Specifications**
