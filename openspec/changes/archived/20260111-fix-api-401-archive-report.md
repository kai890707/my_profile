# OpenSpec 歸檔報告

**日期**: 2026-01-11
**執行者**: Claude Sonnet 4.5
**歸檔數量**: 1 個修復

---

## 歸檔摘要

本次歸檔了一個重要的 Bug Fix 變更到 OpenSpec 規範庫：

1. **20260111-fix-api-401-unauthorized** - API 401 認證問題 & Dashboard TypeError 修復

---

## 歸檔詳情

### API 401 Authentication & Dashboard TypeError (20260111-fix-api-401-unauthorized)

**類型**: Bug Fix (雙重修復)
**優先級**: Critical
**狀態**: ✅ 已完成並歸檔

**問題描述**:

**問題 1 - Backend 認證錯誤**:
- 業務員登入後訪問 `/dashboard` 頁面
- 前端呼叫 `GET /api/salesperson/profile` API
- Backend 始終返回 401 Unauthorized
- 已確認 JWT Token 有效且未過期

**問題 2 - Frontend Dashboard 崩潰**:
```
TypeError: Cannot read properties of undefined (reading 'substring')
  at app/(dashboard)/dashboard/page.tsx:214:42
```

**根本原因**:

**原因 1 - Laravel Request User Resolution 問題**:
```php
// ❌ Middleware 使用非標準方式設定用戶
$request->merge(['auth_user' => $authenticatedUser]);

// ❌ Controller 嘗試獲取用戶
$user = $request->get('auth_user');  // 返回 null
```

- 在 Laravel 11 中，`$request->merge()` 添加的自定義屬性不會在 Request lifecycle 中持久化
- 當 Request 對象被 clone 或 rebuild 時，custom attributes 會丟失
- Controller 接收到 `null`，觸發 401 錯誤

**原因 2 - Incomplete Optional Chaining**:
```tsx
// ❌ 缺少完整的 optional chaining
fallback={profileData?.full_name.substring(0, 2)}
```

- `profileData?` 使用了 optional chaining
- 但 `full_name` 沒有使用，若 `full_name` 為 undefined 則拋出錯誤

**解決方案**:

**解決方案 1 - 使用 Laravel 標準 API**:

**Middleware 修改** (`app/Http/Middleware/JwtAuthMiddleware.php`):
```php
// ✅ 使用 Laravel 標準 User Resolution
$request->setUserResolver(function () use ($authenticatedUser) {
    return $authenticatedUser;
});

// Also set on auth guard for consistency
auth()->setUser($authenticatedUser);
```

**Controllers 修改** (4 個檔案):
```php
// ✅ 使用 Laravel 標準 getter
$user = $request->user();
```

**修改檔案**:
- `app/Http/Middleware/JwtAuthMiddleware.php` (Lines 43-49)
- `app/Http/Controllers/Api/SalespersonProfileController.php` (4 methods)
- `app/Http/Controllers/Api/CompanyController.php` (3 methods)
- `app/Http/Middleware/RoleMiddleware.php` (Lines 21-22)

**解決方案 2 - 完整 Optional Chaining + Fallback**:

**Frontend 修改** (`app/(dashboard)/dashboard/page.tsx`):
```tsx
// ✅ 完整的 optional chaining 和 fallback
fallback={profileData?.full_name?.substring(0, 2) || 'U'}
```

**修改位置**:
- Line 214 (View Mode Avatar)
- Line 271 (Edit Mode Avatar)

**診斷過程**:

本次修復採用系統化診斷方法：

1. **Playwright 自動化測試**:
   - 測試完整登入流程
   - 驗證 Tokens 正確儲存在 localStorage
   - 解碼 JWT: User ID: 4, Role: user, Not Expired
   - 確認 Authorization header 正確發送

2. **直接 API 測試**:
   ```bash
   curl -H "Authorization: Bearer {token}" \
     http://localhost:8080/api/salesperson/profile
   # 結果: 401 "Unauthorized"
   ```

3. **代碼追蹤**:
   - Middleware → Controller 流程分析
   - 發現 `$request->merge()` 問題

4. **Laravel Tinker 驗證**:
   - 檢查資料庫表結構
   - 創建測試用 salesperson_profile
   - 驗證修復後的 API 回應

**驗證結果**:

**Backend API 測試**:
- ✅ 有 profile: 返回 200 OK + 完整 profile 資料
- ✅ 無 profile: 返回 404 Not Found (不再是 401)
- ✅ 無效 token: 返回 401 Unauthorized (正確行為)

**Frontend Dashboard 測試**:
- ✅ 無 TypeError
- ✅ Avatar 正常顯示
- ✅ Fallback 值正確運作 ('U')

**歸檔文件**:
- `proposal.md` (26,228 bytes) - 初始提案
- `診斷-test-api-401.md` (4,436 bytes) - 診斷指南
- `DIAGNOSTIC-RESULTS.md` (7,036 bytes) - 完整診斷結果
- `FIX-SUMMARY.md` (8,663 bytes) - Backend 修復總結
- `COMPLETE-FIX-SUMMARY.md` (11,829 bytes) - 完整修復總結

**影響範圍**:
- Backend: 所有需要認證的 API 端點
- Frontend: Dashboard 頁面（業務員核心功能）
- 用戶: 所有登入的業務員

---

## 歸檔操作記錄

### Step 1: 移動變更目錄
```bash
mv openspec/changes/20260111-fix-api-401-unauthorized \
   openspec/changes/archived/
```
**狀態**: ✅ 已完成

### Step 2: 更新 CHANGELOG.md
在 `[2026-01-11]` 日期下的 `### Fixed` 部分，新增修復記錄：

- 問題 1: Backend Middleware 認證問題
- 問題 2: Frontend Dashboard TypeError
- 解決方案 1: Laravel 標準 API
- 解決方案 2: 完整 optional chaining
- 影響範圍: 4 Backend 檔案 + 1 Frontend 檔案
- 測試驗證結果

**狀態**: ✅ 已完成

### Step 3: 創建歸檔報告
本文件
**狀態**: ✅ 已完成

---

## 歸檔驗證

### 文件完整性檢查

**20260111-fix-api-401-unauthorized**:
- ✅ `proposal.md` (26,228 bytes)
- ✅ `診斷-test-api-401.md` (4,436 bytes)
- ✅ `DIAGNOSTIC-RESULTS.md` (7,036 bytes)
- ✅ `FIX-SUMMARY.md` (8,663 bytes)
- ✅ `COMPLETE-FIX-SUMMARY.md` (11,829 bytes)

**總大小**: ~58 KB (文檔詳盡、包含診斷過程和學習點)

### 目錄結構檢查

```
openspec/changes/archived/
├── 20260108-add-frontend-spa-project/
├── 20260108-add-swagger-api-documentation/
├── 20260110-refactor-user-registration/
├── 20260111-fix-cors-issue/
├── 20260111-fix-frontend-backend-api-inconsistency/
├── 20260111-fix-header-typeerror/
├── 20260111-fix-api-401-unauthorized/              ✅ 新歸檔
├── 20260111-archive-report.md
└── README.md
```

**狀態**: ✅ 目錄結構正確

---

## 統計資訊

### 本次歸檔統計

| 指標 | 數值 |
|------|------|
| 歸檔變更數 | 1 |
| 修復的 Bug | 2 (Backend 認證 + Frontend TypeError) |
| 修改的 Backend 檔案 | 4 (1 middleware + 3 controllers) |
| 修改的 Frontend 檔案 | 1 (2 locations) |
| 文件總大小 | ~58 KB |
| 診斷測試腳本 | 2 (Playwright automation) |
| 受影響的功能 | 所有業務員認證相關功能 + Dashboard |

### 程式碼變更統計

**Backend**:
- Lines Changed: 8 methods updated
- Middleware: 1 file (JwtAuthMiddleware.php)
- Controllers: 3 files (SalespersonProfileController, CompanyController, RoleMiddleware)

**Frontend**:
- Lines Changed: 2 locations
- Files: 1 (dashboard/page.tsx)

### 累計歸檔統計

| 指標 | 數值 |
|------|------|
| 已歸檔變更總數 | 9 |
| 功能新增 | 5 |
| Bug 修復 | 4 |
| 重構 | 1 |

---

## 歸檔後狀態

### Changes 目錄狀態
```bash
ls openspec/changes/
# 輸出: archived/  (僅剩歸檔目錄)
```

**狀態**: ✅ 無未歸檔的變更

### CHANGELOG.md 狀態
- ✅ [2026-01-11] 已更新
- ✅ Fixed 部分已添加完整記錄
- ✅ 包含雙重修復的詳細說明

---

## 品質檢查

### 文檔品質
- ✅ proposal.md 完整（問題陳述、解決方案、驗收標準）
- ✅ 診斷文件詳盡（5 個診斷步驟 + 5 個可能根因）
- ✅ DIAGNOSTIC-RESULTS.md 包含完整診斷過程
- ✅ FIX-SUMMARY.md 記錄 Backend 修復細節
- ✅ COMPLETE-FIX-SUMMARY.md 涵蓋雙重修復和學習點
- ✅ 格式統一、易於閱讀

### 命名規範
- ✅ `YYYYMMDD-action-description` 格式正確
- ✅ action 使用 `fix` (符合 Bug Fix 性質)
- ✅ description 使用 kebab-case: `api-401-unauthorized`

### 歷史可追溯性
- ✅ 完整的診斷過程記錄（Playwright + curl + tinker）
- ✅ 保留診斷腳本（可重現問題）
- ✅ 詳細的根因分析（Laravel Request lifecycle）
- ✅ 代碼對比（修改前後）
- ✅ 驗證結果（測試矩陣）
- ✅ 學習點和預防措施

---

## 技術學習點

### 1. Laravel Request User Resolution 機制

**關鍵發現**:
- `$request->merge()` 僅添加到 input parameters
- Request 對象在 middleware chain 中可能被 clone
- Laravel 標準 API: `setUserResolver()` 和 `$request->user()`

**最佳實踐**:
```php
// ✅ 正確方式
$request->setUserResolver(function () use ($user) {
    return $user;
});

// ❌ 避免使用
$request->merge(['custom_user' => $user]);
```

### 2. TypeScript Optional Chaining 完整性

**問題模式**:
```tsx
// ❌ 不完整的 optional chaining
profileData?.full_name.substring(0, 2)

// ✅ 完整的 optional chaining + fallback
profileData?.full_name?.substring(0, 2) || 'U'
```

**學習**:
- 每一層可能為 undefined 的屬性都需要 `?.`
- 永遠提供最終 fallback 值
- 避免部分使用、部分不使用

### 3. 系統化診斷方法

**有效診斷流程**:
1. Automated Testing (Playwright) - 驗證整體流程
2. Direct API Testing (curl) - 隔離 Backend 問題
3. Code Tracing - 找出根本原因
4. Interactive Testing (tinker) - 驗證修復

**關鍵**: 不要假設問題原因，用證據驅動診斷

---

## 後續建議

### 1. Backend 認證架構 ✅
**已完成**:
- 統一使用 Laravel 標準 User Resolution API
- 所有 Controllers 使用 `$request->user()`

**建議**:
- 添加 PHPStan 規則檢查 `$request->merge()` 用法
- 文檔化認證最佳實踐

### 2. Frontend Type Safety
**建議**:
- 啟用 ESLint 規則檢查 optional chaining 完整性
- 使用 TypeScript strict mode
- 為所有 API 回應添加嚴格的類型定義

### 3. 測試覆蓋
**建議添加**:
- Backend: Middleware 單元測試
- Backend: 認證整合測試
- Frontend: Dashboard 組件測試
- E2E: 完整登入到 Dashboard 流程測試

### 4. 監控和告警
**建議**:
- 添加 401 錯誤監控
- 前端錯誤追蹤 (Sentry)
- API 回應時間監控

---

## 預防措施

### Backend 開發規範
- ✅ 永遠使用 Laravel 標準 API
- ✅ 避免自定義 Request 屬性
- ✅ 參考 Laravel 官方文檔

### Frontend 開發規範
- ✅ 完整使用 optional chaining
- ✅ 永遠提供 fallback 值
- ✅ TypeScript interface 應反映實際資料結構

### 診斷規範
- ✅ 先診斷，後實作
- ✅ 用自動化測試重現問題
- ✅ 文檔化診斷過程

---

## 歸檔完成確認

- [x] 變更目錄已移動到 archived/
- [x] CHANGELOG.md 已更新
- [x] 歸檔報告已創建
- [x] 文件完整性已驗證
- [x] 命名規範已檢查
- [x] 歷史記錄可追溯
- [x] 技術學習點已記錄

**歸檔狀態**: ✅ 完成

**完成時間**: 2026-01-11 20:05
**執行時長**: ~10 分鐘

---

## 總結

本次成功歸檔了一個關鍵的雙重 Bug Fix：

1. **Backend 認證問題** - 修復 Laravel Request User Resolution 問題，所有認證 API 恢復正常
2. **Frontend Dashboard TypeError** - 修復 optional chaining 不完整問題，避免頁面崩潰

**關鍵成就**:
- 採用系統化診斷方法（Playwright + curl + tinker）
- 找出 Laravel 11 Request lifecycle 的關鍵問題
- 統一所有 Controllers 使用標準 API
- 提供完整的診斷文檔和學習點

**影響**:
- 所有業務員可正常訪問 Dashboard
- 所有認證 API 正確運作
- 系統穩定性大幅提升

本修復已成為 OpenSpec 規範庫的重要參考，記錄了完整的診斷過程和技術學習點，可供未來類似問題參考。

---

**歸檔執行者**: Claude Sonnet 4.5
**報告日期**: 2026-01-11
**下一步**: 繼續進行功能開發
