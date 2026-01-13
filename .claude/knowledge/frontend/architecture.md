---
category: frontend
tags: [nextjs, react, app-router, typescript]
priority: high
last_updated: 2026-01-13
applies_to: Next.js 15, React 19, TypeScript 5
related_docs: [component-patterns.md, state-management.md, ../workflow/sdd-process.md]
---

# Next.js 架構模式

## Quick Reference

- 框架: Next.js 15 (App Router)
- React版本: React 19
- 語言: TypeScript 5 (strict mode)
- 樣式: Tailwind CSS
- 組件庫: shadcn/ui
- 狀態管理: React Query + Zustand
- 目錄結構: Feature-based organization

## 使用場景

**適用於**:
- 所有 Frontend 功能開發
- 組件設計和實作
- 頁面路由規劃

**不適用於**:
- 非 Next.js 專案

## 核心概念

YAMU Frontend 採用 **Feature-based** 架構，按功能模組組織程式碼，結合 Next.js App Router 提供優異的開發體驗。

**架構特點**:
1. **App Router**: 使用 Next.js 15 的 App Router（非 Pages Router）
2. **Server Components**: 預設使用 Server Components，需要互動時使用 Client Components
3. **TypeScript Strict**: 嚴格類型檢查
4. **Feature-based**: 按功能模組組織，而非技術類型

## 目錄結構

```
frontend/
├── app/                        # Next.js App Router
│   ├── (auth)/                # Route group: 認證相關頁面
│   │   ├── login/
│   │   │   └── page.tsx
│   │   └── register/
│   │       └── page.tsx
│   ├── (dashboard)/           # Route group: 主要功能
│   │   ├── dashboard/
│   │   │   └── page.tsx
│   │   ├── salespersons/
│   │   │   ├── page.tsx       # 列表頁
│   │   │   └── [id]/
│   │   │       └── page.tsx   # 詳情頁
│   │   └── layout.tsx         # Dashboard layout
│   ├── layout.tsx             # Root layout
│   └── page.tsx               # 首頁
│
├── components/                # 共用組件
│   ├── ui/                    # shadcn/ui 組件
│   │   ├── button.tsx
│   │   ├── card.tsx
│   │   └── ...
│   ├── layouts/               # Layout 組件
│   │   ├── Header.tsx
│   │   ├── Sidebar.tsx
│   │   └── Footer.tsx
│   └── features/              # Feature 專屬組件
│       ├── salesperson/
│       │   ├── SalespersonCard.tsx
│       │   ├── SalespersonForm.tsx
│       │   └── SalespersonList.tsx
│       └── auth/
│           ├── LoginForm.tsx
│           └── RegisterForm.tsx
│
├── lib/                       # 工具與配置
│   ├── api/                   # API Client
│   │   ├── client.ts          # Axios instance
│   │   ├── salesperson.ts     # Salesperson API
│   │   └── auth.ts            # Auth API
│   ├── hooks/                 # Custom Hooks
│   │   ├── useSalesperson.ts  # React Query hooks
│   │   └── useAuth.ts
│   ├── stores/                # Zustand stores
│   │   └── authStore.ts
│   ├── types/                 # TypeScript types
│   │   └── salesperson.ts
│   └── utils/                 # Utility functions
│       └── format.ts
│
├── public/                    # 靜態資源
└── styles/                    # 全域樣式
    └── globals.css
```

## 實例代碼

### Page Component (Server Component)

**檔案**: `app/(dashboard)/salespersons/page.tsx`

```tsx
import { Suspense } from 'react';
import { SalespersonList } from '@/components/features/salesperson/SalespersonList';
import { SalespersonListSkeleton } from '@/components/features/salesperson/SalespersonListSkeleton';

export default function SalespersonsPage() {
  return (
    <div className="container mx-auto py-6">
      <h1 className="text-3xl font-bold mb-6">業務員列表</h1>
      
      <Suspense fallback={<SalespersonListSkeleton />}>
        <SalespersonList />
      </Suspense>
    </div>
  );
}
```

### Client Component (Interactive)

**檔案**: `components/features/salesperson/SalespersonCard.tsx`

```tsx
'use client';

import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useFavoriteSalesperson } from '@/lib/hooks/useSalesperson';
import { Salesperson } from '@/lib/types/salesperson';
import { Heart } from 'lucide-react';

interface SalespersonCardProps {
  salesperson: Salesperson;
}

export function SalespersonCard({ salesperson }: SalespersonCardProps) {
  const { mutate: favorite, isPending } = useFavoriteSalesperson();
  
  const handleFavorite = () => {
    favorite(salesperson.id);
  };
  
  return (
    <Card className="hover:shadow-lg transition-shadow">
      <CardHeader>
        <div className="flex items-center justify-between">
          <h3 className="text-xl font-semibold">{salesperson.user.name}</h3>
          <Button
            variant="ghost"
            size="icon"
            onClick={handleFavorite}
            disabled={isPending}
          >
            <Heart className="h-5 w-5" />
          </Button>
        </div>
      </CardHeader>
      
      <CardContent>
        <p className="text-sm text-muted-foreground">
          {salesperson.position}
        </p>
        <p className="text-sm mt-2">
          {salesperson.company.name}
        </p>
      </CardContent>
    </Card>
  );
}
```

### API Client

**檔案**: `lib/api/client.ts`

```typescript
import axios from 'axios';
import { getAccessToken, refreshAccessToken } from '@/lib/stores/authStore';

const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor: 加入 JWT token
apiClient.interceptors.request.use((config) => {
  const token = getAccessToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor: 處理 401 自動刷新 token
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;
      
      try {
        await refreshAccessToken();
        return apiClient(originalRequest);
      } catch (refreshError) {
        // Token 刷新失敗，導向登入頁
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }
    
    return Promise.reject(error);
  }
);

export default apiClient;
```

**檔案**: `lib/api/salesperson.ts`

```typescript
import apiClient from './client';
import { Salesperson, CreateSalespersonDto } from '@/lib/types/salesperson';

export const salespersonApi = {
  getAll: async (): Promise<Salesperson[]> => {
    const { data } = await apiClient.get('/salespersons');
    return data.data;
  },
  
  getById: async (id: number): Promise<Salesperson> => {
    const { data } = await apiClient.get(`/salespersons/${id}`);
    return data.data;
  },
  
  create: async (dto: CreateSalespersonDto): Promise<Salesperson> => {
    const { data } = await apiClient.post('/salespersons', dto);
    return data.data;
  },
  
  update: async (id: number, dto: Partial<CreateSalespersonDto>): Promise<Salesperson> => {
    const { data } = await apiClient.patch(`/salespersons/${id}`, dto);
    return data.data;
  },
  
  delete: async (id: number): Promise<void> => {
    await apiClient.delete(`/salespersons/${id}`);
  },
  
  favorite: async (id: number): Promise<void> => {
    await apiClient.post(`/salespersons/${id}/favorite`);
  },
};
```

### React Query Hook

**檔案**: `lib/hooks/useSalesperson.ts`

```typescript
'use client';

import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { salespersonApi } from '@/lib/api/salesperson';
import { CreateSalespersonDto } from '@/lib/types/salesperson';
import { toast } from 'sonner';

export function useSalespersons() {
  return useQuery({
    queryKey: ['salespersons'],
    queryFn: salespersonApi.getAll,
  });
}

export function useSalesperson(id: number) {
  return useQuery({
    queryKey: ['salesperson', id],
    queryFn: () => salespersonApi.getById(id),
    enabled: !!id,
  });
}

export function useCreateSalesperson() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (dto: CreateSalespersonDto) => salespersonApi.create(dto),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['salespersons'] });
      toast.success('業務員建立成功');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.detail || '建立失敗');
    },
  });
}

export function useFavoriteSalesperson() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: (id: number) => salespersonApi.favorite(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['salespersons'] });
      toast.success('已加入收藏');
    },
  });
}
```

### TypeScript Types

**檔案**: `lib/types/salesperson.ts`

```typescript
export interface User {
  id: number;
  name: string;
  email: string;
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
  description: string | null;
  photo_url: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateSalespersonDto {
  user_id: number;
  company_id: number;
  position: string;
  description?: string;
  photo?: string; // Base64
}
```

## 常見錯誤

### 錯誤 1: 在 Server Component 使用客戶端功能

**錯誤示範**:
```tsx
// ❌ Server Component 不能使用 useState, useEffect
export default function Page() {
  const [count, setCount] = useState(0); // 錯誤！
  
  return <div>{count}</div>;
}
```

**正確做法**:
```tsx
// ✅ 使用 'use client' directive
'use client';

export default function Page() {
  const [count, setCount] = useState(0);
  
  return <div>{count}</div>;
}
```

### 錯誤 2: 不使用 TypeScript 類型

**錯誤示範**:
```tsx
// ❌ 沒有類型定義
function SalespersonCard({ salesperson }) {
  return <div>{salesperson.user.name}</div>;
}
```

**正確做法**:
```tsx
// ✅ 完整的類型定義
interface SalespersonCardProps {
  salesperson: Salesperson;
}

function SalespersonCard({ salesperson }: SalespersonCardProps) {
  return <div>{salesperson.user.name}</div>;
}
```

### 錯誤 3: API 錯誤處理不完整

**錯誤示範**:
```typescript
// ❌ 沒有錯誤處理
const data = await apiClient.get('/salespersons');
```

**正確做法**:
```typescript
// ✅ 完整的錯誤處理
try {
  const { data } = await apiClient.get('/salespersons');
  return data.data;
} catch (error) {
  console.error('Failed to fetch salespersons:', error);
  throw error;
}
```

## 最佳實踐

### 實作檢查清單

組件開發:
- [ ] 使用 TypeScript 嚴格模式
- [ ] 區分 Server 和 Client Components
- [ ] 組件有完整的 Props 類型定義
- [ ] 使用 shadcn/ui 統一樣式
- [ ] Tailwind CSS 類名使用語義化

API 整合:
- [ ] 使用 React Query 管理資料
- [ ] API Client 統一錯誤處理
- [ ] 實作 Loading 和 Error 狀態
- [ ] 使用 Suspense 處理非同步

### 注意事項

**Server vs Client Components**:
- 預設使用 Server Components
- 需要互動性時才使用 Client Components
- Client Components 使用 `'use client'` directive

**效能優化**:
- 使用 Next.js Image 組件
- 實作 Code Splitting
- 使用 React Query 快取

**可訪問性**:
- 使用語義化 HTML
- 適當的 ARIA 屬性
- 鍵盤導航支援

## 相關知識

### 前置知識
- [SDD 流程](../workflow/sdd-process.md) - 開發流程
- React 19 新特性
- Next.js 15 App Router
- TypeScript 基礎

### 延伸閱讀
- [組件模式](./component-patterns.md) - React 組件設計
- [狀態管理](./state-management.md) - React Query + Zustand
- [API 整合](./api-integration.md) - API Client 詳解
- [測試策略](./testing.md) - 前端測試

### 實作流程
1. [本文件] - 理解架構
2. [組件模式](./component-patterns.md) - 設計組件
3. [API 整合](./api-integration.md) - 整合 API
4. [測試策略](./testing.md) - 撰寫測試

## 決策記錄

### 當前決策 (2026-01-13)

**採用 App Router 的原因**:
- 原因 1: Next.js 15 推薦使用 App Router
- 原因 2: Server Components 提升效能
- 原因 3: 更好的 layouts 和 loading 支援
- 原因 4: 更直觀的檔案系統路由

**為什麼使用 Feature-based 結構**:
- 按功能組織，而非技術類型
- 易於擴展和維護
- 團隊協作更清晰

**技術選型**:
- React Query: 強大的資料同步和快取
- Zustand: 輕量級全域狀態管理
- shadcn/ui: 可自訂的組件庫
- Tailwind CSS: 快速樣式開發

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
