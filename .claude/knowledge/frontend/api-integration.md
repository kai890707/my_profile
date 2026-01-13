---
category: frontend
tags: [api, axios, react-query, http]
priority: medium
last_updated: 2026-01-13
applies_to: Axios, React Query 5.x
related_docs: [architecture.md, state-management.md, ../backend/api-design.md]
---

# API 整合

## Quick Reference

- HTTP Client: Axios
- 狀態管理: React Query
- Base URL: 環境變數 NEXT_PUBLIC_API_URL
- 認證: JWT Bearer Token
- 錯誤處理: Interceptors + Error boundaries
- Retry: React Query 自動重試

## 使用場景

**適用於**:
- 所有 Backend API 呼叫
- 檔案上傳
- WebSocket（使用其他方式）

## 核心概念

統一的 API Client 確保一致的錯誤處理、認證和重試邏輯。

## API Client 設定

見 [architecture.md](./architecture.md) 中的 API Client 實作。

## React Query 整合

```tsx
// lib/hooks/useSalesperson.ts
export function useSalespersons(filters?: SalespersonFilters) {
  return useQuery({
    queryKey: ['salespersons', filters],
    queryFn: () => salespersonApi.getAll(filters),
    staleTime: 5 * 60 * 1000,
    retry: 3,
  });
}
```

## 錯誤處理

```tsx
// components/ErrorBoundary.tsx
export function ErrorBoundary({ children }: { children: React.ReactNode }) {
  return (
    <QueryErrorResetBoundary>
      {({ reset }) => (
        <RootErrorBoundary onReset={reset}>
          {children}
        </RootErrorBoundary>
      )}
    </QueryErrorResetBoundary>
  );
}
```

## Loading 狀態

```tsx
export function SalespersonList() {
  const { data, isLoading, error } = useSalespersons();
  
  if (isLoading) return <Skeleton />;
  if (error) return <ErrorMessage />;
  
  return <div>{/* 渲染資料 */}</div>;
}
```

## 最佳實踐

- [ ] 使用 React Query 管理 API 狀態
- [ ] 實作 Loading 和 Error UI
- [ ] API Client 統一錯誤處理
- [ ] 敏感資料不儲存在前端
- [ ] 使用環境變數管理 API URL

## 相關知識

- [Backend API 設計](../backend/api-design.md) - API 規範
- [狀態管理](./state-management.md) - React Query 詳解

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
