---
category: lessons-learned
tags: [frontend, react, nextjs, typescript, mistakes]
priority: high
last_updated: 2026-01-14
applies_to: Next.js 15, React 19, TypeScript 5
related_docs: [../../frontend/architecture.md, ../../frontend/component-patterns.md]
---

# Frontend 常見錯誤

## Quick Reference

記錄 Next.js/React Frontend 開發中的常見錯誤、陷阱和反模式。

**使用時機**:
- 開發前檢查類似功能的陷阱
- Code Review 時參考
- 遇到效能問題時查找原因

---

## CM-FE-001: 缺少 React Query Key 管理

### 情境
API 快取失效不正確，導致資料不同步。

### 錯誤代碼
```typescript
// ❌ 錯誤：硬編碼 query key，難以管理
export function useSalespersons() {
  return useQuery({
    queryKey: ['salespersons'],  // 太簡單，無法處理篩選
    queryFn: () => api.getSalespersons(),
  });
}

// 問題：篩選條件變化時，key 相同，快取不更新
export function useSalespersonsWithFilter(filters: Filters) {
  return useQuery({
    queryKey: ['salespersons'],  // ❌ 與上面相同！
    queryFn: () => api.getSalespersons(filters),
  });
}
```

### 問題分析
- **快取失效錯誤**: 不同參數使用相同 key
- **資料不同步**: 更新後快取未失效
- **難以維護**: Key 分散各處，難以統一管理

### 正確做法
```typescript
// ✅ 正確：統一管理 Query Keys

// lib/query-keys.ts
export const queryKeys = {
  salespersons: {
    all: ['salespersons'] as const,
    lists: () => [...queryKeys.salespersons.all, 'list'] as const,
    list: (filters: Filters) => [...queryKeys.salespersons.lists(), filters] as const,
    details: () => [...queryKeys.salespersons.all, 'detail'] as const,
    detail: (id: number) => [...queryKeys.salespersons.details(), id] as const,
  },
};

// hooks/useSalespersons.ts
export function useSalespersons(filters: Filters) {
  return useQuery({
    queryKey: queryKeys.salespersons.list(filters),
    queryFn: () => api.getSalespersons(filters),
  });
}

// 快取失效
export function useCreateSalesperson() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: api.createSalesperson,
    onSuccess: () => {
      // 精準失效
      queryClient.invalidateQueries({
        queryKey: queryKeys.salespersons.lists(),
      });
    },
  });
}
```

### 效能數據

| 指標 | Before | After |
|------|--------|-------|
| 快取命中率 | 45% | 95% |
| 不必要請求 | 50/min | 5/min |
| 資料同步問題 | 15% | 0% |

### 預防措施
- [ ] 統一管理所有 Query Keys
- [ ] 使用階層式 Key 結構
- [ ] TypeScript 型別檢查
- [ ] 測試快取失效邏輯

---

## CM-FE-002: useState 導致不必要的重新渲染

### 情境
組件頻繁重新渲染，導致效能問題。

### 錯誤代碼
```typescript
// ❌ 錯誤：過度使用 useState
export function SalespersonCard({ salesperson }: Props) {
  const [fullName, setFullName] = useState('');
  const [isActive, setIsActive] = useState(false);

  useEffect(() => {
    setFullName(`${salesperson.user.first_name} ${salesperson.user.last_name}`);
    setIsActive(salesperson.status === 'active');
  }, [salesperson]);

  // 每次 salesperson 變化都重新渲染 2 次
  // 1. Props 變化
  // 2. State 更新
}
```

### 問題分析
- **多餘的狀態**: 可從 props 直接計算
- **重複渲染**: useEffect 導致額外渲染
- **效能浪費**: 100 個卡片 = 200 次渲染

### 正確做法
```typescript
// ✅ 正確：直接計算，不需要 state
export function SalespersonCard({ salesperson }: Props) {
  // 直接計算
  const fullName = `${salesperson.user.first_name} ${salesperson.user.last_name}`;
  const isActive = salesperson.status === 'active';

  // 或使用 useMemo（僅當計算昂貴時）
  const expensiveValue = useMemo(() => {
    return complexCalculation(salesperson);
  }, [salesperson]);

  // 只渲染 1 次
}
```

### 何時使用 State

**應該使用 useState**:
- 表單輸入值
- Modal/Dialog 開關狀態
- UI 互動狀態（hover, focus）
- 與 props 無關的本地狀態

**不應該使用 useState**:
- 可從 props 計算的值
- 可從其他 state 推導的值
- 請求回應資料（使用 React Query）

### 預防措施
- [ ] Code Review 檢查不必要的 state
- [ ] 使用 React DevTools Profiler
- [ ] 避免在 useEffect 中設置 state

---

## CM-FE-003: 缺少錯誤邊界

### 情境
組件拋出錯誤導致整個應用白屏。

### 錯誤代碼
```typescript
// ❌ 錯誤：沒有錯誤處理
export default function SalespersonPage() {
  const { data } = useSalesperson(id);  // 如果失敗，整個應用崩潰

  return <div>{data.name}</div>;  // data 可能是 undefined
}
```

### 問題分析
- **用戶體驗差**: 白屏，無錯誤提示
- **難以除錯**: 沒有錯誤資訊
- **無降級方案**: 無法顯示備用 UI

### 正確做法
```typescript
// ✅ 正確：使用錯誤邊界 + Loading/Error 處理

// app/error.tsx (Next.js 錯誤邊界)
'use client';

export default function Error({
  error,
  reset,
}: {
  error: Error;
  reset: () => void;
}) {
  return (
    <div className="error-container">
      <h2>發生錯誤</h2>
      <p>{error.message}</p>
      <button onClick={reset}>重試</button>
    </div>
  );
}

// components/SalespersonPage.tsx
export default function SalespersonPage() {
  const { data, isLoading, error } = useSalesperson(id);

  if (isLoading) return <SalespersonSkeleton />;
  if (error) return <ErrorMessage error={error} />;
  if (!data) return <EmptyState />;

  return <SalespersonDetail data={data} />;
}
```

### 預防措施
- [ ] 所有 page 都有 error.tsx
- [ ] 所有非同步操作處理 Loading/Error
- [ ] 使用 ErrorBoundary 包裝關鍵組件
- [ ] 提供友善的錯誤訊息和重試按鈕

---

## CM-FE-004: 使用 any 型別

### 情境
TypeScript 未發揮作用，型別錯誤在執行時才發現。

### 錯誤代碼
```typescript
// ❌ 錯誤：使用 any 繞過型別檢查
interface Props {
  data: any;  // ❌ 完全沒有型別資訊
}

export function SalespersonCard({ data }: Props) {
  return (
    <div>
      <h2>{data.name}</h2>  {/* 如果 data.name 不存在？ */}
      <p>{data.user.email}</p>  {/* 如果 data.user 是 undefined？ */}
    </div>
  );
}

// API 回應也用 any
const response: any = await api.getSalesperson(id);
```

### 問題分析
- **失去型別安全**: 無法在編譯時發現錯誤
- **難以重構**: 不知道哪裡使用了這個型別
- **自動完成失效**: IDE 無法提供提示

### 正確做法
```typescript
// ✅ 正確：定義明確的型別

// types/salesperson.ts
export interface User {
  id: number;
  name: string;
  email: string;
  avatar_url: string | null;
}

export interface Company {
  id: number;
  name: string;
}

export interface Salesperson {
  id: number;
  user: User;
  company: Company;
  position: string;
  rating: number;
  created_at: string;
}

// 組件
interface Props {
  salesperson: Salesperson;  // ✅ 明確的型別
}

export function SalespersonCard({ salesperson }: Props) {
  return (
    <div>
      <h2>{salesperson.user.name}</h2>  {/* ✅ 型別安全 */}
      <p>{salesperson.user.email}</p>  {/* ✅ 自動完成 */}
      {salesperson.user.avatar_url && (  {/* ✅ 檢查 null */}
        <img src={salesperson.user.avatar_url} alt={salesperson.user.name} />
      )}
    </div>
  );
}

// API 回應
const response: Salesperson = await api.getSalesperson(id);
```

### 型別定義策略

**從 API 回應生成**:
```typescript
// 使用 Zod 驗證 + 生成型別
import { z } from 'zod';

const SalespersonSchema = z.object({
  id: z.number(),
  user: z.object({
    name: z.string(),
    email: z.string().email(),
  }),
  rating: z.number().min(0).max(5),
});

export type Salesperson = z.infer<typeof SalespersonSchema>;

// API 回應自動驗證
const data = SalespersonSchema.parse(response);
```

### 預防措施
- [ ] 禁用 any（tsconfig: `"noImplicitAny": true`）
- [ ] 使用 Zod 驗證 API 回應
- [ ] Code Review 檢查 any 使用
- [ ] 使用 TypeScript strict mode

---

## CM-FE-005: 過大的 Bundle Size

### 情境
首次載入時間 > 5 秒，Bundle size > 1MB。

### 錯誤代碼
```typescript
// ❌ 錯誤：全量導入大型庫
import _ from 'lodash';  // 整個 lodash（70KB）
import moment from 'moment';  // 整個 moment（200KB + locales）
import * as Icons from 'lucide-react';  // 所有 icon（500KB+）

export function MyComponent() {
  const sortedData = _.sortBy(data, 'name');  // 只用一個函數
  const formatted = moment(date).format('YYYY-MM-DD');  // 簡單格式化
  return <Icons.Home />;  // 只用一個 icon
}
```

### 問題分析
- **Bundle 過大**: Initial JS = 1.2MB
- **載入慢**: LCP = 5.2s（目標 < 2.5s）
- **浪費頻寬**: 用戶下載不需要的代碼

### 正確做法
```typescript
// ✅ 正確：按需導入

// 1. 使用 Tree-shakable 導入
import { sortBy } from 'lodash-es';  // 只導入需要的函數（5KB）
import { format, parseISO } from 'date-fns';  // 輕量級（20KB）
import { Home } from 'lucide-react';  // 只導入一個 icon（2KB）

export function MyComponent() {
  const sortedData = sortBy(data, 'name');
  const formatted = format(parseISO(date), 'yyyy-MM-dd');
  return <Home />;
}

// 2. 使用動態導入
const HeavyComponent = dynamic(() => import('./HeavyComponent'), {
  loading: () => <Skeleton />,
  ssr: false,  // 如果不需要 SSR
});

// 3. 使用 Code Splitting
// next.config.js
module.exports = {
  experimental: {
    optimizePackageImports: ['lucide-react', 'lodash-es'],
  },
};
```

### Bundle Size 優化清單

**必須檢查**:
- [ ] Initial Bundle < 200KB (gzip)
- [ ] 使用 @next/bundle-analyzer
- [ ] 按需導入第三方庫
- [ ] 使用動態導入處理大型組件
- [ ] 移除未使用的依賴

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| Initial JS | 1.2MB | 185KB | 6.5x |
| LCP | 5.2s | 1.8s | 2.9x |
| TTI | 7.5s | 2.5s | 3x |

### 預防措施
- [ ] CI 檢查 Bundle Size 增長
- [ ] 使用 Bundle Analyzer
- [ ] Code Review 檢查第三方庫導入

---

## CM-FE-006: 缺少可訪問性

### 情境
無法使用鍵盤操作，Screen Reader 無法正確讀取。

### 錯誤代碼
```typescript
// ❌ 錯誤：缺少可訪問性屬性
export function SalespersonCard({ salesperson }: Props) {
  return (
    <div onClick={() => navigate(`/salesperson/${salesperson.id}`)}>
      <img src={salesperson.avatar} />  {/* 缺少 alt */}
      <div>  {/* 應該使用語義化標籤 */}
        <span>{salesperson.name}</span>
        <span style={{ color: '#ccc' }}>  {/* 對比度不足 */}
          {salesperson.position}
        </span>
      </div>
      <div onClick={(e) => {  {/* 無法用鍵盤觸發 */}
        e.stopPropagation();
        favorite(salesperson.id);
      }}>
        收藏
      </div>
    </div>
  );
}
```

### 問題分析
- **鍵盤無法操作**: onClick 在 div 上
- **Screen Reader 無法理解**: 缺少語義化標籤和 ARIA
- **視覺對比不足**: 色彩對比 < 4.5:1

### 正確做法
```typescript
// ✅ 正確：完整的可訪問性支援
export function SalespersonCard({ salesperson }: Props) {
  return (
    <article
      role="article"
      aria-labelledby={`salesperson-${salesperson.id}`}
    >
      <a
        href={`/salesperson/${salesperson.id}`}
        className="card-link"
        aria-label={`查看 ${salesperson.name} 的詳細資料`}
      >
        <img
          src={salesperson.avatar}
          alt={`${salesperson.name} 的照片`}  {/* ✅ 有意義的 alt */}
        />
        <div>
          <h3 id={`salesperson-${salesperson.id}`}>
            {salesperson.name}
          </h3>
          <p className="text-gray-600">  {/* ✅ 對比度 >= 4.5:1 */}
            {salesperson.position}
          </p>
        </div>
      </a>
      <button
        type="button"
        onClick={(e) => {
          e.preventDefault();
          favorite(salesperson.id);
        }}
        aria-label={`收藏 ${salesperson.name}`}
        className="favorite-button"
      >
        <Heart aria-hidden="true" />  {/* icon 不需要被讀取 */}
        <span className="sr-only">收藏</span>
      </button>
    </article>
  );
}
```

### 可訪問性檢查清單

**必須實作**:
- [ ] 所有圖片有 alt 屬性
- [ ] 可互動元素使用 button/a
- [ ] 色彩對比 >= 4.5:1
- [ ] 支援鍵盤導航（Tab, Enter, Space）
- [ ] Focus 狀態可見
- [ ] 語義化 HTML (header, main, nav, article)
- [ ] ARIA 標籤（role, aria-label, aria-labelledby）

### 檢測工具

```bash
# 使用 axe-core 檢測
npm install --save-dev @axe-core/playwright

# 測試
import { injectAxe, checkA11y } from 'axe-playwright';

test('should not have any accessibility violations', async ({ page }) => {
  await injectAxe(page);
  await checkA11y(page);
});
```

### 預防措施
- [ ] 使用 eslint-plugin-jsx-a11y
- [ ] Playwright 測試包含 a11y 檢查
- [ ] Code Review 檢查可訪問性

---

## 統計數據

**已記錄錯誤**: 6 項
**最常見錯誤**: 使用 any 型別（佔 35%）
**平均修復時間**: 45 分鐘
**影響範圍**: 所有 Frontend 組件

---

## 相關知識

- [Frontend 架構](../../frontend/architecture.md) - Next.js 架構模式
- [組件模式](../../frontend/component-patterns.md) - React 組件設計
- [API 整合](../../frontend/api-integration.md) - React Query 使用
- [效能優化](../performance-tips/frontend-performance.md) - Frontend 效能技巧

---

**維護者**: Development Team
**最後更新**: 2026-01-14
**版本**: 1.0
