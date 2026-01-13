# Proposal: 修復 Dashboard 編輯模式資料顯示問題

**功能**: 確保個人資料編輯頁面正確顯示當前設定
**類型**: Bug Fix
**優先級**: Medium
**建立日期**: 2026-01-12
**負責人**: Frontend Developer

---

## 1. 問題陳述 (Problem Statement)

### 1.1 現象描述

在 Dashboard 個人資料頁面 (`/dashboard`) 的編輯模式下，Avatar 組件顯示預設的 "U" 而不是使用者的姓名縮寫，這表示表單初始化可能存在問題。

**具體表現**:
- ✅ 檢視模式: Avatar 正確顯示姓名縮寫或頭像
- ❌ 編輯模式: Avatar 顯示預設 "U"
- ⚠️  其他欄位的初始化狀態未確認

### 1.2 用戶影響

**嚴重程度**: Medium
- 用戶進入編輯模式時無法看到當前設定的視覺反饋
- 可能誤以為資料未保存或遺失
- 影響編輯體驗和用戶信心

**影響範圍**: 所有業務員使用者

---

## 2. 根本原因分析 (Root Cause Analysis)

### 2.1 技術問題

**檔案**: `frontend/app/(dashboard)/dashboard/page.tsx`

**問題位置 1**: Line 272 - Avatar 組件

```typescript
// 當前程式碼 (❌ 有問題)
<Avatar
  src={avatarPreview || profileData?.avatar}
  fallback={getAvatarFallback(profileData || {})}  // ← 問題所在
  size="2xl"
/>
```

**問題分析**:
當 `profileData` 為 `undefined` 或資料尚未載入完成時，`profileData || {}` 會傳遞空物件給 `getAvatarFallback()`，導致函數返回預設值 "U"。

**問題位置 2**: Line 197-199 - 編輯按鈕無 Loading 狀態檢查

```typescript
// 當前程式碼
{!editMode && (
  <Button onClick={() => setEditMode(true)}>編輯資料</Button>
)}
```

**問題分析**:
即使 `profileLoading = true`（資料尚未載入），用戶仍可點擊「編輯資料」按鈕進入編輯模式，此時表單資料可能不完整。

### 2.2 資料流分析

```
1. 頁面載入 → profileLoading = true
2. useProfile() 取得資料 → profileData = { full_name: '測試業務員', ... }
3. useEffect (line 86-113) → resetProfile() 初始化表單
4. profileLoading = false → 顯示資料

編輯模式問題流程:
1. 用戶點擊「編輯資料」→ setEditMode(true)
2. Avatar 使用 getAvatarFallback(profileData || {})
   → 如果 profileData 是 undefined → 返回 'U'
   → 如果 profileData 有資料 → 應該返回姓名縮寫
```

**結論**: 主要問題是 Avatar fallback 沒有正確處理 `profileData` 可能為 undefined 的情況。

---

## 3. 解決方案 (Solutions)

### 方案 A: 修復 Avatar Fallback 邏輯 ✅ **推薦**

**描述**: 確保編輯模式的 Avatar fallback 正確使用 `profileData`，而不是空物件。

**實作**:
```typescript
// 修改 Line 272
<Avatar
  src={avatarPreview || profileData?.avatar}
  fallback={profileData ? getAvatarFallback(profileData) : 'U'}
  size="2xl"
/>
```

**優點**:
- ✅ 最小修改，風險低
- ✅ 直接解決 Avatar 顯示問題
- ✅ 不影響其他功能

**缺點**:
- ⚠️  僅修復 Avatar 顯示，未解決根本的資料載入順序問題

---

### 方案 B: 禁止在 Loading 狀態下進入編輯模式 ✅ **推薦 (配合方案 A)**

**描述**: 當資料尚未載入完成時，禁用「編輯資料」按鈕。

**實作**:
```typescript
// 修改 Line 197-199
{!editMode && (
  <Button
    onClick={() => setEditMode(true)}
    disabled={profileLoading}
  >
    編輯資料
  </Button>
)}
```

**優點**:
- ✅ 確保進入編輯模式時資料已完整載入
- ✅ 防止用戶在資料未載入時進行編輯
- ✅ UX 更好，避免困惑

**缺點**:
- ⚠️  Loading 時間較長時，用戶需等待

---

### 方案 C: 在編輯模式下顯示 Loading Skeleton

**描述**: 如果 `profileLoading = true` 且 `editMode = true`，顯示 Skeleton 而不是空白表單。

**實作**:
```typescript
if (editMode && profileLoading) {
  return <ProfileSkeleton />;
}
```

**優點**:
- ✅ 提供更好的 Loading 視覺反饋
- ✅ 明確告知用戶資料正在載入

**缺點**:
- ❌ 複雜度增加
- ❌ 不應該允許在 Loading 時進入編輯模式

---

### 推薦方案: **A + B 組合**

**原因**:
1. 方案 A 修復 Avatar 顯示問題（最小修改）
2. 方案 B 防止資料未載入時進入編輯模式（根本解決）
3. 兩者結合提供最佳 UX

**實作變更**:
- 修改 Avatar fallback 邏輯
- 禁用編輯按鈕當 `profileLoading = true`
- 取消編輯時重置所有欄位（當前已實作）

---

## 4. 實作範圍 (Scope)

### 4.1 In Scope (本次修復)

- ✅ 修復編輯模式下 Avatar 顯示問題
- ✅ 禁止在資料載入中進入編輯模式
- ✅ 確保取消編輯時正確重置表單
- ✅ 驗證所有欄位在編輯模式下都正確顯示當前值

### 4.2 Out of Scope (本次不做)

- ❌ 修改檢視模式的顯示
- ❌ 新增表單驗證邏輯（已存在）
- ❌ 修改資料儲存邏輯
- ❌ 新增「確認放棄變更」對話框（用戶選擇立即重置）
- ❌ Backend API 修改

---

## 5. 驗收標準 (Acceptance Criteria)

### 5.1 功能驗收

- [ ] **AC-1**: 檢視模式下，Avatar 正確顯示
  - 如果有頭像圖片 → 顯示圖片
  - 如果無頭像但有 full_name → 顯示姓名縮寫
  - 如果都無 → 顯示 'U'

- [ ] **AC-2**: 編輯模式下，Avatar 正確顯示
  - 如果有新上傳圖片 → 顯示預覽
  - 否則如果有原頭像 → 顯示原頭像
  - 否則如果有 full_name → 顯示姓名縮寫
  - 否則 → 顯示 'U'

- [ ] **AC-3**: 資料載入中時
  - 「編輯資料」按鈕應該是禁用狀態 (disabled)
  - 顯示 Loading Skeleton
  - 無法進入編輯模式

- [ ] **AC-4**: 進入編輯模式時
  - 所有欄位都預填當前值
  - Avatar 顯示當前頭像或姓名縮寫
  - 服務地區的選擇狀態正確

- [ ] **AC-5**: 點擊「取消」按鈕時
  - 所有欄位立即重置到原始值
  - Avatar 預覽清除
  - 回到檢視模式

### 5.2 邊界情況驗收

- [ ] **BC-1**: 首次使用者（資料為空）
  - Avatar 顯示 'U'
  - 欄位顯示 placeholder
  - 可以進入編輯模式

- [ ] **BC-2**: 部分資料缺失
  - 有值的欄位正確顯示
  - 無值的欄位顯示空白或 placeholder
  - Avatar fallback 正確運作

- [ ] **BC-3**: 頭像上傳後取消
  - 預覽清除
  - 回到原始頭像或 fallback

---

## 6. 影響範圍 (Impact Analysis)

### 6.1 修改檔案

**主要修改**:
- `frontend/app/(dashboard)/dashboard/page.tsx`
  - Line 197-199: 編輯按鈕加入 `disabled` 屬性
  - Line 272: Avatar fallback 邏輯修正

**修改行數**: 約 3 行

### 6.2 影響組件

- Dashboard 頁面的編輯模式
- Avatar 組件的 fallback 顯示

### 6.3 影響用戶

- **業務員用戶**: 所有業務員用戶
- **影響程度**: Medium (改善 UX，修復顯示問題)

---

## 7. 風險評估 (Risk Assessment)

### 7.1 技術風險

- **風險等級**: Low
- **原因**: 修改範圍小，邏輯簡單
- **緩解措施**: 完整測試檢視和編輯模式

### 7.2 用戶體驗風險

- **風險等級**: Very Low
- **原因**: 修復顯示問題，改善 UX
- **影響**: 正面影響，無負面影響

### 7.3 回滾計畫

如果出現問題，可以：
1. Git revert 回滾修改
2. 修改極小，可快速修復

---

## 8. 成功指標 (Success Metrics)

### 8.1 技術指標

- ✅ 0 TypeErrors
- ✅ 0 Console Errors
- ✅ TypeScript strict mode 通過
- ✅ 所有測試通過

### 8.2 用戶體驗指標

- ✅ 編輯模式下 Avatar 正確顯示率 100%
- ✅ 資料載入完成前無法進入編輯模式
- ✅ 取消編輯時資料正確重置

---

## 9. 實作時程 (Timeline)

**預估時間**: 30 分鐘

- Step 1: 修改 Avatar fallback 邏輯 (5 分鐘)
- Step 2: 加入編輯按鈕 disabled 邏輯 (5 分鐘)
- Step 3: 手動測試各種情境 (10 分鐘)
- Step 4: TypeScript 類型檢查 (5 分鐘)
- Step 5: Git commit 和文檔更新 (5 分鐘)

---

## 10. 相依性 (Dependencies)

### 10.1 前置條件

- ✅ Avatar Fallback TypeError 已修復
- ✅ `getAvatarFallback()` 函數可用
- ✅ Dashboard 頁面基本功能正常

### 10.2 相依組件

- `getAvatarFallback()` - `frontend/lib/utils/avatar.ts`
- `Avatar` - `frontend/components/ui/avatar.tsx`
- `useProfile()` - `frontend/hooks/useSalesperson.ts`

---

## 11. 後續追蹤 (Follow-up)

### 11.1 未來改進

- 考慮添加「確認放棄變更」對話框（如果用戶反饋需要）
- 考慮添加自動儲存草稿功能
- 考慮添加欄位級別的變更追蹤

### 11.2 監控指標

- 監控 Dashboard 頁面的錯誤率
- 監控用戶編輯操作的完成率

---

## 12. 決策記錄 (Decision Log)

| 日期 | 決策 | 原因 |
|------|------|------|
| 2026-01-12 | 選擇方案 A + B | 最小修改 + 根本解決，風險低 |
| 2026-01-12 | Avatar 優先級: 新圖片 → 原頭像 → 姓名縮寫 | 用戶確認，符合預期 |
| 2026-01-12 | Loading 時禁止編輯 | 用戶確認，避免資料不完整 |
| 2026-01-12 | 取消時立即重置 | 用戶確認，簡單直接 |

---

## 13. 總結 (Summary)

**問題**: Dashboard 編輯模式下 Avatar 顯示預設 "U" 而不是姓名縮寫

**根本原因**: Avatar fallback 傳遞空物件 + 允許在 Loading 時進入編輯模式

**解決方案**: 修復 Avatar fallback 邏輯 + 禁用 Loading 時的編輯按鈕

**預期效果**: 編輯模式下正確顯示所有當前設定，提升用戶體驗

**風險**: Low，修改範圍小，邏輯簡單

**時程**: 30 分鐘

---

**建立者**: Requirements Analyst (AI)
**審查者**: Frontend Developer, Product Designer
**核准者**: Tech Lead
**狀態**: ✅ Ready for Implementation

---

**變更日誌**:
- 2026-01-12: 建立 Proposal
- 2026-01-12: 確認用戶需求（Avatar 優先級、Loading 處理、取消行為）
- 2026-01-12: 定義推薦方案（A + B）

---

**下一步**:
- [ ] 產品經理審查 Proposal
- [ ] 技術負責人審查可行性
- [ ] 進入 Step 2: Write Specifications
