# Implementation Tasks: Fix Frontend-Backend API Inconsistency

**Date**: 2026-01-11
**Project**: YAMU Backend API + Frontend
**Status**: Ready for Implementation

---

## 執行概要

**總任務數**: 26 個
**預估總時間**: 14.5 小時
**執行模式**: AUTO-RUN (Step 5 之後自動執行)

---

## Phase 1: Backend - Critical API Endpoints (Priority P0 🔴)

### 1.1 建立 Experience Model 和 Resources（預估 30 分鐘）

- [x] **Task 1.1.1**: 建立 Experience Model
  - 檔案: `my_profile_laravel/app/Models/Experience.php`
  - 內容:
    - $fillable 欄位定義
    - $casts 定義 (dates)
    - Relationships: belongsTo(User), belongsTo(User, 'approved_by')
    - Scopes: approved(), rejected(), pending(), sortedByOrder()
    - Helper: isApproved(), isPending(), isRejected()
  - 參考: `openspec/changes/fix-frontend-backend-api-inconsistency/specs/data-model.md` (Line 150-230)

- [x] **Task 1.1.2**: 建立 ExperienceResource
  - 檔案: `my_profile_laravel/app/Http/Resources/ExperienceResource.php`
  - 內容:
    - 轉換所有欄位
    - 格式化日期 (ISO 8601)
  - 參考: `specs/data-model.md` (Line 330-365)

- [x] **Task 1.1.3**: 更新 User Model 新增 experiences 關聯
  - 檔案: `my_profile_laravel/app/Models/User.php`
  - 內容:
    - 新增 `public function experiences(): HasMany`
  - 參考: `specs/data-model.md` (Line 457-468)

---

### 1.2 建立 Experience CRUD API（預估 3.5 小時）

- [x] **Task 1.2.1**: 建立 ExperienceController
  - 檔案: `my_profile_laravel/app/Http/Controllers/Api/ExperienceController.php`
  - 方法:
    - `index()` - GET /salesperson/experiences
    - `store()` - POST /salesperson/experiences
    - `update()` - PUT /salesperson/experiences/:id
    - `destroy()` - DELETE /salesperson/experiences/:id
  - 參考: `specs/api.md` (Line 20-245)

- [x] **Task 1.2.2**: 建立 StoreExperienceRequest
  - 檔案: `my_profile_laravel/app/Http/Requests/StoreExperienceRequest.php`
  - 驗證規則:
    - company: required, string, max:200
    - position: required, string, max:200
    - start_date: required, date
    - end_date: nullable, date, after_or_equal:start_date
    - description: nullable, string
  - 參考: `specs/api.md` (Line 82-103)

- [x] **Task 1.2.3**: 建立 UpdateExperienceRequest
  - 檔案: `my_profile_laravel/app/Http/Requests/UpdateExperienceRequest.php`
  - 驗證規則: 與 StoreExperienceRequest 相同
  - 參考: `specs/api.md` (Line 170-191)

- [x] **Task 1.2.4**: 新增 Experiences API 路由
  - 檔案: `my_profile_laravel/routes/api.php`
  - 內容:
    ```php
    Route::middleware('jwt.auth')->prefix('salesperson')->group(function () {
        Route::get('/experiences', [ExperienceController::class, 'index']);
        Route::post('/experiences', [ExperienceController::class, 'store']);
        Route::put('/experiences/{id}', [ExperienceController::class, 'update']);
        Route::delete('/experiences/{id}', [ExperienceController::class, 'destroy']);
    });
    ```
  - 參考: `specs/api.md` (Line 1-10)

---

### 1.3 建立 Certification Model 和 Resources（預估 30 分鐘）

- [x] **Task 1.3.1**: 建立 Certification Model
  - 檔案: `my_profile_laravel/app/Models/Certification.php`
  - 內容:
    - $fillable 欄位定義（不含 file_data）
    - $casts 定義 (dates)
    - Relationships: belongsTo(User), belongsTo(User, 'approved_by')
    - Scopes: approved(), rejected(), pending()
    - Helpers: hasFile(), getFileSizeInMB(), isApproved(), isPending(), isRejected()
  - 參考: `specs/data-model.md` (Line 235-310)

- [x] **Task 1.3.2**: 建立 CertificationResource
  - 檔案: `my_profile_laravel/app/Http/Resources/CertificationResource.php`
  - 內容:
    - 轉換所有欄位（file_data 永遠回傳 null）
    - 格式化日期
    - 新增 has_file 欄位
    - 新增 file_size_mb 欄位
  - 參考: `specs/data-model.md` (Line 370-415)

- [x] **Task 1.3.3**: 更新 User Model 新增 certifications 關聯
  - 檔案: `my_profile_laravel/app/Models/User.php`
  - 內容:
    - 新增 `public function certifications(): HasMany`
  - 參考: `specs/data-model.md` (Line 470-481)

---

### 1.4 建立 Certification CRUD API（預估 3.5 小時）

- [x] **Task 1.4.1**: 建立 CertificationController
  - 檔案: `my_profile_laravel/app/Http/Controllers/Api/CertificationController.php`
  - 方法:
    - `index()` - GET /salesperson/certifications
    - `store()` - POST /salesperson/certifications
    - `destroy()` - DELETE /salesperson/certifications/:id
  - 特別注意:
    - store() 需要處理 Base64 解碼
    - store() 需要儲存 file_data, file_mime, file_size
    - 檔案大小限制 16MB
  - 參考: `specs/api.md` (Line 250-380)

- [x] **Task 1.4.2**: 建立 StoreCertificationRequest
  - 檔案: `my_profile_laravel/app/Http/Requests/StoreCertificationRequest.php`
  - 驗證規則:
    - name: required, string, max:200
    - issuer: required, string, max:200
    - issue_date: nullable, date
    - expiry_date: nullable, date, after_or_equal:issue_date
    - description: nullable, string
    - file: required, string (Base64)
    - file_mime: required, in:image/jpeg,image/png,image/jpg,application/pdf
  - 自定義驗證:
    - 檢查 Base64 有效性
    - 檢查解碼後檔案大小 <= 16MB
  - 參考: `specs/api.md` (Line 293-328)

- [x] **Task 1.4.3**: 新增 Certifications API 路由
  - 檔案: `my_profile_laravel/routes/api.php`
  - 內容:
    ```php
    Route::middleware('jwt.auth')->prefix('salesperson')->group(function () {
        Route::get('/certifications', [CertificationController::class, 'index']);
        Route::post('/certifications', [CertificationController::class, 'store']);
        Route::delete('/certifications/{id}', [CertificationController::class, 'destroy']);
    });
    ```
  - 參考: `specs/api.md` (Line 250-260)

---

### 1.5 新增 Profile API 路由別名（預估 10 分鐘）

- [x] **Task 1.5.1**: 新增 /salesperson/profile 路由
  - 檔案: `my_profile_laravel/routes/api.php`
  - 內容:
    ```php
    Route::middleware('jwt.auth')->prefix('salesperson')->group(function () {
        Route::get('/profile', [SalespersonProfileController::class, 'me']);
    });
    ```
  - 參考: `specs/api.md` (Line 385-395)
  - 注意: 這是路由別名,指向現有的 `SalespersonProfileController::me` 方法

---

## Phase 2: Backend - High Priority API Fixes (Priority P1 🟡)

### 2.1 新增 Approval Status 聚合 API（預估 1.5 小時）

- [x] **Task 2.1.1**: 在 SalespersonController 新增 approvalStatus 方法
  - 檔案: `my_profile_laravel/app/Http/Controllers/Api/SalespersonController.php`
  - 方法: `public function approvalStatus(Request $request): JsonResponse`
  - 內容:
    - 查詢 profile 的 approval_status
    - 查詢 company 的 approval_status
    - 查詢所有 certifications 的審核狀態（使用 eager loading）
    - 查詢所有 experiences 的審核狀態（使用 eager loading）
    - 回傳聚合資料
  - 參考: `specs/api.md` (Line 400-500)

- [x] **Task 2.1.2**: 新增 /salesperson/approval-status 路由
  - 檔案: `my_profile_laravel/routes/api.php`
  - 內容:
    ```php
    Route::middleware('jwt.auth')->prefix('salesperson')->group(function () {
        Route::get('/approval-status', [SalespersonController::class, 'approvalStatus']);
    });
    ```

---

### 2.2 修正 Salesperson Status API 回應格式（預估 1 小時）

- [x] **Task 2.2.1**: 修改 SalespersonController::status 方法
  - 檔案: `my_profile_laravel/app/Http/Controllers/Api/SalespersonController.php`
  - 修改內容:
    - 新增 `role` 欄位
    - 欄位命名統一為 `salesperson_status` (而非 `status`)
    - 新增 `days_until_reapply` 計算欄位
    - 調整回應結構為 `{ success: true, data: {...} }`
  - 參考: `specs/api.md` (Line 505-585)

---

## Phase 3: Frontend - TypeScript 和 API 調用修正 (Priority P1 🟡)

### 3.1 修正 TypeScript 類型定義（預估 30 分鐘）

- [x] **Task 3.1.1**: 修正 ApiResponse<T> 介面
  - 檔案: `frontend/types/api.ts`
  - 修改:
    ```typescript
    export interface ApiResponse<T = any> {
      success: boolean;  // ✅ 修正: 從 status 改為 success
      message: string;
      data?: T;
      errors?: Record<string, string[]>;
    }
    ```
  - 參考: API 分析報告 #7

- [x] **Task 3.1.2**: 檢查並修正所有使用 response.status 的地方
  - 檔案:
    - `frontend/lib/api/*.ts`
    - `frontend/hooks/*.ts`
  - 搜尋: `response.status` 或 `res.status`
  - 改為: `response.success` 或 `res.success`
  - 使用 Grep 工具搜尋:
    ```bash
    grep -r "response\.status" frontend/lib/ frontend/hooks/
    ```

---

### 3.2 修正 API 調用端點（預估 30 分鐘）

- [x] **Task 3.2.1**: 修正搜尋詳情 API 端點
  - 檔案: `frontend/lib/api/search.ts`
  - 修改:
    ```typescript
    export async function getSalespersonDetail(id: number): Promise<SalespersonProfile> {
      const response = await apiClient.get<ApiResponse<SalespersonProfile>>(
        `/profiles/${id}`  // ✅ 修正: 從 /search/salespersons/:id 改為 /profiles/:id
      );
      return response.data.data!;
    }
    ```
  - 參考: API 分析報告 #6

- [x] **Task 3.2.2**: 修正儲存公司 API 端點
  - 檔案: `frontend/lib/api/salesperson.ts`
  - 修改:
    ```typescript
    export async function saveCompany(data: SaveCompanyRequest): Promise<ApiResponse<Company>> {
      const response = await apiClient.post<ApiResponse<Company>>(
        '/companies',  // ✅ 修正: 從 /salesperson/company 改為 /companies
        data
      );
      return response.data;
    }
    ```
  - 參考: API 分析報告 #5

---

## Phase 4: Testing（預估 3 小時）

### 4.1 Experience API Feature Tests（預估 1 小時）

- [x] **Task 4.1.1**: 建立 ExperienceControllerTest
  - 檔案: `my_profile_laravel/tests/Feature/Api/ExperienceControllerTest.php`
  - 測試案例（共 12 個）:
    - GET /salesperson/experiences (3 個測試)
    - POST /salesperson/experiences (4 個測試)
    - PUT /salesperson/experiences/:id (3 個測試)
    - DELETE /salesperson/experiences/:id (2 個測試)
  - 參考: `specs/tests.md` (Line 20-290)

---

### 4.2 Certification API Feature Tests（預估 1.5 小時）

- [x] **Task 4.2.1**: 建立 CertificationControllerTest
  - 檔案: `my_profile_laravel/tests/Feature/Api/CertificationControllerTest.php`
  - 測試案例（共 15 個）:
    - GET /salesperson/certifications (3 個測試)
    - POST /salesperson/certifications (9 個測試,含檔案上傳)
    - DELETE /salesperson/certifications/:id (3 個測試)
  - 重點:
    - 測試 Base64 上傳
    - 測試檔案大小限制
    - 測試檔案類型限制
    - 測試 approval_status = 'pending'
  - 參考: `specs/tests.md` (Line 295-600)

---

### 4.3 其他 API Tests（預估 30 分鐘）

- [x] **Task 4.3.1**: 新增 Approval Status API 測試
  - 檔案: `my_profile_laravel/tests/Feature/Api/SalespersonControllerTest.php`
  - 測試案例（共 5 個）:
    - 測試聚合查詢
    - 測試資料完整性
    - 測試 eager loading（無 N+1）
  - 參考: `specs/tests.md` (Line 605-690)

- [x] **Task 4.3.2**: 更新 Salesperson Status API 測試
  - 檔案: `my_profile_laravel/tests/Feature/Api/SalespersonControllerTest.php`
  - 測試案例（共 4 個）:
    - 測試新增的 role 欄位
    - 測試 days_until_reapply 計算
    - 測試回應格式
  - 參考: `specs/tests.md` (Line 695-765)

---

### 4.4 Unit Tests（預估 30 分鐘，可選）

- [x] **Task 4.4.1**: 建立 Experience Model Unit Tests
  - 檔案: `my_profile_laravel/tests/Unit/Models/ExperienceTest.php`
  - 測試 Scopes, Helpers, Relationships
  - 參考: `specs/tests.md` (Line 800-840)
  - ✅ 完成: 4 個測試全部通過

- [x] **Task 4.4.2**: 建立 Certification Model Unit Tests
  - 檔案: `my_profile_laravel/tests/Unit/Models/CertificationTest.php`
  - 測試 Scopes, Helpers, Relationships
  - 參考: `specs/tests.md` (Line 845-885)
  - ✅ 完成: 6 個測試全部通過

---

## Phase 5: Validation & Documentation（預估 30 分鐘）

### 5.1 執行測試驗證（預估 20 分鐘）

- [x] **Task 5.1.1**: 執行所有後端測試
  - 指令:
    ```bash
    cd my_profile_laravel
    docker exec my_profile_laravel_app composer test
    ```
  - 確認: 所有測試通過

- [x] **Task 5.1.2**: 執行靜態分析
  - 指令:
    ```bash
    docker exec my_profile_laravel_app composer analyse
    ```
  - 確認: PHPStan Level 9 無錯誤

- [x] **Task 5.1.3**: 執行程式碼風格檢查
  - 指令:
    ```bash
    docker exec my_profile_laravel_app composer lint
    ```
  - 確認: Laravel Pint 格式化完成

- [x] **Task 5.1.4**: 執行前端類型檢查
  - 指令:
    ```bash
    cd frontend
    npm run typecheck
    ```
  - 確認: TypeScript 編譯無錯誤

---

### 5.2 手動測試（預估 10 分鐘，可選）

- [ ] **Task 5.2.1**: 測試 Experiences CRUD
  - 使用 Postman 或 curl 測試
  - 測試所有端點（GET, POST, PUT, DELETE）

- [ ] **Task 5.2.2**: 測試 Certifications CRUD
  - 測試 Base64 檔案上傳
  - 測試檔案大小和類型限制

- [ ] **Task 5.2.3**: 測試前端頁面
  - 訪問 `/dashboard/profile`
  - 訪問 `/dashboard/experiences`
  - 訪問 `/dashboard/certifications`
  - 訪問 `/dashboard/approval-status`

---

## 任務執行原則

### AUTO-RUN 模式規則

**重要**: 從 Step 5 開始,所有任務將在 AUTO-RUN 模式下自動執行

1. **不詢問確認**
   - 不使用 `AskUserQuestion`
   - 不等待用戶輸入
   - 完全基於規格自主決策

2. **自動錯誤修復**
   - 遇到語法錯誤 → 自動修復
   - 測試失敗 → 自動調整
   - 只有規格不清時才暫停

3. **進度追蹤**
   - 使用 TodoWrite 追蹤所有任務
   - 一次只有一個任務 in_progress
   - 完成立即標記 completed

4. **任務順序**
   - 嚴格按照 Phase 順序執行
   - Phase 內的任務按編號順序
   - 前一個任務完成才開始下一個

---

## 檔案清單總覽

### Backend 新增檔案（共 10 個）

**Models** (2):
- `app/Models/Experience.php`
- `app/Models/Certification.php`

**Controllers** (2):
- `app/Http/Controllers/Api/ExperienceController.php`
- `app/Http/Controllers/Api/CertificationController.php`

**Form Requests** (3):
- `app/Http/Requests/StoreExperienceRequest.php`
- `app/Http/Requests/UpdateExperienceRequest.php`
- `app/Http/Requests/StoreCertificationRequest.php`

**Resources** (2):
- `app/Http/Resources/ExperienceResource.php`
- `app/Http/Resources/CertificationResource.php`

**Tests** (2):
- `tests/Feature/Api/ExperienceControllerTest.php`
- `tests/Feature/Api/CertificationControllerTest.php`

### Backend 修改檔案（共 3 個）

- `app/Models/User.php` (新增 experiences 和 certifications 關聯)
- `routes/api.php` (新增 8 個路由)
- `app/Http/Controllers/Api/SalespersonController.php` (新增 approvalStatus 方法,修改 status 方法)
- `tests/Feature/Api/SalespersonControllerTest.php` (新增測試)

### Frontend 修改檔案（共 3 個）

- `frontend/types/api.ts` (修正 ApiResponse 介面)
- `frontend/lib/api/search.ts` (修正端點)
- `frontend/lib/api/salesperson.ts` (修正端點)
- 可能需要修改的其他檔案（根據 grep 搜尋結果）

---

## 預估時間總覽

| Phase | 內容 | 預估時間 |
|-------|------|---------|
| Phase 1.1 | Experience Model & Resources | 0.5h |
| Phase 1.2 | Experience CRUD API | 3.5h |
| Phase 1.3 | Certification Model & Resources | 0.5h |
| Phase 1.4 | Certification CRUD API | 3.5h |
| Phase 1.5 | Profile API 路由別名 | 0.2h |
| Phase 2.1 | Approval Status API | 1.5h |
| Phase 2.2 | Salesperson Status API | 1.0h |
| Phase 3.1 | TypeScript 類型修正 | 0.5h |
| Phase 3.2 | API 端點修正 | 0.5h |
| Phase 4 | Testing | 3.0h |
| Phase 5 | Validation | 0.5h |
| **總計** | | **14.7h** |

---

## 驗收標準

### Phase 1-2 完成標準（Backend）

- [ ] 所有 10 個 API 端點實作完成
- [ ] 所有 Form Requests 驗證邏輯正確
- [ ] 所有 Models 和 Resources 建立完成
- [ ] routes/api.php 新增所有路由
- [ ] 所有測試通過（41 個測試案例）

### Phase 3 完成標準（Frontend）

- [ ] ApiResponse<T> 類型定義正確
- [ ] 所有使用 response.status 的地方已改為 response.success
- [ ] API 端點調用正確（/profiles/:id, /companies）
- [ ] TypeScript 編譯無錯誤

### Phase 4 完成標準（Testing）

- [ ] composer test 全部通過
- [ ] PHPStan Level 9 無錯誤
- [ ] Laravel Pint 格式化完成
- [ ] npm run typecheck 通過

### Phase 5 完成標準（Overall）

- [ ] 所有 6 個前端頁面可正常載入
- [ ] 無 API 404 或 500 錯誤
- [ ] React Query 快取正常運作
- [ ] 規格已歸檔到 openspec/specs/

---

**開始執行**: 使用 `/develop` 命令啟動 AUTO-RUN 模式
**規格參考**: `openspec/changes/fix-frontend-backend-api-inconsistency/specs/`
**任務狀態**: Ready for Implementation ✅
