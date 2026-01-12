# Implementation Tasks: 修復 Avatar Fallback 的 TypeError Bug

**功能名稱**: fix-salesperson-card-typeerror
**預計時間**: 2 小時
**優先級**: High

---

## Phase 1: 建立工具函數與測試 (30 分鐘)

### Task 1.1: 建立 Avatar 工具函數
**檔案**: `frontend/lib/utils/avatar.ts` (新建)
**描述**: 建立 `getAvatarFallback()` 函數，實作 5 層 fallback 策略
**驗收標準**:
- [ ] 檔案已建立在正確位置
- [ ] 函數簽名正確
- [ ] 實作完整的 5 層 fallback 邏輯
- [ ] 包含完整的 JSDoc 註解
- [ ] TypeScript 類型定義正確
- [ ] 無 TypeScript 錯誤

**參考**: `specs/implementation.md` - Section 3.1

### Task 1.2: 建立單元測試檔案
**檔案**: `frontend/lib/utils/__tests__/avatar.test.ts` (新建)
**描述**: 建立測試檔案並撰寫 27 個測試案例
**驗收標準**:
- [ ] 檔案已建立在正確位置
- [ ] 包含所有 27 個測試案例
- [ ] 測試分為 7 個 describe 區塊
- [ ] 測試覆蓋所有 fallback 層級
- [ ] 測試覆蓋所有邊界情況
- [ ] 無語法錯誤

**參考**: `specs/implementation.md` - Section 4

### Task 1.3: 執行單元測試
**命令**: `cd frontend && npm test avatar.test.ts`
**描述**: 執行測試確保所有案例通過
**驗收標準**:
- [ ] 所有 27 個測試通過
- [ ] 測試覆蓋率 100%
- [ ] 無測試失敗
- [ ] 執行時間 < 5 秒

---

## Phase 2: 修復組件 (45 分鐘)

### Task 2.1: 修復 SalespersonCard 組件
**檔案**: `frontend/components/features/search/salesperson-card.tsx` (修改)
**描述**: 替換直接呼叫 `.substring()` 為使用 `getAvatarFallback()`
**修改位置**: Line 43
**驗收標準**:
- [ ] 已 import `getAvatarFallback`
- [ ] 移除 `salesperson.full_name.substring(0, 2)`
- [ ] 替換為 `getAvatarFallback(salesperson)`
- [ ] TypeScript 檢查通過
- [ ] 組件可正常編譯

**參考**: `specs/implementation.md` - Section 3.2

### Task 2.2: 重構 Header 組件
**檔案**: `frontend/components/layout/header.tsx` (修改)
**描述**: 將現有的內聯 fallback 邏輯重構為使用統一函數
**修改位置**: Lines 95-101
**驗收標準**:
- [ ] 已 import `getAvatarFallback`
- [ ] 移除內聯的 fallback 邏輯
- [ ] 替換為 `getAvatarFallback(user)`
- [ ] 功能保持一致
- [ ] 程式碼更簡潔
- [ ] TypeScript 檢查通過

**參考**: `specs/components.md` - Section 4

### Task 2.3: 修復 Dashboard 頁面
**檔案**: `frontend/app/(dashboard)/dashboard/page.tsx` (修改)
**描述**: 修復 Dashboard 頁面中的 Avatar fallback
**驗收標準**:
- [ ] 找到所有使用 Avatar 的地方
- [ ] 已 import `getAvatarFallback`
- [ ] 替換為使用統一函數
- [ ] TypeScript 檢查通過
- [ ] 頁面可正常渲染

**參考**: `specs/implementation.md` - Section 3.4

### Task 2.4: 修復 Salesperson Detail 頁面
**檔案**: `frontend/app/salesperson/[id]/page.tsx` (修改)
**描述**: 修復業務員詳細頁面中的 Avatar fallback
**驗收標準**:
- [ ] 找到所有使用 Avatar 的地方
- [ ] 已 import `getAvatarFallback`
- [ ] 替換為使用統一函數
- [ ] TypeScript 檢查通過
- [ ] 頁面可正常渲染

**參考**: `specs/implementation.md` - Section 3.4

### Task 2.5: 搜尋其他可能的問題點
**命令**: `cd frontend && grep -r "\.substring(0, 2)" --include="*.tsx" --include="*.ts"`
**描述**: 確保沒有遺漏其他直接呼叫 `.substring()` 的地方
**驗收標準**:
- [ ] 已執行搜尋命令
- [ ] 檢查所有搜尋結果
- [ ] 確認沒有其他危險的 `.substring()` 呼叫
- [ ] 記錄檢查結果

---

## Phase 3: 測試驗證 (30 分鐘)

### Task 3.1: 執行 TypeScript 類型檢查
**命令**: `cd frontend && npm run typecheck`
**描述**: 確保所有修改通過 TypeScript 嚴格檢查
**驗收標準**:
- [ ] TypeScript 編譯通過
- [ ] 無類型錯誤
- [ ] 無類型警告

### Task 3.2: 執行 ESLint 檢查
**命令**: `cd frontend && npm run lint`
**描述**: 確保程式碼符合專案規範
**驗收標準**:
- [ ] ESLint 檢查通過
- [ ] 無 lint 錯誤
- [ ] 無 lint 警告

### Task 3.3: 執行所有單元測試
**命令**: `cd frontend && npm test`
**描述**: 確保所有測試通過，沒有破壞現有功能
**驗收標準**:
- [ ] 所有測試通過
- [ ] 新增的測試通過
- [ ] 現有測試未被破壞
- [ ] 測試覆蓋率未下降

### Task 3.4: 手動測試 - 搜尋頁面
**頁面**: http://localhost:3001/search
**描述**: 測試業務員搜尋頁面的 Avatar 顯示
**測試步驟**:
1. 訪問搜尋頁面
2. 搜尋業務員
3. 檢查 Avatar fallback 顯示
4. 測試有/無 full_name 的業務員

**驗收標準**:
- [ ] 頁面正常載入
- [ ] SalespersonCard 正常顯示
- [ ] Avatar fallback 正確
- [ ] 無 Console 錯誤
- [ ] 無 TypeError

### Task 3.5: 手動測試 - 首頁
**頁面**: http://localhost:3001/
**描述**: 測試首頁的業務員列表顯示
**測試步驟**:
1. 訪問首頁
2. 檢查業務員列表
3. 檢查 Avatar 顯示

**驗收標準**:
- [ ] 頁面正常載入
- [ ] 業務員列表正常顯示
- [ ] Avatar fallback 正確
- [ ] 無 Console 錯誤

### Task 3.6: 手動測試 - Dashboard
**頁面**: http://localhost:3001/dashboard
**描述**: 測試業務員 Dashboard 的 Avatar 顯示
**測試步驟**:
1. 登入為業務員
2. 訪問 Dashboard
3. 檢查個人資料 Avatar

**驗收標準**:
- [ ] Dashboard 正常載入
- [ ] Avatar fallback 正確
- [ ] 無 Console 錯誤

### Task 3.7: 手動測試 - Header
**位置**: 所有頁面右上角
**描述**: 測試 Header 使用者選單的 Avatar
**測試步驟**:
1. 登入為業務員
2. 檢查 Header Avatar
3. 點擊下拉選單
4. 檢查名稱顯示

**驗收標準**:
- [ ] Header Avatar 正常顯示
- [ ] Fallback 策略正確
- [ ] 下拉選單正常
- [ ] 名稱顯示正確

### Task 3.8: 手動測試 - 業務員詳細頁面
**頁面**: http://localhost:3001/salesperson/[id]
**描述**: 測試業務員詳細頁面的 Avatar
**測試步驟**:
1. 訪問任意業務員詳細頁面
2. 檢查大頭貼 Avatar
3. 測試多個不同業務員

**驗收標準**:
- [ ] 詳細頁面正常載入
- [ ] Avatar fallback 正確
- [ ] 無 Console 錯誤

### Task 3.9: 跨瀏覽器測試
**瀏覽器**: Chrome, Firefox, Safari
**描述**: 確保在主流瀏覽器中都正常運作
**驗收標準**:
- [ ] Chrome 測試通過
- [ ] Firefox 測試通過
- [ ] Safari 測試通過（如可用）
- [ ] 所有瀏覽器無錯誤

---

## Phase 4: 文檔與歸檔 (15 分鐘)

### Task 4.1: 更新 TypeScript 類型定義
**檔案**: `frontend/types/api.ts` (如需要)
**描述**: 確認 API 類型定義中 `full_name` 標註為可選
**驗收標準**:
- [ ] 檢查 `SalespersonSearchResult` 介面
- [ ] 確認 `full_name?: string | null`
- [ ] 檢查其他相關介面
- [ ] 類型定義準確反映實際資料結構

### Task 4.2: Git Commit - 建立工具函數
**命令**: `git add frontend/lib/utils/avatar.ts frontend/lib/utils/__tests__/avatar.test.ts`
**描述**: 提交工具函數和測試
**Commit Message**:
```
feat: Add getAvatarFallback utility function

- Implement 5-tier fallback strategy (full_name → name → username → email → 'U')
- Add 27 unit tests with 100% coverage
- Handle null, undefined, and empty string cases
- Support single-character names

Related to: fix-salesperson-card-typeerror
```

**驗收標準**:
- [ ] 檔案已 staged
- [ ] Commit message 清晰
- [ ] Commit 已建立

### Task 4.3: Git Commit - 修復組件
**命令**: `git add frontend/components/ frontend/app/`
**描述**: 提交組件修改
**Commit Message**:
```
fix: Fix Avatar TypeError in SalespersonCard and other components

- Replace direct .substring() calls with getAvatarFallback()
- Fix SalespersonCard component (Line 43)
- Refactor Header component to use unified function
- Fix Dashboard and Salesperson Detail pages
- Prevent "Cannot read properties of undefined" error

Fixes: TypeError at salesperson-card.tsx:43:47
Related to: fix-salesperson-card-typeerror

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
```

**驗收標準**:
- [ ] 所有修改檔案已 staged
- [ ] Commit message 詳細
- [ ] Commit 已建立

### Task 4.4: Git Push
**命令**: `git push origin main`
**描述**: 推送到遠端倉庫
**驗收標準**:
- [ ] Push 成功
- [ ] 無衝突
- [ ] 遠端倉庫已更新

### Task 4.5: 歸檔規格文件
**任務**: 執行 `/archive fix-salesperson-card-typeerror`
**描述**: 將規格文件歸檔到 OpenSpec 規範庫
**驗收標準**:
- [ ] 規格已移動到 `openspec/changes/archived/`
- [ ] CHANGELOG.md 已更新
- [ ] 歸檔報告已建立
- [ ] Git commit 已建立

---

## 任務摘要

| Phase | 任務數 | 預計時間 | 關鍵產出 |
|-------|--------|----------|----------|
| Phase 1 | 3 | 30 分鐘 | 工具函數 + 測試 |
| Phase 2 | 5 | 45 分鐘 | 修復 4 個檔案 |
| Phase 3 | 9 | 30 分鐘 | 完整測試驗證 |
| Phase 4 | 5 | 15 分鐘 | Git 提交 + 歸檔 |
| **總計** | **22** | **2 小時** | 無 TypeError Bug |

---

## 執行順序

**嚴格按照以下順序執行**:

```
Phase 1: 建立基礎
  Task 1.1 → Task 1.2 → Task 1.3
  ✅ 確保測試通過才繼續

Phase 2: 修復組件
  Task 2.1 → Task 2.2 → Task 2.3 → Task 2.4 → Task 2.5
  ✅ 每修復一個檔案就檢查 TypeScript

Phase 3: 全面測試
  Task 3.1 → Task 3.2 → Task 3.3
  ✅ 自動化測試必須全部通過
  Task 3.4 → Task 3.5 → Task 3.6 → Task 3.7 → Task 3.8 → Task 3.9
  ✅ 手動測試確保使用者體驗正常

Phase 4: 提交與歸檔
  Task 4.1 → Task 4.2 → Task 4.3 → Task 4.4 → Task 4.5
  ✅ 規範流程完成
```

---

## 注意事項

⚠️ **必須遵守**:
- 不能跳過任何測試
- 不能在測試失敗時繼續
- 不能修改 Avatar 組件本身
- 不能改變 API 回應結構

✅ **最佳實踐**:
- 每完成一個任務就標記為 completed
- 遇到錯誤立即修復，不累積
- 保持程式碼整潔和一致性
- 測試多種資料情境

---

**建立日期**: 2026-01-12
**預計開始**: 立即
**預計完成**: 2 小時後
