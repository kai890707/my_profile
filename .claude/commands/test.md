# 執行全面測試

**功能**: $ARGUMENTS

**任務**: 使用 QA Engineer Agent 執行全面性測試（API、Frontend E2E、整合測試）

---

## 🔴 重要：使用 QA Engineer Agent

**所有測試任務必須使用 `qa-engineer` agent**：

```
當需要執行全面測試時，必須使用 Task tool 啟動 qa-engineer agent
```

**qa-engineer 負責**：
- ✅ API 測試（cURL、REST Client、測試腳本）
- ✅ Frontend E2E 測試（Playwright）
- ✅ 前後端整合測試
- ✅ 視覺回歸測試
- ✅ 效能測試
- ✅ 可及性測試
- ✅ 生成測試報告

**範例**：
```
Task tool:
- subagent_type: qa-engineer
- prompt: 對 <feature-name> 執行全面測試，包括 API、E2E、整合測試，並生成測試報告
```

詳見：`.claude/agents/qa-engineer.md`

---

## 測試範圍

### 1. API 測試
- ✅ 認證流程測試
- ✅ CRUD 操作測試
- ✅ 業務規則測試
- ✅ 錯誤處理測試
- ✅ 驗證規則測試
- ✅ 效能測試（回應時間）

### 2. Frontend E2E 測試
- ✅ 使用者流程測試
- ✅ 表單互動測試
- ✅ 導航測試
- ✅ 錯誤處理測試
- ✅ Loading 狀態測試
- ✅ 響應式設計測試

### 3. 整合測試
- ✅ Frontend + Backend 資料流測試
- ✅ 認證流程整合測試
- ✅ Optimistic Update 測試
- ✅ Cache 策略測試
- ✅ 錯誤處理整合測試

### 4. 視覺回歸測試
- ✅ 頁面截圖比對
- ✅ Dark Mode 測試
- ✅ 響應式佈局測試

### 5. 效能測試
- ✅ Core Web Vitals (LCP, FID, CLS)
- ✅ API 回應時間
- ✅ 首次載入時間
- ✅ Bundle size 檢查

### 6. 可及性測試
- ✅ 鍵盤導航
- ✅ Screen Reader 友善性
- ✅ ARIA 屬性檢查
- ✅ 色彩對比度

---

## 執行流程

### Step 1: 環境檢查

qa-engineer agent 會先檢查：
```bash
# 檢查後端服務
curl -s http://localhost:8080/api/health

# 檢查前端服務
curl -s http://localhost:3000

# 如果服務未啟動，提示用戶啟動
```

### Step 2: 讀取規格（如果存在）

從 OpenSpec 讀取規格來驗證實作：
```
openspec/changes/<feature-name>/specs/
├── api.md            # API 規格
├── ui-ux.md         # UI/UX 規格
├── business-rules.md # 業務規則
└── acceptance-criteria.md # 驗收標準
```

### Step 3: 執行 API 測試

使用測試腳本或 cURL 執行：
```bash
# 執行 API 測試腳本
bash tests/api-tests.sh

# 或使用 REST Client
code tests/api.http

# 驗證：
# - 所有端點可訪問
# - Request/Response 格式符合規格
# - 錯誤處理正確
# - 業務規則生效
# - 驗證規則正確
```

### Step 4: 執行 Frontend E2E 測試

使用 Playwright 執行：
```bash
cd frontend

# 執行所有 E2E 測試
npx playwright test

# 或執行特定功能測試
npx playwright test tests/e2e/<feature>.spec.ts

# 驗證：
# - 所有頁面正常載入
# - 使用者流程正常
# - 表單互動正常
# - 錯誤處理正確
# - Loading 狀態顯示
```

### Step 5: 執行整合測試

執行整合測試腳本：
```bash
# 執行整合測試
bash tests/integration-tests.sh

# 驗證：
# - Frontend 創建資料 → Backend 儲存成功
# - Backend 資料 → Frontend 正確顯示
# - 認證流程完整
# - Error handling 整合正確
```

### Step 6: 執行視覺回歸測試（可選）

```bash
cd frontend

# 執行視覺測試
npx playwright test tests/visual/

# 如果有差異，生成對比報告
```

### Step 7: 執行效能測試

```bash
# 測試 Core Web Vitals
cd frontend
npx playwright test tests/performance/

# 測試 API 回應時間
bash tests/api-performance.sh
```

### Step 8: 生成測試報告

qa-engineer agent 會生成完整測試報告：
```markdown
# 測試報告

**功能**: <feature-name>
**測試日期**: 2026-01-11
**測試人員**: QA Engineer Agent

## 執行摘要

| 測試類型 | 總數 | 通過 | 失敗 | 通過率 |
|---------|-----|------|------|--------|
| API 測試 | 45 | 43 | 2 | 95.6% |
| Frontend E2E | 32 | 30 | 2 | 93.8% |
| 整合測試 | 15 | 14 | 1 | 93.3% |
| **總計** | **92** | **87** | **5** | **94.6%** |

## 問題總結

### 🔴 Critical
1. [問題描述]

### 🟡 Medium
2. [問題描述]

## 建議
- 短期建議
- 中期建議
- 長期建議
```

---

## 測試品質標準

### 通過標準
- ✅ API 測試通過率 >= 95%
- ✅ Frontend E2E 測試通過率 >= 90%
- ✅ 整合測試通過率 >= 95%
- ✅ 無 Critical 問題
- ✅ 效能指標符合目標

### 效能標準
- ✅ API 回應時間 < 500ms (P95)
- ✅ 首次載入時間 < 3s
- ✅ LCP < 2.5s
- ✅ FID < 100ms
- ✅ CLS < 0.1

### 可及性標準
- ✅ 鍵盤可完整操作
- ✅ Screen Reader 可訪問
- ✅ 色彩對比度符合 WCAG AA
- ✅ ARIA 屬性正確

---

## 使用場景

### 場景 1: 功能開發完成後測試

```bash
# 開發完成，執行全面測試
/test user-registration

# qa-engineer agent 會：
# 1. 執行所有測試類型
# 2. 生成測試報告
# 3. 列出需要修復的問題
```

### 場景 2: PR 提交前測試

```bash
# 在 feature-finish 前執行測試
/test <feature-name>

# 確保所有測試通過才執行 /feature-finish
```

### 場景 3: 回歸測試

```bash
# 修改現有功能後，執行回歸測試
/test salesperson-search

# 確保舊功能未破壞
```

### 場景 4: 效能測試

```bash
# 專注於效能測試
/test performance

# qa-engineer 會執行效能相關測試
```

---

## 測試報告位置

測試完成後，報告會存放在：
```
tests/reports/
├── test-report-<timestamp>.md    # 主報告
├── api-test-results.json        # API 測試結果
├── playwright-report/           # E2E 測試報告
│   └── index.html
├── screenshots/                 # 失敗截圖
│   ├── test-1-failure.png
│   └── test-2-failure.png
└── performance-report.json      # 效能報告
```

---

## 常見問題

### Q1: 測試失敗怎麼辦？

A: qa-engineer agent 會提供：
1. 失敗原因分析
2. 重現步驟
3. 修復建議
4. 相關文檔

### Q2: 如何只執行特定類型測試？

A: 可以在 prompt 中指定：
```bash
/test api-only  # 只執行 API 測試
/test e2e-only  # 只執行 E2E 測試
/test integration-only  # 只執行整合測試
```

### Q3: 測試環境如何準備？

A: qa-engineer agent 會檢查並提示：
- Backend 是否運行
- Frontend 是否運行
- 測試資料庫是否就緒
- 測試工具是否安裝

---

## 與其他命令的整合

### 在 /implement 後執行

```bash
# 1. 開發功能
/implement add-rating-feature

# 2. 執行測試
/test add-rating-feature

# 3. 修復問題（如有）

# 4. 完成開發
/feature-finish
```

### 在 /feature-finish 中自動執行

`/feature-finish` 會自動調用 qa-engineer 執行測試

---

## 輸出範例

```
🧪 Starting Comprehensive Tests...

========================================
Step 1: Environment Check
========================================
✅ Backend is running (http://localhost:8080)
✅ Frontend is running (http://localhost:3000)

========================================
Step 2: Reading Specifications
========================================
📖 Reading specs from: openspec/changes/add-rating-feature/specs/
✅ API specs loaded
✅ Business rules loaded
✅ Acceptance criteria loaded

========================================
Step 3: API Tests
========================================
Running 45 API tests...
✅ Authentication Tests (5/5 passed)
✅ CRUD Tests (28/30 passed)
❌ Performance Tests (10/10 passed)
⚠️  2 tests failed

Failures:
  ❌ Bulk create users - timeout
  ❌ Concurrent updates - race condition

========================================
Step 4: Frontend E2E Tests
========================================
Running 32 E2E tests...
✅ Login Flow (5/5 passed)
✅ Search Functionality (10/12 passed)
❌ 2 tests failed

Failures:
  ❌ Pagination - button not clickable
  ❌ Sort by latest - incorrect order

========================================
Step 5: Integration Tests
========================================
Running 15 integration tests...
✅ 14/15 passed
❌ 1 test failed

Failures:
  ❌ Optimistic update rollback

========================================
Step 6: Performance Tests
========================================
✅ API Response Time: 145ms (target: <200ms)
✅ Page Load Time: 2.1s (target: <3s)
✅ LCP: 1.8s (target: <2.5s)
✅ FID: 45ms (target: <100ms)
✅ CLS: 0.03 (target: <0.1)

========================================
📊 Test Summary
========================================
Total Tests: 92
Passed: 87
Failed: 5
Pass Rate: 94.6%

Critical Issues: 2
Medium Issues: 3

========================================
🔴 Critical Issues (Must Fix)
========================================
1. Race Condition in Concurrent Updates
   - Impact: Data integrity
   - Suggestion: Implement optimistic locking

2. Optimistic Update Rollback Failure
   - Impact: User experience
   - Suggestion: Fix React Query error handler

========================================
📝 Test Report Generated
========================================
Report location: tests/reports/test-report-20260111-143022.md
Screenshots: tests/reports/screenshots/
Playwright report: tests/reports/playwright-report/index.html

🔧 Next Steps:
1. Fix critical issues (#1, #2)
2. Re-run tests
3. Once all tests pass, proceed with /feature-finish
```

---

**相關命令**:
- `/implement` - 開發功能
- `/feature-finish` - 完成功能（包含測試）
- `/pr-review` - PR 審查
