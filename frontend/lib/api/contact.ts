import { apiClient } from './client';
import type { ApiResponse, ContactInfo, ContactRequest } from '@/types/api';

// ========== Contact Info APIs ==========

/**
 * 取得業務員公開聯繫資訊
 */
export async function getContactInfo(salespersonId: number): Promise<ApiResponse<ContactInfo>> {
  const response = await apiClient.get<ApiResponse<ContactInfo>>(
    `/salesperson/${salespersonId}/contact-info`
  );
  return response.data;
}

// ========== Contact Request APIs ==========

/**
 * 提交聯繫請求
 */
export interface CreateContactRequestRequest {
  salesperson_id: number;
  phone?: string;
  message: string;
}

export async function createContactRequest(
  data: CreateContactRequestRequest
): Promise<ApiResponse<ContactRequest>> {
  const response = await apiClient.post<ApiResponse<ContactRequest>>('/contact-requests', data);
  return response.data;
}
