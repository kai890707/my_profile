'use client';

import { useState, useEffect, useRef } from 'react';
import { cn } from '@/lib/utils/cn';

interface OptimizedImageProps {
  src: string;
  alt: string;
  className?: string;
  fallback?: React.ReactNode;
  lazy?: boolean;
}

/**
 * 優化的圖片組件
 * - 支持 lazy loading
 * - 支持 Base64 圖片
 * - 自動處理加載狀態
 */
export function OptimizedImage({
  src,
  alt,
  className,
  fallback,
  lazy = true,
}: OptimizedImageProps) {
  const [isLoading, setIsLoading] = useState(true);
  const [hasError, setHasError] = useState(false);
  const [isInView, setIsInView] = useState(!lazy);
  const imgRef = useRef<HTMLImageElement>(null);

  // Intersection Observer for lazy loading
  useEffect(() => {
    if (!lazy || isInView) return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setIsInView(true);
          observer.disconnect();
        }
      },
      { rootMargin: '50px' }
    );

    const currentRef = imgRef.current;
    if (currentRef) {
      observer.observe(currentRef);
    }

    return () => {
      if (currentRef) {
        observer.unobserve(currentRef);
      }
    };
  }, [lazy, isInView]);

  // Handle image load
  const handleLoad = () => {
    setIsLoading(false);
    setHasError(false);
  };

  // Handle image error
  const handleError = () => {
    setIsLoading(false);
    setHasError(true);
  };

  if (hasError) {
    return (
      fallback || (
        <div
          className={cn(
            'flex items-center justify-center bg-slate-100 text-slate-400',
            className
          )}
        >
          <span className="text-sm">無法載入圖片</span>
        </div>
      )
    );
  }

  return (
    <div className="relative">
      {isLoading && (
        <div
          className={cn(
            'absolute inset-0 bg-slate-100 animate-pulse rounded',
            className
          )}
        />
      )}
      <img
        ref={imgRef}
        src={isInView ? src : undefined}
        alt={alt}
        className={cn(
          'transition-opacity duration-300',
          isLoading ? 'opacity-0' : 'opacity-100',
          className
        )}
        onLoad={handleLoad}
        onError={handleError}
        loading={lazy ? 'lazy' : 'eager'}
      />
    </div>
  );
}
