import { apiClient } from './client';
import type {
  ApiResponse,
  User,
  Statistics,
  PendingApprovalItem,
  SalespersonProfile,
  Company,
  Certification,
  Experience,
  Industry,
  Region,
} from '@/types/api';

// ========== Approval APIs ==========

export interface PendingApprovalsData {
  users: User[];
  profiles: SalespersonProfile[];
  companies: Company[];
  certifications: Certification[];
  experiences: Experience[];
}

/**
 * 取得所有待審核項目
 */
export async function getPendingApprovals(): Promise<ApiResponse<PendingApprovalsData>> {
  const response = await apiClient.get<ApiResponse<PendingApprovalsData>>('/admin/pending-approvals');
  return response.data;
}

/**
 * 審核通過業務員註冊
 */
export async function approveUser(userId: number): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/approve-user/${userId}`);
  return response.data;
}

/**
 * 拒絕業務員註冊
 */
export async function rejectUser(userId: number, reason?: string): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/reject-user/${userId}`, {
    reason,
  });
  return response.data;
}

/**
 * 審核通過公司資訊
 */
export async function approveCompany(companyId: number): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/approve-company/${companyId}`);
  return response.data;
}

/**
 * 拒絕公司資訊
 */
export async function rejectCompany(companyId: number, reason?: string): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/reject-company/${companyId}`, {
    reason,
  });
  return response.data;
}

/**
 * 審核通過證照
 */
export async function approveCertification(certId: number): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/approve-certification/${certId}`);
  return response.data;
}

/**
 * 拒絕證照
 */
export async function rejectCertification(certId: number, reason?: string): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/reject-certification/${certId}`, {
    reason,
  });
  return response.data;
}

/**
 * 審核通過工作經驗
 */
export async function approveExperience(expId: number): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/approve-experience/${expId}`);
  return response.data;
}

/**
 * 拒絕工作經驗
 */
export async function rejectExperience(expId: number, reason?: string): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/reject-experience/${expId}`, {
    reason,
  });
  return response.data;
}

// ========== Salesperson Application Management APIs ==========

export interface SalespersonApplication {
  id: number;
  name: string;
  email: string;
  role: string;
  salesperson_status: 'pending' | 'approved' | 'rejected';
  salesperson_applied_at: string;
  salesperson_profile?: {
    full_name: string;
    phone: string;
    bio: string | null;
    specialties: string | null;
  };
}

/**
 * 取得待審核的業務員申請列表
 */
export async function getSalespersonApplications(): Promise<ApiResponse<SalespersonApplication[]>> {
  const response = await apiClient.get<ApiResponse<SalespersonApplication[]>>('/admin/salesperson-applications');
  return response.data;
}

/**
 * 批准業務員申請
 */
export async function approveSalesperson(userId: number): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/salesperson-applications/${userId}/approve`);
  return response.data;
}

/**
 * 拒絕業務員申請
 */
export interface RejectSalespersonRequest {
  rejection_reason: string;
  reapply_days?: number;
}

export async function rejectSalesperson(userId: number, data: RejectSalespersonRequest): Promise<ApiResponse> {
  const response = await apiClient.post<ApiResponse>(`/admin/salesperson-applications/${userId}/reject`, data);
  return response.data;
}

// ========== User Management APIs ==========

export interface GetUsersParams {
  role?: 'admin' | 'salesperson' | 'user';
  status?: 'pending' | 'active' | 'inactive';
  page?: number;
  per_page?: number;
}

/**
 * 取得使用者列表
 */
export async function getUsers(params?: GetUsersParams): Promise<ApiResponse<User[]>> {
  const response = await apiClient.get<ApiResponse<User[]>>('/admin/users', { params });
  return response.data;
}

/**
 * 更新使用者狀態
 */
export async function updateUserStatus(
  userId: number,
  status: 'active' | 'inactive'
): Promise<ApiResponse> {
  const response = await apiClient.put<ApiResponse>(`/admin/users/${userId}/status`, {
    status,
  });
  return response.data;
}

/**
 * 刪除使用者
 */
export async function deleteUser(userId: number): Promise<ApiResponse> {
  const response = await apiClient.delete<ApiResponse>(`/admin/users/${userId}`);
  return response.data;
}

// ========== Settings APIs ==========

/**
 * 取得產業列表
 */
export async function getIndustries(): Promise<ApiResponse<Industry[]>> {
  const response = await apiClient.get<ApiResponse<Industry[]>>('/admin/settings/industries');
  return response.data;
}

export interface CreateIndustryRequest {
  name: string;
  slug: string;
  description?: string;
}

/**
 * 新增產業
 */
export async function createIndustry(data: CreateIndustryRequest): Promise<ApiResponse<Industry>> {
  const response = await apiClient.post<ApiResponse<Industry>>('/admin/settings/industries', data);
  return response.data;
}

/**
 * 刪除產業
 */
export async function deleteIndustry(industryId: number): Promise<ApiResponse> {
  const response = await apiClient.delete<ApiResponse>(`/admin/settings/industries/${industryId}`);
  return response.data;
}

/**
 * 取得地區列表
 */
export async function getRegions(): Promise<ApiResponse<Region[]>> {
  const response = await apiClient.get<ApiResponse<Region[]>>('/admin/settings/regions');
  return response.data;
}

export interface CreateRegionRequest {
  name: string;
  slug: string;
  parent_id?: number;
}

/**
 * 新增地區
 */
export async function createRegion(data: CreateRegionRequest): Promise<ApiResponse<Region>> {
  const response = await apiClient.post<ApiResponse<Region>>('/admin/settings/regions', data);
  return response.data;
}

/**
 * 刪除地區
 */
export async function deleteRegion(regionId: number): Promise<ApiResponse> {
  const response = await apiClient.delete<ApiResponse>(`/admin/settings/regions/${regionId}`);
  return response.data;
}

// ========== Statistics APIs ==========

/**
 * 取得平台統計資料
 */
export async function getStatistics(): Promise<ApiResponse<Statistics>> {
  const response = await apiClient.get<ApiResponse<Statistics>>('/admin/statistics');
  return response.data;
}
