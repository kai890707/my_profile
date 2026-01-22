# Frontend API 整合規格 - Avatar 功能優化

**功能**: Avatar 相關 API 整合與資料處理
**日期**: 2026-01-21
**版本**: 1.0

---

## 📋 目錄

- [API 總覽](#api-總覽)
- [API 客戶端](#api-客戶端)
- [React Query Hooks](#react-query-hooks)
- [檔案處理](#檔案處理)
- [錯誤處理](#錯誤處理)
- [快取策略](#快取策略)

---

## 🌐 API 總覽

### Avatar 相關 API 端點

| API 端點 | 方法 | 用途 | 使用頁面 |
|----------|------|------|----------|
| `/api/salesperson/profile` | GET | 取得個人檔案 | Dashboard |
| `/api/salesperson/profile` | PUT | 更新個人檔案 (含 Avatar) | Dashboard |
| `/api/search/salespeople` | GET | 搜尋業務員列表 | Search |
| `/api/salesperson/{id}` | GET | 取得業務員詳情 | Detail |

### 資料流向

```
Frontend                    Backend                   Database
───────                    ───────                   ────────

[檔案選擇]
    ↓
[檔案驗證]
    ↓
[圖片壓縮]
    ↓
[Base64 轉換]
    ↓
[API Request] ──────→ [驗證 Base64] ──────→ [儲存到 DB]
                      [MIME type 檢查]        (avatar_data)
                      [大小限制檢查]          (avatar_mime)
                            ↓
[UI 更新] ←────────── [Response]
[Cache 更新]           {success, profile}
```

---

## 🔌 API 客戶端

### 檔案位置

`frontend/lib/api/salesperson.ts`

### 現有實作

```typescript
import apiClient from './client';

// 取得個人檔案
export async function getProfile() {
  const response = await apiClient.get('/salesperson/profile');
  return response.data;
}

// 更新個人檔案
export interface UpdateProfileRequest {
  full_name?: string;
  phone?: string;
  bio?: string;
  specialties?: string;
  service_regions?: string[];
  avatar?: string;  // Base64 Data URL
}

export async function updateProfile(data: UpdateProfileRequest) {
  const response = await apiClient.put('/salesperson/profile', data);
  return response.data;
}
```

### Avatar 特定邏輯

#### 上傳 Avatar

**Request Format**:
```typescript
{
  full_name: "王小明",
  phone: "0912345678",
  avatar: "data:image/jpeg;base64,/9j/4AAQSkZJRg..."  // Base64 Data URL
}
```

**Response Format**:
```typescript
{
  success: true,
  message: "個人資料已更新",
  profile: {
    id: 1,
    user_id: 1,
    full_name: "王小明",
    phone: "0912345678",
    avatar: "data:image/jpeg;base64,/9j/4AAQSkZJRg...",  // 完整 Data URL
    avatar_mime: "image/jpeg",
    bio: "...",
    specialties: "...",
    service_regions: ["台北市", "新北市"],
    company: { id: 1, name: "三商美邦人壽" },
    created_at: "2026-01-20T10:00:00Z",
    updated_at: "2026-01-21T12:00:00Z"
  }
}
```

#### 取得 Avatar

**搜尋列表 API**:
```typescript
// GET /api/search/salespeople?page=1&perPage=20
{
  success: true,
  data: [
    {
      id: 1,
      full_name: "王小明",
      avatar: "data:image/jpeg;base64,...",  // Data URL
      company: { id: 1, name: "三商美邦人壽" },
      // ... 其他欄位
    },
    // ...
  ],
  meta: {
    current_page: 1,
    per_page: 20,
    total: 100,
    last_page: 5
  }
}
```

**業務員詳情 API**:
```typescript
// GET /api/salesperson/{id}
{
  success: true,
  data: {
    id: 1,
    full_name: "王小明",
    avatar: "data:image/jpeg;base64,...",  // Data URL
    company: { id: 1, name: "三商美邦人壽" },
    bio: "...",
    specialties: "保險, 理財",
    service_regions: ["台北市", "新北市"],
    experiences: [...],
    certifications: [...],
    // ...
  }
}
```

---

## ⚡ React Query Hooks

### 檔案位置

`frontend/hooks/useSalesperson.ts`

### Query Keys

```typescript
export const salespersonKeys = {
  profile: ['salesperson', 'profile'] as const,
  detail: (id: number) => ['salesperson', id] as const,
  list: (params: any) => ['salespeople', params] as const,
};
```

### useProfile Hook

**用途**: 取得當前使用者的個人檔案

```typescript
export function useProfile() {
  return useQuery({
    queryKey: salespersonKeys.profile,
    queryFn: async () => {
      const response = await salespersonApi.getProfile();
      // Backend 返回 { success: true, data: { profile: {...} } }
      // 需要解包取出 profile
      const data = response.data as { profile?: any } | any;
      return data?.profile ?? data;
    },
    staleTime: 5 * 60 * 1000,  // 5 分鐘
    cacheTime: 10 * 60 * 1000, // 10 分鐘
  });
}
```

**使用範例**:
```tsx
function DashboardPage() {
  const { data: profile, isLoading, error } = useProfile();

  if (isLoading) return <ProfileSkeleton />;
  if (error) return <ErrorMessage />;

  return (
    <Avatar
      src={profile?.avatar}
      fallback={getAvatarFallback(profile)}
    />
  );
}
```

### useUpdateProfile Hook

**用途**: 更新個人檔案（包含 Avatar）

```typescript
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,
    onSuccess: (response) => {
      // 1. 立即更新 Profile Cache
      queryClient.invalidateQueries({
        queryKey: salespersonKeys.profile
      });

      // 2. 更新搜尋列表 Cache（如果使用者在列表中）
      queryClient.invalidateQueries({
        queryKey: ['salespeople']
      });

      // 3. 更新詳細頁 Cache（如果有的話）
      if (response.data?.profile?.id) {
        queryClient.invalidateQueries({
          queryKey: salespersonKeys.detail(response.data.profile.id)
        });
      }

      // 4. 顯示成功訊息
      toast.success('個人資料已更新');
    },
    onError: (error: any) => {
      const message = error.response?.data?.message || '更新失敗，請稍後再試';
      toast.error(message);
    },
  });
}
```

**使用範例**:
```tsx
function ProfileEditForm() {
  const updateProfileMutation = useUpdateProfile();

  const onSubmit = (data: ProfileFormData) => {
    updateProfileMutation.mutate(data, {
      onSuccess: () => {
        setEditMode(false);
        setAvatarPreview(null);
      },
    });
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <AvatarUploader
        onUploadComplete={(base64) => {
          setValue('avatar', base64);
        }}
      />

      <Button
        type="submit"
        isLoading={updateProfileMutation.isPending}
      >
        儲存變更
      </Button>
    </form>
  );
}
```

### useSalespersonDetail Hook

**用途**: 取得業務員詳細資料

```typescript
export function useSalespersonDetail(id: number) {
  return useQuery({
    queryKey: salespersonKeys.detail(id),
    queryFn: async () => {
      const response = await salespersonApi.getSalespersonDetail(id);
      return response.data;
    },
    enabled: !!id,  // 只有當 id 存在時才執行
    staleTime: 5 * 60 * 1000,
    cacheTime: 10 * 60 * 1000,
  });
}
```

**使用範例**:
```tsx
function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  const { data: salesperson, isLoading } = useSalespersonDetail(id);

  return (
    <Avatar
      src={salesperson?.avatar}
      fallback={getAvatarFallback(salesperson)}
      size="2xl"
      priority={true}
    />
  );
}
```

### useSalespeople Hook

**用途**: 搜尋業務員列表

```typescript
export function useSalespeople(params: SearchParams) {
  return useQuery({
    queryKey: salespersonKeys.list(params),
    queryFn: async () => {
      const response = await salespersonApi.searchSalespeople(params);
      return response.data;
    },
    keepPreviousData: true,  // 保留上一頁資料，避免閃爍
    staleTime: 2 * 60 * 1000,  // 2 分鐘
  });
}
```

**使用範例**:
```tsx
function SearchPage() {
  const [page, setPage] = useState(1);
  const { data, isLoading } = useSalespeople({ page, perPage: 20 });

  return (
    <div className="grid md:grid-cols-2 gap-6">
      {data?.data.map((person) => (
        <Card key={person.id}>
          <Avatar
            src={person.avatar}
            fallback={getAvatarFallback(person)}
            size="md"
            lazy={true}
          />
        </Card>
      ))}
    </div>
  );
}
```

---

## 📁 檔案處理

### 檔案位置

`frontend/lib/utils/image.ts`

### processImageUpload()

**用途**: 完整的圖片上傳處理流程（驗證 + 壓縮 + Base64 轉換）

**函數簽名**:
```typescript
export async function processImageUpload(
  file: File,
  maxSizeMB: number = 2
): Promise<string>
```

**處理流程**:
```
[File 物件]
    ↓
[1. 驗證檔案類型]
    ↓ (檢查 MIME type)
[2. 驗證檔案大小]
    ↓ (初步檢查)
[3. 壓縮圖片]
    ↓ (Canvas API)
    ├─ 計算縮放比例
    ├─ 繪製到 Canvas
    └─ 轉換為 Blob
[4. 轉換為 Base64]
    ↓ (FileReader API)
[返回 Data URL]
"data:image/jpeg;base64,/9j/..."
```

**實作細節**:

```typescript
export async function processImageUpload(
  file: File,
  maxSizeMB: number = 2
): Promise<string> {
  // 1. 驗證圖片類型
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!allowedTypes.includes(file.type)) {
    throw new Error('不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片');
  }

  // 2. 驗證檔案大小（允許稍大的原始檔案，稍後壓縮）
  const validation = validateImage(file, maxSizeMB * 2);
  if (!validation.valid) {
    throw new Error(validation.error);
  }

  // 3. 壓縮圖片
  const compressedFile = await compressImage(file, maxSizeMB);

  // 4. 轉換為 Base64
  const base64 = await fileToBase64(compressedFile);

  return base64;
}
```

### compressImage()

**用途**: 壓縮圖片到目標大小

**函數簽名**:
```typescript
export function compressImage(
  file: File,
  maxSizeMB: number = 2,
  quality: number = 0.8
): Promise<File>
```

**壓縮策略**:

```typescript
export function compressImage(
  file: File,
  maxSizeMB: number = 2,
  quality: number = 0.8
): Promise<File> {
  return new Promise((resolve, reject) => {
    // 1. 如果檔案已經小於目標大小，直接返回
    if (file.size <= maxSizeMB * 1024 * 1024) {
      resolve(file);
      return;
    }

    // 2. 讀取檔案
    const reader = new FileReader();
    reader.readAsDataURL(file);

    reader.onload = (event) => {
      const img = new Image();
      img.src = event.target?.result as string;

      img.onload = () => {
        // 3. 建立 Canvas
        const canvas = document.createElement('canvas');
        let width = img.width;
        let height = img.height;

        // 4. 計算縮放比例（最大 2048px）
        const maxDimension = 2048;
        if (width > height && width > maxDimension) {
          height = (height * maxDimension) / width;
          width = maxDimension;
        } else if (height > maxDimension) {
          width = (width * maxDimension) / height;
          height = maxDimension;
        }

        canvas.width = width;
        canvas.height = height;

        // 5. 繪製圖片
        const ctx = canvas.getContext('2d');
        if (!ctx) {
          reject(new Error('無法建立 Canvas Context'));
          return;
        }

        ctx.drawImage(img, 0, 0, width, height);

        // 6. 轉換為 Blob
        canvas.toBlob(
          (blob) => {
            if (!blob) {
              reject(new Error('壓縮圖片失敗'));
              return;
            }

            // 7. 如果壓縮後仍然太大，降低品質重試
            if (blob.size > maxSizeMB * 1024 * 1024 && quality > 0.5) {
              compressImage(file, maxSizeMB, quality - 0.1)
                .then(resolve)
                .catch(reject);
              return;
            }

            // 8. 建立新的 File 物件
            const compressedFile = new File([blob], file.name, {
              type: file.type,
              lastModified: Date.now(),
            });

            resolve(compressedFile);
          },
          file.type,
          quality
        );
      };

      img.onerror = () => {
        reject(new Error('載入圖片失敗'));
      };
    };

    reader.onerror = () => {
      reject(new Error('讀取檔案失敗'));
    };
  });
}
```

**品質調整邏輯**:
```
初始品質: 0.8 (80%)
    ↓
[壓縮]
    ↓
檔案仍 > 2MB？
    ↓ Yes
品質 -= 0.1 (降至 70%)
    ↓
[重新壓縮]
    ↓
檔案仍 > 2MB？
    ↓ Yes
品質 -= 0.1 (降至 60%)
    ↓
...直到品質 < 0.5 或檔案 < 2MB
```

### fileToBase64()

**用途**: 將 File 物件轉換為 Base64 Data URL

**函數簽名**:
```typescript
export function fileToBase64(file: File): Promise<string>
```

**實作**:
```typescript
export function fileToBase64(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onloadend = () => {
      if (typeof reader.result === 'string') {
        resolve(reader.result);
      } else {
        reject(new Error('無法讀取檔案'));
      }
    };

    reader.onerror = () => {
      reject(new Error('讀取檔案時發生錯誤'));
    };

    reader.readAsDataURL(file);
  });
}
```

**輸出格式**:
```
data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD...
      ↑          ↑      ↑
   MIME type  編碼  Base64 資料
```

### formatFileSize()

**用途**: 格式化檔案大小顯示

**函數簽名**:
```typescript
export function formatFileSize(bytes: number): string
```

**實作**:
```typescript
export function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}
```

**範例**:
```typescript
formatFileSize(0);           // "0 Bytes"
formatFileSize(1024);        // "1 KB"
formatFileSize(1536);        // "1.5 KB"
formatFileSize(2097152);     // "2 MB"
formatFileSize(3145728);     // "3 MB"
```

---

## 🚨 錯誤處理

### 錯誤類型

#### 1. 前端驗證錯誤

**檔案類型錯誤**:
```typescript
if (!allowedTypes.includes(file.type)) {
  throw new Error('不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片');
}
```

**檔案大小錯誤**:
```typescript
if (file.size > maxSizeMB * 1024 * 1024) {
  throw new Error(`檔案大小不能超過 ${maxSizeMB}MB`);
}
```

**圖片損壞錯誤**:
```typescript
img.onerror = () => {
  reject(new Error('圖片檔案損壞，無法讀取，請選擇其他圖片'));
};
```

#### 2. API 錯誤

**網路錯誤**:
```typescript
catch (error: any) {
  if (error.code === 'ERR_NETWORK') {
    toast.error('網路連線失敗，請檢查您的網路連線');
  }
}
```

**伺服器錯誤**:
```typescript
onError: (error: any) => {
  const status = error.response?.status;
  const message = error.response?.data?.message;

  if (status === 500) {
    toast.error('伺服器錯誤，請稍後再試');
  } else if (status === 413) {
    toast.error('檔案過大，請選擇小於 2MB 的圖片');
  } else {
    toast.error(message || '上傳失敗，請稍後再試');
  }
}
```

**驗證錯誤**:
```typescript
// Backend 返回 422 Unprocessable Entity
{
  "success": false,
  "message": "驗證失敗",
  "errors": {
    "avatar": ["圖片檔案過大（超過 2MB）"]
  }
}

// Frontend 處理
onError: (error: any) => {
  const errors = error.response?.data?.errors;

  if (errors?.avatar) {
    toast.error(errors.avatar[0]);
  }
}
```

### 錯誤 UI 處理

**Toast 通知**:
```tsx
// 成功
toast.success('頭像已更新', {
  description: '您的新頭像已在所有頁面同步更新',
});

// 錯誤
toast.error('上傳失敗', {
  description: error.message,
  action: {
    label: '重試',
    onClick: () => handleRetry(),
  },
  duration: 5000,
});

// 載入中
const loadingToast = toast.loading('處理圖片中...');
// ... 處理完成後
toast.dismiss(loadingToast);
```

**錯誤邊界**:
```tsx
<ErrorBoundary
  fallback={
    <div className="text-center py-8">
      <p className="text-slate-600">載入頭像時發生錯誤</p>
      <Button onClick={reset}>重試</Button>
    </div>
  }
>
  <Avatar src={avatar} />
</ErrorBoundary>
```

---

## 💾 快取策略

### React Query 配置

**檔案位置**: `frontend/lib/query/client.ts`

```typescript
export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000,      // 5 分鐘
      cacheTime: 10 * 60 * 1000,     // 10 分鐘
      retry: 1,                       // 失敗重試 1 次
      refetchOnWindowFocus: false,   // 視窗聚焦時不重新獲取
      refetchOnReconnect: true,      // 重新連線時重新獲取
    },
  },
});
```

### Cache 更新策略

#### 1. 樂觀更新 (Optimistic Update)

**適用情境**: 上傳 Avatar

```typescript
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,

    // 樂觀更新
    onMutate: async (newProfile) => {
      // 1. 取消所有相關的查詢
      await queryClient.cancelQueries({ queryKey: salespersonKeys.profile });

      // 2. 備份當前資料
      const previousProfile = queryClient.getQueryData(salespersonKeys.profile);

      // 3. 樂觀更新 UI
      queryClient.setQueryData(salespersonKeys.profile, (old: any) => ({
        ...old,
        ...newProfile,
      }));

      // 4. 返回備份（用於失敗時回滾）
      return { previousProfile };
    },

    // 成功時不需額外處理（已經樂觀更新）
    onSuccess: () => {
      toast.success('個人資料已更新');
    },

    // 失敗時回滾
    onError: (error, variables, context) => {
      queryClient.setQueryData(
        salespersonKeys.profile,
        context?.previousProfile
      );
      toast.error('更新失敗，請稍後再試');
    },

    // 完成時重新獲取（確保同步）
    onSettled: () => {
      queryClient.invalidateQueries({ queryKey: salespersonKeys.profile });
    },
  });
}
```

#### 2. 自動失效 (Invalidation)

**適用情境**: 更新成功後，自動失效相關 cache

```typescript
onSuccess: (response) => {
  // 1. 失效 Profile Cache
  queryClient.invalidateQueries({
    queryKey: salespersonKeys.profile
  });

  // 2. 失效搜尋列表 Cache
  queryClient.invalidateQueries({
    queryKey: ['salespeople']
  });

  // 3. 失效詳細頁 Cache
  if (response.data?.profile?.id) {
    queryClient.invalidateQueries({
      queryKey: salespersonKeys.detail(response.data.profile.id)
    });
  }
}
```

#### 3. 預載入 (Prefetching)

**適用情境**: Hover 時預載入詳細頁面

```tsx
<Link
  href={`/salesperson/${person.id}`}
  onMouseEnter={() => {
    // Hover 時預載入資料
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

### Cache 持久化 (未來考慮)

使用 `@tanstack/react-query-persist-client`:

```typescript
import { persistQueryClient } from '@tanstack/react-query-persist-client';
import { createSyncStoragePersister } from '@tanstack/query-sync-storage-persister';

const persister = createSyncStoragePersister({
  storage: window.localStorage,
});

persistQueryClient({
  queryClient,
  persister,
  maxAge: 1000 * 60 * 60 * 24, // 24 小時
});
```

---

## 🔐 安全性考量

### 1. XSS 防護

**Data URL 安全處理**:
```typescript
// ✅ 安全：直接使用 <img> 標籤
<img src={dataUrl} alt="Avatar" />

// ✅ 安全：React 自動轉義
<div dangerouslySetInnerHTML={{ __html: content }} />  // ❌ 避免使用

// ✅ 安全：驗證 Data URL 格式
if (!dataUrl.startsWith('data:image/')) {
  throw new Error('Invalid data URL');
}
```

### 2. CSRF 防護

**Axios 自動處理**:
```typescript
// Axios 客戶端配置
const apiClient = axios.create({
  withCredentials: true,  // 自動帶上 CSRF Token
});
```

### 3. 檔案大小限制

**前端限制** (2MB):
```typescript
if (file.size > 2 * 1024 * 1024) {
  throw new Error('檔案大小不能超過 2MB');
}
```

**後端驗證** (必須):
```php
// Backend 也要驗證，防止繞過前端檢查
$maxSize = 2097152; // 2MB
if (strlen($decoded) > $maxSize) {
    return response()->json(['error' => '圖片檔案過大'], 422);
}
```

---

## 📊 監控與日誌

### 錯誤追蹤

```typescript
// 使用 Sentry (未來考慮)
import * as Sentry from '@sentry/nextjs';

try {
  await processImageUpload(file);
} catch (error) {
  // 記錄錯誤到 Sentry
  Sentry.captureException(error, {
    extra: {
      fileName: file.name,
      fileSize: file.size,
      fileType: file.type,
    },
  });

  throw error;
}
```

### 效能監控

```typescript
// 監控上傳時間
const startTime = performance.now();

await processImageUpload(file);

const endTime = performance.now();
const duration = endTime - startTime;

// 記錄到分析工具
console.log(`Avatar upload took ${duration}ms`);
```

---

**版本**: 1.0
**最後更新**: 2026-01-21
**開發者**: Frontend Team
