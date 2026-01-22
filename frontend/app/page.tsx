'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { SalespersonCard } from '@/components/features/search/salesperson-card';
import { SalespersonCardSkeleton } from '@/components/ui/skeleton';
import { Search, Users, Shield, TrendingUp, ArrowRight } from 'lucide-react';
import { useSearchSalespersons } from '@/hooks/useSearch';
import { useAuth, useLogout } from '@/hooks/useAuth';

export default function HomePage() {
  const router = useRouter();
  const [keyword, setKeyword] = useState('');

  // 獲取當前用戶資訊
  const { data: user } = useAuth();
  const logoutMutation = useLogout();

  const handleLogout = () => {
    logoutMutation.mutate();
  };

  // 載入熱門業務員 (最新註冊的 6 位)
  const { data: popularSalespersons, isLoading } = useSearchSalespersons({
    sort: 'latest',
    per_page: 6,
  });

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (keyword.trim()) {
      router.push(`/search?keyword=${encodeURIComponent(keyword)}`);
    } else {
      router.push('/search');
    }
  };

  const features = [
    {
      icon: Users,
      title: '專業業務員',
      description: '平台上的業務員都經過審核，確保專業度與可信度',
    },
    {
      icon: Shield,
      title: '安全可靠',
      description: '所有資料都經過驗證，保護您的資訊安全',
    },
    {
      icon: TrendingUp,
      title: '高效媒合',
      description: '快速找到符合需求的業務員，節省您的時間',
    },
  ];

  return (
    <div className="min-h-screen flex flex-col">
      <Header user={user} onLogout={handleLogout} />

      <main className="flex-1">
        {/* Hero Section */}
        <section className="relative bg-gradient-to-br from-primary-50 via-primary-25 to-secondary-50 py-16 md:py-20 lg:py-28 overflow-hidden">
          {/* 背景裝飾 */}
          <div className="absolute inset-0 overflow-hidden">
            <div className="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-primary-200/20 blur-3xl" />
            <div className="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-secondary-200/20 blur-3xl" />
          </div>

          <div className="container mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div className="max-w-4xl mx-auto text-center">
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 mb-6 animate-fade-in-up">
                找到最適合的
                <span className="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
                  {' '}專業業務員
                </span>
              </h1>
              <p className="text-xl text-slate-600 mb-12 max-w-2xl mx-auto animate-fade-in-up animation-delay-100">
                YAMU 連結優質業務員與企業需求，打造透明、高效的商業合作環境
              </p>

              {/* 搜尋列 */}
              <form onSubmit={handleSearch} className="max-w-2xl mx-auto animate-fade-in-up animation-delay-200">
                <div className="flex flex-col sm:flex-row gap-3">
                  <Input
                    size="lg"
                    placeholder="搜尋業務員、公司、產業..."
                    value={keyword}
                    onChange={(e) => setKeyword(e.target.value)}
                    icon={<Search className="h-5 w-5" />}
                    className="text-lg h-14 shadow-lg flex-1"
                  />
                  <Button type="submit" size="lg" className="px-10 h-14 w-full sm:w-auto">
                    搜尋
                  </Button>
                </div>
                <p className="text-sm text-slate-500 mt-3">
                  或{' '}
                  <Link href="/search" className="text-primary-600 hover:text-primary-700 font-medium">
                    瀏覽所有業務員 →
                  </Link>
                </p>
              </form>
            </div>
          </div>
        </section>

        {/* Features Section */}
        <section className="py-16 md:py-20 bg-white">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <div className="text-center mb-16">
              <h2 className="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                為什麼選擇 YAMU？
              </h2>
              <p className="text-lg text-slate-600 max-w-2xl mx-auto">
                我們提供安全、透明、高效的業務員搜尋服務
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
              {features.map((feature, index) => (
                <div
                  key={index}
                  className="animate-fade-in-up"
                  style={{ animationDelay: `${index * 100}ms` }}
                >
                  <Card hover className="text-center border border-slate-100 shadow-md hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                  <CardContent className="pt-8">
                    <div className="inline-flex items-center justify-center h-20 w-20 rounded-3xl bg-gradient-to-br from-primary-500 to-secondary-500 shadow-lg mb-6 transition-transform duration-300 hover:scale-110 hover:rotate-6">
                      <feature.icon className="h-10 w-10 text-white" />
                    </div>
                    <h3 className="text-xl font-bold text-slate-900 mb-3">
                      {feature.title}
                    </h3>
                    <p className="text-slate-600">
                      {feature.description}
                    </p>
                  </CardContent>
                </Card>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Popular Salespersons Section */}
        <section className="py-16 md:py-20 bg-slate-50">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex items-center justify-between mb-12">
              <div>
                <h2 className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">
                  熱門業務員
                </h2>
                <p className="text-lg text-slate-600">
                  最新加入平台的專業業務員
                </p>
              </div>
              <Link href="/search">
                <Button variant="outline" size="lg">
                  查看全部
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>

            {isLoading ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
                {Array.from({ length: 6 }).map((_, i) => (
                  <SalespersonCardSkeleton key={i} />
                ))}
              </div>
            ) : popularSalespersons && popularSalespersons.data.length > 0 ? (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
                {popularSalespersons.data.map((salesperson, index) => (
                  <div
                    key={salesperson.id}
                    className="animate-fade-in-up"
                    style={{ animationDelay: `${index * 50}ms` }}
                  >
                    <SalespersonCard salesperson={salesperson} />
                  </div>
                ))}
              </div>
            ) : (
              <Card>
                <CardContent className="py-12 text-center">
                  <Users className="h-12 w-12 text-slate-400 mx-auto mb-4" />
                  <p className="text-slate-600">目前沒有業務員資料</p>
                </CardContent>
              </Card>
            )}
          </div>
        </section>

        {/* CTA Section */}
        <section className="relative py-16 md:py-20 bg-gradient-to-r from-primary-500 via-primary-600 to-secondary-500 overflow-hidden">
          {/* 背景裝飾 */}
          <div className="absolute inset-0 opacity-30">
            <div className="absolute top-0 left-0 w-96 h-96 bg-white/20 rounded-full blur-3xl" />
            <div className="absolute bottom-0 right-0 w-96 h-96 bg-white/20 rounded-full blur-3xl" />
          </div>

          <div className="container mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div className="max-w-3xl mx-auto text-center text-white">
              <h2 className="text-3xl lg:text-4xl font-bold mb-6">
                準備好找到最佳業務夥伴了嗎？
              </h2>
              <p className="text-xl mb-8 opacity-90">
                立即註冊成為業務員，讓更多客戶找到您
              </p>
              <div className="flex flex-col sm:flex-row gap-4 md:gap-6 justify-center">
                <Button asChild size="lg" variant="secondary" className="h-14 px-10 text-lg shadow-xl hover:brightness-110 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300">
                  <Link href="/register">免費註冊</Link>
                </Button>
                <Button asChild size="lg" variant="outline" className="h-14 px-10 text-lg bg-white/10 border-white text-white hover:bg-white/20 hover:-translate-y-1 transition-all duration-300">
                  <Link href="/search">開始搜尋</Link>
                </Button>
              </div>
            </div>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
