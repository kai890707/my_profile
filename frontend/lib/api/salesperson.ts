import { apiClient } from './client';
import type {
  ApiResponse,
  SalespersonProfile,
  Experience,
  Certification,
  Company,
  ApprovalStatusData,
} from '@/types/api';

// ========== Profile APIs ==========

/**
 * 取得業務員狀態（包含審核狀態和重新申請資訊）
 */
export interface SalespersonStatusResponse {
  role: 'user' | 'salesperson' | 'admin';
  salesperson_status: 'pending' | 'approved' | 'rejected' | null;
  salesperson_applied_at: string | null;
  salesperson_approved_at: string | null;
  rejection_reason: string | null;
  can_reapply: boolean;
  can_reapply_at: string | null;
  days_until_reapply: number | null;
}

export async function getSalespersonStatus(): Promise<ApiResponse<SalespersonStatusResponse>> {
  const response = await apiClient.get<ApiResponse<SalespersonStatusResponse>>('/salesperson/status');
  return response.data;
}

/**
 * 升級為業務員（一般使用者 → 業務員）
 */
export interface UpgradeToSalespersonRequest {
  full_name: string;
  phone: string;
  bio?: string;
  specialties?: string;
  service_regions?: string[];
}

export async function upgradeToSalesperson(data: UpgradeToSalespersonRequest): Promise<ApiResponse<{ user: any; profile: SalespersonProfile }>> {
  const response = await apiClient.post<ApiResponse<{ user: any; profile: SalespersonProfile }>>('/salesperson/upgrade', data);
  return response.data;
}

/**
 * 取得個人檔案
 */
export async function getProfile(): Promise<ApiResponse<SalespersonProfile>> {
  const response = await apiClient.get<ApiResponse<SalespersonProfile>>('/salesperson/profile');
  return response.data;
}

/**
 * 更新個人檔案
 */
export interface UpdateProfileRequest {
  full_name?: string;
  phone?: string;
  bio?: string;
  specialties?: string;
  service_regions?: string[]; // JSON array of region names
  avatar?: string; // Base64 encoded image
  company_id?: number; // Company ID to join
}

export async function updateProfile(data: UpdateProfileRequest): Promise<ApiResponse<SalespersonProfile>> {
  const response = await apiClient.put<ApiResponse<SalespersonProfile>>('/salesperson/profile', data);
  return response.data;
}

// ========== Company APIs ==========

/**
 * 儲存公司資訊
 */
export interface SaveCompanyRequest {
  name: string;
}

export async function saveCompany(data: SaveCompanyRequest): Promise<ApiResponse<Company>> {
  const response = await apiClient.post<ApiResponse<Company>>('/salesperson/company', data);
  return response.data;
}

// ========== Experience APIs ==========

/**
 * 取得工作經驗清單
 */
export async function getExperiences(): Promise<ApiResponse<Experience[]>> {
  const response = await apiClient.get<ApiResponse<Experience[]>>('/salesperson/experiences');
  return response.data;
}

/**
 * 新增工作經驗
 */
export interface CreateExperienceRequest {
  company: string;
  position: string;
  start_date: string; // YYYY-MM-DD
  end_date?: string | null; // YYYY-MM-DD
  description?: string;
}

export async function createExperience(data: CreateExperienceRequest): Promise<ApiResponse<Experience>> {
  const response = await apiClient.post<ApiResponse<Experience>>('/salesperson/experiences', data);
  return response.data;
}

/**
 * 更新工作經驗
 */
export async function updateExperience(id: number, data: CreateExperienceRequest): Promise<ApiResponse<Experience>> {
  const response = await apiClient.put<ApiResponse<Experience>>(`/salesperson/experiences/${id}`, data);
  return response.data;
}

/**
 * 刪除工作經驗
 */
export async function deleteExperience(id: number): Promise<ApiResponse<void>> {
  const response = await apiClient.delete<ApiResponse<void>>(`/salesperson/experiences/${id}`);
  return response.data;
}

// ========== Certification APIs ==========

/**
 * 取得證照清單
 */
export async function getCertifications(): Promise<ApiResponse<Certification[]>> {
  const response = await apiClient.get<ApiResponse<Certification[]>>('/salesperson/certifications');
  return response.data;
}

/**
 * 上傳證照
 */
export interface CreateCertificationRequest {
  name: string;
  issuer: string;
  issue_date: string; // YYYY-MM-DD
  expiry_date?: string | null; // YYYY-MM-DD
  description?: string;
  file_data?: string; // Base64 encoded file
}

export async function createCertification(data: CreateCertificationRequest): Promise<ApiResponse<Certification>> {
  const response = await apiClient.post<ApiResponse<Certification>>('/salesperson/certifications', data);
  return response.data;
}

/**
 * 刪除證照
 */
export async function deleteCertification(id: number): Promise<ApiResponse<void>> {
  const response = await apiClient.delete<ApiResponse<void>>(`/salesperson/certifications/${id}`);
  return response.data;
}

// ========== Approval Status API ==========

/**
 * 查詢審核狀態
 */
export async function getApprovalStatus(): Promise<ApiResponse<ApprovalStatusData>> {
  const response = await apiClient.get<ApiResponse<ApprovalStatusData>>('/salesperson/approval-status');
  return response.data;
}
