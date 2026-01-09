# API Integration Specification

**Project**: YAMU Frontend SPA
**Version**: 1.0.0
**Last Updated**: 2026-01-08

---

## Overview

本規格定義前端如何整合現有的 31 個後端 API 端點，包含：
- API 客戶端架構設計
- 認證與授權機制
- 錯誤處理統一方案
- 每個 API 的呼叫方式和資料格式

---

## API Client Architecture

### Base Configuration

```typescript
// lib/api/client.ts
import axios, { AxiosInstance, AxiosError, InternalAxiosRequestConfig } from 'axios';
import { getAccessToken, refreshAccessToken, clearTokens } from '@/lib/auth/token';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8080/api';

const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request Interceptor - 自動添加 Token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = getAccessToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response Interceptor - 處理 Token 過期
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

    // Token 過期，嘗試續期
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const newToken = await refreshAccessToken();
        if (newToken && originalRequest.headers) {
          originalRequest.headers.Authorization = `Bearer ${newToken}`;
          return apiClient(originalRequest);
        }
      } catch (refreshError) {
        // Refresh 失敗，清除 Token 並導向登入
        clearTokens();
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default apiClient;
```

### Environment Variables

```env
# .env.local (開發環境)
NEXT_PUBLIC_API_BASE_URL=http://localhost:8080/api

# .env.production (生產環境)
NEXT_PUBLIC_API_BASE_URL=https://api.yamu.com/api
```

---

## Authentication APIs (5 APIs)

### 1. POST /api/auth/register

**功能**: 業務員註冊

**Request**:
```typescript
interface RegisterRequest {
  username: string;
  email: string;
  password: string;
  full_name: string;
  phone?: string;
}
```

**Response (201)**:
```typescript
interface RegisterResponse {
  status: 'success';
  data: {
    user: {
      id: number;
      username: string;
      email: string;
      role: 'salesperson';
      status: 'pending';
    };
    profile: {
      id: number;
      user_id: number;
      full_name: string;
      phone: string | null;
    };
  };
  message: string;
}
```

**實作**:
```typescript
// lib/api/auth.ts
import apiClient from './client';
import { RegisterRequest, RegisterResponse } from '@/types/auth';

export async function register(data: RegisterRequest): Promise<RegisterResponse> {
  const response = await apiClient.post<RegisterResponse>('/auth/register', data);
  return response.data;
}
```

**使用範例**:
```typescript
// app/(auth)/register/page.tsx
import { register } from '@/lib/api/auth';

const handleRegister = async (formData: RegisterRequest) => {
  try {
    const result = await register(formData);
    toast.success(result.message);
    router.push('/login');
  } catch (error) {
    if (axios.isAxiosError(error) && error.response) {
      toast.error(error.response.data.message);
    }
  }
};
```

---

### 2. POST /api/auth/login

**功能**: 使用者登入

**Request**:
```typescript
interface LoginRequest {
  email: string;
  password: string;
}
```

**Response (200)**:
```typescript
interface LoginResponse {
  status: 'success';
  data: {
    access_token: string;
    refresh_token: string;
    expires_in: number;
    user: {
      id: number;
      username: string;
      email: string;
      role: 'admin' | 'salesperson' | 'user';
      status: 'active' | 'pending' | 'inactive';
    };
  };
  message: string;
}
```

**實作**:
```typescript
// lib/api/auth.ts
export async function login(credentials: LoginRequest): Promise<LoginResponse> {
  const response = await apiClient.post<LoginResponse>('/auth/login', credentials);

  // 儲存 Token
  const { access_token, refresh_token } = response.data.data;
  setAccessToken(access_token);
  setRefreshToken(refresh_token);

  return response.data;
}
```

**錯誤處理**:
```typescript
// 401: 帳號或密碼錯誤
{
  "status": "error",
  "message": "帳號或密碼錯誤"
}

// 403: 帳號未審核或已停用
{
  "status": "error",
  "message": "您的帳號尚未通過審核"
}
```

---

### 3. POST /api/auth/refresh

**功能**: 刷新 Access Token

**Request**:
```typescript
// Request Body (使用 Refresh Token)
{
  "refresh_token": string
}
```

**Response (200)**:
```typescript
interface RefreshResponse {
  status: 'success';
  data: {
    access_token: string;
    expires_in: number;
  };
  message: string;
}
```

**實作**:
```typescript
// lib/auth/token.ts
export async function refreshAccessToken(): Promise<string | null> {
  const refreshToken = getRefreshToken();
  if (!refreshToken) return null;

  try {
    const response = await axios.post<RefreshResponse>(
      `${API_BASE_URL}/auth/refresh`,
      { refresh_token: refreshToken }
    );

    const newAccessToken = response.data.data.access_token;
    setAccessToken(newAccessToken);
    return newAccessToken;
  } catch (error) {
    clearTokens();
    return null;
  }
}
```

---

### 4. POST /api/auth/logout

**功能**: 使用者登出

**Authentication**: Required

**Response (200)**:
```typescript
{
  "status": "success",
  "message": "登出成功"
}
```

**實作**:
```typescript
// lib/api/auth.ts
export async function logout(): Promise<void> {
  try {
    await apiClient.post('/auth/logout');
  } finally {
    // 無論成功失敗都清除本地 Token
    clearTokens();
  }
}
```

---

### 5. GET /api/auth/me

**功能**: 取得當前使用者資訊

**Authentication**: Required

**Response (200)**:
```typescript
interface MeResponse {
  status: 'success';
  data: {
    id: number;
    username: string;
    email: string;
    role: 'admin' | 'salesperson' | 'user';
    status: 'active' | 'pending' | 'inactive';
    created_at: string;
  };
}
```

**實作**:
```typescript
// lib/api/auth.ts
export async function getCurrentUser(): Promise<MeResponse> {
  const response = await apiClient.get<MeResponse>('/auth/me');
  return response.data;
}
```

**使用範例**:
```typescript
// hooks/useAuth.ts
import { getCurrentUser } from '@/lib/api/auth';
import { useQuery } from '@tanstack/react-query';

export function useAuth() {
  return useQuery({
    queryKey: ['auth', 'me'],
    queryFn: getCurrentUser,
    retry: false,
    staleTime: 5 * 60 * 1000, // 5 分鐘
  });
}
```

---

## Search APIs (2 APIs)

### 6. GET /api/search/salespersons

**功能**: 搜尋業務員

**Query Parameters**:
```typescript
interface SearchParams {
  keyword?: string;        // 關鍵字（姓名、簡介、公司）
  company?: string;        // 公司名稱
  industry_id?: number;    // 產業類別 ID
  region_id?: number;      // 服務地區 ID
  page?: number;           // 頁碼（預設 1）
  per_page?: number;       // 每頁筆數（預設 12）
}
```

**Response (200)**:
```typescript
interface SearchResponse {
  status: 'success';
  data: {
    salespersons: Array<{
      id: number;
      user_id: number;
      full_name: string;
      company_name: string | null;
      industry_name: string | null;
      bio: string | null;
      specialties: string | null;
      avatar_url: string | null; // Base64 或 URL
    }>;
    pagination: {
      current_page: number;
      per_page: number;
      total: number;
      total_pages: number;
    };
  };
}
```

**實作**:
```typescript
// lib/api/search.ts
import apiClient from './client';
import { SearchParams, SearchResponse } from '@/types/search';

export async function searchSalespersons(params: SearchParams): Promise<SearchResponse> {
  const response = await apiClient.get<SearchResponse>('/search/salespersons', { params });
  return response.data;
}
```

**使用範例**:
```typescript
// app/(public)/search/page.tsx
import { searchSalespersons } from '@/lib/api/search';
import { useQuery } from '@tanstack/react-query';

const SearchPage = () => {
  const [filters, setFilters] = useState<SearchParams>({
    keyword: '',
    page: 1,
    per_page: 12,
  });

  const { data, isLoading } = useQuery({
    queryKey: ['search', 'salespersons', filters],
    queryFn: () => searchSalespersons(filters),
  });

  return (
    // ... UI implementation
  );
};
```

---

### 7. GET /api/search/salespersons/:id

**功能**: 查看業務員詳細資料

**URL Parameters**:
- `id`: 業務員 ID (integer)

**Response (200)**:
```typescript
interface SalespersonDetailResponse {
  status: 'success';
  data: {
    id: number;
    user_id: number;
    full_name: string;
    phone: string | null;
    bio: string | null;
    specialties: string | null;
    service_regions: number[];
    avatar_url: string | null;
    company: {
      id: number;
      name: string;
      industry_name: string;
    } | null;
    experiences: Array<{
      id: number;
      company: string;
      title: string;
      start_date: string;
      end_date: string | null;
      description: string | null;
    }>;
    certifications: Array<{
      id: number;
      name: string;
      issuer: string;
      issue_date: string;
      description: string | null;
      image_url: string | null;
      approval_status: 'pending' | 'approved' | 'rejected';
    }>;
  };
}
```

**實作**:
```typescript
// lib/api/search.ts
export async function getSalespersonDetail(id: number): Promise<SalespersonDetailResponse> {
  const response = await apiClient.get<SalespersonDetailResponse>(`/search/salespersons/${id}`);
  return response.data;
}
```

---

## Salesperson Management APIs (9 APIs)

### 8. GET /api/salesperson/profile

**功能**: 取得個人檔案

**Authentication**: Required (salesperson)

**Response (200)**:
```typescript
interface ProfileResponse {
  status: 'success';
  data: {
    id: number;
    user_id: number;
    full_name: string;
    phone: string | null;
    bio: string | null;
    specialties: string | null;
    service_regions: number[];
    avatar_url: string | null;
    approval_status: 'pending' | 'approved' | 'rejected';
    company: {
      id: number;
      name: string;
      approval_status: 'pending' | 'approved' | 'rejected';
    } | null;
  };
}
```

**實作**:
```typescript
// lib/api/salesperson.ts
export async function getProfile(): Promise<ProfileResponse> {
  const response = await apiClient.get<ProfileResponse>('/salesperson/profile');
  return response.data;
}
```

---

### 9. PUT /api/salesperson/profile

**功能**: 更新個人檔案

**Authentication**: Required (salesperson)

**Request**:
```typescript
interface UpdateProfileRequest {
  full_name?: string;
  phone?: string;
  bio?: string;
  specialties?: string;
  service_regions?: number[];
  avatar?: string; // Base64 encoded image
}
```

**Response (200)**:
```typescript
{
  "status": "success",
  "data": ProfileResponse['data'],
  "message": "個人資料已更新"
}
```

**實作**:
```typescript
// lib/api/salesperson.ts
export async function updateProfile(data: UpdateProfileRequest): Promise<ProfileResponse> {
  const response = await apiClient.put<ProfileResponse>('/salesperson/profile', data);
  return response.data;
}
```

**Base64 圖片處理**:
```typescript
// utils/image.ts
export async function fileToBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = (error) => reject(error);
  });
}

// 壓縮圖片
export async function compressImage(file: File, maxSizeMB = 2): Promise<string> {
  if (file.size > maxSizeMB * 1024 * 1024) {
    // 使用 canvas 壓縮
    // ... implementation
  }
  return fileToBase64(file);
}
```

---

### 10. POST /api/salesperson/company

**功能**: 儲存公司資訊（等待審核）

**Authentication**: Required (salesperson)

**Request**:
```typescript
interface SaveCompanyRequest {
  name: string;
  industry_id: number;
}
```

**Response (201)**:
```typescript
{
  "status": "success",
  "data": {
    "id": number,
    "name": string,
    "industry_id": number,
    "approval_status": "pending"
  },
  "message": "公司資訊已送出，等待審核"
}
```

**實作**:
```typescript
// lib/api/salesperson.ts
export async function saveCompany(data: SaveCompanyRequest) {
  const response = await apiClient.post('/salesperson/company', data);
  return response.data;
}
```

---

### 11-15. Experience & Certification APIs

**Experience APIs**:
- `GET /api/salesperson/experiences` - 取得工作經驗清單
- `POST /api/salesperson/experiences` - 新增工作經驗
- `DELETE /api/salesperson/experiences/:id` - 刪除工作經驗

**Certification APIs**:
- `GET /api/salesperson/certifications` - 取得證照清單
- `POST /api/salesperson/certifications` - 上傳證照

**實作範例** (Experience):
```typescript
// lib/api/salesperson.ts

export async function getExperiences(): Promise<ExperienceResponse> {
  const response = await apiClient.get('/salesperson/experiences');
  return response.data;
}

export async function createExperience(data: CreateExperienceRequest) {
  const response = await apiClient.post('/salesperson/experiences', data);
  return response.data;
}

export async function deleteExperience(id: number) {
  const response = await apiClient.delete(`/salesperson/experiences/${id}`);
  return response.data;
}
```

---

### 16. GET /api/salesperson/approval-status

**功能**: 查詢審核狀態

**Authentication**: Required (salesperson)

**Response (200)**:
```typescript
interface ApprovalStatusResponse {
  status: 'success';
  data: {
    profile_status: 'pending' | 'approved' | 'rejected';
    company_status: 'pending' | 'approved' | 'rejected' | null;
    certifications: Array<{
      id: number;
      name: string;
      approval_status: 'pending' | 'approved' | 'rejected';
      rejected_reason: string | null;
    }>;
  };
}
```

---

## Admin APIs (15 APIs)

### 17. GET /api/admin/pending-approvals

**功能**: 取得所有待審核項目

**Authentication**: Required (admin)

**Response (200)**:
```typescript
interface PendingApprovalsResponse {
  status: 'success';
  data: {
    users: Array<{
      id: number;
      username: string;
      email: string;
      full_name: string;
      status: 'pending';
      created_at: string;
    }>;
    profiles: Array<{
      id: number;
      user_id: number;
      full_name: string;
      approval_status: 'pending';
    }>;
    companies: Array<{
      id: number;
      name: string;
      industry_name: string;
      approval_status: 'pending';
    }>;
    certifications: Array<{
      id: number;
      name: string;
      salesperson_name: string;
      approval_status: 'pending';
    }>;
  };
}
```

---

### 18-24. Approval APIs

**User Approval**:
- `POST /api/admin/approve-user/:id` - 審核通過業務員註冊
- `POST /api/admin/reject-user/:id` - 拒絕業務員註冊

**Company Approval**:
- `POST /api/admin/approve-company/:id` - 審核通過公司資訊
- `POST /api/admin/reject-company/:id` - 拒絕公司資訊

**Certification Approval**:
- `POST /api/admin/approve-certification/:id` - 審核通過證照
- `POST /api/admin/reject-certification/:id` - 拒絕證照

**實作範例**:
```typescript
// lib/api/admin.ts

export async function approveUser(id: number) {
  const response = await apiClient.post(`/admin/approve-user/${id}`);
  return response.data;
}

export async function rejectUser(id: number, reason: string) {
  const response = await apiClient.post(`/admin/reject-user/${id}`, { reason });
  return response.data;
}
```

---

### 25-27. User Management APIs

- `GET /api/admin/users` - 取得使用者清單
- `PUT /api/admin/users/:id/status` - 更新使用者狀態
- `DELETE /api/admin/users/:id` - 刪除使用者

---

### 28-31. Settings & Statistics APIs

- `GET /api/admin/settings/industries` - 取得產業類別
- `POST /api/admin/settings/industries` - 新增產業類別
- `GET /api/admin/settings/regions` - 取得地區列表
- `POST /api/admin/settings/regions` - 新增地區
- `GET /api/admin/statistics` - 取得統計資料

**Statistics Response**:
```typescript
interface StatisticsResponse {
  status: 'success';
  data: {
    total_salespersons: number;
    active_salespersons: number;
    pending_salespersons: number;
    total_companies: number;
    pending_approvals: number;
  };
}
```

---

## Error Handling

### Unified Error Handler

```typescript
// lib/api/errors.ts
import { AxiosError } from 'axios';
import { toast } from 'sonner';

export interface ApiError {
  status: 'error';
  message: string;
  errors?: Record<string, string[]>;
}

export function handleApiError(error: unknown): never {
  if (axios.isAxiosError(error)) {
    const apiError = error.response?.data as ApiError;

    // 顯示錯誤訊息
    if (apiError?.errors) {
      // 驗證錯誤 (422)
      Object.values(apiError.errors).forEach(messages => {
        messages.forEach(msg => toast.error(msg));
      });
    } else if (apiError?.message) {
      toast.error(apiError.message);
    } else {
      toast.error('發生未知錯誤，請稍後再試');
    }

    throw error;
  }

  toast.error('網路連線異常');
  throw error;
}
```

### HTTP Status Code Mapping

| Status Code | 意義 | 處理方式 |
|-------------|------|----------|
| 400 | Bad Request | 顯示錯誤訊息 |
| 401 | Unauthorized | 嘗試 Refresh Token，失敗則導向登入 |
| 403 | Forbidden | 顯示權限不足訊息 |
| 404 | Not Found | 顯示資源不存在 |
| 422 | Validation Error | 顯示表單驗證錯誤 |
| 500 | Server Error | 顯示伺服器錯誤訊息 |

---

## React Query Integration

### Query Client Setup

```typescript
// lib/query/client.ts
import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000, // 1 分鐘
      retry: 1,
      refetchOnWindowFocus: false,
    },
    mutations: {
      retry: false,
    },
  },
});
```

### Query Keys Convention

```typescript
// lib/query/keys.ts
export const queryKeys = {
  auth: {
    me: ['auth', 'me'] as const,
  },
  search: {
    salespersons: (params: SearchParams) => ['search', 'salespersons', params] as const,
    detail: (id: number) => ['search', 'salespersons', id] as const,
  },
  salesperson: {
    profile: ['salesperson', 'profile'] as const,
    experiences: ['salesperson', 'experiences'] as const,
    certifications: ['salesperson', 'certifications'] as const,
  },
  admin: {
    pendingApprovals: ['admin', 'pending-approvals'] as const,
    users: (params?: any) => ['admin', 'users', params] as const,
    statistics: ['admin', 'statistics'] as const,
  },
};
```

### Custom Hooks Examples

```typescript
// hooks/useSearch.ts
import { useQuery } from '@tanstack/react-query';
import { searchSalespersons } from '@/lib/api/search';
import { queryKeys } from '@/lib/query/keys';

export function useSearchSalespersons(params: SearchParams) {
  return useQuery({
    queryKey: queryKeys.search.salespersons(params),
    queryFn: () => searchSalespersons(params),
    enabled: !!params, // 有參數才執行
  });
}

// hooks/useProfile.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { getProfile, updateProfile } from '@/lib/api/salesperson';
import { queryKeys } from '@/lib/query/keys';

export function useProfile() {
  return useQuery({
    queryKey: queryKeys.salesperson.profile,
    queryFn: getProfile,
  });
}

export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: updateProfile,
    onSuccess: () => {
      // 更新成功後刷新個人資料
      queryClient.invalidateQueries({ queryKey: queryKeys.salesperson.profile });
      toast.success('個人資料已更新');
    },
    onError: handleApiError,
  });
}
```

---

## TypeScript Type Definitions

### Complete Type Definitions File

```typescript
// types/api.ts

// ===== Auth Types =====
export interface RegisterRequest {
  username: string;
  email: string;
  password: string;
  full_name: string;
  phone?: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface User {
  id: number;
  username: string;
  email: string;
  role: 'admin' | 'salesperson' | 'user';
  status: 'active' | 'pending' | 'inactive';
  created_at: string;
}

// ===== Search Types =====
export interface SearchParams {
  keyword?: string;
  company?: string;
  industry_id?: number;
  region_id?: number;
  page?: number;
  per_page?: number;
}

export interface SalespersonCard {
  id: number;
  user_id: number;
  full_name: string;
  company_name: string | null;
  industry_name: string | null;
  bio: string | null;
  specialties: string | null;
  avatar_url: string | null;
}

// ===== Salesperson Types =====
export interface Profile {
  id: number;
  user_id: number;
  full_name: string;
  phone: string | null;
  bio: string | null;
  specialties: string | null;
  service_regions: number[];
  avatar_url: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  company: Company | null;
}

export interface Experience {
  id: number;
  company: string;
  title: string;
  start_date: string;
  end_date: string | null;
  description: string | null;
}

export interface Certification {
  id: number;
  name: string;
  issuer: string;
  issue_date: string;
  description: string | null;
  image_url: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
}

// ===== Admin Types =====
export interface PendingApprovals {
  users: User[];
  profiles: Profile[];
  companies: Company[];
  certifications: Certification[];
}

export interface Statistics {
  total_salespersons: number;
  active_salespersons: number;
  pending_salespersons: number;
  total_companies: number;
  pending_approvals: number;
}

// ===== Common Types =====
export interface ApiResponse<T> {
  status: 'success' | 'error';
  data?: T;
  message: string;
  errors?: Record<string, string[]>;
}

export interface Pagination {
  current_page: number;
  per_page: number;
  total: number;
  total_pages: number;
}
```

---

## API Testing Checklist

實作每個 API 整合時必須測試：

### 成功情況
- [ ] 正確的 Request 格式能收到預期 Response
- [ ] Response 資料格式符合 TypeScript 型別定義
- [ ] React Query 能正確快取資料

### 錯誤情況
- [ ] 驗證錯誤 (422) 正確顯示錯誤訊息
- [ ] 未認證 (401) 自動嘗試 Refresh Token
- [ ] 權限不足 (403) 顯示適當訊息
- [ ] 網路錯誤能正確捕獲並提示

### 邊界情況
- [ ] Token 過期時自動續期
- [ ] Refresh Token 失敗時清除並導向登入
- [ ] 並發請求時 Token 續期不衝突
- [ ] 大圖片上傳時有壓縮處理

---

## Next Steps

1. 實作 `lib/api/client.ts` - API 客戶端
2. 實作 `lib/auth/token.ts` - Token 管理
3. 為每個 API 模組建立對應檔案：
   - `lib/api/auth.ts`
   - `lib/api/search.ts`
   - `lib/api/salesperson.ts`
   - `lib/api/admin.ts`
4. 建立所有 TypeScript 型別定義
5. 建立 Custom Hooks 封裝 API 呼叫
6. 撰寫 API 整合測試

---

**Status**: ✅ API Integration Specification Complete
**Next Document**: UI Components Design Specification
