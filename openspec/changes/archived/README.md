# Archived OpenSpec Changes

此目錄包含已完成並歸檔的 OpenSpec 功能開發記錄。

**路徑**: `openspec/changes/archived/`

---

## 命名規範

從 2026-01-11 起，所有 spec 資料夾遵循新的命名規範：

```
YYYYMMDD-action-description
```

**範例**: `20260111-fix-frontend-backend-api-inconsistency`

**詳細規範**: 請參閱 [`openspec/NAMING-CONVENTIONS.md`](../../NAMING-CONVENTIONS.md)

---

## 歸檔流程

當一個功能開發完成後，會經過以下歸檔流程：

1. **提取重點** - 從 `changes/YYYYMMDD-action-description/specs/` 提取關鍵規格
2. **更新主規格** - 將新規格整合到 `openspec/specs/` 相應文件中
3. **移動到 archived** - 將完整的 change 目錄從 `changes/` 移至 `changes/archived/`
4. **重新命名** - 確保資料夾名稱符合 `YYYYMMDD-action-description` 格式

---

## 歸檔目錄結構

每個歸檔的功能包含：
- `proposal.md` - 原始需求提案
- `specs/` - 完整技術規格
  - `api.md` - API 端點規格
  - `data-model.md` - 資料模型
  - `business-rules.md` - 業務規則
  - `tests.md` - 測試規格
- `tasks.md` - 開發任務追蹤
- `COMPLETION-SUMMARY.md` - 完成總結報告

---

## 已歸檔功能

### 20260111-fix-frontend-backend-api-inconsistency

**日期**: 2026-01-11
**類型**: fix (修復)
**功能**: 修復前後端 API 不一致問題
**狀態**: ✅ 完成並歸檔

**實現內容**:
- 新增 Experience CRUD API (5 個端點)
- 新增 Certification CRUD API (5 個端點)
- 修正 Frontend ApiResponse 介面 (`status` → `success`)
- 更新 approval-status 端點回應格式

**整合到主規格**:
- `specs/api/endpoints.md` - 新增 11 個 Salesperson API 端點
- `specs/frontend/api-integration.md` - 修正 ApiResponse 型別定義

**測試結果**:
- Backend: 278 tests passed, 28 skipped
- Frontend: Type-safe API integration
- 新增測試: 51 tests (41 Feature + 10 Unit)

**詳細資訊**: [./20260111-fix-frontend-backend-api-inconsistency/COMPLETION-SUMMARY.md](./20260111-fix-frontend-backend-api-inconsistency/COMPLETION-SUMMARY.md)

---

### 20260110-refactor-user-registration

**日期**: 2026-01-10
**類型**: refactor (重構)
**功能**: 重構使用者註冊流程

**實現內容**:
- 重構註冊邏輯，分離業務員與一般用戶註冊
- 新增業務員升級機制
- 實作重新申請冷卻期
- 完善審核流程

**詳細資訊**: [./20260110-refactor-user-registration/COMPLETION-SUMMARY.md](./20260110-refactor-user-registration/COMPLETION-SUMMARY.md)

---

### 20260108-add-swagger-api-documentation

**日期**: 2026-01-08
**類型**: add (新增)
**功能**: 新增 Swagger API 文檔

**實現內容**:
- 整合 Swagger UI
- 自動生成 OpenAPI 3.0 規格
- 提供互動式 API 文檔介面

**詳細資訊**: [./20260108-add-swagger-api-documentation/COMPLETION-SUMMARY.md](./20260108-add-swagger-api-documentation/COMPLETION-SUMMARY.md)

---

### 20260108-add-frontend-spa-project

**日期**: 2026-01-08
**類型**: add (新增)
**功能**: 新增 Frontend SPA 專案

**實現內容**:
- 建立 Next.js 15 前端專案
- 實作基礎 UI 組件
- 整合 API 客戶端
- 實作認證流程

**詳細資訊**: [./20260108-add-frontend-spa-project/tasks.md](./20260108-add-frontend-spa-project/tasks.md)

---

## 查詢歸檔功能

```bash
# 查看所有歸檔功能 (按日期排序)
ls -1 openspec/changes/archived/ | grep "^[0-9]" | sort

# 查看特定功能的完成報告
cat openspec/changes/archived/YYYYMMDD-action-description/COMPLETION-SUMMARY.md

# 查看特定功能的規格
ls openspec/changes/archived/YYYYMMDD-action-description/specs/

# 搜尋特定類型的變更
ls -1 openspec/changes/archived/ | grep "add"      # 新增功能
ls -1 openspec/changes/archived/ | grep "fix"      # 修復問題
ls -1 openspec/changes/archived/ | grep "refactor" # 重構
```

---

## 統計資訊

**總歸檔數**: 4
- **add** (新增): 2
- **fix** (修復): 1
- **refactor** (重構): 1

**最新歸檔**: 20260111-fix-frontend-backend-api-inconsistency
**最早歸檔**: 20260108-add-frontend-spa-project

---

**維護者**: Development Team
**最後更新**: 2026-01-11
**命名規範版本**: 1.0
