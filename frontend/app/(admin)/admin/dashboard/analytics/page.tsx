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
