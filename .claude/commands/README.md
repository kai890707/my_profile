# OpenSpec Commands 使用指南

**版本**: 3.1
**最後更新**: 2026-01-10
**開發方法**: Specification-Driven Development (SDD)

---

## 📚 目錄

1. [快速開始](#快速開始)
2. [Commands 總覽](#commands-總覽)
3. [完整流程 Commands](#完整流程-commands)
4. [單步驟 Commands](#單步驟-commands)
5. [輔助工具 Commands](#輔助工具-commands)
6. [工作流程圖](#工作流程圖)
7. [使用範例](#使用範例)

---

## 🚀 快速開始

### 開發新功能 (推薦)

```bash
# Backend 功能
/implement [功能描述]

# Frontend 功能
/implement-frontend [功能描述]
```

這兩個 commands 會自動完成完整的 SDD 流程 (6 個步驟)。

### 手動控制每個步驟

如果需要更精細的控制,可以使用單步驟 commands:

```bash
/proposal [功能描述]        # Step 1
/spec <feature-name>         # Step 2
/tasks <feature-name>        # Step 3 (新增)
/validate <feature-name>     # Step 4 (新增)
/develop <feature-name>      # Step 5
/archive <feature-name>      # Step 6
```

---

## 📋 Commands 總覽

### 依用途分類

| 分類 | Commands | 用途 |
|------|----------|------|
| **Git Flow** | `git-flow-init`, `feature-start`, `feature-finish`, `release-start`, `release-finish`, `hotfix-start`, `hotfix-finish` | Git 分支管理和發布流程 |
| **代碼審查** | `pr-review` | Pull Request 審查流程 |
| **自動化流程** | `implement`, `implement-frontend` | 一鍵完成完整 SDD 流程 |
| **提案階段** | `proposal` | 建立變更提案並確認需求 |
| **規格階段** | `spec` | 撰寫詳細技術規格 |
| **任務階段** | `tasks` | 拆解實作任務清單 |
| **驗證階段** | `validate` | 驗證規格完整性 |
| **實作階段** | `develop` | 按規格實作程式碼 |
| **歸檔階段** | `archive` | 歸檔到規範庫 |
| **輔助工具** | `status`, `test`, `docs` | 查看狀態、測試、生成文檔 |

### 依開發階段分類

```
需求階段:  /proposal
規格階段:  /spec → /tasks → /validate
實作階段:  /develop → /test
歸檔階段:  /archive
```

---

## 🌳 Git Flow Commands

### `/git-flow-init` - 初始化 Git Flow

**文件**: [git-flow-init.md](./git-flow-init.md)

**用途**: 初始化專案的 Git Flow 工作流程

**語法**:
```bash
/git-flow-init
```

**執行操作**:
- 創建 `develop` 分支
- 設置分支保護規則
- 配置 Git hooks
- 創建初始標籤

---

### `/feature-start` - 開始新功能

**文件**: [feature-start.md](./feature-start.md)

**用途**: 創建 feature 分支並開始功能開發

**語法**:
```bash
/feature-start <feature-name>
```

**範例**:
```bash
/feature-start add-rating-api
```

---

### `/feature-finish` - 完成功能

**文件**: [feature-finish.md](./feature-finish.md)

**用途**: 完成功能開發，創建 Pull Request

**語法**:
```bash
/feature-finish
```

**執行操作**:
- 檢查測試和代碼規範
- 同步 develop 分支
- 創建 Pull Request
- 指定審查者

---

### `/pr-review` - 審查 Pull Request

**文件**: [pr-review.md](./pr-review.md)

**用途**: 執行 Pull Request 代碼審查

**語法**:
```bash
/pr-review <pr-number>
```

**範例**:
```bash
/pr-review 123
```

**審查內容**:
- 功能性審查
- 代碼質量審查
- 規範審查
- 安全性審查
- 性能審查
- 測試審查
- 文檔審查
- API 兼容性審查

---

## 🔄 完整流程 Commands

### 1. `/implement` - Backend 完整 SDD 流程

**用途**: 自動完成 Backend 功能的完整開發流程

**語法**:
```bash
/implement [功能描述]
```

**執行流程**:
```
Step 1: Create Proposal
    → 使用 AskUserQuestion 確認需求
    → 產出 openspec/changes/<feature>/proposal.md

Step 2: Write Specifications
    → 撰寫 API 規格 (api.md)
    → 撰寫資料模型 (data-model.md)
    → 撰寫業務規則 (business-rules.md)

Step 3: Break Down Tasks
    → 拆解實作任務
    → 產出 tasks.md

Step 4: Validate Specs
    → 完整性檢查
    → 一致性檢查
    → 清晰性檢查

Step 5: Implement
    → 使用 TodoWrite 追蹤進度
    → 嚴格按照規格實作
    → 逐步驗證功能

Step 6: Archive
    → 合併規格到 openspec/specs/
    → 移動到 archived/
```

**範例**:
```bash
/implement 新增業務員評分與評論功能
```

**產出**:
- `openspec/changes/rating-feature/proposal.md`
- `openspec/changes/rating-feature/specs/api.md`
- `openspec/changes/rating-feature/specs/data-model.md`
- `openspec/changes/rating-feature/specs/business-rules.md`
- `openspec/changes/rating-feature/tasks.md`
- 完整的程式碼實作
- 歸檔到 `openspec/specs/`

---

### 2. `/implement-frontend` - Frontend 完整 SDD 流程

**用途**: 自動完成 Frontend 功能的完整開發流程

**語法**:
```bash
/implement-frontend [功能描述]
```

**執行流程**:
```
Step 1: Create Proposal
    → 確認 UI/UX 需求
    → 產出 proposal.md

Step 2: Write Specifications
    → 撰寫 UI/UX 規格 (ui-ux.md)
    → 撰寫組件規格 (components.md)
    → 撰寫頁面規格 (pages.md)
    → 撰寫 API 整合規格 (api-integration.md)
    → 撰寫狀態管理規格 (state-routing.md)

Step 3: Break Down Tasks
    → 拆解 UI 開發任務
    → 產出 tasks.md

Step 4: Validate Specs
    → UI/UX 完整性
    → 組件可實作性
    → API 整合明確性

Step 5: Implement
    → 實作組件和頁面
    → 整合 API
    → 視覺驗證

Step 6: Archive
    → 合併到 openspec/specs/frontend/
    → 歸檔變更
```

**範例**:
```bash
/implement-frontend 新增評分 UI 組件與頁面
```

**產出**:
- `openspec/changes/rating-ui/proposal.md`
- `openspec/changes/rating-ui/specs/ui-ux.md`
- `openspec/changes/rating-ui/specs/components.md`
- `openspec/changes/rating-ui/specs/pages.md`
- `openspec/changes/rating-ui/specs/api-integration.md`
- `openspec/changes/rating-ui/specs/state-routing.md`
- `openspec/changes/rating-ui/tasks.md`
- 完整的前端程式碼
- 歸檔到 `openspec/specs/frontend/`

---

## 🔧 單步驟 Commands

當需要更精細的控制時,可以使用單步驟 commands。

### Step 1: `/proposal` - 建立變更提案

**文件**: [proposal.md](./proposal.md)

**用途**: 建立清晰的變更提案,明確需求和範圍

**語法**:
```bash
/proposal [功能描述]
```

**產出**: `openspec/changes/<feature>/proposal.md`

**關鍵內容**:
- Why (問題陳述)
- What (解決方案)
- Scope (In Scope / Out of Scope)
- Success Criteria (驗收標準)
- Dependencies (相依性)
- Risks (風險評估)

---

### Step 2: `/spec` - 撰寫詳細規格

**文件**: [spec.md](./spec.md)

**用途**: 撰寫詳細到「無需解釋就能實作」的技術規格

**語法**:
```bash
/spec <feature-name>
```

**產出**:
- Backend: `api.md`, `data-model.md`, `business-rules.md`
- Frontend: `ui-ux.md`, `components.md`, `pages.md`, `api-integration.md`, `state-routing.md`

**規格要求**:
- API: 完整的 Request/Response 範例
- 資料模型: 完整的 Migration 程式碼
- 業務規則: 明確的驗證邏輯
- UI/UX: 詳細的設計系統和組件規格

---

### Step 3: `/tasks` - 拆解實作任務

**文件**: [tasks.md](./tasks.md) ✨ 新增

**用途**: 將規格拆解為可執行的原子任務

**語法**:
```bash
/tasks <feature-name>
```

**產出**: `openspec/changes/<feature>/tasks.md`

**任務拆解原則**:
- 每個任務獨立可完成
- 任務有明確的驗收標準
- 任務按依賴順序排列
- 任務預估工作量 (S/M/L)

---

### Step 4: `/validate` - 驗證規格完整性

**文件**: [validate.md](./validate.md) ✨ 新增

**用途**: 在實作前驗證規格是否完整、一致、清晰

**語法**:
```bash
/validate <feature-name>
```

**驗證項目**:
- ✅ 完整性: 所有必要章節都已填寫
- ✅ 一致性: 規格之間無衝突
- ✅ 清晰性: 無歧義,可直接實作
- ✅ 可測性: 有明確的驗收標準

---

### Step 5: `/develop` - 實作開發

**文件**: [develop.md](./develop.md)

**用途**: 嚴格按照規格和任務清單實作程式碼

**語法**:
```bash
/develop <feature-name>
```

**實作原則**:
- ❌ 禁止偏離規格
- ✅ 使用 TodoWrite 追蹤進度
- ✅ 每個任務完成立即驗證
- ✅ 嚴格遵循規格定義

---

### Step 6: `/archive` - 歸檔到規範庫

**文件**: [archive.md](./archive.md)

**用途**: 將完成的變更歸檔到 OpenSpec 規範庫

**語法**:
```bash
/archive <feature-name>
```

**執行操作**:
1. 合併 API 規格到 `openspec/specs/api/endpoints.md`
2. 合併資料模型到 `openspec/specs/models/data-models.md`
3. 合併業務規則到 `openspec/specs/business-rules.md`
4. 移動變更到 `openspec/changes/archived/`

---

## 🛠️ 輔助工具 Commands

### `/status` - 查看開發狀態

**文件**: [utils/status.md](./utils/status.md) ✨ 新增

**用途**: 查看當前所有活躍的開發項目狀態

**語法**:
```bash
/status
/status <feature-name>  # 查看特定功能狀態
```

**顯示內容**:
- 活躍的變更提案列表
- 每個提案的完成階段
- 待辦任務統計
- 已完成/總任務數

---

### `/test` - 執行測試

**文件**: [utils/test.md](./utils/test.md) ✨ 新增

**用途**: 執行自動化測試並生成報告

**語法**:
```bash
/test              # 執行所有測試
/test backend      # 僅 Backend 測試
/test frontend     # 僅 Frontend 測試
/test api          # 僅 API 測試
```

**測試類型**:
- Backend: PHPUnit 單元測試 + API 端點測試
- Frontend: Vitest + React Testing Library + Playwright E2E
- Integration: 跨系統整合測試

---

### `/docs` - 生成文檔

**文件**: [utils/docs.md](./utils/docs.md) ✨ 新增

**用途**: 從 OpenSpec 規格自動生成 API 文檔

**語法**:
```bash
/docs              # 生成所有文檔
/docs api          # 生成 API 文檔
/docs frontend     # 生成 Frontend 組件文檔
```

**產出格式**:
- Swagger/OpenAPI 3.0
- Markdown 格式
- HTML 靜態網站

---

## 📊 工作流程圖

詳細的工作流程圖請參考: [WORKFLOW.md](./WORKFLOW.md)

### 快速流程圖

```
┌─────────────────────────────────────────────────────┐
│            開發新功能                                │
└─────────────────────────────────────────────────────┘
                      │
                      ▼
         ┌────────────────────────┐
         │ Backend 還是 Frontend?  │
         └────────────────────────┘
              │              │
              │              │
        Backend          Frontend
              │              │
              ▼              ▼
      /implement    /implement-frontend
              │              │
              └──────┬───────┘
                     │
                     ▼
         ┌──────────────────────┐
         │  自動執行 6 個步驟    │
         └──────────────────────┘
                     │
         ┌───────────┴───────────┐
         ▼                       ▼
    ┌─────────┐            ┌─────────┐
    │ Proposal│            │  Specs  │
    └─────────┘            └─────────┘
         │                       │
         ▼                       ▼
    ┌─────────┐            ┌─────────┐
    │  Tasks  │            │Validate │
    └─────────┘            └─────────┘
         │                       │
         └───────────┬───────────┘
                     ▼
              ┌─────────────┐
              │   Develop   │
              └─────────────┘
                     │
                     ▼
              ┌─────────────┐
              │   Archive   │
              └─────────────┘
                     │
                     ▼
              ┌─────────────┐
              │  完成 ✅     │
              └─────────────┘
```

---

## 💡 使用範例

### 範例 1: 開發評分功能 (自動化)

```bash
# 一鍵完成 Backend
/implement 新增業務員評分與評論功能

# 一鍵完成 Frontend
/implement-frontend 新增評分 UI 組件
```

**執行過程**:
1. 自動詢問需求細節 (AskUserQuestion)
2. 產出 proposal.md 並確認
3. 撰寫完整規格 (api.md, data-model.md, etc.)
4. 拆解任務 (tasks.md)
5. 驗證規格完整性
6. 實作程式碼 (使用 TodoWrite 追蹤)
7. 執行測試
8. 歸檔到規範庫

**預估時間**: Backend 2-3 小時, Frontend 2-3 小時

---

### 範例 2: 精細控制每個步驟 (手動)

```bash
# Step 1: 建立提案
/proposal 優化搜尋效能

# Step 2: 撰寫規格
/spec optimize-search-performance

# Step 3: 拆解任務
/tasks optimize-search-performance

# Step 4: 驗證規格
/validate optimize-search-performance

# Step 5: 實作開發
/develop optimize-search-performance

# Step 6: 測試
/test

# Step 7: 歸檔
/archive optimize-search-performance
```

**適用場景**:
- 複雜功能需要分階段執行
- 需要多人協作 (一人寫規格,另一人實作)
- 需要暫停並稍後繼續
- 學習 SDD 流程的每個細節

---

### 範例 3: 查看狀態和測試

```bash
# 查看所有活躍項目
/status

# 查看特定項目
/status rating-feature

# 執行測試
/test

# 生成文檔
/docs
```

---

## 📚 進階使用

### 並行開發多個功能

```bash
# Terminal 1: 開發 Backend
/implement 新增通知系統

# Terminal 2: 開發 Frontend
/implement-frontend 新增通知 UI
```

### 修復 Bug 的流程

```bash
# Bug 也使用相同流程
/implement 修復購物車計算錯誤
```

**建議**:
- Bug 修復的 proposal 應包含重現步驟
- Scope 應聚焦修復,避免範圍蔓延
- 測試應包含回歸測試

---

## 🎯 最佳實踐

### 1. 使用自動化流程

✅ **推薦**: 使用 `/implement` 和 `/implement-frontend`
- 省時省力
- 確保流程完整
- 自動追蹤進度

❌ **不推薦**: 手動執行每個步驟 (除非有特殊需求)

### 2. 善用 AskUserQuestion

- 任何不確定的地方都要問
- 寧可多問,也不要猜測
- 確認後再繼續下一步

### 3. 規格要詳細

- API 要有完整的 Request/Response 範例
- 資料模型要有完整的 Migration 程式碼
- 業務規則要有明確的驗證邏輯

### 4. 小步快跑

- 每個功能應該可以在 1-3 天內完成
- 太大的功能應該拆分
- 頻繁歸檔,保持規範庫最新

---

## 🔗 相關文檔

### 工作流程文檔 ✨ 新增
- [.claude/workflows/GIT_FLOW.md](../.claude/workflows/GIT_FLOW.md) - **Git Flow 完整指南**
- [.claude/workflows/DEVELOPMENT.md](../.claude/workflows/DEVELOPMENT.md) - **完整開發流程**
- [WORKFLOW.md](./WORKFLOW.md) - Commands 工作流程圖

### 專案開發規範
- [README.md](../../README.md) - 專案總覽
- [my_profile_laravel/README.md](../../my_profile_laravel/README.md) - Laravel Backend 開發規範
- [frontend/README.md](../../frontend/README.md) - Next.js Frontend 開發規範
- [frontend/CLAUDE.md](../../frontend/CLAUDE.md) - Frontend OpenSpec 規範

### 技術標準
- [.claude/skills/php-pro/SKILL.md](../.claude/skills/php-pro/SKILL.md) - PHP Pro 標準（Laravel 遷移專用）

### OpenSpec 規格
- [openspec/specs/api/endpoints.md](../../openspec/specs/api/endpoints.md) - Backend API 規範
- [openspec/specs/models/data-models.md](../../openspec/specs/models/data-models.md) - 資料模型規範
- [openspec/specs/frontend/](../../openspec/specs/frontend/) - Frontend 規範

### Frontend 文檔
- [frontend/docs/](../../frontend/docs/) - Frontend 技術文檔
- [frontend/reports/](../../frontend/reports/) - Frontend 開發報告

### 歷史報告
- [docs/reports/](../../docs/reports/) - 專案開發報告歸檔

---

## 📝 更新日誌

### Version 3.1 (2026-01-10) ✅ **Laravel 遷移完成版**
- 🎉 **移除架構遷移 Commands** (遷移已 100% 完成):
  - 移除 `/migration-start` 和 `/migration-finish`
  - Laravel 11 遷移已完成 (31 APIs, 201 tests, 80%+ coverage)
  - 參考: [MIGRATION_SUMMARY.md](../../my_profile_laravel/MIGRATION_SUMMARY.md)
- 🔄 更新文檔連結，移除已刪除的 CI4 專案參考
- 📚 更新專案結構文檔連結

### Version 3.0 (2026-01-09) 🚀 **新創公司工作流程版**
- ✨ **新增 Git Flow Commands**:
  - `/git-flow-init` - 初始化 Git Flow
  - `/feature-start`, `/feature-finish` - Feature 開發流程
  - `/release-start`, `/release-finish` - 發布流程
  - `/hotfix-start`, `/hotfix-finish` - 緊急修復流程
- ✨ **新增架構遷移 Commands** (Laravel 遷移專用):
  - `/migration-start` - 開始遷移模組
  - `/migration-finish` - 完成遷移（含 API 兼容性測試）
- ✨ **新增代碼審查 Command**:
  - `/pr-review` - Pull Request 審查流程
- 📚 **新增工作流程文檔**:
  - `.claude/workflows/GIT_FLOW.md` - Git Flow 完整指南
  - `.claude/workflows/DEVELOPMENT.md` - 完整開發流程
- 🔄 整合 PHP Pro Skill 標準（Laravel 開發專用）
- 🎯 針對新創公司優化完整開發工作流程

### Version 2.0 (2026-01-09)
- ✨ 新增 Commands README 索引
- ✨ 新增 /tasks command (Step 3)
- ✨ 新增 /validate command (Step 4)
- ✨ 新增輔助工具 (/status, /test, /docs)
- ✨ 新增 WORKFLOW.md 工作流程圖
- 🔄 優化 implement 和 implement-frontend
- 📚 完善使用範例和最佳實踐

### Version 1.0 (2026-01-08)
- ✅ 建立基礎 commands (/implement, /proposal, /spec, /develop, /archive)
- ✅ Frontend SDD 整合 (/implement-frontend)

---

**維護者**: Development Team
**最後更新**: 2026-01-10
**版本**: 3.1 - Laravel 遷移完成版
