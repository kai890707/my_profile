'use client';

import { useEffect, useState } from 'react';

/**
 * 性能監控組件（僅開發環境顯示）
 */
export function PerformanceMonitor() {
  const [metrics, setMetrics] = useState<{
    fcp?: number;
    lcp?: number;
    cls?: number;
    fid?: number;
    ttfb?: number;
  }>({});

  useEffect(() => {
    if (typeof window === 'undefined' || process.env.NODE_ENV !== 'development') {
      return;
    }

    // 監控 Web Vitals
    if ('PerformanceObserver' in window) {
      // First Contentful Paint (FCP)
      const fcpObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const fcp = entries.find((entry) => entry.name === 'first-contentful-paint');
        if (fcp) {
          setMetrics((prev) => ({ ...prev, fcp: fcp.startTime }));
        }
      });
      fcpObserver.observe({ type: 'paint', buffered: true });

      // Largest Contentful Paint (LCP)
      const lcpObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        setMetrics((prev) => ({ ...prev, lcp: lastEntry.startTime }));
      });
      lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });

      // Cumulative Layout Shift (CLS)
      let clsValue = 0;
      const clsObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (!(entry as any).hadRecentInput) {
            clsValue += (entry as any).value;
            setMetrics((prev) => ({ ...prev, cls: clsValue }));
          }
        }
      });
      clsObserver.observe({ type: 'layout-shift', buffered: true });

      // First Input Delay (FID)
      const fidObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const firstInput = entries[0];
        const fid = (firstInput as any).processingStart - firstInput.startTime;
        setMetrics((prev) => ({ ...prev, fid }));
      });
      fidObserver.observe({ type: 'first-input', buffered: true });

      // Time to First Byte (TTFB)
      const navigationEntries = performance.getEntriesByType('navigation');
      if (navigationEntries.length > 0) {
        const nav = navigationEntries[0] as PerformanceNavigationTiming;
        const ttfb = nav.responseStart - nav.requestStart;
        setMetrics((prev) => ({ ...prev, ttfb }));
      }

      return () => {
        fcpObserver.disconnect();
        lcpObserver.disconnect();
        clsObserver.disconnect();
        fidObserver.disconnect();
      };
    }
  }, []);

  if (process.env.NODE_ENV !== 'development') {
    return null;
  }

  return (
    <div className="fixed bottom-4 right-4 z-50 bg-black/90 text-white rounded-lg p-4 text-xs font-mono max-w-xs">
      <div className="font-bold mb-2">⚡ Performance Metrics</div>
      <div className="space-y-1">
        {metrics.fcp && (
          <div className="flex justify-between">
            <span>FCP:</span>
            <span className={metrics.fcp < 2000 ? 'text-green-400' : 'text-yellow-400'}>
              {Math.round(metrics.fcp)}ms
            </span>
          </div>
        )}
        {metrics.lcp && (
          <div className="flex justify-between">
            <span>LCP:</span>
            <span className={metrics.lcp < 2500 ? 'text-green-400' : 'text-yellow-400'}>
              {Math.round(metrics.lcp)}ms
            </span>
          </div>
        )}
        {metrics.cls !== undefined && (
          <div className="flex justify-between">
            <span>CLS:</span>
            <span className={metrics.cls < 0.1 ? 'text-green-400' : 'text-yellow-400'}>
              {metrics.cls.toFixed(3)}
            </span>
          </div>
        )}
        {metrics.fid && (
          <div className="flex justify-between">
            <span>FID:</span>
            <span className={metrics.fid < 100 ? 'text-green-400' : 'text-yellow-400'}>
              {Math.round(metrics.fid)}ms
            </span>
          </div>
        )}
        {metrics.ttfb && (
          <div className="flex justify-between">
            <span>TTFB:</span>
            <span className={metrics.ttfb < 600 ? 'text-green-400' : 'text-yellow-400'}>
              {Math.round(metrics.ttfb)}ms
            </span>
          </div>
        )}
      </div>
    </div>
  );
}
