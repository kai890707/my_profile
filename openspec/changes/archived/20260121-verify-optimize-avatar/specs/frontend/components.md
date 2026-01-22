# Frontend 組件規格 - Avatar 功能優化

**功能**: Avatar 組件與相關輔助組件
**日期**: 2026-01-21
**版本**: 1.0
**開發者**: Frontend Team

---

## 📋 目錄

- [組件總覽](#組件總覽)
- [Avatar 組件](#avatar-組件)
- [AvatarUploader 組件](#avataruploader-組件)
- [AvatarPreview 組件](#avatarpreview-組件)
- [工具函數](#工具函數)
- [使用範例](#使用範例)

---

## 📦 組件總覽

### 組件架構

```
Avatar 相關組件
├── Avatar (核心組件) - 優化版
│   ├── 圖片顯示
│   ├── Fallback 顯示
│   ├── Lazy Loading 支援
│   ├── Error Boundary
│   └── Loading Skeleton
│
├── AvatarUploader (新增) - 上傳介面
│   ├── 檔案選擇
│   ├── 即時預覽
│   ├── 進度顯示
│   └── 錯誤處理
│
├── AvatarPreview (新增) - 預覽組件
│   ├── 對比顯示
│   ├── 檔案資訊
│   └── 壓縮提示
│
└── Utils (工具函數)
    ├── getAvatarFallback()
    ├── processImageUpload()
    └── formatFileSize()
```

### 設計系統參考

遵循 `frontend/docs/design-system.md`：
- 主色: #0EA5E9 (primary-500)
- 圓角: rounded-full (Avatar)
- 間距: 4px 網格系統
- 陰影: shadow-sm (Avatar)

---

## 🎨 Avatar 組件

### 概述

Avatar 是核心展示組件，負責顯示業務員頭像，支援多種尺寸、Fallback 機制、Lazy Loading 和錯誤處理。

### 現有實作

**位置**: `frontend/components/ui/avatar.tsx`

**功能檢查**:
- ✅ Data URL 和 HTTP URL 雙支援
- ✅ Fallback 機制（姓名縮寫 + 預設圖示）
- ✅ 多尺寸支援（xs, sm, md, lg, xl, 2xl）
- ✅ 狀態指示器（online, offline, away, busy）
- ✅ 漸層色背景（primary-400 → secondary-400）
- ⚠️ 無 Lazy Loading（本次新增）
- ⚠️ 無 Error Handling（本次新增）
- ⚠️ 無 Loading State（本次新增）

### 優化後的 Props 定義

```typescript
interface AvatarProps {
  // 基礎屬性
  src?: string | null;                    // 頭像來源 (Data URL / HTTP URL)
  alt?: string;                           // 替代文字 (用於無障礙)
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl'; // 尺寸
  className?: string;                     // 自定義 class
  fallback?: string;                      // Fallback 文字 (姓名縮寫)

  // 新增屬性
  lazy?: boolean;                         // 啟用 Lazy Loading
  loading?: 'lazy' | 'eager';            // 載入策略
  priority?: boolean;                     // 高優先級載入 (禁用 lazy)
  onLoad?: () => void;                   // 圖片載入完成回調
  onError?: () => void;                  // 圖片載入錯誤回調

  // 現有屬性
  status?: 'online' | 'offline' | 'away' | 'busy'; // 狀態指示器
}
```

### Props 詳細說明

#### src (string | null | undefined)

**用途**: 頭像圖片來源

**支援格式**:
1. **Data URL** (優先)
   ```typescript
   src="data:image/jpeg;base64,/9j/4AAQSkZJRg..."
   ```
   - 用於使用者上傳的頭像
   - 直接使用 `<img>` 標籤
   - 不需要 Next.js Image 優化

2. **HTTP URL**
   ```typescript
   src="https://example.com/avatar.jpg"
   ```
   - 用於外部圖片
   - 使用 Next.js Image 組件
   - 自動優化和 Lazy Loading

3. **null / undefined**
   - 顯示 Fallback

**範例**:
```tsx
<Avatar src="data:image/jpeg;base64,..." />
<Avatar src="https://cdn.example.com/avatar.jpg" />
<Avatar src={null} fallback="王小" />
```

#### size (尺寸)

**預設**: `md`

**尺寸對照表**:

| Size | Avatar 尺寸 | Icon 尺寸 | Status 尺寸 | 使用場景 |
|------|------------|-----------|------------|----------|
| `xs` | 32px (h-8 w-8) | 16px (h-4 w-4) | 8px (h-2 w-2) | 評論、標籤 |
| `sm` | 40px (h-10 w-10) | 20px (h-5 w-5) | 10px (h-2.5 w-2.5) | 列表項目 |
| `md` | 48px (h-12 w-12) | 24px (h-6 w-6) | 12px (h-3 w-3) | 搜尋結果卡片 ⭐ |
| `lg` | 64px (h-16 w-16) | 32px (h-8 w-8) | 14px (h-3.5 w-3.5) | 詳細頁面 |
| `xl` | 80px (h-20 w-20) | 40px (h-10 w-10) | 16px (h-4 w-4) | 個人檔案頁 |
| `2xl` | 96px (h-24 w-24) | 48px (h-12 w-12) | 20px (h-5 w-5) | Dashboard 編輯 ⭐ |

**範例**:
```tsx
<Avatar size="xs" />   // 小尺寸
<Avatar size="md" />   // 預設尺寸 ⭐
<Avatar size="2xl" />  // 大尺寸
```

#### fallback (string | undefined)

**用途**: 當無頭像時顯示的文字（通常是姓名縮寫）

**生成邏輯**: 使用 `getAvatarFallback()` 工具函數

```typescript
// 範例
getAvatarFallback({ full_name: '王小明' }) // 返回: "王小"
getAvatarFallback({ full_name: 'John Doe' }) // 返回: "JD"
getAvatarFallback({ full_name: '王' }) // 返回: "王"
getAvatarFallback({ full_name: '' }) // 返回: undefined → 顯示預設圖示
```

**範例**:
```tsx
<Avatar fallback="王小" />
<Avatar fallback={getAvatarFallback(salesperson)} />
```

#### lazy (boolean) - 新增

**預設**: `false`

**用途**: 啟用 Lazy Loading（使用 Intersection Observer）

**使用場景**:
- ✅ 搜尋列表頁（20 個 Avatar）
- ❌ 詳細頁面（單一 Avatar，需要立即顯示）
- ❌ Dashboard（使用者自己的 Avatar）

**範例**:
```tsx
// 列表頁 - 啟用 Lazy Loading
<Avatar src={avatar} lazy={true} />

// 詳細頁 - 禁用 Lazy Loading
<Avatar src={avatar} lazy={false} />
```

#### priority (boolean) - 新增

**預設**: `false`

**用途**: 高優先級載入（禁用 lazy loading，類似 Next.js Image 的 priority）

**使用場景**:
- 詳細頁面的主要 Avatar
- 首屏重要內容

**範例**:
```tsx
<Avatar src={avatar} priority={true} />
```

#### onLoad / onError - 新增

**用途**: 圖片載入回調

**範例**:
```tsx
const [imageLoaded, setImageLoaded] = useState(false);
const [imageError, setImageError] = useState(false);

<Avatar
  src={avatar}
  onLoad={() => setImageLoaded(true)}
  onError={() => setImageError(true)}
/>
```

#### status (狀態指示器)

**預設**: `undefined` (不顯示)

**選項**: `'online' | 'offline' | 'away' | 'busy'`

**顏色對照**:
- `online`: bg-success-500 (綠色)
- `offline`: bg-slate-400 (灰色)
- `away`: bg-warning-500 (橘色)
- `busy`: bg-error-500 (紅色)

**範例**:
```tsx
<Avatar src={avatar} status="online" />
```

### 組件實作

#### 核心邏輯

```typescript
'use client';

import React, { useState, useEffect, useRef } from 'react';
import { cn } from '@/lib/utils';
import { User } from 'lucide-react';
import Image from 'next/image';

export function Avatar({
  src,
  alt = 'Avatar',
  size = 'md',
  className,
  fallback,
  status,
  lazy = false,
  priority = false,
  onLoad,
  onError,
}: AvatarProps) {
  // 狀態管理
  const [imageError, setImageError] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  const [isInView, setIsInView] = useState(!lazy); // Lazy loading 控制

  // Intersection Observer Ref
  const avatarRef = useRef<HTMLDivElement>(null);

  // 尺寸對照
  const sizes = {
    xs: 'h-8 w-8',
    sm: 'h-10 w-10',
    md: 'h-12 w-12',
    lg: 'h-16 w-16',
    xl: 'h-20 w-20',
    '2xl': 'h-24 w-24',
  };

  const iconSizes = {
    xs: 'h-4 w-4',
    sm: 'h-5 w-5',
    md: 'h-6 w-6',
    lg: 'h-8 w-8',
    xl: 'h-10 w-10',
    '2xl': 'h-12 w-12',
  };

  const statusColors = {
    online: 'bg-success-500',
    offline: 'bg-slate-400',
    away: 'bg-warning-500',
    busy: 'bg-error-500',
  };

  const statusSizes = {
    xs: 'h-2 w-2',
    sm: 'h-2.5 w-2.5',
    md: 'h-3 w-3',
    lg: 'h-3.5 w-3.5',
    xl: 'h-4 w-4',
    '2xl': 'h-5 w-5',
  };

  // Lazy Loading with Intersection Observer
  useEffect(() => {
    if (!lazy || priority) {
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
  }, [lazy, priority]);

  // 處理圖片載入完成
  const handleLoad = () => {
    setImageLoaded(true);
    onLoad?.();
  };

  // 處理圖片載入錯誤
  const handleError = () => {
    setImageError(true);
    onError?.();
  };

  // 決定顯示內容
  const shouldShowFallback = !src || imageError || (lazy && !isInView);

  return (
    <div ref={avatarRef} className={cn('relative inline-block', className)}>
      <div
        className={cn(
          'rounded-full overflow-hidden bg-slate-100 border-2 border-white shadow-sm',
          sizes[size]
        )}
      >
        {shouldShowFallback ? (
          // Fallback 顯示
          <FallbackAvatar fallback={fallback} size={size} iconSizes={iconSizes} />
        ) : (
          // 圖片顯示
          <ImageAvatar
            src={src}
            alt={alt}
            priority={priority}
            isLoaded={imageLoaded}
            onLoad={handleLoad}
            onError={handleError}
          />
        )}
      </div>

      {/* 狀態指示器 */}
      {status && (
        <span
          className={cn(
            'absolute bottom-0 right-0 rounded-full border-2 border-white',
            statusColors[status],
            statusSizes[size]
          )}
          aria-label={`狀態: ${status}`}
        />
      )}
    </div>
  );
}
```

#### Fallback Avatar

```typescript
interface FallbackAvatarProps {
  fallback?: string;
  size: AvatarProps['size'];
  iconSizes: Record<string, string>;
}

function FallbackAvatar({ fallback, size, iconSizes }: FallbackAvatarProps) {
  if (fallback) {
    return (
      <div
        className="flex items-center justify-center h-full w-full bg-gradient-to-br from-primary-400 to-secondary-400 text-white font-bold text-sm"
        role="img"
        aria-label={`${fallback} 的頭像`}
      >
        {fallback}
      </div>
    );
  }

  // 預設圖示
  return (
    <div
      className="flex items-center justify-center h-full w-full text-slate-400"
      role="img"
      aria-label="預設頭像"
    >
      <User className={iconSizes[size!]} />
    </div>
  );
}
```

#### Image Avatar

```typescript
interface ImageAvatarProps {
  src: string;
  alt: string;
  priority: boolean;
  isLoaded: boolean;
  onLoad: () => void;
  onError: () => void;
}

function ImageAvatar({
  src,
  alt,
  priority,
  isLoaded,
  onLoad,
  onError,
}: ImageAvatarProps) {
  // Data URL: 使用原生 img
  if (src.startsWith('data:')) {
    return (
      <img
        src={src}
        alt={alt}
        className={cn(
          'object-cover w-full h-full',
          'transition-opacity duration-300',
          isLoaded ? 'opacity-100' : 'opacity-0'
        )}
        onLoad={onLoad}
        onError={onError}
      />
    );
  }

  // HTTP URL: 使用 Next.js Image
  return (
    <Image
      src={src}
      alt={alt}
      width={96}
      height={96}
      className={cn(
        'object-cover w-full h-full',
        'transition-opacity duration-300',
        isLoaded ? 'opacity-100' : 'opacity-0'
      )}
      loading={priority ? 'eager' : 'lazy'}
      priority={priority}
      unoptimized // Data URL 不需要優化
      onLoad={onLoad}
      onError={onError}
    />
  );
}
```

### 使用範例

#### 1. 搜尋列表（Lazy Loading）

```tsx
<div className="grid md:grid-cols-2 gap-6">
  {salespeople.map((person) => (
    <Card key={person.id}>
      <div className="flex items-center gap-4">
        <Avatar
          src={person.avatar}
          fallback={getAvatarFallback(person)}
          size="md"
          lazy={true}
          alt={`${person.full_name}的頭像`}
        />
        <div>
          <h3>{person.full_name}</h3>
          <p>{person.company?.name}</p>
        </div>
      </div>
    </Card>
  ))}
</div>
```

#### 2. 詳細頁面（高優先級）

```tsx
<div className="flex items-center gap-6">
  <Avatar
    src={salesperson.avatar}
    fallback={getAvatarFallback(salesperson)}
    size="2xl"
    priority={true}
    alt={`${salesperson.full_name}的頭像`}
  />
  <div>
    <h1>{salesperson.full_name}</h1>
    <p>{salesperson.company?.name}</p>
  </div>
</div>
```

#### 3. Dashboard（編輯模式）

```tsx
<div className="relative">
  <Avatar
    src={avatarPreview || profile?.avatar}
    fallback={getAvatarFallback(profile)}
    size="2xl"
  />
  <button
    type="button"
    className="absolute bottom-0 right-0 p-2 bg-primary-600 text-white rounded-full"
    onClick={() => fileInputRef.current?.click()}
    aria-label="更換頭像"
  >
    <Camera className="h-4 w-4" />
  </button>
</div>
```

---

## 📤 AvatarUploader 組件

### 概述

AvatarUploader 是新增的上傳介面組件，封裝完整的檔案選擇、驗證、壓縮和預覽流程。

### Props 定義

```typescript
interface AvatarUploaderProps {
  // 當前頭像
  currentAvatar?: string | null;
  currentFallback?: string;

  // 回調函數
  onUploadStart?: () => void;
  onUploadComplete?: (base64: string) => void;
  onUploadError?: (error: Error) => void;
  onCancel?: () => void;

  // 上傳配置
  maxSizeMB?: number;            // 預設 2MB
  allowedTypes?: string[];       // 預設 ['image/jpeg', 'image/png', 'image/webp', 'image/gif']
  targetSize?: number;           // 壓縮目標尺寸，預設 400px

  // UI 配置
  size?: AvatarProps['size'];    // Avatar 尺寸，預設 '2xl'
  showComparison?: boolean;      // 顯示對比（當前 vs 新），預設 true
  showFileInfo?: boolean;        // 顯示檔案資訊，預設 true

  // 樣式
  className?: string;
  disabled?: boolean;
}
```

### 組件實作

```typescript
'use client';

import { useState, useRef, ChangeEvent } from 'react';
import { Avatar } from './avatar';
import { Button } from './button';
import { Camera, ArrowRight, Loader2, CheckCircle2, AlertCircle } from 'lucide-react';
import { processImageUpload, formatFileSize } from '@/lib/utils/image';
import { toast } from 'sonner';
import { cn } from '@/lib/utils';

export function AvatarUploader({
  currentAvatar,
  currentFallback,
  onUploadStart,
  onUploadComplete,
  onUploadError,
  onCancel,
  maxSizeMB = 2,
  allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
  targetSize = 400,
  size = '2xl',
  showComparison = true,
  showFileInfo = true,
  className,
  disabled = false,
}: AvatarUploaderProps) {
  // 狀態管理
  const [preview, setPreview] = useState<string | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [fileInfo, setFileInfo] = useState<{
    name: string;
    originalSize: number;
    compressedSize: number;
  } | null>(null);

  const fileInputRef = useRef<HTMLInputElement>(null);

  // 處理檔案選擇
  const handleFileChange = async (e: ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    try {
      setIsProcessing(true);
      onUploadStart?.();

      const originalSize = file.size;

      // 顯示載入提示
      const loadingToast = toast.loading('處理圖片中...');

      // 處理圖片（驗證 + 壓縮 + 轉 Base64）
      const base64String = await processImageUpload(file, maxSizeMB);

      // 計算壓縮後大小（從 Base64 估算）
      const compressedSize = Math.round((base64String.length * 3) / 4);

      // 更新狀態
      setPreview(base64String);
      setFileInfo({
        name: file.name,
        originalSize,
        compressedSize,
      });

      // 關閉載入提示
      toast.dismiss(loadingToast);

      // 顯示成功訊息
      const savedSize = originalSize - compressedSize;
      const savedPercent = Math.round((savedSize / originalSize) * 100);

      toast.success('圖片已處理', {
        description: `已壓縮 ${savedPercent}% (${formatFileSize(savedSize)})`,
      });

      // 通知上層
      onUploadComplete?.(base64String);
    } catch (error) {
      // 錯誤處理
      const errorMessage = error instanceof Error ? error.message : '處理圖片失敗';
      toast.error(errorMessage);
      onUploadError?.(error as Error);

      // 清除檔案選擇
      if (fileInputRef.current) {
        fileInputRef.current.value = '';
      }
    } finally {
      setIsProcessing(false);
    }
  };

  // 重置狀態
  const handleCancel = () => {
    setPreview(null);
    setFileInfo(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
    onCancel?.();
  };

  return (
    <div className={cn('space-y-4', className)}>
      {/* Avatar 與上傳按鈕 */}
      {!preview ? (
        // 初始狀態
        <div className="space-y-2">
          <div className="relative inline-block">
            <Avatar
              src={currentAvatar}
              fallback={currentFallback}
              size={size}
            />
            <button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              disabled={disabled || isProcessing}
              className={cn(
                'absolute bottom-0 right-0',
                'p-2 rounded-full',
                'bg-primary-600 text-white',
                'shadow-md',
                'hover:bg-primary-700 hover:scale-110',
                'transition-all duration-200',
                'focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                'disabled:opacity-50 disabled:cursor-not-allowed'
              )}
              aria-label="更換頭像"
            >
              <Camera className="h-4 w-4" />
            </button>
          </div>

          <div>
            <p className="text-sm text-slate-600">點擊相機圖示上傳頭像</p>
            <p className="text-xs text-slate-500">
              支援 JPG、PNG、WebP、GIF，最大 {maxSizeMB}MB
            </p>
          </div>

          <input
            ref={fileInputRef}
            type="file"
            accept={allowedTypes.join(',')}
            className="hidden"
            onChange={handleFileChange}
            disabled={disabled || isProcessing}
            aria-label="選擇頭像檔案"
          />
        </div>
      ) : (
        // 預覽狀態
        <div className="space-y-4">
          {/* 對比顯示 */}
          {showComparison && (
            <div className="flex items-center gap-6">
              {/* 當前頭像 */}
              <div className="text-center">
                <p className="text-xs text-slate-500 mb-2">目前頭像</p>
                <Avatar
                  src={currentAvatar}
                  fallback={currentFallback}
                  size={size}
                />
              </div>

              {/* 箭頭 */}
              <div className="text-slate-400">
                <ArrowRight className="h-6 w-6" />
              </div>

              {/* 新頭像 */}
              <div className="text-center">
                <p className="text-xs text-slate-500 mb-2">新頭像</p>
                <Avatar src={preview} size={size} />
              </div>
            </div>
          )}

          {/* 檔案資訊 */}
          {showFileInfo && fileInfo && (
            <div className="bg-slate-50 rounded-lg p-4 space-y-2">
              <div className="flex items-center justify-between text-sm">
                <span className="text-slate-600">檔案名稱</span>
                <span className="text-slate-900 font-medium truncate max-w-[200px]">
                  {fileInfo.name}
                </span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-slate-600">原始大小</span>
                <span className="text-slate-900">{formatFileSize(fileInfo.originalSize)}</span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-slate-600">壓縮後</span>
                <span className="text-slate-900 font-medium">
                  {formatFileSize(fileInfo.compressedSize)}
                </span>
              </div>
            </div>
          )}

          {/* 壓縮提示 */}
          {fileInfo && fileInfo.compressedSize < fileInfo.originalSize && (
            <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
              <div className="flex items-start gap-3">
                <CheckCircle2 className="h-5 w-5 text-primary-600 flex-shrink-0 mt-0.5" />
                <div className="flex-1">
                  <p className="text-sm font-medium text-primary-900">
                    圖片已壓縮
                  </p>
                  <p className="text-xs text-primary-700 mt-1">
                    節省 {formatFileSize(fileInfo.originalSize - fileInfo.compressedSize)}
                    ({Math.round(((fileInfo.originalSize - fileInfo.compressedSize) / fileInfo.originalSize) * 100)}%)
                  </p>
                </div>
              </div>
            </div>
          )}

          {/* 操作按鈕 */}
          <div className="flex gap-3">
            <Button
              type="button"
              variant="outline"
              onClick={handleCancel}
              disabled={disabled || isProcessing}
            >
              取消
            </Button>
            <Button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              disabled={disabled || isProcessing}
            >
              重新選擇
            </Button>
          </div>
        </div>
      )}

      {/* 處理中提示 */}
      {isProcessing && (
        <div className="flex items-center gap-3 text-sm text-slate-600">
          <Loader2 className="h-4 w-4 animate-spin" />
          <span>處理圖片中...</span>
        </div>
      )}
    </div>
  );
}
```

### 使用範例

```tsx
import { AvatarUploader } from '@/components/ui/avatar-uploader';

function ProfileEditForm() {
  const [avatarPreview, setAvatarPreview] = useState<string | null>(null);

  return (
    <form onSubmit={handleSubmit}>
      <AvatarUploader
        currentAvatar={profile?.avatar}
        currentFallback={getAvatarFallback(profile)}
        onUploadComplete={(base64) => {
          setAvatarPreview(base64);
          setValue('avatar', base64); // React Hook Form
        }}
        onUploadError={(error) => {
          console.error('Upload error:', error);
        }}
        size="2xl"
        showComparison={true}
        showFileInfo={true}
      />

      <Button type="submit">儲存變更</Button>
    </form>
  );
}
```

---

## 🔧 工具函數

### getAvatarFallback()

**位置**: `frontend/lib/utils/avatar.ts` (現有)

**用途**: 生成姓名縮寫 Fallback

**實作**:
```typescript
export function getAvatarFallback(user: {
  full_name?: string | null;
}): string | undefined {
  if (!user.full_name) return undefined;

  const name = user.full_name.trim();
  if (!name) return undefined;

  // 中文姓名：取前兩字
  if (/[\u4e00-\u9fa5]/.test(name)) {
    return name.slice(0, 2);
  }

  // 英文姓名：取首字母
  const words = name.split(' ').filter(Boolean);
  if (words.length >= 2) {
    return words[0][0] + words[1][0];
  }

  return words[0]?.slice(0, 2).toUpperCase();
}
```

**範例**:
```typescript
getAvatarFallback({ full_name: '王小明' }); // "王小"
getAvatarFallback({ full_name: 'John Doe' }); // "JD"
getAvatarFallback({ full_name: '王' }); // "王"
getAvatarFallback({ full_name: '' }); // undefined
```

---

## 📚 使用範例總結

### 搜尋列表頁

```tsx
import { Avatar } from '@/components/ui/avatar';
import { getAvatarFallback } from '@/lib/utils/avatar';

<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}
  size="md"
  lazy={true}
  alt={`${salesperson.full_name}的頭像`}
/>
```

### 詳細頁面

```tsx
<Avatar
  src={salesperson.avatar}
  fallback={getAvatarFallback(salesperson)}
  size="2xl"
  priority={true}
  alt={`${salesperson.full_name}的頭像`}
/>
```

### Dashboard 編輯

```tsx
import { AvatarUploader } from '@/components/ui/avatar-uploader';

<AvatarUploader
  currentAvatar={profile?.avatar}
  currentFallback={getAvatarFallback(profile)}
  onUploadComplete={(base64) => {
    setValue('avatar', base64);
  }}
  size="2xl"
/>
```

---

## 🧪 測試建議

### 單元測試

```typescript
describe('Avatar', () => {
  it('should render image when src is provided', () => {
    render(<Avatar src="data:image/jpeg;base64,..." />);
    expect(screen.getByRole('img')).toBeInTheDocument();
  });

  it('should render fallback when no src', () => {
    render(<Avatar fallback="王小" />);
    expect(screen.getByText('王小')).toBeInTheDocument();
  });

  it('should render default icon when no src and no fallback', () => {
    render(<Avatar />);
    expect(screen.getByRole('img')).toBeInTheDocument();
  });

  it('should handle image load error', () => {
    const onError = jest.fn();
    render(<Avatar src="invalid.jpg" onError={onError} />);
    // Simulate error
    fireEvent.error(screen.getByRole('img'));
    expect(onError).toHaveBeenCalled();
  });
});
```

### E2E 測試

```typescript
test('should upload avatar successfully', async ({ page }) => {
  await page.goto('/dashboard');
  await page.click('button[aria-label="更換頭像"]');
  await page.setInputFiles('input[type="file"]', './fixtures/avatar.jpg');
  await expect(page.locator('[alt="新頭像"]')).toBeVisible();
  await page.click('button:has-text("儲存變更")');
  await expect(page.locator('text=頭像已更新')).toBeVisible();
});
```

---

**版本**: 1.0
**最後更新**: 2026-01-21
**開發者**: Frontend Team
