'use client';

import { useState } from 'react';
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { StatCard, formatters } from '@/components/dashboard/StatCard';
import { DualLineChart } from '@/components/dashboard/charts/DualLineChart';
import { ContactList } from '@/components/dashboard/salesperson/ContactList';
import { Eye, Phone, TrendingUp } from 'lucide-react';
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
  } = useSalespersonStats(timeRange);

  const {
    data: trends,
    isLoading: isLoadingTrends,
  } = useSalespersonTrends(timeRange);

  const {
    data: contacts,
    isLoading: isLoadingContacts,
  } = useRecentContacts();

  // Error Handling
  if (statsError) {
    return (
      <div className="text-center py-12">
        <p className="text-red-500">載入數據失敗</p>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Time Range Tabs */}
      <Tabs
        value={timeRange}
        onValueChange={(value: string) => setTimeRange(value as TimeRange)}
        className="w-full"
      >
        <TabsList className="grid w-full max-w-md grid-cols-3">
          <TabsTrigger value="today">今日</TabsTrigger>
          <TabsTrigger value="7days">過去 7 天</TabsTrigger>
          <TabsTrigger value="30days">過去 30 天</TabsTrigger>
        </TabsList>
      </Tabs>

      {/* KPI Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <StatCard
          title="總瀏覽數"
          value={stats?.profile_views || 0}
          icon={<Eye className="h-5 w-5" />}
          variant="primary"
          isLoading={isLoadingStats}
          formatValue={formatters.number}
        />
        <StatCard
          title="總聯繫數"
          value={stats?.contact_requests || 0}
          icon={<Phone className="h-5 w-5" />}
          variant="success"
          isLoading={isLoadingStats}
          formatValue={formatters.number}
        />
        <StatCard
          title="增長率"
          value={`${(stats?.growth_rate || 0).toFixed(1)}%`}
          icon={<TrendingUp className="h-5 w-5" />}
          trend={stats ? {
            value: stats.growth_rate,
            isPositive: stats.growth_rate >= 0,
          } : undefined}
          variant={stats?.growth_rate && stats.growth_rate >= 0 ? 'success' : 'warning'}
          isLoading={isLoadingStats}
        />
      </div>

      {/* Trend Chart */}
      <Card>
        <CardHeader>
          <CardTitle>趨勢分析</CardTitle>
        </CardHeader>
        <CardContent>
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
        </CardContent>
      </Card>

      {/* Contact List */}
      <ContactList
        contacts={contacts || []}
        onContactClick={(id) => {
          console.log('Contact clicked:', id);
        }}
        isLoading={isLoadingContacts}
      />
    </div>
  );
}
