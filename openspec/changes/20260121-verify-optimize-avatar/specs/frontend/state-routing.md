# Frontend 狀態管理與路由規格 - Avatar 功能優化

**功能**: Avatar 相關狀態管理與路由配置
**日期**: 2026-01-21
**版本**: 1.0

---

## 📋 目錄

- [狀態管理總覽](#狀態管理總覽)
- [React Query 狀態](#react-query-狀態)
- [組件本地狀態](#組件本地狀態)
- [路由配置](#路由配置)
- [狀態同步策略](#狀態同步策略)

---

## 📊 狀態管理總覽

### 狀態分層

```
全域狀態 (React Query)
├── Profile 資料
│   ├── salesperson/profile
│   └── 快取時間: 5 分鐘
│
├── 搜尋列表
│   ├── salespeople
│   └── 快取時間: 2 分鐘
│
└── 業務員詳情
    ├── salesperson/[id]
    └── 快取時間: 5 分鐘

頁面狀態 (useState)
├── Dashboard
│   ├── editMode (編輯模式)
│   ├── avatarPreview (預覽圖片)
│   └── isUploading (上傳中)
│
└── SearchPage
    ├── filters (篩選條件)
    ├── page (分頁)
    └── sortBy (排序)

組件狀態 (useState)
├── Avatar
│   ├── imageError (載入錯誤)
│   ├── imageLoaded (載入完成)
│   └── isInView (Lazy Loading)
│
└── AvatarUploader
    ├── preview (預覽)
    ├── isProcessing (處理中)
    └── fileInfo (檔案資訊)
```

### 狀態流向

```
使用者操作
    ↓
組件本地狀態更新 (useState)
    ↓
API 請求 (React Query Mutation)
    ↓
Server 處理
    ↓
Response 返回
    ↓
React Query Cache 更新 (自動)
    ↓
所有使用該 Query 的組件自動重渲染
    ↓
UI 更新完成
```

---

## ⚡ React Query 狀態

### Query Configuration

**檔案位置**: `frontend/lib/query/client.ts`

```typescript
import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // 快取設定
      staleTime: 5 * 60 * 1000,      // 5 分鐘內視為新鮮
      cacheTime: 10 * 60 * 1000,     // 10 分鐘後移除快取

      // 重試設定
      retry: 1,                       // 失敗重試 1 次
      retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),

      // 重新獲取設定
      refetchOnWindowFocus: false,   // 視窗聚焦時不重新獲取
      refetchOnReconnect: true,      // 重新連線時重新獲取
      refetchOnMount: true,          // 組件掛載時重新獲取
    },
    mutations: {
      // Mutation 重試設定
      retry: 0,  // 不重試（由使用者手動重試）
    },
  },
});
```

### Query Keys 定義

**檔案位置**: `frontend/hooks/useSalesperson.ts`

```typescript
export const salespersonKeys = {
  // Profile
  profile: ['salesperson', 'profile'] as const,

  // 業務員詳情
  detail: (id: number) => ['salesperson', id] as const,

  // 搜尋列表
  list: (params: SearchParams) => ['salespeople', params] as const,

  // 狀態相關
  status: ['salesperson', 'status'] as const,
  approvalStatus: ['salesperson', 'approval-status'] as const,

  // 子資源
  experiences: ['salesperson', 'experiences'] as const,
  certifications: ['salesperson', 'certifications'] as const,
};
```

**使用原則**:
- 使用陣列定義 Query Key
- 使用 `as const` 確保類型安全
- 參數化的 Key 使用函數生成
- 保持 Key 的階層性和唯一性

### Profile Query

**用途**: 取得當前使用者的個人檔案（包含 Avatar）

```typescript
export function useProfile() {
  return useQuery({
    queryKey: salespersonKeys.profile,

    queryFn: async () => {
      const response = await salespersonApi.getProfile();
      const data = response.data as { profile?: any } | any;
      return data?.profile ?? data;
    },

    // 快取配置
    staleTime: 5 * 60 * 1000,   // 5 分鐘
    cacheTime: 10 * 60 * 1000,  // 10 分鐘

    // 重試配置
    retry: 1,

    // 初始資料（避免閃爍）
    placeholderData: undefined,
  });
}
```

**狀態屬性**:
```typescript
const {
  data,           // Profile 資料
  isLoading,      // 初次載入中
  isError,        // 是否發生錯誤
  error,          // 錯誤物件
  isSuccess,      // 是否成功
  isFetching,     // 重新獲取中（背景）
  refetch,        // 手動重新獲取
} = useProfile();
```

### Update Profile Mutation

**用途**: 更新個人檔案（包含 Avatar）

```typescript
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,

    // 成功時
    onSuccess: (response) => {
      // 1. 失效相關 Query Cache
      queryClient.invalidateQueries({
        queryKey: salespersonKeys.profile
      });

      queryClient.invalidateQueries({
        queryKey: ['salespeople']
      });

      if (response.data?.profile?.id) {
        queryClient.invalidateQueries({
          queryKey: salespersonKeys.detail(response.data.profile.id)
        });
      }

      // 2. 顯示成功訊息
      toast.success('個人資料已更新');
    },

    // 錯誤時
    onError: (error: any) => {
      const message = error.response?.data?.message || '更新失敗，請稍後再試';
      toast.error(message);
    },
  });
}
```

**狀態屬性**:
```typescript
const updateMutation = useUpdateProfile();

const {
  mutate,         // 執行 Mutation
  mutateAsync,    // 執行 Mutation (Promise)
  isPending,      // 執行中
  isSuccess,      // 是否成功
  isError,        // 是否錯誤
  error,          // 錯誤物件
  reset,          // 重置狀態
} = updateMutation;
```

### Search Query

**用途**: 搜尋業務員列表（包含 Avatar）

```typescript
export function useSalespeople(params: SearchParams) {
  return useQuery({
    queryKey: salespersonKeys.list(params),

    queryFn: async () => {
      const response = await salespersonApi.searchSalespeople(params);
      return response.data;
    },

    // 保留上一頁資料（避免閃爍）
    keepPreviousData: true,

    // 較短的快取時間（搜尋結果可能變動）
    staleTime: 2 * 60 * 1000,  // 2 分鐘

    // 只在有參數時執行
    enabled: !!params,
  });
}
```

### Cache Invalidation 策略

**時機**: 更新 Profile 後

```typescript
// 1. 立即失效 Profile Cache
queryClient.invalidateQueries({
  queryKey: salespersonKeys.profile
});

// 2. 失效搜尋列表 Cache（如果使用者在列表中）
queryClient.invalidateQueries({
  queryKey: ['salespeople']
});

// 3. 失效詳細頁 Cache
queryClient.invalidateQueries({
  queryKey: ['salesperson']  // 失效所有業務員詳情
});
```

**效果**:
- 所有使用這些 Query 的組件會自動重新獲取資料
- Avatar 會在所有頁面同步更新
- 不需要手動刷新頁面

---

## 🏠 組件本地狀態

### Dashboard - Profile Edit

**檔案位置**: `frontend/app/(dashboard)/dashboard/page.tsx`

```typescript
export default function ProfilePage() {
  // ===== 編輯模式狀態 =====
  const [editMode, setEditMode] = useState(false);

  // ===== Avatar 相關狀態 =====
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // ===== 公司編輯狀態 =====
  const [companyEditMode, setCompanyEditMode] = useState(false);
  const [selectedCompany, setSelectedCompany] = useState<Company | null>(null);
  const [isSelfEmployed, setIsSelfEmployed] = useState(false);
  const [businessName, setBusinessName] = useState('');

  // ===== Dialog 狀態 =====
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [showSwitchAlert, setShowSwitchAlert] = useState(false);

  // ===== React Hook Form =====
  const {
    register,
    handleSubmit,
    setValue,
    watch,
    formState: { errors },
    reset,
  } = useForm<ProfileFormData>({
    resolver: zodResolver(profileSchema),
  });

  // ===== React Query =====
  const { data: profile, isLoading } = useProfile();
  const updateProfileMutation = useUpdateProfile();

  // ... 處理邏輯
}
```

**狀態流向**:
```
[使用者點擊「編輯資料」]
    ↓
setEditMode(true)
    ↓
[顯示編輯表單]
    ↓
[使用者選擇檔案]
    ↓
handleAvatarChange()
    ↓
setAvatarPreview(base64)  [即時預覽]
setValue('avatar', base64) [更新表單]
    ↓
[使用者點擊「儲存變更」]
    ↓
onSubmitProfile()
    ↓
updateProfileMutation.mutate(data)
    ↓
[API 請求成功]
    ↓
onSuccess: {
  setEditMode(false)      [退出編輯模式]
  setAvatarPreview(null)  [清除預覽]
  React Query Cache 更新  [自動]
}
```

### Avatar Component

**檔案位置**: `frontend/components/ui/avatar.tsx`

```typescript
export function Avatar({
  src,
  lazy = false,
  onLoad,
  onError,
  ...props
}: AvatarProps) {
  // ===== 圖片載入狀態 =====
  const [imageError, setImageError] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);

  // ===== Lazy Loading 狀態 =====
  const [isInView, setIsInView] = useState(!lazy);
  const avatarRef = useRef<HTMLDivElement>(null);

  // ===== Intersection Observer =====
  useEffect(() => {
    if (!lazy) {
      setIsInView(true);
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setIsInView(true);
            observer.disconnect();
          }
        });
      },
      {
        rootMargin: '50px',
        threshold: 0.01,
      }
    );

    if (avatarRef.current) {
      observer.observe(avatarRef.current);
    }

    return () => observer.disconnect();
  }, [lazy]);

  // ===== 處理載入事件 =====
  const handleLoad = () => {
    setImageLoaded(true);
    onLoad?.();
  };

  const handleError = () => {
    setImageError(true);
    onError?.();
  };

  // ... 渲染邏輯
}
```

**狀態流向**:
```
[Avatar 組件掛載]
    ↓
lazy = true？
    ↓ Yes
[Intersection Observer 初始化]
    ↓
[等待進入 Viewport]
    ↓
[進入 Viewport (rootMargin: 50px)]
    ↓
setIsInView(true)
    ↓
[開始載入圖片]
    ↓
[載入完成]
    ↓
handleLoad()
    ↓
setImageLoaded(true)
    ↓
[淡入動畫 (opacity: 0 → 1)]
```

### AvatarUploader Component

**檔案位置**: `frontend/components/ui/avatar-uploader.tsx` (新增)

```typescript
export function AvatarUploader({
  onUploadComplete,
  onUploadError,
  ...props
}: AvatarUploaderProps) {
  // ===== 預覽狀態 =====
  const [preview, setPreview] = useState<string | null>(null);

  // ===== 處理狀態 =====
  const [isProcessing, setIsProcessing] = useState(false);

  // ===== 檔案資訊 =====
  const [fileInfo, setFileInfo] = useState<{
    name: string;
    originalSize: number;
    compressedSize: number;
  } | null>(null);

  // ===== Ref =====
  const fileInputRef = useRef<HTMLInputElement>(null);

  // ===== 處理檔案選擇 =====
  const handleFileChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      setIsProcessing(true);

      const loadingToast = toast.loading('處理圖片中...');
      const base64String = await processImageUpload(file, 2);

      setPreview(base64String);
      setFileInfo({
        name: file.name,
        originalSize: file.size,
        compressedSize: Math.round((base64String.length * 3) / 4),
      });

      toast.dismiss(loadingToast);
      toast.success('圖片已處理');

      onUploadComplete?.(base64String);
    } catch (error) {
      toast.error(error.message);
      onUploadError?.(error);

      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    } finally {
      setIsProcessing(false);
    }
  };

  // ... 渲染邏輯
}
```

---

## 🗺️ 路由配置

### 路由表

| 路由 | 頁面 | Avatar 功能 | 認證需求 |
|------|------|-------------|----------|
| `/search` | 搜尋列表 | 顯示多個 Avatar | ❌ |
| `/salesperson/[id]` | 業務員詳情 | 顯示單個 Avatar | ❌ |
| `/dashboard` | Dashboard | 編輯 Avatar | ✅ Salesperson |

### Next.js App Router

**結構**:
```
app/
├── (public)/
│   ├── search/
│   │   ├── page.tsx           # 搜尋列表頁
│   │   └── search-content.tsx # 搜尋內容組件
│   │
│   └── salesperson/
│       └── [id]/
│           └── page.tsx       # 業務員詳情頁
│
└── (dashboard)/
    └── dashboard/
        └── page.tsx           # Dashboard (Profile 編輯)
```

### Middleware 認證

**檔案位置**: `middleware.ts`

```typescript
export function middleware(request: NextRequest) {
  const pathname = request.nextUrl.pathname;

  // Dashboard 路由需要認證
  if (pathname.startsWith('/dashboard')) {
    const token = request.cookies.get('access_token');

    if (!token) {
      return NextResponse.redirect(new URL('/login', request.url));
    }

    // 檢查是否為業務員
    const userRole = request.cookies.get('user_role');
    if (userRole?.value !== 'salesperson') {
      return NextResponse.redirect(new URL('/', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/dashboard/:path*'],
};
```

### 路由跳轉

**搜尋列表 → 詳情頁**:
```tsx
import Link from 'next/link';

<Link href={`/salesperson/${person.id}`}>
  <Card>
    <Avatar src={person.avatar} />
    <h3>{person.full_name}</h3>
  </Card>
</Link>
```

**詳情頁 → 返回搜尋**:
```tsx
import { useRouter } from 'next/navigation';

const router = useRouter();

<Button onClick={() => router.push('/search')}>
  <ArrowLeft className="mr-2 h-4 w-4" />
  返回搜尋結果
</Button>
```

**Dashboard → 查看自己的詳情**:
```tsx
const { data: profile } = useProfile();

<Link href={`/salesperson/${profile?.id}`}>
  查看我的公開檔案
</Link>
```

---

## 🔄 狀態同步策略

### 跨頁面同步

**場景**: Dashboard 更新 Avatar 後，搜尋列表和詳情頁自動同步

**實作**:

1. **更新時失效所有相關 Cache**
   ```typescript
   onSuccess: () => {
     // Profile Cache
     queryClient.invalidateQueries({
       queryKey: salespersonKeys.profile
     });

     // 搜尋列表 Cache
     queryClient.invalidateQueries({
       queryKey: ['salespeople']
     });

     // 詳情頁 Cache
     queryClient.invalidateQueries({
       queryKey: ['salesperson']
     });
   }
   ```

2. **React Query 自動重新獲取**
   ```typescript
   // 所有使用這些 Query 的組件會自動重新獲取資料
   const { data: profile } = useProfile();
   // ↑ 當 Cache 失效時，自動重新獲取
   ```

3. **UI 自動更新**
   ```tsx
   // Avatar 組件會自動接收新的 src
   <Avatar src={profile?.avatar} />
   // ↑ profile 更新 → Avatar 重渲染
   ```

### 樂觀更新 (Optimistic Update)

**場景**: 上傳 Avatar 時立即顯示預覽

```typescript
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,

    // 樂觀更新
    onMutate: async (newProfile) => {
      // 1. 取消相關查詢
      await queryClient.cancelQueries({
        queryKey: salespersonKeys.profile
      });

      // 2. 備份當前資料
      const previousProfile = queryClient.getQueryData(
        salespersonKeys.profile
      );

      // 3. 立即更新 UI
      queryClient.setQueryData(
        salespersonKeys.profile,
        (old: any) => ({
          ...old,
          ...newProfile,
        })
      );

      // 4. 返回備份（用於失敗回滾）
      return { previousProfile };
    },

    // 失敗時回滾
    onError: (error, variables, context) => {
      queryClient.setQueryData(
        salespersonKeys.profile,
        context?.previousProfile
      );
      toast.error('更新失敗');
    },

    // 完成時確保同步
    onSettled: () => {
      queryClient.invalidateQueries({
        queryKey: salespersonKeys.profile
      });
    },
  });
}
```

**流程**:
```
[使用者選擇檔案]
    ↓
setAvatarPreview(base64)  [立即顯示預覽]
    ↓
[使用者點擊儲存]
    ↓
onMutate: {
  備份當前資料
  立即更新 Cache  [UI 立即變化]
}
    ↓
[API 請求中...]  [UI 已顯示新 Avatar]
    ↓
成功？
├─ Yes → onSettled: 重新獲取確保同步
└─ No  → onError: 回滾到備份資料
```

### 預載入 (Prefetching)

**場景**: Hover 時預載入詳情頁資料

```tsx
<Link
  href={`/salesperson/${person.id}`}
  onMouseEnter={() => {
    // Hover 時預載入
    queryClient.prefetchQuery({
      queryKey: salespersonKeys.detail(person.id),
      queryFn: () => salespersonApi.getSalespersonDetail(person.id),
      staleTime: 5 * 60 * 1000,
    });
  }}
>
  <SalespersonCard person={person} />
</Link>
```

**效果**:
- 使用者 Hover 時開始載入資料
- 點擊進入詳情頁時資料已經載入完成
- 頁面載入更快，體驗更流暢

---

## 🧪 狀態測試

### React Query 狀態測試

```typescript
import { renderHook, waitFor } from '@testing-library/react';
import { QueryClientProvider } from '@tanstack/react-query';
import { useProfile } from '@/hooks/useSalesperson';

test('should fetch profile successfully', async () => {
  const { result } = renderHook(() => useProfile(), {
    wrapper: ({ children }) => (
      <QueryClientProvider client={queryClient}>
        {children}
      </QueryClientProvider>
    ),
  });

  // 初始狀態
  expect(result.current.isLoading).toBe(true);

  // 等待載入完成
  await waitFor(() => {
    expect(result.current.isSuccess).toBe(true);
  });

  // 檢查資料
  expect(result.current.data).toHaveProperty('avatar');
});
```

### 組件狀態測試

```typescript
import { render, screen, fireEvent } from '@testing-library/react';
import { Avatar } from '@/components/ui/avatar';

test('should handle image load error', () => {
  const onError = jest.fn();

  render(
    <Avatar
      src="invalid-image.jpg"
      fallback="王小"
      onError={onError}
    />
  );

  // 模擬圖片載入錯誤
  const img = screen.getByRole('img');
  fireEvent.error(img);

  // 檢查是否顯示 Fallback
  expect(screen.getByText('王小')).toBeInTheDocument();
  expect(onError).toHaveBeenCalled();
});
```

---

## 📊 狀態監控

### React Query DevTools

**開發環境啟用**:
```tsx
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';

<QueryClientProvider client={queryClient}>
  <App />
  <ReactQueryDevtools initialIsOpen={false} />
</QueryClientProvider>
```

**功能**:
- 查看所有 Query 狀態
- 查看 Cache 內容
- 手動觸發重新獲取
- 查看 Query 執行時間

### 狀態日誌

```typescript
// 在開發環境記錄狀態變化
if (process.env.NODE_ENV === 'development') {
  queryClient.setDefaultOptions({
    queries: {
      onSuccess: (data, query) => {
        console.log(`[Query Success] ${query.queryKey}`, data);
      },
      onError: (error, query) => {
        console.error(`[Query Error] ${query.queryKey}`, error);
      },
    },
  });
}
```

---

## 📋 狀態檢查清單

### 開發檢查

- [ ] React Query 配置正確 (staleTime, cacheTime)
- [ ] Query Keys 定義完整且唯一
- [ ] Cache Invalidation 策略正確
- [ ] 樂觀更新邏輯正確（有備份和回滾）
- [ ] 預載入策略合理
- [ ] 組件本地狀態管理適當
- [ ] 錯誤狀態處理完整

### 測試檢查

- [ ] Query Hook 測試覆蓋
- [ ] Mutation Hook 測試覆蓋
- [ ] 組件狀態測試覆蓋
- [ ] 跨頁面同步測試
- [ ] 樂觀更新測試
- [ ] 錯誤處理測試

### 效能檢查

- [ ] 避免不必要的重渲染
- [ ] Query 不會重複請求
- [ ] Cache 策略合理
- [ ] 預載入不影響效能
- [ ] 樂觀更新流暢

---

**版本**: 1.0
**最後更新**: 2026-01-21
**開發者**: Frontend Team
