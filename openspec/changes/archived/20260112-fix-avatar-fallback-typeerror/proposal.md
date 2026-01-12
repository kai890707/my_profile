# Proposal: 修復 Avatar Fallback 的 TypeError Bug

## 1. 背景與目標

### 業務背景

業務員登入後訪問首頁（或任何顯示業務員列表的頁面）時，前端應用出現 JavaScript 錯誤導致頁面無法正常渲染：

```
TypeError: Cannot read properties of undefined (reading 'substring')
at SalespersonCard (components/features/search/salesperson-card.tsx:43:47)
```

**錯誤發生位置**：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={salesperson.full_name.substring(0, 2)}  // ❌ 當 full_name 為 undefined 時崩潰
  size="lg"
/>
```

### 問題根本原因

1. **API 資料不一致**：`SalespersonSearchResult` 介面定義 `full_name: string`，但實際 API 可能返回 `undefined` 或 `null`
2. **缺少防禦性程式設計**：直接對可能為 `undefined` 的值呼叫 `.substring()` 方法
3. **缺少完整的 Fallback 策略**：未考慮當 `full_name` 不可用時的備選方案

### 影響範圍

**受影響的檔案**：
- `/Users/kai/KAA/my_profile/frontend/components/features/search/salesperson-card.tsx` (主要問題)
- `/Users/kai/KAA/my_profile/frontend/components/layout/header.tsx` (已有完善處理，作為參考)
- `/Users/kai/KAA/my_profile/frontend/app/(dashboard)/dashboard/page.tsx` (類似問題)
- `/Users/kai/KAA/my_profile/frontend/app/salesperson/[id]/page.tsx` (類似問題)

**受影響的使用者**：
- 所有訪問搜尋頁面的使用者
- 所有訪問首頁並看到業務員列表的使用者
- 業務員訪問個人資料頁面時

**發生條件**：
- 當業務員資料的 `full_name` 欄位為 `undefined`、`null` 或空字串時
- 當 API 返回不完整的資料時

### 目標使用者

- **一般使用者**：瀏覽業務員列表和詳細資料
- **業務員使用者**：查看自己的個人資料
- **管理員**：審核和管理業務員資料

### 成功指標

1. **零錯誤**：所有使用 Avatar 的地方不再出現 TypeError
2. **優雅降級**：當資料不完整時，顯示合理的預設值
3. **一致性**：所有 Avatar fallback 使用相同的策略
4. **測試覆蓋**：新增測試案例確保邊界情況被處理

---

## 2. 功能描述

### 核心功能

建立統一的 Avatar Fallback 策略，確保在任何情況下都能顯示合理的 fallback 文字。

### Fallback 策略邏輯

優先級順序（依序嘗試）：

```typescript
function getAvatarFallback(user: {
  full_name?: string | null;
  name?: string | null;
  username?: string | null;
  email?: string | null;
}): string {
  // 1. 優先使用 full_name（業務員使用）
  if (user.full_name && user.full_name.trim().length >= 2) {
    return user.full_name.substring(0, 2).toUpperCase();
  }

  // 2. 備選：使用 name（一般使用者）
  if (user.name && user.name.trim().length >= 2) {
    return user.name.substring(0, 2).toUpperCase();
  }

  // 3. 備選：使用 username
  if (user.username && user.username.trim().length >= 2) {
    return user.username.substring(0, 2).toUpperCase();
  }

  // 4. 備選：使用 email 的前兩個字元
  if (user.email && user.email.trim().length >= 2) {
    return user.email.substring(0, 2).toUpperCase();
  }

  // 5. 最終備選：預設值
  return 'U';
}
```

### 使用情境範例

**情境 1：正常情況**
- 資料：`{ full_name: "張三" }`
- 顯示：`"張三"` (前兩個字元)
- 結果：✅ 正常顯示

**情境 2：full_name 為 undefined**
- 資料：`{ full_name: undefined, username: "zhangsan" }`
- 顯示：`"ZH"` (username 前兩個字元大寫)
- 結果：✅ 優雅降級

**情境 3：所有名稱欄位都為空**
- 資料：`{ full_name: null, name: null, username: null, email: "test@example.com" }`
- 顯示：`"TE"` (email 前兩個字元大寫)
- 結果：✅ 使用 email fallback

**情境 4：完全沒有資料**
- 資料：`{ full_name: null, name: null, username: null, email: null }`
- 顯示：`"U"` (預設值)
- 結果：✅ 最終 fallback

**情境 5：名稱只有一個字元**
- 資料：`{ full_name: "李" }`
- 顯示：`"李U"` 或 `"L"` (根據實作決定)
- 結果：✅ 處理邊界情況

---

## 3. 功能範圍

### In Scope（本次實作）

- ✅ **建立工具函數** `getAvatarFallback()`
  - 位置：`/frontend/lib/utils/avatar.ts`
  - 實作完整的 fallback 邏輯
  - 處理所有邊界情況

- ✅ **修復 SalespersonCard 組件**
  - 檔案：`components/features/search/salesperson-card.tsx`
  - 替換 `salesperson.full_name.substring(0, 2)` 為 `getAvatarFallback(salesperson)`

- ✅ **修復 Dashboard 頁面**
  - 檔案：`app/(dashboard)/dashboard/page.tsx`
  - 使用統一的 fallback 函數

- ✅ **修復 Salesperson Detail 頁面**
  - 檔案：`app/salesperson/[id]/page.tsx`
  - 使用統一的 fallback 函數

- ✅ **更新 Header 組件**（已有良好實作，參考標準）
  - 檔案：`components/layout/header.tsx`
  - 確認現有實作符合新標準
  - 可選：重構為使用統一函數

- ✅ **新增單元測試**
  - 測試檔案：`lib/utils/__tests__/avatar.test.ts`
  - 測試所有 fallback 情境
  - 測試邊界情況（null, undefined, 空字串, 單字元）

- ✅ **更新 TypeScript 類型定義**
  - 檔案：`types/api.ts`
  - 確認 `full_name` 可以是 `string | null | undefined`
  - 檢查其他相關介面

### Out of Scope（不在範圍內）

- ❌ **修改 Backend API**：不改變 API 返回的資料結構
  - 原因：這是 Frontend 防禦性程式設計問題，不應依賴 Backend 保證

- ❌ **Avatar 組件重構**：不改變 Avatar 組件的 API
  - 原因：Avatar 組件設計良好，只需要傳入正確的 fallback 值

- ❌ **全域錯誤處理**：不實作全域 Error Boundary
  - 原因：這是特定問題的修復，不是整體錯誤處理架構

- ❌ **圖片上傳優化**：不處理 avatar 圖片上傳相關邏輯
  - 原因：與本次 bug 修復無關

- ❌ **國際化處理**：不實作多語言 fallback
  - 原因：目前系統只支援中文，未來可擴充

---

## 4. 詳細需求

### 4.1 功能需求

#### FR-001: 建立 Avatar Fallback 工具函數

**描述**: 建立一個可複用的工具函數，用於生成 Avatar 的 fallback 文字

**優先級**: Must Have

**驗收標準**:
- [ ] 函數位於 `lib/utils/avatar.ts`
- [ ] 函數接受包含 `full_name`, `name`, `username`, `email` 的物件
- [ ] 函數返回 2 個字元的字串（或單字元 + 'U'）
- [ ] 依照優先級順序處理：full_name → name → username → email → 'U'
- [ ] 所有輸入都經過 trim() 和長度檢查
- [ ] 返回值統一轉換為大寫（中文除外）
- [ ] 通過 10+ 個測試案例

#### FR-002: 修復 SalespersonCard 組件

**描述**: 替換 SalespersonCard 中直接呼叫 `.substring()` 的程式碼

**優先級**: Must Have

**驗收標準**:
- [ ] 移除 `salesperson.full_name.substring(0, 2)`
- [ ] 使用 `getAvatarFallback(salesperson)` 替換
- [ ] 元件在所有資料情境下都能正常渲染
- [ ] 不再出現 TypeError
- [ ] 通過視覺回歸測試

#### FR-003: 修復其他使用 Avatar 的頁面

**描述**: 確保所有使用 Avatar 的地方都使用統一的 fallback 策略

**優先級**: Must Have

**驗收標準**:
- [ ] Dashboard 頁面已修復
- [ ] Salesperson Detail 頁面已修復
- [ ] Header 組件已審查（或重構）
- [ ] 使用 `grep` 確認沒有其他直接呼叫 `.substring()` 的地方
- [ ] 所有頁面通過手動測試

#### FR-004: 新增單元測試

**描述**: 為 `getAvatarFallback()` 函數新增完整的測試覆蓋

**優先級**: Must Have

**驗收標準**:
- [ ] 測試正常情況（有 full_name）
- [ ] 測試 fallback 到 name
- [ ] 測試 fallback 到 username
- [ ] 測試 fallback 到 email
- [ ] 測試最終 fallback（'U'）
- [ ] 測試 null 值
- [ ] 測試 undefined 值
- [ ] 測試空字串
- [ ] 測試單字元字串
- [ ] 測試包含空格的字串
- [ ] 測試覆蓋率 100%

### 4.2 資料需求

#### 輸入資料結構

```typescript
interface UserWithAvatar {
  full_name?: string | null;
  name?: string | null;
  username?: string | null;
  email?: string | null;
}
```

#### 輸出資料格式

```typescript
// 函數簽名
function getAvatarFallback(user: UserWithAvatar): string;

// 返回值
type AvatarFallbackText = string; // 長度 1-2 字元
```

#### 資料驗證規則

| 規則 | 說明 | 處理方式 |
|------|------|----------|
| null 檢查 | 所有欄位可能為 null | 使用 optional chaining `user.full_name?.trim()` |
| undefined 檢查 | 所有欄位可能為 undefined | 使用 `??` 運算子 |
| 空字串檢查 | trim() 後可能為空字串 | 檢查 `.length >= 2` |
| 長度不足 | 某些名稱可能只有 1 個字元 | 補充 'U' 或只使用該字元 |
| 特殊字符 | Email 可能包含特殊字符 | 直接取前 2 個字元 |

### 4.3 非功能需求

#### NFR-001: 效能要求

- 函數執行時間 < 1ms
- 不應造成頁面渲染延遲
- 避免不必要的字串操作

#### NFR-002: 程式碼品質

- TypeScript strict mode 通過
- ESLint 無警告
- 程式碼覆蓋率 100%
- 遵循 DRY 原則（Don't Repeat Yourself）

#### NFR-003: 可維護性

- 函數有完整的 JSDoc 註解
- 每個分支都有註解說明
- 易於擴充（例如：未來新增其他 fallback 來源）

#### NFR-004: 兼容性

- 支援所有主流瀏覽器
- 支援中文、英文字元
- 支援特殊字符（emoji 會被正常處理）

---

## 5. 邊界情境處理

### 異常情況處理

| 情境 | 系統行為 | 預期結果 |
|-----|---------|---------|
| **所有欄位都為 null** | 返回預設值 'U' | ✅ 顯示預設 Avatar |
| **所有欄位都為 undefined** | 返回預設值 'U' | ✅ 顯示預設 Avatar |
| **所有欄位都為空字串** | 返回預設值 'U' | ✅ 顯示預設 Avatar |
| **full_name 只有 1 個字元** | 使用該字元 + 'U' 或直接使用 | ✅ 顯示 "李U" 或 "L" |
| **full_name 包含空格** | 先 trim() 再取前 2 個字元 | ✅ 正確處理 |
| **email 格式異常** | 仍取前 2 個字元 | ✅ 不驗證 email 格式 |
| **傳入空物件 {}** | 返回預設值 'U' | ✅ 不會崩潰 |
| **傳入 null/undefined** | TypeScript 類型錯誤（編譯時） | ✅ 在開發階段就發現 |

### Edge Cases

#### Edge Case 1: 中文姓名只有單字
- **情境**: `{ full_name: "李" }`
- **預期行為**: 返回 `"李"` 或 `"李U"`
- **實作決策**: 中文單字可以獨立顯示，返回 `"李"`

#### Edge Case 2: 英文姓名只有單字母
- **情境**: `{ full_name: "A" }`
- **預期行為**: 返回 `"A"` 或 `"AU"`
- **實作決策**: 返回 `"A"` （單字母也可接受）

#### Edge Case 3: Email 前兩個字元相同
- **情境**: `{ email: "aa@example.com" }`
- **預期行為**: 返回 `"AA"`
- **錯誤處理**: 正常，無需特殊處理

#### Edge Case 4: 包含 Emoji
- **情境**: `{ full_name: "😀😀" }`
- **預期行為**: 返回 `"😀😀"` 或正確截取
- **錯誤處理**: JavaScript `.substring()` 可能切割 emoji，需要測試

#### Edge Case 5: 包含全形空格
- **情境**: `{ full_name: "　張三　" }` （全形空格）
- **預期行為**: trim() 可能無法移除全形空格
- **錯誤處理**: 使用正則表達式 `.replace(/\s+/g, '')` 移除所有空格

#### Edge Case 6: 混合中英文
- **情境**: `{ full_name: "張Sam" }`
- **預期行為**: 返回 `"張S"`
- **錯誤處理**: 正常處理，無需特殊邏輯

---

## 6. 技術考量

### 技術實作細節

#### 6.1 工具函數實作

**檔案位置**: `/frontend/lib/utils/avatar.ts`

```typescript
/**
 * 生成 Avatar 的 fallback 文字
 *
 * 優先級: full_name > name > username > email > 'U'
 *
 * @param user - 包含使用者名稱資訊的物件
 * @returns 2 個字元的 fallback 文字（或預設 'U'）
 *
 * @example
 * getAvatarFallback({ full_name: '張三' }) // '張三'
 * getAvatarFallback({ full_name: null, username: 'john' }) // 'JO'
 * getAvatarFallback({}) // 'U'
 */
export function getAvatarFallback(user: {
  full_name?: string | null;
  name?: string | null;
  username?: string | null;
  email?: string | null;
}): string {
  // 嘗試從 full_name 取得
  const fullName = user.full_name?.trim();
  if (fullName && fullName.length >= 2) {
    return fullName.substring(0, 2).toUpperCase();
  }
  if (fullName && fullName.length === 1) {
    return fullName.toUpperCase();
  }

  // 嘗試從 name 取得
  const name = user.name?.trim();
  if (name && name.length >= 2) {
    return name.substring(0, 2).toUpperCase();
  }
  if (name && name.length === 1) {
    return name.toUpperCase();
  }

  // 嘗試從 username 取得
  const username = user.username?.trim();
  if (username && username.length >= 2) {
    return username.substring(0, 2).toUpperCase();
  }
  if (username && username.length === 1) {
    return username.toUpperCase();
  }

  // 嘗試從 email 取得
  const email = user.email?.trim();
  if (email && email.length >= 2) {
    return email.substring(0, 2).toUpperCase();
  }
  if (email && email.length === 1) {
    return email.toUpperCase();
  }

  // 最終 fallback
  return 'U';
}
```

#### 6.2 修復 SalespersonCard

**修改前**:
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={salesperson.full_name.substring(0, 2)}  // ❌ 危險
  size="lg"
/>
```

**修改後**:
```tsx
import { getAvatarFallback } from '@/lib/utils/avatar';

<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}  // ✅ 安全
  size="lg"
/>
```

#### 6.3 測試實作

**檔案位置**: `/frontend/lib/utils/__tests__/avatar.test.ts`

```typescript
import { describe, it, expect } from 'vitest';
import { getAvatarFallback } from '../avatar';

describe('getAvatarFallback', () => {
  describe('正常情況', () => {
    it('應該返回 full_name 的前兩個字元', () => {
      expect(getAvatarFallback({ full_name: '張三' })).toBe('張三');
      expect(getAvatarFallback({ full_name: 'John Doe' })).toBe('JO');
    });

    it('應該處理單字元 full_name', () => {
      expect(getAvatarFallback({ full_name: '李' })).toBe('李');
      expect(getAvatarFallback({ full_name: 'A' })).toBe('A');
    });
  });

  describe('Fallback 到 name', () => {
    it('當 full_name 為 null 時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: 'John'
      })).toBe('JO');
    });
  });

  describe('Fallback 到 username', () => {
    it('當 full_name 和 name 都為 null 時，應該使用 username', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: 'john123'
      })).toBe('JO');
    });
  });

  describe('Fallback 到 email', () => {
    it('當所有名稱欄位都為 null 時，應該使用 email', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 'test@example.com'
      })).toBe('TE');
    });
  });

  describe('最終 fallback', () => {
    it('當所有欄位都為 null 時，應該返回 "U"', () => {
      expect(getAvatarFallback({})).toBe('U');
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: null
      })).toBe('U');
    });
  });

  describe('邊界情況', () => {
    it('應該處理空字串', () => {
      expect(getAvatarFallback({ full_name: '' })).toBe('U');
      expect(getAvatarFallback({ full_name: '   ' })).toBe('U');
    });

    it('應該處理 undefined', () => {
      expect(getAvatarFallback({ full_name: undefined })).toBe('U');
    });

    it('應該 trim 空格', () => {
      expect(getAvatarFallback({ full_name: '  John  ' })).toBe('JO');
    });
  });
});
```

### 效能考量

- **函數執行時間**: < 1ms（字串操作非常快）
- **記憶體佔用**: 可忽略不計（只處理短字串）
- **渲染影響**: 無（在渲染前計算完成）

### 安全性考量

- **XSS 風險**: 低（只是顯示 2 個字元，且 React 會自動 escape）
- **注入攻擊**: 不適用（純前端處理）
- **資料驗證**: 不驗證 email 格式（避免額外複雜性）

### 相容性考量

- **瀏覽器支援**: 所有現代瀏覽器（使用標準 JavaScript 方法）
- **TypeScript 版本**: 5.0+
- **React 版本**: 19.x

---

## 7. 驗收標準

### 功能驗收

- [ ] **工具函數已建立**
  - 位於 `lib/utils/avatar.ts`
  - 通過所有單元測試
  - 有完整的 JSDoc 註解
  - TypeScript 類型正確

- [ ] **SalespersonCard 已修復**
  - 不再直接呼叫 `.substring()`
  - 使用 `getAvatarFallback()` 函數
  - 通過手動測試（多種資料情境）

- [ ] **Dashboard 頁面已修復**
  - Avatar 顯示正常
  - 無 TypeError 錯誤

- [ ] **Salesperson Detail 頁面已修復**
  - Avatar 顯示正常
  - 無 TypeError 錯誤

- [ ] **Header 組件已審查**
  - 確認現有實作符合標準
  - 可選：重構為使用統一函數

- [ ] **測試已完成**
  - 單元測試覆蓋率 100%
  - 通過所有測試案例
  - 包含邊界情況測試

### 非功能驗收

- [ ] **效能**：函數執行時間 < 1ms
- [ ] **TypeScript**：無類型錯誤，strict mode 通過
- [ ] **ESLint**：無 lint 警告或錯誤
- [ ] **程式碼審查**：通過 peer review
- [ ] **文檔**：函數有 JSDoc 註解，使用範例清晰

### 測試驗收

#### 單元測試檢查清單

- [ ] 測試正常情況（有 full_name）
- [ ] 測試 fallback 到 name
- [ ] 測試 fallback 到 username
- [ ] 測試 fallback 到 email
- [ ] 測試最終 fallback（'U'）
- [ ] 測試 null 值處理
- [ ] 測試 undefined 值處理
- [ ] 測試空字串處理
- [ ] 測試單字元字串
- [ ] 測試包含空格的字串
- [ ] 測試中文字元
- [ ] 測試英文字元
- [ ] 測試混合字元
- [ ] 測試覆蓋率達到 100%

#### 手動測試檢查清單

- [ ] **搜尋頁面**
  - 訪問 `/search`
  - 確認業務員卡片正常顯示
  - 確認 Avatar fallback 正確

- [ ] **首頁**
  - 訪問 `/`
  - 確認業務員列表正常顯示
  - 確認 Avatar fallback 正確

- [ ] **業務員詳細頁面**
  - 訪問 `/salesperson/[id]`
  - 確認 Avatar 正常顯示
  - 確認大頭貼 fallback 正確

- [ ] **Dashboard 頁面**
  - 登入為業務員
  - 訪問 `/dashboard`
  - 確認個人資料 Avatar 正常

- [ ] **Header 使用者選單**
  - 登入為業務員
  - 確認 Header 中的 Avatar 正常
  - 確認下拉選單中的名稱正確

#### 瀏覽器測試

- [ ] Chrome (最新版)
- [ ] Firefox (最新版)
- [ ] Safari (最新版)
- [ ] Edge (最新版)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## 8. 風險與依賴

### 潛在風險

#### 風險 1: 中文字元處理問題

**描述**: JavaScript 的 `.substring()` 對某些 Unicode 字元（如 emoji）可能會切割錯誤

**機率**: 低
**影響**: 中

**緩解措施**:
- 測試 emoji 和特殊字元
- 如果發現問題，考慮使用 `Array.from(str).slice(0, 2).join('')` 替代
- 新增針對特殊字元的測試案例

#### 風險 2: 效能影響

**描述**: 如果列表中有大量業務員，頻繁呼叫函數可能影響效能

**機率**: 低
**影響**: 低

**緩解措施**:
- 函數非常簡單，執行時間 < 1ms
- React 會自動優化渲染
- 如果真有效能問題，可以考慮 memoization

#### 風險 3: 類型定義不匹配

**描述**: 如果 API 實際返回的資料結構與 TypeScript 類型定義不符

**機率**: 中
**影響**: 高

**緩解措施**:
- 更新 TypeScript 介面，明確標註 `full_name?: string | null`
- 在 API 層面新增 runtime 驗證（可選）
- 使用 Zod schema 驗證 API 回應（未來改進）

#### 風險 4: 現有程式碼遺漏

**描述**: 可能還有其他地方直接呼叫 `.substring()` 但未被發現

**機率**: 中
**影響**: 中

**緩解措施**:
- 使用 `grep` 搜尋所有 `.substring()` 呼叫
- Code review 時仔細檢查
- 新增 ESLint 規則禁止直接對可能為 null 的值呼叫方法（可選）

### 依賴項目

- **React 19**: 需要確保 Avatar 組件與 React 19 相容
- **TypeScript 5**: 使用最新的 TypeScript 特性
- **Vitest**: 用於單元測試
- **現有 Avatar 組件**: 不需要修改，只需要正確傳入 fallback

---

## 9. 實作計畫

### Phase 1: 建立工具函數與測試（30 分鐘）

**任務**:
1. 建立 `/frontend/lib/utils/avatar.ts`
2. 實作 `getAvatarFallback()` 函數
3. 建立 `/frontend/lib/utils/__tests__/avatar.test.ts`
4. 撰寫 14+ 個測試案例
5. 確保測試覆蓋率 100%

**產出**:
- ✅ 工具函數已實作
- ✅ 單元測試已完成
- ✅ 測試全部通過

### Phase 2: 修復所有使用 Avatar 的地方（45 分鐘）

**任務**:
1. 修復 `components/features/search/salesperson-card.tsx`
2. 修復 `app/(dashboard)/dashboard/page.tsx`
3. 修復 `app/salesperson/[id]/page.tsx`
4. 審查並可選重構 `components/layout/header.tsx`
5. 使用 `grep` 確認沒有遺漏

**產出**:
- ✅ 所有檔案已修復
- ✅ 無 TypeError 錯誤
- ✅ 程式碼一致性良好

### Phase 3: 測試與驗證（30 分鐘）

**任務**:
1. 執行單元測試
2. 手動測試所有受影響頁面
3. 在多種資料情境下測試
4. 跨瀏覽器測試
5. 程式碼審查

**產出**:
- ✅ 所有測試通過
- ✅ 手動測試確認無問題
- ✅ 程式碼品質良好

### Phase 4: 文檔與歸檔（15 分鐘）

**任務**:
1. 更新 JSDoc 註解
2. 更新 TypeScript 類型定義（如需要）
3. 提交 commit
4. 歸檔規格到 `openspec/specs/frontend/`

**產出**:
- ✅ 文檔完整
- ✅ 程式碼已提交
- ✅ 規格已歸檔

**總時間估計**: 約 2 小時

---

## 10. 附錄

### 參考資料

1. **現有程式碼**
   - `components/layout/header.tsx` (第 95-101 行) - 良好的 fallback 範例
   - `components/ui/avatar.tsx` - Avatar 組件實作

2. **TypeScript 文檔**
   - [Optional Chaining](https://www.typescriptlang.org/docs/handbook/release-notes/typescript-3-7.html#optional-chaining)
   - [Nullish Coalescing](https://www.typescriptlang.org/docs/handbook/release-notes/typescript-3-7.html#nullish-coalescing)

3. **相關 Issue**
   - TypeError: Cannot read properties of undefined (reading 'substring')

### 未來規劃

#### Phase 2.0: 增強功能（未來改進）

1. **多語言支援**
   - 支援不同語言的 fallback 策略
   - 例如：中文取姓氏，英文取首字母

2. **自訂 Fallback**
   - 允許組件傳入自訂的 fallback 文字
   - 例如：管理員顯示 'A'，業務員顯示 'S'

3. **圖示 Fallback**
   - 當沒有名稱時，顯示預設圖示而非文字
   - 使用 Lucide React 的 User 圖示

4. **色彩策略**
   - 根據名稱生成一致的背景顏色
   - 類似 GitHub 的 Avatar 色彩策略

5. **Runtime 驗證**
   - 使用 Zod 驗證 API 回應
   - 在開發環境中顯示警告

### 相關檔案列表

**需要修改的檔案**:
- `/frontend/lib/utils/avatar.ts` (新建)
- `/frontend/lib/utils/__tests__/avatar.test.ts` (新建)
- `/frontend/components/features/search/salesperson-card.tsx` (修改)
- `/frontend/app/(dashboard)/dashboard/page.tsx` (修改)
- `/frontend/app/salesperson/[id]/page.tsx` (修改)
- `/frontend/components/layout/header.tsx` (可選修改)

**需要審查的檔案**:
- `/frontend/types/api.ts` (檢查類型定義)
- `/frontend/components/ui/avatar.tsx` (確認組件 API)

**參考文件**:
- `/frontend/CLAUDE.md` - Frontend 開發規範
- `openspec/specs/frontend/ui-components.md` - UI 組件規格

---

**建立日期**: 2026-01-12
**建立者**: Product Manager (AI)
**優先級**: High (影響使用者體驗的 bug)
**預計完成時間**: 2 小時
**狀態**: Pending Approval
