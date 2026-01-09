# Git Flow 初始化

初始化專案的 Git Flow 工作流程，建立必要的分支結構和保護規則。

---

## 執行時機

- 新專案開始時
- 現有專案導入 Git Flow 時

---

## 執行步驟

### 1. 檢查當前 Git 狀態

- 使用 `Bash` 工具執行 `git status` 檢查工作目錄狀態
- 使用 `Bash` 工具執行 `git branch -a` 查看現有分支
- 如果有未提交的變更，先處理這些變更

### 2. 創建 develop 分支

```bash
# 如果 develop 分支不存在
git checkout -b develop
git push -u origin develop
```

### 3. 設置分支保護規則（如使用 GitHub）

提醒用戶在 GitHub/GitLab 設置以下保護規則：

**main 分支**:
- 需要 PR 才能合併
- 需要至少 1 人審查
- 需要通過 CI/CD 檢查
- 禁止直接 push
- 禁止強制 push

**develop 分支**:
- 需要 PR 才能合併
- 需要通過 CI/CD 檢查
- 禁止強制 push

### 4. 創建初始標籤

```bash
# 如果是新專案，創建 v0.1.0 標籤
git tag -a v0.1.0 -m "Initial version"
git push origin v0.1.0
```

### 5. 配置 Git Hooks（可選）

創建 `.githooks/` 目錄並設置：
- `pre-commit`: 代碼格式檢查
- `commit-msg`: Commit message 格式檢查
- `pre-push`: 執行測試

```bash
git config core.hooksPath .githooks
```

### 6. 創建 .gitignore（如果不存在）

確保包含常見的忽略項目：
- `node_modules/`
- `vendor/`
- `.env`
- `*.log`
- IDE 配置文件

### 7. 輸出結果

告知用戶：
- Git Flow 初始化完成
- 當前分支結構
- 下一步建議（開始第一個功能或遷移）

---

## 參考文檔

- Git Flow 工作流程: `.claude/workflows/GIT_FLOW.md`
- 分支策略說明
- Commit 規範

---

## 相關 Commands

- `/feature-start` - 開始新功能開發
- `/feature-finish` - 完成功能，創建 PR
- `/pr-review` - 審查 Pull Request
