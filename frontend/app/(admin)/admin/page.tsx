'use client';

import { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { StatsCard } from '@/components/features/admin/stats-card';
import {
  Users,
  Building2,
  CheckSquare,
  Clock,
  Award,
  Briefcase,
  UserCheck,
  AlertCircle,
} from 'lucide-react';
import { useStatistics, usePendingApprovals, useApproveUser, useRejectUser, useApproveCompany, useRejectCompany, useApproveCertification, useRejectCertification } from '@/hooks/useAdmin';
import { formatDate } from '@/lib/utils/format';
import Link from 'next/link';

type Tab = 'users' | 'companies' | 'certifications';

export default function AdminDashboardPage() {
  const [activeTab, setActiveTab] = useState<Tab>('users');

  // 獲取統計資料
  const { data: stats, isLoading: statsLoading } = useStatistics();

  // 獲取待審核項目
  const { data: pendingData, isLoading: pendingLoading } = usePendingApprovals();

  // Mutations
  const approveUserMutation = useApproveUser();
  const rejectUserMutation = useRejectUser();
  const approveCompanyMutation = useApproveCompany();
  const rejectCompanyMutation = useRejectCompany();
  const approveCertMutation = useApproveCertification();
  const rejectCertMutation = useRejectCertification();

  const handleApproveUser = (userId: number) => {
    if (confirm('確定要審核通過這位業務員嗎？')) {
      approveUserMutation.mutate(userId);
    }
  };

  const handleRejectUser = (userId: number) => {
    const reason = prompt('請輸入拒絕原因（選填）：');
    if (reason !== null) {
      rejectUserMutation.mutate({ userId, reason: reason || undefined });
    }
  };

  const handleApproveCompany = (companyId: number) => {
    if (confirm('確定要審核通過這家公司嗎？')) {
      approveCompanyMutation.mutate(companyId);
    }
  };

  const handleRejectCompany = (companyId: number) => {
    const reason = prompt('請輸入拒絕原因（選填）：');
    if (reason !== null) {
      rejectCompanyMutation.mutate({ companyId, reason: reason || undefined });
    }
  };

  const handleApproveCert = (certId: number) => {
    if (confirm('確定要審核通過這張證照嗎？')) {
      approveCertMutation.mutate(certId);
    }
  };

  const handleRejectCert = (certId: number) => {
    const reason = prompt('請輸入拒絕原因（選填）：');
    if (reason !== null) {
      rejectCertMutation.mutate({ certId, reason: reason || undefined });
    }
  };

  if (statsLoading || pendingLoading) {
    return (
      <div className="space-y-6">
        <h1 className="text-2xl font-bold text-slate-900">系統總覽</h1>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  const tabs = [
    { id: 'users' as Tab, label: '業務員註冊', count: pendingData?.users?.length || 0 },
    { id: 'companies' as Tab, label: '公司資訊', count: pendingData?.companies?.length || 0 },
    { id: 'certifications' as Tab, label: '專業證照', count: pendingData?.certifications?.length || 0 },
  ];

  return (
    <div className="space-y-8">
      {/* 頁面標題 */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">系統總覽</h1>
        <p className="text-slate-600 mt-1">平台統計與待審核項目</p>
      </div>

      {/* 統計卡片 */}
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

      {/* 待審核項目 */}
      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <CheckSquare className="h-5 w-5 text-primary-600" />
              待審核項目
              {stats && stats.pending_approvals > 0 && (
                <Badge variant="warning" size="sm">
                  {stats.pending_approvals}
                </Badge>
              )}
            </CardTitle>

            <Link href="/admin/approvals">
              <Button variant="outline" size="sm">
                查看全部
              </Button>
            </Link>
          </div>
        </CardHeader>

        <CardContent>
          {/* Tabs */}
          <div className="flex gap-2 border-b border-slate-200 mb-6">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`
                  px-4 py-2 font-medium text-sm transition-colors -mb-px
                  ${
                    activeTab === tab.id
                      ? 'text-primary-600 border-b-2 border-primary-600'
                      : 'text-slate-600 hover:text-slate-900'
                  }
                `}
              >
                {tab.label}
                {tab.count > 0 && (
                  <span className="ml-2 px-2 py-0.5 text-xs bg-slate-200 text-slate-700 rounded-full">
                    {tab.count}
                  </span>
                )}
              </button>
            ))}
          </div>

          {/* Tab Content */}
          <div className="space-y-4">
            {/* 業務員註冊 */}
            {activeTab === 'users' && (
              <>
                {pendingData?.users && pendingData.users.length > 0 ? (
                  pendingData.users.slice(0, 5).map((user) => (
                    <div
                      key={user.id}
                      className="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:border-primary-300 transition-colors"
                    >
                      <div className="flex items-start gap-4">
                        <div className="p-2 bg-primary-50 rounded-lg">
                          <Users className="h-5 w-5 text-primary-600" />
                        </div>
                        <div>
                          <h4 className="font-semibold text-slate-900">{user.username}</h4>
                          <p className="text-sm text-slate-600">{user.email}</p>
                          <p className="text-xs text-slate-500 mt-1">
                            註冊時間：{formatDate(user.created_at)}
                          </p>
                        </div>
                      </div>

                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          onClick={() => handleApproveUser(user.id)}
                          disabled={approveUserMutation.isPending}
                        >
                          通過
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleRejectUser(user.id)}
                          disabled={rejectUserMutation.isPending}
                        >
                          拒絕
                        </Button>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-12">
                    <CheckSquare className="h-12 w-12 text-slate-300 mx-auto mb-3" />
                    <p className="text-slate-500">目前沒有待審核的業務員註冊</p>
                  </div>
                )}
              </>
            )}

            {/* 公司資訊 */}
            {activeTab === 'companies' && (
              <>
                {pendingData?.companies && pendingData.companies.length > 0 ? (
                  pendingData.companies.slice(0, 5).map((company) => (
                    <div
                      key={company.id}
                      className="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:border-primary-300 transition-colors"
                    >
                      <div className="flex items-start gap-4">
                        <div className="p-2 bg-blue-50 rounded-lg">
                          <Building2 className="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                          <h4 className="font-semibold text-slate-900">{company.name}</h4>
                          <p className="text-sm text-slate-600">統編：{company.tax_id}</p>
                        </div>
                      </div>

                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          onClick={() => handleApproveCompany(company.id)}
                          disabled={approveCompanyMutation.isPending}
                        >
                          通過
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleRejectCompany(company.id)}
                          disabled={rejectCompanyMutation.isPending}
                        >
                          拒絕
                        </Button>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-12">
                    <Building2 className="h-12 w-12 text-slate-300 mx-auto mb-3" />
                    <p className="text-slate-500">目前沒有待審核的公司資訊</p>
                  </div>
                )}
              </>
            )}

            {/* 專業證照 */}
            {activeTab === 'certifications' && (
              <>
                {pendingData?.certifications && pendingData.certifications.length > 0 ? (
                  pendingData.certifications.slice(0, 5).map((cert) => (
                    <div
                      key={cert.id}
                      className="flex items-center justify-between p-4 border border-slate-200 rounded-lg hover:border-primary-300 transition-colors"
                    >
                      <div className="flex items-start gap-4">
                        <div className="p-2 bg-green-50 rounded-lg">
                          <Award className="h-5 w-5 text-green-600" />
                        </div>
                        <div>
                          <h4 className="font-semibold text-slate-900">{cert.name}</h4>
                          <p className="text-sm text-slate-600">發證單位：{cert.issuer}</p>
                          <p className="text-xs text-slate-500 mt-1">
                            發證日期：{formatDate(cert.issue_date)}
                          </p>
                        </div>
                      </div>

                      <div className="flex gap-2">
                        <Button
                          size="sm"
                          onClick={() => handleApproveCert(cert.id)}
                          disabled={approveCertMutation.isPending}
                        >
                          通過
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleRejectCert(cert.id)}
                          disabled={rejectCertMutation.isPending}
                        >
                          拒絕
                        </Button>
                      </div>
                    </div>
                  ))
                ) : (
                  <div className="text-center py-12">
                    <Award className="h-12 w-12 text-slate-300 mx-auto mb-3" />
                    <p className="text-slate-500">目前沒有待審核的專業證照</p>
                  </div>
                )}
              </>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
