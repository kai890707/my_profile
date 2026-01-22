'use client';

import React, { useState, useEffect, useRef } from 'react';
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
  lazy?: boolean;              // 啟用 Lazy Loading
  priority?: boolean;          // 高優先級載入 (禁用 lazy)
  onLoad?: () => void;         // 圖片載入完成回調
  onError?: () => void;        // 圖片載入錯誤回調
}

export function Avatar({
  src,
  alt = 'Avatar',
  size = 'md',
  className,
  fallback,
  status,
  lazy = false,
  priority = false,
  onLoad,
  onError,
}: AvatarProps) {
  // 狀態管理
  const [imageError, setImageError] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  const [isInView, setIsInView] = useState(!lazy); // Lazy loading 控制

  // Intersection Observer Ref
  const avatarRef = useRef<HTMLDivElement>(null);

  // 尺寸對照
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

  // Lazy Loading with Intersection Observer
  useEffect(() => {
    if (!lazy || priority) {
      setIsInView(true);
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setIsInView(true);
            observer.disconnect();
          }
        });
      },
      {
        rootMargin: '50px', // 提前 50px 開始載入
        threshold: 0.01,
      }
    );

    if (avatarRef.current) {
      observer.observe(avatarRef.current);
    }

    return () => observer.disconnect();
  }, [lazy, priority]);

  // 處理圖片載入完成
  const handleLoad = () => {
    setImageLoaded(true);
    onLoad?.();
  };

  // 處理圖片載入錯誤
  const handleError = () => {
    setImageError(true);
    onError?.();
  };

  // 決定顯示內容
  const shouldShowFallback = !src || imageError || (lazy && !isInView);

  return (
    <div ref={avatarRef} className={cn('relative inline-block', className)}>
      <div
        className={cn(
          'rounded-full overflow-hidden bg-slate-100 border-2 border-white shadow-sm',
          sizes[size]
        )}
      >
        {shouldShowFallback ? (
          // Fallback 顯示
          <FallbackAvatar fallback={fallback} size={size} iconSizes={iconSizes} />
        ) : (
          // 圖片顯示
          <ImageAvatar
            src={src}
            alt={alt}
            priority={priority}
            isLoaded={imageLoaded}
            onLoad={handleLoad}
            onError={handleError}
          />
        )}
      </div>

      {/* 狀態指示器 */}
      {status && (
        <span
          className={cn(
            'absolute bottom-0 right-0 rounded-full border-2 border-white',
            statusColors[status],
            statusSizes[size]
          )}
          aria-label={`狀態: ${status}`}
        />
      )}
    </div>
  );
}

// Fallback Avatar 子組件
interface FallbackAvatarProps {
  fallback?: string;
  size: AvatarProps['size'];
  iconSizes: Record<string, string>;
}

function FallbackAvatar({ fallback, size, iconSizes }: FallbackAvatarProps) {
  if (fallback) {
    return (
      <div
        className="flex items-center justify-center h-full w-full bg-gradient-to-br from-primary-400 to-secondary-400 text-white font-bold text-sm"
        role="img"
        aria-label={`${fallback} 的頭像`}
      >
        {fallback}
      </div>
    );
  }

  // 預設圖示
  return (
    <div
      className="flex items-center justify-center h-full w-full text-slate-400"
      role="img"
      aria-label="預設頭像"
    >
      <User className={iconSizes[size!]} />
    </div>
  );
}

// Image Avatar 子組件
interface ImageAvatarProps {
  src: string;
  alt: string;
  priority: boolean;
  isLoaded: boolean;
  onLoad: () => void;
  onError: () => void;
}

function ImageAvatar({
  src,
  alt,
  priority,
  isLoaded,
  onLoad,
  onError,
}: ImageAvatarProps) {
  // Data URL: 使用原生 img
  if (src.startsWith('data:')) {
    return (
      // eslint-disable-next-line @next/next/no-img-element
      <img
        src={src}
        alt={alt}
        className={cn(
          'object-cover w-full h-full',
          'transition-opacity duration-300',
          isLoaded ? 'opacity-100' : 'opacity-0'
        )}
        onLoad={onLoad}
        onError={onError}
      />
    );
  }

  // HTTP URL: 使用 Next.js Image
  return (
    <Image
      src={src}
      alt={alt}
      width={96}
      height={96}
      className={cn(
        'object-cover w-full h-full',
        'transition-opacity duration-300',
        isLoaded ? 'opacity-100' : 'opacity-0'
      )}
      loading={priority ? 'eager' : 'lazy'}
      priority={priority}
      unoptimized
      onLoad={onLoad}
      onError={onError}
    />
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
