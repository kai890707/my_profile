# API 整合規格 - 業務員詳情 API

**功能**: 工作經驗與專業證照 API 整合
**日期**: 2026-01-20

---

## 📋 目錄

- [API 概述](#api-概述)
- [現有 API 使用](#現有-api-使用)
- [資料結構確認](#資料結構確認)
- [Hook 整合](#hook-整合)
- [錯誤處理](#錯誤處理)
- [快取策略](#快取策略)

---

## 🌐 API 概述

### API 端點

**端點**: `GET /api/search/salesperson/{id}`

**功能**: 取得業務員完整詳情，包含工作經驗和專業證照。

**權限**: 公開 (無需認證)

### 結論

✅ **不需要調整 Backend API**

現有 API 已經包含完整的 `experiences` 和 `certifications` 陣列，可以直接使用。

---

## 🔌 現有 API 使用

### Hook: useSalespersonDetail

**位置**: `frontend/hooks/useSearch.ts`

```typescript
export function useSalespersonDetail(id: number) {
  return useQuery({
    queryKey: ['salesperson', id],
    queryFn: () => searchAPI.getSalespersonDetail(id),
    enabled: id > 0,
    staleTime: 5 * 60 * 1000,  // 5 分鐘
    gcTime: 10 * 60 * 1000,    // 10 分鐘
  });
}
```

**特性**:
- ✅ 使用 React Query 管理狀態
- ✅ 自動快取 (5 分鐘 stale time)
- ✅ 自動重試 (失敗時)
- ✅ Loading/Error 狀態自動管理
- ✅ ID 驗證 (enabled: id > 0)

### API 客戶端函數

**位置**: `frontend/lib/api/search.ts`

```typescript
export const searchAPI = {
  /**
   * 取得業務員詳情
   */
  async getSalespersonDetail(id: number): Promise<SalespersonDetail> {
    const response = await apiClient.get(`/search/salesperson/${id}`);
    return response.data;
  },
};
```

---

## 📦 資料結構確認

### Response Schema

```typescript
interface SalespersonDetail {
  // 基本資料
  id: number;
  full_name: string;
  email: string;
  phone: string;
  avatar: string | null;
  company: {
    id: number;
    name: string;
    logo: string | null;
  } | null;
  specialties: string | null;       // 逗號分隔字串
  service_regions: string[] | string | null;
  bio: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  created_at: string;               // ISO 8601
  updated_at: string;               // ISO 8601

  // 工作經驗 ⭐
  experiences: Experience[];

  // 專業證照 ⭐
  certifications: Certification[];
}

interface Experience {
  id: number;
  user_id: number;
  company: string;
  position: string;
  start_date: string;               // YYYY-MM-DD
  end_date: string | null;          // YYYY-MM-DD or null (至今)
  description: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;       // ISO 8601
  rejected_reason: string | null;
  created_at: string;               // ISO 8601
  updated_at: string;               // ISO 8601
}

interface Certification {
  id: number;
  user_id: number;
  name: string;
  issuer: string;
  issue_date: string;               // YYYY-MM-DD
  expiry_date: string | null;       // YYYY-MM-DD or null
  description: string | null;
  file_url: string | null;          // 證書檔案 URL
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;       // ISO 8601
  rejected_reason: string | null;
  created_at: string;               // ISO 8601
  updated_at: string;               // ISO 8601
}
```

### 資料驗證

```typescript
// 使用 Zod 驗證 (可選)
import { z } from 'zod';

const ExperienceSchema = z.object({
  id: z.number(),
  user_id: z.number(),
  company: z.string(),
  position: z.string(),
  start_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
  end_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).nullable(),
  description: z.string().nullable(),
  approval_status: z.enum(['pending', 'approved', 'rejected']),
  approved_by: z.number().nullable(),
  approved_at: z.string().nullable(),
  rejected_reason: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
});

const CertificationSchema = z.object({
  id: z.number(),
  user_id: z.number(),
  name: z.string(),
  issuer: z.string(),
  issue_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
  expiry_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/).nullable(),
  description: z.string().nullable(),
  file_url: z.string().url().nullable(),
  approval_status: z.enum(['pending', 'approved', 'rejected']),
  approved_by: z.number().nullable(),
  approved_at: z.string().nullable(),
  rejected_reason: z.string().nullable(),
  created_at: z.string(),
  updated_at: z.string(),
});
```

---

## 🔗 Hook 整合

### 頁面中的使用

```tsx
export default function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  // 使用 Hook 取得資料
  const {
    data: salesperson,
    isLoading,
    error,
    refetch,
  } = useSalespersonDetail(id);

  // 資料傳遞給組件
  return (
    <>
      {salesperson?.experiences && (
        <ExperienceTimeline
          experiences={salesperson.experiences}
          isLoading={isLoading}
        />
      )}

      {salesperson?.certifications && (
        <CertificationCards
          certifications={salesperson.certifications}
          isLoading={isLoading}
        />
      )}
    </>
  );
}
```

### 組件中的資料使用

```tsx
// ExperienceTimeline 組件
export function ExperienceTimeline({
  experiences,
  isLoading,
}: ExperienceTimelineProps) {
  if (isLoading) {
    return <ExperienceTimelineSkeleton />;
  }

  if (experiences.length === 0) {
    return <EmptyState type="experience" />;
  }

  // 排序和處理資料
  const sortedExperiences = sortExperiences(experiences);

  return (
    <div>
      {sortedExperiences.map((exp) => (
        <ExperienceItem key={exp.id} experience={exp} />
      ))}
    </div>
  );
}
```

---

## ⚠️ 錯誤處理

### 錯誤類型

#### 1. 網路錯誤

```typescript
if (error) {
  // 判斷錯誤類型
  if (axios.isAxiosError(error)) {
    if (error.response?.status === 404) {
      // 業務員不存在
      return <NotFoundError />;
    } else if (error.response?.status >= 500) {
      // 伺服器錯誤
      return <ServerError />;
    } else if (error.code === 'ERR_NETWORK') {
      // 網路錯誤
      return <NetworkError />;
    }
  }

  // 其他未知錯誤
  return <GenericError />;
}
```

#### 2. 資料驗證錯誤

```typescript
// 在組件中驗證資料
if (salesperson.experiences) {
  // 過濾無效資料
  const validExperiences = salesperson.experiences.filter((exp) => {
    return exp.company && exp.position && exp.start_date;
  });

  if (validExperiences.length === 0) {
    // 顯示空狀態
    return <EmptyState type="experience" />;
  }
}
```

### 錯誤訊息

```tsx
// 網路錯誤
<div className="text-center py-12">
  <AlertCircle className="mx-auto h-12 w-12 text-error-500 mb-4" />
  <h3 className="text-lg font-semibold text-slate-900 mb-2">
    無法連接到伺服器
  </h3>
  <p className="text-slate-600 mb-6">
    請檢查您的網路連線
  </p>
  <Button onClick={() => refetch()}>
    重試
  </Button>
</div>

// 404 錯誤
<div className="text-center py-12">
  <UserX className="mx-auto h-12 w-12 text-slate-400 mb-4" />
  <h3 className="text-lg font-semibold text-slate-900 mb-2">
    找不到業務員
  </h3>
  <p className="text-slate-600 mb-6">
    此業務員可能不存在或已被移除
  </p>
  <Link href="/search">
    <Button>
      <ArrowLeft className="mr-2 h-4 w-4" />
      返回搜尋
    </Button>
  </Link>
</div>
```

### 重試策略

```typescript
// React Query 自動重試配置
export function useSalespersonDetail(id: number) {
  return useQuery({
    queryKey: ['salesperson', id],
    queryFn: () => searchAPI.getSalespersonDetail(id),
    enabled: id > 0,
    retry: 3,                    // 失敗後重試 3 次
    retryDelay: (attemptIndex) => {
      return Math.min(1000 * 2 ** attemptIndex, 30000);  // 指數退避
    },
    staleTime: 5 * 60 * 1000,
    gcTime: 10 * 60 * 1000,
  });
}
```

---

## 💾 快取策略

### React Query 快取配置

```typescript
export function useSalespersonDetail(id: number) {
  return useQuery({
    queryKey: ['salesperson', id],
    queryFn: () => searchAPI.getSalespersonDetail(id),

    // 快取配置
    staleTime: 5 * 60 * 1000,    // 5 分鐘內資料視為新鮮
    gcTime: 10 * 60 * 1000,      // 10 分鐘後清除未使用的快取

    // 重新取得策略
    refetchOnWindowFocus: false, // 視窗聚焦時不重新取得
    refetchOnMount: false,       // 重新掛載時不重新取得 (使用快取)
    refetchOnReconnect: true,    // 重新連線時重新取得

    // 啟用條件
    enabled: id > 0,
  });
}
```

### 快取失效

```typescript
// 手動失效快取 (例如:更新資料後)
import { useQueryClient } from '@tanstack/react-query';

const queryClient = useQueryClient();

// 失效特定業務員快取
queryClient.invalidateQueries({
  queryKey: ['salesperson', id],
});

// 失效所有業務員快取
queryClient.invalidateQueries({
  queryKey: ['salesperson'],
});
```

### 預載入優化 (可選)

```typescript
// 在搜尋頁面預載入業務員詳情
import { useQueryClient } from '@tanstack/react-query';

function SalespersonSearchCard({ salesperson }) {
  const queryClient = useQueryClient();

  const handleMouseEnter = () => {
    // Hover 時預載入詳情
    queryClient.prefetchQuery({
      queryKey: ['salesperson', salesperson.id],
      queryFn: () => searchAPI.getSalespersonDetail(salesperson.id),
      staleTime: 5 * 60 * 1000,
    });
  };

  return (
    <Link
      href={`/salesperson/${salesperson.id}`}
      onMouseEnter={handleMouseEnter}
    >
      {/* 卡片內容 */}
    </Link>
  );
}
```

---

## 🔄 資料轉換

### 日期格式化

```typescript
/**
 * 工具函數: 格式化 API 日期
 * API: "2022-01-15" (YYYY-MM-DD)
 * UI: "2022/01" (YYYY/MM)
 */
export function formatApiDate(dateString: string): string {
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString();
  return `${year}/${month}`;
}

/**
 * 計算年資
 */
export function calculateDuration(
  startDate: string,
  endDate: string | null
): string {
  const start = new Date(startDate);
  const end = endDate ? new Date(endDate) : new Date();

  const months = (end.getFullYear() - start.getFullYear()) * 12 +
                 (end.getMonth() - start.getMonth());

  const years = Math.floor(months / 12);
  const remainingMonths = months % 12;

  if (years === 0) {
    return `${remainingMonths}個月`;
  } else if (remainingMonths === 0) {
    return `${years}年`;
  } else {
    return `${years}年${remainingMonths}個月`;
  }
}
```

### 審核狀態對應

```typescript
/**
 * 審核狀態 Badge 配置
 */
export function getApprovalBadgeConfig(
  status: 'pending' | 'approved' | 'rejected'
) {
  const config = {
    pending: {
      variant: 'warning' as const,
      label: '審核中',
      icon: Clock,
    },
    approved: {
      variant: 'success' as const,
      label: '已驗證',
      icon: CheckCircle2,
    },
    rejected: {
      variant: 'error' as const,
      label: '已拒絕',
      icon: XCircle,
    },
  };

  return config[status];
}
```

---

## 🧪 測試策略

### Mock 資料

```typescript
// 用於測試的 Mock 資料
export const mockSalespersonDetail: SalespersonDetail = {
  id: 1,
  full_name: '王大明',
  email: 'wang@example.com',
  phone: '0912-345-678',
  avatar: null,
  company: {
    id: 1,
    name: '三商美邦人壽',
    logo: null,
  },
  specialties: '壽險規劃,投資型保險',
  service_regions: ['台北市', '新北市'],
  bio: '從事保險業務 10 年...',
  approval_status: 'approved',
  created_at: '2020-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',

  experiences: [
    {
      id: 1,
      user_id: 1,
      company: '三商美邦人壽',
      position: '資深業務經理',
      start_date: '2022-01-01',
      end_date: null,  // 至今
      description: '負責企業客戶開發與維護...',
      approval_status: 'approved',
      approved_by: 2,
      approved_at: '2022-01-15T00:00:00Z',
      rejected_reason: null,
      created_at: '2022-01-01T00:00:00Z',
      updated_at: '2022-01-01T00:00:00Z',
    },
    {
      id: 2,
      user_id: 1,
      company: '南山人壽',
      position: '業務專員',
      start_date: '2020-03-01',
      end_date: '2021-12-31',
      description: '開發個人客戶...',
      approval_status: 'approved',
      approved_by: 2,
      approved_at: '2020-03-15T00:00:00Z',
      rejected_reason: null,
      created_at: '2020-03-01T00:00:00Z',
      updated_at: '2020-03-01T00:00:00Z',
    },
  ],

  certifications: [
    {
      id: 1,
      user_id: 1,
      name: '國際認證理財規劃師 (CFP)',
      issuer: '中華民國財務規劃師協會',
      issue_date: '2021-03-01',
      expiry_date: '2026-03-01',
      description: '國際認證理財規劃師...',
      file_url: 'https://example.com/cert1.pdf',
      approval_status: 'approved',
      approved_by: 2,
      approved_at: '2021-03-15T00:00:00Z',
      rejected_reason: null,
      created_at: '2021-03-01T00:00:00Z',
      updated_at: '2021-03-01T00:00:00Z',
    },
    {
      id: 2,
      user_id: 1,
      name: '人身保險業務員資格',
      issuer: '中華民國人壽保險商業同業公會',
      issue_date: '2020-01-01',
      expiry_date: null,  // 永久有效
      description: '通過人身保險業務員資格測驗...',
      file_url: null,
      approval_status: 'approved',
      approved_by: 2,
      approved_at: '2020-01-15T00:00:00Z',
      rejected_reason: null,
      created_at: '2020-01-01T00:00:00Z',
      updated_at: '2020-01-01T00:00:00Z',
    },
  ],
};
```

### 測試案例

```typescript
describe('useSalespersonDetail', () => {
  it('應該成功取得業務員詳情', async () => {
    const { result } = renderHook(() => useSalespersonDetail(1));

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });

    expect(result.current.data).toBeDefined();
    expect(result.current.data?.experiences).toBeInstanceOf(Array);
    expect(result.current.data?.certifications).toBeInstanceOf(Array);
  });

  it('應該處理 404 錯誤', async () => {
    // Mock 404 response
    const { result } = renderHook(() => useSalespersonDetail(999));

    await waitFor(() => {
      expect(result.current.isLoading).toBe(false);
    });

    expect(result.current.error).toBeDefined();
    expect(result.current.data).toBeUndefined();
  });
});
```

---

## ✅ API 整合檢查清單

### 開發前
- [ ] 確認 API 端點存在
- [ ] 確認資料結構
- [ ] 確認 Hook 可用

### 開發中
- [ ] 正確使用 Hook
- [ ] 正確傳遞 Props
- [ ] 處理 Loading 狀態
- [ ] 處理錯誤狀態
- [ ] 處理空狀態

### 開發後
- [ ] API 正常回應
- [ ] 資料正確顯示
- [ ] 錯誤處理完整
- [ ] 快取策略正常
- [ ] 無 console 錯誤

---

**版本**: 1.0
**日期**: 2026-01-20
**狀態**: Ready for Implementation
