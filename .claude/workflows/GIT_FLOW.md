# Git Flow 工作流程

**專案**: YAMU 業務員推廣系統
**團隊類型**: 新創公司
**更新日期**: 2026-01-09

---

## 📋 目錄

1. [分支策略](#分支策略)
2. [分支命名規範](#分支命名規範)
3. [工作流程](#工作流程)
4. [Commit 規範](#commit-規範)
5. [Pull Request 流程](#pull-request-流程)
6. [緊急修復流程](#緊急修復流程)

---

## 🌳 分支策略

### 主要分支（永久分支）

```
main (production)          ← 生產環境代碼，隨時可部署
  │
develop (staging)          ← 開發整合分支，下一版本的代碼
```

### 輔助分支（臨時分支）

```
feature/*                  ← 新功能開發
release/*                  ← 發布準備
hotfix/*                   ← 緊急修復
migration/*                ← 架構遷移專用（Laravel 遷移）
```

---

## 📝 分支命名規範

### Feature 分支
```bash
feature/<issue-number>-<short-description>
feature/<category>/<description>

範例：
feature/123-add-rating-api
feature/backend/laravel-migration
feature/frontend/dark-mode
```

### Release 分支
```bash
release/v<version>

範例：
release/v1.0.0
release/v1.1.0
release/v2.0.0-beta.1
```

### Hotfix 分支
```bash
hotfix/v<version>-<issue>

範例：
hotfix/v1.0.1-fix-auth-bug
hotfix/v1.0.2-security-patch
```

### Migration 分支（架構遷移專用）
```bash
migration/<framework>/<module>

範例：
migration/laravel/auth-module
migration/laravel/api-endpoints
migration/laravel/database-layer
```

---

## 🔄 工作流程

### 1. 功能開發流程（Feature）

```bash
# 1. 從 develop 創建 feature 分支
git checkout develop
git pull origin develop
git checkout -b feature/add-rating-api

# 2. 開發功能（遵循 OpenSpec SDD 流程）
# - 使用 /implement 或 /implement-frontend 命令
# - 頻繁 commit（小步提交）
git add .
git commit -m "feat: add rating API endpoint"

# 3. 推送到遠端
git push -u origin feature/add-rating-api

# 4. 創建 Pull Request
# - 使用 /pr-create 命令
# - 目標分支: develop
# - 指定審查者

# 5. Code Review
# - 使用 /pr-review 命令
# - 至少 1 人審查通過

# 6. 合併到 develop
# - 使用 Squash and Merge（保持歷史清晰）
# - 刪除 feature 分支
git checkout develop
git pull origin develop
git branch -d feature/add-rating-api
```

### 2. 發布流程（Release）

```bash
# 1. 從 develop 創建 release 分支
git checkout develop
git pull origin develop
git checkout -b release/v1.0.0

# 2. 準備發布
# - 更新版本號（package.json, composer.json）
# - 更新 CHANGELOG.md
# - 最後的測試和 bug 修復
git commit -m "chore: bump version to 1.0.0"

# 3. 合併到 main（生產環境）
git checkout main
git merge --no-ff release/v1.0.0
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin main --tags

# 4. 合併回 develop（同步變更）
git checkout develop
git merge --no-ff release/v1.0.0
git push origin develop

# 5. 刪除 release 分支
git branch -d release/v1.0.0
```

### 3. 緊急修復流程（Hotfix）

```bash
# 1. 從 main 創建 hotfix 分支
git checkout main
git pull origin main
git checkout -b hotfix/v1.0.1-fix-auth-bug

# 2. 修復 bug
git add .
git commit -m "fix: resolve authentication token expiration bug"

# 3. 合併到 main
git checkout main
git merge --no-ff hotfix/v1.0.1-fix-auth-bug
git tag -a v1.0.1 -m "Hotfix: authentication bug"
git push origin main --tags

# 4. 合併回 develop
git checkout develop
git merge --no-ff hotfix/v1.0.1-fix-auth-bug
git push origin develop

# 5. 刪除 hotfix 分支
git branch -d hotfix/v1.0.1-fix-auth-bug
```

### 4. 架構遷移流程（Migration）- Laravel 專用

```bash
# 1. 從 develop 創建 migration 分支
git checkout develop
git pull origin develop
git checkout -b migration/laravel/initial-setup

# 2. 按模組遷移（小步快跑）
# - 使用 /implement 命令配合遷移規格
# - 每個模組一個 commit
# - 確保 API 兼容性測試通過

# 模組順序建議：
# migration/laravel/01-project-setup        (Laravel 初始化)
# migration/laravel/02-database-layer       (Models, Migrations)
# migration/laravel/03-auth-module          (JWT 認證)
# migration/laravel/04-api-endpoints        (Controllers, Routes)
# migration/laravel/05-business-logic       (Services, Repositories)
# migration/laravel/06-testing              (PHPUnit Tests)
# migration/laravel/07-deployment           (Docker, CI/CD)

# 3. 每個模組完成後創建 PR
git push -u origin migration/laravel/initial-setup

# 4. 審查和測試
# - API 兼容性測試必須通過
# - 前端整合測試必須通過
# - 性能測試必須通過

# 5. 合併到 develop
# - 使用 Merge Commit（保留遷移歷史）
git checkout develop
git merge --no-ff migration/laravel/initial-setup
git push origin develop
```

---

## 📜 Commit 規範

### Commit Message 格式

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Type 類型

| Type | 說明 | 範例 |
|------|------|------|
| `feat` | 新功能 | `feat(api): add rating endpoint` |
| `fix` | Bug 修復 | `fix(auth): resolve token expiration` |
| `docs` | 文檔更新 | `docs: update API documentation` |
| `style` | 代碼格式（不影響功能） | `style: format code with PSR-12` |
| `refactor` | 重構（不新增功能或修復 bug） | `refactor: extract service layer` |
| `perf` | 性能優化 | `perf: optimize database queries` |
| `test` | 測試相關 | `test: add unit tests for rating API` |
| `chore` | 構建/工具相關 | `chore: update composer dependencies` |
| `ci` | CI/CD 相關 | `ci: add GitHub Actions workflow` |
| `build` | 構建系統 | `build: update Dockerfile` |
| `revert` | 回退 commit | `revert: revert feat(api): add rating` |
| `migrate` | 架構遷移 | `migrate: convert auth to Laravel` |

### Scope 範圍

- `api` - API 相關
- `auth` - 認證相關
- `frontend` - 前端相關
- `backend` - 後端相關
- `db` - 資料庫相關
- `docker` - Docker 相關
- `ci` - CI/CD 相關

### Commit Message 範例

```bash
# 好的範例 ✅
git commit -m "feat(api): add salesperson rating endpoint

- Add POST /api/ratings endpoint
- Implement rating validation (1-5 range)
- Add permission check (only clients can rate)
- Update API documentation

Closes #123"

# 壞的範例 ❌
git commit -m "update"
git commit -m "fix bug"
git commit -m "changes"
```

---

## 🔀 Pull Request 流程

### PR 創建檢查清單

創建 PR 前確認：
- [ ] 代碼遵循專案規範（PSR-12, ESLint）
- [ ] 所有測試通過（單元測試、整合測試）
- [ ] 更新相關文檔（API docs, CHANGELOG）
- [ ] Commit 符合規範
- [ ] 無 merge conflicts
- [ ] 通過 CI/CD 檢查

### PR 標題格式

```
<type>: <description>

範例：
feat: Add rating API endpoint
fix: Resolve authentication token bug
migrate: Convert auth module to Laravel
```

### PR 描述模板

```markdown
## 📝 變更摘要
簡述這個 PR 的主要變更

## 🎯 相關 Issue
Closes #123

## 🔄 變更類型
- [ ] 新功能 (feat)
- [ ] Bug 修復 (fix)
- [ ] 重構 (refactor)
- [ ] 架構遷移 (migrate)
- [ ] 文檔更新 (docs)
- [ ] 性能優化 (perf)

## 📋 變更內容
- 變更 1
- 變更 2
- 變更 3

## 🧪 測試
- [ ] 單元測試已通過
- [ ] 整合測試已通過
- [ ] 手動測試已完成
- [ ] API 兼容性測試通過（遷移專用）

## 📸 截圖（如適用）
[附上截圖]

## 🔗 相關連結
- OpenSpec 規格: `openspec/changes/feature-name/`
- API 文檔: [連結]

## ✅ Checklist
- [ ] 代碼符合規範
- [ ] 測試覆蓋率達標
- [ ] 文檔已更新
- [ ] CHANGELOG 已更新
```

### Code Review 標準

審查者需檢查：
1. **功能性**: 代碼是否實現需求？
2. **規範性**: 是否遵循專案規範？
3. **測試性**: 測試覆蓋是否充足？
4. **安全性**: 是否有安全漏洞？
5. **性能**: 是否有性能問題？
6. **可維護性**: 代碼是否易於維護？
7. **兼容性**: API 是否向後兼容？（遷移專用）

### Review Comments 類型

- `MUST`: 必須修改才能合併
- `SHOULD`: 強烈建議修改
- `COULD`: 可選優化
- `QUESTION`: 需要解釋或討論

---

## 🚨 緊急修復流程

### 何時使用 Hotfix？

- 生產環境重大 bug
- 安全漏洞
- 數據損壞風險
- 服務中斷

### Hotfix 流程

1. **評估嚴重性**: 確認需要緊急修復
2. **創建 hotfix 分支**: 從 `main` 分支創建
3. **快速修復**: 最小化變更，只修復問題
4. **測試**: 快速但完整的測試
5. **部署**: 同時合併到 `main` 和 `develop`
6. **通知**: 通知團隊和用戶

---

## 🔐 保護規則

### main 分支保護

- ✅ 需要 PR 才能合併
- ✅ 需要至少 1 人審查
- ✅ 需要通過 CI/CD 檢查
- ✅ 需要管理員審批（重大變更）
- ❌ 禁止直接 push
- ❌ 禁止強制 push

### develop 分支保護

- ✅ 需要 PR 才能合併
- ✅ 需要通過 CI/CD 檢查
- ⚠️ 允許直接 push（緊急情況）
- ❌ 禁止強制 push

---

## 📊 版本號規範

遵循 **Semantic Versioning 2.0.0** (semver.org)

```
MAJOR.MINOR.PATCH

範例：
1.0.0 - 初始版本
1.0.1 - Bug 修復
1.1.0 - 新功能（向後兼容）
2.0.0 - 重大變更（不向後兼容）
```

### 版本號規則

- **MAJOR**: 不向後兼容的 API 變更（Laravel 遷移會是 2.0.0）
- **MINOR**: 向後兼容的新功能
- **PATCH**: 向後兼容的 bug 修復

---

## 🎯 最佳實踐

### 1. 頻繁 Commit
- 每個邏輯變更一個 commit
- Commit message 清晰描述變更
- 避免大型 commit（難以審查）

### 2. 保持分支更新
```bash
# 定期同步 develop
git checkout develop
git pull origin develop
git checkout feature/your-feature
git rebase develop
```

### 3. 使用 Rebase 保持歷史清晰
```bash
# 在 PR 前整理 commits
git rebase -i develop
```

### 4. 刪除已合併分支
```bash
# 本地刪除
git branch -d feature/old-feature

# 遠端刪除
git push origin --delete feature/old-feature
```

### 5. 使用 Git Hooks
- **pre-commit**: 代碼格式檢查（PSR-12, ESLint）
- **commit-msg**: Commit message 格式檢查
- **pre-push**: 執行測試

---

## 🛠️ 相關 Commands

使用這些 commands 簡化 Git Flow：

```bash
/git-flow-init              # 初始化 Git Flow
/feature-start <name>       # 開始新功能
/feature-finish             # 完成功能（創建 PR）
/release-start <version>    # 開始發布
/release-finish             # 完成發布
/hotfix-start <version>     # 開始熱修復
/hotfix-finish              # 完成熱修復
/migration-start <module>   # 開始遷移模組
/migration-finish           # 完成遷移模組
```

---

## 📚 參考資源

- [Git Flow 原始文章](https://nvie.com/posts/a-successful-git-branching-model/)
- [Semantic Versioning](https://semver.org/)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

**維護者**: Development Team
**最後更新**: 2026-01-09
**版本**: 1.0
