# 完整開發流程

**專案**: YAMU 業務員推廣系統
**團隊類型**: 新創公司
**更新日期**: 2026-01-09

---

## 📋 目錄

1. [開發環境設置](#開發環境設置)
2. [日常開發流程](#日常開發流程)
3. [測試流程](#測試流程)
4. [代碼審查流程](#代碼審查流程)
5. [部署流程](#部署流程)
6. [緊急修復流程](#緊急修復流程)

---

## 🛠️ 開發環境設置

### 初次設置

#### 1. 克隆專案

```bash
git clone <repository-url>
cd my_profile
```

#### 2. 初始化 Git Flow

```bash
/git-flow-init
```

這會：
- 創建 `develop` 分支
- 設置分支保護規則（需要在 GitHub/GitLab 配置）
- 配置 Git hooks

#### 3. 設置後端環境（CodeIgniter 4）

```bash
cd my_profile_ci4

# 啟動 Docker 容器
docker-compose up -d

# 執行 migrations
docker exec -it my_profile_ci4-app-1 php spark migrate

# 執行 seeders
docker exec -it my_profile_ci4-app-1 php spark db:seed SystemDataSeeder

# 測試 API
curl http://localhost:8080/api/industries
```

#### 4. 設置前端環境（Next.js）

```bash
cd frontend

# 安裝依賴
npm install

# 配置環境變數
cp .env.example .env.local
# 編輯 .env.local 設置 API URL

# 啟動開發伺服器
npm run dev

# 訪問: http://localhost:3000
```

#### 5. 設置 IDE

**VS Code 推薦擴展**:
- PHP Intelephense
- ESLint
- Prettier
- GitLens
- Docker

**配置**:
```json
{
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "php.validate.executablePath": "/usr/bin/php",
  "php.suggest.basic": false
}
```

---

## 💻 日常開發流程

### 流程 1: 開發新功能（使用 OpenSpec）

這是**推薦的開發方式**，適用於大部分功能開發。

```mermaid
graph TD
    A[開始] --> B[/feature-start <name>]
    B --> C{功能類型?}
    C -->|Backend| D[/implement 功能描述]
    C -->|Frontend| E[/implement-frontend 功能描述]
    D --> F[Step 1: Proposal 確認需求]
    E --> F
    F --> G[Step 2: Write Specs]
    G --> H[Step 3: Break Down Tasks]
    H --> I[Step 4: Validate Specs]
    I --> J[Step 5: Implement]
    J --> K[Step 6: Archive Specs]
    K --> L[/feature-finish]
    L --> M[創建 PR]
    M --> N[Code Review]
    N --> O{通過?}
    O -->|是| P[合併到 develop]
    O -->|否| Q[修改代碼]
    Q --> N
    P --> R[刪除分支]
    R --> S[結束]
```

#### 詳細步驟

##### Step 1: 開始功能分支

```bash
/feature-start add-rating-api
```

系統會：
- 詢問功能類型（Backend/Frontend）
- 創建 feature 分支
- 推送到遠端

##### Step 2: 執行開發流程

**Backend 功能**:
```bash
/implement 新增業務員評分 API
```

**Frontend 功能**:
```bash
/implement-frontend 新增評分 UI 組件
```

這會自動執行 OpenSpec SDD 流程：
1. **Proposal**: 使用 AskUserQuestion 確認所有需求細節
2. **Specs**: 撰寫完整規格（API/UI/Data Model/Business Rules）
3. **Tasks**: 拆解為可執行任務
4. **Validate**: 驗證規格完整性
5. **Implement**: 使用 TodoWrite 追蹤進度實作
6. **Archive**: 歸檔到 `openspec/specs/`

##### Step 3: 完成功能

```bash
/feature-finish
```

系統會：
- 檢查測試是否通過
- 確認代碼規範
- 創建 Pull Request
- 指定審查者

##### Step 4: 代碼審查

審查者執行：
```bash
/pr-review <pr-number>
```

##### Step 5: 合併

審查通過後：
- 使用 **Squash and Merge**
- 刪除 feature 分支

---

### 流程 2: 快速修復（不使用 OpenSpec）

適用於小型 bug 修復或簡單變更。

```bash
# 1. 開始 feature 分支
/feature-start fix-login-bug

# 2. 直接修改代碼（不使用 /implement）
# 編輯文件...

# 3. Commit
git add .
git commit -m "fix(auth): resolve login token expiration bug"

# 4. 完成
/feature-finish
```

**注意**: 即使是快速修復，也要：
- 遵循 Commit 規範
- 撰寫測試
- 更新文檔（如需要）

---

### 流程 3: Laravel 遷移開發

專門用於 CodeIgniter 4 到 Laravel 的架構遷移。

```mermaid
graph TD
    A[開始遷移] --> B[規劃遷移模組]
    B --> C[/migration-start <module>]
    C --> D[讀取遷移規格]
    D --> E[實作遷移]
    E --> F[執行測試]
    F --> G{API 兼容?}
    G -->|否| H[修復不兼容]
    H --> F
    G -->|是| I[前端整合測試]
    I --> J{前端正常?}
    J -->|否| K[修復問題]
    K --> I
    J -->|是| L[/migration-finish]
    L --> M[創建 PR]
    M --> N[Code Review + 兼容性檢查]
    N --> O{通過?}
    O -->|是| P[合併到 develop]
    O -->|否| Q[修改]
    Q --> N
    P --> R[下一個模組]
    R --> C
```

#### 遷移模組順序

```bash
# 模組 1: 專案初始化
/migration-start 01-project-setup

# 模組 2: 資料庫層
/migration-start 02-database-layer

# 模組 3: 認證模組
/migration-start 03-auth-module

# 模組 4: API 端點
/migration-start 04-api-endpoints

# 模組 5: 業務邏輯
/migration-start 05-business-logic

# 模組 6: 測試
/migration-start 06-testing

# 模組 7: 部署
/migration-start 07-deployment
```

#### 每個模組完成後

```bash
# 完成模組
/migration-finish

# 這會：
# 1. 執行 API 兼容性測試
# 2. 執行單元測試
# 3. 前端整合測試
# 4. 創建 PR
```

---

## 🧪 測試流程

### 後端測試（CodeIgniter 4）

```bash
cd my_profile_ci4

# 執行 API 測試腳本
./scripts/test-api.sh

# 手動測試特定端點
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'
```

### 後端測試（Laravel）

```bash
cd my_profile_laravel

# 執行所有測試
php artisan test

# 執行特定測試
php artisan test --filter RatingTest

# 測試覆蓋率
php artisan test --coverage --min=80

# 靜態分析
./vendor/bin/phpstan analyse --level=9 app/

# 代碼格式檢查
./vendor/bin/phpcs --standard=PSR12 app/
```

### 前端測試

```bash
cd frontend

# 執行單元測試
npm test

# 測試覆蓋率
npm run test:coverage

# TypeScript 檢查
npm run typecheck

# ESLint 檢查
npm run lint

# 構建測試
npm run build
```

### API 兼容性測試（遷移專用）

```bash
# 啟動兩個服務
# CI4: http://localhost:8080
# Laravel: http://localhost:8081

# 執行兼容性測試
./scripts/test-api-compatibility.sh

# 查看報告
cat reports/api-compatibility-test.log
```

---

## 👀 代碼審查流程

### 審查者流程

```bash
# 1. 收到審查請求
/pr-review <pr-number>

# 系統會自動：
# - Checkout PR 分支
# - 執行自動化檢查（格式、測試、構建）
# - 提供審查指引

# 2. 手動審查代碼
# - 檢查功能實現
# - 檢查代碼質量
# - 檢查安全性
# - 檢查性能

# 3. 提供審查意見
# - MUST: 必須修改
# - SHOULD: 強烈建議
# - COULD: 可選優化
# - QUESTION: 需要討論

# 4. 批准或要求修改
gh pr review <pr-number> --approve
# 或
gh pr review <pr-number> --request-changes
```

### 審查標準

參考文檔：
- **Backend**: `.claude/skills/php-pro/SKILL.md`
- **Frontend**: `frontend/CLAUDE.md`
- **Git Flow**: `.claude/workflows/GIT_FLOW.md`

### 遷移 PR 額外檢查

- ✅ API 兼容性測試 100% 通過
- ✅ 前端整合測試通過
- ✅ 性能無明顯降低
- ✅ 代碼符合 PHP Pro 標準

---

## 🚀 部署流程

### 準備發布

```bash
# 1. 從 develop 創建 release 分支
/release-start v1.0.0

# 2. 準備發布工作
# - 更新版本號
# - 更新 CHANGELOG.md
# - 最後的測試
# - 修復發現的小問題

# 3. 完成發布
/release-finish

# 這會：
# - 合併到 main
# - 創建 tag
# - 合併回 develop
# - 刪除 release 分支
```

### 部署到生產環境

```bash
# 1. 確保在 main 分支
git checkout main
git pull origin main

# 2. 部署後端
cd my_profile_ci4
docker-compose -f docker-compose.prod.yml up -d

# 或 Laravel（遷移後）
cd my_profile_laravel
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. 部署前端
cd frontend
npm run build
# 上傳到 Vercel/Netlify 或自建服務器

# 4. 驗證部署
curl https://api.yamu.com/api/health
curl https://yamu.com
```

---

## 🚨 緊急修復流程

### 何時使用 Hotfix

- 生產環境重大 bug
- 安全漏洞
- 數據損壞風險
- 服務中斷

### Hotfix 流程

```bash
# 1. 從 main 創建 hotfix 分支
/hotfix-start v1.0.1-fix-auth-bug

# 2. 快速修復
# - 最小化變更
# - 只修復問題本身
# - 撰寫測試

# 3. 測試
# - 單元測試
# - 整合測試
# - 手動驗證

# 4. 完成 hotfix
/hotfix-finish

# 這會：
# - 合併到 main
# - 創建 tag
# - 合併回 develop
# - 刪除 hotfix 分支

# 5. 立即部署
# - 部署到生產環境
# - 驗證修復
# - 通知團隊和用戶
```

---

## 📊 開發指標

### 代碼質量指標

| 指標 | 目標 | 工具 |
|------|------|------|
| 測試覆蓋率 | ≥ 80% | PHPUnit, Jest |
| 靜態分析 | Level 9 | PHPStan |
| 代碼格式 | 100% | PSR-12, ESLint |
| 構建成功率 | 100% | CI/CD |

### 開發效率指標

| 指標 | 目標 |
|------|------|
| PR 審查時間 | < 24 小時 |
| 單元測試時間 | < 5 分鐘 |
| 構建時間 | < 10 分鐘 |
| 部署時間 | < 15 分鐘 |

---

## 🛠️ 常用命令速查

### Git Flow Commands

```bash
/git-flow-init              # 初始化 Git Flow
/feature-start <name>       # 開始新功能
/feature-finish             # 完成功能
/release-start <version>    # 開始發布
/release-finish             # 完成發布
/hotfix-start <version>     # 開始熱修復
/hotfix-finish              # 完成熱修復
/migration-start <module>   # 開始遷移模組
/migration-finish           # 完成遷移模組
```

### 開發 Commands

```bash
/implement <功能描述>              # Backend OpenSpec 開發
/implement-frontend <功能描述>     # Frontend OpenSpec 開發
/proposal <功能描述>               # 建立變更提案
/spec <feature-name>              # 撰寫詳細規格
/develop <feature-name>           # 依據規格開發
/archive <feature-name>           # 歸檔規格
```

### 審查 Commands

```bash
/pr-review <pr-number>      # 審查 Pull Request
```

---

## 📚 參考文檔

### 工作流程
- [Git Flow 工作流程](./.claude/workflows/GIT_FLOW.md)
- [Commands 總覽](../.claude/commands/README.md)
- [Commands 工作流程](../.claude/commands/WORKFLOW.md)

### 開發規範
- [後端開發規範](../my_profile_ci4/CLAUDE.md)
- [前端開發規範](../frontend/CLAUDE.md)
- [PHP Pro Skill](../.claude/skills/php-pro/SKILL.md)

### OpenSpec 規範
- [API 端點規範](../openspec/specs/api/endpoints.md)
- [資料模型規範](../openspec/specs/models/data-models.md)
- [Frontend UI 規範](../openspec/specs/frontend/ui-components.md)

---

## ❓ 常見問題

### Q: 什麼時候使用 OpenSpec 流程？

A:
- ✅ **使用**: 新功能開發、重構、複雜變更
- ❌ **不使用**: 簡單 bug 修復、typo 修正、小調整

### Q: 如何處理 merge conflicts？

A:
```bash
# 1. 同步 develop
git fetch origin develop
git rebase origin/develop

# 2. 解決衝突
# 手動編輯衝突文件

# 3. 標記為已解決
git add <resolved-files>
git rebase --continue

# 4. 推送
git push --force-with-lease
```

### Q: PR 太大怎麼辦？

A: 拆分為多個小 PR：
- 每個 PR 專注一個功能點
- 保持 PR 大小 < 500 lines
- 更容易審查，降低風險

### Q: 遷移時如何確保兼容性？

A:
1. 執行 API 兼容性測試腳本
2. 對照 CI4 和 Laravel 的 Response
3. 前端整合測試
4. 通過才能合併

---

**維護者**: Development Team
**最後更新**: 2026-01-09
**版本**: 1.0
