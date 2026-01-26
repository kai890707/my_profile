# 組件規格 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24
**Designer**: Senior Product Designer

---

## 📋 目錄

- [組件概覽](#組件概覽)
- [通用組件](#通用組件)
- [業務員專用組件](#業務員專用組件)
- [管理員專用組件](#管理員專用組件)
- [圖表組件](#圖表組件)
- [組件組合策略](#組件組合策略)

---

## 🎯 組件概覽

### 組件架構

```
Analytics Dashboard Components
├── 通用組件 (Shared)
│   ├── StatCard          # 統計卡片
│   ├── TrendBadge        # 趨勢徽章
│   ├── EmptyState        # 空狀態
│   └── ErrorFallback     # 錯誤回退
│
├── 圖表組件 (Charts)
│   ├── LineChart         # 折線圖
│   ├── DualLineChart     # 雙軸折線圖
│   └── ChartTooltip      # 圖表提示框
│
├── 業務員組件 (Salesperson)
│   ├── SalespersonStats  # 業務員統計
│   ├── TrendChart        # 趨勢圖表
│   └── ContactList       # 聯繫列表
│
└── 管理員組件 (Admin)
    ├── PlatformOverview  # 平台概覽
    ├── TopSalespersons   # 熱門業務員
    ├── ActivityCard      # 活躍度卡片
    └── GrowthTrends      # 成長趨勢
```

### 設計原則

1. **可複用性** - 組件應該通用，避免業務邏輯
2. **可組合性** - 大組件由小組件組合而成
3. **類型安全** - 完整的 TypeScript 類型定義
4. **狀態處理** - Loading / Empty / Error 三態完整

---

## 📦 通用組件

### 1. StatCard (統計卡片)

**用途**: 顯示單一統計指標的卡片組件

**檔案**: `components/dashboard/StatCard.tsx`

**Props 定義**:
```typescript
interface StatCardProps {
  title: string;
  value: number | string;
  icon: React.ReactNode;
  trend?: {
    value: number;        // 趨勢百分比 (正數為上升，負數為下降)
    isPositive: boolean;  // 是否為正向趨勢
  };
  variant?: 'primary' | 'success' | 'warning' | 'purple';
  isLoading?: boolean;
  formatValue?: (value: number) => string;  // 自定義格式化函數
}
```

**完整實作**:
```typescript
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { TrendingUp, TrendingDown } from 'lucide-react';
import { cn } from '@/lib/utils';

export interface StatCardProps {
  title: string;
  value: number | string;
  icon: React.ReactNode;
  trend?: {
    value: number;
    isPositive: boolean;
  };
  variant?: 'primary' | 'success' | 'warning' | 'purple';
  isLoading?: boolean;
  formatValue?: (value: number) => string;
}

const variantStyles = {
  primary: 'bg-sky-50 text-sky-600',
  success: 'bg-teal-50 text-teal-600',
  warning: 'bg-amber-50 text-amber-600',
  purple: 'bg-purple-50 text-purple-600',
};

export function StatCard({
  title,
  value,
  icon,
  trend,
  variant = 'primary',
  isLoading,
  formatValue,
}: StatCardProps) {
  if (isLoading) {
    return (
      <Card>
        <CardContent className="p-6">
          <Skeleton className="h-5 w-24 mb-4" />
          <Skeleton className="h-10 w-32 mb-2" />
          <Skeleton className="h-4 w-20" />
        </CardContent>
      </Card>
    );
  }

  const formattedValue =
    typeof value === 'number' && formatValue ? formatValue(value) : value;

  return (
    <Card className="transition-all hover:shadow-lg">
      <CardContent className="p-6">
        {/* Header */}
        <div className="flex items-center justify-between mb-4">
          <span className="text-sm font-medium text-slate-600">{title}</span>
          <div className={cn('p-2 rounded-lg', variantStyles[variant])}>
            {icon}
          </div>
        </div>

        {/* Value */}
        <div className="mb-2">
          <span className="text-3xl font-bold text-slate-900">
            {formattedValue}
          </span>
        </div>

        {/* Trend Badge */}
        {trend && (
          <Badge
            variant={trend.isPositive ? 'success' : 'destructive'}
            className="flex items-center gap-1 w-fit"
          >
            {trend.isPositive ? (
              <TrendingUp className="h-3 w-3" />
            ) : (
              <TrendingDown className="h-3 w-3" />
            )}
            <span>
              {trend.isPositive ? '+' : ''}
              {trend.value.toFixed(1)}%
            </span>
          </Badge>
        )}
      </CardContent>
    </Card>
  );
}

// 數值格式化工具函數
export const formatters = {
  number: (value: number) => value.toLocaleString('zh-TW'),
  percentage: (value: number) => `${value.toFixed(1)}%`,
  currency: (value: number) => `NT$ ${value.toLocaleString('zh-TW')}`,
};
```

**使用範例**:
```typescript
<StatCard
  title="總瀏覽數"
  value={1234}
  icon={<Eye className="h-5 w-5" />}
  trend={{ value: 15.3, isPositive: true }}
  variant="primary"
  formatValue={formatters.number}
/>
```

---

### 2. TrendBadge (趨勢徽章)

**用途**: 顯示增長/下降趨勢的徽章組件

**檔案**: `components/dashboard/TrendBadge.tsx`

**Props 定義**:
```typescript
interface TrendBadgeProps {
  value: number;          // 趨勢百分比
  isPositive: boolean;    // 是否為正向趨勢
  size?: 'sm' | 'md' | 'lg';
}
```

**完整實作**:
```typescript
import { Badge } from '@/components/ui/badge';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';
import { cn } from '@/lib/utils';

export interface TrendBadgeProps {
  value: number;
  isPositive: boolean;
  size?: 'sm' | 'md' | 'lg';
}

const sizeStyles = {
  sm: 'text-xs px-2 py-0.5',
  md: 'text-sm px-3 py-1',
  lg: 'text-base px-4 py-1.5',
};

export function TrendBadge({ value, isPositive, size = 'md' }: TrendBadgeProps) {
  const isZero = value === 0;

  return (
    <Badge
      variant={isZero ? 'secondary' : isPositive ? 'success' : 'destructive'}
      className={cn('flex items-center gap-1 w-fit', sizeStyles[size])}
    >
      {isZero ? (
        <Minus className="h-3 w-3" />
      ) : isPositive ? (
        <TrendingUp className="h-3 w-3" />
      ) : (
        <TrendingDown className="h-3 w-3" />
      )}
      <span>
        {isPositive && value > 0 ? '+' : ''}
        {value.toFixed(1)}%
      </span>
    </Badge>
  );
}
```

---

### 3. EmptyState (空狀態)

**用途**: 當無數據時顯示的友善提示組件

**檔案**: `components/dashboard/EmptyState.tsx`

**Props 定義**:
```typescript
interface EmptyStateProps {
  icon?: React.ReactNode;
  title: string;
  description: string;
  action?: {
    label: string;
    onClick: () => void;
  };
}
```

**完整實作**:
```typescript
import { Button } from '@/components/ui/button';
import { BarChart3 } from 'lucide-react';

export interface EmptyStateProps {
  icon?: React.ReactNode;
  title: string;
  description: string;
  action?: {
    label: string;
    onClick: () => void;
  };
}

export function EmptyState({
  icon = <BarChart3 className="h-12 w-12 text-slate-300" />,
  title,
  description,
  action,
}: EmptyStateProps) {
  return (
    <div className="flex flex-col items-center justify-center py-12 px-4">
      <div className="mb-4">{icon}</div>
      <h3 className="text-lg font-semibold text-slate-900 mb-2">{title}</h3>
      <p className="text-sm text-slate-500 text-center max-w-md mb-6">
        {description}
      </p>
      {action && (
        <Button variant="outline" onClick={action.onClick}>
          {action.label}
        </Button>
      )}
    </div>
  );
}
```

**使用範例**:
```typescript
<EmptyState
  title="尚無瀏覽記錄"
  description="您的檔案還沒有被瀏覽。分享您的檔案連結以獲得更多曝光！"
  action={{
    label: '分享檔案',
    onClick: () => handleShare(),
  }}
/>
```

---

### 4. ErrorFallback (錯誤回退)

**用途**: 當發生錯誤時顯示的錯誤處理組件

**檔案**: `components/dashboard/ErrorFallback.tsx`

**Props 定義**:
```typescript
interface ErrorFallbackProps {
  error: Error;
  resetErrorBoundary: () => void;
}
```

**完整實作**:
```typescript
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { AlertCircle, RefreshCw } from 'lucide-react';

export interface ErrorFallbackProps {
  error: Error;
  resetErrorBoundary: () => void;
}

export function ErrorFallback({ error, resetErrorBoundary }: ErrorFallbackProps) {
  return (
    <Card className="border-red-200">
      <CardContent className="p-6">
        <div className="flex items-center gap-3 mb-4">
          <AlertCircle className="h-6 w-6 text-red-500" />
          <h3 className="text-lg font-semibold text-red-900">載入失敗</h3>
        </div>
        <p className="text-sm text-red-700 mb-4">
          {error.message || '發生未知錯誤，請稍後再試'}
        </p>
        <Button variant="outline" onClick={resetErrorBoundary}>
          <RefreshCw className="h-4 w-4 mr-2" />
          重新載入
        </Button>
      </CardContent>
    </Card>
  );
}
```

---

## 📊 圖表組件

### 5. LineChart (折線圖)

**用途**: 顯示趨勢數據的折線圖組件

**檔案**: `components/dashboard/charts/LineChart.tsx`

**Props 定義**:
```typescript
interface LineChartProps {
  data: Array<{ date: string; value: number }>;
  xAxisKey: string;
  yAxisKey: string;
  lineColor?: string;
  height?: number;
  showGrid?: boolean;
  showTooltip?: boolean;
  isLoading?: boolean;
}
```

**完整實作**:
```typescript
import React from 'react';
import {
  LineChart as RechartsLineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
} from 'recharts';
import { Skeleton } from '@/components/ui/skeleton';

export interface LineChartProps {
  data: Array<Record<string, any>>;
  xAxisKey: string;
  yAxisKey: string;
  lineColor?: string;
  height?: number;
  showGrid?: boolean;
  showTooltip?: boolean;
  isLoading?: boolean;
}

export function LineChart({
  data,
  xAxisKey,
  yAxisKey,
  lineColor = '#0EA5E9',
  height = 300,
  showGrid = true,
  showTooltip = true,
  isLoading,
}: LineChartProps) {
  if (isLoading) {
    return <Skeleton className={`w-full h-[${height}px]`} />;
  }

  if (!data || data.length === 0) {
    return (
      <div className="flex items-center justify-center h-[${height}px] text-slate-400">
        暫無數據
      </div>
    );
  }

  return (
    <ResponsiveContainer width="100%" height={height}>
      <RechartsLineChart
        data={data}
        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
      >
        {showGrid && <CartesianGrid strokeDasharray="3 3" stroke="#E2E8F0" />}
        <XAxis
          dataKey={xAxisKey}
          stroke="#64748B"
          fontSize={12}
          tickLine={false}
          axisLine={false}
        />
        <YAxis
          stroke="#64748B"
          fontSize={12}
          tickLine={false}
          axisLine={false}
          tickFormatter={(value) => value.toLocaleString()}
        />
        {showTooltip && (
          <Tooltip
            contentStyle={{
              backgroundColor: '#1E293B',
              border: 'none',
              borderRadius: '8px',
              color: '#F1F5F9',
            }}
            formatter={(value: any) => [value.toLocaleString(), yAxisKey]}
          />
        )}
        <Line
          type="monotone"
          dataKey={yAxisKey}
          stroke={lineColor}
          strokeWidth={2}
          dot={{ fill: lineColor, strokeWidth: 2, r: 4 }}
          activeDot={{ r: 6 }}
        />
      </RechartsLineChart>
    </ResponsiveContainer>
  );
}
```

---

### 6. DualLineChart (雙軸折線圖)

**用途**: 同時顯示兩條數據線的圖表（例如：瀏覽數 + 聯繫數）

**檔案**: `components/dashboard/charts/DualLineChart.tsx`

**Props 定義**:
```typescript
interface DualLineChartProps {
  data: Array<{ date: string; [key: string]: any }>;
  xAxisKey: string;
  lines: Array<{
    dataKey: string;
    name: string;
    color: string;
  }>;
  height?: number;
  isLoading?: boolean;
}
```

**完整實作**:
```typescript
import React from 'react';
import {
  LineChart as RechartsLineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';
import { Skeleton } from '@/components/ui/skeleton';

export interface DualLineChartProps {
  data: Array<Record<string, any>>;
  xAxisKey: string;
  lines: Array<{
    dataKey: string;
    name: string;
    color: string;
  }>;
  height?: number;
  isLoading?: boolean;
}

export function DualLineChart({
  data,
  xAxisKey,
  lines,
  height = 350,
  isLoading,
}: DualLineChartProps) {
  if (isLoading) {
    return <Skeleton className={`w-full h-[${height}px]`} />;
  }

  if (!data || data.length === 0) {
    return (
      <div className="flex items-center justify-center h-[${height}px] text-slate-400">
        暫無數據
      </div>
    );
  }

  return (
    <ResponsiveContainer width="100%" height={height}>
      <RechartsLineChart
        data={data}
        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
      >
        <CartesianGrid strokeDasharray="3 3" stroke="#E2E8F0" />
        <XAxis
          dataKey={xAxisKey}
          stroke="#64748B"
          fontSize={12}
          tickLine={false}
          axisLine={false}
        />
        <YAxis
          stroke="#64748B"
          fontSize={12}
          tickLine={false}
          axisLine={false}
          tickFormatter={(value) => value.toLocaleString()}
        />
        <Tooltip
          contentStyle={{
            backgroundColor: '#1E293B',
            border: 'none',
            borderRadius: '8px',
            color: '#F1F5F9',
          }}
        />
        <Legend
          wrapperStyle={{
            paddingTop: '20px',
          }}
          iconType="line"
        />
        {lines.map((line) => (
          <Line
            key={line.dataKey}
            type="monotone"
            dataKey={line.dataKey}
            name={line.name}
            stroke={line.color}
            strokeWidth={2}
            dot={{ fill: line.color, strokeWidth: 2, r: 4 }}
            activeDot={{ r: 6 }}
          />
        ))}
      </RechartsLineChart>
    </ResponsiveContainer>
  );
}
```

**使用範例**:
```typescript
<DualLineChart
  data={trendsData}
  xAxisKey="date"
  lines={[
    { dataKey: 'views', name: '瀏覽數', color: '#0EA5E9' },
    { dataKey: 'contacts', name: '聯繫數', color: '#14B8A6' },
  ]}
  height={350}
/>
```

---

## 👤 業務員專用組件

### 7. SalespersonStats (業務員統計)

**用途**: 顯示業務員核心統計數據（3 個 KPI 卡片）

**檔案**: `components/dashboard/salesperson/SalespersonStats.tsx`

**Props 定義**:
```typescript
interface SalespersonStatsProps {
  stats: {
    profile_views: number;
    contact_requests: number;
    growth_rate: number;
  };
  isLoading?: boolean;
}
```

**完整實作**:
```typescript
import { Eye, Phone, TrendingUp } from 'lucide-react';
import { StatCard, formatters } from '@/components/dashboard/StatCard';

export interface SalespersonStatsProps {
  stats: {
    profile_views: number;
    contact_requests: number;
    growth_rate: number;
  };
  isLoading?: boolean;
}

export function SalespersonStats({ stats, isLoading }: SalespersonStatsProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {/* 總瀏覽數 */}
      <StatCard
        title="總瀏覽數"
        value={stats.profile_views}
        icon={<Eye className="h-5 w-5" />}
        variant="primary"
        isLoading={isLoading}
        formatValue={formatters.number}
      />

      {/* 總聯繫數 */}
      <StatCard
        title="總聯繫數"
        value={stats.contact_requests}
        icon={<Phone className="h-5 w-5" />}
        variant="success"
        isLoading={isLoading}
        formatValue={formatters.number}
      />

      {/* 增長率 */}
      <StatCard
        title="增長率"
        value={`${stats.growth_rate.toFixed(1)}%`}
        icon={<TrendingUp className="h-5 w-5" />}
        trend={{
          value: stats.growth_rate,
          isPositive: stats.growth_rate >= 0,
        }}
        variant={stats.growth_rate >= 0 ? 'success' : 'warning'}
        isLoading={isLoading}
      />
    </div>
  );
}
```

---

### 8. ContactList (聯繫列表)

**用途**: 顯示最近聯繫記錄列表

**檔案**: `components/dashboard/salesperson/ContactList.tsx`

**Props 定義**:
```typescript
interface ContactListProps {
  contacts: Array<{
    id: number;
    customer_name: string;
    customer_email: string;
    message: string;
    status: 'pending' | 'contacted' | 'closed';
    created_at: string;
  }>;
  onContactClick?: (contactId: number) => void;
  isLoading?: boolean;
}
```

**完整實作**:
```typescript
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { formatDistanceToNow } from 'date-fns';
import { zhTW } from 'date-fns/locale';
import { EmptyState } from '@/components/dashboard/EmptyState';
import { MessageSquare } from 'lucide-react';

export interface ContactListProps {
  contacts: Array<{
    id: number;
    customer_name: string;
    customer_email: string;
    message: string;
    status: 'pending' | 'contacted' | 'closed';
    created_at: string;
  }>;
  onContactClick?: (contactId: number) => void;
  isLoading?: boolean;
}

const statusConfig = {
  pending: { label: '待處理', variant: 'warning' as const },
  contacted: { label: '已聯繫', variant: 'default' as const },
  closed: { label: '已結案', variant: 'secondary' as const },
};

export function ContactList({
  contacts,
  onContactClick,
  isLoading,
}: ContactListProps) {
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <Skeleton className="h-6 w-32" />
        </CardHeader>
        <CardContent>
          {[...Array(3)].map((_, i) => (
            <div key={i} className="mb-4">
              <Skeleton className="h-4 w-full mb-2" />
              <Skeleton className="h-3 w-3/4" />
            </div>
          ))}
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <MessageSquare className="h-5 w-5" />
          最近聯繫記錄
        </CardTitle>
      </CardHeader>
      <CardContent>
        {contacts.length === 0 ? (
          <EmptyState
            icon={<MessageSquare className="h-8 w-8 text-slate-300" />}
            title="尚無聯繫記錄"
            description="還沒有客戶聯繫您。優化您的檔案以吸引更多客戶！"
          />
        ) : (
          <div className="space-y-4">
            {contacts.map((contact) => (
              <div
                key={contact.id}
                className="p-4 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
                onClick={() => onContactClick?.(contact.id)}
              >
                {/* Header */}
                <div className="flex items-center justify-between mb-2">
                  <div>
                    <p className="font-semibold text-slate-900">
                      {contact.customer_name}
                    </p>
                    <p className="text-sm text-slate-500">
                      {contact.customer_email}
                    </p>
                  </div>
                  <Badge variant={statusConfig[contact.status].variant}>
                    {statusConfig[contact.status].label}
                  </Badge>
                </div>

                {/* Message Preview */}
                <p className="text-sm text-slate-600 line-clamp-2 mb-2">
                  {contact.message}
                </p>

                {/* Time */}
                <p className="text-xs text-slate-400">
                  {formatDistanceToNow(new Date(contact.created_at), {
                    addSuffix: true,
                    locale: zhTW,
                  })}
                </p>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
```

---

## 👨‍💼 管理員專用組件

### 9. TopSalespersons (熱門業務員)

**用途**: 顯示瀏覽數最高的 Top 10 業務員

**檔案**: `components/dashboard/admin/TopSalespersons.tsx`

**Props 定義**:
```typescript
interface TopSalespersonsProps {
  salespersons: Array<{
    id: number;
    name: string;
    profile_views: number;
    contact_requests: number;
    conversion_rate: number;
  }>;
  onSalespersonClick?: (salespersonId: number) => void;
  isLoading?: boolean;
}
```

**完整實作**:
```typescript
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { Trophy, Eye, Phone } from 'lucide-react';

export interface TopSalespersonsProps {
  salespersons: Array<{
    id: number;
    name: string;
    profile_views: number;
    contact_requests: number;
    conversion_rate: number;
  }>;
  onSalespersonClick?: (salespersonId: number) => void;
  isLoading?: boolean;
}

export function TopSalespersons({
  salespersons,
  onSalespersonClick,
  isLoading,
}: TopSalespersonsProps) {
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <Skeleton className="h-6 w-40" />
        </CardHeader>
        <CardContent>
          {[...Array(5)].map((_, i) => (
            <Skeleton key={i} className="h-16 w-full mb-3" />
          ))}
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Trophy className="h-5 w-5 text-amber-500" />
          熱門業務員 Top 10
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-3">
          {salespersons.map((salesperson, index) => (
            <div
              key={salesperson.id}
              className="flex items-center gap-4 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
              onClick={() => onSalespersonClick?.(salesperson.id)}
            >
              {/* Ranking */}
              <div
                className={`flex items-center justify-center w-8 h-8 rounded-full font-bold ${
                  index === 0
                    ? 'bg-amber-100 text-amber-600'
                    : index === 1
                    ? 'bg-slate-100 text-slate-600'
                    : index === 2
                    ? 'bg-orange-100 text-orange-600'
                    : 'bg-slate-50 text-slate-500'
                }`}
              >
                {index + 1}
              </div>

              {/* Name */}
              <div className="flex-1">
                <p className="font-semibold text-slate-900">
                  {salesperson.name}
                </p>
                <div className="flex items-center gap-3 mt-1">
                  <span className="flex items-center gap-1 text-xs text-slate-500">
                    <Eye className="h-3 w-3" />
                    {salesperson.profile_views.toLocaleString()}
                  </span>
                  <span className="flex items-center gap-1 text-xs text-slate-500">
                    <Phone className="h-3 w-3" />
                    {salesperson.contact_requests}
                  </span>
                </div>
              </div>

              {/* Conversion Rate */}
              <Badge variant="success">
                {salesperson.conversion_rate.toFixed(1)}%
              </Badge>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
}
```

---

### 10. ActivityCard (活躍度卡片)

**用途**: 顯示業務員活躍度統計

**檔案**: `components/dashboard/admin/ActivityCard.tsx`

**完整實作**:
```typescript
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Users, UserCheck, UserX } from 'lucide-react';
import { Skeleton } from '@/components/ui/skeleton';

export interface ActivityCardProps {
  activity: {
    active_salespersons: number;
    inactive_salespersons: number;
    total_salespersons: number;
    activity_rate: number;
  };
  isLoading?: boolean;
}

export function ActivityCard({ activity, isLoading }: ActivityCardProps) {
  if (isLoading) {
    return (
      <Card>
        <CardHeader>
          <Skeleton className="h-6 w-32" />
        </CardHeader>
        <CardContent>
          <Skeleton className="h-20 w-full" />
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Users className="h-5 w-5" />
          業務員活躍度
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {/* Total */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Users className="h-4 w-4 text-slate-500" />
              <span className="text-sm text-slate-600">總業務員數</span>
            </div>
            <span className="text-2xl font-bold text-slate-900">
              {activity.total_salespersons}
            </span>
          </div>

          {/* Active */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <UserCheck className="h-4 w-4 text-teal-500" />
              <span className="text-sm text-slate-600">活躍業務員</span>
            </div>
            <span className="text-xl font-semibold text-teal-600">
              {activity.active_salespersons}
            </span>
          </div>

          {/* Inactive */}
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <UserX className="h-4 w-4 text-slate-400" />
              <span className="text-sm text-slate-600">低活躍業務員</span>
            </div>
            <span className="text-xl font-semibold text-slate-500">
              {activity.inactive_salespersons}
            </span>
          </div>

          {/* Activity Rate */}
          <div className="pt-4 border-t border-slate-200">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-700">
                活躍率
              </span>
              <span className="text-2xl font-bold text-teal-600">
                {activity.activity_rate.toFixed(1)}%
              </span>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
```

---

## 🎨 組件組合策略

### 業務員 Dashboard 組合

```typescript
// app/(dashboard)/dashboard/analytics/page.tsx

import { SalespersonStats } from '@/components/dashboard/salesperson/SalespersonStats';
import { DualLineChart } from '@/components/dashboard/charts/DualLineChart';
import { ContactList } from '@/components/dashboard/salesperson/ContactList';

export default function SalespersonAnalyticsPage() {
  return (
    <div className="space-y-6">
      {/* Stats Cards */}
      <SalespersonStats stats={stats} isLoading={isLoading} />

      {/* Trend Chart */}
      <Card>
        <CardHeader>
          <CardTitle>趨勢分析</CardTitle>
        </CardHeader>
        <CardContent>
          <DualLineChart
            data={trendsData}
            xAxisKey="date"
            lines={[
              { dataKey: 'views', name: '瀏覽數', color: '#0EA5E9' },
              { dataKey: 'contacts', name: '聯繫數', color: '#14B8A6' },
            ]}
          />
        </CardContent>
      </Card>

      {/* Contact List */}
      <ContactList contacts={contacts} onContactClick={handleContactClick} />
    </div>
  );
}
```

### 管理員 Dashboard 組合

```typescript
// app/(admin)/admin/dashboard/analytics/page.tsx

import { StatCard } from '@/components/dashboard/StatCard';
import { TopSalespersons } from '@/components/dashboard/admin/TopSalespersons';
import { ActivityCard } from '@/components/dashboard/admin/ActivityCard';
import { DualLineChart } from '@/components/dashboard/charts/DualLineChart';

export default function AdminAnalyticsPage() {
  return (
    <div className="space-y-6">
      {/* KPI Cards Grid (4 cards) */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard {...kpiCard1} />
        <StatCard {...kpiCard2} />
        <StatCard {...kpiCard3} />
        <StatCard {...kpiCard4} />
      </div>

      {/* Two Column Layout */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <TopSalespersons salespersons={topSalespersons} />
        <ActivityCard activity={activity} />
      </div>

      {/* Growth Trends Chart */}
      <Card>
        <CardHeader>
          <CardTitle>平台成長趨勢</CardTitle>
        </CardHeader>
        <CardContent>
          <DualLineChart data={trendsData} {...chartConfig} />
        </CardContent>
      </Card>
    </div>
  );
}
```

---

## ✅ 組件檢查清單

開發每個組件時，確保：

- [ ] TypeScript Props 介面完整定義
- [ ] 支援 Loading 狀態 (Skeleton)
- [ ] 支援 Empty 狀態 (EmptyState)
- [ ] 支援 Error 狀態 (ErrorFallback)
- [ ] 響應式設計 (Mobile/Tablet/Desktop)
- [ ] 無障礙性屬性 (aria-label, role)
- [ ] 互動回饋 (hover, active, focus)
- [ ] 程式碼可直接複製使用
- [ ] 遵循設計系統規範

---

**Version**: 1.0
**Last Updated**: 2026-01-24
**Total Components**: 10
**Dependencies**: Recharts, Radix UI, Lucide React
