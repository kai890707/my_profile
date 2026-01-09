import { cn } from '@/lib/utils';

interface SkeletonProps {
  className?: string;
  variant?: 'text' | 'circular' | 'rectangular';
  animation?: 'pulse' | 'wave' | 'none';
}

export function Skeleton({
  className,
  variant = 'rectangular',
  animation = 'pulse',
}: SkeletonProps) {
  const variantClasses = {
    text: 'h-4 w-full rounded',
    circular: 'rounded-full',
    rectangular: 'rounded-lg',
  };

  const animationClasses = {
    pulse: 'animate-pulse',
    wave: 'animate-wave',
    none: '',
  };

  return (
    <div
      className={cn(
        'bg-slate-200',
        variantClasses[variant],
        animationClasses[animation],
        className
      )}
    />
  );
}

// 預設 Skeleton 組合 - 頭像卡片
export function AvatarSkeleton({ size = 'md' }: { size?: 'sm' | 'md' | 'lg' }) {
  const sizes = {
    sm: 'h-10 w-10',
    md: 'h-12 w-12',
    lg: 'h-16 w-16',
  };

  return <Skeleton variant="circular" className={sizes[size]} />;
}

// 預設 Skeleton 組合 - 文字行
export function TextSkeleton({ lines = 3 }: { lines?: number }) {
  return (
    <div className="space-y-2">
      {Array.from({ length: lines }).map((_, i) => (
        <Skeleton
          key={i}
          variant="text"
          className={i === lines - 1 ? 'w-2/3' : 'w-full'}
        />
      ))}
    </div>
  );
}

// 預設 Skeleton 組合 - 業務員卡片
export function SalespersonCardSkeleton() {
  return (
    <div className="p-6 bg-white rounded-xl border border-slate-200 space-y-4">
      {/* 頭像與基本資訊 */}
      <div className="flex items-start gap-4">
        <AvatarSkeleton size="lg" />
        <div className="flex-1 space-y-2">
          <Skeleton variant="text" className="w-1/3 h-5" />
          <Skeleton variant="text" className="w-1/2 h-4" />
        </div>
      </div>

      {/* 簡介 */}
      <TextSkeleton lines={2} />

      {/* 標籤 */}
      <div className="flex gap-2">
        <Skeleton className="h-6 w-16 rounded-full" />
        <Skeleton className="h-6 w-20 rounded-full" />
        <Skeleton className="h-6 w-18 rounded-full" />
      </div>
    </div>
  );
}

// 預設 Skeleton 組合 - 表格行
export function TableRowSkeleton({ columns = 4 }: { columns?: number }) {
  return (
    <div className="flex items-center gap-4 p-4 border-b border-slate-200">
      {Array.from({ length: columns }).map((_, i) => (
        <Skeleton key={i} variant="text" className="flex-1" />
      ))}
    </div>
  );
}

// 預設 Skeleton 組合 - 個人資料
export function ProfileSkeleton() {
  return (
    <div className="space-y-6">
      {/* 頭像區塊 */}
      <div className="flex items-center gap-6">
        <AvatarSkeleton size="lg" />
        <div className="flex-1 space-y-2">
          <Skeleton variant="text" className="w-1/4 h-6" />
          <Skeleton variant="text" className="w-1/3 h-4" />
        </div>
      </div>

      {/* 資訊欄位 */}
      <div className="space-y-4">
        <div className="space-y-2">
          <Skeleton variant="text" className="w-20 h-4" />
          <Skeleton variant="rectangular" className="w-full h-10" />
        </div>
        <div className="space-y-2">
          <Skeleton variant="text" className="w-20 h-4" />
          <Skeleton variant="rectangular" className="w-full h-10" />
        </div>
        <div className="space-y-2">
          <Skeleton variant="text" className="w-20 h-4" />
          <Skeleton variant="rectangular" className="w-full h-24" />
        </div>
      </div>
    </div>
  );
}
