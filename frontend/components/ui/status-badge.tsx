import { Badge } from '@/components/ui/badge';
import { CheckCircle2, Clock, AlertCircle, type LucideIcon } from 'lucide-react';
import type { ApprovalStatus } from '@/types/api';

interface StatusBadgeConfig {
  variant: 'success' | 'warning' | 'error' | 'secondary';
  label: string;
  icon: LucideIcon;
  color: string;
  bg: string;
}

export interface StatusBadgeProps {
  status: ApprovalStatus;
  size?: 'sm' | 'md' | 'lg';
  showIcon?: boolean;
}

/**
 * 取得審核狀態配置
 */
export function getStatusConfig(status: ApprovalStatus): StatusBadgeConfig {
  switch (status) {
    case 'approved':
      return {
        variant: 'success',
        label: '已通過',
        icon: CheckCircle2,
        color: 'text-green-600',
        bg: 'bg-green-50',
      };
    case 'pending':
      return {
        variant: 'warning',
        label: '審核中',
        icon: Clock,
        color: 'text-yellow-600',
        bg: 'bg-yellow-50',
      };
    case 'rejected':
      return {
        variant: 'error',
        label: '已拒絕',
        icon: AlertCircle,
        color: 'text-red-600',
        bg: 'bg-red-50',
      };
    default:
      return {
        variant: 'secondary',
        label: status,
        icon: Clock,
        color: 'text-slate-600',
        bg: 'bg-slate-50',
      };
  }
}

/**
 * 審核狀態徽章組件
 *
 * @example
 * <StatusBadge status="approved" />
 * <StatusBadge status="pending" size="sm" />
 * <StatusBadge status="rejected" showIcon={false} />
 */
export function StatusBadge({
  status,
  size = 'sm',
  showIcon = true,
}: StatusBadgeProps) {
  const config = getStatusConfig(status);
  const Icon = config.icon;

  return (
    <Badge variant={config.variant} size={size}>
      {showIcon && <Icon className="mr-1 h-3 w-3" />}
      {config.label}
    </Badge>
  );
}

/**
 * 審核狀態圖標 (用於大型展示區域)
 */
export function StatusIcon({
  status,
  className = 'h-6 w-6',
}: {
  status: ApprovalStatus;
  className?: string;
}) {
  const config = getStatusConfig(status);
  const Icon = config.icon;

  return (
    <div className={`p-3 ${config.bg} rounded-xl inline-flex`}>
      <Icon className={`${className} ${config.color}`} />
    </div>
  );
}

/**
 * 拒絕原因顯示組件
 */
export function RejectedReason({ reason }: { reason: string }) {
  return (
    <div className="bg-red-50 border border-red-200 rounded-lg p-3">
      <p className="text-sm text-red-800">
        <strong>拒絕原因：</strong>
        {reason}
      </p>
    </div>
  );
}
