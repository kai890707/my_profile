import Link from 'next/link';
import { ShieldX } from 'lucide-react';
import { Button } from '@/components/ui/button';

export default function ForbiddenPage() {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 px-4">
      <div className="text-center max-w-md">
        <div className="inline-flex p-6 bg-red-100 rounded-full mb-6">
          <ShieldX className="h-24 w-24 text-red-600" />
        </div>

        <h1 className="text-6xl font-bold text-slate-900 mb-4">403</h1>
        <h2 className="text-2xl font-semibold text-slate-900 mb-4">拒絕訪問</h2>
        <p className="text-slate-600 mb-8">
          您沒有權限訪問此頁面。如果您認為這是錯誤，請聯繫管理員。
        </p>

        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Link href="/">
            <Button variant="outline">返回首頁</Button>
          </Link>
          <Link href="/login">
            <Button>重新登入</Button>
          </Link>
        </div>
      </div>
    </div>
  );
}
