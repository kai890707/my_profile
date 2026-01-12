# 實作規格：Avatar Fallback 修復

**功能**: 修復 Avatar Fallback 的 TypeError Bug
**版本**: 1.0
**建立日期**: 2026-01-12
**作者**: React Specialist (AI)

---

## 📋 目錄

1. [實作總覽](#1-實作總覽)
2. [檔案修改清單](#2-檔案修改清單)
3. [詳細實作步驟](#3-詳細實作步驟)
4. [測試實作](#4-測試實作)
5. [驗證步驟](#5-驗證步驟)
6. [回滾計畫](#6-回滾計畫)

---

## 1. 實作總覽

### 1.1 實作策略

```
Phase 1: 建立工具函數（30 分鐘）
├─ 建立 lib/utils/avatar.ts
├─ 實作 getAvatarFallback()
├─ 建立測試檔案
└─ 撰寫 14+ 個測試案例

Phase 2: 修復組件（45 分鐘）
├─ 修復 SalespersonCard
├─ 修復 Dashboard 頁面
├─ 修復 Salesperson Detail 頁面
└─ 重構 Header 組件

Phase 3: 測試驗證（30 分鐘）
├─ 執行單元測試
├─ 手動測試所有頁面
├─ 跨瀏覽器測試
└─ 響應式測試

Phase 4: 程式碼審查與清理（15 分鐘）
├─ ESLint 檢查
├─ TypeScript 檢查
├─ 程式碼格式化
└─ 提交 commit
```

### 1.2 實作原則

**遵循原則**：
1. **最小改動**：只修改必要的部分
2. **向後相容**：不破壞現有功能
3. **測試驅動**：先寫測試，確保正確性
4. **漸進式**：一次修改一個檔案，逐步驗證

---

## 2. 檔案修改清單

### 2.1 新建檔案

| 檔案路徑 | 類型 | 行數 | 說明 |
|---------|------|------|------|
| `/frontend/lib/utils/avatar.ts` | 新建 | ~60 | 核心工具函數 |
| `/frontend/lib/utils/__tests__/avatar.test.ts` | 新建 | ~180 | 單元測試 |

### 2.2 修改檔案

| 檔案路徑 | 修改類型 | 受影響行號 | 變更內容 |
|---------|---------|-----------|---------|
| `/frontend/components/features/search/salesperson-card.tsx` | 修改 | 1-7, 41-45, 49 | Import + Avatar fallback |
| `/frontend/components/layout/header.tsx` | 重構 | 1-8, 93-103 | Import + Avatar fallback |
| `/frontend/app/(dashboard)/dashboard/page.tsx` | 待檢查 | TBD | Avatar 使用 |
| `/frontend/app/salesperson/[id]/page.tsx` | 待檢查 | TBD | Avatar 使用 |
| `/frontend/types/api.ts` | 可選 | TBD | 類型定義 |

### 2.3 不修改檔案

| 檔案路徑 | 原因 |
|---------|------|
| `/frontend/components/ui/avatar.tsx` | 組件設計良好，無需修改 |

---

## 3. 詳細實作步驟

### 3.1 建立工具函數

#### 檔案：`/frontend/lib/utils/avatar.ts`

**完整程式碼**：

```typescript
/**
 * Avatar Fallback 工具函數
 *
 * @module lib/utils/avatar
 * @description 提供統一的 Avatar fallback 策略，確保在任何情況下都能顯示合理的 fallback 文字
 */

/**
 * Avatar Fallback 使用者資料介面
 *
 * 所有欄位都是可選的，函數會依照優先級順序嘗試取得資料：
 * 1. full_name（業務員的完整姓名）
 * 2. name（一般使用者的名字）
 * 3. username（使用者名稱）
 * 4. email（電子郵件）
 */
export interface AvatarFallbackUser {
  /** 完整姓名（優先級 1）- 業務員使用 */
  full_name?: string | null;

  /** 使用者名字（優先級 2）- 一般使用者使用 */
  name?: string | null;

  /** 使用者名稱（優先級 3）- 技術性較強 */
  username?: string | null;

  /** 電子郵件（優先級 4）- 最後手段 */
  email?: string | null;
}

/**
 * 處理單個欄位的 fallback 邏輯
 *
 * @internal
 * @param value - 要處理的字串值
 * @returns 處理後的 fallback 文字，如果無效則返回 null
 */
function processFallbackField(value: string | null | undefined): string | null {
  // 檢查 null 或 undefined
  if (!value) return null;

  // 移除前後空格
  const trimmed = value.trim();

  // 檢查是否為空字串
  if (trimmed.length === 0) return null;

  // 單字元：直接返回（轉大寫）
  if (trimmed.length === 1) {
    return trimmed.toUpperCase();
  }

  // 多字元：取前兩個字元並轉大寫
  return trimmed.substring(0, 2).toUpperCase();
}

/**
 * 生成 Avatar 的 fallback 文字
 *
 * 此函數提供統一的 Avatar fallback 策略，依照優先級順序嘗試從多個欄位取得資料。
 * 當某個欄位無效（null、undefined、空字串）時，自動 fallback 到下一個欄位。
 *
 * **優先級順序**：
 * 1. full_name（業務員的完整姓名）
 * 2. name（一般使用者的名字）
 * 3. username（使用者名稱）
 * 4. email（電子郵件）
 * 5. 'U'（最終預設值）
 *
 * @param user - 包含使用者名稱資訊的物件
 * @returns 1-2 個字元的 fallback 文字（或預設 'U'）
 *
 * @example
 * // 中文名字（保持原樣）
 * getAvatarFallback({ full_name: '張三' })
 * // => '張三'
 *
 * @example
 * // 英文名字（轉換為大寫）
 * getAvatarFallback({ full_name: 'John Doe' })
 * // => 'JO'
 *
 * @example
 * // 單字元名字
 * getAvatarFallback({ full_name: '李' })
 * // => '李'
 *
 * @example
 * // Fallback 到 username
 * getAvatarFallback({ full_name: null, username: 'john123' })
 * // => 'JO'
 *
 * @example
 * // Fallback 到 email
 * getAvatarFallback({
 *   full_name: null,
 *   name: null,
 *   username: null,
 *   email: 'test@example.com'
 * })
 * // => 'TE'
 *
 * @example
 * // 最終 fallback
 * getAvatarFallback({})
 * // => 'U'
 *
 * @remarks
 * - 所有輸入都會經過 trim() 處理，移除前後空格
 * - 英文字母會轉換為大寫（`.toUpperCase()`）
 * - 中文字元保持原樣（`.toUpperCase()` 對中文無效）
 * - 單字元字串直接返回，不補充其他字元
 * - 函數永遠返回有效字串，不會返回 null 或 undefined
 * - 時間複雜度：O(1)，執行時間 < 1ms
 *
 * @see {@link AvatarFallbackUser} 輸入資料介面
 */
export function getAvatarFallback(user: AvatarFallbackUser): string {
  // 優先級 1: 嘗試從 full_name 取得
  const fullNameFallback = processFallbackField(user.full_name);
  if (fullNameFallback) return fullNameFallback;

  // 優先級 2: 嘗試從 name 取得
  const nameFallback = processFallbackField(user.name);
  if (nameFallback) return nameFallback;

  // 優先級 3: 嘗試從 username 取得
  const usernameFallback = processFallbackField(user.username);
  if (usernameFallback) return usernameFallback;

  // 優先級 4: 嘗試從 email 取得
  const emailFallback = processFallbackField(user.email);
  if (emailFallback) return emailFallback;

  // 優先級 5: 最終 fallback
  return 'U';
}
```

**程式碼說明**：

1. **模組化設計**：將 `processFallbackField` 抽取為獨立函數，提高可測試性
2. **完整的 JSDoc**：包含類型、說明、範例、注意事項
3. **純函數**：無副作用，相同輸入永遠產生相同輸出
4. **防禦性程式設計**：處理所有 null/undefined/空字串情況
5. **效能優化**：使用 early return，避免不必要的計算

---

### 3.2 修復 SalespersonCard 組件

#### 檔案：`/frontend/components/features/search/salesperson-card.tsx`

#### 修改 1：Import 區塊

**修改前**（第 1-6 行）：
```typescript
import Link from 'next/link';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { MapPin, Briefcase } from 'lucide-react';
import { SalespersonSearchResult } from '@/types/api';
```

**修改後**（第 1-7 行）：
```typescript
import Link from 'next/link';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { MapPin, Briefcase } from 'lucide-react';
import { SalespersonSearchResult } from '@/types/api';
import { getAvatarFallback } from '@/lib/utils/avatar';  // ← 新增
```

**變更說明**：新增工具函數的 import

---

#### 修改 2：Avatar 使用

**修改前**（第 41-45 行）：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={salesperson.full_name.substring(0, 2)}  // ❌ TypeError 風險
  size="lg"
/>
```

**修改後**（第 42-46 行）：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}  // ✅ 安全
  size="lg"
  alt={`${salesperson.full_name || '業務員'} 的頭像`}
/>
```

**變更說明**：
1. 使用 `getAvatarFallback()` 取代直接呼叫 `.substring()`
2. 新增 `alt` 屬性，改善無障礙性

---

#### 修改 3：標題顯示（可選改善）

**修改前**（第 47-49 行）：
```tsx
<h3 className="text-lg font-semibold text-slate-900 truncate">
  {salesperson.full_name}
</h3>
```

**修改後**（第 48-50 行）：
```tsx
<h3 className="text-lg font-semibold text-slate-900 truncate">
  {salesperson.full_name || salesperson.username || '未命名業務員'}
</h3>
```

**變更說明**：當 `full_name` 為空時，顯示 fallback 文字

---

#### 完整修改後的組件

**檔案內容**（關鍵部分）：
```tsx
import Link from 'next/link';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { MapPin, Briefcase } from 'lucide-react';
import { SalespersonSearchResult } from '@/types/api';
import { getAvatarFallback } from '@/lib/utils/avatar';

interface SalespersonCardProps {
  salesperson: SalespersonSearchResult;
}

export function SalespersonCard({ salesperson }: SalespersonCardProps) {
  const specialtiesList = salesperson.specialties
    ? salesperson.specialties.split(',').slice(0, 3)
    : [];

  // ... service_regions 邏輯（不變）

  return (
    <Link href={`/salesperson/${salesperson.id}`}>
      <Card hover className="h-full transition-all duration-200 hover:shadow-xl">
        <CardContent className="p-6">
          {/* 頭像與基本資訊 */}
          <div className="flex items-start gap-4 mb-4">
            <Avatar
              src={salesperson.avatar}
              fallback={getAvatarFallback(salesperson)}
              size="lg"
              alt={`${salesperson.full_name || '業務員'} 的頭像`}
            />
            <div className="flex-1 min-w-0">
              <h3 className="text-lg font-semibold text-slate-900 truncate">
                {salesperson.full_name || salesperson.username || '未命名業務員'}
              </h3>
              {/* ... 其他內容（不變）*/}
            </div>
          </div>
          {/* ... 其他區塊（不變）*/}
        </CardContent>
      </Card>
    </Link>
  );
}
```

**Git Diff 格式**：
```diff
 import Link from 'next/link';
 import { Avatar } from '@/components/ui/avatar';
 import { Badge } from '@/components/ui/badge';
 import { Card, CardContent } from '@/components/ui/card';
 import { MapPin, Briefcase } from 'lucide-react';
 import { SalespersonSearchResult } from '@/types/api';
+import { getAvatarFallback } from '@/lib/utils/avatar';

 interface SalespersonCardProps {
   salesperson: SalespersonSearchResult;
 }

 export function SalespersonCard({ salesperson }: SalespersonCardProps) {
   // ... 省略不變的程式碼

           <Avatar
             src={salesperson.avatar}
-            fallback={salesperson.full_name.substring(0, 2)}
+            fallback={getAvatarFallback(salesperson)}
             size="lg"
+            alt={`${salesperson.full_name || '業務員'} 的頭像`}
           />
           <div className="flex-1 min-w-0">
             <h3 className="text-lg font-semibold text-slate-900 truncate">
-              {salesperson.full_name}
+              {salesperson.full_name || salesperson.username || '未命名業務員'}
             </h3>
```

---

### 3.3 重構 Header 組件

#### 檔案：`/frontend/components/layout/header.tsx`

#### 修改 1：Import 區塊

**修改前**（第 1-15 行）：
```typescript
'use client';

import Link from 'next/link';
import { useState } from 'react';
import { Menu, X, User, LogOut, Settings, LayoutDashboard } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Avatar } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
```

**修改後**（第 1-16 行）：
```typescript
'use client';

import Link from 'next/link';
import { useState } from 'react';
import { Menu, X, User, LogOut, Settings, LayoutDashboard } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Avatar } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getAvatarFallback } from '@/lib/utils/avatar';  // ← 新增
```

---

#### 修改 2：Avatar 使用

**修改前**（第 93-103 行）：
```tsx
<Avatar
  src={user.avatar}
  fallback={
    user.full_name?.substring(0, 2) ||
    user.name?.substring(0, 2).toUpperCase() ||
    user.username?.substring(0, 2).toUpperCase() ||
    user.email?.substring(0, 2).toUpperCase() ||
    'U'
  }
  size="sm"
/>
```

**修改後**（第 94-98 行）：
```tsx
<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  size="sm"
  alt={`${user.full_name || user.username || '使用者'} 的頭像`}
/>
```

**Git Diff**：
```diff
 'use client';

 import Link from 'next/link';
 import { useState } from 'react';
 import { Menu, X, User, LogOut, Settings, LayoutDashboard } from 'lucide-react';
 import { Button } from '@/components/ui/button';
 import { Avatar } from '@/components/ui/avatar';
 import {
   DropdownMenu,
   DropdownMenuContent,
   DropdownMenuItem,
   DropdownMenuLabel,
   DropdownMenuSeparator,
   DropdownMenuTrigger,
 } from '@/components/ui/dropdown-menu';
+import { getAvatarFallback } from '@/lib/utils/avatar';

 // ... 省略不變的程式碼

                     <Avatar
                       src={user.avatar}
-                      fallback={
-                        user.full_name?.substring(0, 2) ||
-                        user.name?.substring(0, 2).toUpperCase() ||
-                        user.username?.substring(0, 2).toUpperCase() ||
-                        user.email?.substring(0, 2).toUpperCase() ||
-                        'U'
-                      }
+                      fallback={getAvatarFallback(user)}
                       size="sm"
+                      alt={`${user.full_name || user.username || '使用者'} 的頭像`}
                     />
```

---

### 3.4 檢查並修復其他頁面

#### 步驟 1：搜尋所有 Avatar 使用

```bash
# 在 frontend 目錄執行
grep -r "fallback.*substring" app/ components/ --include="*.tsx" --include="*.ts"
```

**預期結果**：
- 應該不會找到其他使用 `.substring()` 的地方（除了已修復的）
- 如果找到，按照相同模式修復

#### 步驟 2：檢查 Dashboard 頁面

**檔案**: `/frontend/app/(dashboard)/dashboard/page.tsx`

**檢查要點**：
1. 搜尋 `<Avatar` 使用
2. 檢查 fallback 屬性
3. 如果使用 `.substring()`，替換為 `getAvatarFallback()`

**修改模板**：
```tsx
// 修改前
<Avatar
  src={user.avatar}
  fallback={user.full_name?.substring(0, 2)}
  size="xl"
/>

// 修改後
import { getAvatarFallback } from '@/lib/utils/avatar';

<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  size="xl"
  alt={`${user.full_name || '使用者'} 的頭像`}
/>
```

#### 步驟 3：檢查 Salesperson Detail 頁面

**檔案**: `/frontend/app/salesperson/[id]/page.tsx`

**同樣的修改模式**（如果需要）

---

### 3.5 TypeScript 類型檢查（可選）

#### 檔案：`/frontend/types/api.ts`

**檢查 SalespersonSearchResult 介面**：

**如果 full_name 定義為必填**：
```typescript
// 修改前
export interface SalespersonSearchResult {
  id: number;
  full_name: string;  // ❌ 可能與實際 API 不符
  avatar?: string | null;
  // ...
}

// 修改後
export interface SalespersonSearchResult {
  id: number;
  full_name?: string | null;  // ✅ 允許 undefined 和 null
  avatar?: string | null;
  // ...
}
```

**檢查 User 介面**：
```typescript
export interface User {
  id: number;
  username?: string;
  name?: string;
  full_name?: string | null;  // ← 確保是可選的
  email?: string;
  avatar?: string | null;
  role: 'admin' | 'salesperson' | 'user';
}
```

---

## 4. 測試實作

### 4.1 單元測試檔案

#### 檔案：`/frontend/lib/utils/__tests__/avatar.test.ts`

**完整測試程式碼**：

```typescript
import { describe, it, expect } from 'vitest';
import { getAvatarFallback } from '../avatar';

describe('getAvatarFallback', () => {
  /**
   * 測試分組 1：正常情況
   */
  describe('正常情況 - full_name', () => {
    it('應該返回中文 full_name 的前兩個字元', () => {
      expect(getAvatarFallback({ full_name: '張三' })).toBe('張三');
      expect(getAvatarFallback({ full_name: '李四' })).toBe('李四');
      expect(getAvatarFallback({ full_name: '王小明' })).toBe('王小');
    });

    it('應該返回英文 full_name 的前兩個字元（大寫）', () => {
      expect(getAvatarFallback({ full_name: 'John Doe' })).toBe('JO');
      expect(getAvatarFallback({ full_name: 'jane smith' })).toBe('JA');
      expect(getAvatarFallback({ full_name: 'ALICE' })).toBe('AL');
    });

    it('應該處理單字元 full_name', () => {
      expect(getAvatarFallback({ full_name: '李' })).toBe('李');
      expect(getAvatarFallback({ full_name: 'A' })).toBe('A');
      expect(getAvatarFallback({ full_name: 'z' })).toBe('Z');
    });

    it('應該將英文字母轉換為大寫', () => {
      expect(getAvatarFallback({ full_name: 'john' })).toBe('JO');
      expect(getAvatarFallback({ full_name: 'abc' })).toBe('AB');
    });

    it('應該處理中英文混合', () => {
      expect(getAvatarFallback({ full_name: '張Sam' })).toBe('張S');
      expect(getAvatarFallback({ full_name: 'John王' })).toBe('JO');
    });
  });

  /**
   * 測試分組 2：Fallback 到 name
   */
  describe('Fallback 到 name', () => {
    it('當 full_name 為 null 時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: 'John'
      })).toBe('JO');
    });

    it('當 full_name 為 undefined 時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: undefined,
        name: 'Jane'
      })).toBe('JA');
    });

    it('當 full_name 為空字串時，應該使用 name', () => {
      expect(getAvatarFallback({
        full_name: '',
        name: 'Alice'
      })).toBe('AL');
    });
  });

  /**
   * 測試分組 3：Fallback 到 username
   */
  describe('Fallback 到 username', () => {
    it('當 full_name 和 name 都為 null 時，應該使用 username', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: 'john123'
      })).toBe('JO');
    });

    it('當 full_name 和 name 都為 undefined 時，應該使用 username', () => {
      expect(getAvatarFallback({
        full_name: undefined,
        name: undefined,
        username: 'alice456'
      })).toBe('AL');
    });
  });

  /**
   * 測試分組 4：Fallback 到 email
   */
  describe('Fallback 到 email', () => {
    it('當所有名稱欄位都為 null 時，應該使用 email', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 'test@example.com'
      })).toBe('TE');
    });

    it('應該處理不同的 email 格式', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 'alice@company.com'
      })).toBe('AL');

      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: 'support@help.org'
      })).toBe('SU');
    });
  });

  /**
   * 測試分組 5：最終 fallback
   */
  describe('最終 fallback', () => {
    it('當所有欄位都為 null 時，應該返回 "U"', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: null,
        email: null
      })).toBe('U');
    });

    it('當傳入空物件時，應該返回 "U"', () => {
      expect(getAvatarFallback({})).toBe('U');
    });

    it('當所有欄位都為 undefined 時，應該返回 "U"', () => {
      expect(getAvatarFallback({
        full_name: undefined,
        name: undefined,
        username: undefined,
        email: undefined
      })).toBe('U');
    });
  });

  /**
   * 測試分組 6：邊界情況 - 空格處理
   */
  describe('邊界情況 - 空格處理', () => {
    it('應該處理純空格字串', () => {
      expect(getAvatarFallback({ full_name: '   ' })).toBe('U');
      expect(getAvatarFallback({ full_name: '\t\n' })).toBe('U');
    });

    it('應該 trim 前後空格', () => {
      expect(getAvatarFallback({ full_name: '  John  ' })).toBe('JO');
      expect(getAvatarFallback({ full_name: '\t張三\n' })).toBe('張三');
    });

    it('應該保留中間空格', () => {
      expect(getAvatarFallback({ full_name: 'John Doe' })).toBe('JO');
      expect(getAvatarFallback({ full_name: 'A B' })).toBe('A ');
    });
  });

  /**
   * 測試分組 7：邊界情況 - 特殊字符
   */
  describe('邊界情況 - 特殊字符', () => {
    it('應該處理數字', () => {
      expect(getAvatarFallback({ full_name: '123' })).toBe('12');
      expect(getAvatarFallback({ username: '007' })).toBe('00');
    });

    it('應該處理特殊符號', () => {
      expect(getAvatarFallback({ username: '@john' })).toBe('@J');
      expect(getAvatarFallback({ username: '_alice_' })).toBe('_A');
    });

    // 注意：Emoji 可能會被 substring 切割，這是已知的限制
    it('應該處理 Emoji（可能不完整）', () => {
      const result = getAvatarFallback({ full_name: '😀😀' });
      expect(result).toBeTruthy();  // 只要有返回值即可
      expect(result.length).toBeGreaterThan(0);
    });
  });

  /**
   * 測試分組 8：優先級順序驗證
   */
  describe('優先級順序', () => {
    it('應該優先使用 full_name 而非 name', () => {
      expect(getAvatarFallback({
        full_name: 'John',
        name: 'Jane'
      })).toBe('JO');  // 使用 John，不是 Jane
    });

    it('應該優先使用 name 而非 username', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: 'Alice',
        username: 'bob'
      })).toBe('AL');  // 使用 Alice，不是 bob
    });

    it('應該優先使用 username 而非 email', () => {
      expect(getAvatarFallback({
        full_name: null,
        name: null,
        username: 'charlie',
        email: 'test@example.com'
      })).toBe('CH');  // 使用 charlie，不是 test
    });
  });

  /**
   * 測試分組 9：性能測試（選用）
   */
  describe('性能測試', () => {
    it('應該在合理時間內完成（< 10ms for 1000 次）', () => {
      const startTime = performance.now();

      for (let i = 0; i < 1000; i++) {
        getAvatarFallback({ full_name: 'Test User' });
      }

      const endTime = performance.now();
      const duration = endTime - startTime;

      expect(duration).toBeLessThan(10);  // 1000 次執行 < 10ms
    });
  });
});
```

**測試統計**：
- 總測試案例：27 個
- 涵蓋場景：正常、fallback、邊界、優先級、性能
- 預期覆蓋率：100%

---

### 4.2 執行測試

```bash
# 在 frontend 目錄執行

# 運行所有測試
npm test

# 運行特定測試檔案
npm test avatar.test.ts

# 運行測試並查看覆蓋率
npm run test:coverage

# 運行測試 UI（互動模式）
npm run test:ui
```

**預期結果**：
```
✓ getAvatarFallback (27)
  ✓ 正常情況 - full_name (5)
  ✓ Fallback 到 name (3)
  ✓ Fallback 到 username (2)
  ✓ Fallback 到 email (2)
  ✓ 最終 fallback (3)
  ✓ 邊界情況 - 空格處理 (3)
  ✓ 邊界情況 - 特殊字符 (3)
  ✓ 優先級順序 (3)
  ✓ 性能測試 (1)

Test Files  1 passed (1)
     Tests  27 passed (27)
  Start at  12:00:00
  Duration  150ms

Coverage:
  avatar.ts  100%  (Statements 100%, Branches 100%, Functions 100%, Lines 100%)
```

---

### 4.3 組件整合測試（可選）

#### 檔案：`/frontend/components/features/search/__tests__/salesperson-card.test.tsx`

```typescript
import { render, screen } from '@testing-library/react';
import { SalespersonCard } from '../salesperson-card';

describe('SalespersonCard - Avatar Fallback', () => {
  const baseSalesperson = {
    id: 1,
    full_name: '張三',
    avatar: null,
    company_name: '三商美邦',
    industry_name: '保險業',
    bio: '專業業務員',
    specialties: '壽險,投資型保單',
    service_regions: ['台北', '新北'],
    years_of_experience: 5,
  };

  it('應該顯示業務員的 Avatar 與 fallback', () => {
    render(<SalespersonCard salesperson={baseSalesperson} />);

    // 檢查 Avatar 的 alt 屬性
    const avatar = screen.getByAltText('張三 的頭像');
    expect(avatar).toBeInTheDocument();
  });

  it('當 full_name 為 undefined 時，不應該崩潰', () => {
    const salesperson = {
      ...baseSalesperson,
      full_name: undefined as any,
      username: 'zhangsan',
    };

    // 不應該拋出錯誤
    expect(() => {
      render(<SalespersonCard salesperson={salesperson} />);
    }).not.toThrow();
  });

  it('當 full_name 為 null 時，應該顯示 username fallback', () => {
    const salesperson = {
      ...baseSalesperson,
      full_name: null as any,
      username: 'zhangsan',
    };

    render(<SalespersonCard salesperson={salesperson} />);

    // 應該顯示 username
    expect(screen.getByText('zhangsan')).toBeInTheDocument();
  });

  it('當所有名稱欄位都為空時，應該顯示預設文字', () => {
    const salesperson = {
      ...baseSalesperson,
      full_name: null as any,
      username: null as any,
    };

    render(<SalespersonCard salesperson={salesperson} />);

    // 應該顯示預設文字
    expect(screen.getByText('未命名業務員')).toBeInTheDocument();
  });
});
```

---

## 5. 驗證步驟

### 5.1 自動化測試驗證

```bash
# 步驟 1：執行單元測試
npm test avatar.test.ts

# 步驟 2：執行所有測試
npm test

# 步驟 3：檢查測試覆蓋率
npm run test:coverage

# 步驟 4：TypeScript 類型檢查
npm run typecheck

# 步驟 5：ESLint 檢查
npm run lint

# 步驟 6：Prettier 格式化
npm run format
```

**通過標準**：
- ✅ 所有測試通過（0 失敗）
- ✅ 測試覆蓋率 100%
- ✅ TypeScript 無類型錯誤
- ✅ ESLint 無警告
- ✅ Prettier 格式正確

---

### 5.2 手動測試驗證

#### 測試環境準備

```bash
# 1. 啟動開發伺服器
npm run dev

# 2. 訪問 http://localhost:3001
```

#### 測試案例清單

##### 測試 1：搜尋頁面（/search）

**步驟**：
1. 訪問搜尋頁面
2. 觀察業務員卡片
3. 檢查 Avatar 顯示

**預期結果**：
- ✅ 有 full_name 的業務員顯示名字首字母
- ✅ 沒有 full_name 的業務員顯示 username 首字母
- ✅ 所有 Avatar 正常顯示，無錯誤
- ✅ 懸停效果正常

**測試資料**：
```
業務員 A：full_name = "張三" → Avatar 顯示 "張三"
業務員 B：full_name = null, username = "john123" → Avatar 顯示 "JO"
業務員 C：所有欄位為空 → Avatar 顯示 "U"
```

---

##### 測試 2：業務員詳細頁面（/salesperson/[id]）

**步驟**：
1. 點擊業務員卡片
2. 進入詳細頁面
3. 檢查大頭貼顯示

**預期結果**：
- ✅ 大頭貼正常顯示（尺寸較大）
- ✅ Fallback 文字清晰可見
- ✅ 無 TypeError 錯誤

---

##### 測試 3：Dashboard 頁面（/dashboard）

**前提**：需要以業務員身份登入

**步驟**：
1. 登入為業務員
2. 訪問 Dashboard
3. 檢查個人資料 Avatar

**預期結果**：
- ✅ Avatar 正常顯示
- ✅ 如果沒有設定 full_name，顯示 fallback
- ✅ 無錯誤訊息

---

##### 測試 4：Header 使用者選單

**步驟**：
1. 登入系統
2. 查看 Header 右上角 Avatar
3. 點擊 Avatar 展開下拉選單

**預期結果**：
- ✅ Avatar 小圖示正常顯示
- ✅ Fallback 文字清晰（size="sm"）
- ✅ 下拉選單正常運作
- ✅ 顯示正確的使用者名稱

---

##### 測試 5：響應式測試

**測試裝置**：
- 📱 手機（375px）
- 📱 平板（768px）
- 💻 桌面（1280px）

**步驟**：
1. 調整瀏覽器視窗大小
2. 觀察 Avatar 顯示
3. 檢查佈局

**預期結果**：
- ✅ 所有尺寸下 Avatar 正常顯示
- ✅ Fallback 文字不被截斷
- ✅ 佈局不破壞

---

##### 測試 6：瀏覽器兼容性

**測試瀏覽器**：
- ✅ Chrome (最新版)
- ✅ Firefox (最新版)
- ✅ Safari (最新版)
- ✅ Edge (最新版)

**預期結果**：
- 所有瀏覽器中 Avatar 顯示一致
- 無 JavaScript 錯誤
- 無樣式問題

---

### 5.3 驗證檢查清單

**功能驗證**：
- [ ] 所有單元測試通過
- [ ] 搜尋頁面 Avatar 正常
- [ ] 業務員詳細頁面 Avatar 正常
- [ ] Dashboard 頁面 Avatar 正常
- [ ] Header 使用者選單 Avatar 正常
- [ ] 無 TypeError 錯誤

**視覺驗證**：
- [ ] Avatar 大小正確
- [ ] Fallback 文字清晰
- [ ] 色彩符合設計系統
- [ ] 圓角正確（rounded-full）
- [ ] 漸層背景正確

**響應式驗證**：
- [ ] 手機版正常
- [ ] 平板版正常
- [ ] 桌面版正常
- [ ] 觸控區域足夠大

**瀏覽器驗證**：
- [ ] Chrome 正常
- [ ] Firefox 正常
- [ ] Safari 正常
- [ ] Edge 正常

**無障礙驗證**：
- [ ] 所有 Avatar 有 alt 屬性
- [ ] 螢幕閱讀器可正確朗讀
- [ ] 鍵盤導航正常
- [ ] 色彩對比度符合 WCAG AA

**效能驗證**：
- [ ] 頁面載入速度無明顯變慢
- [ ] 無記憶體洩漏
- [ ] 無不必要的重新渲染

---

## 6. 回滾計畫

### 6.1 Git 版本控制

**Commit 策略**：每個階段獨立 commit，方便回滾

```bash
# Commit 1：建立工具函數
git add lib/utils/avatar.ts lib/utils/__tests__/avatar.test.ts
git commit -m "feat: Add getAvatarFallback utility function with tests"

# Commit 2：修復 SalespersonCard
git add components/features/search/salesperson-card.tsx
git commit -m "fix: Fix Avatar fallback in SalespersonCard"

# Commit 3：重構 Header
git add components/layout/header.tsx
git commit -m "refactor: Refactor Header Avatar to use getAvatarFallback"

# Commit 4：修復其他頁面（如果有）
git add app/
git commit -m "fix: Fix Avatar fallback in Dashboard and detail pages"
```

### 6.2 回滾步驟

**情境 1：發現新的 Bug**

```bash
# 回滾到上一個 commit
git revert HEAD

# 或回滾特定 commit
git revert <commit-hash>
```

**情境 2：需要完全回滾**

```bash
# 回滾所有修改（危險！）
git reset --hard HEAD~4  # 回滾 4 個 commits

# 或回滾到特定 commit
git reset --hard <commit-hash>
```

**情境 3：部分回滾**

```bash
# 只回滾特定檔案
git checkout HEAD~1 -- components/features/search/salesperson-card.tsx
```

### 6.3 緊急修復計畫

**如果線上環境出現問題**：

1. **立即回滾**：
   ```bash
   git revert HEAD
   git push origin main
   ```

2. **部署舊版本**：
   ```bash
   # 重新部署上一個穩定版本
   npm run build
   npm run deploy
   ```

3. **修復並重新部署**：
   - 在本地修復問題
   - 重新測試
   - 建立新的 commit
   - 部署

### 6.4 風險緩解

**降低風險的措施**：
1. ✅ 完整的單元測試（100% 覆蓋率）
2. ✅ 手動測試所有受影響頁面
3. ✅ 跨瀏覽器測試
4. ✅ 分階段 commit（易於部分回滾）
5. ✅ 程式碼審查（peer review）

---

## 7. 實作檢查清單

### 7.1 開發前檢查

- [ ] 已閱讀 Proposal
- [ ] 已閱讀 UI/UX 規格
- [ ] 已閱讀組件規格
- [ ] 已理解問題根本原因
- [ ] 已準備開發環境

### 7.2 實作過程檢查

**Phase 1：工具函數**
- [ ] 建立 `lib/utils/avatar.ts`
- [ ] 實作 `getAvatarFallback()`
- [ ] 實作 `processFallbackField()`
- [ ] 新增完整 JSDoc 註解
- [ ] 新增 TypeScript 類型定義
- [ ] 建立測試檔案
- [ ] 撰寫 27 個測試案例
- [ ] 所有測試通過
- [ ] 測試覆蓋率 100%

**Phase 2：修復組件**
- [ ] 修復 SalespersonCard
  - [ ] Import 工具函數
  - [ ] 修改 Avatar fallback
  - [ ] 新增 alt 屬性
  - [ ] 改善標題顯示
- [ ] 重構 Header
  - [ ] Import 工具函數
  - [ ] 簡化 Avatar fallback
  - [ ] 新增 alt 屬性
- [ ] 檢查 Dashboard 頁面
- [ ] 檢查 Salesperson Detail 頁面
- [ ] 使用 grep 搜尋遺漏

**Phase 3：測試驗證**
- [ ] 執行單元測試
- [ ] 執行組件測試
- [ ] TypeScript 檢查
- [ ] ESLint 檢查
- [ ] Prettier 格式化
- [ ] 手動測試所有頁面
- [ ] 響應式測試
- [ ] 跨瀏覽器測試
- [ ] 無障礙測試

**Phase 4：程式碼品質**
- [ ] 程式碼審查
- [ ] 清理 console.log
- [ ] 清理註解
- [ ] Git commit message 規範
- [ ] 更新相關文檔

### 7.3 部署前檢查

- [ ] 所有測試通過
- [ ] 無 TypeScript 錯誤
- [ ] 無 ESLint 警告
- [ ] 程式碼格式化完成
- [ ] 手動測試完成
- [ ] 程式碼審查通過
- [ ] 建立 Pull Request
- [ ] CI/CD 通過

---

## 8. 預期時間表

### 8.1 時間分配

| 階段 | 任務 | 預計時間 |
|-----|------|---------|
| Phase 1 | 建立工具函數與測試 | 30 分鐘 |
| Phase 2 | 修復組件 | 45 分鐘 |
| Phase 3 | 測試驗證 | 30 分鐘 |
| Phase 4 | 程式碼審查與清理 | 15 分鐘 |
| **總計** | | **2 小時** |

### 8.2 詳細時間表

**Phase 1: 建立工具函數（30 分鐘）**
- 0-10 分鐘：建立 `avatar.ts`，實作函數
- 10-15 分鐘：新增 JSDoc 和類型定義
- 15-30 分鐘：建立測試檔案，撰寫測試案例

**Phase 2: 修復組件（45 分鐘）**
- 0-15 分鐘：修復 SalespersonCard
- 15-25 分鐘：重構 Header
- 25-35 分鐘：檢查並修復 Dashboard 和 Detail 頁面
- 35-45 分鐘：使用 grep 搜尋並修復遺漏

**Phase 3: 測試驗證（30 分鐘）**
- 0-5 分鐘：執行自動化測試
- 5-20 分鐘：手動測試所有頁面
- 20-25 分鐘：響應式和跨瀏覽器測試
- 25-30 分鐘：無障礙測試

**Phase 4: 程式碼審查（15 分鐘）**
- 0-5 分鐘：清理程式碼
- 5-10 分鐘：程式碼格式化和檢查
- 10-15 分鐘：建立 commits 和 PR

---

## 9. 成功標準

### 9.1 功能標準

- ✅ **零錯誤**：所有使用 Avatar 的地方不再出現 TypeError
- ✅ **優雅降級**：當資料不完整時，顯示合理的預設值
- ✅ **一致性**：所有 Avatar fallback 使用相同的策略
- ✅ **測試覆蓋**：工具函數測試覆蓋率 100%

### 9.2 品質標準

- ✅ **TypeScript**：strict mode 通過，無類型錯誤
- ✅ **ESLint**：無警告或錯誤
- ✅ **測試**：所有測試通過，覆蓋率 100%
- ✅ **文檔**：完整的 JSDoc 註解

### 9.3 使用者體驗標準

- ✅ **視覺一致**：所有 Avatar fallback 視覺一致
- ✅ **響應式**：所有裝置上正常顯示
- ✅ **無障礙**：符合 WCAG 2.1 AA 標準
- ✅ **效能**：無明顯效能影響

---

## 10. 附錄

### 10.1 常見問題

**Q1: 為什麼選擇建立工具函數，而不是修改 Avatar 組件？**

A: 因為 Avatar 組件設計良好，已經接受 `fallback` prop。問題在於 **如何生成 fallback**，而非 Avatar 組件本身。建立工具函數可以：
- 保持 Avatar 組件的通用性
- 統一 fallback 邏輯
- 易於測試
- 可在任何地方複用

---

**Q2: 為什麼不在 processFallbackField 中處理 Emoji？**

A: Emoji 處理複雜，且不是常見情況。目前的 `substring()` 可能會切割 Emoji，但：
- 大多數使用者不會用 Emoji 作為名字
- 處理 Emoji 需要額外的依賴（如 `grapheme-splitter`）
- 增加複雜度和效能開銷

未來如果需要，可以使用：
```typescript
function processFallbackField(value: string | null | undefined): string | null {
  // ... 其他邏輯

  // 使用 Array.from 正確處理 Emoji
  const chars = Array.from(trimmed);
  if (chars.length === 1) {
    return chars[0].toUpperCase();
  }
  return chars.slice(0, 2).join('').toUpperCase();
}
```

---

**Q3: 為什麼測試覆蓋率要求 100%？**

A: 因為這是一個 **關鍵的防禦性函數**：
- 防止頁面崩潰
- 邏輯簡單，容易達到 100%
- 測試案例不多（27 個）
- 完整測試能確保所有邊界情況都被處理

---

**Q4: 如果 API 改變，怎麼辦？**

A: 工具函數已經設計為 **防禦性的**：
- 接受所有可能的欄位（full_name, name, username, email）
- 所有欄位都是可選的（`?`）
- 永遠返回有效字串（最差情況返回 'U'）

即使 API 增加新欄位，也不會破壞現有功能。

---

### 10.2 參考資料

**內部文檔**：
- [Proposal](../proposal.md)
- [UI/UX 規格](./ui-ux.md)
- [組件規格](./components.md)
- [Frontend 開發規範](/frontend/CLAUDE.md)
- [設計系統](/frontend/docs/design-system.md)

**外部資源**：
- [TypeScript Optional Chaining](https://www.typescriptlang.org/docs/handbook/release-notes/typescript-3-7.html#optional-chaining)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Vitest Documentation](https://vitest.dev/)

---

**建立日期**: 2026-01-12
**作者**: React Specialist (AI)
**審查者**: QA Engineer, DevOps Engineer
**狀態**: Ready for Implementation
**預計完成**: 2026-01-12 (2 小時)
