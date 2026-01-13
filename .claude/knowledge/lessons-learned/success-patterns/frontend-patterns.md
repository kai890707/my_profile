---
category: lessons-learned
tags: [frontend, patterns, react, nextjs]
priority: high
last_updated: 2026-01-14
applies_to: Next.js 15, React 19
related_docs: [../../frontend/component-patterns.md]
---

# Frontend 成功模式

## Quick Reference

記錄實踐驗證有效的 Frontend 設計模式。

---

## SP-FE-001: 組件組合模式

### 目的
提升組件靈活性和可複用性。

### 實作範例
```typescript
// 複合組件模式
export function Card({ children }: { children: React.ReactNode }) {
  return <div className="card">{children}</div>;
}

Card.Header = function CardHeader({ children }: { children: React.ReactNode }) {
  return <div className="card-header">{children}</div>;
};

Card.Body = function CardBody({ children }: { children: React.ReactNode }) {
  return <div className="card-body">{children}</div>;
};

Card.Footer = function CardFooter({ children }: { children: React.ReactNode }) {
  return <div className="card-footer">{children}</div>;
};

// 使用
<Card>
  <Card.Header><h2>Title</h2></Card.Header>
  <Card.Body><p>Content</p></Card.Body>
  <Card.Footer><Button>Action</Button></Card.Footer>
</Card>
```

### 優點
- 高度靈活
- 明確的結構
- 易於理解和使用

---

## SP-FE-002: Custom Hooks 抽取邏輯

### 目的
複用狀態邏輯，保持組件簡潔。

### 實作範例
```typescript
// hooks/usePagination.ts
export function usePagination<T>(
  fetchFn: (page: number) => Promise<PaginatedResponse<T>>,
  initialPage = 1
) {
  const [page, setPage] = useState(initialPage);
  const { data, isLoading } = useQuery({
    queryKey: ['paginated', page],
    queryFn: () => fetchFn(page),
  });

  return {
    data: data?.data ?? [],
    pagination: data?.meta,
    page,
    setPage,
    isLoading,
  };
}

// 使用
export function SalespersonList() {
  const { data, pagination, page, setPage, isLoading } = usePagination(
    (page) => api.getSalespersons({ page })
  );

  if (isLoading) return <Skeleton />;

  return (
    <div>
      {data.map(item => <SalespersonCard key={item.id} data={item} />)}
      <Pagination current={page} total={pagination.total} onChange={setPage} />
    </div>
  );
}
```

---

## SP-FE-003: Error Boundary 包裝

### 實作範例
```typescript
// components/ErrorBoundary.tsx
export function ErrorBoundary({ children }: { children: React.ReactNode }) {
  return (
    <QueryErrorResetBoundary>
      {({ reset }) => (
        <RootErrorBoundary onReset={reset}>
          <Suspense fallback={<Loading />}>
            {children}
          </Suspense>
        </RootErrorBoundary>
      )}
    </QueryErrorResetBoundary>
  );
}

// app/layout.tsx
export default function RootLayout({ children }) {
  return (
    <html>
      <body>
        <ErrorBoundary>
          {children}
        </ErrorBoundary>
      </body>
    </html>
  );
}
```

---

**已記錄**: 3 個成功模式

**相關**: [組件模式](../../frontend/component-patterns.md)
