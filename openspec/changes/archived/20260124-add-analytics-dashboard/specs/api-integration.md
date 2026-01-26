# API 整合規格 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24
**Developer**: Senior Frontend Engineer

---

## 📋 目錄

- [整合概覽](#整合概覽)
- [API Client 函數](#api-client-函數)
- [TypeScript 類型定義](#typescript-類型定義)
- [React Query Hooks](#react-query-hooks)
- [錯誤處理](#錯誤處理)
- [快取策略](#快取策略)

---

## 🎯 整合概覽

### API 端點總覽

本功能需要整合 7 個 API 端點：

| 端點 | 方法 | 用途 | 權限 |
|------|------|------|------|
| `/api/dashboard/salesperson/stats` | GET | 業務員統計數據 | salesperson |
| `/api/dashboard/salesperson/trends` | GET | 業務員趨勢數據 | salesperson |
| `/api/dashboard/salesperson/recent-contacts` | GET | 最近聯繫記錄 | salesperson |
| `/api/dashboard/admin/overview` | GET | 平台整體概覽 | admin |
| `/api/dashboard/admin/top-salespersons` | GET | 熱門業務員 Top 10 | admin |
| `/api/dashboard/admin/activity` | GET | 業務員活躍度 | admin |
| `/api/dashboard/admin/trends` | GET | 平台成長趨勢 | admin |

### 技術架構

```
Frontend Components
    ↓ (使用 React Query Hooks)
React Query Hooks (useDashboard.ts)
    ↓ (呼叫 API Client)
API Client Functions (lib/api/dashboard.ts)
    ↓ (使用 Axios)
Axios Instance (lib/api/axios.ts)
    ↓ (HTTP Request + JWT Token)
Backend API (Laravel)
```

---

## 🔌 API Client 函數

### 檔案位置

`lib/api/dashboard.ts`

### 完整實作

```typescript
import { apiClient } from './axios';
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
```

---

## 📊 TypeScript 類型定義

### 檔案位置

`types/dashboard.ts`

### 完整實作

```typescript
// ============================================
// Common Types
// ============================================

/**
 * 時間範圍類型
 */
export type TimeRange = 'today' | '7days' | '30days';

/**
 * 聯繫狀態類型
 */
export type ContactStatus = 'pending' | 'contacted' | 'closed';

// ============================================
// 業務員 Dashboard Types
// ============================================

/**
 * 業務員統計數據
 */
export interface SalespersonStats {
  profile_views: number;          // 總瀏覽數
  contact_requests: number;       // 總聯繫數
  growth_rate: number;            // 增長率（%）
  period: TimeRange;              // 統計時間範圍
  previous_profile_views: number; // 上一期瀏覽數
  previous_contact_requests: number; // 上一期聯繫數
}

/**
 * 趨勢數據點
 */
export interface TrendData {
  date: string;                   // 日期 (YYYY-MM-DD)
  profile_views: number;          // 瀏覽數
  contact_requests: number;       // 聯繫數
  unique_visitors?: number;       // 獨立訪客數（可選）
}

/**
 * 聯繫記錄
 */
export interface Contact {
  id: number;
  customer_name: string;          // 客戶姓名
  customer_email: string;         // 客戶 Email
  customer_phone?: string;        // 客戶電話（可選）
  message: string;                // 聯繫訊息
  status: ContactStatus;          // 狀態
  created_at: string;             // 建立時間 (ISO 8601)
  updated_at: string;             // 更新時間
}

// ============================================
// 管理員 Dashboard Types
// ============================================

/**
 * 平台整體概覽
 */
export interface AdminOverview {
  total_salespersons: number;          // 總業務員數
  total_profile_views: number;         // 總瀏覽數
  total_contact_requests: number;      // 總聯繫數
  platform_conversion_rate: number;    // 平台轉換率（%）
  period: string;                      // 統計時間範圍
}

/**
 * 業務員資訊（Top 10）
 */
export interface Salesperson {
  id: number;
  name: string;                   // 業務員姓名
  email: string;                  // 業務員 Email
  profile_views: number;          // 瀏覽數
  contact_requests: number;       // 聯繫數
  conversion_rate: number;        // 轉換率（%）
  rank: number;                   // 排名
}

/**
 * 業務員活躍度
 */
export interface Activity {
  active_salespersons: number;        // 活躍業務員數（過去 7 天有瀏覽）
  inactive_salespersons: number;      // 低活躍業務員數（0 瀏覽）
  total_salespersons: number;         // 總業務員數
  activity_rate: number;              // 活躍率（%）
  period: string;                     // 統計時間範圍
}

// ============================================
// API Response Wrappers
// ============================================

/**
 * 成功回應格式
 */
export interface ApiSuccessResponse<T> {
  success: true;
  data: T;
  message?: string;
}

/**
 * 錯誤回應格式
 */
export interface ApiErrorResponse {
  success: false;
  error: {
    code: string;
    message: string;
    details?: Record<string, string[]>;
  };
}

/**
 * 通用 API 回應類型
 */
export type ApiResponse<T> = ApiSuccessResponse<T> | ApiErrorResponse;
```

---

## 🪝 React Query Hooks

### 檔案位置

`hooks/useDashboard.ts`

### 完整實作

```typescript
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
```

---

## ⚠️ 錯誤處理

### 全域錯誤處理

**檔案**: `lib/api/axios.ts`

```typescript
import axios, { AxiosError, AxiosInstance } from 'axios';
import { toast } from 'sonner';

// Axios 實例已配置（假設已存在）
export const apiClient: AxiosInstance = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8080/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Response Interceptor - 統一錯誤處理
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiErrorResponse>) => {
    // 提取錯誤訊息
    const errorMessage =
      error.response?.data?.error?.message ||
      error.message ||
      '發生未知錯誤';

    // 根據狀態碼處理
    switch (error.response?.status) {
      case 401:
        toast.error('請重新登入');
        // 重定向到登入頁
        window.location.href = '/login';
        break;

      case 403:
        toast.error('您沒有權限存取此資源');
        break;

      case 404:
        toast.error('找不到此資源');
        break;

      case 422:
        // 驗證錯誤
        const details = error.response.data?.error?.details;
        if (details) {
          Object.values(details).forEach((messages) => {
            messages.forEach((msg) => toast.error(msg));
          });
        } else {
          toast.error(errorMessage);
        }
        break;

      case 500:
        toast.error('伺服器錯誤，請稍後再試');
        break;

      default:
        toast.error(errorMessage);
    }

    return Promise.reject(error);
  }
);
```

### React Query 錯誤處理

**檔案**: `lib/query/queryClient.ts`

```typescript
import { QueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 分鐘
      gcTime: 10 * 60 * 1000,   // 10 分鐘
      retry: 3,
      retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
      refetchOnWindowFocus: true,
      refetchOnReconnect: true,
      // 全域錯誤處理（可選）
      onError: (error) => {
        console.error('React Query Error:', error);
        // 這裡已由 Axios Interceptor 處理，不重複顯示
      },
    },
    mutations: {
      retry: 1,
      onError: (error) => {
        console.error('Mutation Error:', error);
      },
    },
  },
});
```

---

## 💾 快取策略

### 快取設計原則

| 資料類型 | staleTime | gcTime | refetchOnWindowFocus |
|---------|-----------|--------|---------------------|
| **統計數據** | 5 分鐘 | 10 分鐘 | ✅ |
| **趨勢圖表** | 5 分鐘 | 10 分鐘 | ✅ |
| **聯繫記錄** | 2 分鐘 | 5 分鐘 | ✅ |
| **熱門業務員** | 5 分鐘 | 10 分鐘 | ✅ |
| **活躍度** | 5 分鐘 | 10 分鐘 | ✅ |

### Query Key 命名規範

```typescript
// 業務員 Dashboard
['salesperson', 'stats', range]           // 統計數據
['salesperson', 'trends', range]          // 趨勢數據
['salesperson', 'contacts', 'recent', limit] // 聯繫記錄

// 管理員 Dashboard
['admin', 'overview']                     // 平台概覽
['admin', 'top-salespersons']             // 熱門業務員
['admin', 'activity']                     // 活躍度
['admin', 'trends']                       // 平台趨勢
```

### 手動重新整理

```typescript
import { useQueryClient } from '@tanstack/react-query';

function RefreshButton() {
  const queryClient = useQueryClient();

  const handleRefresh = () => {
    // 重新整理所有業務員數據
    queryClient.invalidateQueries({ queryKey: ['salesperson'] });

    // 或重新整理特定 query
    queryClient.invalidateQueries({ queryKey: ['salesperson', 'stats'] });
  };

  return (
    <button onClick={handleRefresh}>
      刷新數據
    </button>
  );
}
```

---

## 🧪 API 測試範例

### 測試檔案

**檔案**: `__tests__/api/dashboard.test.ts`

```typescript
import { describe, it, expect, vi } from 'vitest';
import { getSalespersonStats, getAdminOverview } from '@/lib/api/dashboard';
import { apiClient } from '@/lib/api/axios';

vi.mock('@/lib/api/axios');

describe('Dashboard API', () => {
  describe('getSalespersonStats', () => {
    it('should fetch salesperson stats successfully', async () => {
      const mockData = {
        profile_views: 1234,
        contact_requests: 56,
        growth_rate: 15.3,
        period: '7days',
        previous_profile_views: 1071,
        previous_contact_requests: 49,
      };

      vi.mocked(apiClient.get).mockResolvedValueOnce({
        data: { success: true, data: mockData },
      });

      const result = await getSalespersonStats('7days');

      expect(result).toEqual(mockData);
      expect(apiClient.get).toHaveBeenCalledWith(
        '/dashboard/salesperson/stats',
        { params: { range: '7days' } }
      );
    });
  });

  describe('getAdminOverview', () => {
    it('should fetch admin overview successfully', async () => {
      const mockData = {
        total_salespersons: 128,
        total_profile_views: 45230,
        total_contact_requests: 3802,
        platform_conversion_rate: 8.4,
        period: '30days',
      };

      vi.mocked(apiClient.get).mockResolvedValueOnce({
        data: { success: true, data: mockData },
      });

      const result = await getAdminOverview();

      expect(result).toEqual(mockData);
      expect(apiClient.get).toHaveBeenCalledWith('/dashboard/admin/overview');
    });
  });
});
```

---

## ✅ API 整合檢查清單

完成 API 整合時，確保：

- [ ] 所有 API 函數已實作（7 個）
- [ ] TypeScript 類型完整定義
- [ ] React Query Hooks 已建立
- [ ] 錯誤處理完整（Axios Interceptor + Error Boundary）
- [ ] 快取策略已配置
- [ ] Query Key 命名一致
- [ ] Loading 狀態處理完整
- [ ] 測試已撰寫（單元測試）
- [ ] API 回應格式符合 Backend 規格

---

**Version**: 1.0
**Last Updated**: 2026-01-24
**Total API Endpoints**: 7
**Dependencies**: Axios, React Query 5.x
