'use client';

import { useState } from 'react';
import { Certification } from '@/types/api';
import { cn } from '@/lib/utils/cn';
import {
  Award,
  Calendar,
  CheckCircle2,
  Clock,
  ChevronDown,
  ChevronUp,
  FileText,
  ExternalLink,
} from 'lucide-react';

export interface CertificationCardProps {
  /**
   * 證照資料
   */
  certification: Certification;

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
  return date.toLocaleDateString('zh-TW', {
    year: 'numeric',
    month: '2-digit',
  });
}

/**
 * 檢查證照是否即將過期或已過期
 * @returns 'valid' | 'expiring' | 'expired'
 */
function checkExpiryStatus(expiryDate: string | null): {
  status: 'valid' | 'expiring' | 'expired';
  message?: string;
} {
  if (!expiryDate) {
    return { status: 'valid' };
  }

  const now = new Date();
  const expiry = new Date(expiryDate);
  const monthsUntilExpiry =
    (expiry.getTime() - now.getTime()) / (1000 * 60 * 60 * 24 * 30);

  if (monthsUntilExpiry < 0) {
    return { status: 'expired', message: '已過期' };
  } else if (monthsUntilExpiry < 3) {
    return { status: 'expiring', message: '即將過期' };
  } else {
    return { status: 'valid' };
  }
}

/**
 * CertificationCard 組件 - 單張專業證照卡片
 *
 * @example
 * <CertificationCard certification={certificationData} />
 */
export function CertificationCard({
  certification,
  className,
}: CertificationCardProps) {
  const [isExpanded, setIsExpanded] = useState(false);

  const isApproved = certification.approval_status === 'approved';
  const isPending = certification.approval_status === 'pending';
  const expiryStatus = checkExpiryStatus(certification.expiry_date);

  // 判斷是否需要顯示展開按鈕
  const shouldShowExpandButton =
    certification.description && certification.description.length > 100;

  return (
    <article
      className={cn(
        'bg-white rounded-2xl p-6',
        'border-2 border-slate-100',
        'shadow-sm hover:shadow-lg',
        'hover:-translate-y-1 hover:border-primary-200',
        'transition-all duration-200 ease-out',
        className
      )}
    >
      {/* 標題行 */}
      <div className="flex items-start gap-3 mb-3">
        {/* 徽章圖示 */}
        <Award
          className="h-6 w-6 text-amber-500 flex-shrink-0 mt-0.5"
          aria-hidden="true"
        />

        {/* 證照名稱 */}
        <div className="flex-1 min-w-0">
          <h3 className="text-lg font-bold text-slate-900 line-clamp-2 mb-1">
            {certification.name}
          </h3>
        </div>

        {/* 驗證標記 */}
        {isApproved && (
          <CheckCircle2
            className="h-5 w-5 text-success-600 flex-shrink-0"
            aria-label="已驗證"
          />
        )}
        {isPending && (
          <Clock
            className="h-5 w-5 text-warning-600 flex-shrink-0"
            aria-label="審核中"
          />
        )}
      </div>

      {/* 發證機構 */}
      <p className="text-sm text-slate-600 mb-3 truncate">
        {certification.issuer}
      </p>

      {/* 日期行 */}
      <div className="flex items-center gap-2 text-xs text-slate-500 mb-3">
        <Calendar className="h-4 w-4" aria-hidden="true" />
        <time dateTime={certification.issue_date}>
          {formatDate(certification.issue_date)}
        </time>
        <span>-</span>
        {certification.expiry_date ? (
          <>
            <time dateTime={certification.expiry_date}>
              {formatDate(certification.expiry_date)}
            </time>
            {expiryStatus.status === 'expiring' && (
              <span className="text-warning-600 font-medium">
                ({expiryStatus.message})
              </span>
            )}
            {expiryStatus.status === 'expired' && (
              <span className="text-error-600 font-medium">
                ({expiryStatus.message})
              </span>
            )}
          </>
        ) : (
          <span className="text-slate-500">永久有效</span>
        )}
      </div>

      {/* 描述 */}
      {certification.description && (
        <div className="mt-3">
          <p
            className={cn(
              'text-sm text-slate-600 leading-relaxed',
              !isExpanded && 'line-clamp-2'
            )}
          >
            {certification.description}
          </p>

          {/* 展開按鈕 */}
          {shouldShowExpandButton && (
            <button
              onClick={() => setIsExpanded(!isExpanded)}
              aria-expanded={isExpanded}
              aria-controls={`cert-detail-${certification.id}`}
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

      {/* 查看證書按鈕 */}
      {certification.file_url && (
        <div className="mt-4 pt-4 border-t border-slate-100">
          <a
            href={certification.file_url}
            target="_blank"
            rel="noopener noreferrer"
            className={cn(
              'inline-flex items-center gap-2',
              'px-4 py-2 text-sm font-medium',
              'text-primary-600 border-2 border-primary-500 rounded-lg',
              'hover:bg-primary-50 hover:border-primary-600',
              'transition-all duration-200',
              'focus-visible:outline-none',
              'focus-visible:ring-2',
              'focus-visible:ring-primary-500',
              'focus-visible:ring-offset-2'
            )}
          >
            <FileText className="h-4 w-4" aria-hidden="true" />
            查看證書
            <ExternalLink className="h-3 w-3" aria-hidden="true" />
          </a>
        </div>
      )}
    </article>
  );
}
