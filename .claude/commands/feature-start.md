# 開始新功能開發

創建新的 feature 分支並開始功能開發。

---

## 執行時機

當需要開發新功能時使用此命令。

---

## 參數

```bash
/feature-start <feature-name>
```

- `<feature-name>`: 功能名稱（使用 kebab-case）

範例:
- `/feature-start add-rating-api`
- `/feature-start user-profile-enhancement`

---

## 執行步驟

### 1. 使用 AskUserQuestion 確認資訊

詢問用戶：
- **功能名稱**: 確認 feature 名稱
- **功能類型**: Backend / Frontend / 全棧
- **是否需要 OpenSpec 規格**: 是否需要先撰寫規格？

### 2. 檢查當前狀態

```bash
# 檢查是否有未提交的變更
git status

# 如果有未提交變更，詢問用戶是否要暫存
```

### 3. 切換到 develop 並更新

```bash
git checkout develop
git pull origin develop
```

### 4. 創建 feature 分支

```bash
# 根據命名規範創建分支
git checkout -b feature/<feature-name>
```

### 5. 推送到遠端

```bash
git push -u origin feature/<feature-name>
```

### 6. 開始開發

根據功能類型，引導用戶使用相應的開發流程：

**如果是 Backend 功能**:
```bash
# 建議使用 OpenSpec SDD 流程
/implement <功能描述>
```

**如果是 Frontend 功能**:
```bash
# 建議使用 Frontend SDD 流程
/implement-frontend <功能描述>
```

**如果選擇不使用 OpenSpec**:
提醒用戶遵循以下規範：
- 代碼規範 (PSR-12 for PHP, ESLint for JS)
- 測試覆蓋率要求
- Commit message 規範
- 參考相應的 CLAUDE.md 文檔

### 7. 輸出結果

告知用戶：
- Feature 分支已創建: `feature/<feature-name>`
- 當前分支狀態
- 建議的下一步操作
- 相關文檔連結

---

## Commit 規範提醒

提醒用戶遵循 Commit Message 格式：

```
<type>(<scope>): <subject>

範例:
feat(api): add rating endpoint
fix(auth): resolve token expiration
test(api): add rating endpoint tests
```

---

## 參考文檔

- Git Flow 工作流程: `.claude/workflows/GIT_FLOW.md`
- Commit 規範
- 後端開發規範: `my_profile_ci4/CLAUDE.md`
- 前端開發規範: `frontend/CLAUDE.md`

---

## 相關 Commands

- `/feature-finish` - 完成功能開發，創建 PR
- `/implement` - Backend OpenSpec 開發流程
- `/implement-frontend` - Frontend OpenSpec 開發流程
