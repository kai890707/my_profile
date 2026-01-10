'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useSalespersonApplications, useApproveSalesperson, useRejectSalesperson } from '@/hooks/useAdmin';
import type { SalespersonApplication } from '@/lib/api/admin';

// Reject form schema
const rejectSchema = z.object({
  rejection_reason: z.string().min(5, '拒絕原因至少需要 5 個字元'),
  reapply_days: z.number().min(1, '等待天數至少需要 1 天').max(365, '等待天數不能超過 365 天'),
});

type RejectForm = z.infer<typeof rejectSchema>;

interface RejectModalProps {
  application: SalespersonApplication | null;
  onClose: () => void;
  onConfirm: (data: RejectForm) => void;
  isLoading: boolean;
}

function RejectModal({ application, onClose, onConfirm, isLoading }: RejectModalProps) {
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<RejectForm>({
    resolver: zodResolver(rejectSchema),
    defaultValues: {
      reapply_days: 7,
    },
  });

  const onSubmit = (data: RejectForm) => {
    onConfirm(data);
    reset();
  };

  const handleClose = () => {
    reset();
    onClose();
  };

  if (!application) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-xl shadow-xl max-w-lg w-full">
        <div className="p-6 border-b border-slate-200">
          <h2 className="text-xl font-bold text-slate-900">拒絕業務員申請</h2>
          <p className="text-sm text-slate-600 mt-1">
            申請人：{application.salesperson_profile?.full_name || application.name}
          </p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="p-6 space-y-6">
          <div className="space-y-2">
            <label className="block text-sm font-medium text-slate-700">
              拒絕原因 <span className="text-red-600">*</span>
            </label>
            <textarea
              rows={4}
              placeholder="請說明拒絕的原因，以便申請人了解並改進"
              className="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
              {...register('rejection_reason')}
            />
            {errors.rejection_reason && (
              <p className="text-sm text-red-600">{errors.rejection_reason.message}</p>
            )}
          </div>

          <Input
            label="等待天數"
            type="number"
            min={1}
            max={365}
            placeholder="7"
            error={errors.reapply_days?.message}
            helperText="申請人需要等待多少天後才能重新申請"
            required
            {...register('reapply_days', { valueAsNumber: true })}
          />

          <div className="flex gap-3">
            <Button
              type="button"
              variant="outline"
              onClick={handleClose}
              className="flex-1"
              disabled={isLoading}
            >
              取消
            </Button>
            <Button
              type="submit"
              className="flex-1 bg-red-600 hover:bg-red-700"
              isLoading={isLoading}
            >
              {isLoading ? '送出中...' : '確認拒絕'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default function SalespersonApplicationsPage() {
  const { data: applications, isLoading } = useSalespersonApplications();
  const approveMutation = useApproveSalesperson();
  const rejectMutation = useRejectSalesperson();

  const [rejectingApplication, setRejectingApplication] = useState<SalespersonApplication | null>(null);

  const handleApprove = (userId: number) => {
    if (confirm('確定要批准此業務員申請嗎？')) {
      approveMutation.mutate(userId);
    }
  };

  const handleReject = (application: SalespersonApplication) => {
    setRejectingApplication(application);
  };

  const handleConfirmReject = (data: RejectForm) => {
    if (!rejectingApplication) return;

    rejectMutation.mutate(
      {
        userId: rejectingApplication.id,
        data: {
          rejection_reason: data.rejection_reason,
          reapply_days: data.reapply_days,
        },
      },
      {
        onSuccess: () => {
          setRejectingApplication(null);
        },
      }
    );
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  const pendingApplications = applications?.filter((app) => app.salesperson_status === 'pending') || [];

  return (
    <div className="max-w-7xl mx-auto py-8 px-4">
      <div className="space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-foreground">業務員申請審核</h1>
            <p className="text-slate-600 mt-1">
              共有 <span className="font-semibold text-primary-600">{pendingApplications.length}</span> 個待審核申請
            </p>
          </div>
        </div>

        {/* Applications Table */}
        {pendingApplications.length === 0 ? (
          <div className="bg-white border border-slate-200 rounded-xl p-12 text-center">
            <svg className="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 className="text-lg font-semibold text-slate-900 mb-2">沒有待審核的申請</h3>
            <p className="text-sm text-slate-600">所有業務員申請都已處理完畢</p>
          </div>
        ) : (
          <div className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <table className="w-full">
              <thead className="bg-slate-50 border-b border-slate-200">
                <tr>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    申請人
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    聯絡資訊
                  </th>
                  <th className="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    申請時間
                  </th>
                  <th className="px-6 py-4 text-right text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    操作
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-200">
                {pendingApplications.map((application) => (
                  <tr key={application.id} className="hover:bg-slate-50 transition-colors">
                    <td className="px-6 py-4">
                      <div>
                        <p className="font-semibold text-slate-900">
                          {application.salesperson_profile?.full_name || application.name}
                        </p>
                        <p className="text-sm text-slate-600">{application.email}</p>
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <div className="text-sm">
                        <p className="text-slate-900">
                          {application.salesperson_profile?.phone || '未提供'}
                        </p>
                        {application.salesperson_profile?.specialties && (
                          <p className="text-slate-600 mt-1">
                            專長：{application.salesperson_profile.specialties}
                          </p>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4">
                      <p className="text-sm text-slate-900">
                        {new Date(application.salesperson_applied_at).toLocaleDateString('zh-TW')}
                      </p>
                      <p className="text-xs text-slate-600">
                        {new Date(application.salesperson_applied_at).toLocaleTimeString('zh-TW')}
                      </p>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex gap-2 justify-end">
                        <button
                          onClick={() => handleApprove(application.id)}
                          disabled={approveMutation.isPending}
                          className="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                          批准
                        </button>
                        <button
                          onClick={() => handleReject(application)}
                          disabled={rejectMutation.isPending}
                          className="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white text-sm font-semibold rounded-lg transition-colors"
                        >
                          拒絕
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* Reject Modal */}
      <RejectModal
        application={rejectingApplication}
        onClose={() => setRejectingApplication(null)}
        onConfirm={handleConfirmReject}
        isLoading={rejectMutation.isPending}
      />
    </div>
  );
}
