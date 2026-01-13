---
category: frontend
tags: [state, react-query, zustand, react]
priority: medium
last_updated: 2026-01-13
applies_to: React Query 5.x, Zustand 4.x
related_docs: [architecture.md, api-integration.md]
---

# 狀態管理

## Quick Reference

- Server State: React Query
- Client State: Zustand
- Form State: React Hook Form
- URL State: Next.js searchParams
- 避免: Redux (專案不使用)

## 使用場景

**適用於**:
- API 資料管理（React Query）
- 全域狀態（Zustand）
- 表單狀態（React Hook Form）

## 核心概念

YAMU 使用雙狀態管理策略：React Query 處理伺服器狀態，Zustand 處理客戶端狀態。

## React Query (伺服器狀態)

```tsx
// lib/hooks/useSalesperson.ts
export function useSalespersons() {
  return useQuery({
    queryKey: ['salespersons'],
    queryFn: salespersonApi.getAll,
    staleTime: 5 * 60 * 1000, // 5 分鐘
  });
}

export function useCreateSalesperson() {
  const queryClient = useQueryClient();
  
  return useMutation({
    mutationFn: salespersonApi.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['salespersons'] });
    },
  });
}
```

## Zustand (客戶端狀態)

```typescript
// lib/stores/authStore.ts
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface AuthState {
  accessToken: string | null;
  refreshToken: string | null;
  user: User | null;
  setTokens: (access: string, refresh: string) => void;
  setUser: (user: User) => void;
  logout: () => void;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      accessToken: null,
      refreshToken: null,
      user: null,
      setTokens: (access, refresh) => 
        set({ accessToken: access, refreshToken: refresh }),
      setUser: (user) => set({ user }),
      logout: () => set({ accessToken: null, refreshToken: null, user: null }),
    }),
    {
      name: 'auth-storage',
    }
  )
);
```

## React Hook Form (表單狀態)

```tsx
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';

const schema = z.object({
  position: z.string().min(1).max(100),
  description: z.string().max(500).optional(),
});

type FormData = z.infer<typeof schema>;

export function SalespersonForm() {
  const { register, handleSubmit, formState: { errors } } = useForm<FormData>({
    resolver: zodResolver(schema),
  });
  
  const onSubmit = (data: FormData) => {
    console.log(data);
  };
  
  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <input {...register('position')} />
      {errors.position && <span>{errors.position.message}</span>}
    </form>
  );
}
```

## 最佳實踐

- [ ] 伺服器資料使用 React Query
- [ ] 全域狀態使用 Zustand
- [ ] 表單使用 React Hook Form
- [ ] 避免過度使用全域狀態
- [ ] 使用 TypeScript 定義狀態類型

## 相關知識

- [架構模式](./architecture.md) - 狀態在架構中的位置
- [API 整合](./api-integration.md) - React Query 詳解

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
