# YAMU 開發系統指南

**專案**: YAMU 業務員推廣系統
**架構**: Laravel 11 (Backend) + Next.js 15 (Frontend)
**開發方法**: OpenSpec Specification-Driven Development (SDD)
**版本**: 4.0
**最後更新**: 2026-01-11

---

## 🚀 快速開始

### 5 秒開始開發

```bash
# Backend 功能開發
/implement [功能描述]

# Frontend 功能開發
/implement-frontend [功能描述]

# 執行全面測試
/test [功能名稱]

# 部署到生產環境
/deploy production
```

### 第一次使用？

1. 📖 **[查看工作流程知識庫](./workflows/README.md)** - 完整的開發流程導航
2. 💻 **[閱讀開發指南](./workflows/DEVELOPMENT_GUIDE.md)** - 開發環境設置
3. 🤖 **[了解 Agents 協作](./workflows/AGENTS_INTEGRATION.md)** - AI 如何協助開發

---

## 📚 核心資源

### 🔥 必讀文檔

| 文檔 | 說明 | 何時閱讀 |
|------|------|----------|
| **[工作流程知識庫](./workflows/README.md)** | 所有工作流程的統一入口 | ⭐ 首選導航 |
| **[Commands 使用指南](./commands/README.md)** | 所有可用的 Commands | 需要查找命令時 |
| **[專案總覽](../CLAUDE.md)** | YAMU 專案整體說明 | 了解專案背景 |

### 📖 工作流程知識庫

**位置**: [.claude/workflows/](./workflows/)

| 流程文檔 | 說明 |
|---------|------|
| **[OpenSpec SDD](./workflows/OPENSPEC_SDD.md)** | 規範驅動開發 6 步驟流程 |
| **[Agents 整合](./workflows/AGENTS_INTEGRATION.md)** | 7 個專業 Agents 工作流程 |
| **[Git Flow](./workflows/GIT_FLOW.md)** | 分支管理與版本控制 |
| **[開發指南](./workflows/DEVELOPMENT_GUIDE.md)** | 日常開發、測試、部署 |

📘 **詳見**: [工作流程知識庫完整導航](./workflows/README.md)

---

## 🏗️ 專案架構

```
my_profile/                          # 專案根目錄
├── .claude/                         # 開發系統配置 ⭐ 你在這裡
│   ├── README.md                    # 本文件 - 快速導航
│   ├── workflows/                   # 📚 工作流程知識庫
│   │   ├── README.md                # 知識庫導航 (必讀)
│   │   ├── OPENSPEC_SDD.md          # OpenSpec 規範驅動開發
│   │   ├── AGENTS_INTEGRATION.md    # Agents 整合流程
│   │   ├── GIT_FLOW.md              # Git Flow 工作流程
│   │   └── DEVELOPMENT_GUIDE.md     # 開發指南
│   ├── commands/                    # 開發 Commands
│   │   └── README.md                # Commands 使用指南
│   ├── agents/                      # 專業 Agents
│   │   ├── requirements-analyst.md  # 需求分析專家 (PM)
│   │   ├── software-architect.md    # 軟體架構師
│   │   ├── product-designer.md      # 產品設計師 (UI/UX)
│   │   ├── laravel-specialist.md    # Laravel 框架專家
│   │   ├── react-specialist.md      # React 前端工程師
│   │   ├── qa-engineer.md           # QA 工程師
│   │   └── devops-engineer.md       # DevOps 工程師
│   └── skills/                      # 專業技能
│       ├── php-pro/                 # PHP Pro Skill
│       ├── frontend-design/         # Frontend Design Skill
│       └── playwright-skill/        # Playwright E2E Testing
│
├── my_profile_laravel/              # Backend API (Laravel 11)
│   └── CLAUDE.md                    # Backend 開發規範
│
├── frontend/                        # Frontend SPA (Next.js 15)
│   └── CLAUDE.md                    # Frontend 開發規範
│
└── openspec/                        # OpenSpec 規範庫
    ├── specs/                       # 歸檔的規格
    │   ├── backend/                 # Backend API 規範
    │   └── frontend/                # Frontend UI 規範
    └── changes/                     # 進行中的變更
```

---

## 🎯 開發流程概覽

### OpenSpec SDD 6 步驟

```
┌─────────────────────────────────────────────────────────────┐
│  Step 1: Proposal      → 確認需求，明確範圍                 │
│  Step 2: Specification → 撰寫詳細規格 (API/UI/DB)           │
│  Step 3: Tasks         → 拆解實作任務                       │
│  Step 4: Validate      → 驗證規格完整性                     │
│  Step 5: Implement     → 實作程式碼                         │
│  Step 6: Archive       → 歸檔到規範庫                       │
└─────────────────────────────────────────────────────────────┘
```

📖 **詳細說明**: [OpenSpec SDD 流程](./workflows/OPENSPEC_SDD.md)

### 7 個專業 Agents

```
🤖 requirements-analyst  → Step 1: 需求訪談與分析
🤖 software-architect    → Step 2: Backend 架構設計
🤖 product-designer      → Step 2: Frontend UI/UX 設計
🤖 laravel-specialist    → Step 5: Laravel 實作
🤖 react-specialist      → Step 5: React/Next.js 實作
🤖 qa-engineer           → Testing: 全面測試
🤖 devops-engineer       → Deployment: 部署與運維
```

📖 **詳細說明**: [Agents 整合](./workflows/AGENTS_INTEGRATION.md)

---

## 🔧 常用 Commands

### 開發 Commands

```bash
/implement <功能描述>              # Backend OpenSpec 完整流程
/implement-frontend <功能描述>     # Frontend OpenSpec 完整流程
/test <功能名稱>                   # 執行全面測試
/feature-finish                    # 完成功能，創建 PR
```

### Git Flow Commands

```bash
/feature-start <name>       # 開始新功能 (創建分支)
/feature-finish             # 完成功能 (創建 PR)
/pr-review <pr-number>      # 審查 Pull Request
```

### DevOps Commands

```bash
/deploy production          # 部署到生產環境
/setup-cicd                 # 設置 CI/CD Pipeline
/setup-monitoring           # 設置監控系統
```

📖 **完整列表**: [Commands 使用指南](./commands/README.md)

---

## 🤖 專業 Agents 系統

### 為什麼需要 Agents？

不同開發階段需要不同領域的專業知識：
- **需求分析** → PM 的系統化思維
- **架構設計** → 架構師的系統設計經驗
- **UI/UX 設計** → 設計師的同理心和美學素養
- **Laravel 開發** → 框架專家的最佳實踐
- **React 開發** → 前端專家的效能優化能力
- **品質測試** → QA 工程師的全面測試策略
- **部署運維** → DevOps 工程師的 CI/CD 專業知識

### 7 個 Agents 概覽

| Agent | 角色 | 負責階段 | 詳細文檔 |
|-------|------|---------|---------|
| **requirements-analyst** | 產品經理 | Step 1: Proposal | [文檔](./agents/requirements-analyst.md) |
| **software-architect** | 軟體架構師 | Step 2: Backend Spec | [文檔](./agents/software-architect.md) |
| **product-designer** | 產品設計師 | Step 2: Frontend Spec | [文檔](./agents/product-designer.md) |
| **laravel-specialist** | Laravel 專家 | Step 5: Backend Implement | [文檔](./agents/laravel-specialist.md) |
| **react-specialist** | React 專家 | Step 5: Frontend Implement | [文檔](./agents/react-specialist.md) |
| **qa-engineer** | QA 工程師 | Testing | [文檔](./agents/qa-engineer.md) |
| **devops-engineer** | DevOps 工程師 | Deployment | [文檔](./agents/devops-engineer.md) |

📖 **Agents 工作流程**: [Agents 整合](./workflows/AGENTS_INTEGRATION.md)

---

## 📊 專案統計

### Backend (Laravel 11)
- **API 端點**: 31 個 (完整 RESTful)
- **測試數量**: 201 個 (165 Feature + 36 Unit)
- **測試覆蓋率**: 80%+
- **代碼品質**: PHPStan Level 9
- **文檔覆蓋率**: 100% (OpenAPI 3.1)

### Frontend (Next.js 15)
- **UI 組件**: 30+ 組件
- **頁面路由**: 18 個路由
- **API 整合**: 31 個端點
- **設計系統**: 完整的 Tailwind 設計系統
- **測試框架**: Vitest + React Testing Library + Playwright

### OpenSpec 規範庫
- **API 規格**: 31 個端點完整定義
- **資料模型**: 8 個 Models 完整 Schema
- **Frontend 規格**: UI/UX + 組件 + 頁面 + API 整合
- **業務規則**: 完整的驗證邏輯定義

---

## 🎓 學習路徑

### 新手入門
1. 📖 閱讀 **[工作流程知識庫](./workflows/README.md)**
2. 💻 閱讀 **[開發指南](./workflows/DEVELOPMENT_GUIDE.md)** - 設置環境
3. 🌿 閱讀 **[Git Flow](./workflows/GIT_FLOW.md)** - 了解分支管理
4. 📝 閱讀 **[OpenSpec SDD](./workflows/OPENSPEC_SDD.md)** - 了解開發流程
5. 🤖 閱讀 **[Agents 整合](./workflows/AGENTS_INTEGRATION.md)** - 了解 AI 協助
6. ✅ 實際操作一次完整流程

### 日常開發
```bash
/feature-start <name>       # 1. 創建分支
/implement <description>    # 2. 開發功能
/test                       # 3. 執行測試
/feature-finish             # 4. 創建 PR
/pr-review <number>         # 5. 審查代碼
```

---

## 🎯 核心原則

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

### Laravel 開發必須使用 laravel-specialist Agent

🔴 **重要**: 所有 Laravel 後端開發任務必須使用 `laravel-specialist` agent

**必須使用的場景**:
- ✅ 建立或修改 Controllers、Models、Migrations
- ✅ 實作 Eloquent 關聯
- ✅ 建立 Form Requests、Policies、Middleware
- ✅ 撰寫 API Resources、Service Classes
- ✅ 資料庫查詢優化

詳見: [.claude/agents/laravel-specialist.md](./agents/laravel-specialist.md)

---

## 💡 常見問題

### Q: 應該從哪裡開始？
A: 從 **[工作流程知識庫](./workflows/README.md)** 開始，它會引導你找到需要的文檔。

### Q: 如何開發新功能？
A: 使用 `/implement` (Backend) 或 `/implement-frontend` (Frontend)，系統會自動執行完整的 SDD 流程。

### Q: Agents 會自動執行嗎？
A: 是的，當你使用 `/implement` 或 `/implement-frontend` 時，相關的 Agents 會自動在適當階段啟動。詳見 [Agents 整合](./workflows/AGENTS_INTEGRATION.md)。

### Q: 如何查看開發進度？
A: 使用 `/status` 命令，或查看 `openspec/changes/` 目錄。

### Q: Commands 和 Agents 的區別？
A:
- **Commands**: 用戶手動執行的指令 (`/implement`, `/test` 等)
- **Agents**: Commands 執行時自動啟動的專業 AI 助手

---

## 🔗 延伸閱讀

### 專案核心
- **[專案總覽](../CLAUDE.md)** - YAMU 專案整體說明
- **[Backend 規範](../my_profile_laravel/CLAUDE.md)** - Laravel 開發規範
- **[Frontend 規範](../frontend/CLAUDE.md)** - Next.js 開發規範

### 工作流程
- **[工作流程知識庫](./workflows/README.md)** - 所有流程的統一入口 ⭐
- **[OpenSpec SDD](./workflows/OPENSPEC_SDD.md)** - 規範驅動開發流程
- **[Agents 整合](./workflows/AGENTS_INTEGRATION.md)** - AI 協作流程
- **[Git Flow](./workflows/GIT_FLOW.md)** - 分支管理流程
- **[開發指南](./workflows/DEVELOPMENT_GUIDE.md)** - 日常開發指南

### Commands 與 Agents
- **[Commands 使用指南](./commands/README.md)** - 所有可用的 Commands
- **[Agents 文檔](./agents/)** - 7 個專業 Agents 詳細說明

### OpenSpec 規範庫
- **[Backend API 規範](../openspec/specs/backend/)** - API 端點規範
- **[Frontend UI 規範](../openspec/specs/frontend/)** - UI 組件規範

---

## 🌟 系統優勢

| 優勢 | 說明 |
|------|------|
| **規範驅動** | 規格先行，降低幻覺和錯誤 |
| **AI 協作** | 7 個專業 Agents 全程協助 |
| **系統化** | 流程標準化，可追溯 |
| **品質保證** | 多重驗證，減少錯誤 |
| **可維護** | 規範庫持續更新 |
| **專業分工** | Backend/Frontend 分離開發 |
| **Git Flow** | 完整的分支管理和發布流程 |
| **測試保障** | 201 測試，80%+ 覆蓋率 |
| **文檔完整** | OpenAPI 文檔自動生成 |

---

## 🚀 開始你的第一個功能

```bash
# 1. 閱讀工作流程知識庫
cat .claude/workflows/README.md

# 2. 開始新功能
/feature-start my-first-feature

# 3. 開發功能 (自動執行 SDD 6 步驟)
/implement 新增我的第一個功能

# 4. 測試
/test my-first-feature

# 5. 完成
/feature-finish

# 6. 審查
/pr-review <pr-number>
```

---

**維護者**: Development Team
**最後更新**: 2026-01-11
**版本**: 4.0 - 簡化版，統一工作流程知識庫
