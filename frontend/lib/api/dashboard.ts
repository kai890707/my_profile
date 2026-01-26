import { apiClient } from './client';
import type {
  TimeRange,
  SalespersonStats,
  TrendData,
  Contact,
  AdminOverview,
  Salesperson,
  Activity,
} from '@/types/dashboard';

// ============================================
// 業務員 API Functions
// ============================================

/**
 * 取得業務員統計數據
 * @param range - 時間範圍 ('today' | '7days' | '30days')
 * @returns 統計數據 (瀏覽數、聯繫數、增長率)
 */
export async function getSalespersonStats(
  range: TimeRange
): Promise<SalespersonStats> {
  const response = await apiClient.get<{
    success: boolean;
    data: SalespersonStats;
  }>('/dashboard/salesperson/stats', {
    params: { range },
  });

  return response.data.data;
}

/**
 * 取得業務員趨勢數據
 * @param range - 時間範圍
 * @returns 每日趨勢數據陣列
 */
export async function getSalespersonTrends(
  range: TimeRange
): Promise<TrendData[]> {
  const response = await apiClient.get<{
    success: boolean;
    data: { trends: TrendData[] };
  }>('/dashboard/salesperson/trends', {
    params: { range },
  });

  return response.data.data.trends;
}

/**
 * 取得最近聯繫記錄
 * @param limit - 限制筆數（預設 10）
 * @returns 聯繫記錄陣列
 */
export async function getRecentContacts(limit = 10): Promise<Contact[]> {
  const response = await apiClient.get<{
    success: boolean;
    data: { contacts: Contact[] };
  }>('/dashboard/salesperson/recent-contacts', {
    params: { limit },
  });

  return response.data.data.contacts;
}

// ============================================
// 管理員 API Functions
// ============================================

/**
 * 取得平台整體概覽
 * @returns 平台 KPI 數據
 */
export async function getAdminOverview(): Promise<AdminOverview> {
  const response = await apiClient.get<{
    success: boolean;
    data: AdminOverview;
  }>('/dashboard/admin/overview');

  return response.data.data;
}

/**
 * 取得熱門業務員 Top 10
 * @returns 業務員陣列（按瀏覽數降序）
 */
export async function getTopSalespersons(): Promise<Salesperson[]> {
  const response = await apiClient.get<{
    success: boolean;
    data: { salespersons: Salesperson[] };
  }>('/dashboard/admin/top-salespersons');

  return response.data.data.salespersons;
}

/**
 * 取得業務員活躍度統計
 * @returns 活躍度數據
 */
export async function getAdminActivity(): Promise<Activity> {
  const response = await apiClient.get<{
    success: boolean;
    data: Activity;
  }>('/dashboard/admin/activity');

  return response.data.data;
}

/**
 * 取得平台成長趨勢
 * @returns 每日趨勢數據陣列（過去 30 天）
 */
export async function getAdminTrends(): Promise<TrendData[]> {
  const response = await apiClient.get<{
    success: boolean;
    data: { trends: TrendData[] };
  }>('/dashboard/admin/trends');

  return response.data.data.trends;
}
