# Proposal: Swagger API Documentation

**Feature Name**: swagger-api-documentation

**Date**: 2026-01-08

**Status**: Proposal

---

## Why (問題陳述)

### Current Problem

專案目前缺乏完整的 API 文件，開發者需要：
- 手動閱讀 Controller 原始碼才能了解 API 規格
- 無法快速查看 Request/Response 格式
- 測試 API 需要使用 Postman 或 curl 手動建立請求
- 前後端協作時容易產生理解落差

### Impact

- 新成員加入時學習曲線陡峭
- API 變更時難以追蹤影響範圍
- 測試 API 效率低落
- 文件與程式碼容易不同步

---

## What (解決方案)

### Core Value

建立基於 OpenAPI 3.0 規範的 Swagger API 文件系統，提供：

1. **互動式 API 文件** - 自動生成的 Swagger UI 介面
2. **完整的 API 規格** - 涵蓋所有現有 API 端點
3. **環境控制** - 僅在開發環境可見，生產環境自動隱藏
4. **JWT 認證支援** - 可在 Swagger UI 中輸入 Token 進行測試

---

## Scope

### In Scope (本次實作)

✅ **基礎設定**
- 安裝 Swagger PHP 套件（zircote/swagger-php）
- 設定 Swagger UI 路由和 Controller
- 配置環境變數控制可見性

✅ **API 文件撰寫**
- 認證模組 API（AuthController）
  - POST /api/auth/register - 業務員註冊
  - POST /api/auth/login - 登入
  - POST /api/auth/refresh - 刷新 Token
  - POST /api/auth/logout - 登出
  - GET /api/auth/me - 取得當前用戶資訊

- 搜尋模組 API（SearchController）
  - GET /api/search/salespersons - 搜尋業務員
  - GET /api/search/salespersons/{id} - 查看業務員詳細資訊

- 業務員模組 API（SalespersonController）
  - GET /api/salesperson/profile - 取得個人檔案
  - PUT /api/salesperson/profile - 更新個人檔案
  - POST /api/salesperson/company - 儲存公司資訊
  - GET /api/salesperson/experiences - 取得工作經驗清單
  - POST /api/salesperson/experiences - 新增工作經驗
  - DELETE /api/salesperson/experiences/{id} - 刪除工作經驗
  - GET /api/salesperson/certifications - 取得證照清單
  - POST /api/salesperson/certifications - 新增證照
  - GET /api/salesperson/approval-status - 查詢審核狀態

- 管理員模組 API（AdminController）
  - GET /api/admin/pending-approvals - 取得待審核清單
  - POST /api/admin/approve-user/{id} - 核准使用者
  - POST /api/admin/reject-user/{id} - 拒絕使用者
  - POST /api/admin/approve-company/{id} - 核准公司
  - POST /api/admin/reject-company/{id} - 拒絕公司
  - POST /api/admin/approve-certification/{id} - 核准證照
  - POST /api/admin/reject-certification/{id} - 拒絕證照
  - GET /api/admin/users - 取得使用者清單
  - PUT /api/admin/users/{id}/status - 更新使用者狀態
  - DELETE /api/admin/users/{id} - 刪除使用者
  - GET /api/admin/settings/industries - 取得產業清單
  - POST /api/admin/settings/industries - 新增產業
  - GET /api/admin/settings/regions - 取得地區清單
  - POST /api/admin/settings/regions - 新增地區
  - GET /api/admin/statistics - 取得統計資料

✅ **JWT 認證整合**
- 在 Swagger UI 中配置 Bearer Token 輸入
- 定義 Security Schemes
- 標記需要認證的端點

✅ **環境控制**
- 開發環境（CI_ENVIRONMENT=development）顯示 Swagger UI
- 生產環境（CI_ENVIRONMENT=production）自動隱藏，返回 404

✅ **基本範例和 Schema**
- 定義常用的 Request/Response Schema
- 標準錯誤格式（400, 401, 403, 404, 422, 500）
- JWT Token 格式

---

### Out of Scope (本次不做)

❌ **自動化測試生成** - 不自動生成 API 測試程式碼

❌ **API 版本控制** - 不處理 API v1/v2 版本管理

❌ **Mock Server** - 不建立 Mock API Server

❌ **Postman Collection 匯出** - 不提供 Postman Collection 匯出功能

❌ **多語系文件** - 文件僅使用繁體中文

❌ **API 效能監控** - 不整合 API 效能追蹤

❌ **自動化文件部署** - 不設定 CI/CD 自動部署文件

---

## Success Criteria (驗收標準)

### 功能驗收

- [ ] **Swagger UI 可存取**: 開發環境訪問 `http://localhost:8080/api/docs` 可看到 Swagger UI
- [ ] **環境控制生效**: 生產環境訪問 `/api/docs` 返回 404
- [ ] **完整 API 文件**: 所有 32 個 API 端點都已記錄
- [ ] **Schema 定義完整**: 所有 Request Body 和 Response 都有 Schema 定義
- [ ] **JWT 認證可用**: 可以在 Swagger UI 的 "Authorize" 按鈕輸入 JWT Token
- [ ] **Try it out 功能**: 可以直接在 Swagger UI 測試 API 並收到正確回應
- [ ] **錯誤格式正確**: 所有 API 的錯誤回應格式一致且有文件說明

### 技術驗收

- [ ] **無語法錯誤**: Swagger JSON/YAML 可以正確解析
- [ ] **符合 OpenAPI 3.0**: 規格符合 OpenAPI 3.0 標準
- [ ] **註解位置正確**: Swagger 註解在對應的 Controller 方法上方
- [ ] **無安全漏洞**: Swagger UI 未暴露敏感資訊（如資料庫連線字串）

### 文件品質

- [ ] **描述清晰**: 每個 API 端點都有清楚的中文說明
- [ ] **參數完整**: 所有必填/選填參數都有標記
- [ ] **範例完整**: 關鍵 API 都有 Request/Response 範例
- [ ] **錯誤說明完整**: 各種錯誤情況都有說明

---

## Technical Approach

### Implementation Method

使用 **PHP Annotations** 方式：
- 在 Controller 方法上方加入 `@OA\*` 註解
- 使用 `zircote/swagger-php` 掃描註解生成 OpenAPI JSON
- 優點：程式碼和文件在同一處，易於維護

### Technology Stack

- **swagger-php**: ^4.x - PHP OpenAPI/Swagger 註解解析器
- **Swagger UI**: 透過 CDN 引入前端介面
- **OpenAPI**: 3.0.0 規範

### Architecture

```
/api/docs
    ↓
SwaggerController::index()
    ↓
檢查環境 (CI_ENVIRONMENT)
    ↓
development → 顯示 Swagger UI (HTML + CDN)
production  → 返回 404

/api/docs/openapi.json
    ↓
SwaggerController::json()
    ↓
掃描 Controllers 目錄 @OA 註解
    ↓
生成 OpenAPI JSON
    ↓
返回 JSON 給 Swagger UI
```

---

## Risks & Mitigation

### Risk 1: 文件與程式碼不同步

**風險**: API 修改後忘記更新 Swagger 註解

**緩解**:
- 將 Swagger 註解直接寫在 Controller 方法上，與程式碼綁定
- 在 Code Review 時檢查 API 變更是否更新文件
- 未來可考慮增加 CI 檢查 Swagger JSON 是否可正常生成

### Risk 2: 效能影響

**風險**: 每次請求都掃描 Controller 註解可能影響效能

**緩解**:
- 生產環境關閉 Swagger UI，不執行掃描
- 開發環境可接受稍慢的文件載入時間
- 未來可考慮將生成的 JSON 快取起來

### Risk 3: 安全性

**風險**: Swagger UI 可能暴露 API 內部實作細節

**緩解**:
- 僅在開發環境啟用
- 生產環境完全隱藏（404）
- 不在 Swagger 註解中包含敏感資訊（密碼、金鑰等）

---

## Dependencies

### 前置條件

- ✅ CodeIgniter 4.6.4 已安裝
- ✅ Composer 可用
- ✅ JWT 認證已實作

### 相依套件

- `zircote/swagger-php: ^4.0` - 透過 Composer 安裝

---

## Estimated Effort

**總預估時間**: 4-5 小時

- 環境設定與套件安裝: 30 分鐘
- SwaggerController 開發: 45 分鐘
- 認證模組文件 (5 API): 45 分鐘
- 搜尋模組文件 (2 API): 20 分鐘
- 業務員模組文件 (9 API): 60 分鐘
- 管理員模組文件 (16 API): 90 分鐘
- 測試與調整: 30 分鐘

---

## Alternatives Considered

### Option 1: 手動撰寫 YAML 檔案

❌ **不採用原因**:
- 文件與程式碼分離，容易不同步
- 修改 API 需要同時修改兩處

### Option 2: 使用 API Blueprint 或 RAML

❌ **不採用原因**:
- OpenAPI/Swagger 生態系更成熟
- Swagger UI 工具更普及
- PHP 套件支援度較高

### Option 3: 僅提供 Postman Collection

❌ **不採用原因**:
- 需要額外工具（Postman）
- 無法在瀏覽器直接查看
- 不符合業界標準（OpenAPI）

---

## References

- OpenAPI 3.0 Specification: https://swagger.io/specification/
- swagger-php Documentation: https://zircote.github.io/swagger-php/
- Swagger UI: https://swagger.io/tools/swagger-ui/

---

## Approval

**Proposed By**: AI Assistant

**Date**: 2026-01-08

**Status**: ⏳ Waiting for User Approval

---

## Next Steps

1. ✅ User reviews and approves this proposal
2. → Write detailed specifications (API, Data Model, Business Rules)
3. → Break down implementation tasks
4. → Validate specifications
5. → Implement
6. → Archive to spec library
