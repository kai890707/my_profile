'use client';

import { useState } from 'react';
import { Experience } from '@/types/api';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils/cn';
import {
  Briefcase,
  Calendar,
  CheckCircle2,
  ChevronDown,
  ChevronUp,
} from 'lucide-react';

export interface ExperienceItemProps {
  /**
   * 工作經驗資料
   */
  experience: Experience;

  /**
   * 是否為最後一項 (用於判斷時間節點樣式)
   */
  isLast?: boolean;

  /**
   * 額外的 CSS class
   */
  className?: string;
}

/**
 * 格式化日期為 YYYY/MM
 */
function formatDate(dateString: string): string {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString();
  return `${year}/${month}`;
}

/**
 * 計算年資
 * @returns "X年X個月" 或 "X個月"
 */
function calculateDuration(startDate: string, endDate: string | null): string {
  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : new Date();

  const months =
    (end.getFullYear() - start.getFullYear()) * 12 +
    (end.getMonth() - start.getMonth());

  const years = Math.floor(months / 12);
  const remainingMonths = months % 12;

  if (years === 0) {
    return `${remainingMonths}個月`;
  } else if (remainingMonths === 0) {
    return `${years}年`;
  } else {
    return `${years}年${remainingMonths}個月`;
  }
}

/**
 * ExperienceItem 組件 - 單個工作經驗項目
 *
 * @example
 * <ExperienceItem experience={experienceData} />
 */
export function ExperienceItem({
  experience,
  isLast = false,
  className,
}: ExperienceItemProps) {
  const [isExpanded, setIsExpanded] = useState(false);

  const isOngoing = experience.end_date === null;
  const isApproved = experience.approval_status === 'approved';
  const duration = calculateDuration(experience.start_date, experience.end_date);

  // 取得公司名稱（處理可能的 company object）
  const companyName = typeof experience.company === 'string'
    ? experience.company
    : experience.company?.name || '';

  // 判斷是否需要顯示展開按鈕
  const shouldShowExpandButton =
    experience.description && experience.description.length > 200;

  return (
    <li role="listitem" className={cn('relative pl-12 md:pl-16', className)}>
      {/* 時間節點 */}
      <div
        className={cn(
          'absolute left-3 md:left-3 top-2',
          'w-3 h-3 md:w-3 md:h-3 rounded-full',
          'ring-4 ring-white',
          isOngoing
            ? 'bg-primary-500' // 在職中: 實心藍色
            : isLast
            ? 'bg-white border-2 border-slate-300' // 最後一個: 空心灰色
            : 'bg-primary-300' // 已離職: 實心淡藍色
        )}
        aria-hidden="true"
      />

      {/* 工作資訊卡片 */}
      <article
        className={cn(
          'bg-white rounded-xl p-4 md:p-6',
          'border border-slate-100 shadow-sm',
          'hover:shadow-md hover:-translate-y-1',
          'transition-all duration-200 ease-out'
        )}
      >
        {/* 標題行 */}
        <div className="flex items-start justify-between mb-2">
          <div className="flex items-start gap-2 flex-1">
            <Briefcase
              className="h-5 w-5 text-primary-500 flex-shrink-0 mt-0.5"
              aria-hidden="true"
            />
            <div className="flex-1 min-w-0">
              <h3 className="text-base md:text-lg font-semibold text-slate-900 mb-1">
                {experience.position}
              </h3>
              <p className="text-sm md:text-base text-slate-600 truncate">
                {companyName}
              </p>
            </div>
          </div>

          {/* 驗證標記 */}
          {isApproved && (
            <Badge variant="success" size="sm" className="flex-shrink-0">
              <CheckCircle2 className="mr-1 h-3 w-3" aria-hidden="true" />
              已驗證
            </Badge>
          )}
        </div>

        {/* 日期行 */}
        <div className="flex items-center gap-2 text-xs md:text-sm text-slate-600 mb-3">
          <Calendar className="h-4 w-4" aria-hidden="true" />
          <time dateTime={experience.start_date}>
            {formatDate(experience.start_date)}
          </time>
          <span>-</span>
          {isOngoing ? (
            <>
              <time dateTime={new Date().toISOString()}>至今</time>
              <span className="inline-flex items-center gap-1">
                <span className="w-2 h-2 rounded-full bg-success-500 animate-pulse" />
                <span className="text-success-600 font-medium">在職中</span>
              </span>
            </>
          ) : (
            <time dateTime={experience.end_date!}>
              {formatDate(experience.end_date!)}
            </time>
          )}
          <span className="text-slate-500">({duration})</span>
        </div>

        {/* 描述 */}
        {experience.description && (
          <div className="mt-3">
            <p
              className={cn(
                'text-sm md:text-base text-slate-700 leading-relaxed whitespace-pre-line',
                !isExpanded && 'line-clamp-3'
              )}
            >
              {experience.description}
            </p>

            {/* 展開按鈕 */}
            {shouldShowExpandButton && (
              <button
                onClick={() => setIsExpanded(!isExpanded)}
                aria-expanded={isExpanded}
                aria-controls={`experience-detail-${experience.id}`}
                className={cn(
                  'mt-2 text-sm font-medium text-primary-600',
                  'hover:text-primary-700 hover:underline',
                  'transition-colors duration-150',
                  'flex items-center gap-1',
                  'focus-visible:outline-none',
                  'focus-visible:ring-2',
                  'focus-visible:ring-primary-500',
                  'focus-visible:ring-offset-2',
                  'rounded-lg'
                )}
              >
                {isExpanded ? (
                  <>
                    收合
                    <ChevronUp className="h-4 w-4" aria-hidden="true" />
                  </>
                ) : (
                  <>
                    展開更多
                    <ChevronDown className="h-4 w-4" aria-hidden="true" />
                  </>
                )}
              </button>
            )}
          </div>
        )}
      </article>
    </li>
  );
}
