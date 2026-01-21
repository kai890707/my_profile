'use client';

import { useState, useMemo } from 'react';
import { Certification } from '@/types/api';
import { CertificationCard } from './certification-card';
import { EmptyState } from '@/components/ui/empty-state';
import { cn } from '@/lib/utils/cn';
import { Award } from 'lucide-react';

export interface CertificationCardsProps {
  /**
   * 證照列表
   */
  certifications: Certification[];

  /**
   * Loading 狀態
   */
  isLoading?: boolean;

  /**
   * 是否顯示篩選下拉選單
   * @default false
   */
  showFilter?: boolean;

  /**
   * 額外的 CSS class
   */
  className?: string;
}

/**
 * CertificationCards 組件 - 專業證照容器
 *
 * @example
 * <CertificationCards certifications={certifications} isLoading={false} />
 */
export function CertificationCards({
  certifications,
  isLoading = false,
  showFilter = false,
  className,
}: CertificationCardsProps) {
  const [filter, setFilter] = useState<'all' | 'approved'>('all');

  // Loading 狀態
  if (isLoading) {
    return <CertificationCardsSkeleton count={4} />;
  }

  // 空狀態
  if (certifications.length === 0) {
    return (
      <EmptyState
        icon={Award}
        title="尚無專業證照記錄"
        description="此業務員尚未新增專業證照"
      />
    );
  }

  // 篩選和排序證照
  const filteredCertifications = useMemo(() => {
    let filtered = certifications;

    // 篩選
    if (filter === 'approved') {
      filtered = filtered.filter((cert) => cert.approval_status === 'approved');
    }

    // 排序: 已驗證優先，再依發證日期倒序
    return [...filtered].sort((a, b) => {
      // 已驗證優先
      if (a.approval_status === 'approved' && b.approval_status !== 'approved') {
        return -1;
      }
      if (a.approval_status !== 'approved' && b.approval_status === 'approved') {
        return 1;
      }

      // 依發證日期倒序
      return (
        new Date(b.issue_date).getTime() - new Date(a.issue_date).getTime()
      );
    });
  }, [certifications, filter]);

  const approvedCount = certifications.filter(
    (c) => c.approval_status === 'approved'
  ).length;

  return (
    <div className={cn(className)}>
      {/* 篩選下拉選單 */}
      {showFilter && (
        <div className="flex justify-end mb-4">
          <select
            value={filter}
            onChange={(e) => setFilter(e.target.value as 'all' | 'approved')}
            className="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            aria-label="篩選證照"
          >
            <option value="all">全部 ({certifications.length})</option>
            <option value="approved">已驗證 ({approvedCount})</option>
          </select>
        </div>
      )}

      {/* 證照網格 */}
      {filteredCertifications.length > 0 ? (
        <div
          role="list"
          aria-label="專業證照列表"
          className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6"
        >
          {filteredCertifications.map((certification) => (
            <div key={certification.id} role="listitem">
              <CertificationCard certification={certification} />
            </div>
          ))}
        </div>
      ) : (
        /* 篩選後無結果 */
        <div className="text-center py-12">
          <p className="text-slate-600">目前沒有已驗證的證照</p>
        </div>
      )}
    </div>
  );
}

/**
 * CertificationCardsSkeleton - Loading 骨架屏
 */
interface CertificationCardsSkeletonProps {
  count?: number;
}

export function CertificationCardsSkeleton({
  count = 4,
}: CertificationCardsSkeletonProps) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
      {Array.from({ length: count }).map((_, index) => (
        <div
          key={index}
          className="animate-pulse bg-white rounded-2xl p-6 border-2 border-slate-100 shadow-sm"
        >
          <div className="flex items-start gap-3 mb-4">
            <div className="w-6 h-6 bg-slate-200 rounded" />
            <div className="flex-1">
              <div className="h-5 bg-slate-200 rounded w-3/4 mb-2" />
              <div className="h-4 bg-slate-200 rounded w-1/2" />
            </div>
          </div>
          <div className="h-4 bg-slate-200 rounded w-1/3 mb-4" />
          <div className="space-y-2">
            <div className="h-3 bg-slate-200 rounded w-full" />
            <div className="h-3 bg-slate-200 rounded w-5/6" />
          </div>
        </div>
      ))}
    </div>
  );
}
