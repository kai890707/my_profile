'use client';

import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useRegisterUser, useRegisterSalesperson } from '@/hooks/useAuth';

type RegistrationType = 'user' | 'salesperson';

// 一般使用者註冊表單 Schema
const registerUserSchema = z.object({
  name: z.string().min(2, '姓名至少需要 2 個字元').max(100, '姓名不能超過 100 個字元'),
  email: z.string().email('請輸入有效的電子郵件'),
  password: z.string().min(8, '密碼至少需要 8 個字元'),
});

// 業務員註冊表單 Schema
const registerSalespersonSchema = z.object({
  name: z.string().min(2, '姓名至少需要 2 個字元').max(100, '姓名不能超過 100 個字元'),
  email: z.string().email('請輸入有效的電子郵件'),
  password: z.string().min(8, '密碼至少需要 8 個字元'),
  full_name: z.string().min(2, '真實姓名至少需要 2 個字元').max(100, '真實姓名不能超過 100 個字元'),
  phone: z.string().regex(/^09\d{8}$/, '請輸入有效的手機號碼（例：0912345678）'),
  bio: z.string().max(500, '簡介不能超過 500 個字元').optional(),
  specialties: z.string().max(200, '專長不能超過 200 個字元').optional(),
});

type RegisterUserForm = z.infer<typeof registerUserSchema>;
type RegisterSalespersonForm = z.infer<typeof registerSalespersonSchema>;

export default function RegisterPage() {
  const [registrationType, setRegistrationType] = useState<RegistrationType | null>(null);

  const registerUserMutation = useRegisterUser();
  const registerSalespersonMutation = useRegisterSalesperson();

  // 一般使用者表單
  const userForm = useForm<RegisterUserForm>({
    resolver: zodResolver(registerUserSchema),
  });

  // 業務員表單
  const salespersonForm = useForm<RegisterSalespersonForm>({
    resolver: zodResolver(registerSalespersonSchema),
  });

  const onSubmitUser = (data: RegisterUserForm) => {
    registerUserMutation.mutate(data);
  };

  const onSubmitSalesperson = (data: RegisterSalespersonForm) => {
    const payload = {
      ...data,
      bio: data.bio || undefined,
      specialties: data.specialties || undefined,
      service_regions: [], // 可以之後再填寫
    };
    registerSalespersonMutation.mutate(payload);
  };

  // Step 1: 選擇註冊方式
  if (registrationType === null) {
    return (
      <div className="space-y-8">
        <div className="text-center">
          <h2 className="text-3xl font-bold text-foreground">註冊帳號</h2>
          <p className="text-slate-600 mt-2 text-base">請選擇您要註冊的帳號類型</p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* 一般使用者選項 */}
          <button
            onClick={() => setRegistrationType('user')}
            className="group relative flex flex-col items-center justify-center p-8 border-2 border-slate-200 rounded-2xl hover:border-primary-500 hover:bg-primary-50 transition-all"
          >
            <div className="w-16 h-16 bg-slate-100 group-hover:bg-primary-100 rounded-full flex items-center justify-center mb-4 transition-colors">
              <svg className="w-8 h-8 text-slate-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 className="text-xl font-bold text-foreground mb-2">一般使用者</h3>
            <p className="text-sm text-slate-600 text-center">
              瀏覽業務員資訊、查看公司資料
            </p>
          </button>

          {/* 業務員選項 */}
          <button
            onClick={() => setRegistrationType('salesperson')}
            className="group relative flex flex-col items-center justify-center p-8 border-2 border-slate-200 rounded-2xl hover:border-primary-500 hover:bg-primary-50 transition-all"
          >
            <div className="w-16 h-16 bg-slate-100 group-hover:bg-primary-100 rounded-full flex items-center justify-center mb-4 transition-colors">
              <svg className="w-8 h-8 text-slate-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <h3 className="text-xl font-bold text-foreground mb-2">業務員</h3>
            <p className="text-sm text-slate-600 text-center">
              建立個人檔案、管理公司資料、參與評分
            </p>
            <div className="mt-3 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
              需審核
            </div>
          </button>
        </div>

        <div className="relative">
          <div className="absolute inset-0 flex items-center">
            <div className="w-full border-t border-slate-200"></div>
          </div>
          <div className="relative flex justify-center text-sm">
            <span className="px-4 bg-white text-slate-500">已經有帳號了？</span>
          </div>
        </div>

        <div className="text-center">
          <Link
            href="/login"
            className="inline-flex items-center justify-center text-primary-600 font-semibold hover:text-primary-700 transition-colors"
          >
            立即登入 →
          </Link>
        </div>
      </div>
    );
  }

  // Step 2A: 一般使用者註冊表單
  if (registrationType === 'user') {
    return (
      <div className="space-y-8">
        <div className="text-center">
          <button
            onClick={() => setRegistrationType(null)}
            className="inline-flex items-center text-sm text-slate-600 hover:text-slate-800 mb-4"
          >
            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            返回選擇
          </button>
          <h2 className="text-3xl font-bold text-foreground">註冊一般使用者</h2>
          <p className="text-slate-600 mt-2 text-base">填寫以下資訊建立您的帳號</p>
        </div>

        <form onSubmit={userForm.handleSubmit(onSubmitUser)} className="space-y-4">
          <Input
            label="姓名"
            type="text"
            placeholder="王小明"
            error={userForm.formState.errors.name?.message}
            required
            {...userForm.register('name')}
          />

          <Input
            label="電子郵件"
            type="email"
            placeholder="your@email.com"
            error={userForm.formState.errors.email?.message}
            required
            {...userForm.register('email')}
          />

          <Input
            label="密碼"
            type="password"
            placeholder="請輸入至少 8 個字元"
            error={userForm.formState.errors.password?.message}
            helperText="至少 8 個字元"
            required
            {...userForm.register('password')}
          />

          <div className="pt-4">
            <Button
              type="submit"
              className="w-full text-xl font-bold tracking-wide"
              size="lg"
              isLoading={registerUserMutation.isPending}
            >
              {registerUserMutation.isPending ? '註冊中...' : '立即註冊'}
            </Button>
          </div>
        </form>

        <div className="relative">
          <div className="absolute inset-0 flex items-center">
            <div className="w-full border-t border-slate-200"></div>
          </div>
          <div className="relative flex justify-center text-sm">
            <span className="px-4 bg-white text-slate-500">已經有帳號了？</span>
          </div>
        </div>

        <div className="text-center">
          <Link
            href="/login"
            className="inline-flex items-center justify-center text-primary-600 font-semibold hover:text-primary-700 transition-colors"
          >
            立即登入 →
          </Link>
        </div>
      </div>
    );
  }

  // Step 2B: 業務員註冊表單
  if (registrationType === 'salesperson') {
    return (
      <div className="space-y-8">
        <div className="text-center">
          <button
            onClick={() => setRegistrationType(null)}
            className="inline-flex items-center text-sm text-slate-600 hover:text-slate-800 mb-4"
          >
            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            返回選擇
          </button>
          <h2 className="text-3xl font-bold text-foreground">註冊業務員帳號</h2>
          <p className="text-slate-600 mt-2 text-base">填寫以下資訊開始您的業務之旅</p>
        </div>

        <form onSubmit={salespersonForm.handleSubmit(onSubmitSalesperson)} className="space-y-4">
          <Input
            label="使用者名稱"
            type="text"
            placeholder="johndoe"
            error={salespersonForm.formState.errors.name?.message}
            helperText="用於登入的名稱"
            required
            {...salespersonForm.register('name')}
          />

          <Input
            label="真實姓名"
            type="text"
            placeholder="王小明"
            error={salespersonForm.formState.errors.full_name?.message}
            helperText="顯示在個人檔案的真實姓名"
            required
            {...salespersonForm.register('full_name')}
          />

          <Input
            label="電子郵件"
            type="email"
            placeholder="your@email.com"
            error={salespersonForm.formState.errors.email?.message}
            required
            {...salespersonForm.register('email')}
          />

          <Input
            label="手機號碼"
            type="tel"
            placeholder="0912345678"
            error={salespersonForm.formState.errors.phone?.message}
            helperText="格式：0912345678"
            required
            {...salespersonForm.register('phone')}
          />

          <Input
            label="密碼"
            type="password"
            placeholder="請輸入至少 8 個字元"
            error={salespersonForm.formState.errors.password?.message}
            helperText="至少 8 個字元"
            required
            {...salespersonForm.register('password')}
          />

          <div className="space-y-2">
            <label className="block text-sm font-medium text-slate-700">
              個人簡介 <span className="text-slate-400">(選填)</span>
            </label>
            <textarea
              rows={3}
              placeholder="簡單介紹您的背景和經驗"
              className="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              {...salespersonForm.register('bio')}
            />
            {salespersonForm.formState.errors.bio && (
              <p className="text-sm text-red-600">{salespersonForm.formState.errors.bio.message}</p>
            )}
          </div>

          <Input
            label="專長"
            type="text"
            placeholder="壽險、投資型保單、團體保險"
            error={salespersonForm.formState.errors.specialties?.message}
            helperText="選填，用逗號分隔"
            {...salespersonForm.register('specialties')}
          />

          <div className="pt-4">
            <Button
              type="submit"
              className="w-full text-xl font-bold tracking-wide"
              size="lg"
              isLoading={registerSalespersonMutation.isPending}
            >
              {registerSalespersonMutation.isPending ? '註冊中...' : '立即註冊'}
            </Button>
          </div>
        </form>

        <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
          <div className="flex items-start">
            <svg className="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
            </svg>
            <p className="text-sm text-blue-800 leading-relaxed">
              註冊後需要等待管理員審核才能使用完整功能。審核通過前，您可以瀏覽和搜尋相關資訊。
            </p>
          </div>
        </div>

        <div className="relative">
          <div className="absolute inset-0 flex items-center">
            <div className="w-full border-t border-slate-200"></div>
          </div>
          <div className="relative flex justify-center text-sm">
            <span className="px-4 bg-white text-slate-500">已經有帳號了？</span>
          </div>
        </div>

        <div className="text-center">
          <Link
            href="/login"
            className="inline-flex items-center justify-center text-primary-600 font-semibold hover:text-primary-700 transition-colors"
          >
            立即登入 →
          </Link>
        </div>
      </div>
    );
  }

  return null;
}
