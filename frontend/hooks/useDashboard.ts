import { useQuery, UseQueryResult } from '@tanstack/react-query';
import {
  getSalespersonStats,
  getSalespersonTrends,
  getRecentContacts,
  getAdminOverview,
  getTopSalespersons,
  getAdminActivity,
  getAdminTrends,
} from '@/lib/api/dashboard';
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
// 業務員 Dashboard Hooks
// ============================================

/**
 * 查詢業務員統計數據
 * @param range - 時間範圍
 * @returns React Query result
 */
export function useSalespersonStats(
  range: TimeRange
): UseQueryResult<SalespersonStats, Error> {
  return useQuery({
    queryKey: ['salesperson', 'stats', range],
    queryFn: () => getSalespersonStats(range),
    staleTime: 5 * 60 * 1000, // 5 分鐘
    gcTime: 10 * 60 * 1000,   // 10 分鐘
    refetchOnWindowFocus: true,
    retry: 3,
    retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
  });
}

/**
 * 查詢業務員趨勢數據
 * @param range - 時間範圍
 * @returns React Query result
 */
export function useSalespersonTrends(
  range: TimeRange
): UseQueryResult<TrendData[], Error> {
  return useQuery({
    queryKey: ['salesperson', 'trends', range],
    queryFn: () => getSalespersonTrends(range),
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    refetchOnWindowFocus: true,
    retry: 3,
  });
}

/**
 * 查詢最近聯繫記錄
 * @param limit - 限制筆數（預設 10）
 * @returns React Query result
 */
export function useRecentContacts(
  limit = 10
): UseQueryResult<Contact[], Error> {
  return useQuery({
    queryKey: ['salesperson', 'contacts', 'recent', limit],
    queryFn: () => getRecentContacts(limit),
    staleTime: 2 * 60 * 1000, // 2 分鐘（聯繫記錄更即時）
    gcTime: 5 * 60 * 1000,
    refetchOnWindowFocus: true,
    retry: 3,
  });
}

// ============================================
// 管理員 Dashboard Hooks
// ============================================

/**
 * 查詢平台整體概覽
 * @returns React Query result
 */
export function useAdminOverview(): UseQueryResult<AdminOverview, Error> {
  return useQuery({
    queryKey: ['admin', 'overview'],
    queryFn: getAdminOverview,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    refetchOnWindowFocus: true,
    retry: 3,
  });
}

/**
 * 查詢熱門業務員 Top 10
 * @returns React Query result
 */
export function useTopSalespersons(): UseQueryResult<Salesperson[], Error> {
  return useQuery({
    queryKey: ['admin', 'top-salespersons'],
    queryFn: getTopSalespersons,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    refetchOnWindowFocus: true,
    retry: 3,
  });
}

/**
 * 查詢業務員活躍度
 * @returns React Query result
 */
export function useAdminActivity(): UseQueryResult<Activity, Error> {
  return useQuery({
    queryKey: ['admin', 'activity'],
    queryFn: getAdminActivity,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    refetchOnWindowFocus: true,
    retry: 3,
  });
}

/**
 * 查詢平台成長趨勢
 * @returns React Query result
 */
export function useAdminTrends(): UseQueryResult<TrendData[], Error> {
  return useQuery({
    queryKey: ['admin', 'trends'],
    queryFn: getAdminTrends,
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
    refetchOnWindowFocus: true,
    retry: 3,
  });
}
