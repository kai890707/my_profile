'use client';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { StatsCard } from '@/components/features/admin/stats-card';
import {
  SalespersonStatusChart,
  PendingApprovalsChart,
  SalespersonOverviewChart,
} from '@/components/features/admin/charts';
import {
  Users,
  Building2,
  TrendingUp,
  Clock,
  UserCheck,
  Activity,
} from 'lucide-react';
import { useStatistics, usePendingApprovals } from '@/hooks/useAdmin';

export default function StatisticsPage() {
  const { data: stats, isLoading: statsLoading } = useStatistics();
  const { data: pendingData, isLoading: pendingLoading } = usePendingApprovals();

  if (statsLoading || pendingLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-bold text-slate-900">統計報表</h1>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  // 計算待審核統計
  const pendingUsers = pendingData?.users?.length || 0;
  const pendingCompanies = pendingData?.companies?.length || 0;
  const pendingCertifications = pendingData?.certifications?.length || 0;
  const pendingExperiences = pendingData?.experiences?.length || 0;
  const totalPending = pendingUsers + pendingCompanies + pendingCertifications + pendingExperiences;

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">統計報表</h1>
        <p className="text-slate-600 mt-1">平台數據總覽與趨勢分析</p>
      </div>

      {/* 總覽統計卡片 */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatsCard
          title="總業務員數"
          value={stats?.total_salespersons || 0}
          icon={Users}
          color="primary"
        />

        <StatsCard
          title="活躍業務員"
          value={stats?.active_salespersons || 0}
          icon={UserCheck}
          color="success"
        />

        <StatsCard
          title="待審核業務員"
          value={stats?.pending_salespersons || 0}
          icon={Clock}
          color="warning"
        />

        <StatsCard
          title="公司總數"
          value={stats?.total_companies || 0}
          icon={Building2}
          color="info"
        />
      </div>

      {/* 業務員統計 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Users className="h-5 w-5 text-primary-600" />
            業務員統計
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-3 gap-6">
            {/* 總業務員 */}
            <div className="text-center p-6 bg-slate-50 rounded-xl">
              <div className="inline-flex p-3 bg-primary-100 rounded-full mb-3">
                <Users className="h-8 w-8 text-primary-600" />
              </div>
              <p className="text-3xl font-bold text-slate-900">
                {stats?.total_salespersons || 0}
              </p>
              <p className="text-sm text-slate-600 mt-1">總業務員數</p>
            </div>

            {/* 活躍業務員 */}
            <div className="text-center p-6 bg-green-50 rounded-xl">
              <div className="inline-flex p-3 bg-green-100 rounded-full mb-3">
                <UserCheck className="h-8 w-8 text-green-600" />
              </div>
              <p className="text-3xl font-bold text-green-900">
                {stats?.active_salespersons || 0}
              </p>
              <p className="text-sm text-green-700 mt-1">活躍業務員</p>
              <p className="text-xs text-green-600 mt-2">
                {stats?.total_salespersons
                  ? Math.round(
                      ((stats.active_salespersons || 0) / stats.total_salespersons) * 100
                    )
                  : 0}
                % 活躍率
              </p>
            </div>

            {/* 待審核業務員 */}
            <div className="text-center p-6 bg-yellow-50 rounded-xl">
              <div className="inline-flex p-3 bg-yellow-100 rounded-full mb-3">
                <Clock className="h-8 w-8 text-yellow-600" />
              </div>
              <p className="text-3xl font-bold text-yellow-900">
                {stats?.pending_salespersons || 0}
              </p>
              <p className="text-sm text-yellow-700 mt-1">待審核</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* 待審核統計 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5 text-yellow-600" />
            待審核項目統計
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid md:grid-cols-4 gap-6">
            {/* 總待審核 */}
            <div className="text-center p-6 bg-slate-50 rounded-xl">
              <div className="inline-flex p-3 bg-slate-200 rounded-full mb-3">
                <Activity className="h-8 w-8 text-slate-600" />
              </div>
              <p className="text-3xl font-bold text-slate-900">{totalPending}</p>
              <p className="text-sm text-slate-600 mt-1">總待審核數</p>
            </div>

            {/* 業務員註冊 */}
            <div className="text-center p-6 bg-blue-50 rounded-xl">
              <div className="inline-flex p-3 bg-blue-100 rounded-full mb-3">
                <Users className="h-8 w-8 text-blue-600" />
              </div>
              <p className="text-3xl font-bold text-blue-900">{pendingUsers}</p>
              <p className="text-sm text-blue-700 mt-1">業務員註冊</p>
            </div>

            {/* 公司資訊 */}
            <div className="text-center p-6 bg-purple-50 rounded-xl">
              <div className="inline-flex p-3 bg-purple-100 rounded-full mb-3">
                <Building2 className="h-8 w-8 text-purple-600" />
              </div>
              <p className="text-3xl font-bold text-purple-900">{pendingCompanies}</p>
              <p className="text-sm text-purple-700 mt-1">公司資訊</p>
            </div>

            {/* 專業證照 */}
            <div className="text-center p-6 bg-green-50 rounded-xl">
              <div className="inline-flex p-3 bg-green-100 rounded-full mb-3">
                <Activity className="h-8 w-8 text-green-600" />
              </div>
              <p className="text-3xl font-bold text-green-900">{pendingCertifications}</p>
              <p className="text-sm text-green-700 mt-1">專業證照</p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* 公司統計 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Building2 className="h-5 w-5 text-primary-600" />
            公司統計
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="text-center p-8 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
            <div className="inline-flex p-4 bg-white rounded-full shadow-lg mb-4">
              <Building2 className="h-12 w-12 text-blue-600" />
            </div>
            <p className="text-5xl font-bold text-blue-900">
              {stats?.total_companies || 0}
            </p>
            <p className="text-lg text-blue-700 mt-2">合作公司總數</p>
            <p className="text-sm text-blue-600 mt-4">
              平台目前已有 {stats?.total_companies || 0} 家企業加入
            </p>
          </div>
        </CardContent>
      </Card>

      {/* 圖表區域 */}
      <div className="grid lg:grid-cols-2 gap-6">
        {/* 平台總覽統計 */}
        <SalespersonOverviewChart
          total={stats?.total_salespersons || 0}
          active={stats?.active_salespersons || 0}
          pending={stats?.pending_salespersons || 0}
          totalCompanies={stats?.total_companies || 0}
        />

        {/* 業務員狀態分佈 */}
        <SalespersonStatusChart
          total={stats?.total_salespersons || 0}
          active={stats?.active_salespersons || 0}
          pending={stats?.pending_salespersons || 0}
        />
      </div>

      {/* 待審核項目統計圖表 */}
      <PendingApprovalsChart
        users={pendingUsers}
        companies={pendingCompanies}
        certifications={pendingCertifications}
        experiences={pendingExperiences}
      />

      {/* 快速操作 */}
      <div className="bg-primary-50 border-2 border-primary-200 rounded-xl p-6">
        <h4 className="font-semibold text-primary-900 mb-4 flex items-center gap-2">
          <TrendingUp className="h-5 w-5" />
          數據摘要
        </h4>
        <div className="grid md:grid-cols-2 gap-4 text-sm">
          <div className="bg-white rounded-lg p-4">
            <p className="text-slate-600">平台活躍率</p>
            <p className="text-2xl font-bold text-primary-600 mt-1">
              {stats?.total_salespersons
                ? Math.round(
                    ((stats.active_salespersons || 0) / stats.total_salespersons) * 100
                  )
                : 0}
              %
            </p>
          </div>
          <div className="bg-white rounded-lg p-4">
            <p className="text-slate-600">待處理項目</p>
            <p className="text-2xl font-bold text-yellow-600 mt-1">{totalPending}</p>
          </div>
        </div>
      </div>
    </div>
  );
}
