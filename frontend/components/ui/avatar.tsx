import React from 'react';
import { cn } from '@/lib/utils';
import { User } from 'lucide-react';
import Image from 'next/image';

interface AvatarProps {
  src?: string | null;
  alt?: string;
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
  className?: string;
  fallback?: string;
  status?: 'online' | 'offline' | 'away' | 'busy';
}

export function Avatar({
  src,
  alt = 'Avatar',
  size = 'md',
  className,
  fallback,
  status,
}: AvatarProps) {
  const sizes = {
    xs: 'h-8 w-8',
    sm: 'h-10 w-10',
    md: 'h-12 w-12',
    lg: 'h-16 w-16',
    xl: 'h-20 w-20',
    '2xl': 'h-24 w-24',
  };

  const iconSizes = {
    xs: 'h-4 w-4',
    sm: 'h-5 w-5',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-10 w-10',
    '2xl': 'h-12 w-12',
  };

  const statusColors = {
    online: 'bg-success-500',
    offline: 'bg-slate-400',
    away: 'bg-warning-500',
    busy: 'bg-error-500',
  };

  const statusSizes = {
    xs: 'h-2 w-2',
    sm: 'h-2.5 w-2.5',
    md: 'h-3 w-3',
    lg: 'h-3.5 w-3.5',
    xl: 'h-4 w-4',
    '2xl': 'h-5 w-5',
  };

  return (
    <div className={cn('relative inline-block', className)}>
      <div
        className={cn(
          'rounded-full overflow-hidden bg-slate-100 border-2 border-white shadow-sm',
          sizes[size]
        )}
      >
        {src ? (
          <Image
            src={src}
            alt={alt}
            width={96}
            height={96}
            className="object-cover w-full h-full"
          />
        ) : fallback ? (
          <div className="flex items-center justify-center h-full w-full bg-gradient-to-br from-primary-400 to-secondary-400 text-white font-bold text-sm">
            {fallback}
          </div>
        ) : (
          <div className="flex items-center justify-center h-full w-full text-slate-400">
            <User className={iconSizes[size]} />
          </div>
        )}
      </div>

      {status && (
        <span
          className={cn(
            'absolute bottom-0 right-0 rounded-full border-2 border-white',
            statusColors[status],
            statusSizes[size]
          )}
        />
      )}
    </div>
  );
}

interface AvatarGroupProps {
  children: React.ReactNode;
  max?: number;
  className?: string;
}

export function AvatarGroup({ children, max = 5, className }: AvatarGroupProps) {
  const childrenArray = React.Children.toArray(children);
  const displayedChildren = max ? childrenArray.slice(0, max) : childrenArray;
  const remainingCount = max && childrenArray.length > max ? childrenArray.length - max : 0;

  return (
    <div className={cn('flex -space-x-2', className)}>
      {displayedChildren}
      {remainingCount > 0 && (
        <div className="flex items-center justify-center h-12 w-12 rounded-full bg-slate-200 border-2 border-white text-slate-700 text-sm font-semibold z-10">
          +{remainingCount}
        </div>
      )}
    </div>
  );
}
