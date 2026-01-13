---
category: frontend
tags: [react, components, patterns, typescript]
priority: high
last_updated: 2026-01-13
applies_to: React 19, TypeScript 5
related_docs: [architecture.md, state-management.md]
---

# React 組件模式

## Quick Reference

- 組件命名: PascalCase
- 檔案命名: PascalCase.tsx
- Props interface: {ComponentName}Props
- 優先使用: Functional Components + Hooks
- 組件分類: Presentation, Container, Layout
- 狀態管理: Props drilling避免，使用 Context/Zustand

## 使用場景

**適用於**:
- 所有 React 組件設計
- UI 組件開發
- 組件重構

## 核心概念

良好的組件設計是可維護前端的基礎。遵循 React 最佳實踐和設計模式。

## 組件分類

### 1. Presentation Components (展示組件)

純展示，接收 props，無狀態邏輯。

```tsx
interface SalespersonCardProps {
  name: string;
  position: string;
  company: string;
  onFavorite: () => void;
}

export function SalespersonCard({ 
  name, 
  position, 
  company, 
  onFavorite 
}: SalespersonCardProps) {
  return (
    <Card>
      <CardHeader>
        <h3>{name}</h3>
      </CardHeader>
      <CardContent>
        <p>{position}</p>
        <p>{company}</p>
        <Button onClick={onFavorite}>收藏</Button>
      </CardContent>
    </Card>
  );
}
```

### 2. Container Components (容器組件)

包含業務邏輯和狀態管理。

```tsx
'use client';

import { useSalespersons } from '@/lib/hooks/useSalesperson';
import { SalespersonCard } from './SalespersonCard';
import { SalespersonListSkeleton } from './SalespersonListSkeleton';

export function SalespersonList() {
  const { data: salespersons, isLoading, error } = useSalespersons();
  
  if (isLoading) return <SalespersonListSkeleton />;
  if (error) return <ErrorMessage error={error} />;
  
  return (
    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
      {salespersons?.map((salesperson) => (
        <SalespersonCard
          key={salesperson.id}
          salesperson={salesperson}
        />
      ))}
    </div>
  );
}
```

### 3. Layout Components

頁面佈局組件。

```tsx
interface DashboardLayoutProps {
  children: React.ReactNode;
}

export function DashboardLayout({ children }: DashboardLayoutProps) {
  return (
    <div className="flex min-h-screen">
      <Sidebar />
      <main className="flex-1">
        <Header />
        <div className="container mx-auto p-6">
          {children}
        </div>
      </main>
    </div>
  );
}
```

## 常見模式

### Compound Components

```tsx
export function Card({ children }: { children: React.ReactNode }) {
  return <div className="card">{children}</div>;
}

Card.Header = function CardHeader({ children }: { children: React.ReactNode }) {
  return <div className="card-header">{children}</div>;
};

Card.Content = function CardContent({ children }: { children: React.ReactNode }) {
  return <div className="card-content">{children}</div>;
};

// 使用
<Card>
  <Card.Header>標題</Card.Header>
  <Card.Content>內容</Card.Content>
</Card>
```

### Render Props

```tsx
interface DataFetcherProps<T> {
  url: string;
  render: (data: T) => React.ReactNode;
}

function DataFetcher<T>({ url, render }: DataFetcherProps<T>) {
  const { data, isLoading } = useQuery({
    queryKey: [url],
    queryFn: () => fetch(url).then(r => r.json())
  });
  
  if (isLoading) return <Spinner />;
  
  return <>{render(data)}</>;
}
```

### Custom Hooks

```tsx
function useFormValidation<T>(initialValues: T, validate: (values: T) => Record<string, string>) {
  const [values, setValues] = useState(initialValues);
  const [errors, setErrors] = useState<Record<string, string>>({});
  
  const handleChange = (name: keyof T, value: any) => {
    setValues(prev => ({ ...prev, [name]: value }));
  };
  
  const handleSubmit = () => {
    const validationErrors = validate(values);
    setErrors(validationErrors);
    return Object.keys(validationErrors).length === 0;
  };
  
  return { values, errors, handleChange, handleSubmit };
}
```

## 最佳實踐

- [ ] 組件單一職責
- [ ] Props 有完整類型定義
- [ ] 避免 Props drilling（超過2層使用 Context）
- [ ] 使用 React.memo 優化效能（需要時）
- [ ] 組件命名清晰表達用途
- [ ] 使用 TypeScript 泛型提高重用性

## 相關知識

- [架構模式](./architecture.md) - 組件組織
- [狀態管理](./state-management.md) - 狀態處理

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
