import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { useRouter } from 'next/navigation';
import { toast } from 'sonner';
import * as companiesApi from '@/lib/api/companies';

// ========== Query Keys ==========

export const companyKeys = {
  all: ['companies'] as const,
  myCompanies: ['companies', 'my'] as const,
  detail: (id: number) => ['companies', id] as const,
  search: (params: companiesApi.SearchCompaniesParams) => ['companies', 'search', params] as const,
};

// ========== Company Hooks ==========

/**
 * 搜尋公司（by tax_id 或 name）
 */
export function useSearchCompanies(params: companiesApi.SearchCompaniesParams) {
  return useQuery({
    queryKey: companyKeys.search(params),
    queryFn: async () => {
      const response = await companiesApi.searchCompanies(params);
      return response;
    },
    enabled: !!(params.tax_id || params.name),
  });
}

/**
 * 建立公司
 */
export function useCreateCompany() {
  const router = useRouter();
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: companiesApi.createCompany,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: companyKeys.myCompanies });
      queryClient.invalidateQueries({ queryKey: ['salesperson', 'profile'] });
      toast.success(response.message || '公司建立成功！');
      router.push('/dashboard');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || error.response?.data?.error || '建立失敗，請稍後再試';
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
 * 取得我的公司列表
 */
export function useMyCompanies() {
  return useQuery({
    queryKey: companyKeys.myCompanies,
    queryFn: async () => {
      const response = await companiesApi.getMyCompanies();
      return response.data;
    },
  });
}

/**
 * 取得單一公司詳情
 */
export function useCompany(id: number) {
  return useQuery({
    queryKey: companyKeys.detail(id),
    queryFn: async () => {
      const response = await companiesApi.getCompany(id);
      return response.data;
    },
    enabled: !!id,
  });
}
