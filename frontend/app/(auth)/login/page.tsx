'use client';

import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useLogin } from '@/hooks/useAuth';

const loginSchema = z.object({
  email: z.string().email('請輸入有效的電子郵件'),
  password: z.string().min(1, '請輸入密碼'),
});

type LoginForm = z.infer<typeof loginSchema>;

export default function LoginPage() {
  const login = useLogin();

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<LoginForm>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = (data: LoginForm) => {
    login.mutate(data);
  };

  return (
    <div className="space-y-8">
      <div className="text-center">
        <h2 className="text-3xl font-bold text-foreground">登入</h2>
        <p className="text-slate-600 mt-2 text-base">歡迎回來！請登入您的帳號</p>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-5">
        <Input
          label="電子郵件"
          type="email"
          placeholder="your@email.com"
          error={errors.email?.message}
          required
          {...register('email')}
        />

        <Input
          label="密碼"
          type="password"
          placeholder="請輸入密碼"
          error={errors.password?.message}
          required
          {...register('password')}
        />

        <div className="pt-4">
          <Button
            type="submit"
            className="w-full text-xl font-bold tracking-wide"
            size="lg"
            isLoading={login.isPending}
          >
            {login.isPending ? '登入中...' : '登入'}
          </Button>
        </div>

        <div className="text-center">
          <a href="#" className="text-sm text-primary-600 hover:text-primary-700 hover:underline">
            忘記密碼？
          </a>
        </div>
      </form>

      <div className="relative">
        <div className="absolute inset-0 flex items-center">
          <div className="w-full border-t border-slate-200"></div>
        </div>
        <div className="relative flex justify-center text-sm">
          <span className="px-4 bg-white text-slate-500">還沒有帳號？</span>
        </div>
      </div>

      <div className="text-center">
        <Link
          href="/register"
          className="inline-flex items-center justify-center text-primary-600 font-semibold hover:text-primary-700 transition-colors"
        >
          立即註冊 →
        </Link>
      </div>
    </div>
  );
}
