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
