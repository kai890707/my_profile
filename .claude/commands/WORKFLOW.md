# OpenSpec 完整工作流程

**版本**: 1.0
**最後更新**: 2026-01-13
**目標**: 串連 Commands、Agents、Skills，形成完整的開發工作流程

---

## 📊 總覽：三大工作流程

```
┌─────────────────────────────────────────────────────────────┐
│                    OpenSpec 開發生態系統                      │
├─────────────────────────────────────────────────────────────┤
│  Commands (指令層)  →  Agents (執行層)  →  Skills (工具層)   │
└─────────────────────────────────────────────────────────────┘

三大工作流程:
1. Backend 功能開發流程 (Laravel API)
2. Frontend 功能開發流程 (Next.js UI)
3. 全棧功能開發流程 (Backend + Frontend)
```

---

## 🔄 工作流程 1: Backend 功能開發

### 流程圖

```mermaid
graph TD
    A[用戶: 我要開發 Backend 功能] --> B[/feature-start]
    B --> C[Git: 創建 feature 分支]
    C --> D[/implement 功能描述]

    D --> E1[Step 1: Proposal]
    E1 --> E2[使用 requirements-analyst agent]
    E2 --> E3[需求訪談 → proposal.md]
    E3 --> E4[✋ 用戶確認]

    E4 --> F1[Step 2: Specifications]
    F1 --> F2[使用 software-architect agent]
    F2 --> F3[技術設計 → specs/*.md]

    F3 --> G1[Step 3: Tasks]
    G1 --> G2[拆解任務 → tasks.md]

    G2 --> H1[Step 4: Validate]
    H1 --> H2[驗證規格完整性]
    H2 --> H3[✋ 用戶確認啟動 AUTO-RUN]

    H3 --> I1[Step 5: Implement 🤖 AUTO-RUN]
    I1 --> I2[使用 laravel-specialist agent]
    I2 --> I3[實作程式碼 + 測試]

    I3 --> J1[Step 6: Archive]
    J1 --> J2[歸檔到規範庫]

    J2 --> K[/test]
    K --> K1[使用 qa-engineer agent]
    K1 --> K2[全面測試 + 報告]

    K2 --> L[/feature-finish]
    L --> L1[使用 qa-engineer agent]
    L1 --> L2[品質檢查 + 創建 PR]

    L2 --> M[/pr-review]
    M --> N[合併到 develop]
    N --> O[/deploy staging]
    O --> O1[使用 devops-engineer agent]
    O1 --> O2[自動部署到 Staging]

    O2 --> P{測試通過?}
    P -->|Yes| Q[/deploy production]
    Q --> Q1[使用 devops-engineer agent]
    Q1 --> Q2[部署到 Production]

    P -->|No| R[修復問題]
    R --> K
```

### 詳細步驟與 Agents/Skills 使用

#### Phase 1: 專案準備 (Git Flow)

**Command**: `/feature-start <feature-name>`

| 步驟 | 操作 | 使用工具 | 產出 |
|------|------|----------|------|
| 1.1 | 詢問用戶功能類型 | AskUserQuestion | 確認 Backend/Frontend/全棧 |
| 1.2 | 檢查 git 狀態 | Bash (git status) | 當前狀態 |
| 1.3 | 切換到 develop | Bash (git checkout) | 切換分支 |
| 1.4 | 創建 feature 分支 | Bash (git checkout -b) | `feature/<name>` |
| 1.5 | 推送到遠端 | Bash (git push -u) | 遠端分支 |

**產出**: Feature 分支已就緒

---

#### Phase 2: 規範驅動開發 (SDD)

**Command**: `/implement [功能描述]`

這個 command 會自動執行 6 個步驟，每個步驟都會使用特定的 agent。

---

##### Step 1: Proposal (需求訪談)

**使用 Agent**: `requirements-analyst`

```
Task tool:
  subagent_type: requirements-analyst
  prompt: 進行需求訪談，分析「[功能描述]」的完整需求
```

**Agent 工作流程**:
1. 理解需求背景（背景、目標、使用者）
2. 系統化提問
   - 功能目的
   - 目標用戶
   - 使用情境
   - 技術限制
   - 優先級
3. 邊界情境分析
4. 定義 In Scope / Out of Scope
5. 制定驗收標準

**產出**: `openspec/changes/<feature-name>/proposal.md`

**暫停點**: ✋ 用戶確認需求理解正確

---

##### Step 2: Specifications (技術設計)

**使用 Agent**: `software-architect`

```
Task tool:
  subagent_type: software-architect
  prompt: 基於 proposal，設計「[功能描述]」的技術架構和資料模型
```

**Agent 工作流程**:
1. 讀取 proposal.md
2. 設計 API 端點
   - Request/Response 格式
   - 驗證規則
   - 錯誤處理
3. 設計資料模型
   - 資料表結構
   - 索引設計
   - 關聯設計
4. 定義業務規則
   - 驗證邏輯
   - 業務約束
5. 考慮效能、安全性、擴展性

**產出**:
- `specs/api.md` - API 規格
- `specs/data-model.md` - 資料模型
- `specs/business-rules.md` - 業務規則

**暫停點**: 無（自動執行）

---

##### Step 3: Tasks (任務拆解)

**不使用 Agent**，由主 Assistant 執行

**工作流程**:
1. 讀取所有 specs
2. 拆解為原子任務
   - Phase 1: Database & Models
   - Phase 2: API Endpoints
   - Phase 3: Business Logic
   - Phase 4: Tests
3. 每個任務標註檔案位置和依賴

**產出**: `tasks.md`

**暫停點**: 無（自動執行）

---

##### Step 4: Validate (規格驗證)

**不使用 Agent**，由主 Assistant 執行

**驗證清單**:
- ✅ 完整性: 所有必要章節已填寫
- ✅ 一致性: API、資料模型、業務規則對應
- ✅ 清晰性: 無歧義，可直接實作
- ✅ 可測性: 有明確驗收標準

**使用工具**: AskUserQuestion

**詢問用戶**:
```
✅ 規格驗證完成！

⚠️  接下來將啟動 AUTO-RUN 模式:
  🤖 系統會自動完成所有實作任務
  🤖 不會再詢問確認，直到全部完成
  🤖 預估時間: 15-20 分鐘

是否繼續進入 AUTO-RUN 模式?
```

**暫停點**: ✋ 最後確認點（啟動 AUTO-RUN）

---

##### Step 5: Implement (程式碼實作) 🤖 AUTO-RUN

**使用 Agent**: `laravel-specialist`

```
Task tool:
  subagent_type: laravel-specialist
  prompt: 按照 tasks.md 實作所有程式碼，遵循 Laravel 最佳實踐
```

**Agent 工作流程**:
1. 初始化 AUTO-RUN 模式
2. 使用 TodoWrite 建立任務清單
3. 逐步實作（不詢問用戶）
   - 每個 task 標記為 in_progress
   - 讀取規格
   - 實作程式碼（Controllers、Models、Migrations、Tests）
   - 自動驗證
   - 標記為 completed
4. 自動錯誤修復
5. 輸出進度（Task 3/12 completed）

**Agent 特性**:
- ✅ Laravel 框架規範
- ✅ Eloquent 關聯正確
- ✅ Query 優化（防止 N+1）
- ✅ Form Requests 驗證
- ✅ Policy 授權邏輯
- ✅ 測試覆蓋

**產出**: 完整的程式碼實作

**暫停點**: 無（AUTO-RUN 模式，只有規格不清時才暫停）

---

##### Step 6: Archive (規格歸檔) 🤖 AUTO-RUN

**不使用 Agent**，由主 Assistant 執行

**操作**:
1. 合併 specs/api.md 到 openspec/specs/api/endpoints.md
2. 合併 specs/data-model.md 到 openspec/specs/models/data-models.md
3. 合併 specs/business-rules.md 到 openspec/specs/business-rules.md
4. 移動 openspec/changes/<feature>/ 到 openspec/changes/archived/

**產出**: 規格已歸檔，規範庫更新

**暫停點**: 無（AUTO-RUN 模式）

---

#### Phase 3: 測試驗證

**Command**: `/test <feature-name>`

**使用 Agent**: `qa-engineer`

```
Task tool:
  subagent_type: qa-engineer
  prompt: 對「[feature-name]」執行全面測試，包括 API、E2E、整合測試
```

**Agent 工作流程**:
1. 環境檢查（Backend、Frontend 是否運行）
2. 讀取規格（從 OpenSpec）
3. 執行 API 測試
   - 所有端點可訪問
   - Request/Response 符合規格
   - 業務規則生效
   - 錯誤處理正確
4. 執行 Frontend E2E 測試（如有）
   - Playwright 自動化測試
5. 執行整合測試
6. 執行效能測試
7. 生成測試報告

**使用 Skills**:
- `playwright-skill` - 執行 E2E 測試

**產出**:
- `tests/reports/test-report-<timestamp>.md`
- 測試通過率統計
- 問題清單

---

#### Phase 4: 完成功能 (PR 流程)

**Command**: `/feature-finish`

**使用 Agent**: `qa-engineer` (品質檢查)

**工作流程**:

1. **品質檢查** (強制執行)
   ```
   Task tool:
     subagent_type: qa-engineer
     prompt: 執行完整品質檢查，包括測試、覆蓋率、靜態分析
   ```

   **檢查項目**:
   - ✅ Backend Tests: 201/201 passing
   - ✅ Coverage: ≥80%
   - ✅ PHPStan: Level 9 passed
   - ✅ Code Style: PSR-12 compliant

   **如果失敗**: 阻止 PR 創建，輸出問題清單

2. **同步 develop 分支**
   ```bash
   git fetch origin develop
   git rebase origin/develop
   ```

3. **創建 Pull Request**
   ```bash
   gh pr create --base develop --title "..." --body "..."
   ```

   **PR 內容包含**:
   - 變更摘要
   - 品質檢查結果
   - 測試報告
   - OpenSpec 規格連結

4. **指定審查者**

---

#### Phase 5: Code Review

**Command**: `/pr-review <pr-number>`

**不使用 Agent**，由主 Assistant 執行

**審查項目**:
- ✅ 功能性: 實作符合需求
- ✅ 規範性: 遵循專案規範
- ✅ 安全性: 無安全漏洞
- ✅ 性能: 無 N+1、無記憶體洩漏
- ✅ 測試: 測試覆蓋充足
- ✅ 文檔: OpenSpec 規格完整

**產出**: Review 評論和建議

---

#### Phase 6: 部署

##### 6.1 部署到 Staging (自動)

**觸發**: PR 合併到 develop

**執行**: GitHub Actions 自動部署

**流程**:
1. 程式碼品質檢查
2. 執行測試
3. 安全掃描
4. 構建 Docker 映像
5. 部署到 Staging
6. 健康檢查

---

##### 6.2 部署到 Production (手動)

**Command**: `/deploy production`

**使用 Agent**: `devops-engineer`

```
Task tool:
  subagent_type: devops-engineer
  prompt: 部署當前版本到 production，執行 Zero-downtime 部署並驗證健康
```

**Agent 工作流程**:
1. 部署前檢查
   - 所有測試通過
   - 無 critical issues
   - 資料庫備份完成
   - Staging 測試成功
2. 備份
   - 資料庫備份
   - 儲存備份
3. Blue-Green 部署
   - 啟動 Green 環境
   - 執行 migrations
   - 健康檢查
   - 切換流量
   - 關閉 Blue
4. 部署後驗證
   - 健康檢查
   - Smoke 測試
   - 監控指標
5. 通知團隊

**產出**: 部署完成報告

---

## 🔄 工作流程 2: Frontend 功能開發

### 流程圖

```mermaid
graph TD
    A[用戶: 我要開發 Frontend 功能] --> B[/feature-start]
    B --> C[Git: 創建 feature 分支]
    C --> D[/implement-frontend 功能描述]

    D --> E1[Step 1: Proposal]
    E1 --> E2[使用 requirements-analyst agent]
    E2 --> E3[UI/UX 需求訪談 → proposal.md]
    E3 --> E4[✋ 用戶確認]

    E4 --> F1[Step 2: Specifications]
    F1 --> F2[使用 product-designer agent]
    F2 --> F3[UI/UX 設計 → specs/*.md]

    F3 --> G1[Step 3: Tasks]
    G1 --> G2[拆解 UI 任務 → tasks.md]

    G2 --> H1[Step 4: Validate]
    H1 --> H2[驗證 UI/UX 規格]
    H2 --> H3[✋ 用戶確認啟動 AUTO-RUN]

    H3 --> I1[Step 5: Implement 🤖 AUTO-RUN]
    I1 --> I2[使用 react-specialist agent]
    I2 --> I3[實作組件 + 頁面]

    I3 --> J1[Step 6: Archive]
    J1 --> J2[歸檔到 Frontend 規範庫]

    J2 --> K[/test]
    K --> K1[使用 qa-engineer agent]
    K1 --> K2[E2E 測試 + 視覺測試]

    K2 --> L[/feature-finish]
    L --> M[後續流程同 Backend]
```

### 與 Backend 流程的差異

| 階段 | Backend | Frontend |
|------|---------|----------|
| **Step 2: Specs** | software-architect | product-designer |
| **規格產出** | api.md, data-model.md, business-rules.md | ui-ux.md, components.md, pages.md, api-integration.md, state-routing.md |
| **Step 5: Implement** | laravel-specialist | react-specialist |
| **實作內容** | Controllers, Models, Tests | Components, Pages, Hooks |
| **測試重點** | PHPUnit API 測試 | Playwright E2E 測試 |

---

### Step 2: Frontend Specifications (UI/UX 設計)

**使用 Agent**: `product-designer`

```
Task tool:
  subagent_type: product-designer
  prompt: 設計「[功能描述]」的完整 UI/UX，包括設計系統、組件和頁面
```

**Agent 工作流程**:
1. 使用者研究
   - 目標用戶角色
   - 使用情境分析
2. 資訊架構
   - 頁面結構
   - 導航設計
3. 互動設計
   - 使用者流程
   - 狀態設計（Loading、Error、Empty）
4. 視覺設計
   - 設計系統（色彩、字體、間距）
   - 組件規格
5. 響應式設計
   - 斷點設計
   - 行動裝置優化
6. 無障礙設計
   - 鍵盤導航
   - Screen Reader 友善

**產出**:
- `specs/ui-ux.md` - UI/UX 設計規格
- `specs/components.md` - 組件規格
- `specs/pages.md` - 頁面規格
- `specs/api-integration.md` - API 整合方式
- `specs/state-routing.md` - 狀態管理與路由

---

### Step 5: Frontend Implementation (組件實作)

**使用 Agent**: `react-specialist`

```
Task tool:
  subagent_type: react-specialist
  prompt: 按照 tasks.md 實作所有組件和頁面，確保 Type-Safe 和 High-Performance
```

**Agent 工作流程**:
1. 初始化 AUTO-RUN 模式
2. 實作組件
   - TypeScript 型別定義
   - Props 驗證
   - 事件處理
3. 實作頁面
   - 路由配置
   - 資料獲取（React Query）
   - Loading/Error 狀態
4. API 整合
   - API Client 設置
   - Custom Hooks
5. 效能優化
   - React.memo
   - useMemo / useCallback
   - Code Splitting
6. 無障礙優化
   - ARIA 屬性
   - 鍵盤導航

**Agent 特性**:
- ✅ TypeScript 嚴格模式
- ✅ React 效能優化
- ✅ Next.js 13+ App Router
- ✅ Tailwind CSS
- ✅ shadcn/ui 組件庫

**使用 Skills** (可選):
- `frontend-design` - 精緻的 UI 設計
- `artifacts-builder` - 複雜多組件 UI

---

## 🔄 工作流程 3: 全棧功能開發

完整的全棧功能開發需要依序完成 Backend 和 Frontend：

```
┌─────────────────────────────────────────────────────────┐
│              全棧功能開發流程                             │
└─────────────────────────────────────────────────────────┘

Phase 1: Backend API 開發
  ↓
  /feature-start add-rating-api
  ↓
  /implement 新增業務員評分與評論 API
    → Step 1-6 (使用 laravel-specialist)
  ↓
  /test add-rating-api (使用 qa-engineer)
  ↓
  /feature-finish
  ↓
  PR 合併到 develop
  ↓
  自動部署到 Staging

Phase 2: Frontend UI 開發
  ↓
  /feature-start add-rating-ui
  ↓
  /implement-frontend 新增評分 UI 組件與頁面
    → Step 1-6 (使用 react-specialist)
  ↓
  /test add-rating-ui (使用 qa-engineer + playwright-skill)
  ↓
  /feature-finish
  ↓
  PR 合併到 develop
  ↓
  自動部署到 Staging

Phase 3: 整合測試
  ↓
  /test integration (使用 qa-engineer)
    → 測試 Frontend + Backend 整合
  ↓
  確認所有測試通過

Phase 4: Production 部署
  ↓
  /deploy production (使用 devops-engineer)
  ↓
  監控上線狀態
```

### 為什麼要分離 Backend 和 Frontend？

**優點**:
1. ✅ **並行開發**: Backend 和 Frontend 可由不同人並行開發
2. ✅ **獨立測試**: API 測試和 UI 測試分開進行
3. ✅ **獨立部署**: Backend 和 Frontend 可獨立部署和回滾
4. ✅ **清晰界限**: API 契約明確，減少溝通成本
5. ✅ **規格分離**: API 規格和 UI 規格各自獨立歸檔

**缺點**:
1. ❌ **時間較長**: 需要兩個完整的 SDD 流程
2. ❌ **整合複雜**: 需要額外的整合測試

**建議**:
- 小型功能: 可以在同一個 PR 中完成（但仍分開撰寫規格）
- 大型功能: 建議分離為兩個 PR

---

## 📋 Commands 與 Agents/Skills 對應表

### Commands 使用 Agents/Skills 總覽

| Command | 使用 Agent | 使用 Skill | 階段 |
|---------|-----------|-----------|------|
| `/feature-start` | - | - | Git Flow |
| `/implement` (Step 1) | requirements-analyst | - | Proposal |
| `/implement` (Step 2) | software-architect | - | Specifications |
| `/implement` (Step 5) | laravel-specialist | - | Implement (Backend) |
| `/implement-frontend` (Step 1) | requirements-analyst | - | Proposal |
| `/implement-frontend` (Step 2) | product-designer | - | Specifications (UI/UX) |
| `/implement-frontend` (Step 5) | react-specialist | frontend-design* | Implement (Frontend) |
| `/test` | qa-engineer | playwright-skill | Testing |
| `/feature-finish` | qa-engineer | - | Quality Check |
| `/pr-review` | - | - | Code Review |
| `/deploy` | devops-engineer | - | Deployment |
| `/setup-cicd` | devops-engineer | - | Infrastructure |
| `/setup-monitoring` | devops-engineer | - | Monitoring |

*Skills 為可選使用

---

## 🎯 實際使用場景

### 場景 1: 開發新功能（評分系統）

```bash
# 第 1 天: Backend API 開發
/feature-start add-rating-api
/implement 新增業務員評分與評論功能
# → 自動完成 Step 1-6 (1-2 小時)

/test add-rating-api
# → qa-engineer 執行測試 (10-15 分鐘)

/feature-finish
# → 品質檢查 + 創建 PR (5 分鐘)

# PR Review → 合併到 develop → 自動部署 Staging

# 第 2 天: Frontend UI 開發
/feature-start add-rating-ui
/implement-frontend 新增評分 UI 組件與頁面
# → 自動完成 Step 1-6 (1-2 小時)

/test add-rating-ui
# → qa-engineer + playwright-skill (15-20 分鐘)

/feature-finish
# → PR → 合併 → 自動部署 Staging

# 第 3 天: 整合測試與上線
/test integration
# → 測試 Backend + Frontend 整合

/deploy production
# → devops-engineer 執行部署 (5-10 分鐘)
```

**總時間**: 約 3 天（實際開發時間 3-4 小時）

---

### 場景 2: 修復 Bug

```bash
# Bug: 購物車計算錯誤

/feature-start fix-cart-calculation
/implement 修復購物車折扣計算錯誤

# → Step 1: 建立 proposal
#    - 描述 Bug 現象
#    - 重現步驟
#    - 根本原因
#    - 修復方案

# → Step 2-6: 實作修復 (laravel-specialist)

/test fix-cart-calculation
# → 回歸測試

/feature-finish
# → 創建 PR

# 緊急 Bug?
/deploy production --hotfix
# → 快速部署到生產
```

---

### 場景 3: 優化效能

```bash
# 優化業務員搜尋效能

/feature-start optimize-search-performance

/implement 優化業務員搜尋查詢效能
# → Step 2: software-architect 設計優化方案
#    - 添加索引
#    - 優化查詢
#    - 添加快取

# → Step 5: laravel-specialist 實作優化

/test optimize-search-performance
# → qa-engineer 執行效能測試
#    - 回應時間對比
#    - 負載測試

/feature-finish
/deploy production
```

---

## 🔧 進階使用技巧

### 技巧 1: 精細控制開發流程

⚠️ **注意**: 單步驟 commands 已整合到自動化流程中。

**建議方式**:

```bash
# 方式 1: 使用自動化 commands（推薦）
/auto-develop 新增通知系統
# 或
/implement 新增通知系統

# 方式 2: 直接編輯規格文件
# 如需更精細控制，可以：
# 1. 手動創建 openspec/changes/<feature>/ 目錄
# 2. 手動編輯 proposal.md, specs/*.md
# 3. 然後使用 /implement 或 /auto-develop 繼續

# 方式 3: 只執行測試
/test notification-system
```

---

### 技巧 2: 並行開發多個功能

```bash
# Terminal 1: Backend API
/implement 新增評論功能

# Terminal 2: Frontend UI（同時進行）
/implement-frontend 新增評論 UI
```

---

### 技巧 3: 使用特定 Skill

當需要特別精緻的 UI：

```bash
/implement-frontend 新增 Dashboard 首頁

# 在 Step 5 時，react-specialist 會自動考慮使用 frontend-design skill
# 如需強制使用，在 prompt 中指定：
# "使用 frontend-design skill 創建精緻的 Dashboard UI"
```

---

### 技巧 4: 僅執行特定類型測試

```bash
# 只測試 API
/test api-only

# 只測試 Frontend E2E
/test e2e-only

# 只測試整合
/test integration-only

# 只測試效能
/test performance
```

---

## 📊 成功指標

### 開發效率

- ⏱️ Backend 功能: 1-2 小時（AUTO-RUN 模式）
- ⏱️ Frontend 功能: 1-2 小時（AUTO-RUN 模式）
- ⏱️ 測試時間: 10-20 分鐘
- ⏱️ 部署時間: 5-10 分鐘

### 品質指標

- ✅ Backend 測試覆蓋率: ≥80%
- ✅ PHPStan: Level 9 (0 errors)
- ✅ Frontend TypeScript: 0 errors
- ✅ E2E 測試通過率: ≥90%

### 流程完整性

- ✅ 100% 功能有 OpenSpec 規格
- ✅ 100% PR 通過品質檢查
- ✅ 100% 部署有健康檢查

---

## 🚀 最佳實踐

### 1. 總是使用完整流程

❌ **錯誤做法**:
```bash
# 直接開始寫程式碼，沒有規格
vim app/Controllers/RatingController.php
```

✅ **正確做法**:
```bash
# 完整的 SDD 流程
/implement 新增評分功能
```

---

### 2. 善用 AUTO-RUN 模式

✅ **優點**:
- 省時省力
- 自動錯誤修復
- 確保流程完整

⚠️ **注意**:
- Step 1 (Proposal) 必須仔細確認
- Step 4 (Validate) 是最後確認點

---

### 3. 測試驅動品質

✅ **在 feature-finish 之前**:
```bash
/test <feature-name>
```

確保所有測試通過再創建 PR。

---

### 4. 規格是唯一真相來源

所有實作必須基於 OpenSpec 規格：

- ✅ 實作前讀取規格
- ✅ 實作中遵循規格
- ✅ 實作後對照規格驗證

---

### 5. 持續更新規範庫

每次功能完成後，規格會自動歸檔：

```
openspec/specs/
├── api/
│   └── endpoints.md        # 所有 API 端點
├── models/
│   └── data-models.md      # 所有資料模型
├── business-rules.md       # 所有業務規則
└── frontend/
    ├── ui-components.md    # 所有 UI 組件
    └── pages.md            # 所有頁面
```

---

## 📚 相關文檔

### Commands 文檔
- [README.md](./README.md) - Commands 使用指南
- [implement.md](./implement.md) - Backend SDD 流程
- [implement-frontend.md](./implement-frontend.md) - Frontend SDD 流程
- [test.md](./test.md) - 測試流程
- [deploy.md](./deploy.md) - 部署流程

### Agents 文檔
- [requirements-analyst.md](../.claude/agents/requirements-analyst.md) - 需求分析
- [software-architect.md](../.claude/agents/software-architect.md) - 技術架構
- [product-designer.md](../.claude/agents/product-designer.md) - UI/UX 設計
- [laravel-specialist.md](../.claude/agents/laravel-specialist.md) - Laravel 實作
- [react-specialist.md](../.claude/agents/react-specialist.md) - React 實作
- [qa-engineer.md](../.claude/agents/qa-engineer.md) - QA 測試
- [devops-engineer.md](../.claude/agents/devops-engineer.md) - DevOps

### Skills 文檔
- [playwright-skill](../.claude/skills/playwright-skill/SKILL.md) - E2E 測試
- [frontend-design](../.claude/skills/frontend-design/SKILL.md) - 前端設計
- [php-pro](../.claude/skills/php-pro/SKILL.md) - PHP 專家

---

## 🎓 學習路徑

### 新手入門
1. 閱讀 [README.md](./README.md)
2. 執行第一個功能：`/implement 新增測試功能`
3. 理解 6 個步驟的流程
4. 體驗 AUTO-RUN 模式

### 進階使用
1. 學習手動控制每個步驟
2. 理解各個 Agent 的職責
3. 學習如何優化規格品質
4. 掌握測試和部署流程

### 專家級別
1. 自定義 Agent 行為
2. 優化工作流程
3. 建立團隊最佳實踐
4. 持續改進規範庫

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0 - 完整工作流程版
