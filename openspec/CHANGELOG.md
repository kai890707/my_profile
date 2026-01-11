# OpenSpec Changelog

記錄所有歸檔到規範庫的功能變更

---

## [2026-01-11]

### Added
- **User Registration Refactor** (user-registration-refactor)
  - 實作雙層級使用者系統（一般使用者 + 業務員）
  - 新增 2 個註冊 API 端點 (POST /api/auth/register/user, POST /api/auth/register/salesperson)
  - 新增業務員升級功能 (POST /api/salesperson/upgrade)
  - 新增業務員審核功能 (PATCH /api/admin/salesperson/{id}/approve, reject)
  - 資料庫 Schema 更新：users 表新增 role, salesperson_status 等欄位
  - companies 表簡化，移除使用者專屬欄位
  - salesperson_profiles 表的 company_id 改為可空
  - 70+ 測試案例，覆蓋所有註冊情境
  - Frontend 註冊頁面重構，支援帳號類型選擇

### Fixed
- **CORS Issue** (20260111-fix-cors-issue)
  - 修復 Frontend 呼叫 Backend API 的 CORS 錯誤
  - 問題：Frontend 實際運行在 port 3002，但 Backend CORS 配置僅允許 port 3001
  - 解決方案：終止 port 3002 進程，重新啟動 Frontend 在正確的 port 3001
  - 驗證：CORS 預檢請求和實際 API 請求均成功

- **Header Component TypeError** (20260111-fix-header-typeerror)
  - 修復 Header 組件中 `user.email.substring()` 缺少 optional chaining 導致的 TypeError
  - 問題：當 `user.email` 為 undefined 時拋出 "Cannot read properties of undefined" 錯誤
  - 解決方案：
    - 添加 optional chaining: `user.email?.substring(0, 2)`
    - 提供 fallback 值: Avatar initials 使用 'U'，使用者名稱使用 '使用者'
    - 修正 TypeScript 介面: `email: string` → `email?: string`
  - 驗證：TypeScript 類型檢查通過，運行時無錯誤

- **API 401 Authentication & Dashboard TypeError** (20260111-fix-api-401-unauthorized)
  - 修復 Backend Middleware 認證問題，API 不再返回 401 錯誤
  - 修復 Frontend Dashboard Avatar TypeError
  - 問題 1: Middleware 使用非標準方式 `$request->merge(['auth_user' => ...])` 設定認證用戶
    - 在 Laravel 11 中，`merge()` 添加的自定義屬性不會在 Request lifecycle 中持久化
    - Controller 使用 `$request->get('auth_user')` 獲取 null，導致 401 錯誤
  - 問題 2: Dashboard Avatar 缺少完整的 optional chaining
    - `profileData?.full_name.substring()` 在 `full_name` 為 undefined 時拋出 TypeError
  - 解決方案 1: 改用 Laravel 標準 API
    - Middleware: `$request->setUserResolver()` + `auth()->setUser()`
    - Controllers: 從 `$request->get('auth_user')` 改為 `$request->user()`
  - 解決方案 2: 添加完整 optional chaining 和 fallback
    - `profileData?.full_name?.substring(0, 2) || 'U'`
  - 影響範圍: 4 個 Backend 檔案 (1 middleware, 3 controllers) + 1 個 Frontend 檔案 (2 locations)
  - 測試驗證:
    - ✅ Backend API 正確返回 200 OK (有 profile) 或 404 (無 profile)，不再返回 401
    - ✅ Frontend Dashboard 無 TypeError，Avatar 正常顯示

---
