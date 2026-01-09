'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useRegister } from '@/hooks/useAuth';

const registerSchema = z.object({
  username: z.string().min(3, '使用者名稱至少需要 3 個字元').max(50, '使用者名稱不能超過 50 個字元'),
  email: z.string().email('請輸入有效的電子郵件'),
  password: z.string().min(8, '密碼至少需要 8 個字元'),
  full_name: z.string().min(2, '姓名至少需要 2 個字元').max(100, '姓名不能超過 100 個字元'),
  phone: z.string().regex(/^09\d{8}$/, '請輸入有效的手機號碼（例：0912345678）').optional().or(z.literal('')),
});

type RegisterForm = z.infer<typeof registerSchema>;

export default function RegisterPage() {
  const registerMutation = useRegister();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<RegisterForm>({
    resolver: zodResolver(registerSchema),
  });

  const onSubmit = (data: RegisterForm) => {
    // 確保 phone 是字串（空字串轉為空字串）
    const payload = {
      ...data,
      phone: data.phone || '',
    };
    registerMutation.mutate(payload);
  };

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h2 className="text-3xl font-bold text-foreground">註冊業務員帳號</h2>
        <p className="text-slate-600 mt-2 text-base">填寫以下資訊開始您的業務之旅</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
        <Input
          label="使用者名稱"
          type="text"
          placeholder="johndoe"
          error={errors.username?.message}
          required
          {...register('username')}
        />

        <Input
          label="姓名"
          type="text"
          placeholder="王小明"
          error={errors.full_name?.message}
          required
          {...register('full_name')}
        />

        <Input
          label="電子郵件"
          type="email"
          placeholder="your@email.com"
          error={errors.email?.message}
          required
          {...register('email')}
        />

        <Input
          label="手機號碼"
          type="tel"
          placeholder="0912345678"
          error={errors.phone?.message}
          helperText="選填，格式：0912345678"
          {...register('phone')}
        />

        <Input
          label="密碼"
          type="password"
          placeholder="請輸入至少 8 個字元"
          error={errors.password?.message}
          helperText="至少 8 個字元"
          required
          {...register('password')}
        />

        <div className="pt-4">
          <Button
            type="submit"
            className="w-full text-xl font-bold tracking-wide"
            size="lg"
            isLoading={registerMutation.isPending}
          >
            {registerMutation.isPending ? '註冊中...' : '立即註冊'}
          </Button>
        </div>
      </form>

      <div className="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div className="flex items-start">
          <svg className="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
          </svg>
          <p className="text-sm text-blue-800 leading-relaxed">
            註冊後需要等待管理員審核才能使用完整功能
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
