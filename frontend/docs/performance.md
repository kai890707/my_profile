# Performance Optimization Guide

本文檔說明 YAMU Frontend 專案的性能優化策略和最佳實踐。

## 已實施的優化

### 1. React Query 配置

**位置**: `lib/query/client.ts`

```typescript
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000, // 1 分鐘
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});
```

**效果**:
- 減少不必要的 API 請求
- 提供離線緩存體驗
- 降低伺服器負載

### 2. 圖片優化

**組件**: `components/ui/optimized-image.tsx`

**功能**:
- Lazy Loading（延遲加載）
- Intersection Observer API
- 自動處理加載狀態
- 錯誤處理和 fallback

**使用範例**:
```tsx
<OptimizedImage
  src={imageUrl}
  alt="Description"
  className="w-full h-64 object-cover rounded-lg"
  lazy={true}
/>
```

### 3. 路由守衛優化

**位置**: `middleware.ts`

**效果**:
- 在伺服器端進行權限檢查
- 避免不必要的客戶端渲染
- 減少頁面閃爍

### 4. 錯誤邊界

**位置**: `app/error.tsx`

**效果**:
- 防止整個應用崩潰
- 提供友善的錯誤訊息
- 允許錯誤恢復

## 性能監控

### 開發環境監控

使用 `PerformanceMonitor` 組件追蹤 Web Vitals:

```tsx
// app/layout.tsx
import { PerformanceMonitor } from '@/components/dev/performance-monitor';

export default function RootLayout({ children }) {
  return (
    <html>
      <body>
        {children}
        <PerformanceMonitor />
      </body>
    </html>
  );
}
```

### 監控指標

- **FCP (First Contentful Paint)**: < 2s
- **LCP (Largest Contentful Paint)**: < 2.5s
- **CLS (Cumulative Layout Shift)**: < 0.1
- **FID (First Input Delay)**: < 100ms
- **TTFB (Time to First Byte)**: < 600ms

## 進一步優化建議

### 1. 動態導入 (Dynamic Import)

對於大型組件，使用動態導入減少初始包大小：

```tsx
import dynamic from 'next/dynamic';

// 延遲加載管理員面板組件
const AdminDashboard = dynamic(() => import('@/components/features/admin/dashboard'), {
  loading: () => <div>Loading...</div>,
  ssr: false, // 如果不需要 SSR
});
```

### 2. 代碼分割建議

- **Admin Panel**: 只在管理員登入後加載
- **Charts**: 只在統計頁面加載（如 Recharts）
- **Rich Text Editor**: 只在需要時加載

### 3. 圖片優化清單

- [ ] 使用 WebP 格式（如果可能）
- [ ] 實施圖片 CDN（生產環境）
- [ ] 設置適當的快取標頭
- [ ] 使用響應式圖片（不同尺寸）

### 4. 字體優化

**已實施** (`app/layout.tsx`):
```tsx
const inter = Inter({
  subsets: ['latin'],
  display: 'swap', // 避免 FOIT
  variable: '--font-inter',
});
```

### 5. 打包優化

```bash
# 分析打包大小
npm run build
npx @next/bundle-analyzer

# 檢查哪些模塊佔用空間最大
```

### 6. React Query 進階配置

```typescript
// 針對不同類型的數據設置不同的 staleTime
export const salespersonKeys = {
  all: ['salespersons'] as const,
  lists: () => [...salespersonKeys.all, 'list'] as const,
  list: (filters: string) => [...salespersonKeys.lists(), { filters }] as const,
  details: () => [...salespersonKeys.all, 'detail'] as const,
  detail: (id: number) => [...salespersonKeys.details(), id] as const,
};

// 長時間緩存靜態數據
useQuery({
  queryKey: ['industries'],
  queryFn: getIndustries,
  staleTime: 5 * 60 * 1000, // 5 分鐘
  cacheTime: 10 * 60 * 1000, // 10 分鐘
});

// 短時間緩存動態數據
useQuery({
  queryKey: ['pending-approvals'],
  queryFn: getPendingApprovals,
  staleTime: 30 * 1000, // 30 秒
  refetchInterval: 60 * 1000, // 每分鐘自動刷新
});
```

## 性能測試

### Lighthouse 測試

```bash
# 安裝 Lighthouse
npm install -g lighthouse

# 運行測試
lighthouse http://localhost:3000 --view

# 針對特定頁面測試
lighthouse http://localhost:3000/dashboard --view
```

### 目標分數

- **Performance**: > 80
- **Accessibility**: > 90
- **Best Practices**: > 90
- **SEO**: > 90

## 監控和分析

### 生產環境監控

推薦使用以下服務：

1. **Vercel Analytics** (如果部署到 Vercel)
2. **Google Analytics 4** (用戶行為分析)
3. **Sentry** (錯誤追蹤)
4. **Web Vitals API** (性能監控)

### 實施 Web Vitals 追蹤

```tsx
// app/layout.tsx
import { useReportWebVitals } from 'next/web-vitals';

export function WebVitals() {
  useReportWebVitals((metric) => {
    // 發送到分析服務
    console.log(metric);
  });
}
```

## 效能檢查清單

### 開發階段
- [ ] 使用 React DevTools Profiler
- [ ] 檢查不必要的重新渲染
- [ ] 使用 `React.memo` 優化組件
- [ ] 避免在 render 中創建新對象/函數

### 構建階段
- [ ] 運行 `npm run build` 檢查打包大小
- [ ] 檢查 bundle size 警告
- [ ] 確保沒有重複的依賴

### 部署前
- [ ] 運行 Lighthouse 測試
- [ ] 測試真實設備性能
- [ ] 測試慢速網絡 (3G)
- [ ] 檢查 Console 無錯誤

### 部署後
- [ ] 監控真實用戶指標 (RUM)
- [ ] 設置性能預算
- [ ] 定期審查性能報告

## 常見性能問題和解決方案

### 問題 1: 首次載入慢

**原因**: 打包體積過大

**解決**:
- 使用動態導入拆分代碼
- 移除未使用的依賴
- Tree-shaking 檢查

### 問題 2: 頁面切換卡頓

**原因**: 同步資料獲取

**解決**:
- 使用 React Query 預取數據
- 實施骨架屏（Skeleton）
- 使用 Suspense

### 問題 3: 圖片載入慢

**原因**: 圖片未優化

**解決**:
- 使用 OptimizedImage 組件
- 實施 lazy loading
- 壓縮圖片大小

### 問題 4: API 請求過多

**原因**: 重複請求同一資源

**解決**:
- 調整 React Query staleTime
- 使用 prefetch 策略
- 合併多個請求

## 參考資源

- [Next.js Performance](https://nextjs.org/docs/app/building-your-application/optimizing)
- [Web Vitals](https://web.dev/vitals/)
- [React Query Performance](https://tanstack.com/query/latest/docs/react/guides/performance)
- [MDN Web Performance](https://developer.mozilla.org/en-US/docs/Web/Performance)

---

最後更新: 2026-01-09
