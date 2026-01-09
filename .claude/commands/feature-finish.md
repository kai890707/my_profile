# 完成功能開發

完成 feature 開發，**執行強制品質檢查**，創建 Pull Request 準備合併到 develop。

---

## ⚠️ 重要說明

此命令包含**強制品質檢查**，確保所有程式碼符合專案標準：

### 品質門檻 (Quality Gates)

**Backend (Laravel)**:
- ✅ 測試通過率: 100% (201/201 tests)
- ✅ 測試覆蓋率: ≥80%
- ✅ PHPStan: Level 9 (0 errors)
- ✅ Code Style: PSR-12 compliant

**Frontend (Next.js)**:
- ✅ TypeScript: 編譯無錯誤
- ✅ ESLint: 0 errors (warnings 可接受)
- ✅ Tests: 所有測試通過
- ✅ Build: 構建成功

**如果任何檢查失敗，將阻止 PR 創建**，必須先修復問題。

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

### 6. 執行測試與品質檢查 ⚠️ **強制檢查**

**重要**: 此步驟為**強制執行**，所有檢查通過才能創建 PR。

#### 6.1 Backend 檢查 (Laravel)

```bash
# 切換到 Backend 目錄
cd my_profile_laravel

# 1. 執行所有測試
php artisan test

# 檢查點:
# - 測試通過率: 100% (201/201 tests passing)
# - 如果有測試失敗，顯示失敗原因並阻止 PR 創建

# 2. 檢查測試覆蓋率
php artisan test --coverage --min=80

# 檢查點:
# - 最低覆蓋率: ≥80%
# - 如果覆蓋率不足，顯示詳細報告並阻止 PR 創建

# 3. PHPStan Level 9 靜態分析
vendor/bin/phpstan analyse

# 檢查點:
# - 必須通過 Level 9 檢查 (0 errors)
# - 如果有錯誤，顯示錯誤列表並阻止 PR 創建

# 4. Laravel Pint 代碼風格檢查
vendor/bin/pint --test

# 檢查點:
# - 代碼風格符合 PSR-12
# - 如果不符合，提示執行 vendor/bin/pint 修復
```

#### 6.2 Frontend 檢查 (Next.js)

```bash
# 切換到 Frontend 目錄
cd ../frontend

# 1. TypeScript 編譯檢查
npm run type-check

# 檢查點:
# - TypeScript 編譯無錯誤
# - 如果有錯誤，顯示錯誤列表並阻止 PR 創建

# 2. ESLint 檢查
npm run lint

# 檢查點:
# - ESLint 檢查通過 (0 errors, warnings 可接受)
# - 如果有錯誤，顯示錯誤列表並阻止 PR 創建

# 3. 執行測試 (如果存在)
npm test -- --run

# 檢查點:
# - 所有測試通過
# - 建議覆蓋率: ≥70%

# 4. 構建檢查
npm run build

# 檢查點:
# - 構建成功，無錯誤
# - 如果構建失敗，顯示錯誤並阻止 PR 創建
```

#### 6.3 檢查結果處理

**如果所有檢查通過**:
```
✅ 所有品質檢查通過！

Backend:
  ✅ Tests: 201/201 passing
  ✅ Coverage: 82%
  ✅ PHPStan: Level 9 passed
  ✅ Code Style: PSR-12 compliant

Frontend:
  ✅ TypeScript: Compiled successfully
  ✅ ESLint: 0 errors
  ✅ Build: Success

▶️  繼續創建 Pull Request...
```

**如果任何檢查失敗**:
```
❌ 品質檢查失敗！無法創建 PR。

失敗項目:
  ❌ Backend Tests: 198/201 passing (3 failed)
  ❌ Coverage: 75% (需要 ≥80%)

請先修復以下問題:

1. Backend Tests Failed:
   - SalespersonProfileTest::test_update_profile_validation
   - CompanyTest::test_create_company_requires_auth
   - AdminTest::test_approve_company

2. Coverage Not Met:
   - Services/SalespersonProfileService: 72%
   - Controllers/Api/AdminController: 68%

🔧 修復建議:
   cd my_profile_laravel
   php artisan test --filter=SalespersonProfileTest
   php artisan test --coverage

❌ PR 創建已取消。修復問題後重新執行 /feature-finish
```

#### 6.4 警告處理

如果只有 warnings (非 errors):
- ESLint warnings
- TypeScript 嚴格模式 warnings

使用 `AskUserQuestion` 詢問用戶是否繼續：
```
⚠️  發現 Warnings (非阻塞性問題)

Warnings:
  ⚠️  ESLint: 3 warnings in components/
  ⚠️  TypeScript: 2 implicit any warnings

這些 warnings 不會阻止 PR 創建，但建議修復。

是否繼續創建 PR?
  - 是，繼續創建 (建議在 PR 中說明)
  - 否，先修復 warnings
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

## 🧪 測試與品質檢查

### Backend (Laravel)
- [x] Tests: 201/201 passing (100%)
- [x] Coverage: 82% (≥80%)
- [x] PHPStan: Level 9 passed (0 errors)
- [x] Code Style: PSR-12 compliant

### Frontend (Next.js)
- [x] TypeScript: Compiled successfully
- [x] ESLint: 0 errors
- [x] Tests: All passing
- [x] Build: Success

### 其他測試
- [x] 手動測試已完成
- [x] API 整合測試通過
- [ ] 瀏覽器兼容性測試 (如適用)

## 🔗 相關連結
- OpenSpec 規格: \`openspec/changes/<feature-name>/\`
- 測試報告: [連結]

## ✅ PR Merge 要求
- [x] 至少 1 人 Code Review 通過
- [x] 所有測試通過 (201/201)
- [x] 測試覆蓋率達標 (≥80%)
- [x] PHPStan Level 9 無錯誤
- [x] TypeScript 編譯無錯誤
- [x] OpenSpec 規格已歸檔

---
🤖 此 PR 已通過 /feature-finish 的所有品質檢查
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
- 品質檢查結果
- PR 已創建
- PR 連結
- 等待審查
- 下一步操作建議

範例輸出：
```
🎉 Feature 開發完成！

✅ 品質檢查通過:
Backend:
  ✅ Tests: 201/201 passing
  ✅ Coverage: 82% (目標: ≥80%)
  ✅ PHPStan: Level 9 passed
  ✅ Code Style: PSR-12 compliant

Frontend:
  ✅ TypeScript: Compiled
  ✅ ESLint: 0 errors
  ✅ Build: Success

📋 PR 資訊:
- 標題: feat: add rating API endpoint
- 連結: https://github.com/user/repo/pull/123
- 目標分支: develop
- 審查者: @reviewer

⏳ 下一步:
1. 等待審查者 Code Review
2. 根據 feedback 進行修改（如需要）
3. 確保所有檢查通過（CI/CD）
4. 審查通過後，使用 Squash and Merge 合併到 develop
5. 合併後 feature 分支會自動刪除

📚 參考:
- PR Merge 要求: 至少 1 人 Review + 所有測試通過
- Code Review 標準: .claude/workflows/GIT_FLOW.md
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
