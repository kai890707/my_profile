'use client';

import { Experience } from '@/types/api';
import { ExperienceItem } from './experience-item';
import { EmptyState } from '@/components/ui/empty-state';
import { cn } from '@/lib/utils/cn';
import { Briefcase } from 'lucide-react';

export interface ExperienceTimelineProps {
  /**
   * 工作經驗列表
   */
  experiences: Experience[];

  /**
   * Loading 狀態
   */
  isLoading?: boolean;

  /**
   * 額外的 CSS class
   */
  className?: string;
}

/**
 * 排序工作經驗 (最新在上)
 * 規則:
 * 1. 在職中 (end_date === null) 的排在最前
 * 2. 依開始日期倒序排列
 */
function sortExperiences(experiences: Experience[]): Experience[] {
  return [...experiences].sort((a, b) => {
    // 在職中的優先
    if (a.end_date === null && b.end_date !== null) return -1;
    if (a.end_date !== null && b.end_date === null) return 1;

    // 依開始日期倒序
    return new Date(b.start_date).getTime() - new Date(a.start_date).getTime();
  });
}

/**
 * ExperienceTimeline 組件 - 工作經驗時間軸
 *
 * @example
 * <ExperienceTimeline experiences={experiences} isLoading={false} />
 */
export function ExperienceTimeline({
  experiences,
  isLoading = false,
  className,
}: ExperienceTimelineProps) {
  // Loading 狀態
  if (isLoading) {
    return <ExperienceTimelineSkeleton count={3} />;
  }

  // 空狀態
  if (experiences.length === 0) {
    return (
      <EmptyState
        icon={Briefcase}
        title="尚無工作經驗記錄"
        description="此業務員尚未新增工作經驗"
      />
    );
  }

  // 排序經驗
  const sortedExperiences = sortExperiences(experiences);

  return (
    <div className={cn('relative', className)}>
      {/* 時間軸線 */}
      <div
        className="absolute left-6 top-0 bottom-0 w-0.5 bg-primary-200"
        aria-hidden="true"
      />

      {/* 經驗列表 */}
      <ol role="list" aria-label="工作經驗時間軸" className="space-y-6">
        {sortedExperiences.map((experience, index) => (
          <ExperienceItem
            key={experience.id}
            experience={experience}
            isLast={index === sortedExperiences.length - 1}
          />
        ))}
      </ol>
    </div>
  );
}

/**
 * ExperienceTimelineSkeleton - Loading 骨架屏
 */
interface ExperienceTimelineSkeletonProps {
  count?: number;
}

export function ExperienceTimelineSkeleton({
  count = 3,
}: ExperienceTimelineSkeletonProps) {
  return (
    <div className="relative space-y-6">
      {/* 時間軸線 */}
      <div className="absolute left-6 top-0 bottom-0 w-0.5 bg-slate-200" />

      {/* 骨架項目 */}
      {Array.from({ length: count }).map((_, index) => (
        <div key={index} className="relative pl-16">
          {/* 時間節點 */}
          <div className="absolute left-3 top-2 w-6 h-6 rounded-full bg-slate-200 ring-4 ring-white" />

          {/* 卡片骨架 */}
          <div className="animate-pulse bg-white rounded-xl p-6 border border-slate-100 shadow-sm">
            <div className="h-5 bg-slate-200 rounded w-3/4 mb-3" />
            <div className="h-4 bg-slate-200 rounded w-1/2 mb-4" />
            <div className="h-4 bg-slate-200 rounded w-1/3 mb-4" />
            <div className="space-y-2">
              <div className="h-3 bg-slate-200 rounded w-full" />
              <div className="h-3 bg-slate-200 rounded w-5/6" />
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
