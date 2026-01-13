---
category: workflow
tags: [sdd, specification-driven, development-process, openspec]
priority: high
last_updated: 2026-01-13
applies_to: All YAMU development
related_docs: [git-workflow.md, ../backend/architecture.md, ../frontend/architecture.md]
---

# Specification-Driven Development (SDD) 流程

## Quick Reference

- SDD 核心原則: 先寫規格，再寫程式碼
- 規格存放位置: `openspec/changes/<feature-name>/`
- 資料夾命名: `YYYYMMDD-action-description`
- 自動化指令: `/auto-develop`, `/implement`, `/implement-frontend`
- 規格必須包含: Proposal, API Specs, DB Schema, Tests, UI/UX
- 完成後歸檔: 歸檔到 `openspec/specs/`

## 使用場景

**適用於**:
- 任何新功能開發（Backend, Frontend, Full-stack）
- 需要多人協作的功能
- 複雜的架構變更
- 需要明確設計決策的場景

**不適用於**:
- 緊急修復（改幾行代碼）
- 文案修改（只改文字）
- 簡單的樣式微調（只改 CSS）
- 配置調整（改設定檔）

## 核心概念

Specification-Driven Development (SDD) 是 YAMU 專案的核心開發方法論，確保所有開發工作都有明確的規格作為基礎。

SDD 的核心理念是「先思考，後實作」：在撰寫任何程式碼之前，必須先完整地定義功能的需求、設計、API、資料結構和測試策略。這樣可以：
1. 減少返工和重構
2. 確保團隊對需求的理解一致
3. 提供清晰的實作依據
4. 建立可追溯的文檔

所有規格統一存放在 `openspec/` 目錄，使用 Markdown 格式，版本控制納入 Git。

## SDD 完整流程

### 階段 1: 需求分析 (Proposal)

**目標**: 將用戶需求轉化為明確的功能提案

**步驟**:
1. 與需求方（用戶/PM）對話
2. 使用 `requirements-analyst` agent 進行需求訪談
3. 產出 `proposal.md`

**Proposal 必須包含**:
- 功能描述與目標
- 使用者故事 (User Stories)
- 成功標準 (Acceptance Criteria)
- 技術範圍判斷（Backend/Frontend/Full-stack）
- 初步的技術方案

**範例對話**:
```
User: 我需要新增評分功能
Agent (requirements-analyst):
  - 評分範圍？(1-5 星)
  - 需要評論？(可選文字評論)
  - 誰可以評分？(已登入用戶)
  - 是否可以修改評分？(不可修改)
  - API 需求？(需要)
  - UI 需求？(需要評分組件)
```

**產出**: `openspec/changes/20260113-add-rating-feature/proposal.md`

### 階段 2: 規格化 (Specification)

**目標**: 將 Proposal 轉化為詳細的技術規格

#### 2.1 Backend 規格 (使用 `software-architect` agent)

產出以下規格文件：

**API 規格** (`specs/api.md`):
```yaml
POST /api/v1/ratings
Request Body:
  {
    "salesperson_id": 123,
    "rating": 5,
    "comment": "優秀的服務"
  }
Response:
  {
    "id": 456,
    "rating": 5,
    "created_at": "2026-01-13T10:00:00Z"
  }
```

**資料庫規格** (`specs/data-model.md`):
```sql
CREATE TABLE ratings (
  id BIGINT PRIMARY KEY,
  user_id BIGINT NOT NULL,
  salesperson_id BIGINT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP,
  UNIQUE KEY unique_user_salesperson (user_id, salesperson_id)
);
```

**業務規則** (`specs/business-rules.md`):
- 每個用戶對每個業務員只能評分一次
- 評分範圍: 1-5 星
- 評論可選，最多 500 字
- 不可修改或刪除已提交的評分

**測試規格** (`specs/tests.md`):
- Feature Tests: API 端點測試
- Unit Tests: Rating Model 邏輯測試
- Validation Tests: 邊界條件測試

#### 2.2 Frontend 規格 (使用 `product-designer` agent)

產出以下規格文件：

**UI/UX 規格** (`specs/ui-ux.md`):
- Wireframes
- 使用者互動流程
- 錯誤狀態處理
- Loading 狀態

**組件規格** (`specs/components.md`):
```typescript
interface RatingComponentProps {
  salespersonId: number;
  onRatingSubmit: (rating: Rating) => void;
  maxRating?: number; // default: 5
}
```

**API 整合** (`specs/api-integration.md`):
- API Client 使用方式
- 錯誤處理策略
- Loading 狀態管理

### 階段 3: 任務拆解 (Task Breakdown)

將規格拆解為可執行的任務：

**Backend 任務**:
- [ ] 建立 Migration
- [ ] 建立 Rating Model
- [ ] 建立 RatingController
- [ ] 建立 FormRequest 驗證
- [ ] 實作業務邏輯
- [ ] 撰寫 Feature Tests
- [ ] 撰寫 Unit Tests

**Frontend 任務**:
- [ ] 建立 RatingForm 組件
- [ ] 實作星級選擇 UI
- [ ] 整合 API Client
- [ ] 錯誤處理與 Toast 通知
- [ ] 撰寫 Component Tests

### 階段 4: 實作 (Development)

**Backend 實作** (使用 `laravel-specialist` agent):
1. 按照 `specs/` 逐項實作
2. 遵循 Laravel 最佳實踐
3. 確保程式碼通過 PHPStan Level 9
4. 撰寫完整的測試

**Frontend 實作** (使用 `react-specialist` agent):
1. 按照 `specs/` 逐項實作
2. 遵循 React/Next.js 最佳實踐
3. 確保 TypeScript 嚴格模式通過
4. 撰寫 Component Tests

**並行開發**:
當使用 `/auto-develop` 時，Backend 和 Frontend 會同時進行，節省時間。

### 階段 5: 測試驗證 (Testing)

**Backend 測試** (使用 `qa-engineer` agent):
```bash
# 執行所有測試
composer test

# 測試覆蓋率（需 >= 80%）
composer test:coverage

# 靜態分析（PHPStan Level 9）
composer analyse
```

**Frontend 測試**:
```bash
# 單元測試
npm test

# E2E 測試
npx playwright test

# 類型檢查
npm run typecheck
```

**整合測試**:
- API 與 Frontend 整合測試
- 端到端使用者流程測試

### 階段 6: 歸檔 (Archiving)

**目標**: 將變更規格歸檔到規範庫

**歸檔規則**:
- Backend 規格 → `openspec/specs/backend/`
- Frontend 規格 → `openspec/specs/frontend/`
- 歸檔時合併到對應的主規格文件
- 保持規範庫的結構一致性

**歸檔後**:
```
openspec/
├── specs/
│   ├── backend/
│   │   ├── api.md          # 合併新的 API
│   │   ├── data-model.md   # 合併新的 Table
│   │   └── ...
│   └── frontend/
│       ├── components.md   # 合併新的組件
│       └── ...
└── changes/
    └── 20260113-add-rating-feature/  # 保留完整變更記錄
```

## 實例代碼

### 使用 `/auto-develop` (完全自動化)

```bash
# 在專案根目錄執行
/auto-develop 新增業務員評分功能

# 需求分析對話（10 分鐘）
> 評分範圍？ 1-5 星
> 需要評論？ 可選文字評論
> 誰可以評分？ 已登入用戶

# 確認開始
✋ 是否開始 AUTO-RUN？
   → 點擊「是」

# 自動執行（2-4 小時）
🤖 Phase 1: 需求分析（完成）
🤖 Phase 2: 規格化（並行執行）
🤖 Phase 3: Git branch 創建
🤖 Phase 4: 開發（前後端並行）
🤖 Phase 5: 測試
🤖 Phase 6: 歸檔
🤖 Phase 7: Git commit + PR

# 完成通知
✅ 完成！PR 已創建: #123
```

### 使用 `/implement` (Backend 單獨開發)

```bash
# Backend 開發
/implement 新增評分 API

# Phase 1: 需求分析
[requirements-analyst agent 訪談]

# Phase 2: 確認 Proposal
✋ 查看 proposal.md，確認無誤

# Phase 3: 規格化
[software-architect agent 產出規格]

# Phase 4: 確認規格
✋ 查看 specs/，確認完整

# Phase 5: 實作（自動執行）
[laravel-specialist agent 實作]

# Phase 6: 歸檔
[自動歸檔到 openspec/specs/backend/]
```

### 使用 `/implement-frontend` (Frontend 單獨開發)

```bash
# Frontend 開發
/implement-frontend 新增評分 UI

# Phase 1: 需求分析
[requirements-analyst agent 訪談]

# Phase 2: UI 設計
[product-designer agent 設計 UI/UX]

# Phase 3: 確認設計
✋ 查看 specs/ui-ux.md

# Phase 4: 實作（自動執行）
[react-specialist agent 實作]

# Phase 5: 歸檔
[自動歸檔到 openspec/specs/frontend/]
```

## 常見錯誤

### 錯誤 1: 未撰寫規格就開始寫程式碼

**錯誤示範**:
```bash
# 直接開始寫 code
User: 幫我新增評分功能
Assistant: 好的，我現在開始寫 Rating Model...
```

**問題**:
- 沒有明確的需求定義
- 實作可能偏離用戶期望
- 後續變更成本高

**正確做法**:
```bash
User: 幫我新增評分功能
Assistant: 我使用 /auto-develop 來進行完整的 SDD 流程
  → 先進行需求分析
  → 產出 proposal.md
  → 規格化後再實作
```

### 錯誤 2: 規格不完整就進入實作

**錯誤示範**:
```markdown
# proposal.md（不完整）
新增評分功能
```

**問題**: 規格過於簡略，缺少關鍵資訊

**正確做法**:
```markdown
# proposal.md（完整）
## 功能描述
允許用戶對業務員進行 1-5 星評分

## 使用者故事
- 作為已登入用戶，我可以對業務員評分
- 作為用戶，我可以看到我的歷史評分

## 技術需求
- Backend: RESTful API
- Frontend: 評分組件
- Database: ratings table

## 成功標準
- 評分成功率 >= 99%
- API 回應時間 < 200ms
```

### 錯誤 3: 實作偏離規格

**錯誤示範**:
```php
// 規格: 評分範圍 1-5
// 實作: 評分範圍 0-10 ❌
class Rating extends Model
{
    protected function rating(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => min(10, max(0, $value)), // 錯誤！
        );
    }
}
```

**正確做法**:
```php
// 嚴格遵循規格: 評分範圍 1-5
class Rating extends Model
{
    protected function rating(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => min(5, max(1, $value)), // 正確
        );
    }
}
```

## 最佳實踐

### 實作檢查清單

開發任何功能前:
- [ ] 已建立 `openspec/changes/<feature-name>/` 目錄
- [ ] 已完成需求分析，產出 `proposal.md`
- [ ] Proposal 包含完整的使用者故事和成功標準
- [ ] 已完成規格化，產出所有必要的 `specs/` 文件
- [ ] 規格已經過審查和確認
- [ ] 已拆解為具體的實作任務
- [ ] 測試規格已明確定義

實作過程中:
- [ ] 嚴格遵循規格，不隨意偏離
- [ ] 遇到規格不明確時，先更新規格再實作
- [ ] 所有程式碼都有對應的測試
- [ ] 程式碼通過靜態分析（PHPStan Level 9 / TypeScript strict）

完成後:
- [ ] 所有測試通過（>= 80% 覆蓋率）
- [ ] 規格已歸檔到 `openspec/specs/`
- [ ] 已建立 Git commit 和 PR
- [ ] PR 描述包含變更摘要和測試結果

### 注意事項

**規格品質**:
- 規格要足夠詳細，讓 AI 或任何開發者都能獨立實作
- 使用具體的範例而非抽象描述
- API 規格要包含完整的 Request/Response 範例
- DB Schema 要包含索引、約束、關聯定義

**變更管理**:
- 需求變更時，先更新 `proposal.md` 和 `specs/`
- 重大變更需要重新審查規格
- 保持 Git 提交與規格的對應關係

**團隊協作**:
- 規格是團隊溝通的基礎
- 所有人都應該能看懂規格
- 使用標準的 Markdown 格式
- 避免使用專業術語，或提供解釋

## 相關知識

### 前置知識

在開始 SDD 流程前，建議先了解:
- [Git 工作流程](./git-workflow.md) - 如何管理分支和 PR
- [Backend 架構](../backend/architecture.md) - Laravel 架構模式
- [Frontend 架構](../frontend/architecture.md) - Next.js 架構模式

### 延伸閱讀

深入了解各階段的細節:
- [API 設計規範](../backend/api-design.md) - RESTful API 設計
- [資料庫設計](../backend/database.md) - DB Schema 設計
- [組件模式](../frontend/component-patterns.md) - React 組件設計
- [測試策略](../backend/testing.md) - 測試撰寫指南

### 實作流程

完整功能開發的建議順序:
1. [本文件] - 理解 SDD 流程
2. [Git 工作流程](./git-workflow.md) - 建立 feature branch
3. [Backend 架構](../backend/architecture.md) - 實作 Backend
4. [Frontend 架構](../frontend/architecture.md) - 實作 Frontend
5. [部署流程](./deployment.md) - 部署到生產

## 決策記錄

### 當前決策 (2026-01-13)

**採用 SDD 方法論的原因**:
- 原因 1: 確保所有開發工作都有明確的規格基礎，減少返工
- 原因 2: 提供清晰的實作依據，適合 AI 輔助開發
- 原因 3: 建立可追溯的文檔，便於維護和知識傳承
- 原因 4: 強制進行設計思考，提高程式碼品質

**考慮的替代方案**:
- 方案 A (敏捷開發): 過於靈活，缺乏明確規格，不適合 AI 輔助
- 方案 B (瀑布模型): 過於僵化，無法快速迭代

### 歷史演進

**2026-01-13**: 完善自動化流程
- 新增 `/auto-develop` 完全自動化指令
- 實現前後端並行開發
- 優化規格歸檔流程

**2026-01-10**: 初始版本
- 建立 SDD 基本流程
- 定義規格文件結構
- 整合 OpenSpec 規範庫

## 參考資源

### 內部文檔
- `.claude/commands/README.md` - Commands 使用指南
- `.claude/commands/auto-develop.md` - 自動化開發指令
- `.claude/commands/implement.md` - Backend 實作指令
- `.claude/commands/implement-frontend.md` - Frontend 實作指令

### 專案規範
- `openspec/specs/backend/README.md` - Backend 規範總覽
- `openspec/specs/frontend/README.md` - Frontend 規範總覽

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
