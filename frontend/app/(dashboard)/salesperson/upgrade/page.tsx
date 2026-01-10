'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useUpgradeToSalesperson } from '@/hooks/useSalesperson';
import { useAuth } from '@/hooks/useAuth';

// 升級表單 Schema
const upgradeSchema = z.object({
  full_name: z.string().min(2, '真實姓名至少需要 2 個字元').max(100, '真實姓名不能超過 100 個字元'),
  phone: z.string().regex(/^09\d{8}$/, '請輸入有效的手機號碼（例：0912345678）'),
  bio: z.string().max(500, '簡介不能超過 500 個字元').optional(),
  specialties: z.string().max(200, '專長不能超過 200 個字元').optional(),
});

type UpgradeForm = z.infer<typeof upgradeSchema>;

export default function UpgradePage() {
  const router = useRouter();
  const { data: user, isLoading: userLoading } = useAuth();
  const upgradeMutation = useUpgradeToSalesperson();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<UpgradeForm>({
    resolver: zodResolver(upgradeSchema),
  });

  // 如果已經是業務員，重定向到 dashboard
  useEffect(() => {
    if (!userLoading && user && user.role === 'salesperson') {
      router.push('/dashboard');
    }
  }, [user, userLoading, router]);

  const onSubmit = (data: UpgradeForm) => {
    const payload = {
      ...data,
      bio: data.bio || undefined,
      specialties: data.specialties || undefined,
      service_regions: [], // 可以之後再填寫
    };
    upgradeMutation.mutate(payload);
  };

  if (userLoading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    );
  }

  return (
    <div className="max-w-3xl mx-auto py-8 px-4">
      <div className="space-y-8">
        {/* Header */}
        <div className="text-center">
          <h1 className="text-3xl font-bold text-foreground">升級為業務員</h1>
          <p className="text-slate-600 mt-2 text-base">
            填寫以下資訊開始您的業務之旅
          </p>
        </div>

        {/* Info Card */}
        <div className="bg-blue-50 border border-blue-200 rounded-xl p-6">
          <div className="flex items-start">
            <svg className="h-6 w-6 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
            </svg>
            <div>
              <h3 className="text-sm font-semibold text-blue-900 mb-1">升級說明</h3>
              <ul className="text-sm text-blue-800 space-y-1">
                <li>• 升級後需要等待管理員審核</li>
                <li>• 審核通過前，您可以瀏覽和搜尋相關資訊</li>
                <li>• 審核通過後，您可以建立公司資料、參與評分等</li>
              </ul>
            </div>
          </div>
        </div>

        {/* Form */}
        <div className="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            <Input
              label="真實姓名"
              type="text"
              placeholder="王小明"
              error={errors.full_name?.message}
              helperText="顯示在個人檔案的真實姓名"
              required
              {...register('full_name')}
            />

            <Input
              label="手機號碼"
              type="tel"
              placeholder="0912345678"
              error={errors.phone?.message}
              helperText="格式：0912345678"
              required
              {...register('phone')}
            />

            <div className="space-y-2">
              <label className="block text-sm font-medium text-slate-700">
                個人簡介 <span className="text-slate-400">(選填)</span>
              </label>
              <textarea
                rows={4}
                placeholder="簡單介紹您的背景和經驗，讓客戶更認識您"
                className="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"
                {...register('bio')}
              />
              {errors.bio && (
                <p className="text-sm text-red-600">{errors.bio.message}</p>
              )}
              <p className="text-xs text-slate-500">最多 500 個字元</p>
            </div>

            <Input
              label="專長"
              type="text"
              placeholder="壽險、投資型保單、團體保險"
              error={errors.specialties?.message}
              helperText="選填，用逗號分隔多個專長"
              {...register('specialties')}
            />

            {/* 服務地區提示 */}
            <div className="bg-slate-50 border border-slate-200 rounded-lg p-4">
              <p className="text-sm text-slate-600">
                <strong>提示：</strong>服務地區可以之後在個人檔案中設定。
              </p>
            </div>

            {/* Submit Button */}
            <div className="pt-4">
              <Button
                type="submit"
                className="w-full text-lg font-bold tracking-wide"
                size="lg"
                isLoading={upgradeMutation.isPending}
              >
                {upgradeMutation.isPending ? '送出中...' : '送出升級申請'}
              </Button>
            </div>

            {/* Cancel Button */}
            <button
              type="button"
              onClick={() => router.back()}
              className="w-full py-3 text-sm text-slate-600 hover:text-slate-800 font-medium transition-colors"
            >
              取消
            </button>
          </form>
        </div>

        {/* Benefits Section */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
            <div className="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-4">
              <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 className="font-bold text-slate-900 mb-2">管理公司資料</h3>
            <p className="text-sm text-slate-700">建立和管理公司資訊，讓客戶找到您的服務</p>
          </div>

          <div className="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
            <div className="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-4">
              <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <h3 className="font-bold text-slate-900 mb-2">參與評分</h3>
            <p className="text-sm text-slate-700">為其他業務員評分，建立專業的社群網絡</p>
          </div>

          <div className="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
            <div className="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-4">
              <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 className="font-bold text-slate-900 mb-2">展示專業</h3>
            <p className="text-sm text-slate-700">建立完整的個人檔案，展示您的專業能力</p>
          </div>
        </div>
      </div>
    </div>
  );
}
