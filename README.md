# YAMU 業務員推廣系統

> 完整的業務員檔案管理與搜尋平台，採用 Laravel 11 + Next.js 架構

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-15-000000?logo=next.js)](https://nextjs.org)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?logo=typescript)](https://www.typescriptlang.org)
[![Tests](https://img.shields.io/badge/Tests-201%20passing-success)](my_profile_laravel/tests)
[![Coverage](https://img.shields.io/badge/Coverage-80%25-success)](my_profile_laravel/tests)

---

## 📋 專案簡介

YAMU 是一個專為業務員設計的個人檔案管理與搜尋平台，提供：

- 🔐 **完整的認證系統** - JWT 雙令牌機制
- 👤 **業務員檔案管理** - 個人資料、公司、證照、經歷
- 🔍 **強大的搜尋功能** - 多條件篩選、地區搜尋
- 🛡️ **三級權限管理** - Admin、Salesperson、User
- ✅ **審核流程** - 完整的申請審核機制
- 📊 **生產級品質** - 201 個測試、80%+ 覆蓋率、PHPStan Level 9

---

## 🏗️ 專案架構

```
my_profile/
├── my_profile_laravel/    # Backend API (Laravel 11)
│   ├── app/              # 應用程式碼
│   ├── tests/            # 201 測試 (80%+ 覆蓋)
│   └── docker/           # Docker 配置
├── frontend/             # Frontend UI (Next.js 15)
│   ├── app/              # App Router 頁面
│   ├── components/       # React 組件
│   └── lib/              # 工具函式
├── openspec/             # OpenSpec 規範庫
│   ├── specs/            # 系統規範文件
│   └── changes/          # 功能變更提案
├── .claude/              # Claude Code 配置
│   ├── commands/         # OpenSpec Commands (/implement, /test, etc.)
│   └── skills/           # 專業技能模組
└── docs/                 # 專案文檔
```

---

## 🚀 快速開始

### 必要條件

- Docker Desktop 或 Docker Engine
- Node.js 18+ (用於前端開發)
- Git

### 啟動 Backend API

```bash
# 1. 進入 Laravel 專案目錄
cd my_profile_laravel

# 2. 啟動 Docker 容器
docker-compose up -d

# 3. 執行資料庫遷移
docker exec -it my_profile_laravel_app php artisan migrate

# 4. 執行資料種子
docker exec -it my_profile_laravel_app php artisan db:seed

# 5. 測試 API
curl http://localhost:8080/api/health
```

### 啟動 Frontend (開發中)

```bash
# 1. 進入前端專案目錄
cd frontend

# 2. 安裝依賴
npm install

# 3. 啟動開發伺服器
npm run dev
```

---

## 📊 技術棧

### Backend (Laravel 11)

| 技術 | 版本 | 用途 |
|------|------|------|
| **Laravel** | 11.x | PHP Web Framework |
| **PHP** | 8.4 | 程式語言 |
| **MySQL** | 8.0 | 資料庫 |
| **JWT** | - | 身份認證 |
| **PHPStan** | Level 9 | 靜態分析 |
| **Pest** | 3.x | 測試框架 |
| **Swagger** | OpenAPI 3.0 | API 文檔 |
| **Docker** | - | 容器化部署 |

### Frontend (Next.js 15)

| 技術 | 版本 | 用途 |
|------|------|------|
| **Next.js** | 15.x | React Framework |
| **React** | 19.x | UI 函式庫 |
| **TypeScript** | 5.x | 型別安全 |
| **Tailwind CSS** | 3.x | CSS Framework |
| **Radix UI** | - | UI 組件 |
| **shadcn/ui** | - | UI 組件庫 |

---

## 🌐 服務存取點

| 服務 | URL | 說明 |
|------|-----|------|
| **Laravel API** | http://localhost:8080 | RESTful API 端點 |
| **Swagger UI** | http://localhost:8080/api/docs | 互動式 API 文檔 |
| **phpMyAdmin** | http://localhost:8081 | 資料庫管理介面 |
| **Next.js** | http://localhost:3001 | 前端應用程式 |

**資料庫連線**:
- Host: `localhost:3306`
- Database: `my_profile_laravel`
- Username: `root` / `laravel`
- Password: `123456`

---

## 📡 API 規範

### Swagger 互動式文檔

本專案提供完整的 Swagger/OpenAPI 3.0 文檔：

- **Swagger UI**: http://localhost:8080/api/docs
  - 互動式 API 測試介面
  - 即時試用所有 API 端點
  - JWT 認證整合
  - 完整的請求/回應範例

- **OpenAPI JSON**: http://localhost:8080/api/docs/openapi.json
  - 標準 OpenAPI 3.0 規範
  - 可用於生成客戶端 SDK
  - 支援 Postman、Insomnia 匯入

**特性**:
- ✅ 自動從 Controller 註解生成
- ✅ 即時更新 (開發環境)
- ✅ 僅在開發環境可見 (安全性)
- ✅ 完整的認證流程說明
- ✅ 詳細的錯誤回應定義

### API 端點總覽

**總計: 37 個端點**

```
認證 (Authentication) - 6 個端點
├── POST   /api/auth/register              # 一般用戶註冊
├── POST   /api/auth/register-salesperson  # 業務員註冊
├── POST   /api/auth/login                 # 用戶登入
├── POST   /api/auth/refresh               # 刷新令牌
├── POST   /api/auth/logout                # 用戶登出
└── GET    /api/auth/me                    # 取得當前用戶

搜尋 (Search) - 2 個端點
├── GET    /api/search/salespersons        # 搜尋業務員
└── GET    /api/search/salespersons/{id}   # 取得業務員詳情

業務員管理 (Salesperson) - 11 個端點
├── GET    /api/salesperson/profile        # 取得個人檔案
├── PUT    /api/salesperson/profile        # 更新個人檔案
├── POST   /api/salesperson/upgrade        # 升級為業務員
├── GET    /api/salesperson/status         # 取得審核狀態
├── POST   /api/salesperson/company        # 提交公司資訊
├── GET    /api/salesperson/experiences    # 取得工作經歷
├── POST   /api/salesperson/experiences    # 新增工作經歷
├── PUT    /api/salesperson/experiences/{id} # 更新工作經歷
├── DELETE /api/salesperson/experiences/{id} # 刪除工作經歷
├── GET    /api/salesperson/certifications # 取得證照列表
├── POST   /api/salesperson/certifications # 上傳證照
└── DELETE /api/salesperson/certifications/{id} # 刪除證照

公司管理 (Companies) - 2 個端點
├── GET    /api/companies/search           # 搜尋公司
└── POST   /api/companies                  # 建立公司

管理員 (Admin) - 14 個端點
├── GET    /api/admin/pending-approvals    # 待審核項目
├── POST   /api/admin/approve-user/{id}    # 核准用戶註冊
├── POST   /api/admin/reject-user/{id}     # 拒絕用戶註冊
├── POST   /api/admin/approve-company/{id} # 核准公司
├── POST   /api/admin/reject-company/{id}  # 拒絕公司
├── POST   /api/admin/approve-certification/{id} # 核准證照
├── POST   /api/admin/reject-certification/{id}  # 拒絕證照
├── GET    /api/admin/salesperson-applications  # 業務員申請列表
├── POST   /api/admin/salesperson-applications/{id}/approve # 核准業務員
├── POST   /api/admin/salesperson-applications/{id}/reject  # 拒絕業務員
├── GET    /api/admin/users                # 用戶列表
├── PUT    /api/admin/users/{id}/status    # 更新用戶狀態
├── DELETE /api/admin/users/{id}           # 刪除用戶
└── GET    /api/admin/statistics           # 平台統計資料

API 文檔 (Swagger) - 2 個端點
├── GET    /api/docs                       # Swagger UI 介面
└── GET    /api/docs/openapi.json          # OpenAPI 3.0 規範
```

---

## 🧪 測試

### Backend 測試

```bash
# 執行所有測試
docker exec -it my_profile_laravel_app php artisan test

# 執行特定測試套件
docker exec -it my_profile_laravel_app php artisan test --testsuite=Feature
docker exec -it my_profile_laravel_app php artisan test --testsuite=Unit

# 產生測試覆蓋率報告
docker exec -it my_profile_laravel_app php artisan test --coverage
```

**測試統計**:
- ✅ 201 測試全部通過
- 🎯 80%+ 代碼覆蓋率
- 📦 165 Feature Tests (API 整合)
- 🔧 36 Unit Tests (Service Layer)

### Frontend 測試 (開發中)

```bash
cd frontend
npm run test
```

---

## 📚 開發方法

### OpenSpec 規範驅動開發 (SDD)

本專案採用 **Specification-Driven Development** 方法：

1. **規範先行** - 先撰寫完整規格，後寫程式碼
2. **規範即文檔** - 規格文件同時是 API 文檔和開發指南
3. **變更追蹤** - 所有功能變更都有完整的提案和任務拆解

```bash
# 開發新功能流程
/implement [功能描述]

# 流程自動執行:
# 1. Create Proposal  → openspec/changes/<feature>/proposal.md
# 2. Write Specs      → openspec/changes/<feature>/specs/
# 3. Break Down Tasks → openspec/changes/<feature>/tasks.md
# 4. Validate Specs   → 驗證規格完整性
# 5. Implement        → 嚴格按照規格實作
# 6. Archive          → 歸檔到 openspec/specs/
```

**OpenSpec 目錄結構**:
```
openspec/
├── specs/                    # 當前系統完整規範 (Single Source of Truth)
│   ├── api/endpoints.md      # API 端點規範 (31 endpoints)
│   ├── models/data-models.md # 資料模型規範 (8 tables)
│   └── architecture/         # 系統架構設計
└── changes/                  # 功能變更提案
    ├── active/               # 進行中的變更
    └── archived/             # 已完成的變更
```

---

## 🗄️ 資料庫架構

### 核心資料表

| 資料表 | 說明 | 記錄數 |
|-------|------|-------|
| `users` | 用戶基本資訊 (含角色與審核狀態) | - |
| `salesperson_profiles` | 業務員詳細檔案 | - |
| `companies` | 公司資訊 (簡化版) | - |
| `industries` | 產業類別 | 10 |
| `regions` | 地區資料 | 368 |
| `certifications` | 證照資料 | - |
| `experiences` | 工作經歷 | - |
| `approval_logs` | 審核記錄 | - |

### 重要 Schema 變更 (2026-01-11)

**`users` 表新增欄位** - 業務員審核機制重構:
```sql
role ENUM('user', 'salesperson', 'admin')           -- 用戶角色
salesperson_status ENUM('pending', 'approved', 'rejected')  -- 審核狀態
salesperson_applied_at TIMESTAMP                    -- 申請時間
salesperson_approved_at TIMESTAMP                   -- 核准時間
rejection_reason TEXT                               -- 拒絕原因
can_reapply_at TIMESTAMP                            -- 可重新申請時間
is_paid_member BOOLEAN                              -- 付費會員標記
```

**`companies` 表簡化**:
```sql
移除: industry_id, address, phone, approval_status, approved_by
新增: is_personal BOOLEAN  -- 支援個人工作室
變更: tax_id 改為 nullable  -- 個人工作室無統編
```

**`salesperson_profiles` 表簡化**:
```sql
移除: approval_status, rejected_reason, approved_by, approved_at
原因: 審核狀態統一由 users 表管理
```

完整的資料模型文件: [openspec/specs/models/data-models.md](openspec/specs/models/data-models.md)

---

## 🔐 認證與授權

### JWT 雙令牌機制

```
登入流程:
1. POST /api/auth/login
   → 返回 access_token (60分鐘) 和 refresh_token (7天)

2. 使用 access_token 存取受保護的 API
   → Header: Authorization: Bearer <access_token>

3. access_token 過期時使用 refresh_token 更新
   → POST /api/auth/refresh

4. 登出時清除令牌
   → POST /api/auth/logout
```

### 角色與權限

| 角色 | 權限 |
|------|------|
| **Admin** | 完整系統管理權限、審核公司和業務員 |
| **Salesperson** | 管理個人檔案、公司資料、查看業務員列表 |
| **User** | 查看公開業務員檔案、搜尋功能 |

---

## 📖 文檔資源

### Backend 文檔

| 文檔 | 說明 |
|------|------|
| [README_LARAVEL.md](my_profile_laravel/README.md) | Laravel 專案說明 |
| [MIGRATION_SUMMARY.md](my_profile_laravel/MIGRATION_SUMMARY.md) | 遷移完成報告 |
| [MODULE_07_COMPLETION.md](my_profile_laravel/MODULE_07_COMPLETION.md) | 部署配置文檔 |
| [SWAGGER_IMPLEMENTATION.md](my_profile_laravel/SWAGGER_IMPLEMENTATION.md) | Swagger 實作報告 |

### Frontend 文檔

| 文檔 | 說明 |
|------|------|
| [CLAUDE.md](frontend/CLAUDE.md) | 前端開發指南 |
| [README.md](frontend/README.md) | Next.js 專案說明 |

### OpenSpec 規範

| 文檔 | 說明 |
|------|------|
| [API Endpoints](openspec/specs/api/endpoints.md) | 31 個 API 端點規範 |
| [Data Models](openspec/specs/models/data-models.md) | 8 個資料表定義 |
| [Architecture](openspec/specs/architecture/overview.md) | 系統架構設計 |

---

## 🚢 部署

### 本地開發環境

```bash
# 使用 Docker Compose
docker-compose up -d

# 服務自動啟動：
# - Laravel API (port 8080)
# - MySQL (port 3306)
# - phpMyAdmin (port 8081)
```

### 生產環境部署

完整的部署指南請參考: [MODULE_07_COMPLETION.md](my_profile_laravel/MODULE_07_COMPLETION.md)

**部署特性**:
- ✅ Multi-stage Docker 建置
- ✅ 生產級 Nginx 配置
- ✅ CI/CD Pipeline (GitHub Actions)
- ✅ Blue-Green 部署策略
- ✅ Health Checks
- ✅ 自動化測試

---

## 📊 專案狀態

### ✅ 已完成 (v1.0.0)

**Backend (Laravel 11)**:
- ✅ Module 01: 專案設置與 Docker 環境
- ✅ Module 02: 資料庫層與 Eloquent Models
- ✅ Module 03: JWT 認證系統
- ✅ Module 04: 業務邏輯 APIs (31 endpoints)
- ✅ Module 05: 參考數據 APIs
- ✅ Module 06: 綜合測試套件 (201 tests)
- ✅ Module 07: 生產環境部署配置
- ✅ Swagger/OpenAPI 3.0 完整文檔

**Frontend (Next.js 15)**:
- ✅ 專案架構設置
- ✅ UI 組件系統 (shadcn/ui)
- ✅ 18 個頁面完整實作
- ✅ 30+ 個 React 組件開發
- ✅ 31 個 API 端點整合
- ✅ JWT 認證流程與路由守衛
- ✅ React Query + Zustand 狀態管理
- ✅ Recharts 圖表整合
- ✅ TypeScript 嚴格模式 (0 錯誤)
- ✅ 響應式設計 (Mobile/Tablet/Desktop)

**完整功能模組**:
- ✅ 公開頁面: 首頁、搜尋、業務員詳情
- ✅ 認證系統: 登入、註冊、密碼管理
- ✅ 業務員 Dashboard: 個人檔案、經歷、證照管理
- ✅ 管理員後台: 審核管理、用戶管理、統計儀表板

### 🔄 進行中

**Frontend Testing**:
- 🔄 E2E 測試 (Playwright)
- 🔄 跨瀏覽器相容性測試
- 🔄 可訪問性測試 (WCAG AA)

### 📋 計劃中

**Phase 2 Features**:
- 📋 業務員評分與評論系統
- 📋 訊息通知功能
- 📋 進階搜尋與篩選
- 📋 行動裝置優化

---

## 🤝 貢獻指南

### 開發流程

1. **了解 OpenSpec SDD** - 閱讀 [OpenSpec 規範驅動開發](openspec/README.md)
2. **建立變更提案** - 使用 `/implement [功能描述]`
3. **撰寫完整規格** - API + 資料模型 + 業務規則
4. **實作與測試** - 嚴格遵循規格
5. **提交 Pull Request** - 包含測試和文檔

### 代碼品質標準

**Backend (Laravel)**:
- ✅ PHPStan Level 9 通過
- ✅ 所有測試通過
- ✅ 80%+ 測試覆蓋率
- ✅ Strict types 宣告

**Frontend (Next.js)**:
- ✅ TypeScript 無錯誤
- ✅ ESLint 規則通過
- ✅ 組件測試覆蓋

---

## 📜 版本歷史

### v1.0.0 (2026-01-10)

**重大里程碑**: CodeIgniter 4 → Laravel 11 遷移完成

- ✅ 完整遷移 31 個 API 端點
- ✅ 100% API 向後相容
- ✅ 201 個測試，80%+ 覆蓋率
- ✅ PHPStan Level 9 合規
- ✅ Swagger/OpenAPI 3.0 文檔
- ✅ 生產級 Docker 部署
- ✅ CI/CD Pipeline 設置

詳細變更: [MIGRATION_SUMMARY.md](my_profile_laravel/MIGRATION_SUMMARY.md)

---

## 📄 授權

此專案為個人作品集專案。

---

## 👤 維護者

**Kai Tsai**
- GitHub: [@kai890707](https://github.com/kai890707)

---

## 🔗 相關連結

- [Laravel Documentation](https://laravel.com/docs)
- [Next.js Documentation](https://nextjs.org/docs)
- [OpenAPI Specification](https://swagger.io/specification/)
- [OpenSpec SDD Methodology](openspec/README.md)

---

**最後更新**: 2026-01-10 | **版本**: 1.0.0
