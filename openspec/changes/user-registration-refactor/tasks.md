# 實作任務清單 - 用戶註冊流程重構

**Feature**: 用戶註冊流程重構
**Created**: 2026-01-10
**Total Tasks**: 36

---

## Phase 1: Backend 基礎架構 (8 tasks)

### Task 1.1: Database Migration - Users Table
**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_add_salesperson_fields_to_users_table.php`
**內容**:
- 新增 `role` enum 欄位
- 新增 `salesperson_status` enum 欄位
- 新增 `salesperson_applied_at` timestamp 欄位
- 新增 `salesperson_approved_at` timestamp 欄位
- 新增 `rejection_reason` text 欄位
- 新增 `can_reapply_at` timestamp 欄位
- 新增 `is_paid_member` boolean 欄位
- 建立適當的 indexes

**驗收標準**:
- Migration 可成功執行 `php artisan migrate`
- Migration 可成功回滾 `php artisan migrate:rollback`
- 欄位類型和約束符合規格

---

### Task 1.2: Database Migration - Companies Table 簡化
**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_simplify_companies_table.php`
**內容**:
- 新增 `is_personal` boolean 欄位
- 將 `tax_id` 改為 nullable
- 移除 `industry_id`, `address`, `phone` 欄位
- 移除 `approval_status`, `rejected_reason`, `approved_by`, `approved_at` 欄位
- 刪除相關 foreign keys 和 indexes

**驗收標準**:
- Migration 可成功執行
- Migration 可成功回滾
- 欄位變更符合規格

---

### Task 1.3: Database Migration - SalespersonProfiles Table
**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_make_company_id_nullable_in_salesperson_profiles.php`
**內容**:
- 將 `company_id` 改為 nullable
- 移除舊的 `approval_status`, `rejected_reason`, `approved_by`, `approved_at` 欄位（如果存在）

**驗收標準**:
- Migration 可成功執行
- `company_id` 可以為 null

---

### Task 1.4: 更新 User Model
**檔案**: `app/Models/User.php`
**內容**:
- 新增 role 和 status 常數
- 更新 `$fillable` 屬性
- 更新 `$casts` 屬性
- 新增 `salespersonProfile()` relationship
- 新增 helper methods: `isUser()`, `isSalesperson()`, `isApprovedSalesperson()`, `isPendingSalesperson()`, `isAdmin()`, `canReapply()`
- 新增 business methods: `upgradeToSalesperson()`, `approveSalesperson()`, `rejectSalesperson()`

**驗收標準**:
- PHPStan Level 9 無錯誤
- 所有 helper methods 正常運作
- Business methods 正確更新資料庫

---

### Task 1.5: 更新 Company Model
**檔案**: `app/Models/Company.php`
**內容**:
- 更新 `$fillable` 屬性（移除審核和詳細資料欄位）
- 更新 `$casts` 屬性
- 移除 `industry()`, `approver()`, `approvalLogs()` relationships
- 保留 `creator()` 和 `salespersonProfiles()` relationships
- 新增 `scopeRegistered()` 和 `scopePersonal()` scopes

**驗收標準**:
- PHPStan Level 9 無錯誤
- Model relationships 正常運作
- Scopes 正確過濾資料

---

### Task 1.6: 更新 SalespersonProfile Model
**檔案**: `app/Models/SalespersonProfile.php`
**內容**:
- 更新 `$fillable` 屬性（移除審核欄位）
- 移除 `approver()` 和 `approvalLogs()` relationships
- 新增 `getApprovalStatusAttribute()` accessor（從 user 取得）

**驗收標準**:
- PHPStan Level 9 無錯誤
- Accessor 正確返回 user 的 salesperson_status

---

### Task 1.7: 建立 Policies
**檔案**: `app/Policies/SalespersonPolicy.php`, `app/Policies/CompanyPolicy.php`
**內容**:
- SalespersonPolicy: `viewDashboard()`, `createCompany()`, `createRating()`, `canBeSearched()`
- CompanyPolicy: 更新 `create()` method，移除 `approve()` 和 `reject()` methods

**驗收標準**:
- PHPStan Level 9 無錯誤
- Policies 正確判斷權限
- 單元測試通過

---

### Task 1.8: 建立 Middleware
**檔案**: `app/Http/Middleware/EnsureApprovedSalesperson.php`, `app/Http/Middleware/EnsureSalesperson.php`, `app/Http/Middleware/EnsureAdmin.php`
**內容**:
- EnsureApprovedSalesperson: 檢查 `isApprovedSalesperson()`
- EnsureSalesperson: 檢查 `isSalesperson()`
- EnsureAdmin: 檢查 `isAdmin()`
- 註冊到 `bootstrap/app.php` 或 `app/Http/Kernel.php`

**驗收標準**:
- Middleware 正確阻擋未授權請求
- 返回適當的錯誤訊息和 HTTP 狀態碼

---

## Phase 2: Backend API (10 tasks)

### Task 2.1: 建立 Form Requests - Auth
**檔案**: `app/Http/Requests/RegisterRequest.php`, `app/Http/Requests/RegisterSalespersonRequest.php`
**內容**:
- RegisterRequest: `name`, `email`, `password` 驗證
- RegisterSalespersonRequest: 上述 + `full_name`, `phone`, `bio`, `specialties`, `service_regions` 驗證

**驗收標準**:
- PHPStan Level 9 無錯誤
- 驗證規則符合規格
- 錯誤訊息清楚

---

### Task 2.2: 建立 Form Requests - Salesperson
**檔案**: `app/Http/Requests/UpgradeSalespersonRequest.php`, `app/Http/Requests/UpdateSalespersonProfileRequest.php`
**內容**:
- UpgradeSalespersonRequest: 業務員資料驗證
- UpdateSalespersonProfileRequest: 更新業務員資料驗證（包括 company_id）

**驗收標準**:
- PHPStan Level 9 無錯誤
- 驗證規則符合規格

---

### Task 2.3: 建立 Form Requests - Admin & Company
**檔案**: `app/Http/Requests/RejectSalespersonRequest.php`, `app/Http/Requests/StoreCompanyRequest.php`
**內容**:
- RejectSalespersonRequest: `rejection_reason`, `reapply_days` 驗證
- StoreCompanyRequest: `name`, `tax_id` (unique), `is_personal` 驗證（如果 is_personal=false，tax_id 必填）

**驗收標準**:
- PHPStan Level 9 無錯誤
- 複雜驗證規則正確運作

---

### Task 2.4: 更新 AuthController
**檔案**: `app/Http/Controllers/Api/AuthController.php`
**內容**:
- 新增 `register()` method（一般使用者註冊）
- 新增 `registerSalesperson()` method（業務員註冊）
- 使用 Transaction 確保資料一致性

**驗收標準**:
- API endpoint 正常運作
- 返回正確的 response 結構
- 整合測試通過

---

### Task 2.5: 建立 SalespersonController
**檔案**: `app/Http/Controllers/Api/SalespersonController.php`
**內容**:
- `upgrade()`: 升級為業務員
- `status()`: 查詢業務員狀態
- `updateProfile()`: 更新業務員資料
- `index()`: 搜尋已審核業務員

**驗收標準**:
- 所有 methods 正常運作
- 權限檢查正確
- 整合測試通過

---

### Task 2.6: 更新 AdminController
**檔案**: `app/Http/Controllers/Api/AdminController.php`
**內容**:
- 新增 `salespersonApplications()`: 查看待審核申請
- 新增 `approveSalesperson()`: 批准業務員
- 新增 `rejectSalesperson()`: 拒絕業務員
- 移除 `approveCompany()` 和 `rejectCompany()` methods

**驗收標準**:
- 新增的 methods 正常運作
- 移除的 methods 已清除
- 整合測試通過

---

### Task 2.7: 更新 CompanyController
**檔案**: `app/Http/Controllers/Api/CompanyController.php`
**內容**:
- 更新 `store()`: 簡化建立邏輯，僅需 name + tax_id
- 新增 `search()`: 搜尋公司（by tax_id 或 name）
- 移除審核相關邏輯

**驗收標準**:
- `store()` 正確驗證權限（僅 approved salesperson）
- `search()` 正確搜尋公司
- 整合測試通過

---

### Task 2.8: 更新 API Routes
**檔案**: `routes/api.php`
**內容**:
- 新增 Auth routes: `/auth/register`, `/auth/register-salesperson`
- 新增 Salesperson routes: `/salesperson/upgrade`, `/salesperson/status`, `/salesperson/profile`, `/salespeople`
- 新增 Admin routes: `/admin/salesperson-applications`, `/admin/salesperson-applications/{id}/approve`, `/admin/salesperson-applications/{id}/reject`
- 新增 Company routes: `/companies/search`
- 移除舊的公司審核 routes
- 套用適當的 middleware

**驗收標準**:
- 所有 routes 正確定義
- Middleware 正確套用
- `php artisan route:list` 顯示正確

---

### Task 2.9: 資料遷移腳本
**檔案**: `database/migrations/YYYY_MM_DD_HHMMSS_migrate_existing_data.php`
**內容**:
- 將現有業務員設為 `role='salesperson'`, `status='approved'`
- 將沒有 salesperson_profile 的使用者設為 `role='user'`
- 將現有公司設為 `is_personal=false`

**驗收標準**:
- Migration 可成功執行
- 現有資料正確遷移
- 可安全回滾

---

### Task 2.10: API 文檔更新
**檔案**: `docs/api.md` 或 OpenAPI spec
**內容**:
- 記錄所有新增的 API endpoints
- 記錄 request/response schemas
- 記錄錯誤碼和訊息

**驗收標準**:
- 文檔完整且準確
- 包含使用範例

---

## Phase 3: Frontend (12 tasks)

### Task 3.1: 建立註冊頁面 UI
**檔案**: `app/(auth)/register/page.tsx`
**內容**:
- Step 1: 選擇註冊方式（一般使用者 / 業務員）
- Step 2A: 一般使用者註冊表單
- Step 2B: 業務員註冊表單（包含業務員資料）
- 表單驗證（client-side）

**驗收標準**:
- UI 符合設計規格
- 表單驗證正常運作
- 錯誤訊息顯示正確

---

### Task 3.2: 整合註冊 API
**檔案**: `app/(auth)/register/page.tsx`
**內容**:
- 整合 `POST /api/auth/register` API
- 整合 `POST /api/auth/register-salesperson` API
- 處理成功/失敗 response
- 註冊成功後跳轉到適當頁面

**驗收標準**:
- API 呼叫成功
- 錯誤處理正確
- Token 儲存正確

---

### Task 3.3: 建立升級為業務員頁面
**檔案**: `app/(dashboard)/salesperson/upgrade/page.tsx`
**內容**:
- 升級表單 UI（姓名、電話、專長等）
- 表單驗證
- 整合 `POST /api/salesperson/upgrade` API
- 處理「已經是業務員」和「等待期未到」的錯誤

**驗收標準**:
- UI 符合設計規格
- API 整合正確
- 錯誤處理完善

---

### Task 3.4: 建立業務員狀態顯示元件
**檔案**: `components/SalespersonStatusBadge.tsx`
**內容**:
- pending 狀態顯示（審核中提示）
- approved 狀態顯示
- rejected 狀態顯示（拒絕原因 + 重新申請按鈕/倒數）
- 整合 `GET /api/salesperson/status` API

**驗收標準**:
- 三種狀態顯示正確
- UI 符合設計規格
- 倒數計時功能正常

---

### Task 3.5: 建立建立公司頁面 - Step 1
**檔案**: `app/(dashboard)/companies/create/page.tsx`
**內容**:
- Step 1: 選擇公司類型（註冊公司 / 個人工作室）
- 路由狀態管理

**驗收標準**:
- UI 清晰易用
- 狀態管理正確

---

### Task 3.6: 建立建立公司頁面 - Step 2A（註冊公司）
**檔案**: `app/(dashboard)/companies/create/page.tsx`
**內容**:
- 統編輸入欄位
- 整合 `GET /api/companies/search?tax_id={tax_id}` API
- 即時檢查統編是否已存在
- 如果已存在：顯示既有公司資訊 + [加入此公司] 按鈕
- 如果不存在：顯示公司名稱輸入欄位 + [建立公司] 按鈕

**驗收標準**:
- 統編檢查即時反饋
- 兩種情境處理正確
- UI 符合設計規格

---

### Task 3.7: 建立建立公司頁面 - Step 2B（個人工作室）
**檔案**: `app/(dashboard)/companies/create/page.tsx`
**內容**:
- 工作室名稱輸入欄位
- [建立工作室] 按鈕
- 整合 `POST /api/companies` API (tax_id=null, is_personal=true)

**驗收標準**:
- 簡單易用
- API 整合正確

---

### Task 3.8: 建立公司頁面 - 加入既有公司功能
**檔案**: `app/(dashboard)/companies/create/page.tsx`
**內容**:
- [加入此公司] 按鈕功能
- 整合 `PUT /api/salesperson/profile` API（更新 company_id）
- 成功後跳轉到業務員 dashboard

**驗收標準**:
- 加入公司功能正常
- API 整合正確

---

### Task 3.9: 建立管理員審核介面 - 列表頁
**檔案**: `app/(admin)/salesperson-applications/page.tsx`
**內容**:
- 待審核業務員列表 Table
- 顯示：姓名、Email、電話、申請時間
- 操作按鈕：[查看詳情]、[批准]、[拒絕]
- 整合 `GET /api/admin/salesperson-applications` API
- 分頁功能

**驗收標準**:
- 列表顯示正確
- 分頁功能正常
- 操作按鈕可點擊

---

### Task 3.10: 建立管理員審核介面 - 批准/拒絕功能
**檔案**: `app/(admin)/salesperson-applications/page.tsx`
**內容**:
- [批准] 按鈕整合 `POST /api/admin/salesperson-applications/{id}/approve` API
- [拒絕] 按鈕開啟 Modal/Dialog
  - 拒絕原因輸入欄位（必填）
  - 等待天數輸入欄位（預設 7）
  - [確認拒絕] 按鈕整合 `POST /api/admin/salesperson-applications/{id}/reject` API
- 成功後更新列表

**驗收標準**:
- 批准功能正常
- 拒絕功能正常（含拒絕原因和等待期）
- 列表即時更新

---

### Task 3.11: 更新業務員搜尋頁面
**檔案**: `app/(public)/salespeople/page.tsx`
**內容**:
- 整合 `GET /api/salespeople` API
- 確保僅顯示 `status='approved'` 的業務員
- 顯示業務員資料（姓名、公司、專長等）

**驗收標準**:
- 僅顯示已審核業務員
- 資料顯示完整
- 分頁功能正常

---

### Task 3.12: 移除公司審核相關 UI
**檔案**: 各相關元件
**內容**:
- 移除公司審核狀態顯示
- 移除公司詳細資料欄位（產業、地址、電話）
- 移除公司審核 Admin 頁面
- 更新公司列表頁面（簡化顯示）

**驗收標準**:
- 所有審核相關 UI 已移除
- 不影響其他功能
- UI 簡潔清晰

---

## Phase 4: 測試與品質檢查 (6 tasks)

### Task 4.1: 單元測試 - Models
**檔案**: `tests/Unit/Models/UserTest.php`, `tests/Unit/Models/CompanyTest.php`, `tests/Unit/Models/SalespersonProfileTest.php`
**內容**:
- User model: 測試所有 helper methods 和 business methods
- Company model: 測試 scopes 和 unique constraint
- SalespersonProfile model: 測試 accessor

**驗收標準**:
- 所有測試通過
- 測試覆蓋率 ≥80%

---

### Task 4.2: 單元測試 - Policies
**檔案**: `tests/Unit/Policies/SalespersonPolicyTest.php`, `tests/Unit/Policies/CompanyPolicyTest.php`
**內容**:
- 測試所有 policy methods
- 測試各種角色和狀態組合

**驗收標準**:
- 所有測試通過
- 涵蓋所有邊界情況

---

### Task 4.3: 整合測試 - Auth & Salesperson APIs
**檔案**: `tests/Feature/Controllers/AuthControllerTest.php`, `tests/Feature/Controllers/SalespersonControllerTest.php`
**內容**:
- Auth: 測試註冊、業務員註冊
- Salesperson: 測試升級、狀態查詢、更新資料、搜尋

**驗收標準**:
- 所有 API endpoints 測試通過
- 測試覆蓋成功和失敗情境

---

### Task 4.4: 整合測試 - Admin & Company APIs
**檔案**: `tests/Feature/Controllers/AdminControllerTest.php`, `tests/Feature/Controllers/CompanyControllerTest.php`
**內容**:
- Admin: 測試查看申請、批准、拒絕
- Company: 測試建立、搜尋、權限檢查

**驗收標準**:
- 所有 API endpoints 測試通過
- 權限檢查正確

---

### Task 4.5: E2E 測試（可選）
**檔案**: `tests/E2E/` 或使用 Playwright
**內容**:
- 完整註冊流程（一般使用者 + 業務員）
- 升級為業務員流程
- 建立公司流程
- 管理員審核流程

**驗收標準**:
- 所有流程可順利完成
- UI 互動正常

---

### Task 4.6: 品質檢查與修復
**內容**:
- 執行 `php artisan test` - 確保 100% 測試通過
- 執行 `php artisan test --coverage --min=80` - 確保覆蓋率 ≥80%
- 執行 `vendor/bin/phpstan analyse` - 確保 Level 9 無錯誤
- 執行 `vendor/bin/pint --test` - 確保代碼風格符合 PSR-12
- 執行 `npm run type-check` - 確保 TypeScript 編譯無錯誤
- 執行 `npm run lint` - 確保 ESLint 0 errors
- 執行 `npm run build` - 確保構建成功
- 修復所有發現的問題

**驗收標準**:
- ✅ Tests: 100% passing
- ✅ Coverage: ≥80%
- ✅ PHPStan: Level 9, 0 errors
- ✅ Code Style: PSR-12 compliant
- ✅ TypeScript: 0 errors
- ✅ ESLint: 0 errors
- ✅ Build: Success

---

## 任務總結

- **Phase 1** (Backend 基礎架構): 8 tasks
- **Phase 2** (Backend API): 10 tasks
- **Phase 3** (Frontend): 12 tasks
- **Phase 4** (測試與品質): 6 tasks

**總計**: 36 tasks

**預估時間**:
- Phase 1: 2-3 小時
- Phase 2: 3-4 小時
- Phase 3: 4-5 小時
- Phase 4: 2-3 小時
- **總計**: 11-15 小時（或 AUTO-RUN 模式 25-30 分鐘）

**關鍵里程碑**:
1. ✅ Phase 1 完成 → Backend 基礎架構就緒
2. ✅ Phase 2 完成 → 所有 API endpoints 可用
3. ✅ Phase 3 完成 → 完整的 UI 功能
4. ✅ Phase 4 完成 → 品質檢查通過，準備上線

**下一步**: Step 4 規格驗證（使用者最後確認點）
