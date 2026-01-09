'use client';

import { useEffect } from 'react';
import { AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    // 可以將錯誤記錄到錯誤報告服務
    console.error('Error:', error);
  }, [error]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
      <div className="text-center max-w-md">
        <div className="inline-flex p-6 bg-red-100 rounded-full mb-6">
          <AlertTriangle className="h-24 w-24 text-red-600" />
        </div>

        <h1 className="text-3xl font-bold text-slate-900 mb-4">糟糕！發生錯誤</h1>
        <p className="text-slate-600 mb-6">
          {error.message || '頁面載入時發生錯誤，請稍後再試。'}
        </p>

        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Button onClick={reset}>重新嘗試</Button>
          <Button variant="outline" onClick={() => (window.location.href = '/')}>
            返回首頁
          </Button>
        </div>

        {process.env.NODE_ENV === 'development' && error.stack && (
          <details className="mt-8 text-left">
            <summary className="cursor-pointer text-sm font-medium text-slate-700 mb-2">
              錯誤詳情（僅開發環境顯示）
            </summary>
            <pre className="text-xs bg-slate-100 p-4 rounded-lg overflow-auto max-h-64">
              {error.stack}
            </pre>
          </details>
        )}
      </div>
    </div>
  );
}
