# 完成功能開發

完成 feature 開發，創建 Pull Request 準備合併到 develop。

---

## 執行時機

當功能開發完成，準備提交審查時使用此命令。

---

## 執行步驟

### 1. 檢查當前狀態

```bash
# 確認在 feature 分支上
git branch --show-current

# 檢查是否有未提交的變更
git status
```

### 2. 確認完成度

使用 `AskUserQuestion` 詢問用戶：

**開發檢查清單**:
- [ ] 功能開發完成
- [ ] 測試已通過（單元測試、整合測試）
- [ ] 代碼符合規範（PSR-12 / ESLint）
- [ ] 文檔已更新
- [ ] OpenSpec 規格已歸檔（如適用）

如果有未完成項目，提醒用戶先完成。

### 3. 提交所有變更

```bash
# 如果有未提交的變更
git add .
git commit -m "<commit-message>"
```

提醒遵循 Commit 規範。

### 4. 同步 develop 分支

```bash
# 拉取最新的 develop
git fetch origin develop

# Rebase 到 develop（保持歷史清晰）
git rebase origin/develop

# 如果有衝突，引導用戶解決
```

### 5. 推送到遠端

```bash
# 如果已經 rebase，需要強制推送
git push origin feature/<feature-name> --force-with-lease
```

### 6. 執行測試（如有 CI/CD）

```bash
# 等待 CI/CD 檢查通過
# 如果失敗，提醒用戶修復問題
```

### 7. 創建 Pull Request

使用 `Bash` 工具執行 `gh` 命令創建 PR：

```bash
gh pr create --base develop --head feature/<feature-name> \
  --title "<type>: <description>" \
  --body "$(cat <<'EOF'
## 📝 變更摘要
[功能描述]

## 🎯 相關 Issue
Closes #[issue-number]

## 🔄 變更類型
- [x] 新功能 (feat)
- [ ] Bug 修復 (fix)
- [ ] 重構 (refactor)

## 📋 變更內容
- 變更 1
- 變更 2

## 🧪 測試
- [x] 單元測試已通過
- [x] 整合測試已通過
- [x] 手動測試已完成

## 🔗 相關連結
- OpenSpec 規格: \`openspec/changes/<feature-name>/\`

## ✅ Checklist
- [x] 代碼符合規範
- [x] 測試覆蓋率達標
- [x] 文檔已更新
EOF
)"
```

### 8. 指定審查者

```bash
# 如果團隊有多人，使用 gh pr edit 添加審查者
gh pr edit --add-reviewer <reviewer-username>
```

### 9. 輸出結果

告知用戶：
- PR 已創建
- PR 連結
- 等待審查
- 下一步操作建議

範例輸出：
```
✅ Pull Request 已創建！

📋 PR 資訊:
- 標題: feat: add rating API endpoint
- 連結: https://github.com/user/repo/pull/123
- 目標分支: develop
- 審查者: @reviewer

⏳ 下一步:
1. 等待審查者 review
2. 根據 feedback 進行修改（如需要）
3. 審查通過後，使用 Squash and Merge 合併
4. 合併後分支會自動刪除

📚 參考:
- Code Review 標準: .claude/workflows/GIT_FLOW.md#code-review-標準
```

---

## Code Review 提醒

提醒審查者檢查：
- ✅ 功能性: 代碼是否實現需求？
- ✅ 規範性: 是否遵循專案規範？
- ✅ 測試性: 測試覆蓋是否充足？
- ✅ 安全性: 是否有安全漏洞？
- ✅ 性能: 是否有性能問題？
- ✅ 可維護性: 代碼是否易於維護？

---

## 常見問題處理

### 問題 1: Rebase 衝突

如果 rebase 時出現衝突：
```bash
# 1. 查看衝突文件
git status

# 2. 手動解決衝突

# 3. 標記為已解決
git add <resolved-files>

# 4. 繼續 rebase
git rebase --continue
```

### 問題 2: CI/CD 失敗

如果 CI/CD 檢查失敗：
1. 查看失敗原因
2. 在 feature 分支上修復
3. Commit 並推送
4. 等待重新檢查

### 問題 3: PR 需要修改

如果審查者要求修改：
1. 在 feature 分支上進行修改
2. Commit 變更
3. 推送到遠端
4. PR 會自動更新

---

## 參考文檔

- Git Flow 工作流程: `.claude/workflows/GIT_FLOW.md`
- PR 流程和模板
- Code Review 標準

---

## 相關 Commands

- `/feature-start` - 開始新功能開發
- `/pr-review` - 審查 Pull Request
