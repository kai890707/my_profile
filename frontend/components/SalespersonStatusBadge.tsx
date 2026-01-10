'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useSalespersonStatus } from '@/hooks/useSalesperson';

interface SalespersonStatusBadgeProps {
  showReapplyButton?: boolean;
}

export default function SalespersonStatusBadge({ showReapplyButton = true }: SalespersonStatusBadgeProps) {
  const router = useRouter();
  const { data: status, isLoading } = useSalespersonStatus();
  const [daysRemaining, setDaysRemaining] = useState<number | null>(null);

  // Calculate days remaining for reapply countdown
  useEffect(() => {
    if (status?.can_reapply_at && !status.can_reapply) {
      const calculateDays = () => {
        const reapplyDate = new Date(status.can_reapply_at!);
        const now = new Date();
        const diff = reapplyDate.getTime() - now.getTime();
        const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
        setDaysRemaining(days > 0 ? days : 0);
      };

      calculateDays();
      const interval = setInterval(calculateDays, 1000 * 60 * 60); // Update every hour

      return () => clearInterval(interval);
    }
  }, [status]);

  if (isLoading) {
    return (
      <div className="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 animate-pulse">
        <div className="w-16 h-4 bg-slate-200 rounded"></div>
      </div>
    );
  }

  if (!status || status.role !== 'salesperson') {
    return null;
  }

  // Pending status
  if (status.salesperson_status === 'pending') {
    return (
      <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <div className="flex items-start">
          <div className="flex-shrink-0">
            <svg className="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3 flex-1">
            <h3 className="text-sm font-semibold text-yellow-900">
              審核中
            </h3>
            <div className="mt-2 text-sm text-yellow-800">
              <p>您的業務員申請正在審核中，請耐心等待。</p>
              {status.salesperson_applied_at && (
                <p className="mt-1 text-xs text-yellow-700">
                  申請時間：{new Date(status.salesperson_applied_at).toLocaleDateString('zh-TW')}
                </p>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }

  // Approved status
  if (status.salesperson_status === 'approved') {
    return (
      <div className="inline-flex items-center px-4 py-2 rounded-full bg-green-100 border border-green-300">
        <svg className="h-5 w-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
        </svg>
        <span className="text-sm font-semibold text-green-900">已認證業務員</span>
      </div>
    );
  }

  // Rejected status
  if (status.salesperson_status === 'rejected') {
    return (
      <div className="bg-red-50 border border-red-200 rounded-xl p-4">
        <div className="flex items-start">
          <div className="flex-shrink-0">
            <svg className="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
          </div>
          <div className="ml-3 flex-1">
            <h3 className="text-sm font-semibold text-red-900">
              申請未通過
            </h3>

            {status.rejection_reason && (
              <div className="mt-2 p-3 bg-red-100 rounded-lg">
                <p className="text-sm font-medium text-red-900 mb-1">拒絕原因：</p>
                <p className="text-sm text-red-800">{status.rejection_reason}</p>
              </div>
            )}

            {/* Reapply Section */}
            <div className="mt-4">
              {status.can_reapply ? (
                // Can reapply now
                <>
                  <p className="text-sm text-red-800 mb-3">
                    您現在可以重新申請業務員資格。
                  </p>
                  {showReapplyButton && (
                    <button
                      onClick={() => router.push('/dashboard/salesperson/upgrade')}
                      className="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors"
                    >
                      <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      </svg>
                      重新申請
                    </button>
                  )}
                </>
              ) : (
                // Cannot reapply yet - show countdown
                <>
                  <p className="text-sm text-red-800">
                    您需要等待一段時間後才能重新申請。
                  </p>
                  {daysRemaining !== null && daysRemaining > 0 && (
                    <div className="mt-2 inline-flex items-center px-3 py-2 bg-red-100 rounded-lg">
                      <svg className="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clipRule="evenodd" />
                      </svg>
                      <span className="text-sm font-semibold text-red-900">
                        還需等待 {daysRemaining} 天
                      </span>
                    </div>
                  )}
                  {status.can_reapply_at && (
                    <p className="mt-2 text-xs text-red-700">
                      可重新申請日期：{new Date(status.can_reapply_at).toLocaleDateString('zh-TW')}
                    </p>
                  )}
                </>
              )}
            </div>
          </div>
        </div>
      </div>
    );
  }

  return null;
}
