'use client';

import { useState } from 'react';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ProfileSkeleton } from '@/components/ui/skeleton';
import { Plus, Briefcase, Calendar, Edit, Trash2, CheckCircle2, Clock, AlertCircle } from 'lucide-react';
import { ExperienceFormModal } from '@/components/features/dashboard/experience-form-modal';
import {
  useExperiences,
  useCreateExperience,
  useUpdateExperience,
  useDeleteExperience,
} from '@/hooks/useSalesperson';
import { formatDate } from '@/lib/utils/format';
import type { Experience } from '@/types/api';
import { toast } from 'sonner';

export default function ExperiencesPage() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingExperience, setEditingExperience] = useState<Experience | null>(null);

  // 獲取工作經驗列表
  const { data: experiences, isLoading } = useExperiences();

  // Mutations
  const createMutation = useCreateExperience();
  const updateMutation = useUpdateExperience();
  const deleteMutation = useDeleteExperience();

  // 開啟新增 Modal
  const handleAdd = () => {
    setEditingExperience(null);
    setIsModalOpen(true);
  };

  // 開啟編輯 Modal
  const handleEdit = (experience: Experience) => {
    setEditingExperience(experience);
    setIsModalOpen(true);
  };

  // 處理表單提交
  const handleSubmit = (data: any) => {
    if (editingExperience) {
      // 更新現有經驗
      updateMutation.mutate(
        { id: editingExperience.id, data },
        {
          onSuccess: () => {
            setIsModalOpen(false);
            setEditingExperience(null);
          },
        }
      );
    } else {
      // 新增經驗
      createMutation.mutate(data, {
        onSuccess: () => {
          setIsModalOpen(false);
        },
      });
    }
  };

  // 處理刪除
  const handleDelete = (experience: Experience) => {
    if (
      confirm(`確定要刪除「${experience.company} - ${experience.position}」這筆工作經驗嗎？`)
    ) {
      deleteMutation.mutate(experience.id);
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
          <h1 className="text-2xl font-bold text-slate-900">工作經驗</h1>
          <p className="text-slate-600 mt-1">管理您的工作經歷</p>
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
          <h1 className="text-2xl font-bold text-slate-900">工作經驗</h1>
          <p className="text-slate-600 mt-1">管理您的工作經歷</p>
        </div>
        <Button onClick={handleAdd}>
          <Plus className="mr-2 h-4 w-4" />
          新增經驗
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
            新增的工作經驗需要經過管理員審核才會顯示在您的公開個人檔案上
          </p>
        </div>
      </div>

      {/* 工作經驗列表 */}
      {experiences && experiences.length > 0 ? (
        <div className="space-y-4">
          {experiences.map((exp) => {
            const statusBadge = getStatusBadge(exp.approval_status);
            const StatusIcon = statusBadge.icon;

            return (
              <Card key={exp.id} hover>
                <CardContent className="p-6">
                  <div className="flex items-start justify-between mb-4">
                    <div className="flex items-start gap-4">
                      <div className="p-3 bg-primary-50 rounded-xl">
                        <Briefcase className="h-6 w-6 text-primary-600" />
                      </div>
                      <div className="flex-1">
                        <div className="flex items-start gap-3 mb-2">
                          <h3 className="text-lg font-semibold text-slate-900">
                            {exp.position}
                          </h3>
                          <Badge variant={statusBadge.variant} size="sm">
                            <StatusIcon className="mr-1 h-3 w-3" />
                            {statusBadge.label}
                          </Badge>
                        </div>
                        <p className="text-slate-600 font-medium">{exp.company}</p>
                        <div className="flex items-center gap-2 text-sm text-slate-500 mt-2">
                          <Calendar className="h-4 w-4" />
                          <span>
                            {formatDate(exp.start_date)} -{' '}
                            {exp.end_date ? formatDate(exp.end_date) : '至今'}
                          </span>
                        </div>
                      </div>
                    </div>

                    {/* 操作按鈕 */}
                    <div className="flex gap-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleEdit(exp)}
                      >
                        <Edit className="h-4 w-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handleDelete(exp)}
                        className="text-red-600 hover:text-red-700 hover:bg-red-50"
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>

                  {/* 工作描述 */}
                  {exp.description && (
                    <div className="pl-16">
                      <p className="text-sm text-slate-600 whitespace-pre-line">
                        {exp.description}
                      </p>
                    </div>
                  )}

                  {/* 拒絕原因 */}
                  {exp.approval_status === 'rejected' && exp.rejected_reason && (
                    <div className="pl-16 mt-3">
                      <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                        <p className="text-sm text-red-800">
                          <strong>拒絕原因：</strong>
                          {exp.rejected_reason}
                        </p>
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>
            );
          })}
        </div>
      ) : (
        // 空狀態
        <Card>
          <CardContent className="py-16 text-center">
            <Briefcase className="h-16 w-16 text-slate-300 mx-auto mb-4" />
            <h3 className="text-xl font-semibold text-slate-900 mb-2">尚未新增工作經驗</h3>
            <p className="text-slate-600 mb-6">開始建立您的專業經歷，讓客戶更了解您</p>
            <Button onClick={handleAdd}>
              <Plus className="mr-2 h-4 w-4" />
              新增第一筆經驗
            </Button>
          </CardContent>
        </Card>
      )}

      {/* 工作經驗表單 Modal */}
      <ExperienceFormModal
        isOpen={isModalOpen}
        onClose={() => {
          setIsModalOpen(false);
          setEditingExperience(null);
        }}
        onSubmit={handleSubmit}
        experience={editingExperience}
        isLoading={createMutation.isPending || updateMutation.isPending}
      />
    </div>
  );
}
