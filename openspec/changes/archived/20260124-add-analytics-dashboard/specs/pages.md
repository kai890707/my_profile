# 頁面規格 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24
**Designer**: Senior Product Designer

---

## 📋 目錄

- [頁面概覽](#頁面概覽)
- [業務員 Dashboard](#業務員-dashboard)
- [管理員 Dashboard](#管理員-dashboard)
- [共用邏輯](#共用邏輯)
- [SEO 與 Metadata](#seo-與-metadata)

---

## 🎯 頁面概覽

### 頁面架構

```
Analytics Dashboard Pages
├── 業務員 Dashboard
│   └── /dashboard/analytics
│       └── page.tsx
│
└── 管理員 Dashboard
    └── /admin/dashboard/analytics
        └── page.tsx
```

### 頁面特性

| 特性 | 業務員 Dashboard | 管理員 Dashboard |
|------|----------------|----------------|
| **認證** | 需要 salesperson 角色 | 需要 admin 角色 |
| **Layout** | (dashboard)/layout.tsx | (admin)/layout.tsx |
| **響應式** | Mobile-first | Desktop-first |
| **刷新策略** | 進入時刷新 | 進入時刷新 |
| **快取時間** | 5 分鐘 | 5 分鐘 |
| **錯誤處理** | ErrorBoundary | ErrorBoundary |

---

## 👤 業務員 Dashboard

### 路由資訊

- **路徑**: `/dashboard/analytics`
- **檔案**: `app/(dashboard)/dashboard/analytics/page.tsx`
- **Layout**: `app/(dashboard)/layout.tsx`
- **認證**: 需要 `salesperson` 角色 (Middleware 已處理)

### 頁面結構

```
┌────────────────────────────────────────────────────────────┐
│ Header: 數據分析 Dashboard                                  │
├────────────────────────────────────────────────────────────┤
│ Time Range Tabs                                            │
│ [今日] [過去 7 天] [過去 30 天]                              │
├────────────────────────────────────────────────────────────┤
│ KPI Cards (3 個)                                           │
│ ┌────────┐  ┌────────┐  ┌────────┐                        │
│ │總瀏覽數│  │總聯繫數│  │增長率  │                        │
│ └────────┘  └────────┘  └────────┘                        │
├────────────────────────────────────────────────────────────┤
│ Trend Chart Card                                           │
│ ┌────────────────────────────────────────────────────────┐ │
│ │ 趨勢分析                                                │ │
│ │ [瀏覽數與聯繫數雙線圖]                                   │ │
│ └────────────────────────────────────────────────────────┘ │
├────────────────────────────────────────────────────────────┤
│ Contact List Card                                          │
│ ┌────────────────────────────────────────────────────────┐ │
│ │ 最近聯繫記錄 (最新 10 筆)                               │ │
│ └────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### 完整實作

**檔案**: `app/(dashboard)/dashboard/analytics/page.tsx`

```typescript
import { Metadata } from 'next';
import { Suspense } from 'react';
import { AnalyticsDashboard } from './components/AnalyticsDashboard';
import { DashboardSkeleton } from './components/DashboardSkeleton';

export const metadata: Metadata = {
  title: '數據分析 | YAMU',
  description: '查看您的檔案瀏覽數、聯繫請求和趨勢分析',
};

export default function SalespersonAnalyticsPage() {
  return (
    <div className="container mx-auto px-4 py-8">
      {/* Page Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-slate-900 mb-2">
          數據分析 Dashboard
        </h1>
        <p className="text-slate-600">
          追蹤您的檔案表現，了解客戶互動情況
        </p>
      </div>

      {/* Dashboard Content */}
      <Suspense fallback={<DashboardSkeleton />}>
        <AnalyticsDashboard />
      </Suspense>
    </div>
  );
}
```

### Dashboard 組件實作

**檔案**: `app/(dashboard)/dashboard/analytics/components/AnalyticsDashboard.tsx`

```typescript
'use client';

import { useState } from 'react';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { SalespersonStats } from '@/components/dashboard/salesperson/SalespersonStats';
import { DualLineChart } from '@/components/dashboard/charts/DualLineChart';
import { ContactList } from '@/components/dashboard/salesperson/ContactList';
import { ErrorFallback } from '@/components/dashboard/ErrorFallback';
import {
  useSalespersonStats,
  useSalespersonTrends,
  useRecentContacts,
} from '@/hooks/useDashboard';
import type { TimeRange } from '@/types/dashboard';

export function AnalyticsDashboard() {
  const [timeRange, setTimeRange] = useState<TimeRange>('7days');

  // React Query Hooks
  const {
    data: stats,
    isLoading: isLoadingStats,
    error: statsError,
    refetch: refetchStats,
  } = useSalespersonStats(timeRange);

  const {
    data: trends,
    isLoading: isLoadingTrends,
    error: trendsError,
  } = useSalespersonTrends(timeRange);

  const {
    data: contacts,
    isLoading: isLoadingContacts,
    error: contactsError,
  } = useRecentContacts();

  // Error Handling
  if (statsError) {
    return (
      <ErrorFallback
        error={statsError as Error}
        resetErrorBoundary={() => refetchStats()}
      />
    );
  }

  return (
    <div className="space-y-6">
      {/* Time Range Tabs */}
      <Tabs
        value={timeRange}
        onValueChange={(value) => setTimeRange(value as TimeRange)}
        className="w-full"
      >
        <TabsList className="grid w-full max-w-md grid-cols-3">
          <TabsTrigger value="today">今日</TabsTrigger>
          <TabsTrigger value="7days">過去 7 天</TabsTrigger>
          <TabsTrigger value="30days">過去 30 天</TabsTrigger>
        </TabsList>
      </Tabs>

      {/* KPI Stats Cards */}
      {stats && (
        <SalespersonStats stats={stats} isLoading={isLoadingStats} />
      )}

      {/* Trend Chart */}
      <Card>
        <CardHeader>
          <CardTitle>趨勢分析</CardTitle>
        </CardHeader>
        <CardContent>
          {trendsError ? (
            <div className="text-center py-8 text-red-500">
              載入趨勢數據失敗
            </div>
          ) : (
            <DualLineChart
              data={trends || []}
              xAxisKey="date"
              lines={[
                {
                  dataKey: 'profile_views',
                  name: '瀏覽數',
                  color: '#0EA5E9',
                },
                {
                  dataKey: 'contact_requests',
                  name: '聯繫數',
                  color: '#14B8A6',
                },
              ]}
              height={350}
              isLoading={isLoadingTrends}
            />
          )}
        </CardContent>
      </Card>

      {/* Contact List */}
      {contactsError ? (
        <Card>
          <CardContent className="py-8 text-center text-red-500">
            載入聯繫記錄失敗
          </CardContent>
        </Card>
      ) : (
        <ContactList
          contacts={contacts || []}
          onContactClick={(id) => {
            // TODO: 開啟聯繫詳情 Modal
            console.log('Contact clicked:', id);
          }}
          isLoading={isLoadingContacts}
        />
      )}
    </div>
  );
}
```

### Loading Skeleton

**檔案**: `app/(dashboard)/dashboard/analytics/components/DashboardSkeleton.tsx`

```typescript
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

export function DashboardSkeleton() {
  return (
    <div className="space-y-6">
      {/* Tabs Skeleton */}
      <Skeleton className="h-10 w-full max-w-md" />

      {/* KPI Cards Skeleton */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {[...Array(3)].map((_, i) => (
          <Card key={i}>
            <CardContent className="p-6">
              <Skeleton className="h-5 w-24 mb-4" />
              <Skeleton className="h-10 w-32 mb-2" />
              <Skeleton className="h-4 w-20" />
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Chart Skeleton */}
      <Card>
        <CardContent className="p-6">
          <Skeleton className="h-[350px] w-full" />
        </CardContent>
      </Card>

      {/* Contact List Skeleton */}
      <Card>
        <CardContent className="p-6">
          <Skeleton className="h-64 w-full" />
        </CardContent>
      </Card>
    </div>
  );
}
```

---

## 👨‍💼 管理員 Dashboard

### 路由資訊

- **路徑**: `/admin/dashboard/analytics`
- **檔案**: `app/(admin)/admin/dashboard/analytics/page.tsx`
- **Layout**: `app/(admin)/layout.tsx`
- **認證**: 需要 `admin` 角色 (Middleware 已處理)

### 頁面結構

```
┌────────────────────────────────────────────────────────────┐
│ Header: 平台數據分析 Dashboard                              │
├────────────────────────────────────────────────────────────┤
│ KPI Cards Grid (4 個)                                      │
│ ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                    │
│ │總業務員│  │總瀏覽數│  │總聯繫數│  │轉換率 │                │
│ └──────┘  └──────┘  └──────┘  └──────┘                    │
├────────────────────────────────────────────────────────────┤
│ Two Column Layout                                          │
│ ┌──────────────────┐  ┌──────────────────┐                │
│ │ 熱門業務員 Top 10 │  │ 業務員活躍度      │                │
│ │                  │  │                  │                │
│ └──────────────────┘  └──────────────────┘                │
├────────────────────────────────────────────────────────────┤
│ Growth Trends Chart Card                                   │
│ ┌────────────────────────────────────────────────────────┐ │
│ │ 平台成長趨勢 (過去 30 天)                               │ │
│ └────────────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────────┘
```

### 完整實作

**檔案**: `app/(admin)/admin/dashboard/analytics/page.tsx`

```typescript
import { Metadata } from 'next';
import { Suspense } from 'react';
import { AdminAnalyticsDashboard } from './components/AdminAnalyticsDashboard';
import { AdminDashboardSkeleton } from './components/AdminDashboardSkeleton';

export const metadata: Metadata = {
  title: '平台數據分析 | YAMU Admin',
  description: '查看平台整體數據、熱門業務員和成長趨勢',
};

export default function AdminAnalyticsPage() {
  return (
    <div className="container mx-auto px-4 py-8">
      {/* Page Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-slate-900 mb-2">
          平台數據分析 Dashboard
        </h1>
        <p className="text-slate-600">
          全面掌握平台健康度，數據驅動營運決策
        </p>
      </div>

      {/* Dashboard Content */}
      <Suspense fallback={<AdminDashboardSkeleton />}>
        <AdminAnalyticsDashboard />
      </Suspense>
    </div>
  );
}
```

### Admin Dashboard 組件實作

**檔案**: `app/(admin)/admin/dashboard/analytics/components/AdminAnalyticsDashboard.tsx`

```typescript
'use client';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { StatCard, formatters } from '@/components/dashboard/StatCard';
import { TopSalespersons } from '@/components/dashboard/admin/TopSalespersons';
import { ActivityCard } from '@/components/dashboard/admin/ActivityCard';
import { DualLineChart } from '@/components/dashboard/charts/DualLineChart';
import { ErrorFallback } from '@/components/dashboard/ErrorFallback';
import {
  useAdminOverview,
  useTopSalespersons,
  useAdminActivity,
  useAdminTrends,
} from '@/hooks/useDashboard';
import { Users, Eye, Phone, TrendingUp } from 'lucide-react';
import { useRouter } from 'next/navigation';

export function AdminAnalyticsDashboard() {
  const router = useRouter();

  // React Query Hooks
  const {
    data: overview,
    isLoading: isLoadingOverview,
    error: overviewError,
    refetch: refetchOverview,
  } = useAdminOverview();

  const {
    data: topSalespersons,
    isLoading: isLoadingTop,
    error: topError,
  } = useTopSalespersons();

  const {
    data: activity,
    isLoading: isLoadingActivity,
    error: activityError,
  } = useAdminActivity();

  const {
    data: trends,
    isLoading: isLoadingTrends,
    error: trendsError,
  } = useAdminTrends();

  // Error Handling
  if (overviewError) {
    return (
      <ErrorFallback
        error={overviewError as Error}
        resetErrorBoundary={() => refetchOverview()}
      />
    );
  }

  return (
    <div className="space-y-6">
      {/* KPI Cards Grid (4 cards) */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {/* 總業務員數 */}
        <StatCard
          title="總業務員數"
          value={overview?.total_salespersons || 0}
          icon={<Users className="h-5 w-5" />}
          variant="primary"
          isLoading={isLoadingOverview}
          formatValue={formatters.number}
        />

        {/* 總瀏覽數 */}
        <StatCard
          title="總瀏覽數"
          value={overview?.total_profile_views || 0}
          icon={<Eye className="h-5 w-5" />}
          variant="success"
          isLoading={isLoadingOverview}
          formatValue={formatters.number}
        />

        {/* 總聯繫數 */}
        <StatCard
          title="總聯繫數"
          value={overview?.total_contact_requests || 0}
          icon={<Phone className="h-5 w-5" />}
          variant="warning"
          isLoading={isLoadingOverview}
          formatValue={formatters.number}
        />

        {/* 平台轉換率 */}
        <StatCard
          title="平台轉換率"
          value={`${(overview?.platform_conversion_rate || 0).toFixed(1)}%`}
          icon={<TrendingUp className="h-5 w-5" />}
          variant="purple"
          isLoading={isLoadingOverview}
        />
      </div>

      {/* Two Column Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Salespersons */}
        {topError ? (
          <Card>
            <CardContent className="py-8 text-center text-red-500">
              載入熱門業務員失敗
            </CardContent>
          </Card>
        ) : (
          <TopSalespersons
            salespersons={topSalespersons || []}
            onSalespersonClick={(id) => {
              router.push(`/salesperson/${id}`);
            }}
            isLoading={isLoadingTop}
          />
        )}

        {/* Activity Card */}
        {activityError ? (
          <Card>
            <CardContent className="py-8 text-center text-red-500">
              載入活躍度數據失敗
            </CardContent>
          </Card>
        ) : (
          <ActivityCard
            activity={activity || {
              active_salespersons: 0,
              inactive_salespersons: 0,
              total_salespersons: 0,
              activity_rate: 0,
            }}
            isLoading={isLoadingActivity}
          />
        )}
      </div>

      {/* Growth Trends Chart */}
      <Card>
        <CardHeader>
          <CardTitle>平台成長趨勢</CardTitle>
        </CardHeader>
        <CardContent>
          {trendsError ? (
            <div className="text-center py-8 text-red-500">
              載入趨勢數據失敗
            </div>
          ) : (
            <DualLineChart
              data={trends || []}
              xAxisKey="date"
              lines={[
                {
                  dataKey: 'total_profile_views',
                  name: '總瀏覽數',
                  color: '#0EA5E9',
                },
                {
                  dataKey: 'total_contact_requests',
                  name: '總聯繫數',
                  color: '#14B8A6',
                },
              ]}
              height={400}
              isLoading={isLoadingTrends}
            />
          )}
        </CardContent>
      </Card>
    </div>
  );
}
```

### Admin Loading Skeleton

**檔案**: `app/(admin)/admin/dashboard/analytics/components/AdminDashboardSkeleton.tsx`

```typescript
import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';

export function AdminDashboardSkeleton() {
  return (
    <div className="space-y-6">
      {/* KPI Cards Skeleton (4 cards) */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {[...Array(4)].map((_, i) => (
          <Card key={i}>
            <CardContent className="p-6">
              <Skeleton className="h-5 w-24 mb-4" />
              <Skeleton className="h-10 w-32 mb-2" />
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Two Column Skeleton */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardContent className="p-6">
            <Skeleton className="h-64 w-full" />
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-6">
            <Skeleton className="h-64 w-full" />
          </CardContent>
        </Card>
      </div>

      {/* Chart Skeleton */}
      <Card>
        <CardContent className="p-6">
          <Skeleton className="h-[400px] w-full" />
        </CardContent>
      </Card>
    </div>
  );
}
```

---

## 🔄 共用邏輯

### Error Boundary Wrapper

**檔案**: `app/(dashboard)/dashboard/analytics/error.tsx` 和 `app/(admin)/admin/dashboard/analytics/error.tsx`

```typescript
'use client';

import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { AlertCircle, RefreshCw } from 'lucide-react';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // Log error to error reporting service
    console.error('Analytics Dashboard Error:', error);
  }, [error]);

  return (
    <div className="container mx-auto px-4 py-8">
      <Card className="border-red-200 max-w-2xl mx-auto">
        <CardContent className="p-8">
          <div className="flex items-center gap-3 mb-4">
            <AlertCircle className="h-8 w-8 text-red-500" />
            <h2 className="text-2xl font-bold text-red-900">
              Dashboard 載入失敗
            </h2>
          </div>
          <p className="text-red-700 mb-6">
            {error.message || '發生未知錯誤，請稍後再試'}
          </p>
          <div className="flex gap-3">
            <Button variant="outline" onClick={reset}>
              <RefreshCw className="h-4 w-4 mr-2" />
              重新載入
            </Button>
            <Button
              variant="ghost"
              onClick={() => window.location.href = '/dashboard'}
            >
              返回首頁
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
```

### Not Found 頁面

**檔案**: `app/(dashboard)/dashboard/analytics/not-found.tsx`

```typescript
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { FileQuestion } from 'lucide-react';

export default function NotFound() {
  return (
    <div className="container mx-auto px-4 py-16 text-center">
      <FileQuestion className="h-16 w-16 text-slate-300 mx-auto mb-4" />
      <h2 className="text-2xl font-bold text-slate-900 mb-2">
        找不到此頁面
      </h2>
      <p className="text-slate-600 mb-6">
        您訪問的頁面不存在或已被移除
      </p>
      <Button asChild>
        <Link href="/dashboard">返回 Dashboard</Link>
      </Button>
    </div>
  );
}
```

---

## 📱 SEO 與 Metadata

### 業務員頁面 Metadata

```typescript
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: '數據分析 | YAMU',
  description: '查看您的檔案瀏覽數、聯繫請求和趨勢分析',
  openGraph: {
    title: '數據分析 Dashboard - YAMU',
    description: '追蹤您的業務員檔案表現',
    type: 'website',
  },
  robots: {
    index: false,  // 不索引（需登入）
    follow: false,
  },
};
```

### 管理員頁面 Metadata

```typescript
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: '平台數據分析 | YAMU Admin',
  description: '查看平台整體數據、熱門業務員和成長趨勢',
  openGraph: {
    title: '平台數據分析 Dashboard - YAMU Admin',
    description: '全面掌握平台健康度',
    type: 'website',
  },
  robots: {
    index: false,  // 不索引（僅管理員）
    follow: false,
  },
};
```

---

## ✅ 頁面檢查清單

開發每個頁面時，確保：

- [ ] Metadata 已正確設置
- [ ] Suspense + Loading Skeleton 已實作
- [ ] Error Boundary 已配置
- [ ] Not Found 頁面已建立
- [ ] React Query Hooks 已整合
- [ ] 響應式設計測試通過
- [ ] 認證守衛測試通過 (Middleware)
- [ ] 頁面載入效能 < 2 秒
- [ ] TypeScript 嚴格模式無錯誤
- [ ] ESLint 無警告

---

**Version**: 1.0
**Last Updated**: 2026-01-24
**Total Pages**: 2 (業務員 + 管理員)
**Dependencies**: Next.js 16.1.1, React 19, React Query 5.x
