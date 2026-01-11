# 工作流程知識庫

**專案**: YAMU 業務員推廣系統
**版本**: 1.0
**最後更新**: 2026-01-11

---

## 📚 知識庫總覽

這是 YAMU 專案的**統一工作流程知識庫**，涵蓋所有開發、測試、部署相關的流程規範。

### 本知識庫包含

| 文檔 | 說明 | 使用場景 |
|------|------|---------|
| **[OpenSpec SDD 流程](./OPENSPEC_SDD.md)** | 規範驅動開發完整流程 | 功能開發、需求實作 |
| **[Agents 整合](./AGENTS_INTEGRATION.md)** | 專業 Agents 工作流程整合 | 了解 Agents 如何協作 |
| **[Git Flow 工作流程](./GIT_FLOW.md)** | 分支管理與版本控制 | 分支管理、PR 流程 |
| **[開發指南](./DEVELOPMENT_GUIDE.md)** | 完整開發流程與最佳實踐 | 日常開發、測試、部署 |

---

## 🎯 快速導航

### 我要開發新功能
1. 📖 先閱讀 **[OpenSpec SDD 流程](./OPENSPEC_SDD.md)** 了解 6 步驟開發流程
2. 🤖 查看 **[Agents 整合](./AGENTS_INTEGRATION.md)** 了解哪些 Agent 會協助你
3. 🌿 參考 **[Git Flow](./GIT_FLOW.md)** 了解分支管理
4. 💻 使用 **[開發指南](./DEVELOPMENT_GUIDE.md)** 執行日常操作

### 我要了解 Git 工作流程
📖 直接閱讀 **[Git Flow 工作流程](./GIT_FLOW.md)**

### 我要了解 Agents 如何運作
🤖 直接閱讀 **[Agents 整合](./AGENTS_INTEGRATION.md)**

### 我要了解測試、部署流程
💻 直接閱讀 **[開發指南](./DEVELOPMENT_GUIDE.md)**

---

## 📖 文檔詳細介紹

### 1. OpenSpec SDD 流程

**檔案**: [OPENSPEC_SDD.md](./OPENSPEC_SDD.md)

**內容**:
- ✅ 完整的 6 步驟 SDD 流程圖
- ✅ 每個步驟的詳細說明
- ✅ Proposal → Spec → Tasks → Validate → Implement → Archive
- ✅ Backend 和 Frontend 開發流程
- ✅ 並行開發流程
- ✅ 狀態追蹤和測試流程
- ✅ 最佳實踐檢查清單

**適用於**:
- 使用 `/implement` 或 `/implement-frontend` 開發功能
- 了解規範驅動開發的完整流程
- 查看每個步驟的產出和檢查點

### 2. Agents 整合

**檔案**: [AGENTS_INTEGRATION.md](./AGENTS_INTEGRATION.md)

**內容**:
- ✅ 7 個專業 Agents 詳細說明
- ✅ Agents 在 SDD 流程中的整合方式
- ✅ 自動觸發 vs 手動觸發規則
- ✅ Agent 決策表
- ✅ 最佳實踐和疑難排解

**7 個 Agents**:
1. **requirements-analyst** - 需求分析專家 (PM)
2. **software-architect** - 軟體架構師 (Backend)
3. **product-designer** - 產品設計師 (Frontend UI/UX)
4. **laravel-specialist** - Laravel 框架專家
5. **react-specialist** - React/Next.js 專家
6. **qa-engineer** - QA 工程師
7. **devops-engineer** - DevOps 工程師

**適用於**:
- 了解哪個階段使用哪個 Agent
- 查看 Agent 的觸發條件
- 學習如何與 Agents 協作

### 3. Git Flow 工作流程

**檔案**: [GIT_FLOW.md](./GIT_FLOW.md)

**內容**:
- ✅ 分支策略 (main, develop, feature, release, hotfix)
- ✅ 分支命名規範
- ✅ 完整的 Git 工作流程
- ✅ Commit 規範 (Conventional Commits)
- ✅ Pull Request 流程
- ✅ 緊急修復流程

**適用於**:
- 創建和管理分支
- 撰寫規範的 Commit Message
- 創建和審查 Pull Request
- 處理緊急修復 (Hotfix)

### 4. 開發指南

**檔案**: [DEVELOPMENT_GUIDE.md](./DEVELOPMENT_GUIDE.md)

**內容**:
- ✅ 開發環境設置
- ✅ 日常開發流程
- ✅ 測試流程 (Backend, Frontend, API)
- ✅ 代碼審查流程
- ✅ 部署流程
- ✅ 緊急修復流程

**適用於**:
- 初次設置開發環境
- 執行測試
- 進行代碼審查
- 部署到生產環境

---

## 🔄 工作流程關係圖

```
┌─────────────────────────────────────────────────────────────┐
│                      開始新功能開發                          │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │   Git Flow: 創建 feature 分支         │
        │   📖 GIT_FLOW.md                      │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │   OpenSpec SDD: 6 步驟開發流程        │
        │   📖 OPENSPEC_SDD.md                  │
        │                                       │
        │   Step 1: Proposal (需求確認)         │
        │   Step 2: Specifications (規格設計)   │
        │   Step 3: Tasks (任務拆解)            │
        │   Step 4: Validate (規格驗證)         │
        │   Step 5: Implement (程式碼實作)      │
        │   Step 6: Archive (歸檔規範)          │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │   Agents: 自動協助各階段開發          │
        │   🤖 AGENTS_INTEGRATION.md            │
        │                                       │
        │   • requirements-analyst (Step 1)     │
        │   • software-architect (Step 2)       │
        │   • product-designer (Step 2)         │
        │   • laravel-specialist (Step 5)       │
        │   • react-specialist (Step 5)         │
        │   • qa-engineer (Testing)             │
        │   • devops-engineer (Deployment)      │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │   測試、審查、部署                     │
        │   💻 DEVELOPMENT_GUIDE.md             │
        └───────────────────────────────────────┘
                            │
                            ▼
        ┌───────────────────────────────────────┐
        │   Git Flow: 創建 PR、合併、部署       │
        │   📖 GIT_FLOW.md                      │
        └───────────────────────────────────────┘
                            │
                            ▼
                    功能開發完成 ✅
```

---

## 🎓 學習路徑

### 新手入門 (第一次使用)
1. 📖 閱讀 **[開發指南](./DEVELOPMENT_GUIDE.md)** - 了解開發環境設置
2. 📖 閱讀 **[Git Flow](./GIT_FLOW.md)** - 了解分支管理
3. 📖 閱讀 **[OpenSpec SDD](./OPENSPEC_SDD.md)** - 了解開發流程
4. 🤖 閱讀 **[Agents 整合](./AGENTS_INTEGRATION.md)** - 了解 AI 協助
5. ✅ 實際操作一次完整流程

### 日常開發 (熟悉流程後)
1. 使用 `/feature-start` 創建分支
2. 使用 `/implement` 或 `/implement-frontend` 開發功能
3. 使用 `/test` 執行測試
4. 使用 `/feature-finish` 創建 PR
5. 使用 `/pr-review` 審查代碼
6. 合併到 develop 分支

### 進階使用 (精通流程)
- 手動控制 SDD 每個步驟 (`/proposal`, `/spec`, `/develop` 等)
- 使用專業 Agents 進行深度分析
- 自訂工作流程和最佳實踐
- 優化團隊協作流程

---

## 🔗 相關資源

### 專案核心文檔
- **[專案總覽](../../CLAUDE.md)** - YAMU 專案整體說明
- **[Commands 指南](../commands/README.md)** - 所有可用的 Commands

### 開發規範
- **[Backend 規範](../../my_profile_laravel/CLAUDE.md)** - Laravel 開發規範
- **[Frontend 規範](../../frontend/CLAUDE.md)** - Next.js 開發規範

### OpenSpec 規範庫
- **[Backend API 規範](../../openspec/specs/backend/)** - API 端點規範
- **[Frontend UI 規範](../../openspec/specs/frontend/)** - UI 組件規範

---

## 💡 常見問題

### Q: 應該先看哪個文檔？
A:
- **新手**: 先看 [開發指南](./DEVELOPMENT_GUIDE.md)
- **了解流程**: 先看 [OpenSpec SDD](./OPENSPEC_SDD.md)
- **了解 Git**: 先看 [Git Flow](./GIT_FLOW.md)
- **了解 AI**: 先看 [Agents 整合](./AGENTS_INTEGRATION.md)

### Q: 這些流程是強制的嗎？
A:
- ✅ **強制**: Git Flow 分支管理、Commit 規範、PR 流程
- ✅ **強烈建議**: OpenSpec SDD 流程（新功能開發）
- ⚠️ **可選**: 手動控制 SDD 步驟（簡單修復可跳過）

### Q: Agents 會自動執行嗎？
A: 是的，當你使用 `/implement` 或 `/implement-frontend` 時，相關的 Agents 會自動在適當的階段啟動。詳見 [Agents 整合](./AGENTS_INTEGRATION.md)。

### Q: 如何查看我的開發進度？
A: 使用 `/status` 命令，或查看 `openspec/changes/` 目錄下的任務文件。

---

## 📞 獲取幫助

遇到問題時：
1. 📖 查看對應的工作流程文檔
2. 💬 查看文檔中的「常見問題」章節
3. 🔍 搜尋專案根目錄的 `CLAUDE.md`
4. 📝 提交 Issue 或詢問團隊

---

**維護者**: Development Team
**最後更新**: 2026-01-11
**版本**: 1.0
