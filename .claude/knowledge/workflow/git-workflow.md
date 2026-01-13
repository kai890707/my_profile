---
category: workflow
tags: [git, version-control, branching, collaboration]
priority: high
last_updated: 2026-01-13
applies_to: All YAMU development
related_docs: [sdd-process.md, deployment.md]
---

# Git 工作流程與分支策略

## Quick Reference

- 主分支: `main` (生產環境)
- 功能分支命名: `feature/<feature-name>`
- 分支來源: 從 `main` 分支
- PR 目標: 合併回 `main`
- Commit 規範: `type: description`
- 自動化指令: `/feature-start`, `/feature-finish`
- 所有 commit 必須包含: `Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>`

## 使用場景

**適用於**:
- 所有新功能開發
- Bug 修復
- 重構工作
- 文檔更新

**不適用於**:
- 緊急熱修復（可直接在 `main` 修改並立即部署）
- 實驗性分支（使用 `experiment/` 前綴）

## 核心概念

YAMU 專案採用簡化的 Git Flow，以 `main` 為唯一長期分支。所有開發工作都在功能分支（Feature Branch）上進行，完成後通過 Pull Request 合併回 `main`。

這種策略的優勢：
1. 簡單直觀，易於理解和操作
2. 適合小型團隊和 AI 輔助開發
3. 主分支始終保持可部署狀態
4. 通過 PR 進行代碼審查和品質控制

## Git 工作流程

### 階段 1: 開始新功能

**手動方式**:
```bash
# 確保在 main 分支且為最新
git checkout main
git pull origin main

# 建立新的功能分支
git checkout -b feature/add-rating-system

# 推送到遠端
git push -u origin feature/add-rating-system
```

**自動化方式** (推薦):
```bash
# 使用 /feature-start 指令
/feature-start add-rating-system

# 自動執行:
# 1. 切換到 main 並更新
# 2. 建立 feature/add-rating-system 分支
# 3. 推送到遠端
```

### 階段 2: 開發與提交

**開發過程**:
```bash
# 在功能分支上開發
# 使用 /auto-develop 或 /implement 進行 SDD 開發

# 定期提交
git add .
git commit -m "feat: Add rating model and migration

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```

**Commit 規範**:
```
<type>: <description>

[optional body]

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

**Type 類型**:
- `feat`: 新功能
- `fix`: Bug 修復
- `refactor`: 重構
- `docs`: 文檔更新
- `test`: 測試相關
- `chore`: 建構工具、依賴更新等

### 階段 3: 保持同步

```bash
# 定期與 main 同步（避免衝突）
git checkout main
git pull origin main
git checkout feature/add-rating-system
git merge main

# 或使用 rebase（保持線性歷史）
git rebase main
```

### 階段 4: 創建 Pull Request

**手動方式**:
```bash
# 推送最新變更
git push origin feature/add-rating-system

# 使用 gh CLI 創建 PR
gh pr create --title "Add rating system" --body "$(cat <<'EOF'
## Summary
- 新增業務員評分功能
- 包含 Backend API 和 Frontend UI

## Changes
- 新增 Rating Model 和 Migration
- 實作評分 API 端點
- 新增評分 UI 組件

## Test Plan
- [x] Feature Tests 通過
- [x] Component Tests 通過
- [x] E2E Tests 通過

Generated with Claude Code
EOF
)"
```

**自動化方式** (推薦):
```bash
# 使用 /feature-finish 指令
/feature-finish

# 自動執行:
# 1. 執行品質檢查（PHPStan, ESLint）
# 2. 執行所有測試
# 3. 歸檔規格到 openspec/specs/
# 4. 建立 commit（如有未提交變更）
# 5. 推送到遠端
# 6. 創建 Pull Request
```

### 階段 5: 代碼審查與合併

**審查檢查清單**:
- [ ] 所有測試通過（CI/CD）
- [ ] 程式碼品質檢查通過（PHPStan Level 9, ESLint）
- [ ] 規格已歸檔到 `openspec/specs/`
- [ ] PR 描述清晰，包含變更摘要
- [ ] 沒有合併衝突
- [ ] 功能符合原始需求

**合併方式**:
```bash
# 使用 Squash and Merge（推薦）
# 將多個 commit 合併為一個，保持 main 分支乾淨

# 或使用 gh CLI
gh pr merge --squash --delete-branch
```

**合併後清理**:
```bash
# 自動刪除遠端分支（GitHub 設定）
# 手動刪除本地分支
git checkout main
git pull origin main
git branch -d feature/add-rating-system
```

## 實例代碼

### 完整功能開發流程

```bash
# Step 1: 開始新功能
/feature-start add-rating-system

# 自動執行:
# - git checkout main
# - git pull origin main
# - git checkout -b feature/add-rating-system
# - git push -u origin feature/add-rating-system

# Step 2: 開發功能
/auto-develop 新增業務員評分功能

# 自動執行:
# - 需求分析
# - 規格化
# - 實作（包含自動 commit）
# - 測試
# - 規格歸檔

# Step 3: 完成功能
/feature-finish

# 自動執行:
# - 品質檢查
# - 測試驗證
# - 最終 commit
# - git push
# - 創建 PR

# Step 4: 審查與合併（手動）
# - 在 GitHub 上進行 Code Review
# - Squash and Merge
# - 刪除分支

# Step 5: 清理本地分支
git checkout main
git pull origin main
git branch -d feature/add-rating-system
```

### 處理合併衝突

```bash
# 在合併時發生衝突
git merge main
# Auto-merging app/Models/Rating.php
# CONFLICT (content): Merge conflict in app/Models/Rating.php

# 解決衝突
# 1. 打開衝突文件
# 2. 手動編輯，移除衝突標記
# 3. 保存文件

# 標記衝突已解決
git add app/Models/Rating.php

# 完成合併
git commit -m "Merge main into feature/add-rating-system"

# 推送
git push origin feature/add-rating-system
```

### 緊急修復流程

```bash
# 生產環境發現嚴重 Bug，需要立即修復

# 方案 A: 從 main 建立 hotfix 分支
git checkout main
git pull origin main
git checkout -b hotfix/fix-critical-bug

# 修復 Bug
# ... 編輯文件 ...

# 提交
git add .
git commit -m "fix: Fix critical bug in rating validation

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"

# 推送並創建 PR
git push -u origin hotfix/fix-critical-bug
gh pr create --title "Hotfix: Fix critical bug"

# 快速審查後合併
gh pr merge --squash --delete-branch

# 方案 B: 直接在 main 修復（極度緊急）
git checkout main
git pull origin main

# 修復 Bug
# ... 編輯文件 ...

# 直接提交到 main
git add .
git commit -m "fix: Fix critical bug (hotfix)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"

git push origin main

# 立即部署
/deploy production
```

## 常見錯誤

### 錯誤 1: 忘記從 main 分支建立功能分支

**錯誤示範**:
```bash
# 在舊的功能分支上建立新分支
git checkout feature/old-feature
git checkout -b feature/new-feature  # 錯誤！
```

**問題**: 新分支包含舊功能的 commit，導致 PR 混亂

**正確做法**:
```bash
# 永遠從最新的 main 建立新分支
git checkout main
git pull origin main
git checkout -b feature/new-feature  # 正確
```

### 錯誤 2: Commit message 不符合規範

**錯誤示範**:
```bash
# 不清楚的 commit message
git commit -m "update code"
git commit -m "fix bug"
git commit -m "WIP"
```

**問題**: 無法從 commit history 了解變更內容

**正確做法**:
```bash
# 清晰的 commit message
git commit -m "feat: Add rating validation in RatingController

- Add validation rules for rating range (1-5)
- Add unique constraint check
- Add proper error messages

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```

### 錯誤 3: 長時間不與 main 同步

**錯誤示範**:
```bash
# 在功能分支開發一個月，從未與 main 同步
# 最後要合併時發現大量衝突
```

**問題**: 大量衝突難以解決，可能破壞功能

**正確做法**:
```bash
# 每 2-3 天與 main 同步一次
git checkout main
git pull origin main
git checkout feature/add-rating-system
git merge main

# 或使用 rebase
git rebase main
```

### 錯誤 4: 在 PR 合併前刪除分支

**錯誤示範**:
```bash
# 創建 PR 後立即刪除本地分支
gh pr create --title "Add rating system"
git branch -D feature/add-rating-system  # 錯誤！
```

**問題**: 如果 PR 需要修改，無法繼續開發

**正確做法**:
```bash
# PR 合併後才刪除分支
gh pr create --title "Add rating system"

# 等待 PR 合併...

# PR 合併後
git checkout main
git pull origin main
git branch -d feature/add-rating-system  # 正確
```

## 最佳實踐

### 實作檢查清單

開始新功能前:
- [ ] 已切換到 main 分支
- [ ] 已拉取最新的 main 分支
- [ ] 使用規範的分支命名 `feature/<name>`
- [ ] 分支已推送到遠端

開發過程中:
- [ ] 定期提交（每完成一個小功能）
- [ ] Commit message 符合規範
- [ ] 每個 commit 包含 `Co-Authored-By`
- [ ] 每 2-3 天與 main 同步一次

完成功能後:
- [ ] 所有測試通過
- [ ] 程式碼品質檢查通過
- [ ] 規格已歸檔
- [ ] 已建立 Pull Request
- [ ] PR 描述完整清晰

### 注意事項

**分支管理**:
- 功能分支應該短期存在（1-2 週內完成）
- 避免在功能分支上進行多個不相關的變更
- 保持功能分支專注於單一功能或修復

**Commit 頻率**:
- 經常提交，每完成一個邏輯單元就提交
- 每個 commit 應該是完整且可運行的
- 避免提交半成品或無法編譯的程式碼

**PR 大小**:
- 單個 PR 的變更應該在合理範圍內（< 500 行）
- 大型功能應該拆分為多個小 PR
- 每個 PR 應該有明確的目標

**代碼審查**:
- PR 創建後盡快進行審查
- 審查者應該仔細檢查邏輯和測試
- 使用 GitHub 的 Review 功能留下評論

## 相關知識

### 前置知識

在開始使用 Git 工作流程前，建議先了解:
- [SDD 流程](./sdd-process.md) - 開發流程與規格管理
- Git 基礎操作（clone, commit, push, pull）
- GitHub Pull Request 機制

### 延伸閱讀

深入了解相關主題:
- [部署流程](./deployment.md) - PR 合併後的部署流程
- [Backend 測試](../backend/testing.md) - CI/CD 測試要求
- [Frontend 測試](../frontend/testing.md) - 前端測試標準

### 實作流程

完整功能開發的 Git 流程:
1. [本文件] - 建立功能分支
2. [SDD 流程](./sdd-process.md) - 開發功能
3. [本文件] - 創建 PR 並合併
4. [部署流程](./deployment.md) - 部署到生產

## 決策記錄

### 當前決策 (2026-01-13)

**採用簡化 Git Flow 的原因**:
- 原因 1: 專案規模小，不需要複雜的分支策略
- 原因 2: 主分支即生產，簡化部署流程
- 原因 3: 適合 AI 輔助開發，減少手動操作
- 原因 4: 團隊規模小，溝通成本低

**考慮的替代方案**:
- 方案 A (Git Flow): 過於複雜，有 develop, release 等多個長期分支
- 方案 B (GitHub Flow): 與當前方案類似，但我們加強了自動化

**為什麼要求 Co-Authored-By**:
- 明確標記 AI 輔助開發的貢獻
- 符合 GitHub 的協作標準
- 便於追蹤 AI 參與的開發工作

### 歷史演進

**2026-01-13**: 完善自動化指令
- 新增 `/feature-start` 自動建立分支
- 新增 `/feature-finish` 自動完成 PR 流程
- 強制要求 `Co-Authored-By` 標記

**2026-01-10**: 初始版本
- 確立簡化的 Git Flow
- 定義分支命名規範
- 建立 Commit message 規範

## 參考資源

### 官方文檔
- [GitHub Flow](https://guides.github.com/introduction/flow/) - GitHub 官方工作流程指南
- [Git Documentation](https://git-scm.com/doc) - Git 官方文檔

### 相關文章
- [Commit Message Guidelines](https://www.conventionalcommits.org/) - Commit message 規範

### 專案內部文檔
- `.claude/commands/feature-start.md` - 功能分支建立指令
- `.claude/commands/feature-finish.md` - 功能完成指令
- `.claude/commands/pr-review.md` - PR 審查指令

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
