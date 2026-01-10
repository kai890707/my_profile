import { apiClient } from './client';
import type { ApiResponse, Company } from '@/types/api';

// ========== Company APIs ==========

/**
 * 搜尋公司（by tax_id 或 name）
 */
export interface SearchCompaniesParams {
  tax_id?: string;
  name?: string;
}

export interface SearchCompaniesResponse {
  success: boolean;
  exists: boolean;
  companies: Company[];
}

export async function searchCompanies(params: SearchCompaniesParams): Promise<SearchCompaniesResponse> {
  const response = await apiClient.get<SearchCompaniesResponse>('/companies/search', { params });
  return response.data;
}

/**
 * 建立公司
 */
export interface CreateCompanyRequest {
  name: string;
  tax_id?: string | null;
  is_personal: boolean;
}

export async function createCompany(data: CreateCompanyRequest): Promise<ApiResponse<Company>> {
  const response = await apiClient.post<ApiResponse<Company>>('/companies', data);
  return response.data;
}

/**
 * 取得我的公司列表
 */
export async function getMyCompanies(): Promise<ApiResponse<Company[]>> {
  const response = await apiClient.get<ApiResponse<Company[]>>('/companies/my');
  return response.data;
}

/**
 * 取得單一公司詳情
 */
export async function getCompany(id: number): Promise<ApiResponse<Company>> {
  const response = await apiClient.get<ApiResponse<Company>>(`/companies/${id}`);
  return response.data;
}
