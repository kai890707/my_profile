# Pull Request 審查

執行 Pull Request 的代碼審查流程。

---

## 執行時機

當收到 PR 審查請求時使用此命令。

---

## 參數

```bash
/pr-review <pr-number>
```

- `<pr-number>`: Pull Request 編號

範例:
- `/pr-review 123`

---

## 執行步驟

### 1. 獲取 PR 資訊

```bash
# 使用 gh CLI 獲取 PR 詳情
gh pr view <pr-number>

# 查看 PR 的 diff
gh pr diff <pr-number>

# 查看 PR 的文件變更
gh pr view <pr-number> --web
```

### 2. Checkout PR 分支

```bash
# Checkout PR 分支到本地
gh pr checkout <pr-number>

# 或手動 checkout
git fetch origin pull/<pr-number>/head:pr-<pr-number>
git checkout pr-<pr-number>
```

### 3. 執行自動化檢查

#### A. 代碼格式檢查

**Backend (PHP)**:
```bash
# PSR-12 檢查
./vendor/bin/phpcs --standard=PSR12 app/

# 自動修復（如需要）
./vendor/bin/phpcbf --standard=PSR12 app/
```

**Frontend (TypeScript/JavaScript)**:
```bash
# ESLint 檢查
npm run lint

# TypeScript 檢查
npm run typecheck
```

#### B. 靜態分析

**Backend**:
```bash
# PHPStan 分析
./vendor/bin/phpstan analyse --level=9 app/
```

**Frontend**:
```bash
# TypeScript 編譯檢查
npx tsc --noEmit
```

#### C. 執行測試

**Backend**:
```bash
# 單元測試
php artisan test

# 測試覆蓋率
php artisan test --coverage --min=80
```

**Frontend**:
```bash
# 單元測試
npm test

# 構建測試
npm run build
```

### 4. 執行手動代碼審查

使用 `Read` 工具閱讀變更的文件，檢查以下項目：

#### A. 功能性審查
- ✅ 代碼是否實現了 PR 描述的功能？
- ✅ 邏輯是否正確？
- ✅ 是否處理了邊界條件？
- ✅ 錯誤處理是否完善？

#### B. 代碼質量審查
- ✅ 命名是否清晰易懂？
- ✅ 函數是否保持簡單（單一職責）？
- ✅ 是否有重複代碼？
- ✅ 是否遵循 DRY 原則？
- ✅ 註釋是否充足且有意義？

#### C. 規範審查（參考對應 CLAUDE.md）

**Backend** (參考 `my_profile_ci4/CLAUDE.md` 或 Laravel 規範):
- ✅ PSR-12 代碼格式
- ✅ Type declarations
- ✅ Service Layer 架構
- ✅ Repository Pattern
- ✅ Strict types
- ✅ PHPDoc 註釋

**Frontend** (參考 `frontend/CLAUDE.md`):
- ✅ Component 設計原則
- ✅ 命名規範（PascalCase for components）
- ✅ Props 類型定義
- ✅ 使用設計系統
- ✅ 響應式設計

#### D. 安全性審查
- ✅ 是否有 SQL 注入風險？
- ✅ 是否有 XSS 風險？
- ✅ 輸入驗證是否完整？
- ✅ 敏感資料是否加密？
- ✅ 認證和授權是否正確？

#### E. 性能審查
- ✅ 是否有 N+1 查詢問題？
- ✅ 是否有不必要的循環？
- ✅ 是否正確使用緩存？
- ✅ 是否有內存泄漏風險？

#### F. 測試審查
- ✅ 測試覆蓋率是否達標？
- ✅ 測試是否有意義（不是形式測試）？
- ✅ 是否測試了邊界條件？
- ✅ 是否測試了錯誤處理？

#### G. 文檔審查
- ✅ API 文檔是否更新？
- ✅ OpenSpec 規格是否更新？
- ✅ CHANGELOG 是否更新？
- ✅ README 是否需要更新？

### 5. 遷移 PR 特殊檢查（如適用）

如果是 Laravel 遷移 PR，額外檢查：

#### API 兼容性檢查
```bash
# 執行 API 兼容性測試
./scripts/test-api-compatibility.sh

# 比對結果
diff <(curl -s http://localhost:8080/api/endpoint) \
     <(curl -s http://localhost:8081/api/endpoint)
```

- ✅ Endpoint 路徑一致
- ✅ Request 格式一致
- ✅ Response 格式一致
- ✅ 錯誤處理一致
- ✅ HTTP 狀態碼一致

#### 前端整合測試
```bash
# 切換 API URL 到新版本
# 測試前端功能是否正常
```

- ✅ 前端無錯誤
- ✅ 功能正常運作
- ✅ 數據顯示正確

### 6. 提供審查意見

使用 `gh` 命令提供審查意見：

#### 如果發現必須修改的問題（MUST）

```bash
gh pr review <pr-number> --request-changes --body "發現以下問題需要修改：

**MUST (必須修改)**
1. [文件名:行號] 問題描述
   - 原因：...
   - 建議：...

2. [文件名:行號] 問題描述
   - 原因：...
   - 建議：...

**SHOULD (強烈建議)**
1. ...

**測試結果**
❌ 單元測試失敗：...
❌ 代碼格式不符：...
"
```

#### 如果有建議但可以通過（SHOULD/COULD）

```bash
gh pr review <pr-number> --comment --body "整體看起來不錯！有一些優化建議：

**SHOULD (強烈建議修改)**
1. [文件名:行號] 建議描述

**COULD (可選優化)**
1. [文件名:行號] 優化建議

**QUESTION (需要討論)**
1. 為什麼選擇這種實現方式？

**測試結果**
✅ 所有測試通過
✅ 代碼格式符合規範
"
```

#### 如果通過審查（APPROVED）

```bash
gh pr review <pr-number> --approve --body "✅ 審查通過！

**檢查項目**
✅ 功能實現正確
✅ 代碼質量良好
✅ 測試覆蓋充足
✅ 符合專案規範
✅ 無安全問題
✅ 性能無問題
✅ 文檔已更新

**測試結果**
✅ 單元測試: XX tests passed
✅ 代碼格式: PSR-12 / ESLint passed
✅ 靜態分析: PHPStan Level 9 passed
✅ 構建: Success

可以合併了！建議使用 Squash and Merge。
"
```

### 7. 特殊情況處理

#### 情況 1: 發現安全漏洞

```bash
# 立即標記為 high-priority
gh pr edit <pr-number> --add-label "security,high-priority"

# 私下通知作者（不公開評論）
gh pr review <pr-number> --request-changes --body "發現安全問題，已私下聯繫。"
```

#### 情況 2: 需要討論設計決策

```bash
# 添加 discussion 標籤
gh pr edit <pr-number> --add-label "discussion"

# 發起討論
gh pr review <pr-number> --comment --body "關於實現方式有一些疑問，希望討論一下：
1. ...
2. ...
"
```

#### 情況 3: PR 太大需要拆分

```bash
gh pr review <pr-number> --comment --body "這個 PR 包含太多變更（XX files changed）。

**建議拆分為**:
1. PR-1: 功能 A
2. PR-2: 功能 B
3. PR-3: 功能 C

這樣更容易審查，也降低風險。
"
```

### 8. 輸出審查報告

生成審查報告給用戶：

```
📋 PR 審查報告

**PR 資訊**
- PR #123: feat: add rating API endpoint
- 作者: @developer
- 變更: 15 files, +500 -200 lines

**自動化檢查**
✅ PSR-12 格式檢查通過
✅ PHPStan Level 9 通過
✅ 測試通過 (42 tests, 85% coverage)
✅ 構建成功

**代碼審查結果**
✅ 功能實現正確
✅ 代碼質量良好
⚠️  有 2 個建議項目（SHOULD）
ℹ️  有 1 個可選優化（COULD）

**審查意見**
已通過 gh pr review 提交

**建議**
- 修改 2 個 SHOULD 項目後可合併
- 使用 Squash and Merge
```

---

## 審查標準參考

### Backend (PHP)
參考文檔:
- `.claude/skills/php-pro/SKILL.md` - PHP Pro 標準
- `my_profile_ci4/CLAUDE.md` - 後端開發規範（CI4）
- Laravel 規範（遷移後）

### Frontend (React/Next.js)
參考文檔:
- `frontend/CLAUDE.md` - 前端開發規範
- `frontend/docs/design-system.md` - 設計系統
- `frontend/docs/testing.md` - 測試指南

---

## Review Comments 類型說明

| 類型 | 說明 | 是否阻擋合併 |
|------|------|--------------|
| `MUST` | 必須修改 | ✅ 是 |
| `SHOULD` | 強烈建議修改 | ⚠️ 建議先修改 |
| `COULD` | 可選優化 | ❌ 否 |
| `QUESTION` | 需要解釋或討論 | 視情況 |
| `NITPICK` | 小問題（可忽略） | ❌ 否 |

---

## 最佳實踐

### 1. 及時審查
- PR 創建後 24 小時內完成審查
- 大型 PR 可能需要更多時間

### 2. 建設性反饋
- 說明問題的原因
- 提供具體的改進建議
- 肯定做得好的地方

### 3. 使用 Code Suggestions
```bash
# 在 GitHub 上使用 suggestion 功能
# 直接提供修改代碼建議
```

### 4. 保持專業
- 對事不對人
- 尊重不同的實現方式
- 鼓勵討論

---

## 參考文檔

- Git Flow 工作流程: `.claude/workflows/GIT_FLOW.md`
- Code Review 標準
- 專案開發規範

---

## 相關 Commands

- `/feature-finish` - 創建 PR
- `/migration-finish` - 創建遷移 PR
