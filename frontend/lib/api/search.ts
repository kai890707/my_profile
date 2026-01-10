import { apiClient } from './client';
import {
  ApiResponse,
  PaginatedResponse,
  SearchParams,
  SalespersonSearchResult,
  SalespersonProfile,
} from '@/types/api';

/**
 * 搜尋業務員（僅返回已審核通過的業務員）
 */
export async function searchSalespersons(
  params: SearchParams
): Promise<PaginatedResponse<SalespersonSearchResult>> {
  const response = await apiClient.get<ApiResponse<PaginatedResponse<SalespersonSearchResult>>>(
    '/salespeople',
    { params }
  );
  return response.data.data!;
}

/**
 * 取得業務員詳細資料
 */
export async function getSalespersonDetail(id: number): Promise<SalespersonProfile> {
  const response = await apiClient.get<ApiResponse<SalespersonProfile>>(
    `/search/salespersons/${id}`
  );
  return response.data.data!;
}

/**
 * 取得產業類別列表
 */
export async function getIndustries() {
  const response = await apiClient.get('/industries');
  return response.data.data;
}

/**
 * 取得地區列表
 */
export async function getRegions() {
  const response = await apiClient.get('/regions');
  return response.data.data;
}
