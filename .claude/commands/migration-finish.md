# 完成架構遷移模組

完成遷移模組開發，通過 API 兼容性測試，創建 Pull Request。

---

## 執行時機

當遷移模組開發完成，通過所有測試後使用此命令。

---

## 執行步驟

### 1. 檢查當前狀態

```bash
# 確認在 migration 分支上
git branch --show-current

# 檢查是否有未提交的變更
git status
```

### 2. 執行完整測試套件

#### A. 單元測試和整合測試

```bash
# Laravel 測試
cd my_profile_laravel
php artisan test --coverage --min=80

# 確保測試覆蓋率達到 80%+
```

#### B. API 兼容性測試（關鍵！）

創建並執行 API 兼容性測試腳本：

```bash
# 1. 啟動兩個服務
# CI4: http://localhost:8080
# Laravel: http://localhost:8081

# 2. 執行兼容性測試
./scripts/test-api-compatibility.sh

# 3. 檢查測試報告
cat reports/api-compatibility-test.log
```

**測試內容**：
- 比對所有 endpoint 的 Response 格式
- 驗證錯誤處理一致性
- 確認認證流程兼容
- 檢查權限控制一致

#### C. 前端整合測試

```bash
# 1. 啟動 Frontend (Next.js)
cd frontend
npm run dev

# 2. 切換 API Base URL 到 Laravel
# 修改 .env.local
NEXT_PUBLIC_API_URL=http://localhost:8081/api

# 3. 手動測試前端功能
# - 登入/登出
# - 搜尋業務員
# - Dashboard 功能
# - Admin 功能

# 4. 確認無錯誤
```

### 3. 遷移完成度檢查

使用 `AskUserQuestion` 詢問用戶確認：

**遷移檢查清單**:
- [ ] 所有功能已遷移完成
- [ ] 單元測試通過（80%+ 覆蓋率）
- [ ] API 兼容性測試通過（100%）
- [ ] 前端整合測試通過
- [ ] 代碼符合 PHP Pro 標準
- [ ] PHPStan Level 9 通過
- [ ] PSR-12 格式檢查通過
- [ ] 文檔已更新

如果有未完成項目，停止並提醒用戶先完成。

### 4. 代碼質量檢查

```bash
# PSR-12 代碼格式檢查
./vendor/bin/phpcs --standard=PSR12 app/

# PHPStan 靜態分析
./vendor/bin/phpstan analyse --level=9 app/

# 如果有問題，修復後再繼續
```

### 5. 提交所有變更

```bash
# 如果有未提交的變更
git add .
git commit -m "migrate(<module>): <description>

- 功能描述 1
- 功能描述 2

API Compatibility: ✅ Passed
Tests: ✅ XX tests, 8X% coverage
Code Quality: ✅ PHPStan Level 9, PSR-12
"
```

### 6. 同步 develop 分支

```bash
# 拉取最新的 develop
git fetch origin develop

# Merge develop（遷移分支使用 merge，不用 rebase）
git merge origin/develop

# 如果有衝突，引導用戶解決
```

### 7. 推送到遠端

```bash
git push origin migration/laravel/<module-name>
```

### 8. 生成測試報告

創建測試報告文件：

```bash
# 生成 API 兼容性報告
./scripts/generate-compatibility-report.sh > reports/migration-<module>-compatibility.md

# 生成測試覆蓋率報告
php artisan test --coverage-html reports/coverage
```

### 9. 創建 Pull Request

使用 `Bash` 工具執行 `gh` 命令創建 PR：

```bash
gh pr create --base develop --head migration/laravel/<module-name> \
  --title "migrate: Laravel migration - <module-name>" \
  --body "$(cat <<'EOF'
## 📝 遷移摘要
Laravel 遷移 - <模組名稱>

## 🎯 遷移範圍
- 功能 1
- 功能 2
- 功能 3

## 🔄 變更類型
- [x] 架構遷移 (migrate)

## 📋 遷移內容

### 已遷移功能
- ✅ 功能點 1
- ✅ 功能點 2
- ✅ 功能點 3

### 技術實現
- **Framework**: Laravel 11.x
- **PHP Version**: 8.3+
- **Architecture**: Service Layer + Repository Pattern
- **Type Safety**: Strict types, PHPStan Level 9
- **Standards**: PSR-12 compliant

## 🧪 測試結果

### 單元測試 ✅
```
Tests: XX passed
Coverage: XX%
PHPStan: Level 9 passed
PSR-12: Compliant
```

### API 兼容性測試 ✅
```
Endpoints tested: XX
CI4 vs Laravel: 100% compatible
Error handling: Consistent
Authentication: Compatible
```

### 前端整合測試 ✅
- ✅ 登入/登出功能正常
- ✅ API 調用無錯誤
- ✅ 數據顯示正確
- ✅ 無 console errors

## 📊 性能比較

| Metric | CI4 | Laravel | Change |
|--------|-----|---------|--------|
| Response Time | XXms | XXms | ±XX% |
| Memory Usage | XXmb | XXmb | ±XX% |

## 🔗 相關連結
- 遷移規格: `openspec/changes/laravel-migration/specs/`
- 測試報告: `reports/migration-<module>-compatibility.md`
- CI4 實現: `my_profile_ci4/app/`

## ⚠️ 注意事項
- API 完全向後兼容，前端無需修改
- 已通過所有兼容性測試
- 代碼符合 PHP Pro 標準

## ✅ Checklist
- [x] 功能遷移完成
- [x] API 兼容性 100%
- [x] 測試覆蓋率 ≥ 80%
- [x] PHPStan Level 9
- [x] PSR-12 compliant
- [x] 文檔已更新
- [x] 前端測試通過
EOF
)"
```

### 10. 指定審查者

```bash
# 添加審查者
gh pr edit --add-reviewer <reviewer-username>

# 添加標籤
gh pr edit --add-label "migration,laravel,high-priority"
```

### 11. 輸出結果

告知用戶：
- PR 已創建
- 測試結果摘要
- API 兼容性確認
- 下一步操作

範例輸出：
```
✅ Laravel 遷移模組 PR 已創建！

📋 遷移資訊:
- 模組: 03-auth-module (JWT 認證系統)
- 分支: migration/laravel/03-auth-module
- PR 連結: https://github.com/user/repo/pull/123

🧪 測試結果:
✅ 單元測試: 42 tests passed, 85% coverage
✅ PHPStan: Level 9 passed
✅ PSR-12: Compliant
✅ API 兼容性: 100% (15/15 endpoints)
✅ 前端整合: 無錯誤

⏳ 下一步:
1. 等待 Code Review
2. 審查通過後合併到 develop
3. 繼續下一個遷移模組

📚 測試報告:
- API 兼容性: reports/migration-auth-compatibility.md
- 測試覆蓋率: reports/coverage/index.html

🎯 遷移進度:
✅ 01-project-setup
✅ 02-database-layer
✅ 03-auth-module (當前)
⏳ 04-api-endpoints (下一個)
⏳ 05-business-logic
⏳ 06-testing
⏳ 07-deployment
```

---

## Code Review 重點（遷移專用）

審查者需特別檢查：

### 1. API 兼容性（最重要！）
- ✅ Endpoint 路徑一致
- ✅ Request 參數格式一致
- ✅ Response JSON 結構一致
- ✅ HTTP 狀態碼一致
- ✅ 錯誤訊息格式一致

### 2. 代碼質量
- ✅ 遵循 PHP Pro 標準
- ✅ 使用 Laravel 最佳實踐
- ✅ Strict types declaration
- ✅ Service Layer 架構
- ✅ Repository Pattern

### 3. 測試覆蓋
- ✅ 單元測試覆蓋率 ≥ 80%
- ✅ API 測試完整
- ✅ 邊界條件測試
- ✅ 錯誤處理測試

### 4. 文檔更新
- ✅ OpenSpec 規格更新
- ✅ API 文檔更新
- ✅ 遷移決策記錄

---

## 常見問題處理

### 問題 1: API 兼容性測試失敗

如果發現不兼容：
1. 分析差異（Response 格式、狀態碼、錯誤訊息）
2. 調整 Laravel 實現以匹配 CI4
3. 重新測試直到 100% 兼容

### 問題 2: 測試覆蓋率不足

如果覆蓋率 < 80%：
1. 識別未覆蓋的代碼
2. 添加缺失的測試
3. 特別關注 edge cases

### 問題 3: 前端整合失敗

如果前端出現錯誤：
1. 檢查 API Response 格式
2. 確認 CORS 配置
3. 驗證認證流程
4. 檢查錯誤處理

---

## 參考文檔

- Git Flow 工作流程: `.claude/workflows/GIT_FLOW.md`
- PHP Pro Skill: `.claude/skills/php-pro/SKILL.md`
- 遷移規格: `openspec/changes/laravel-migration/`

---

## 相關 Commands

- `/migration-start` - 開始下一個遷移模組
- `/pr-review` - 審查 Pull Request
