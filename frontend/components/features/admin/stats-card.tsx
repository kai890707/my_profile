import { type LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';

export interface StatsCardProps {
  title: string;
  value: number | string;
  icon: LucideIcon;
  color?: 'primary' | 'success' | 'warning' | 'error' | 'info';
  subtitle?: string;
  trend?: {
    value: number;
    label: string;
    isPositive: boolean;
  };
}

const colorClasses = {
  primary: {
    bg: 'bg-primary-50',
    icon: 'text-primary-600',
    value: 'text-primary-900',
  },
  success: {
    bg: 'bg-green-50',
    icon: 'text-green-600',
    value: 'text-green-900',
  },
  warning: {
    bg: 'bg-yellow-50',
    icon: 'text-yellow-600',
    value: 'text-yellow-900',
  },
  error: {
    bg: 'bg-red-50',
    icon: 'text-red-600',
    value: 'text-red-900',
  },
  info: {
    bg: 'bg-blue-50',
    icon: 'text-blue-600',
    value: 'text-blue-900',
  },
};

/**
 * 統計卡片組件
 *
 * @example
 * <StatsCard
 *   title="總業務員數"
 *   value={150}
 *   icon={Users}
 *   color="primary"
 *   trend={{ value: 12, label: "較上月", isPositive: true }}
 * />
 */
export function StatsCard({
  title,
  value,
  icon: Icon,
  color = 'primary',
  subtitle,
  trend,
}: StatsCardProps) {
  const classes = colorClasses[color];

  return (
    <Card hover>
      <CardContent className="p-6">
        <div className="flex items-start justify-between">
          <div className="flex-1">
            <p className="text-sm font-medium text-slate-600 mb-2">{title}</p>
            <p className={`text-3xl font-bold ${classes.value}`}>
              {typeof value === 'number' ? value.toLocaleString() : value}
            </p>

            {subtitle && <p className="text-sm text-slate-500 mt-1">{subtitle}</p>}

            {trend && (
              <div className="flex items-center gap-1 mt-2">
                <span
                  className={`text-sm font-medium ${
                    trend.isPositive ? 'text-green-600' : 'text-red-600'
                  }`}
                >
                  {trend.isPositive ? '+' : ''}
                  {trend.value}%
                </span>
                <span className="text-xs text-slate-500">{trend.label}</span>
              </div>
            )}
          </div>

          <div className={`p-3 ${classes.bg} rounded-xl`}>
            <Icon className={`h-6 w-6 ${classes.icon}`} />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
