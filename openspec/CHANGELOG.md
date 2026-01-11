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

---
