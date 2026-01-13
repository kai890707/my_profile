---
category: lessons-learned
tags: [performance, frontend, optimization, react]
priority: high
last_updated: 2026-01-14
applies_to: Next.js 15, React 19
related_docs: [../../frontend/architecture.md]
---

# Frontend 效能優化技巧

## Quick Reference

記錄實際驗證有效的 Frontend 效能優化技巧。

---

## PT-FE-001: 圖片優化

### 問題
首頁載入 5 秒，圖片佔 3MB。

### 解決方案
使用 Next.js Image 組件 + WebP 格式。

### 實作
```typescript
// Before
<img src="/images/salesperson.jpg" alt="Salesperson" />
// 原始圖片: 500KB

// After
import Image from 'next/image';

<Image
  src="/images/salesperson.jpg"
  alt="Salesperson"
  width={300}
  height={300}
  quality={80}
  placeholder="blur"
/>
// 自動轉換: WebP 80KB, 自動尺寸調整, 懶載入
```

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| 圖片大小 | 3MB | 400KB | 7.5x |
| LCP | 5.2s | 1.6s | 3.3x |
| 頻寬節省 | - | 87% | - |

---

## PT-FE-002: Code Splitting

### 問題
Initial Bundle 1.2MB，首次載入慢。

### 解決方案
動態導入大型組件。

### 實作
```typescript
// Before
import HeavyChart from './HeavyChart';  // 200KB

export function Dashboard() {
  return <HeavyChart data={data} />;
}

// After
import dynamic from 'next/dynamic';

const HeavyChart = dynamic(() => import('./HeavyChart'), {
  loading: () => <ChartSkeleton />,
  ssr: false,  // 不需要 SSR
});

export function Dashboard() {
  return <HeavyChart data={data} />;
}
```

### 效能數據
- Initial Bundle: 1.2MB → 200KB (6x)
- TTI: 4.5s → 1.8s (2.5x)

---

## PT-FE-003: 虛擬化長列表

### 問題
渲染 1000 項列表卡頓。

### 解決方案
使用 React Virtual 虛擬化。

### 實作
```typescript
import { useVirtualizer } from '@tanstack/react-virtual';

export function SalespersonList({ items }: { items: Salesperson[] }) {
  const parentRef = useRef<HTMLDivElement>(null);

  const virtualizer = useVirtualizer({
    count: items.length,
    getScrollElement: () => parentRef.current,
    estimateSize: () => 100,  // 每項高度
  });

  return (
    <div ref={parentRef} style={{ height: '600px', overflow: 'auto' }}>
      <div style={{ height: `${virtualizer.getTotalSize()}px` }}>
        {virtualizer.getVirtualItems().map((virtualItem) => (
          <div
            key={virtualItem.key}
            style={{
              position: 'absolute',
              top: 0,
              left: 0,
              width: '100%',
              height: `${virtualItem.size}px`,
              transform: `translateY(${virtualItem.start}px)`,
            }}
          >
            <SalespersonCard data={items[virtualItem.index]} />
          </div>
        ))}
      </div>
    </div>
  );
}
```

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| 初始渲染 | 2500ms | 150ms | 17x |
| 滾動 FPS | 15 | 60 | 4x |
| 記憶體 | 250MB | 30MB | 8x |

---

## PT-FE-004: React Query 快取策略

### 實作
```typescript
export function useSalespersons(filters: Filters) {
  return useQuery({
    queryKey: ['salespersons', filters],
    queryFn: () => api.getSalespersons(filters),
    staleTime: 5 * 60 * 1000,  // 5 分鐘內不重新請求
    cacheTime: 10 * 60 * 1000,  // 10 分鐘後清除快取
    refetchOnWindowFocus: false,  // 視窗焦點不重新請求
  });
}
```

### 效能數據
- API 請求數: 減少 80%
- 頁面切換: 即時顯示（從快取）

---

**已記錄**: 4 個效能優化技巧

**相關**: [Frontend 架構](../../frontend/architecture.md)
