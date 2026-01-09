'use client';

import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import {
  CheckCircle2,
  Clock,
  AlertCircle,
  User,
  Building2,
  Award,
  Briefcase,
  Info
} from 'lucide-react';
import { useApprovalStatus } from '@/hooks/useSalesperson';
import type { ApprovalStatus } from '@/types/api';

export default function ApprovalStatusPage() {
  const { data: approvalData, isLoading } = useApprovalStatus();

  // 取得審核狀態徽章配置
  const getStatusBadge = (status: ApprovalStatus | null | undefined) => {
    switch (status) {
      case 'approved':
        return {
          variant: 'success' as const,
          label: '已通過',
          icon: CheckCircle2,
          color: 'text-green-600',
          bg: 'bg-green-50',
        };
      case 'pending':
        return {
          variant: 'warning' as const,
          label: '審核中',
          icon: Clock,
          color: 'text-yellow-600',
          bg: 'bg-yellow-50',
        };
      case 'rejected':
        return {
          variant: 'error' as const,
          label: '已拒絕',
          icon: AlertCircle,
          color: 'text-red-600',
          bg: 'bg-red-50',
        };
      default:
        return {
          variant: 'secondary' as const,
          label: '未知',
          icon: Clock,
          color: 'text-slate-600',
          bg: 'bg-slate-50',
        };
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-slate-900">審核狀態</h1>
          <p className="text-slate-600 mt-1">查看您的資料審核進度</p>
        </div>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  if (!approvalData) {
    return (
      <div className="space-y-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-slate-900">審核狀態</h1>
          <p className="text-slate-600 mt-1">查看您的資料審核進度</p>
        </div>
        <Card>
          <CardContent className="py-16 text-center">
            <AlertCircle className="h-16 w-16 text-slate-300 mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-slate-900 mb-2">無法載入審核狀態</h3>
            <p className="text-slate-600">請稍後再試</p>
          </CardContent>
        </Card>
      </div>
    );
  }

  // 安全地獲取資料，提供默認值
  const experiences = approvalData.experiences || [];
  const certifications = approvalData.certifications || [];

  const profileBadge = getStatusBadge(approvalData.profile_status);
  const ProfileIcon = profileBadge.icon;

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div>
        <h1 className="text-2xl font-bold text-slate-900">審核狀態</h1>
        <p className="text-slate-600 mt-1">查看您的資料審核進度</p>
      </div>

      {/* 提示訊息 */}
      <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div className="flex items-start">
          <Info className="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
          <p className="text-sm text-blue-800 leading-relaxed">
            您上傳的資料需要經過管理員審核後才會顯示在公開個人檔案上。審核通常在 1-3 個工作日內完成。
          </p>
        </div>
      </div>

      {/* 個人資料審核狀態 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-3">
            <div className="p-2 bg-primary-50 rounded-lg">
              <User className="h-5 w-5 text-primary-600" />
            </div>
            個人資料
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-3">
            <div className={`p-3 ${profileBadge.bg} rounded-xl`}>
              <ProfileIcon className={`h-6 w-6 ${profileBadge.color}`} />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600 mb-1">審核狀態</p>
              <Badge variant={profileBadge.variant}>
                <ProfileIcon className="mr-1 h-3 w-3" />
                {profileBadge.label}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* 公司資料審核狀態 */}
      {approvalData.company_status && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-3">
              <div className="p-2 bg-primary-50 rounded-lg">
                <Building2 className="h-5 w-5 text-primary-600" />
              </div>
              公司資料
            </CardTitle>
          </CardHeader>
          <CardContent>
            {(() => {
              const companyBadge = getStatusBadge(approvalData.company_status);
              const CompanyIcon = companyBadge.icon;

              return (
                <div className="flex items-center gap-3">
                  <div className={`p-3 ${companyBadge.bg} rounded-xl`}>
                    <CompanyIcon className={`h-6 w-6 ${companyBadge.color}`} />
                  </div>
                  <div className="flex-1">
                    <p className="text-sm text-slate-600 mb-1">審核狀態</p>
                    <Badge variant={companyBadge.variant}>
                      <CompanyIcon className="mr-1 h-3 w-3" />
                      {companyBadge.label}
                    </Badge>
                  </div>
                </div>
              );
            })()}
          </CardContent>
        </Card>
      )}

      {/* 工作經驗審核狀態 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-3">
            <div className="p-2 bg-primary-50 rounded-lg">
              <Briefcase className="h-5 w-5 text-primary-600" />
            </div>
            工作經驗（{experiences.length}）
          </CardTitle>
        </CardHeader>
        <CardContent>
          {experiences.length > 0 ? (
            <div className="space-y-4">
              {experiences.map((exp) => {
                const expBadge = getStatusBadge(exp.approval_status);
                const ExpIcon = expBadge.icon;

                return (
                  <div key={exp.id} className="border-2 border-slate-200 rounded-xl p-4">
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex-1">
                        <h4 className="font-semibold text-slate-900 mb-1">
                          {exp.position}
                        </h4>
                        <p className="text-sm text-slate-600">{exp.company}</p>
                      </div>
                      <Badge variant={expBadge.variant} size="sm">
                        <ExpIcon className="mr-1 h-3 w-3" />
                        {expBadge.label}
                      </Badge>
                    </div>

                    {/* 拒絕原因 */}
                    {exp.approval_status === 'rejected' && exp.rejected_reason && (
                      <div className="bg-red-50 border border-red-200 rounded-lg p-3 mt-3">
                        <p className="text-sm text-red-800">
                          <strong>拒絕原因：</strong>
                          {exp.rejected_reason}
                        </p>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="text-center py-8">
              <Briefcase className="h-12 w-12 text-slate-300 mx-auto mb-3" />
              <p className="text-slate-500">尚未新增工作經驗</p>
            </div>
          )}
        </CardContent>
      </Card>

      {/* 專業證照審核狀態 */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-3">
            <div className="p-2 bg-primary-50 rounded-lg">
              <Award className="h-5 w-5 text-primary-600" />
            </div>
            專業證照（{certifications.length}）
          </CardTitle>
        </CardHeader>
        <CardContent>
          {certifications.length > 0 ? (
            <div className="space-y-4">
              {certifications.map((cert) => {
                const certBadge = getStatusBadge(cert.approval_status);
                const CertIcon = certBadge.icon;

                return (
                  <div key={cert.id} className="border-2 border-slate-200 rounded-xl p-4">
                    <div className="flex items-start justify-between mb-3">
                      <div className="flex-1">
                        <h4 className="font-semibold text-slate-900">
                          {cert.name}
                        </h4>
                      </div>
                      <Badge variant={certBadge.variant} size="sm">
                        <CertIcon className="mr-1 h-3 w-3" />
                        {certBadge.label}
                      </Badge>
                    </div>

                    {/* 拒絕原因 */}
                    {cert.approval_status === 'rejected' && cert.rejected_reason && (
                      <div className="bg-red-50 border border-red-200 rounded-lg p-3 mt-3">
                        <p className="text-sm text-red-800">
                          <strong>拒絕原因：</strong>
                          {cert.rejected_reason}
                        </p>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="text-center py-8">
              <Award className="h-12 w-12 text-slate-300 mx-auto mb-3" />
              <p className="text-slate-500">尚未新增專業證照</p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
