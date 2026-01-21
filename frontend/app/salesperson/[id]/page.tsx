'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { getAvatarFallback } from '@/lib/utils/avatar';
import { Button } from '@/components/ui/button';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import {
  Mail,
  Phone,
  MapPin,
  Briefcase,
  Award,
  Calendar,
  Building2,
  ArrowLeft,
  CheckCircle2,
  Clock,
} from 'lucide-react';
import { useSalespersonDetail } from '@/hooks/useSearch';
import { useAuth, useLogout } from '@/hooks/useAuth';
import { formatDate } from '@/lib/utils/format';
import { ExperienceTimeline } from '@/components/features/salesperson/experience-timeline';
import { CertificationCards } from '@/components/features/salesperson/certification-cards';

export default function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  const { data: salesperson, isLoading, error } = useSalespersonDetail(id);
  const { data: user, isLoading: authLoading } = useAuth();
  const logoutMutation = useLogout();

  const handleLogout = () => {
    logoutMutation.mutate();
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header user={user} onLogout={handleLogout} isLoading={authLoading} />
        <main className="flex-1 bg-slate-50">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Card>
              <CardContent className="p-8">
                <ProfileSkeleton />
              </CardContent>
            </Card>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  if (error || !salesperson) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header user={user} onLogout={handleLogout} isLoading={authLoading} />
        <main className="flex-1 bg-slate-50">
          <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Card>
              <CardContent className="py-16 text-center">
                <h3 className="text-xl font-semibold text-slate-900 mb-2">
                  找不到業務員資料
                </h3>
                <p className="text-slate-600 mb-6">
                  此業務員可能不存在或已被移除
                </p>
                <Link href="/search">
                  <Button>
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    返回搜尋頁面
                  </Button>
                </Link>
              </CardContent>
            </Card>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  const specialtiesList = salesperson.specialties
    ? salesperson.specialties.split(',')
    : [];

  // Normalize service_regions to array
  const serviceRegions = (() => {
    const regions = salesperson.service_regions as string[] | string | null | undefined;
    if (!regions) return [];
    if (Array.isArray(regions)) return regions;
    if (typeof regions === 'string') {
      try {
        // Try parsing as JSON first
        const parsed = JSON.parse(regions);
        return Array.isArray(parsed) ? parsed : [regions];
      } catch {
        // If not JSON, split by comma
        return regions.split(',').map((r: string) => r.trim());
      }
    }
    return [];
  })();

  const statusBadgeMap = {
    pending: { variant: 'warning' as const, label: '審核中', icon: Clock },
    approved: { variant: 'success' as const, label: '已審核', icon: CheckCircle2 },
    rejected: { variant: 'error' as const, label: '已拒絕', icon: Clock },
  };

  const statusBadge = salesperson.approval_status && statusBadgeMap[salesperson.approval_status as keyof typeof statusBadgeMap]
    ? statusBadgeMap[salesperson.approval_status as keyof typeof statusBadgeMap]
    : { variant: 'secondary' as const, label: '未知', icon: Clock };

  return (
    <div className="min-h-screen flex flex-col">
      <Header user={user} onLogout={handleLogout} isLoading={authLoading} />

      <main className="flex-1 bg-slate-50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* 返回按鈕 */}
          <div className="mb-6">
            <Link href="/search">
              <Button variant="ghost">
                <ArrowLeft className="mr-2 h-4 w-4" />
                返回搜尋結果
              </Button>
            </Link>
          </div>

          <div className="grid lg:grid-cols-3 gap-6">
            {/* 左側主要資訊 */}
            <div className="lg:col-span-2 space-y-6">
              {/* 個人資料卡片 */}
              <Card>
                <CardContent className="p-8">
                  <div className="flex flex-col md:flex-row items-start md:items-center gap-6 mb-6">
                    <Avatar
                      src={salesperson.avatar}
                      fallback={getAvatarFallback(salesperson)}
                      size="2xl"
                    />
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <h1 className="text-3xl font-bold text-slate-900">
                          {salesperson.full_name}
                        </h1>
                        <Badge variant={statusBadge.variant} size="sm">
                          <statusBadge.icon className="mr-1 h-3 w-3" />
                          {statusBadge.label}
                        </Badge>
                      </div>

                      {salesperson.company && (
                        <div className="flex items-center gap-2 text-slate-600 mb-3">
                          <Building2 className="h-5 w-5" />
                          <span className="text-lg">{salesperson.company.name}</span>
                        </div>
                      )}

                      {/* 專長標籤 */}
                      {specialtiesList.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                          {specialtiesList.map((specialty, index) => (
                            <Badge key={index} variant="primary" size="sm">
                              {specialty.trim()}
                            </Badge>
                          ))}
                        </div>
                      )}
                    </div>
                  </div>

                  {/* 簡介 */}
                  {salesperson.bio && (
                    <div className="pt-6 border-t border-slate-200">
                      <h3 className="text-lg font-semibold text-slate-900 mb-3">
                        個人簡介
                      </h3>
                      <p className="text-slate-600 whitespace-pre-line">
                        {salesperson.bio}
                      </p>
                    </div>
                  )}
                </CardContent>
              </Card>

              {/* 工作經驗 */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Briefcase className="h-5 w-5" />
                    工作經驗
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <ExperienceTimeline
                    experiences={salesperson.experiences || []}
                    isLoading={isLoading}
                  />
                </CardContent>
              </Card>

              {/* 專業證照 */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Award className="h-5 w-5" />
                    專業證照
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <CertificationCards
                    certifications={salesperson.certifications || []}
                    isLoading={isLoading}
                  />
                </CardContent>
              </Card>
            </div>

            {/* 右側聯絡資訊 */}
            <div className="lg:col-span-1">
              <Card className="sticky top-20">
                <CardHeader>
                  <CardTitle>聯絡資訊</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  {/* 電話 */}
                  <div className="flex items-start gap-3">
                    <Phone className="h-5 w-5 text-slate-400 mt-0.5 flex-shrink-0" />
                    <div>
                      <p className="text-sm text-slate-500 mb-1">電話</p>
                      <a
                        href={`tel:${salesperson.phone}`}
                        className="text-slate-900 hover:text-primary-600 transition-colors"
                      >
                        {salesperson.phone}
                      </a>
                    </div>
                  </div>

                  {/* 服務地區 */}
                  {serviceRegions.length > 0 && (
                    <div className="flex items-start gap-3">
                      <MapPin className="h-5 w-5 text-slate-400 mt-0.5 flex-shrink-0" />
                      <div>
                        <p className="text-sm text-slate-500 mb-1">服務地區</p>
                        <div className="flex flex-wrap gap-2">
                          {serviceRegions.map((region, index) => (
                            <Badge key={index} variant="secondary" size="sm">
                              {region}
                            </Badge>
                          ))}
                        </div>
                      </div>
                    </div>
                  )}


                  {/* 註冊時間 */}
                  <div className="pt-4 border-t border-slate-200">
                    <div className="flex items-center gap-2 text-sm text-slate-500">
                      <Calendar className="h-4 w-4" />
                      <span>
                        註冊時間：{formatDate(salesperson.created_at)}
                      </span>
                    </div>
                  </div>

                  {/* CTA */}
                  <div className="pt-4 space-y-2">
                    <Button className="w-full" size="lg">
                      <Phone className="mr-2 h-4 w-4" />
                      立即聯絡
                    </Button>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
