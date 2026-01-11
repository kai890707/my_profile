# Proposal: 修復 Header 組件 TypeError

**Status**: Completed
**Created**: 2026-01-11
**Priority**: High
**Type**: Bug Fix

---

## Why (問題陳述)

### 目前的問題

Frontend Header 組件在顯示已登入使用者資訊時拋出 TypeError，導致頁面無法正常渲染。

**錯誤訊息**:
```
TypeError: Cannot read properties of undefined (reading 'substring')
  at Header (components/layout/header.tsx:99:38)
```

**根本原因**:
1. Line 99: `user.email.substring(0, 2)` 缺少 optional chaining (`?.`)
2. TypeScript 介面將 `email` 定義為必填，但實際運行時可能為 undefined
3. 沒有提供最終的 fallback 值

**影響範圍**:
- 影響的用戶: 所有 `user.email` 為 undefined 的已登入用戶
- 影響的頁面: 所有公開頁面 (/, /search) + Dashboard 頁面
- 嚴重性: High - 頁面完全無法渲染

**技術細節**:
```typescript
// ❌ 錯誤代碼 (Line 97-99)
user.name?.substring(0, 2).toUpperCase() ||
user.username?.substring(0, 2).toUpperCase() ||
user.email.substring(0, 2).toUpperCase()  // 缺少 ?.
```

---

## What (解決方案)

### 功能概述

修復 Header 組件中的 optional chaining 缺失，並提供適當的 fallback 值。

### 核心價值

1. **穩定性**: 避免因 undefined 值導致的運行時錯誤
2. **一致性**: 統一使用 optional chaining 處理可選欄位
3. **類型安全**: TypeScript 介面正確反映實際資料結構

### 主要修復

1. **添加 optional chaining**
   - Line 99: `user.email.substring()` → `user.email?.substring()`
   - 與 Line 97-98 保持一致

2. **提供 fallback 值**
   - Avatar initials: 添加 `'U'` 作為最終 fallback
   - 使用者名稱: 添加 `'使用者'` 作為最終 fallback

3. **修正 TypeScript 介面**
   - `email: string` → `email?: string`
   - 正確反映 email 可能為 undefined 的事實

---

## Scope (範圍)

### In Scope (本次修復)

- ✅ 修復 Line 99 的 optional chaining 缺失
- ✅ 添加 Avatar fallback 值 ('U')
- ✅ 添加使用者名稱 fallback 值 ('使用者')
- ✅ 修正 TypeScript 介面 (email 改為選填)
- ✅ 驗證 TypeScript 類型檢查通過
- ✅ 驗證運行時無錯誤

### Out of Scope (未來改進)

- ❌ 建立全域 User 類型定義 (建議但非必要)
- ❌ 建立 getUserDisplayName/getUserInitials helper functions
- ❌ 添加單元測試 (建議但非本次範圍)
- ❌ 設置 ESLint 規則防止類似問題

---

## Success Criteria (驗收標準)

### 功能性驗收

- [x] Header 組件無 TypeError
- [x] Avatar 能正確顯示 fallback initials ('U')
- [x] 使用者名稱能正確顯示 fallback ('使用者')
- [x] 所有頁面能正常渲染

### 非功能性驗收

- [x] TypeScript 類型檢查通過 (`npm run typecheck`)
- [x] 程式碼一致性: 所有 optional 欄位都使用 optional chaining
- [x] 前端開發伺服器編譯成功

### 邊界測試案例

| 測試情境 | user.email | 預期 Avatar | 預期名稱 |
|----------|-----------|------------|---------|
| 有 email | ✓ | email 前 2 字元 | email |
| 無 email, 有 username | ✗ / ✓ | username 前 2 字元 | username |
| 無 email, 無 username, 有 name | ✗ / ✗ / ✓ | name 前 2 字元 | name |
| 全部為 undefined | ✗ / ✗ / ✗ | 'U' | '使用者' |

---

## Dependencies (相依性)

### 必要的前置條件

- Frontend 開發服務器運行中 (http://localhost:3001)
- TypeScript 編譯環境正常

### 技術相依

- Next.js 16.1.1
- TypeScript 5
- React 19

---

## Risks (風險評估)

| 風險 | 影響 | 機率 | 緩解措施 |
|------|------|------|-------------|
| 修改 TypeScript 介面影響其他組件 | Medium | Low | 檢查所有使用 HeaderProps 的地方 |
| Fallback 文字不符合 UI/UX 期望 | Low | Low | 'U' 和 '使用者' 是合理的預設值 |
| 其他組件也有類似問題 | Medium | Medium | 建議全域搜尋類似模式 |

---

## Implementation Notes

### 修復步驟

1. **修復 Avatar Fallback (Line 93-103)**
   ```typescript
   // 添加 optional chaining 和最終 fallback
   user.email?.substring(0, 2).toUpperCase() || 'U'
   ```

2. **修復使用者名稱 (Line 104-111)**
   ```typescript
   // 添加最終 fallback
   {user.full_name || user.name || user.username || user.email || '使用者'}
   ```

3. **修正 TypeScript 介面 (Line 17-28)**
   ```typescript
   // email 改為選填
   email?: string;
   ```

4. **驗證**
   ```bash
   npm run typecheck
   # 訪問 http://localhost:3001 確認無錯誤
   ```

---

## Open Questions (已確認)

- [x] Avatar fallback 使用 'U' 是否合適？ → **是，通用且簡潔**
- [x] 使用者名稱 fallback 使用 '使用者' 是否合適？ → **是，符合繁體中文慣例**
- [x] 是否需要建立全域 User 類型？ → **建議但非必要**
- [x] 是否需要添加單元測試？ → **建議但非本次範圍**

---

**Next Step**: 已完成修復並驗證通過 ✅

**相關文檔**:
- Completion Summary: `COMPLETION-SUMMARY.md`
- Modified File: `frontend/components/layout/header.tsx`
