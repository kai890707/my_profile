# 組件規格：Avatar Fallback 系統

**功能**: 修復 Avatar Fallback 的 TypeError Bug
**版本**: 1.0
**建立日期**: 2026-01-12
**作者**: React Specialist (AI)

---

## 1. 組件架構總覽

### 1.1 組件關係圖

```
lib/utils/avatar.ts
    └─ getAvatarFallback()  ← 核心工具函數
         ↓
         ├─ components/ui/avatar.tsx (Avatar 組件)
         ↓
         ├─ components/features/search/salesperson-card.tsx
         ├─ components/layout/header.tsx
         ├─ app/(dashboard)/dashboard/page.tsx
         └─ app/salesperson/[id]/page.tsx
```

### 1.2 檔案結構

```
frontend/
├── lib/
│   └── utils/
│       ├── avatar.ts                          ← 新建：工具函數
│       └── __tests__/
│           └── avatar.test.ts                  ← 新建：單元測試
│
├── components/
│   ├── ui/
│   │   └── avatar.tsx                          ← 已存在：不修改
│   ├── features/
│   │   └── search/
│   │       └── salesperson-card.tsx            ← 修改：使用新函數
│   └── layout/
│       └── header.tsx                          ← 審查：可選重構
│
├── app/
│   ├── (dashboard)/
│   │   └── dashboard/
│   │       └── page.tsx                        ← 修改：使用新函數
│   └── salesperson/
│       └── [id]/
│           └── page.tsx                        ← 修改：使用新函數
│
└── types/
    └── api.ts                                  ← 檢查：類型定義
```

---

## 2. 核心工具函數規格

### 2.1 函數簽名

**檔案位置**: `/frontend/lib/utils/avatar.ts`

```typescript
/**
 * 生成 Avatar 的 fallback 文字
 *
 * 此函數提供統一的 Avatar fallback 策略，依照優先級順序嘗試從多個欄位取得資料：
 * 1. full_name（業務員的完整姓名）
 * 2. name（一般使用者的名字）
 * 3. username（使用者名稱）
 * 4. email（電子郵件）
 * 5. 'U'（最終預設值）
 *
 * @param user - 包含使用者名稱資訊的物件
 * @returns 1-2 個字元的 fallback 文字
 *
 * @example
 * // 中文名字
 * getAvatarFallback({ full_name: '張三' }) // '張三'
 *
 * @example
 * // 英文名字
 * getAvatarFallback({ full_name: 'John Doe' }) // 'JO'
 *
 * @example
 * // 使用 username fallback
 * getAvatarFallback({ full_name: null, username: 'john123' }) // 'JO'
 *
 * @example
 * // 最終 fallback
 * getAvatarFallback({}) // 'U'
 *
 * @remarks
 * - 所有輸入都會經過 trim() 處理
 * - 英文字母會轉換為大寫
 * - 中文字元保持原樣
 * - 單字元字串直接返回（不補充其他字元）
 */
export function getAvatarFallback(user: {
  full_name?: string | null;
  name?: string | null;
  username?: string | null;
  email?: string | null;
}): string;
```

### 2.2 類型定義

```typescript
/**
 * Avatar Fallback 使用者資料介面
 *
 * 所有欄位都是可選的，函數會依照優先級順序嘗試取得資料
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
 * Avatar Fallback 文字
 *
 * 長度為 1-2 個字元的字串
 */
export type AvatarFallbackText = string;
```

### 2.3 完整實作

```typescript
/**
 * 處理單個欄位的 fallback 邏輯
 *
 * @internal
 */
function processFallbackField(value: string | null | undefined): string | null {
  if (!value) return null;

  const trimmed = value.trim();
  if (trimmed.length === 0) return null;

  if (trimmed.length === 1) {
    return trimmed.toUpperCase();
  }

  return trimmed.substring(0, 2).toUpperCase();
}

/**
 * 生成 Avatar 的 fallback 文字
 */
export function getAvatarFallback(user: AvatarFallbackUser): AvatarFallbackText {
  // 嘗試 full_name
  const fullNameFallback = processFallbackField(user.full_name);
  if (fullNameFallback) return fullNameFallback;

  // 嘗試 name
  const nameFallback = processFallbackField(user.name);
  if (nameFallback) return nameFallback;

  // 嘗試 username
  const usernameFallback = processFallbackField(user.username);
  if (usernameFallback) return usernameFallback;

  // 嘗試 email
  const emailFallback = processFallbackField(user.email);
  if (emailFallback) return emailFallback;

  // 最終 fallback
  return 'U';
}
```

### 2.4 函數特性

**純函數（Pure Function）**：
- ✅ 相同輸入永遠產生相同輸出
- ✅ 無副作用
- ✅ 不依賴外部狀態
- ✅ 易於測試

**效能特性**：
- 時間複雜度：O(1)
- 空間複雜度：O(1)
- 執行時間：< 1ms

**錯誤處理**：
- 所有輸入都經過 null/undefined 檢查
- 使用 optional chaining 避免崩潰
- 永遠返回有效字串（最差情況返回 'U'）

---

## 3. Avatar 組件規格

### 3.1 現有 Avatar 組件

**檔案位置**: `/frontend/components/ui/avatar.tsx`

**現狀**：組件設計良好，不需要修改

**Props 介面**：
```typescript
interface AvatarProps {
  /** 頭像圖片 URL */
  src?: string | null;

  /** 圖片替代文字 */
  alt?: string;

  /** Avatar 尺寸 */
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';

  /** 自訂 CSS class */
  className?: string;

  /** Fallback 文字（當圖片無法載入時顯示）*/
  fallback?: string;

  /** 在線狀態指示器 */
  status?: 'online' | 'offline' | 'away' | 'busy';
}
```

**尺寸對應表**：
```typescript
const sizes = {
  xs: 'h-8 w-8',      // 32px
  sm: 'h-10 w-10',    // 40px
  md: 'h-12 w-12',    // 48px
  lg: 'h-16 w-16',    // 64px ← SalespersonCard 使用
  xl: 'h-20 w-20',    // 80px
  '2xl': 'h-24 w-24', // 96px
};
```

**使用範例**：
```tsx
import { Avatar } from '@/components/ui/avatar';
import { getAvatarFallback } from '@/lib/utils/avatar';

<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  size="lg"
  alt={`${user.full_name || '使用者'} 的頭像`}
/>
```

---

## 4. SalespersonCard 組件修改規格

### 4.1 組件位置

**檔案**: `/frontend/components/features/search/salesperson-card.tsx`

### 4.2 現有問題

**第 43 行**（錯誤位置）：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={salesperson.full_name.substring(0, 2)}  // ❌ 當 full_name 為 undefined 時崩潰
  size="lg"
/>
```

**錯誤類型**：
```
TypeError: Cannot read properties of undefined (reading 'substring')
```

### 4.3 修改方案

#### 步驟 1：Import 工具函數

**在檔案頂部新增**：
```typescript
import { getAvatarFallback } from '@/lib/utils/avatar';
```

**修改後的 import 區塊**（第 1-6 行）：
```typescript
import Link from 'next/link';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { MapPin, Briefcase } from 'lucide-react';
import { SalespersonSearchResult } from '@/types/api';
import { getAvatarFallback } from '@/lib/utils/avatar';  // ← 新增
```

#### 步驟 2：修改 Avatar 使用

**修改前**（第 41-45 行）：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={salesperson.full_name.substring(0, 2)}  // ❌ 危險
  size="lg"
/>
```

**修改後**：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}  // ✅ 安全
  size="lg"
  alt={`${salesperson.full_name || '業務員'} 的頭像`}
/>
```

### 4.4 完整修改後的組件

**組件程式碼**（完整版）：
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

  // Normalize service_regions to array
  const serviceRegions = (() => {
    const regions = salesperson.service_regions as string[] | string | null | undefined;
    if (!regions) return [];
    if (Array.isArray(regions)) return regions;
    if (typeof regions === 'string') {
      try {
        // Try parsing as JSON first
        const parsed = JSON.parse(regions);
        return Array.isArray(parsed) ? parsed : [regions];
      } catch {
        // If not JSON, split by comma
        return regions.split(',').map((r: string) => r.trim());
      }
    }
    return [];
  })();

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
              {salesperson.company_name && (
                <div className="flex items-center gap-1 text-sm text-slate-600 mt-1">
                  <Briefcase className="h-4 w-4" />
                  <span className="truncate">{salesperson.company_name}</span>
                </div>
              )}
              {salesperson.industry_name && (
                <Badge variant="secondary" size="sm" className="mt-2">
                  {salesperson.industry_name}
                </Badge>
              )}
            </div>
          </div>

          {/* 簡介 */}
          {salesperson.bio && (
            <p className="text-sm text-slate-600 line-clamp-2 mb-4">
              {salesperson.bio}
            </p>
          )}

          {/* 專長標籤 */}
          {specialtiesList.length > 0 && (
            <div className="flex flex-wrap gap-2 mb-4">
              {specialtiesList.map((specialty, index) => (
                <Badge key={index} variant="primary" size="sm">
                  {specialty.trim()}
                </Badge>
              ))}
            </div>
          )}

          {/* 服務地區 */}
          {serviceRegions.length > 0 && (
            <div className="flex items-center gap-2 text-xs text-slate-500">
              <MapPin className="h-3.5 w-3.5" />
              <span>服務地區: {serviceRegions.join('、')}</span>
            </div>
          )}
        </CardContent>
      </Card>
    </Link>
  );
}
```

**變更摘要**：
1. ✅ 新增 import：`getAvatarFallback`
2. ✅ 修改 Avatar fallback：使用工具函數
3. ✅ 新增 alt 屬性：改善無障礙性
4. ✅ 改善標題顯示：處理 full_name 為空的情況

---

## 5. Dashboard 頁面修改規格

### 5.1 檔案位置

**檔案**: `/frontend/app/(dashboard)/dashboard/page.tsx`

### 5.2 需要檢查的 Avatar 使用位置

**搜尋模式**：在檔案中搜尋所有 `<Avatar` 使用

**可能的位置**：
1. 個人資料卡片
2. 最近活動列表
3. 統計卡片

### 5.3 修改模板

**每個 Avatar 使用都應該遵循此模板**：

```tsx
import { getAvatarFallback } from '@/lib/utils/avatar';

// 在元件中
<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  size="xl"  // 根據需求調整
  alt={`${user.full_name || '使用者'} 的頭像`}
/>
```

### 5.4 檢查清單

- [ ] 搜尋所有 `<Avatar` 使用
- [ ] 檢查每個 Avatar 的 fallback 屬性
- [ ] 如果直接使用 `.substring()`，替換為 `getAvatarFallback()`
- [ ] 確認已 import `getAvatarFallback`
- [ ] 新增 alt 屬性（如果缺少）

---

## 6. Salesperson Detail 頁面修改規格

### 6.1 檔案位置

**檔案**: `/frontend/app/salesperson/[id]/page.tsx`

### 6.2 預期的 Avatar 使用

**業務員詳細頁面通常會有**：
1. 頁面頂部的大頭貼（size="2xl"）
2. 聯絡資訊區域的小頭像（size="md"）

### 6.3 修改範例

**大頭貼**（頁面頂部）：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}
  size="2xl"
  alt={`${salesperson.full_name || '業務員'} 的頭像`}
  className="border-4 border-white shadow-xl"
/>
```

**小頭像**（聯絡區域）：
```tsx
<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}
  size="md"
  alt={`${salesperson.full_name || '業務員'} 的頭像`}
/>
```

---

## 7. Header 組件審查規格

### 7.1 檔案位置

**檔案**: `/frontend/components/layout/header.tsx`

### 7.2 現有實作（第 95-101 行）

**優點**：已經有完善的 fallback 處理

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

**問題**：使用 optional chaining，但仍有潛在風險
- 如果 `full_name` 是空字串 `''`，`substring()` 不會報錯，但會返回空字串
- 邏輯複雜，重複程式碼

### 7.3 重構建議（可選）

#### 選項 A：保持現狀

**理由**：
- 目前實作已經能正常運作
- 使用 optional chaining 避免崩潰
- 不是優先修復對象

**建議**：先修復其他檔案，最後再決定是否重構 Header

#### 選項 B：重構為使用統一函數

**修改前**：
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

**修改後**：
```tsx
import { getAvatarFallback } from '@/lib/utils/avatar';

<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  size="sm"
  alt={`${user.full_name || user.username || '使用者'} 的頭像`}
/>
```

**優點**：
- 程式碼更簡潔
- 邏輯統一
- 易於維護

**缺點**：
- 改動現有運作正常的程式碼
- 需要額外測試

### 7.4 決策

**推薦**：選項 B（重構）

**理由**：
- 統一性優於保持現狀
- 降低未來維護成本
- 測試成本低（Header 使用廣泛，容易發現問題）

---

## 8. TypeScript 類型檢查

### 8.1 檢查 API 類型定義

**檔案**: `/frontend/types/api.ts`

**需要檢查的介面**：
```typescript
export interface SalespersonSearchResult {
  id: number;
  full_name: string;  // ← 檢查這裡
  avatar?: string | null;
  // ... 其他欄位
}

export interface User {
  id: number;
  username?: string;
  name?: string;
  full_name?: string;  // ← 檢查這裡
  email?: string;
  avatar?: string | null;
  // ... 其他欄位
}
```

### 8.2 類型修正建議

**如果 `full_name` 定義為 `string`**（不可選）：

**修改前**：
```typescript
export interface SalespersonSearchResult {
  full_name: string;  // ❌ 與實際 API 不符
}
```

**修改後**：
```typescript
export interface SalespersonSearchResult {
  full_name?: string | null;  // ✅ 允許 undefined 和 null
}
```

**理由**：
- API 可能返回 `null` 或省略該欄位
- 前端應該防禦性處理
- 與 `getAvatarFallback` 類型一致

---

## 9. 組件測試策略

### 9.1 單元測試（工具函數）

**檔案位置**: `/frontend/lib/utils/__tests__/avatar.test.ts`

**測試框架**: Vitest + @testing-library/react

**測試案例清單**（14+ 個）：

#### 分組 1：正常情況
```typescript
describe('getAvatarFallback - 正常情況', () => {
  it('應該返回 full_name 的前兩個字元', () => {
    expect(getAvatarFallback({ full_name: '張三' })).toBe('張三');
    expect(getAvatarFallback({ full_name: 'John Doe' })).toBe('JO');
  });

  it('應該處理單字元 full_name', () => {
    expect(getAvatarFallback({ full_name: '李' })).toBe('李');
    expect(getAvatarFallback({ full_name: 'A' })).toBe('A');
  });

  it('應該將英文轉換為大寫', () => {
    expect(getAvatarFallback({ full_name: 'john' })).toBe('JO');
  });
});
```

#### 分組 2：Fallback 到 name
```typescript
describe('getAvatarFallback - Fallback 到 name', () => {
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
});
```

#### 分組 3：Fallback 到 username
```typescript
describe('getAvatarFallback - Fallback 到 username', () => {
  it('當 full_name 和 name 都為 null 時，應該使用 username', () => {
    expect(getAvatarFallback({
      full_name: null,
      name: null,
      username: 'john123'
    })).toBe('JO');
  });
});
```

#### 分組 4：Fallback 到 email
```typescript
describe('getAvatarFallback - Fallback 到 email', () => {
  it('當所有名稱欄位都為 null 時，應該使用 email', () => {
    expect(getAvatarFallback({
      full_name: null,
      name: null,
      username: null,
      email: 'test@example.com'
    })).toBe('TE');
  });
});
```

#### 分組 5：最終 fallback
```typescript
describe('getAvatarFallback - 最終 fallback', () => {
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
```

#### 分組 6：邊界情況
```typescript
describe('getAvatarFallback - 邊界情況', () => {
  it('應該處理空字串', () => {
    expect(getAvatarFallback({ full_name: '' })).toBe('U');
    expect(getAvatarFallback({ full_name: '   ' })).toBe('U');
  });

  it('應該 trim 空格', () => {
    expect(getAvatarFallback({ full_name: '  John  ' })).toBe('JO');
  });

  it('應該處理中英文混合', () => {
    expect(getAvatarFallback({ full_name: '張Sam' })).toBe('張S');
  });
});
```

**測試覆蓋率目標**：100%

### 9.2 組件測試（SalespersonCard）

**測試重點**：
1. Avatar 正常渲染
2. Fallback 正確顯示
3. 不再出現 TypeError

**測試案例**：
```typescript
import { render, screen } from '@testing-library/react';
import { SalespersonCard } from './salesperson-card';

describe('SalespersonCard', () => {
  it('應該顯示業務員的 Avatar', () => {
    const salesperson = {
      id: 1,
      full_name: '張三',
      avatar: 'https://example.com/avatar.jpg',
      // ... 其他欄位
    };

    render(<SalespersonCard salesperson={salesperson} />);

    const avatar = screen.getByAltText('張三 的頭像');
    expect(avatar).toBeInTheDocument();
  });

  it('當 full_name 為 undefined 時，不應該崩潰', () => {
    const salesperson = {
      id: 1,
      full_name: undefined,
      username: 'zhangsan',
      avatar: null,
      // ... 其他欄位
    };

    // 不應該拋出錯誤
    expect(() => {
      render(<SalespersonCard salesperson={salesperson} />);
    }).not.toThrow();
  });

  it('應該顯示 fallback 文字', () => {
    const salesperson = {
      id: 1,
      full_name: '張三',
      avatar: null,
      // ... 其他欄位
    };

    render(<SalespersonCard salesperson={salesperson} />);

    // 檢查 fallback 文字是否存在
    expect(screen.getByText('張三')).toBeInTheDocument();
  });
});
```

---

## 10. 使用範例與最佳實踐

### 10.1 基本使用

```tsx
import { Avatar } from '@/components/ui/avatar';
import { getAvatarFallback } from '@/lib/utils/avatar';

function UserProfile({ user }) {
  return (
    <div>
      <Avatar
        src={user.avatar}
        fallback={getAvatarFallback(user)}
        size="lg"
      />
      <h2>{user.full_name || user.username}</h2>
    </div>
  );
}
```

### 10.2 搭配 useMemo 優化

**當使用者資料可能頻繁更新時**：

```tsx
import { useMemo } from 'react';
import { Avatar } from '@/components/ui/avatar';
import { getAvatarFallback } from '@/lib/utils/avatar';

function UserProfile({ user }) {
  // 只在 user 變更時重新計算
  const avatarFallback = useMemo(
    () => getAvatarFallback(user),
    [user]
  );

  return (
    <Avatar
      src={user.avatar}
      fallback={avatarFallback}
      size="lg"
    />
  );
}
```

### 10.3 列表中使用

```tsx
function UserList({ users }) {
  return (
    <div className="grid grid-cols-3 gap-4">
      {users.map((user) => (
        <div key={user.id}>
          <Avatar
            src={user.avatar}
            fallback={getAvatarFallback(user)}
            size="md"
          />
          <p>{user.full_name || user.username}</p>
        </div>
      ))}
    </div>
  );
}
```

### 10.4 搭配 Loading 狀態

```tsx
function UserProfile({ userId }) {
  const { data: user, isLoading } = useQuery(['user', userId], fetchUser);

  if (isLoading) {
    return (
      <div className="h-16 w-16 rounded-full bg-slate-200 animate-pulse" />
    );
  }

  return (
    <Avatar
      src={user?.avatar}
      fallback={getAvatarFallback(user || {})}
      size="lg"
    />
  );
}
```

---

## 11. 錯誤處理與除錯

### 11.1 常見錯誤

#### 錯誤 1：忘記 import 工具函數

**症狀**：
```
ReferenceError: getAvatarFallback is not defined
```

**解決**：
```tsx
import { getAvatarFallback } from '@/lib/utils/avatar';
```

#### 錯誤 2：傳入錯誤的資料結構

**症狀**：
```tsx
// ❌ 錯誤：傳入 string 而非 object
<Avatar fallback={getAvatarFallback(user.full_name)} />
```

**解決**：
```tsx
// ✅ 正確：傳入 object
<Avatar fallback={getAvatarFallback(user)} />
```

#### 錯誤 3：TypeScript 類型錯誤

**症狀**：
```
Type 'string' is not assignable to type 'AvatarFallbackUser'
```

**解決**：確保傳入的物件包含正確的欄位
```tsx
// ✅ 正確
const user: AvatarFallbackUser = {
  full_name: '張三',
  // ... 其他欄位
};

<Avatar fallback={getAvatarFallback(user)} />
```

### 11.2 除錯技巧

**在開發環境中輸出 fallback 結果**：
```tsx
const fallback = getAvatarFallback(user);
console.log('Avatar fallback:', fallback);  // 開發時除錯用

<Avatar fallback={fallback} />
```

**使用 React DevTools 檢查 Props**：
```
<Avatar>
  src: "https://..."
  fallback: "張三"  ← 檢查這個值
  size: "lg"
</Avatar>
```

---

## 12. 效能考量

### 12.1 函數執行效能

**基準測試結果**（預期）：
```
執行 10,000 次 getAvatarFallback()：
平均時間：< 0.01ms / 次
記憶體佔用：可忽略不計
```

**結論**：效能影響微乎其微，無需優化

### 12.2 渲染效能

**最佳實踐**：
- ✅ 在 props 中傳入預計算的 fallback
- ✅ 使用 useMemo（如果 user 頻繁更新）
- ❌ 避免在 render 中多次調用

**範例**：
```tsx
// ✅ 好：計算一次
function UserCard({ user }) {
  const fallback = getAvatarFallback(user);

  return (
    <div>
      <Avatar fallback={fallback} />
      <Avatar fallback={fallback} />  {/* 複用同一個值 */}
    </div>
  );
}

// ❌ 不好：計算兩次
function UserCard({ user }) {
  return (
    <div>
      <Avatar fallback={getAvatarFallback(user)} />
      <Avatar fallback={getAvatarFallback(user)} />
    </div>
  );
}
```

---

## 13. 組件檢查清單

### 13.1 實作前檢查

- [ ] 已閱讀 UI/UX 規格
- [ ] 已理解 Avatar 組件 API
- [ ] 已了解現有問題和解決方案
- [ ] 已準備測試環境

### 13.2 實作過程檢查

- [ ] 建立 `lib/utils/avatar.ts`
- [ ] 實作 `getAvatarFallback()` 函數
- [ ] 新增完整的 JSDoc 註解
- [ ] 建立測試檔案 `avatar.test.ts`
- [ ] 撰寫 14+ 個測試案例
- [ ] 所有測試通過
- [ ] TypeScript 無類型錯誤

### 13.3 實作後檢查

- [ ] 修改 SalespersonCard 組件
- [ ] 修改 Dashboard 頁面
- [ ] 修改 Salesperson Detail 頁面
- [ ] 審查 Header 組件（可選重構）
- [ ] 使用 `grep` 確認沒有遺漏
- [ ] 手動測試所有受影響頁面
- [ ] 跨瀏覽器測試
- [ ] 響應式測試

### 13.4 程式碼品質檢查

- [ ] ESLint 無警告
- [ ] Prettier 格式化完成
- [ ] TypeScript strict mode 通過
- [ ] 測試覆蓋率 100%
- [ ] 無 console.log（清理除錯程式碼）
- [ ] 程式碼審查通過

---

**審查者**: React Specialist, QA Engineer
**狀態**: Draft → Ready for Implementation
**下一步**: 撰寫實作規格 (implementation.md)
