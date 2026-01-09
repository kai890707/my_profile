'use client';

import { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { Plus, Award, Calendar, Trash2, CheckCircle2, Clock, AlertCircle, FileText } from 'lucide-react';
import { CertificationUploadModal } from '@/components/features/dashboard/certification-upload-modal';
import {
  useCertifications,
  useCreateCertification,
  useDeleteCertification,
} from '@/hooks/useSalesperson';
import { formatDate } from '@/lib/utils/format';
import type { Certification } from '@/types/api';
import { toast } from 'sonner';

export default function CertificationsPage() {
  const [isModalOpen, setIsModalOpen] = useState(false);

  // 獲取證照列表
  const { data: certifications, isLoading } = useCertifications();

  // Mutations
  const createMutation = useCreateCertification();
  const deleteMutation = useDeleteCertification();

  // 開啟新增 Modal
  const handleAdd = () => {
    setIsModalOpen(true);
  };

  // 處理表單提交
  const handleSubmit = (data: any) => {
    createMutation.mutate(data, {
      onSuccess: () => {
        setIsModalOpen(false);
      },
    });
  };

  // 處理刪除
  const handleDelete = (certification: Certification) => {
    if (
      confirm(`確定要刪除「${certification.name}」這張證照嗎？`)
    ) {
      deleteMutation.mutate(certification.id);
    }
  };

  // 取得審核狀態徽章配置
  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'approved':
        return {
          variant: 'success' as const,
          label: '已驗證',
          icon: CheckCircle2,
        };
      case 'pending':
        return {
          variant: 'warning' as const,
          label: '審核中',
          icon: Clock,
        };
      case 'rejected':
        return {
          variant: 'error' as const,
          label: '已拒絕',
          icon: AlertCircle,
        };
      default:
        return {
          variant: 'secondary' as const,
          label: status,
          icon: Clock,
        };
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-slate-900">專業證照</h1>
          <p className="text-slate-600 mt-1">管理您的專業認證</p>
        </div>
        <Card>
          <CardContent className="p-8">
            <ProfileSkeleton />
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* 頁面標題 */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">專業證照</h1>
          <p className="text-slate-600 mt-1">管理您的專業認證</p>
        </div>
        <Button onClick={handleAdd}>
          <Plus className="mr-2 h-4 w-4" />
          新增證照
        </Button>
      </div>

      {/* 提示訊息 */}
      <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div className="flex items-start">
          <svg
            className="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fillRule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
              clipRule="evenodd"
            />
          </svg>
          <p className="text-sm text-blue-800 leading-relaxed">
            上傳的證照需要經過管理員審核才會顯示在您的公開個人檔案上
          </p>
        </div>
      </div>

      {/* 證照列表 */}
      {certifications && certifications.length > 0 ? (
        <div className="grid md:grid-cols-2 gap-4">
          {certifications.map((cert) => {
            const statusBadge = getStatusBadge(cert.approval_status);
            const StatusIcon = statusBadge.icon;

            return (
              <Card key={cert.id} hover>
                <CardContent className="p-6">
                  <div className="flex items-start justify-between mb-4">
                    <div className="flex items-start gap-3">
                      <div className="p-3 bg-primary-50 rounded-xl">
                        <Award className="h-6 w-6 text-primary-600" />
                      </div>
                      <div className="flex-1">
                        <div className="flex items-start gap-2 mb-2">
                          <h3 className="text-lg font-semibold text-slate-900">
                            {cert.name}
                          </h3>
                          <Badge variant={statusBadge.variant} size="sm">
                            <StatusIcon className="mr-1 h-3 w-3" />
                            {statusBadge.label}
                          </Badge>
                        </div>
                        <p className="text-slate-600 font-medium">{cert.issuer}</p>
                      </div>
                    </div>

                    {/* 刪除按鈕 */}
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={() => handleDelete(cert)}
                      className="text-red-600 hover:text-red-700 hover:bg-red-50"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>

                  {/* 證照資訊 */}
                  <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm text-slate-500">
                      <Calendar className="h-4 w-4" />
                      <span>
                        發證日期：{formatDate(cert.issue_date)}
                      </span>
                    </div>

                    {cert.expiry_date && (
                      <div className="flex items-center gap-2 text-sm text-slate-500">
                        <Calendar className="h-4 w-4" />
                        <span>
                          到期日期：{formatDate(cert.expiry_date)}
                        </span>
                      </div>
                    )}

                    {/* 證照說明 */}
                    {cert.description && (
                      <div className="text-sm text-slate-600 bg-slate-50 rounded-lg p-3">
                        <p className="whitespace-pre-line">{cert.description}</p>
                      </div>
                    )}

                    {/* 證照圖片 */}
                    {cert.file_url && (
                      <div className="border-2 border-slate-200 rounded-xl overflow-hidden">
                        <img
                          src={cert.file_url}
                          alt={cert.name}
                          className="w-full h-48 object-contain bg-slate-50"
                        />
                      </div>
                    )}

                    {/* 拒絕原因 */}
                    {cert.approval_status === 'rejected' && cert.rejected_reason && (
                      <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p className="text-sm text-red-800">
                          <strong>拒絕原因：</strong>
                          {cert.rejected_reason}
                        </p>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>
      ) : (
        // 空狀態
        <Card>
          <CardContent className="py-16 text-center">
            <Award className="h-16 w-16 text-slate-300 mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-slate-900 mb-2">尚未新增專業證照</h3>
            <p className="text-slate-600 mb-6">上傳您的專業認證，提升客戶信任度</p>
            <Button onClick={handleAdd}>
              <Plus className="mr-2 h-4 w-4" />
              新增第一張證照
            </Button>
          </CardContent>
        </Card>
      )}

      {/* 證照上傳 Modal */}
      <CertificationUploadModal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSubmit={handleSubmit}
        isLoading={createMutation.isPending}
      />
    </div>
  );
}
