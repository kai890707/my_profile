import { useQuery, UseQueryResult } from '@tanstack/react-query';
import {
  searchSalespersons,
  getSalespersonDetail,
  getIndustries,
  getRegions,
} from '@/lib/api/search';
import {
  PaginatedResponse,
  SearchParams,
  SalespersonSearchResult,
  SalespersonProfile,
  Industry,
  Region,
} from '@/types/api';

/**
 * 搜尋業務員 Hook
 */
export function useSearchSalespersons(params: SearchParams) {
  return useQuery<PaginatedResponse<SalespersonSearchResult>>({
    queryKey: ['salespersons', params],
    queryFn: () => searchSalespersons(params),
    staleTime: 30000, // 30 秒
  });
}

/**
 * 取得業務員詳細資料 Hook
 */
export function useSalespersonDetail(id: number) {
  return useQuery<SalespersonProfile>({
    queryKey: ['salesperson', id],
    queryFn: () => getSalespersonDetail(id),
    enabled: !!id,
    staleTime: 60000, // 60 秒
  });
}

/**
 * 取得產業類別列表 Hook
 */
export function useIndustries() {
  return useQuery<Industry[]>({
    queryKey: ['industries'],
    queryFn: getIndustries,
    staleTime: 300000, // 5 分鐘
  });
}

/**
 * 取得地區列表 Hook
 */
export function useRegions() {
  return useQuery<Region[]>({
    queryKey: ['regions'],
    queryFn: getRegions,
    staleTime: 300000, // 5 分鐘
  });
}
