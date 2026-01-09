'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { Avatar } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
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
import { formatDate } from '@/lib/utils/format';

export default function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  const { data: salesperson, isLoading, error } = useSalespersonDetail(id);

  if (isLoading) {
    return (
      <div className="min-h-screen flex flex-col">
        <Header />
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
        <Header />
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

  const statusBadge = {
    pending: { variant: 'warning' as const, label: '審核中', icon: Clock },
    approved: { variant: 'success' as const, label: '已審核', icon: CheckCircle2 },
    rejected: { variant: 'error' as const, label: '已拒絕', icon: Clock },
  }[salesperson.approval_status];

  return (
    <div className="min-h-screen flex flex-col">
      <Header />

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
                      fallback={salesperson.full_name.substring(0, 2)}
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
                          {salesperson.company.industry && (
                            <Badge variant="secondary" size="sm">
                              {salesperson.company.industry.name}
                            </Badge>
                          )}
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
              {salesperson.experiences && salesperson.experiences.length > 0 && (
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Briefcase className="h-5 w-5" />
                      工作經驗
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-6">
                      {salesperson.experiences.map((exp) => (
                        <div key={exp.id} className="border-l-2 border-primary-200 pl-4">
                          <div className="flex items-start justify-between mb-2">
                            <div>
                              <h4 className="text-lg font-semibold text-slate-900">
                                {exp.position}
                              </h4>
                              <p className="text-slate-600">{exp.company}</p>
                            </div>
                            {exp.approval_status === 'approved' && (
                              <Badge variant="success" size="sm">
                                <CheckCircle2 className="mr-1 h-3 w-3" />
                                已驗證
                              </Badge>
                            )}
                          </div>
                          <div className="flex items-center gap-2 text-sm text-slate-500 mb-2">
                            <Calendar className="h-4 w-4" />
                            <span>
                              {formatDate(exp.start_date)} -{' '}
                              {exp.end_date ? formatDate(exp.end_date) : '至今'}
                            </span>
                          </div>
                          {exp.description && (
                            <p className="text-slate-600 text-sm whitespace-pre-line">
                              {exp.description}
                            </p>
                          )}
                        </div>
                      ))}
                    </div>
                  </CardContent>
                </Card>
              )}

              {/* 專業證照 */}
              {salesperson.certifications &&
                salesperson.certifications.length > 0 && (
                  <Card>
                    <CardHeader>
                      <CardTitle className="flex items-center gap-2">
                        <Award className="h-5 w-5" />
                        專業證照
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div className="grid md:grid-cols-2 gap-4">
                        {salesperson.certifications.map((cert) => (
                          <div
                            key={cert.id}
                            className="p-4 rounded-lg border border-slate-200 hover:border-primary-300 transition-colors"
                          >
                            <div className="flex items-start justify-between mb-2">
                              <h4 className="font-semibold text-slate-900">
                                {cert.name}
                              </h4>
                              {cert.approval_status === 'approved' && (
                                <CheckCircle2 className="h-5 w-5 text-success-600 flex-shrink-0" />
                              )}
                            </div>
                            <p className="text-sm text-slate-600 mb-2">
                              {cert.issuer}
                            </p>
                            <div className="flex items-center gap-2 text-xs text-slate-500">
                              <Calendar className="h-3 w-3" />
                              <span>{formatDate(cert.issue_date)}</span>
                              {cert.expiry_date && (
                                <>
                                  <span>-</span>
                                  <span>{formatDate(cert.expiry_date)}</span>
                                </>
                              )}
                            </div>
                            {cert.description && (
                              <p className="text-xs text-slate-600 mt-2">
                                {cert.description}
                              </p>
                            )}
                          </div>
                        ))}
                      </div>
                    </CardContent>
                  </Card>
                )}
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

                  {/* 公司地址 */}
                  {salesperson.company?.address && (
                    <div className="flex items-start gap-3">
                      <Building2 className="h-5 w-5 text-slate-400 mt-0.5 flex-shrink-0" />
                      <div>
                        <p className="text-sm text-slate-500 mb-1">公司地址</p>
                        <p className="text-slate-900">
                          {salesperson.company.address}
                        </p>
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
