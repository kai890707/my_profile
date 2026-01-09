# Backend 開發規範 (CodeIgniter 4)

**專案**: YAMU Backend API
**框架**: CodeIgniter 4.6.4 + MySQL 8.0
**開發方法**: OpenSpec Specification-Driven Development (SDD)
**最後更新**: 2026-01-09

---

## 🚀 快速開始

### 使用 OpenSpec Commands 開發新功能

```bash
# 在專案根目錄執行
/implement [功能描述]
```

這會自動執行完整的 SDD 流程：
1. Create Proposal → 確認需求
2. Write Specs → API + Data Model + Business Rules
3. Break Down Tasks → 拆解任務
4. Validate → 驗證規格
5. Implement → 實作程式碼
6. Archive → 歸檔到規範庫

**Commands 參考**: `../../.claude/commands/README.md`

---

## 📁 專案結構

```
my_profile_ci4/
├── app/
│   ├── Controllers/Api/       # API 控制器
│   ├── Models/                # 資料模型
│   ├── Database/Migrations/   # 資料庫遷移
│   └── Filters/               # 中間件 (認證, 權限)
├── openspec/                  # OpenSpec 規範 (於專案根目錄)
│   ├── specs/                 # 當前系統規範
│   └── changes/               # 功能變更提案
├── DEVELOPMENT.md             # 詳細開發指南
└── CLAUDE.md                  # 本文件
```

---

## 🛠️ 技術棧

### Core
- **Framework**: CodeIgniter 4.6.4
- **PHP**: 8.1+
- **Database**: MySQL 8.0
- **Authentication**: JWT (firebase/php-jwt)

### Deployment
- **Container**: Docker + Docker Compose
- **Web Server**: Apache (內建於 Docker)
- **Database Admin**: phpMyAdmin

---

## 📊 系統規格

完整的系統規格請參考 OpenSpec 規範庫:

- **API 端點**: `../../openspec/specs/api/endpoints.md` (35 個端點)
- **資料模型**: `../../openspec/specs/models/data-models.md` (8 個資料表)
- **架構設計**: `../../openspec/specs/architecture/overview.md`
- **業務規則**: `../../openspec/specs/business-rules.md`

---

## 🔧 開發流程

### 1. 環境設置

```bash
# 啟動 Docker 容器
docker-compose up -d

# 執行 Migration
docker exec -it my_profile_ci4-app-1 php spark migrate

# 執行 Seeder
docker exec -it my_profile_ci4-app-1 php spark db:seed SystemDataSeeder

# 測試 API
curl http://localhost:8080/api/industries
```

### 2. 開發新功能

**推薦方式** - 使用 OpenSpec Commands:

```bash
cd /path/to/project/root
/implement 新增業務員評分功能
```

**手動方式** - 按步驟執行:

1. **建立變更提案**
   ```bash
   /proposal 新增業務員評分功能
   ```
   產出: `openspec/changes/rating-feature/proposal.md`

2. **撰寫詳細規格**
   ```bash
   /spec rating-feature
   ```
   產出: `openspec/changes/rating-feature/specs/`
   - `api.md` - API 端點規格
   - `data-model.md` - 資料模型 + Migration 程式碼
   - `business-rules.md` - 業務規則

3. **實作功能**
   ```bash
   /develop rating-feature
   ```
   - 讀取規格中的 Migration 程式碼
   - 建立 Model, Controller
   - 實作業務邏輯
   - 執行測試

4. **歸檔規格**
   ```bash
   /archive rating-feature
   ```
   - 合併規格到 `openspec/specs/`
   - 移動變更到 `openspec/changes/archived/`

---

## 📝 開發規範

### API 設計原則

1. **RESTful API**
   - GET: 查詢資源
   - POST: 建立資源
   - PUT: 完整更新資源
   - PATCH: 部分更新資源
   - DELETE: 刪除資源

2. **統一回應格式**
   ```json
   {
     "status": "success|error",
     "message": "操作成功",
     "data": {...}
   }
   ```

3. **錯誤處理**
   - 400: Bad Request (驗證失敗)
   - 401: Unauthorized (未登入)
   - 403: Forbidden (權限不足)
   - 404: Not Found (資源不存在)
   - 422: Unprocessable Entity (業務規則違反)
   - 500: Internal Server Error (伺服器錯誤)

### 資料庫設計原則

1. **命名規範**
   - 表名: 複數小寫蛇形 (`users`, `salesperson_profiles`)
   - 欄位: 小寫蛇形 (`created_at`, `is_active`)

2. **必要欄位**
   - `id`: BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
   - `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   - `updated_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

3. **軟刪除**
   - 使用 `deleted_at` TIMESTAMP NULL

### 代碼規範

1. **命名規範**
   - Controller: PascalCase + `Controller` 後綴
   - Model: PascalCase + `Model` 後綴
   - 方法: camelCase

2. **注釋要求**
   - 每個 public 方法都要有 PHPDoc
   - 複雜邏輯要加註釋
   - API 端點要註明用途

3. **安全性**
   - 所有輸入都要驗證
   - SQL 使用 Query Builder (防注入)
   - 密碼使用 `password_hash()`
   - 敏感資料加密儲存

---

## 🧪 測試策略

### 測試類型

1. **API 端點測試**
   ```bash
   # 使用測試腳本
   ./scripts/test-api.sh
   ```

2. **單元測試** (建議)
   ```bash
   vendor/bin/phpunit
   ```

3. **整合測試** (建議)
   - 測試完整的 API 流程
   - 驗證資料庫操作

### 測試覆蓋目標

- API 端點: 100%
- Model 方法: 80%+
- Business Logic: 90%+

---

## 🔐 認證與授權

### JWT 認證流程

1. **登入** → 取得 Access Token + Refresh Token
2. **API 請求** → Header 帶 `Authorization: Bearer <access_token>`
3. **Token 過期** → 使用 Refresh Token 更新
4. **登出** → 清除 Token

### 角色權限

- **admin**: 完整系統管理權限
- **salesperson**: 管理自己的資料和檔案
- **user**: 僅查詢公開資料

### 權限檢查

使用 Filters 實現:
- `AuthFilter`: 驗證 JWT Token
- `RoleFilter`: 檢查角色權限

---

## 📚 參考文檔

### 專案文檔
- [DEVELOPMENT.md](./DEVELOPMENT.md) - 完整開發指南
- [README.md](../README.md) - 專案總覽

### OpenSpec 規範
- [API 端點規範](../openspec/specs/api/endpoints.md)
- [資料模型規範](../openspec/specs/models/data-models.md)
- [系統架構](../openspec/specs/architecture/overview.md)

### Commands 使用
- [Commands README](../.claude/commands/README.md)
- [工作流程圖](../.claude/commands/WORKFLOW.md)

---

## 🐛 常見問題

### Q: 如何新增 API 端點?

A: 使用 OpenSpec Commands:
```bash
/implement 新增 XX API
```
或手動:
1. 撰寫 API 規格 (`openspec/changes/<feature>/specs/api.md`)
2. 建立 Controller (`php spark make:controller Api/XXController`)
3. 定義路由 (`app/Config/Routes.php`)
4. 實作業務邏輯
5. 測試 API

### Q: 如何修改資料表結構?

A: **必須使用 Migration**:
```bash
# 建立 Migration
docker exec -it my_profile_ci4-app-1 php spark make:migration ModifyTableName

# 執行 Migration
docker exec -it my_profile_ci4-app-1 php spark migrate
```

**禁止** 直接修改資料庫!

### Q: 如何處理跨域請求?

A: CORS 已配置於 `app/Config/Filters.php`:
- 允許的 Origins: localhost:3000, localhost:5173, localhost:8080
- 允許的 Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS
- 允許的 Headers: Content-Type, Authorization

---

## ⚠️ 重要原則

### 規範驅動開發

❌ **禁止**:
- 未撰寫規格就開始寫程式
- 規格模糊就開始實作
- 實作過程中隨意偏離規格
- 直接修改資料庫結構 (不使用 Migration)

✅ **必須**:
- 先撰寫完整規格
- 規格通過驗證後才實作
- 嚴格遵循規格
- 所有資料庫變更都使用 Migration

### 代碼品質

❌ **禁止**:
- SQL 注入風險 (使用 Query Builder)
- XSS 攻擊 (輸出時轉義)
- 硬編碼敏感資訊 (使用 .env)
- 缺少錯誤處理

✅ **必須**:
- 輸入驗證
- 錯誤處理
- 日誌記錄
- 安全性檢查

---

## 🎯 開發檢查清單

開發新功能前檢查:
- [ ] 需求已明確 (proposal.md)
- [ ] 規格已完整 (specs/*.md)
- [ ] 任務已拆解 (tasks.md)
- [ ] 規格已驗證

開發完成後檢查:
- [ ] 代碼符合規範
- [ ] API 測試通過
- [ ] 無安全漏洞
- [ ] 文檔已更新
- [ ] 規格已歸檔

---

**維護者**: Development Team
**最後更新**: 2026-01-09
**版本**: 1.0
