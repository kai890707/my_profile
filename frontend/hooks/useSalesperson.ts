import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';
import * as salespersonApi from '@/lib/api/salesperson';

// ========== Query Keys ==========

export const salespersonKeys = {
  profile: ['salesperson', 'profile'] as const,
  experiences: ['salesperson', 'experiences'] as const,
  certifications: ['salesperson', 'certifications'] as const,
  approvalStatus: ['salesperson', 'approval-status'] as const,
};

// ========== Profile Hooks ==========

/**
 * 取得個人檔案
 */
export function useProfile() {
  return useQuery({
    queryKey: salespersonKeys.profile,
    queryFn: async () => {
      const response = await salespersonApi.getProfile();
      return response.data;
    },
  });
}

/**
 * 更新個人檔案
 */
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.profile });
      toast.success(response.message || '個人資料已更新');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '更新失敗，請稍後再試';
      toast.error(message);
    },
  });
}

// ========== Company Hooks ==========

/**
 * 儲存公司資訊
 */
export function useSaveCompany() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.saveCompany,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.profile });
      toast.success(response.message || '公司資訊已送出，等待審核');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '儲存失敗，請稍後再試';
      toast.error(message);
    },
  });
}

// ========== Experience Hooks ==========

/**
 * 取得工作經驗列表
 */
export function useExperiences() {
  return useQuery({
    queryKey: salespersonKeys.experiences,
    queryFn: async () => {
      const response = await salespersonApi.getExperiences();
      return response.data;
    },
  });
}

/**
 * 新增工作經驗
 */
export function useCreateExperience() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.createExperience,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.experiences });
      toast.success(response.message || '工作經驗已新增');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '新增失敗，請稍後再試';
      toast.error(message);
    },
  });
}

/**
 * 更新工作經驗
 */
export function useUpdateExperience() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: salespersonApi.CreateExperienceRequest }) =>
      salespersonApi.updateExperience(id, data),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.experiences });
      toast.success(response.message || '工作經驗已更新');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '更新失敗，請稍後再試';
      toast.error(message);
    },
  });
}

/**
 * 刪除工作經驗
 */
export function useDeleteExperience() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.deleteExperience,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.experiences });
      toast.success(response.message || '工作經驗已刪除');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '刪除失敗，請稍後再試';
      toast.error(message);
    },
  });
}

// ========== Certification Hooks ==========

/**
 * 取得證照列表
 */
export function useCertifications() {
  return useQuery({
    queryKey: salespersonKeys.certifications,
    queryFn: async () => {
      const response = await salespersonApi.getCertifications();
      return response.data;
    },
  });
}

/**
 * 上傳證照
 */
export function useCreateCertification() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.createCertification,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.certifications });
      queryClient.invalidateQueries({ queryKey: salespersonKeys.approvalStatus });
      toast.success(response.message || '證照已上傳，等待審核');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '上傳失敗，請稍後再試';
      toast.error(message);
    },
  });
}

/**
 * 刪除證照
 */
export function useDeleteCertification() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.deleteCertification,
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.certifications });
      toast.success(response.message || '證照已刪除');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '刪除失敗，請稍後再試';
      toast.error(message);
    },
  });
}

// ========== Approval Status Hook ==========

/**
 * 查詢審核狀態
 */
export function useApprovalStatus() {
  return useQuery({
    queryKey: salespersonKeys.approvalStatus,
    queryFn: async () => {
      const response = await salespersonApi.getApprovalStatus();
      return response.data;
    },
  });
}
