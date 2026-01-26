import { Badge } from '@/components/ui/badge';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';
import { cn } from '@/lib/utils';

export interface TrendBadgeProps {
  value: number;
  isPositive: boolean;
  size?: 'sm' | 'md' | 'lg';
}

const sizeStyles = {
  sm: 'text-xs px-2 py-0.5',
  md: 'text-sm px-3 py-1',
  lg: 'text-base px-4 py-1.5',
};

export function TrendBadge({ value, isPositive, size = 'md' }: TrendBadgeProps) {
  const isZero = value === 0;

  return (
    <Badge
      variant={isZero ? 'secondary' : isPositive ? 'success' : 'error'}
      className={cn('flex items-center gap-1 w-fit', sizeStyles[size])}
    >
      {isZero ? (
        <Minus className="h-3 w-3" />
      ) : isPositive ? (
        <TrendingUp className="h-3 w-3" />
      ) : (
        <TrendingDown className="h-3 w-3" />
      )}
      <span>
        {isPositive && value > 0 ? '+' : ''}
        {value.toFixed(1)}%
      </span>
    </Badge>
  );
}
