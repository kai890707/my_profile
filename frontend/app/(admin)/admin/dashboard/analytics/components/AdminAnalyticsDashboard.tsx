'use client';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { StatCard, formatters } from '@/components/dashboard/StatCard';
import { TopSalespersons } from '@/components/dashboard/admin/TopSalespersons';
import { ActivityCard } from '@/components/dashboard/admin/ActivityCard';
import { DualLineChart } from '@/components/dashboard/charts/DualLineChart';
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
  } = useAdminOverview();

  const {
    data: topSalespersons,
    isLoading: isLoadingTop,
  } = useTopSalespersons();

  const {
    data: activity,
    isLoading: isLoadingActivity,
  } = useAdminActivity();

  const {
    data: trends,
    isLoading: isLoadingTrends,
  } = useAdminTrends();

  // Error Handling
  if (overviewError) {
    return (
      <div className="text-center py-12">
        <p className="text-red-500">載入數據失敗</p>
      </div>
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
        <TopSalespersons
          salespersons={topSalespersons || []}
          onSalespersonClick={(id) => {
            router.push(`/salesperson/${id}`);
          }}
          isLoading={isLoadingTop}
        />

        {/* Activity Card */}
        <ActivityCard
          activity={activity || {
            active_salespersons: 0,
            inactive_salespersons: 0,
            total_salespersons: 0,
            activity_rate: 0,
            period: '',
          }}
          isLoading={isLoadingActivity}
        />
      </div>

      {/* Growth Trends Chart */}
      <Card>
        <CardHeader>
          <CardTitle>平台成長趨勢</CardTitle>
        </CardHeader>
        <CardContent>
          <DualLineChart
            data={trends || []}
            xAxisKey="date"
            lines={[
              {
                dataKey: 'profile_views',
                name: '總瀏覽數',
                color: '#0EA5E9',
              },
              {
                dataKey: 'contact_requests',
                name: '總聯繫數',
                color: '#14B8A6',
              },
            ]}
            height={400}
            isLoading={isLoadingTrends}
          />
        </CardContent>
      </Card>
    </div>
  );
}
