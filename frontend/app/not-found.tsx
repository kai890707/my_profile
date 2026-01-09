import Link from 'next/link';
import { FileQuestion } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function NotFound() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
      <div className="text-center max-w-md">
        <div className="inline-flex p-6 bg-primary-100 rounded-full mb-6">
          <FileQuestion className="h-24 w-24 text-primary-600" />
        </div>

        <h1 className="text-6xl font-bold text-slate-900 mb-4">404</h1>
        <h2 className="text-2xl font-semibold text-slate-900 mb-4">找不到頁面</h2>
        <p className="text-slate-600 mb-8">
          抱歉，您訪問的頁面不存在或已被移除。
        </p>

        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link href="/">
            <Button>返回首頁</Button>
          </Link>
          <Link href="/search">
            <Button variant="outline">搜尋業務員</Button>
          </Link>
        </div>
      </div>
    </div>
  );
}
