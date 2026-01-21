import { LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils/cn';

export interface EmptyStateProps {
  /**
   * 圖示組件 (Lucide Icon)
   */
  icon: LucideIcon;

  /**
   * 標題文字
   */
  title: string;

  /**
   * 描述文字
   */
  description: string;

  /**
   * 額外的 CSS class
   */
  className?: string;
}

/**
 * EmptyState 組件 - 用於顯示空狀態
 *
 * @example
 * <EmptyState
 *   icon={Briefcase}
 *   title="尚無工作經驗記錄"
 *   description="此業務員尚未新增工作經驗"
 * />
 */
export function EmptyState({
  icon: Icon,
  title,
  description,
  className,
}: EmptyStateProps) {
  return (
    <div className={cn('text-center py-12', className)}>
      <Icon className="mx-auto h-12 w-12 text-slate-300 mb-4" aria-hidden="true" />
      <p className="text-base font-medium text-slate-600 mb-2">{title}</p>
      <p className="text-sm text-slate-500">{description}</p>
    </div>
  );
}
