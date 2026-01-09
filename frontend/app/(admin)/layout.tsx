'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth, useLogout } from '@/hooks/useAuth';
import { LayoutDashboard, Users, Settings, BarChart3, CheckSquare, LogOut } from 'lucide-react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';

export default function AdminLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const pathname = usePathname();
  const { data: user, isLoading } = useAuth();
  const logoutMutation = useLogout();

  // 權限檢查
  useEffect(() => {
    if (!isLoading && (!user || user.role !== 'admin')) {
      router.push('/login');
    }
  }, [user, isLoading, router]);

  // Loading 狀態
  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  // 非 admin 用戶不顯示內容
  if (!user || user.role !== 'admin') {
    return null;
  }

  const navigation = [
    {
      name: '儀表板',
      href: '/admin',
      icon: LayoutDashboard,
      exact: true,
    },
    {
      name: '待審核',
      href: '/admin/approvals',
      icon: CheckSquare,
    },
    {
      name: '使用者管理',
      href: '/admin/users',
      icon: Users,
    },
    {
      name: '統計報表',
      href: '/admin/statistics',
      icon: BarChart3,
    },
    {
      name: '系統設定',
      href: '/admin/settings',
      icon: Settings,
    },
  ];

  const isActive = (item: typeof navigation[0]) => {
    if (item.exact) {
      return pathname === item.href;
    }
    return pathname.startsWith(item.href);
  };

  return (
    <div className="min-h-screen bg-slate-50">
      {/* Header */}
      <header className="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div className="px-6 py-4">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold text-slate-900">管理後台</h1>
              <p className="text-sm text-slate-600 mt-1">系統管理與監控</p>
            </div>

            <div className="flex items-center gap-4">
              {user && (
                <div className="text-right">
                  <p className="text-sm font-semibold text-slate-900">{user.username}</p>
                  <p className="text-xs text-slate-500">{user.email}</p>
                </div>
              )}

              <button
                onClick={() => {
                  logoutMutation.mutate();
                }}
                className="p-2 hover:bg-slate-100 rounded-lg transition-colors"
                title="登出"
              >
                <LogOut className="h-5 w-5 text-slate-600" />
              </button>
            </div>
          </div>

          {/* Navigation */}
          <nav className="flex gap-2 mt-6 -mb-px">
            {navigation.map((item) => {
              const Icon = item.icon;
              const active = isActive(item);

              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`
                    flex items-center gap-2 px-4 py-2 rounded-t-lg font-medium text-sm transition-colors
                    ${
                      active
                        ? 'bg-primary-50 text-primary-700 border-b-2 border-primary-600'
                        : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'
                    }
                  `}
                >
                  <Icon className="h-4 w-4" />
                  {item.name}
                </Link>
              );
            })}
          </nav>
        </div>
      </header>

      {/* Main Content */}
      <main className="px-6 py-8">
        <div className="max-w-7xl mx-auto">{children}</div>
      </main>
    </div>
  );
}
