# YAMU 業務員推廣系統 - Claude 開發指南

**專案類型**: Full-Stack Monorepo
**架構**: Laravel 11 (Backend) + Next.js 15 (Frontend)
**開發方法**: OpenSpec Specification-Driven Development (SDD)
**最後更新**: 2026-01-20

---

## 🎯 專案總覽

YAMU 是一個完整的業務員檔案管理與搜尋平台，採用前後端分離架構：

- **Backend API**: Laravel 11 + MySQL (Port 8080)
- **Frontend SPA**: Next.js 15 + React 19 (Port 3001)
- **開發方法**: OpenSpec 規範驅動開發
- **測試覆蓋**: 201+ 測試，80%+ 覆蓋率

---

## 📁 Monorepo 結構

```
my_profile/                          # 專案根目錄 (此目錄)
├── CLAUDE.md                        # 本文件 - 整體專案導引
├── README.md                        # 專案說明文件
│
├── my_profile_laravel/              # Backend API (Laravel 11)
│   ├── CLAUDE.md                    # Backend 開發規範 📚
│   ├── app/                         # Laravel 應用程式碼
│   ├── tests/                       # 201 個測試
│   ├── docker/                      # Docker 配置
│   └── ...
│
├── frontend/                        # Frontend SPA (Next.js 15)
│   ├── CLAUDE.md                    # Frontend 開發規範 📚
│   ├── app/                         # Next.js App Router
│   ├── components/                  # React 組件
│   ├── lib/                         # API 客戶端與工具
│   └── ...
│
├── openspec/                        # OpenSpec 規範庫
│   ├── specs/                       # 系統規範
│   │   ├── backend/                 # Backend API 規範
│   │   └── frontend/                # Frontend UI 規範
│   └── changes/                     # 功能變更提案
│       └── <feature-name>/
│
├── docs/                            # 專案文檔
│   ├── architecture.md              # 架構設計
│   ├── api/                         # API 文檔
│   └── deployment.md                # 部署指南
│
└── .claude/                         # Claude Code 配置
    ├── commands/                    # OpenSpec Commands
    │   ├── README.md                # Commands 使用指南
    │   ├── WORKFLOW.md              # 開發工作流程
    │   ├── implement.md             # Backend 實作命令
    │   ├── implement-frontend.md    # Frontend 實作命令
    │   ├── feature-finish.md        # 完成功能
    │   ├── test.md                  # 測試命令
    │   └── ...
    ├── agents/                      # 專業 Agents
    │   ├── qa-engineer.md           # QA 測試 Agent
    │   ├── devops-engineer.md       # DevOps Agent
    │   └── react-specialist.md      # React 專家 Agent
    └── skills/                      # 專業 Skills (NEW!)
        ├── README.md                # Skills 使用指南
        ├── react-best-practices/    # React/Next.js 效能優化
        ├── webapp-testing/          # Playwright Web 測試
        ├── playwright-skill/        # 瀏覽器自動化
        ├── frontend-design/         # UI/UX 設計
        ├── php-pro/                 # PHP/Laravel 專家
        └── artifacts-builder/       # 複雜 HTML artifacts
```

---

## 🚦 快速導航

### 我要開發 Backend API
```bash
cd my_profile_laravel
# 閱讀 Backend 開發規範
cat CLAUDE.md
```

**參考文件**:
- `my_profile_laravel/CLAUDE.md` - Backend 開發規範
- `openspec/specs/backend/README.md` - Backend API 規範總覽
- `.claude/commands/implement.md` - Backend 實作命令

### 我要開發 Frontend UI
```bash
cd frontend
# 閱讀 Frontend 開發規範
cat CLAUDE.md
```

**參考文件**:
- `frontend/CLAUDE.md` - Frontend 開發規範
- `openspec/specs/frontend/README.md` - Frontend UI 規範總覽
- `.claude/commands/implement-frontend.md` - Frontend 實作命令

### 我要了解 OpenSpec Commands
```bash
# 閱讀 Commands 使用指南
cat .claude/commands/README.md

# 查看開發工作流程
cat .claude/commands/WORKFLOW.md
```

**可用命令**:
- `/implement` - Backend 規範驅動開發
- `/implement-frontend` - Frontend 規範驅動開發
- `/test` - 執行全面測試
- `/feature-finish` - 完成功能開發
- `/pr-review` - Pull Request 審查

### 我要使用專業 Skills

```bash
# 閱讀 Skills 使用指南
cat .claude/skills/README.md
```

**可用 Skills** (6 個):
- `react-best-practices` - React/Next.js 效能優化 (自動觸發)
- `webapp-testing` - Playwright Web 應用測試
- `playwright-skill` - 完整瀏覽器自動化測試
- `frontend-design` - UI/UX 設計專家
- `php-pro` - PHP/Laravel 開發專家 (自動觸發)
- `artifacts-builder` - 複雜多組件 HTML artifacts

**快速使用**:
```
"請使用 playwright-skill 測試登入流程"
"請使用 react-best-practices 審查這個組件"
"請使用 frontend-design 設計評分介面"
```

**詳細文檔**: `.claude/skills/README.md`

---

## 🎯 Skills 自動觸發機制

某些 Skills 會在特定情境下**自動觸發**，無需手動調用：

### 自動觸發場景

| 場景 | 觸發的 Skill | 說明 |
|------|-------------|------|
| 撰寫 React 組件 | `react-best-practices` | 自動檢查效能最佳實踐 |
| 審查 React 程式碼 | `react-best-practices` | 提供優化建議 |
| 處理 Laravel 程式碼 | `php-pro` | Laravel 最佳實踐指導 |
| 執行 `/implement-frontend` | `frontend-design` | UI/UX 設計階段自動調用 |
| 重構 React/Next.js | `react-best-practices` | 確保符合效能標準 |

### 主動調用場景

以下情況需要**主動調用** Skills：

```bash
# 測試前端功能
"請使用 playwright-skill 測試搜尋功能"

# 測試登入流程
"請使用 webapp-testing 驗證登入流程"

# 設計新介面
"請使用 frontend-design 設計業務員卡片"

# 創建複雜組件
"請使用 artifacts-builder 創建互動式儀表板"
```

---

## 🔧 開發環境設置

### 1. 啟動 Backend API

```bash
cd my_profile_laravel

# 啟動 Docker 容器
docker-compose up -d

# 執行資料庫遷移
docker exec -it my_profile_laravel_app php artisan migrate

# 執行資料種子
docker exec -it my_profile_laravel_app php artisan db:seed

# 測試 API
curl http://localhost:8080/api/health
```

**Backend 服務**:
- API: http://localhost:8080
- MySQL: localhost:3307
- OpenAPI: http://localhost:8080/docs/api

### 2. 啟動 Frontend

```bash
cd frontend

# 安裝依賴
npm install

# 啟動開發伺服器
npm run dev
```

**Frontend 服務**:
- Dev Server: http://localhost:3001
- Turbopack: 啟用

---

## 📝 開發流程 (OpenSpec SDD)

### Backend 功能開發

```bash
# 在專案根目錄執行
/implement <功能描述>
```

這會自動執行完整的 Backend SDD 流程：
1. 建立變更提案
2. 撰寫詳細規格 (API、DB Schema、Business Rules、Tests)
3. 拆解任務
4. 實作 Backend 程式碼
5. 執行測試驗證
6. 歸檔規格

### Frontend 功能開發

```bash
# 在專案根目錄執行
/implement-frontend <功能描述>
```

這會自動執行完整的 Frontend SDD 流程：
1. 建立變更提案
2. 撰寫詳細規格 (UI/UX、Components、API Integration、State)
3. 拆解 UI 任務
4. 實作 Frontend 組件
5. 整合 API
6. 歸檔規格

### 完整功能開發流程

```bash
# 1. 開發 Backend API
/implement 新增評分功能

# 2. 開發 Frontend UI
/implement-frontend 新增評分 UI

# 3. 執行全面測試
/test 評分功能

# 4. 完成功能開發
/feature-finish
```

---

## 🎯 關鍵原則

### 規範驅動開發 (Specification-Driven Development)

❌ **禁止**:
- 未撰寫規格就開始寫程式碼
- 規格不完整就開始實作
- 實作過程中偏離規格
- 跳過測試驗證

✅ **必須**:
- 先撰寫完整、明確的規格
- 規格包含所有必要細節 (API、DB、UI、Tests)
- 實作嚴格遵循規格
- 完成後歸檔到規範庫

### Monorepo 工作原則

1. **前後端分離**
   - Backend 和 Frontend 是獨立的子專案
   - 各自有獨立的 CLAUDE.md 開發規範
   - 通過 API 進行通信

2. **規範統一管理**
   - 所有規範統一存放在 `openspec/` 目錄
   - Backend 規範: `openspec/specs/backend/`
   - Frontend 規範: `openspec/specs/frontend/`

3. **Commands 統一執行**
   - 所有 Commands 在專案根目錄執行
   - Commands 會自動處理前後端切換

---

## 🔍 專案資源索引

### 核心文檔

| 文檔 | 路徑 | 說明 |
|------|------|------|
| **專案總覽** | `README.md` | 專案說明、快速開始 |
| **Backend 規範** | `my_profile_laravel/CLAUDE.md` | Laravel 開發規範 |
| **Frontend 規範** | `frontend/CLAUDE.md` | Next.js 開發規範 |
| **Commands 指南** | `.claude/commands/README.md` | OpenSpec Commands 使用 |
| **工作流程** | `.claude/commands/WORKFLOW.md` | 開發流程圖 |

### 規範庫

| 類型 | 路徑 | 說明 |
|------|------|------|
| **Backend 規範** | `openspec/specs/backend/` | API、DB、Business Rules |
| **Frontend 規範** | `openspec/specs/frontend/` | UI/UX、Components、State |
| **功能提案** | `openspec/changes/` | 功能變更提案與規格 |

### API 文檔

| 類型 | URL | 說明 |
|------|-----|------|
| **OpenAPI Docs** | http://localhost:8080/docs/api | Swagger UI 互動文檔 |
| **OpenAPI JSON** | http://localhost:8080/docs/openapi.json | OpenAPI 3.1 規範 |
| **Health Check** | http://localhost:8080/api/health | API 健康檢查 |

---

## 🧪 測試策略

### Backend 測試

```bash
cd my_profile_laravel

# 執行所有測試
docker exec -it my_profile_laravel_app composer test

# 測試覆蓋率
docker exec -it my_profile_laravel_app composer test:coverage

# 靜態分析
docker exec -it my_profile_laravel_app composer analyse
```

**測試目標**:
- Feature Tests: 95%+ 覆蓋率
- Unit Tests: 90%+ 覆蓋率
- PHPStan: Level 9 (最嚴格)

### Frontend 測試

```bash
cd frontend

# 執行單元測試
npm test

# E2E 測試 (Playwright)
npx playwright test

# 類型檢查
npm run typecheck
```

**測試目標**:
- Component Tests: 80%+ 覆蓋率
- E2E Tests: 覆蓋關鍵流程
- TypeScript: strict mode

### 整合測試

使用 QA Engineer Agent 執行全面測試：

```bash
/test <feature-name>
```

包含：
- API 測試
- Frontend E2E 測試
- 前後端整合測試
- 效能測試
- 視覺回歸測試

---

## 🚀 部署流程

### 1. 環境準備

- **開發環境**: Docker Compose
- **測試環境**: Docker Compose + CI/CD
- **生產環境**: Kubernetes / Docker Swarm

### 2. 部署 Backend

```bash
cd my_profile_laravel

# 構建 Docker Image
docker build -t yamu-backend:latest .

# 部署到生產
docker-compose -f docker-compose.prod.yml up -d
```

### 3. 部署 Frontend

```bash
cd frontend

# 構建生產版本
npm run build

# 啟動生產伺服器
npm start
```

詳見: `docs/deployment.md`

---

## 🛠️ 常用工具

### Docker 管理

```bash
# 查看運行中的容器
docker ps

# 查看日誌
docker logs my_profile_laravel_app

# 進入容器
docker exec -it my_profile_laravel_app bash

# 重啟服務
docker-compose restart
```

### 資料庫管理

```bash
# 連接到 MySQL
mysql -h 127.0.0.1 -P 3307 -u sail -p

# 執行遷移
docker exec -it my_profile_laravel_app php artisan migrate

# 回滾遷移
docker exec -it my_profile_laravel_app php artisan migrate:rollback

# 重置資料庫
docker exec -it my_profile_laravel_app php artisan migrate:fresh --seed
```

### Git 工作流程

```bash
# 創建功能分支
git checkout -b feature/add-rating-system

# 開發完成後提交
git add .
git commit -m "feat: Add rating system"

# 推送到遠端
git push origin feature/add-rating-system

# 創建 Pull Request
gh pr create --title "Add rating system"
```

---

## 📞 問題排查

### Backend 問題

| 問題 | 解決方案 |
|------|----------|
| 容器啟動失敗 | 檢查 Docker Desktop 是否運行 |
| 資料庫連接失敗 | 確認 MySQL 容器運行中 |
| Migration 失敗 | 檢查資料庫權限、表結構衝突 |
| 測試失敗 | 執行 `composer test` 查看詳細錯誤 |

### Frontend 問題

| 問題 | 解決方案 |
|------|----------|
| npm install 失敗 | 刪除 node_modules 和 package-lock.json 重試 |
| API 連接失敗 | 確認 Backend 運行在 localhost:8080 |
| 類型錯誤 | 執行 `npm run typecheck` |
| Build 失敗 | 檢查環境變數配置 |

### 整合問題

| 問題 | 解決方案 |
|------|----------|
| CORS 錯誤 | 檢查 Backend CORS 配置 |
| 認證失敗 | 確認 JWT Token 正確傳遞 |
| 資料不同步 | 檢查 React Query cache 配置 |

---

## 🎓 學習資源

### Laravel 資源
- [Laravel 官方文檔](https://laravel.com/docs/11.x)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)

### Next.js 資源
- [Next.js 官方文檔](https://nextjs.org/docs)
- [React 官方文檔](https://react.dev)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)

### OpenSpec 資源
- `.claude/commands/README.md` - Commands 使用指南
- `.claude/commands/WORKFLOW.md` - 開發工作流程
- `openspec/specs/` - 規範範例

---

## 📋 開發檢查清單

開始新功能前：
- [ ] 確認 Backend 服務運行 (http://localhost:8080)
- [ ] 確認 Frontend 服務運行 (http://localhost:3001)
- [ ] 閱讀相關規範文件
- [ ] 了解 API 端點和資料結構

開發過程中：
- [ ] 使用 OpenSpec Commands
- [ ] 先撰寫規格，再寫程式碼
- [ ] 遵循專案程式碼風格
- [ ] 撰寫測試

提交前檢查：
- [ ] 所有測試通過
- [ ] 程式碼品質檢查通過 (PHPStan / ESLint)
- [ ] 規格已歸檔到 openspec/
- [ ] Commit message 符合規範

---

## 🔐 安全注意事項

1. **敏感資訊管理**
   - 不要提交 `.env` 文件
   - 使用環境變數管理機密
   - API Keys 存放在安全位置

2. **認證與授權**
   - 所有 API 端點都需要驗證
   - 使用 JWT 雙令牌機制
   - 定期更新 Token

3. **資料驗證**
   - Backend: Laravel Validation
   - Frontend: Zod Schema
   - 永遠不信任用戶輸入

---

## 📮 聯絡資訊

- **專案維護者**: Development Team
- **Issue Tracker**: GitHub Issues
- **文檔問題**: 提交 PR 到 `docs/` 目錄

---

**最後更新**: 2026-01-11
**版本**: 1.0
**Claude Code Version**: Latest
