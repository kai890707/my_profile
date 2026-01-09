# 開始架構遷移模組

創建架構遷移分支，專用於 CodeIgniter 4 到 Laravel 的遷移工作。

---

## 執行時機

當需要進行架構遷移時使用此命令（特別是 Laravel 遷移）。

---

## 參數

```bash
/migration-start <module-name>
```

- `<module-name>`: 遷移模組名稱

建議的模組順序：
1. `01-project-setup` - Laravel 專案初始化
2. `02-database-layer` - Models, Migrations, Seeders
3. `03-auth-module` - JWT 認證系統
4. `04-api-endpoints` - Controllers, Routes, Middleware
5. `05-business-logic` - Services, Repositories, Policies
6. `06-testing` - PHPUnit Tests, API Tests
7. `07-deployment` - Docker, CI/CD

---

## 執行步驟

### 1. 確認遷移計劃

使用 `AskUserQuestion` 確認：
- **模組名稱**: 確認正在遷移的模組
- **遷移範圍**: 確認此模組包含哪些功能
- **API 兼容性**: 是否需要保持 API 完全兼容？（答案通常是是）
- **測試計劃**: 如何驗證遷移成功？

### 2. 檢查遷移規格

檢查是否存在遷移規格：
- 讀取 `openspec/changes/laravel-migration/specs/migration-<module>.md`
- 如果不存在，提醒用戶先建立遷移規格：
  ```bash
  /proposal Laravel 遷移 - <模組名稱>
  /spec laravel-migration-<module>
  ```

### 3. 切換到 develop 並更新

```bash
git checkout develop
git pull origin develop
```

### 4. 創建 migration 分支

```bash
# 根據命名規範創建分支
git checkout -b migration/laravel/<module-name>
```

### 5. 推送到遠端

```bash
git push -u origin migration/laravel/<module-name>
```

### 6. 參考 PHP Pro Skill

提醒用戶此遷移將使用 PHP Pro Skill 的標準：
- 閱讀 `.claude/skills/php-pro/SKILL.md`
- 遵循 Laravel 最佳實踐
- 使用 PHP 8.3+ 特性
- 嚴格類型聲明
- PSR-12 代碼規範
- PHPStan Level 9
- 80%+ 測試覆蓋率

### 7. 建立遷移檢查清單

使用 `TodoWrite` 建立遷移任務清單：

範例（模組: auth-module）：
```
- [ ] 分析 CI4 Auth 實現
- [ ] 設計 Laravel Auth 架構
- [ ] 建立 User Model
- [ ] 建立 JWT Service
- [ ] 建立 Auth Controller
- [ ] 建立 Auth Middleware
- [ ] 撰寫 Auth Tests
- [ ] API 兼容性測試
- [ ] 前端整合測試
```

### 8. 開始遷移開發

根據模組類型，引導用戶：

**模組 01: Project Setup**
```bash
# 1. 建立 Laravel 專案
composer create-project laravel/laravel my_profile_laravel

# 2. 配置基礎設置
# - 複製 .env.example
# - 配置資料庫連接
# - 安裝必要套件 (JWT, CORS, etc.)

# 3. 建立 Docker 環境
# - Dockerfile
# - docker-compose.yml
# - 確保與 CI4 並行運行

# 4. 提交初始化
git add .
git commit -m "migrate: initialize Laravel project structure"
```

**模組 02-07: 功能遷移**
使用 OpenSpec SDD 流程：
```bash
# 如果有規格，使用 /develop
/develop laravel-migration-<module>

# 如果沒有規格，使用完整流程
/implement Laravel 遷移 - <模組功能描述>
```

### 9. 確保 API 兼容性

每個模組完成後，必須執行 API 兼容性測試：

```bash
# 1. 啟動 Laravel 服務（不同端口）
php artisan serve --port=8081

# 2. 執行 API 測試腳本
./scripts/test-api-compatibility.sh

# 3. 比對結果
# - CI4 API (port 8080)
# - Laravel API (port 8081)
# - 確保 Response 格式一致
```

### 10. 輸出結果

告知用戶：
- Migration 分支已創建: `migration/laravel/<module-name>`
- 遷移檢查清單已建立
- 當前模組的遷移範圍
- 參考文檔連結

範例輸出：
```
✅ Laravel 遷移分支已創建！

📋 遷移資訊:
- 分支: migration/laravel/03-auth-module
- 模組: JWT 認證系統
- 範圍: User Model, JWT Service, Auth Controller, Middleware

📝 遷移檢查清單:
已建立 9 個任務，使用 TodoWrite 追蹤進度

⚠️ 重要提醒:
1. 必須保持 API 完全兼容（Request/Response 格式）
2. 遵循 PHP Pro Skill 標準 (.claude/skills/php-pro/SKILL.md)
3. 每個 endpoint 都要通過兼容性測試
4. 測試覆蓋率必須達到 80%+

📚 參考文檔:
- 遷移規格: openspec/changes/laravel-migration/specs/
- PHP Pro Skill: .claude/skills/php-pro/SKILL.md
- Laravel 文檔: https://laravel.com/docs
- CI4 實現: my_profile_ci4/app/
```

---

## 遷移最佳實踐

### 1. 小步快跑
- 每個模組獨立遷移
- 頻繁 commit（每個功能點一個 commit）
- 及時測試，發現問題立即修復

### 2. 保持兼容性
```bash
# 對照測試 - 確保輸出一致
# CI4
curl http://localhost:8080/api/auth/login -d '{"email":"test@example.com","password":"test123"}'

# Laravel
curl http://localhost:8081/api/auth/login -d '{"email":"test@example.com","password":"test123"}'

# 比對 JSON 輸出
```

### 3. 參考現有實現
- 閱讀 CI4 代碼了解業務邏輯
- 不要重新設計，保持功能一致
- 只改進代碼結構和質量

### 4. 文檔同步更新
- 更新 OpenSpec 規格
- 更新 API 文檔
- 記錄遷移決策和原因

---

## API 兼容性檢查清單

每個模組完成後檢查：
- [ ] 所有 endpoint 路徑一致
- [ ] Request 參數格式一致
- [ ] Response 格式一致（JSON 結構）
- [ ] HTTP 狀態碼一致
- [ ] 錯誤訊息格式一致
- [ ] 認證機制兼容（JWT Token）
- [ ] 權限檢查邏輯一致

---

## Commit 規範

使用 `migrate` type：

```bash
git commit -m "migrate(auth): convert JWT authentication to Laravel

- Implement JWT service using tymon/jwt-auth
- Create AuthController with login/register/refresh
- Add JWTMiddleware for route protection
- Maintain exact API compatibility with CI4
- Add PHPUnit tests (85% coverage)

API Compatibility: ✅ All endpoints tested
Tests: ✅ 42 tests passed
"
```

---

## 參考文檔

- Git Flow 工作流程: `.claude/workflows/GIT_FLOW.md`
- PHP Pro Skill: `.claude/skills/php-pro/SKILL.md`
- Laravel 文檔: https://laravel.com/docs
- CI4 實現: `my_profile_ci4/`

---

## 相關 Commands

- `/migration-finish` - 完成遷移模組，創建 PR
- `/implement` - OpenSpec 開發流程
- `/develop` - 依據規格開發
