# 狀態管理與路由規格 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24
**Developer**: Senior Frontend Engineer

---

## 📋 目錄

- [路由規範](#路由規範)
- [狀態管理策略](#狀態管理策略)
- [導航更新](#導航更新)
- [認證與授權](#認證與授權)
- [URL 狀態管理](#url-狀態管理)

---

## 🗺️ 路由規範

### 路由總覽

```
Analytics Dashboard Routes
├── 業務員路由
│   └── /dashboard/analytics
│       ├── page.tsx
│       ├── error.tsx
│       ├── not-found.tsx
│       └── components/
│
└── 管理員路由
    └── /admin/dashboard/analytics
        ├── page.tsx
        ├── error.tsx
        ├── not-found.tsx
        └── components/
```

### 路由詳細規格

#### 業務員 Dashboard 路由

**路徑**: `/dashboard/analytics`

**檔案結構**:
```
app/
└── (dashboard)/
    └── dashboard/
        └── analytics/
            ├── page.tsx                      # 主頁面
            ├── error.tsx                     # 錯誤處理
            ├── not-found.tsx                 # 404 頁面
            ├── loading.tsx                   # Loading UI
            └── components/
                ├── AnalyticsDashboard.tsx    # 主組件
                └── DashboardSkeleton.tsx     # Skeleton
```

**Layout 配置**:
```typescript
// app/(dashboard)/layout.tsx (已存在)

export default function DashboardLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="flex min-h-screen">
      <Sidebar />  {/* 包含導航連結 */}
      <main className="flex-1">{children}</main>
    </div>
  );
}
```

**Metadata**:
```typescript
// app/(dashboard)/dashboard/analytics/page.tsx

import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: '數據分析 | YAMU',
  description: '查看您的檔案瀏覽數、聯繫請求和趨勢分析',
  robots: {
    index: false,  // 不索引（需登入）
    follow: false,
  },
};
```

#### 管理員 Dashboard 路由

**路徑**: `/admin/dashboard/analytics`

**檔案結構**:
```
app/
└── (admin)/
    └── admin/
        └── dashboard/
            └── analytics/
                ├── page.tsx                      # 主頁面
                ├── error.tsx                     # 錯誤處理
                ├── not-found.tsx                 # 404 頁面
                ├── loading.tsx                   # Loading UI
                └── components/
                    ├── AdminAnalyticsDashboard.tsx  # 主組件
                    └── AdminDashboardSkeleton.tsx   # Skeleton
```

**Layout 配置**:
```typescript
// app/(admin)/layout.tsx (已存在)

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="flex min-h-screen">
      <AdminSidebar />  {/* 管理員導航 */}
      <main className="flex-1 bg-slate-50">{children}</main>
    </div>
  );
}
```

**Metadata**:
```typescript
// app/(admin)/admin/dashboard/analytics/page.tsx

import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: '平台數據分析 | YAMU Admin',
  description: '查看平台整體數據、熱門業務員和成長趨勢',
  robots: {
    index: false,  // 不索引（僅管理員）
    follow: false,
  },
};
```

---

## 🗂️ 狀態管理策略

### 狀態分類

本功能使用 **混合狀態管理策略**：

| 狀態類型 | 管理工具 | 用途 | 持久化 |
|---------|---------|------|--------|
| **Server State** | React Query | API 數據 | ✅ (快取) |
| **Client State** | useState | UI 狀態 | ❌ |
| **URL State** | Next.js Router | 時間範圍 | ✅ (URL) |
| **Global State** | Zustand (可選) | 跨頁面狀態 | ❌ |

### Server State (React Query)

**管理對象**:
- Dashboard 統計數據
- 趨勢圖表數據
- 聯繫列表數據

**配置**:
```typescript
// lib/query/queryClient.ts

import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,      // 5 分鐘
      gcTime: 10 * 60 * 1000,         // 10 分鐘
      refetchOnWindowFocus: true,     // 進入頁面時刷新
      refetchOnReconnect: true,       // 重新連線時刷新
      retry: 3,                       // 失敗重試 3 次
    },
  },
});
```

**使用範例**:
```typescript
const { data, isLoading, error } = useSalespersonStats('7days');
```

### Client State (useState)

**管理對象**:
- 時間範圍選擇 (timeRange)
- Modal 開關狀態
- Loading 狀態（組件層級）

**使用範例**:
```typescript
'use client';

import { useState } from 'react';
import type { TimeRange } from '@/types/dashboard';

export function AnalyticsDashboard() {
  const [timeRange, setTimeRange] = useState<TimeRange>('7days');
  const [isModalOpen, setIsModalOpen] = useState(false);

  return (
    <Tabs value={timeRange} onValueChange={setTimeRange}>
      {/* ... */}
    </Tabs>
  );
}
```

### URL State (Next.js Router)

**管理對象**:
- 時間範圍（可選，透過 URL 參數共享連結）

**實作方式**:
```typescript
'use client';

import { useRouter, useSearchParams } from 'next/navigation';
import { useEffect } from 'react';

export function AnalyticsDashboard() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [timeRange, setTimeRange] = useState<TimeRange>(
    (searchParams.get('range') as TimeRange) || '7days'
  );

  // 更新 URL 參數
  const updateURL = (range: TimeRange) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('range', range);
    router.push(`?${params.toString()}`, { scroll: false });
  };

  const handleRangeChange = (range: TimeRange) => {
    setTimeRange(range);
    updateURL(range);
  };

  return (
    <Tabs value={timeRange} onValueChange={handleRangeChange}>
      {/* ... */}
    </Tabs>
  );
}
```

### Global State (Zustand - 可選)

**使用情境**: 如果需要在多個頁面共享狀態

**範例** (僅在需要時實作):
```typescript
// store/dashboardStore.ts

import { create } from 'zustand';
import type { TimeRange } from '@/types/dashboard';

interface DashboardState {
  timeRange: TimeRange;
  setTimeRange: (range: TimeRange) => void;
}

export const useDashboardStore = create<DashboardState>((set) => ({
  timeRange: '7days',
  setTimeRange: (range) => set({ timeRange: range }),
}));
```

---

## 🧭 導航更新

### 業務員側邊欄

**檔案**: `components/layout/SalespersonNav.tsx` (假設已存在)

**更新內容**: 新增「數據分析」連結

```typescript
import { BarChart3, Home, User, Settings } from 'lucide-react';

export const salespersonNavItems = [
  {
    title: '首頁',
    href: '/dashboard',
    icon: Home,
  },
  {
    title: '我的檔案',
    href: '/dashboard/profile',
    icon: User,
  },
  {
    title: '數據分析',  // 新增
    href: '/dashboard/analytics',
    icon: BarChart3,
  },
  {
    title: '設定',
    href: '/dashboard/settings',
    icon: Settings,
  },
];
```

**UI 呈現**:
```typescript
// components/layout/Sidebar.tsx

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { salespersonNavItems } from './SalespersonNav';
import { cn } from '@/lib/utils';

export function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-64 bg-white border-r border-slate-200">
      <nav className="p-4 space-y-2">
        {salespersonNavItems.map((item) => {
          const Icon = item.icon;
          const isActive = pathname === item.href;

          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                'flex items-center gap-3 px-4 py-2 rounded-lg transition-colors',
                isActive
                  ? 'bg-primary-50 text-primary-600 font-semibold'
                  : 'text-slate-600 hover:bg-slate-50'
              )}
            >
              <Icon className="h-5 w-5" />
              <span>{item.title}</span>
            </Link>
          );
        })}
      </nav>
    </aside>
  );
}
```

### 管理員側邊欄

**檔案**: `components/layout/AdminNav.tsx` (假設已存在)

**更新內容**: 新增「平台數據」連結

```typescript
import { TrendingUp, Users, Building, Settings } from 'lucide-react';

export const adminNavItems = [
  {
    title: '管理首頁',
    href: '/admin/dashboard',
    icon: TrendingUp,
  },
  {
    title: '平台數據',  // 新增
    href: '/admin/dashboard/analytics',
    icon: TrendingUp,
  },
  {
    title: '使用者管理',
    href: '/admin/users',
    icon: Users,
  },
  {
    title: '公司管理',
    href: '/admin/companies',
    icon: Building,
  },
  {
    title: '系統設定',
    href: '/admin/settings',
    icon: Settings,
  },
];
```

---

## 🔐 認證與授權

### Middleware 配置

**檔案**: `middleware.ts` (專案根目錄)

**更新內容**: 確保 Dashboard 路由受保護

```typescript
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { verifyAuth } from '@/lib/auth';

export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // 檢查是否為 Dashboard 路由
  if (pathname.startsWith('/dashboard') || pathname.startsWith('/admin')) {
    // 驗證 JWT Token
    const token = request.cookies.get('access_token')?.value;

    if (!token) {
      // 未登入，重定向到登入頁
      return NextResponse.redirect(new URL('/login', request.url));
    }

    try {
      const user = await verifyAuth(token);

      // 檢查業務員路由權限
      if (pathname.startsWith('/dashboard') && user.role !== 'salesperson') {
        return NextResponse.redirect(new URL('/403', request.url));
      }

      // 檢查管理員路由權限
      if (pathname.startsWith('/admin') && user.role !== 'admin') {
        return NextResponse.redirect(new URL('/403', request.url));
      }

      return NextResponse.next();
    } catch (error) {
      // Token 無效，重定向到登入頁
      return NextResponse.redirect(new URL('/login', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    '/dashboard/:path*',
    '/admin/:path*',
  ],
};
```

### 客戶端認證檢查

**檔案**: `hooks/useAuth.ts` (假設已存在)

**使用方式**:
```typescript
'use client';

import { useAuth } from '@/hooks/useAuth';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';

export function AnalyticsDashboard() {
  const { user, isLoading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!isLoading && !user) {
      router.push('/login');
    }

    if (!isLoading && user && user.role !== 'salesperson') {
      router.push('/403');
    }
  }, [user, isLoading, router]);

  if (isLoading) {
    return <DashboardSkeleton />;
  }

  return (
    // ... Dashboard 內容
  );
}
```

---

## 🔗 URL 狀態管理

### 支援的 URL 參數

#### 業務員 Dashboard

| 參數 | 類型 | 預設值 | 描述 | 範例 |
|------|------|--------|------|------|
| `range` | TimeRange | '7days' | 時間範圍 | `?range=30days` |

**完整 URL 範例**:
```
/dashboard/analytics?range=7days
/dashboard/analytics?range=today
/dashboard/analytics?range=30days
```

#### 管理員 Dashboard

目前**不使用 URL 參數**（管理員通常查看固定時間範圍）

### URL 參數處理

**檔案**: `app/(dashboard)/dashboard/analytics/components/AnalyticsDashboard.tsx`

```typescript
'use client';

import { useSearchParams, useRouter } from 'next/navigation';
import { useState, useEffect } from 'react';
import type { TimeRange } from '@/types/dashboard';

const validRanges: TimeRange[] = ['today', '7days', '30days'];

export function AnalyticsDashboard() {
  const router = useRouter();
  const searchParams = useSearchParams();

  // 從 URL 讀取時間範圍（帶驗證）
  const urlRange = searchParams.get('range');
  const initialRange: TimeRange =
    urlRange && validRanges.includes(urlRange as TimeRange)
      ? (urlRange as TimeRange)
      : '7days';

  const [timeRange, setTimeRange] = useState<TimeRange>(initialRange);

  // 同步 URL 參數
  const updateURL = (range: TimeRange) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set('range', range);
    router.replace(`?${params.toString()}`, { scroll: false });
  };

  const handleRangeChange = (range: TimeRange) => {
    setTimeRange(range);
    updateURL(range);
  };

  return (
    <Tabs value={timeRange} onValueChange={handleRangeChange}>
      {/* ... */}
    </Tabs>
  );
}
```

### 深度連結支援

**功能**: 使用者可以分享帶參數的 URL

**範例**:
```
# 業務員分享過去 30 天的數據
https://yamu.com/dashboard/analytics?range=30days

# 使用者點擊後：
# 1. Middleware 驗證認證
# 2. 頁面載入
# 3. 自動顯示「過去 30 天」Tab
# 4. 載入對應數據
```

---

## 📊 狀態流程圖

```
使用者進入 /dashboard/analytics
    ↓
Middleware 驗證認證與授權
    ↓ (通過)
Next.js 載入頁面
    ↓
讀取 URL 參數 (range)
    ↓
初始化 Client State (timeRange)
    ↓
React Query 載入 Server State
    ↓ (並行)
┌─────────────┬─────────────┬─────────────┐
│ Stats API   │ Trends API  │ Contacts API│
└─────────────┴─────────────┴─────────────┘
    ↓ (成功)
渲染 Dashboard 組件
    ↓
使用者切換 Tab
    ↓
更新 Client State + URL 參數
    ↓
React Query 自動重新 fetch
    ↓
更新 UI
```

---

## ✅ 狀態管理檢查清單

完成狀態管理時，確保：

- [ ] 路由配置正確（業務員 + 管理員）
- [ ] Middleware 認證守衛已配置
- [ ] Server State (React Query) 已整合
- [ ] Client State (useState) 已實作
- [ ] URL 狀態管理已實作（可選）
- [ ] 導航連結已更新（側邊欄）
- [ ] 權限檢查完整（角色驗證）
- [ ] 錯誤處理頁面已建立
- [ ] Loading 狀態已實作
- [ ] 深度連結支援已測試

---

## 🧪 路由測試範例

### 認證守衛測試

```typescript
// __tests__/middleware/auth.test.ts

import { describe, it, expect } from 'vitest';
import { middleware } from '@/middleware';

describe('Middleware - Dashboard Routes', () => {
  it('should redirect to /login if no token', async () => {
    const request = new Request('http://localhost:3000/dashboard/analytics');
    const response = await middleware(request);

    expect(response.status).toBe(307); // Redirect
    expect(response.headers.get('location')).toContain('/login');
  });

  it('should allow salesperson to access /dashboard/analytics', async () => {
    const request = new Request('http://localhost:3000/dashboard/analytics', {
      headers: {
        Cookie: 'access_token=valid-salesperson-token',
      },
    });

    const response = await middleware(request);

    expect(response.status).toBe(200); // Allow
  });

  it('should redirect non-admin to /403 when accessing /admin', async () => {
    const request = new Request('http://localhost:3000/admin/dashboard/analytics', {
      headers: {
        Cookie: 'access_token=valid-salesperson-token',
      },
    });

    const response = await middleware(request);

    expect(response.status).toBe(307); // Redirect
    expect(response.headers.get('location')).toContain('/403');
  });
});
```

---

**Version**: 1.0
**Last Updated**: 2026-01-24
**Total Routes**: 2 (業務員 + 管理員)
**Dependencies**: Next.js 16.1.1, React Query 5.x, Zustand 5.x (可選)
