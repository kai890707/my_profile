# Frontend 頁面規格 - Avatar 功能優化

**功能**: 各頁面的 Avatar 顯示與互動
**日期**: 2026-01-21
**版本**: 1.0

---

## 📋 目錄

- [頁面總覽](#頁面總覽)
- [搜尋列表頁](#搜尋列表頁)
- [業務員詳細頁](#業務員詳細頁)
- [Dashboard Profile 頁](#dashboard-profile-頁)
- [跨頁面驗證](#跨頁面驗證)

---

## 📑 頁面總覽

### Avatar 使用頁面

| 頁面 | 路由 | Avatar 用途 | 尺寸 | Lazy Loading | 上傳功能 |
|------|------|-------------|------|--------------|----------|
| **搜尋列表** | `/search` | 顯示列表 | `md` | ✅ | ❌ |
| **詳細頁面** | `/salesperson/[id]` | 主要展示 | `2xl` | ❌ | ❌ |
| **Dashboard** | `/dashboard` | 編輯頭像 | `2xl` | ❌ | ✅ |

### 優化重點

#### 搜尋列表頁
- ✅ Lazy Loading（20 個 Avatar）
- ✅ Skeleton Loading
- ✅ 錯誤處理（Fallback）
- ✅ 響應式設計

#### 詳細頁面
- ✅ 高優先級載入
- ✅ 大尺寸顯示
- ✅ 淡入動畫
- ✅ 錯誤處理

#### Dashboard
- ✅ 上傳功能
- ✅ 即時預覽
- ✅ 壓縮提示
- ✅ 跨頁面同步

---

## 🔍 搜尋列表頁

### 頁面資訊

**路由**: `/search`
**檔案**: `frontend/app/search/page.tsx`
**元件**: `SearchContent`

### 現有實作

```tsx
'use client';

import { Suspense } from 'react';
import { Header } from '@/components/layout/header';
import { Footer } from '@/components/layout/footer';
import { SalespersonCardSkeleton } from '@/components/ui/skeleton';
import { SearchContent } from './search-content';

export default function SearchPage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1 bg-slate-50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div className="mb-8">
            <h1 className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">
              搜尋業務員
            </h1>
            <p className="text-lg text-slate-600">
              找到最適合您需求的專業業務員
            </p>
          </div>

          <Suspense fallback={<SalespersonCardSkeleton />}>
            <SearchContent />
          </Suspense>
        </div>
      </main>
      <Footer />
    </div>
  );
}
```

### Avatar 使用情境

#### 1. 業務員卡片

**位置**: `SearchContent` 內的卡片列表

**佈局**:
```
┌───────────────────────────────────────┐
│  [Avatar]  王小明                     │
│  md尺寸     業務經理                  │
│            三商美邦人壽               │
│            ★★★★☆ 4.2              │
└───────────────────────────────────────┘
```

**程式碼**:
```tsx
import { Avatar } from '@/components/ui/avatar';
import { getAvatarFallback } from '@/lib/utils/avatar';

<Card className="hover:shadow-lg transition-shadow">
  <CardContent className="p-6">
    <div className="flex items-center gap-4">
      {/* Avatar - 啟用 Lazy Loading */}
      <Avatar
        src={salesperson.avatar}
        fallback={getAvatarFallback(salesperson)}
        size="md"
        lazy={true}
        alt={`${salesperson.full_name}的頭像`}
      />

      {/* 資訊 */}
      <div className="flex-1 min-w-0">
        <h3 className="text-lg font-semibold text-slate-900 truncate">
          {salesperson.full_name}
        </h3>
        {salesperson.company && (
          <p className="text-sm text-slate-600 truncate">
            {salesperson.company.name}
          </p>
        )}
        {/* 評分、專長等 */}
      </div>
    </div>
  </CardContent>
</Card>
```

#### 2. Skeleton Loading

**顯示時機**:
- 初始載入時
- 篩選條件變更時
- 分頁切換時

**設計**:
```tsx
function SalespersonCardSkeleton() {
  return (
    <Card>
      <CardContent className="p-6">
        <div className="flex items-center gap-4">
          {/* Avatar Skeleton */}
          <div className="h-12 w-12 rounded-full bg-slate-200 animate-pulse" />

          {/* 內容 Skeleton */}
          <div className="flex-1 space-y-2">
            <div className="h-5 w-32 bg-slate-200 rounded animate-pulse" />
            <div className="h-4 w-24 bg-slate-200 rounded animate-pulse" />
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
```

#### 3. 錯誤處理

**情境 1: 圖片載入失敗**
```tsx
// Avatar 組件內部自動處理
// 自動顯示 Fallback (姓名縮寫)
```

**情境 2: API 錯誤**
```tsx
{error && (
  <Card>
    <CardContent className="py-16 text-center">
      <AlertCircle className="h-12 w-12 text-slate-400 mx-auto mb-4" />
      <h3 className="text-lg font-semibold text-slate-900 mb-2">
        載入失敗
      </h3>
      <p className="text-slate-600 mb-4">
        無法載入業務員資料，請稍後再試
      </p>
      <Button onClick={refetch}>重試</Button>
    </CardContent>
  </Card>
)}
```

### Lazy Loading 策略

#### 實作方式

Avatar 組件內部使用 Intersection Observer：

```typescript
// 在 Avatar 組件內
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
      rootMargin: '50px', // 提前 50px 開始載入
      threshold: 0.01,
    }
  );

  if (avatarRef.current) {
    observer.observe(avatarRef.current);
  }

  return () => observer.disconnect();
}, [lazy]);
```

#### 載入順序

```
頁面載入
  ↓
Viewport 內的 Avatar (前 5 個)
  ↓ (立即載入)
Skeleton 顯示
  ↓
使用者滾動
  ↓
進入 Viewport 前 50px (rootMargin)
  ↓
開始載入 Avatar
  ↓
載入完成 → 淡入動畫
```

### 響應式設計

#### Desktop (≥ 1024px)

```tsx
<div className="grid lg:grid-cols-4 gap-6">
  {/* 側邊欄 */}
  <aside className="lg:col-span-1">
    {/* 篩選器 */}
  </aside>

  {/* 主要內容 */}
  <div className="lg:col-span-3">
    <div className="grid md:grid-cols-2 gap-6">
      {salespeople.map((person) => (
        <SalespersonCard key={person.id} person={person} />
      ))}
    </div>
  </div>
</div>
```

#### Mobile (< 768px)

```tsx
<div className="space-y-6">
  {/* 篩選按鈕 */}
  <Button onClick={openFilters}>
    篩選條件
  </Button>

  {/* 列表 (單欄) */}
  <div className="space-y-4">
    {salespeople.map((person) => (
      <SalespersonCard key={person.id} person={person} />
    ))}
  </div>
</div>
```

**Avatar 尺寸調整**:
```tsx
<Avatar
  size="md"           // Desktop
  className="md:block hidden"
/>
<Avatar
  size="sm"           // Mobile
  className="md:hidden"
/>
```

### 效能優化

#### 1. 虛擬化列表 (未來考慮)

如果列表超過 50 個：
```tsx
import { useVirtualizer } from '@tanstack/react-virtual';

// 實作虛擬化滾動
```

#### 2. 預載入策略

```tsx
// Hover 時預載入下一頁
<Link
  href={`/salesperson/${person.id}`}
  onMouseEnter={() => {
    // 預載入詳細頁面資料
    queryClient.prefetchQuery({
      queryKey: ['salesperson', person.id],
      queryFn: () => getSalespersonDetail(person.id),
    });
  }}
>
  <SalespersonCard person={person} />
</Link>
```

---

## 👤 業務員詳細頁

### 頁面資訊

**路由**: `/salesperson/[id]`
**檔案**: `frontend/app/salesperson/[id]/page.tsx`

### 現有實作

```tsx
export default function SalespersonDetailPage() {
  const params = useParams();
  const id = parseInt(params.id as string);

  const { data: salesperson, isLoading, error } = useSalespersonDetail(id);

  // Loading / Error 處理...

  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1 bg-slate-50">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
          {/* 內容 */}
        </div>
      </main>
      <Footer />
    </div>
  );
}
```

### Avatar 使用情境

#### 1. 主要頭像

**位置**: 頁面頂部，個人資料卡片內

**佈局**:
```
┌───────────────────────────────────────┐
│                                       │
│  [  大頭像  ]  王小明                 │
│   2xl 尺寸     業務經理               │
│               三商美邦人壽            │
│               專長: 保險、理財        │
│                                       │
└───────────────────────────────────────┘
```

**程式碼**:
```tsx
<Card>
  <CardContent className="p-8">
    <div className="flex flex-col md:flex-row items-start md:items-center gap-6 mb-6">
      {/* Avatar - 高優先級載入 */}
      <Avatar
        src={salesperson.avatar}
        fallback={getAvatarFallback(salesperson)}
        size="2xl"
        priority={true}
        alt={`${salesperson.full_name}的頭像`}
        onLoad={() => setImageLoaded(true)}
        onError={() => setImageError(true)}
      />

      {/* 資訊 */}
      <div className="flex-1">
        <div className="flex items-center gap-3 mb-2">
          <h1 className="text-3xl font-bold text-slate-900">
            {salesperson.full_name}
          </h1>
          <Badge variant={statusBadge.variant} size="sm">
            {statusBadge.label}
          </Badge>
        </div>

        {salesperson.company && (
          <div className="flex items-center gap-2 text-slate-600 mb-3">
            <Building2 className="h-5 w-5" />
            <span className="text-lg">{salesperson.company.name}</span>
          </div>
        )}

        {/* 專長標籤 */}
        {specialtiesList.length > 0 && (
          <div className="flex flex-wrap gap-2">
            {specialtiesList.map((specialty, index) => (
              <Badge key={index} variant="primary" size="sm">
                {specialty.trim()}
              </Badge>
            ))}
          </div>
        )}
      </div>
    </div>

    {/* 簡介 */}
    {salesperson.bio && (
      <div className="pt-6 border-t border-slate-200">
        <h3 className="text-lg font-semibold text-slate-900 mb-3">
          個人簡介
        </h3>
        <p className="text-slate-600 whitespace-pre-line">
          {salesperson.bio}
        </p>
      </div>
    )}
  </CardContent>
</Card>
```

#### 2. Loading 狀態

**Skeleton 設計**:
```tsx
{isLoading && (
  <Card>
    <CardContent className="p-8">
      <div className="flex flex-col md:flex-row items-start md:items-center gap-6">
        {/* Avatar Skeleton */}
        <div className="h-24 w-24 rounded-full bg-slate-200 animate-pulse" />

        {/* 內容 Skeleton */}
        <div className="flex-1 space-y-3">
          <div className="h-8 w-48 bg-slate-200 rounded animate-pulse" />
          <div className="h-5 w-32 bg-slate-200 rounded animate-pulse" />
          <div className="flex gap-2">
            <div className="h-6 w-20 bg-slate-200 rounded-full animate-pulse" />
            <div className="h-6 w-20 bg-slate-200 rounded-full animate-pulse" />
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
)}
```

#### 3. Error 處理

**情境: 業務員不存在**
```tsx
{error || !salesperson ? (
  <Card>
    <CardContent className="py-16 text-center">
      <h3 className="text-xl font-semibold text-slate-900 mb-2">
        找不到業務員資料
      </h3>
      <p className="text-slate-600 mb-6">
        此業務員可能不存在或已被移除
      </p>
      <Link href="/search">
        <Button>
          <ArrowLeft className="mr-2 h-4 w-4" />
          返回搜尋頁面
        </Button>
      </Link>
    </CardContent>
  </Card>
) : (
  // 正常顯示
)}
```

### 淡入動畫

**實作**:
```tsx
const [imageLoaded, setImageLoaded] = useState(false);

<Avatar
  src={salesperson.avatar}
  className={cn(
    'transition-opacity duration-300',
    imageLoaded ? 'opacity-100' : 'opacity-0'
  )}
  onLoad={() => setImageLoaded(true)}
/>
```

### 響應式設計

#### Desktop

```tsx
<div className="flex flex-row items-center gap-6">
  <Avatar size="2xl" />
  <div className="flex-1">
    {/* 資訊 */}
  </div>
</div>
```

#### Mobile

```tsx
<div className="flex flex-col items-start gap-6">
  <Avatar size="xl" />  {/* 稍小 */}
  <div className="w-full">
    {/* 資訊 */}
  </div>
</div>
```

---

## 📝 Dashboard Profile 頁

### 頁面資訊

**路由**: `/dashboard`
**檔案**: `frontend/app/(dashboard)/dashboard/page.tsx`

### 現有實作

```tsx
export default function ProfilePage() {
  const [editMode, setEditMode] = useState(false);
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const { data: profile } = useProfile();
  const updateProfileMutation = useUpdateProfile();

  // 處理頭像上傳
  const handleAvatarChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      const loadingToast = toast.loading('處理圖片中...');
      const base64String = await processImageUpload(file, 2);
      setAvatarPreview(base64String);
      setProfileValue('avatar', base64String);
      toast.dismiss(loadingToast);
      toast.success('圖片已處理');
    } catch (error) {
      toast.error(error.message);
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    }
  };

  // 提交表單
  const onSubmitProfile = (data) => {
    updateProfileMutation.mutate(data, {
      onSuccess: () => {
        setEditMode(false);
        setAvatarPreview(null);
      },
    });
  };

  return (/* 頁面內容 */);
}
```

### Avatar 使用情境

#### 1. 檢視模式

**顯示當前頭像**:
```tsx
{!editMode && (
  <div className="flex items-center gap-6">
    <Avatar
      src={profile?.avatar}
      fallback={getAvatarFallback(profile)}
      size="2xl"
    />
    <div>
      <h3 className="text-xl font-semibold text-slate-900">
        {profile?.full_name}
      </h3>
      {profile?.phone && (
        <p className="text-slate-600">{profile.phone}</p>
      )}
    </div>
  </div>
)}
```

#### 2. 編輯模式

**上傳與預覽**:
```tsx
{editMode && (
  <form onSubmit={handleSubmit(onSubmitProfile)}>
    {/* Avatar 上傳 */}
    <div className="flex items-start gap-6">
      <div className="relative">
        {/* 顯示預覽或當前頭像 */}
        <Avatar
          src={avatarPreview || profile?.avatar}
          fallback={getAvatarFallback(profile)}
          size="2xl"
        />

        {/* 相機按鈕 */}
        <button
          type="button"
          onClick={() => fileInputRef.current?.click()}
          className="
            absolute bottom-0 right-0
            p-2 bg-primary-600 text-white
            rounded-full shadow-md
            hover:bg-primary-700 hover:scale-110
            transition-all duration-200
            focus:outline-none
            focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
          "
          aria-label="更換頭像"
        >
          <Camera className="h-4 w-4" />
        </button>

        {/* 隱藏的檔案輸入 */}
        <input
          ref={fileInputRef}
          type="file"
          accept="image/*"
          className="hidden"
          onChange={handleAvatarChange}
        />
      </div>

      <div className="flex-1">
        <p className="text-sm text-slate-600 mb-1">
          點擊相機圖示上傳頭像
        </p>
        <p className="text-xs text-slate-500">
          支援 JPG、PNG 格式，檔案大小不超過 2MB
        </p>
      </div>
    </div>

    {/* 其他表單欄位 */}
    <Input label="姓名" {...register('full_name')} />
    <Input label="手機號碼" {...register('phone')} />

    {/* 提交按鈕 */}
    <div className="flex gap-3 pt-4">
      <Button
        type="submit"
        isLoading={updateProfileMutation.isPending}
      >
        <Save className="mr-2 h-4 w-4" />
        儲存變更
      </Button>
      <Button
        type="button"
        variant="outline"
        onClick={() => {
          setEditMode(false);
          setAvatarPreview(null);
          resetProfile();
        }}
      >
        取消
      </Button>
    </div>
  </form>
)}
```

### 上傳流程

#### 1. 選擇檔案

```typescript
const handleAvatarChange = async (e: ChangeEvent<HTMLInputElement>) => {
  const file = e.target.files?.[0];
  if (!file) return;

  try {
    // 1. 顯示載入提示
    const loadingToast = toast.loading('處理圖片中...');

    // 2. 處理圖片 (驗證 + 壓縮 + Base64)
    const base64String = await processImageUpload(file, 2);

    // 3. 更新預覽
    setAvatarPreview(base64String);
    setProfileValue('avatar', base64String);

    // 4. 顯示成功訊息
    toast.dismiss(loadingToast);
    toast.success(`圖片已處理（${formatFileSize(file.size)}）`);
  } catch (error) {
    // 5. 錯誤處理
    toast.error(error.message);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  }
};
```

#### 2. 即時預覽

```tsx
// 顯示預覽（優先）或當前頭像
<Avatar
  src={avatarPreview || profile?.avatar}
  fallback={getAvatarFallback(profile)}
  size="2xl"
/>
```

#### 3. 提交儲存

```typescript
const onSubmitProfile = (data: ProfileFormData) => {
  updateProfileMutation.mutate(data, {
    onSuccess: (response) => {
      // 1. 退出編輯模式
      setEditMode(false);

      // 2. 清除預覽
      setAvatarPreview(null);

      // 3. React Query 自動更新 cache
      // queryClient.invalidateQueries(['salesperson', 'profile']);

      // 4. 顯示成功訊息
      toast.success('個人資料已更新');
    },
    onError: (error) => {
      toast.error('更新失敗，請稍後再試');
    },
  });
};
```

### 錯誤處理

#### 檔案過大

```tsx
// processImageUpload 內部處理
if (file.size > maxSizeMB * 1024 * 1024) {
  // 嘗試壓縮
  const compressed = await compressImage(file, maxSizeMB);

  if (compressed.size > maxSizeMB * 1024 * 1024) {
    throw new Error(`檔案過大（${formatFileSize(file.size)}），請選擇小於 ${maxSizeMB}MB 的圖片`);
  }
}
```

**UI 顯示**:
```tsx
toast.error('檔案過大', {
  description: `檔案大小 3.5MB，請選擇小於 2MB 的圖片`,
  duration: 5000,
});
```

#### 檔案格式錯誤

```tsx
const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
if (!allowedTypes.includes(file.type)) {
  throw new Error('不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片');
}
```

#### 網路錯誤

```tsx
updateProfileMutation.mutate(data, {
  onError: (error: any) => {
    const message = error.response?.data?.message || '更新失敗，請檢查網路連線後重試';

    toast.error('上傳失敗', {
      description: message,
      action: {
        label: '重試',
        onClick: () => updateProfileMutation.mutate(data),
      },
    });
  },
});
```

### 響應式設計

#### Desktop

```tsx
<div className="flex items-start gap-6">
  <div className="relative">
    <Avatar size="2xl" />
    <button className="p-2">
      <Camera className="h-4 w-4" />
    </button>
  </div>
  <div className="flex-1">
    <p>提示文字</p>
  </div>
</div>
```

#### Mobile

```tsx
<div className="flex flex-col items-center gap-4">
  <div className="relative">
    <Avatar size="xl" />  {/* 稍小 */}
    <button className="p-1.5"> {/* 按鈕稍小 */}
      <Camera className="h-3 w-3" />
    </button>
  </div>
  <div className="text-center">
    <p className="text-sm">提示文字</p>
  </div>
</div>
```

---

## 🔄 跨頁面驗證

### 同步更新測試

**測試流程**:
1. Dashboard 上傳新頭像
2. 儲存成功
3. 前往搜尋頁面
4. 確認頭像已更新
5. 前往自己的詳細頁面
6. 確認頭像已更新

### React Query Cache 策略

```typescript
// 更新 Profile 後，自動失效相關 cache
export function useUpdateProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: salespersonApi.updateProfile,
    onSuccess: (response) => {
      // 1. 失效 Profile Cache
      queryClient.invalidateQueries({
        queryKey: salespersonKeys.profile
      });

      // 2. 失效搜尋列表 Cache（包含當前使用者）
      queryClient.invalidateQueries({
        queryKey: ['salespeople']
      });

      // 3. 失效詳細頁 Cache
      queryClient.invalidateQueries({
        queryKey: ['salesperson', response.data.id]
      });

      toast.success('個人資料已更新');
    },
  });
}
```

### 驗收標準

- [ ] Dashboard 上傳成功後，頭像立即更新
- [ ] 搜尋頁面刷新後，顯示新頭像
- [ ] 詳細頁面刷新後，顯示新頭像
- [ ] 不需要手動刷新瀏覽器
- [ ] 所有頁面的 Avatar 尺寸正確
- [ ] 所有頁面的 Fallback 正確顯示

---

## 📊 效能監控

### 關鍵指標

| 頁面 | LCP 目標 | FCP 目標 | Avatar 載入 | 測量方式 |
|------|----------|----------|-------------|----------|
| 搜尋列表 | < 2.5s | < 1.8s | < 2s (20個) | Lighthouse |
| 詳細頁面 | < 2.5s | < 1.8s | < 500ms | Lighthouse |
| Dashboard | < 2.5s | < 1.8s | < 500ms | Lighthouse |

### 測量工具

1. **Lighthouse CI** (自動化)
   ```bash
   npm run lighthouse:ci
   ```

2. **Chrome DevTools** (手動)
   - Network Tab: 檢查圖片載入時間
   - Performance Tab: 分析渲染效能

3. **React DevTools Profiler**
   - 檢查組件重渲染
   - 優化 memo 使用

---

## 🧪 測試計畫

### E2E 測試

```typescript
// 搜尋列表頁
test('should display avatars in search list', async ({ page }) => {
  await page.goto('/search');
  await page.waitForSelector('[role="img"]');

  const avatars = await page.locator('[role="img"]').count();
  expect(avatars).toBeGreaterThan(0);
});

// 詳細頁面
test('should display avatar in detail page', async ({ page }) => {
  await page.goto('/salesperson/1');
  await expect(page.locator('[alt*="頭像"]')).toBeVisible();
});

// Dashboard 上傳
test('should upload avatar successfully', async ({ page }) => {
  await page.goto('/dashboard');
  await page.click('button[aria-label="更換頭像"]');
  await page.setInputFiles('input[type="file"]', './fixtures/avatar.jpg');
  await page.click('button:has-text("儲存變更")');
  await expect(page.locator('text=個人資料已更新')).toBeVisible();
});
```

### Visual Regression 測試

```typescript
test('avatar visual regression', async ({ page }) => {
  await page.goto('/search');
  await expect(page).toHaveScreenshot('search-avatars.png');
});
```

---

**版本**: 1.0
**最後更新**: 2026-01-21
**開發者**: Frontend Team
