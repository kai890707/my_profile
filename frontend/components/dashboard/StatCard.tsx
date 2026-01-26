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
            variant={trend.isPositive ? 'success' : 'error'}
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
