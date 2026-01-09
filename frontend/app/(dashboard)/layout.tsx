'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/hooks/useAuth';
import { DashboardSidebar } from '@/components/layout/dashboard-sidebar';

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const { data: user, isLoading, error } = useAuth();

  useEffect(() => {
    if (!isLoading) {
      // 未登入，導向登入頁
      if (error || !user) {
        router.push('/login');
        return;
      }

      // 非業務員角色，導向首頁
      if (user.role !== 'salesperson') {
        router.push('/');
        return;
      }

      // 帳號未審核通過，顯示提示
      if (user.status === 'pending') {
        // 可以導向到一個"等待審核"頁面，或顯示提示
        // 暫時允許訪問以便查看審核狀態
      }
    }
  }, [user, isLoading, error, router]);

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  if (!user || user.role !== 'salesperson') {
    return null;
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <div className="flex">
        {/* Sidebar */}
        <DashboardSidebar />

        {/* Main Content */}
        <main className="flex-1 lg:ml-64">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}
