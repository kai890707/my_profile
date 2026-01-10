'use client';

import { Suspense } from 'react';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { SalespersonCardSkeleton } from '@/components/ui/skeleton';
import { SearchContent } from './search-content';
import { useAuth, useLogout } from '@/hooks/useAuth';

export default function SearchPage() {
  // 獲取當前用戶資訊
  const { data: user } = useAuth();
  const logoutMutation = useLogout();

  const handleLogout = () => {
    logoutMutation.mutate();
  };

  return (
    <div className="min-h-screen flex flex-col">
      <Header user={user} onLogout={handleLogout} />

      <main className="flex-1 bg-slate-50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* 頁面標題 */}
          <div className="mb-8">
            <h1 className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">
              搜尋業務員
            </h1>
            <p className="text-lg text-slate-600">
              找到最適合您需求的專業業務員
            </p>
          </div>

          <Suspense
            fallback={
              <div className="grid lg:grid-cols-4 gap-6">
                <aside className="lg:col-span-1"></aside>
                <div className="lg:col-span-3">
                  <div className="grid md:grid-cols-2 gap-6">
                    {Array.from({ length: 6 }).map((_, i) => (
                      <SalespersonCardSkeleton key={i} />
                    ))}
                  </div>
                </div>
              </div>
            }
          >
            <SearchContent />
          </Suspense>
        </div>
      </main>

      <Footer />
    </div>
  );
}
