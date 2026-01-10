import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import * as adminApi from '@/lib/api/admin';
import { toast } from 'sonner';

// ========== Query Keys ==========

export const adminKeys = {
  all: ['admin'] as const,
  pendingApprovals: ['admin', 'pending-approvals'] as const,
  salespersonApplications: ['admin', 'salesperson-applications'] as const,
  users: ['admin', 'users'] as const,
  statistics: ['admin', 'statistics'] as const,
  industries: ['admin', 'industries'] as const,
  regions: ['admin', 'regions'] as const,
};

// ========== Salesperson Application Management Hooks ==========

/**
 * 查詢待審核的業務員申請
 */
export function useSalespersonApplications() {
  return useQuery({
    queryKey: adminKeys.salespersonApplications,
    queryFn: async () => {
      const response = await adminApi.getSalespersonApplications();
      return response.data;
    },
  });
}

/**
 * 批准業務員申請
 */
export function useApproveSalesperson() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (userId: number) => adminApi.approveSalesperson(userId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.salespersonApplications });
      queryClient.invalidateQueries({ queryKey: adminKeys.users });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '已批准業務員申請');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || error.response?.data?.error || '批准失敗');
    },
  });
}

/**
 * 拒絕業務員申請
 */
export function useRejectSalesperson() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ userId, data }: { userId: number; data: adminApi.RejectSalespersonRequest }) =>
      adminApi.rejectSalesperson(userId, data),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.salespersonApplications });
      queryClient.invalidateQueries({ queryKey: adminKeys.users });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '已拒絕業務員申請');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || error.response?.data?.error || '拒絕失敗');
    },
  });
}

// ========== Pending Approvals Hooks ==========

/**
 * 查詢待審核項目
 */
export function usePendingApprovals() {
  return useQuery({
    queryKey: adminKeys.pendingApprovals,
    queryFn: async () => {
      const response = await adminApi.getPendingApprovals();
      return response.data;
    },
  });
}

/**
 * 審核通過業務員
 */
export function useApproveUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (userId: number) => adminApi.approveUser(userId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.users });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '審核通過');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 拒絕業務員
 */
export function useRejectUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ userId, reason }: { userId: number; reason?: string }) =>
      adminApi.rejectUser(userId, reason),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.users });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '已拒絕');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 審核通過公司
 */
export function useApproveCompany() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (companyId: number) => adminApi.approveCompany(companyId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '審核通過');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 拒絕公司
 */
export function useRejectCompany() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ companyId, reason }: { companyId: number; reason?: string }) =>
      adminApi.rejectCompany(companyId, reason),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '已拒絕');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 審核通過證照
 */
export function useApproveCertification() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (certId: number) => adminApi.approveCertification(certId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '審核通過');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 拒絕證照
 */
export function useRejectCertification() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ certId, reason }: { certId: number; reason?: string }) =>
      adminApi.rejectCertification(certId, reason),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '已拒絕');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 審核通過工作經驗
 */
export function useApproveExperience() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (expId: number) => adminApi.approveExperience(expId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '審核通過');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 拒絕工作經驗
 */
export function useRejectExperience() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ expId, reason }: { expId: number; reason?: string }) =>
      adminApi.rejectExperience(expId, reason),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.pendingApprovals });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '已拒絕');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

// ========== User Management Hooks ==========

/**
 * 查詢使用者列表
 */
export function useUsers(params?: adminApi.GetUsersParams) {
  return useQuery({
    queryKey: [...adminKeys.users, params],
    queryFn: async () => {
      const response = await adminApi.getUsers(params);
      return response.data;
    },
  });
}

/**
 * 更新使用者狀態
 */
export function useUpdateUserStatus() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ userId, status }: { userId: number; status: 'active' | 'inactive' }) =>
      adminApi.updateUserStatus(userId, status),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.users });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '狀態已更新');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 刪除使用者
 */
export function useDeleteUser() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (userId: number) => adminApi.deleteUser(userId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.users });
      queryClient.invalidateQueries({ queryKey: adminKeys.statistics });
      toast.success(response.message || '使用者已刪除');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

// ========== Statistics Hooks ==========

/**
 * 查詢統計資料
 */
export function useStatistics() {
  return useQuery({
    queryKey: adminKeys.statistics,
    queryFn: async () => {
      const response = await adminApi.getStatistics();
      return response.data;
    },
  });
}

// ========== Settings Hooks ==========

/**
 * 查詢產業列表
 */
export function useIndustries() {
  return useQuery({
    queryKey: adminKeys.industries,
    queryFn: async () => {
      const response = await adminApi.getIndustries();
      return response.data;
    },
  });
}

/**
 * 新增產業
 */
export function useCreateIndustry() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: adminApi.CreateIndustryRequest) => adminApi.createIndustry(data),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.industries });
      toast.success(response.message || '產業已新增');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 刪除產業
 */
export function useDeleteIndustry() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (industryId: number) => adminApi.deleteIndustry(industryId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.industries });
      toast.success(response.message || '產業已刪除');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 查詢地區列表
 */
export function useRegions() {
  return useQuery({
    queryKey: adminKeys.regions,
    queryFn: async () => {
      const response = await adminApi.getRegions();
      return response.data;
    },
  });
}

/**
 * 新增地區
 */
export function useCreateRegion() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: adminApi.CreateRegionRequest) => adminApi.createRegion(data),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.regions });
      toast.success(response.message || '地區已新增');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}

/**
 * 刪除地區
 */
export function useDeleteRegion() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (regionId: number) => adminApi.deleteRegion(regionId),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: adminKeys.regions });
      toast.success(response.message || '地區已刪除');
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || '操作失敗');
    },
  });
}
