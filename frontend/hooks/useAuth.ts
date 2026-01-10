import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';
import { login, logout, register, registerUser, registerSalesperson, getCurrentUser } from '@/lib/api/auth';
import { queryKeys } from '@/lib/query/keys';
import { setUserRole } from '@/lib/auth/token';
import type { LoginRequest, RegisterRequest, RegisterUserRequest, RegisterSalespersonRequest } from '@/types/api';

/**
 * 取得當前使用者資訊
 */
export function useAuth() {
  return useQuery({
    queryKey: queryKeys.auth.me,
    queryFn: async () => {
      const response = await getCurrentUser();
      return response.data;
    },
    retry: false,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

/**
 * 登入 Mutation
 */
export function useLogin() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: login,
    onSuccess: async (data) => {
      // 預先設定用戶資料到 cache，避免跳轉後重新獲取
      const user = data.data?.user;
      if (user) {
        queryClient.setQueryData(queryKeys.auth.me, user);
        // 設置用戶角色到 cookie（用於 middleware）
        setUserRole(user.role);
      }

      toast.success(data.message || '登入成功');

      // 根據角色導向對應頁面
      if (user?.role === 'admin') {
        router.push('/admin');
      } else if (user?.role === 'salesperson') {
        router.push('/dashboard');
      } else {
        router.push('/');
      }
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '登入失敗';
      toast.error(message);
    },
  });
}

/**
 * 登出 Mutation
 */
export function useLogout() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: logout,
    onSuccess: () => {
      // 清除所有快取
      queryClient.clear();

      toast.success('登出成功');
      router.push('/login');
    },
    onError: () => {
      // 即使登出失敗也清除本地資料並導向登入頁
      queryClient.clear();
      router.push('/login');
    },
  });
}

/**
 * 一般使用者註冊 Mutation
 */
export function useRegisterUser() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: registerUser,
    onSuccess: async (data) => {
      // 預先設定用戶資料到 cache
      const user = data.data?.user;
      if (user) {
        queryClient.setQueryData(queryKeys.auth.me, user);
        setUserRole(user.role);
      }

      toast.success(data.message || '註冊成功！');

      // 導向首頁
      router.push('/');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '註冊失敗';
      const errors = error.response?.data?.errors;

      if (errors) {
        Object.values(errors).flat().forEach((err) => {
          toast.error(err as string);
        });
      } else {
        toast.error(message);
      }
    },
  });
}

/**
 * 業務員註冊 Mutation
 */
export function useRegisterSalesperson() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: registerSalesperson,
    onSuccess: async (data) => {
      // 預先設定用戶資料到 cache
      const user = data.data?.user;
      if (user) {
        queryClient.setQueryData(queryKeys.auth.me, user);
        setUserRole(user.role);
      }

      toast.success(data.message || '註冊成功，請等待管理員審核');

      // 導向 dashboard
      router.push('/dashboard');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '註冊失敗';
      const errors = error.response?.data?.errors;

      if (errors) {
        Object.values(errors).flat().forEach((err) => {
          toast.error(err as string);
        });
      } else {
        toast.error(message);
      }
    },
  });
}

/**
 * 註冊 Mutation (Legacy)
 */
export function useRegister() {
  const router = useRouter();

  return useMutation({
    mutationFn: register,
    onSuccess: (data) => {
      toast.success(data.message || '註冊成功，請等待管理員審核');
      router.push('/login');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '註冊失敗';
      const errors = error.response?.data?.errors;

      if (errors) {
        // 顯示驗證錯誤
        Object.values(errors).flat().forEach((err) => {
          toast.error(err as string);
        });
      } else {
        toast.error(message);
      }
    },
  });
}
