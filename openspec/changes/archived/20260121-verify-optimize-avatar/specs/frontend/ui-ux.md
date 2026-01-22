# Frontend UI/UX 設計規格 - Avatar 功能優化

**功能**: Avatar 顯示與上傳優化
**日期**: 2026-01-21
**版本**: 1.0
**設計師**: Product Design Team

---

## 📋 目錄

- [設計目標](#設計目標)
- [使用者研究](#使用者研究)
- [設計原則](#設計原則)
- [Avatar 顯示設計](#avatar-顯示設計)
- [上傳流程設計](#上傳流程設計)
- [狀態設計](#狀態設計)
- [響應式設計](#響應式設計)
- [無障礙設計](#無障礙設計)
- [互動設計](#互動設計)
- [視覺規範](#視覺規範)

---

## 🎯 設計目標

### 核心目標

1. **一致性體驗**
   - 所有頁面的 Avatar 顯示風格統一
   - 載入、錯誤、成功狀態視覺一致
   - 跨頁面同步更新無感知

2. **高效能**
   - 列表頁面快速載入 (20 個 Avatar < 2s)
   - Lazy loading 減少初始載入時間
   - 圖片壓縮不影響視覺品質

3. **友善上傳**
   - 拖放上傳支援
   - 即時預覽
   - 清晰的進度提示
   - 友善的錯誤提示

4. **專業可信**
   - 高品質的 Avatar 顯示
   - 優雅的 Fallback 設計
   - 流暢的載入動畫

---

## 👥 使用者研究

### 主要使用者

**業務員 - Alex (30 歲)**

**情境**: 上傳專業頭像提升個人形象

**目標**:
- 快速上傳頭像
- 確保頭像清晰專業
- 在搜尋列表中脫穎而出

**痛點**:
- 不知道檔案過大會被拒絕
- 上傳後不確定是否成功
- 不清楚圖片品質是否符合要求

**期望**:
- 自動壓縮過大檔案
- 即時預覽上傳結果
- 明確的成功確認

---

### 次要使用者

**一般使用者 - Linda (35 歲)**

**情境**: 瀏覽業務員列表尋找合作對象

**目標**:
- 快速瀏覽大量業務員資料
- 透過頭像快速識別業務員
- 專業形象增加信任感

**痛點**:
- 頁面載入慢
- 圖片破圖影響體驗
- 無頭像的業務員難以記憶

**期望**:
- 快速流暢的瀏覽體驗
- 所有 Avatar 都能正確顯示
- 有意義的 Fallback 顯示

---

## 🎨 設計原則

### 1. 漸進式增強 (Progressive Enhancement)

```
基礎體驗 (所有使用者)
└─ 姓名縮寫 Fallback
   └─ 漸層色背景
      └─ 預設圖示

增強體驗 (現代瀏覽器)
└─ Lazy Loading
   └─ 平滑載入動畫
      └─ Hover 互動效果
```

### 2. 即時反饋 (Immediate Feedback)

```
使用者操作 → 立即視覺回饋 → 背景處理
   ↓              ↓              ↓
選擇檔案 →    顯示預覽   →   開始壓縮
點擊上傳 →    顯示進度   →   上傳檔案
完成上傳 →    更新頭像   →   同步快取
```

### 3. 容錯設計 (Fault Tolerance)

```
錯誤場景              → 系統行為           → 使用者體驗
─────────────────────────────────────────────────────
檔案過大              → 自動壓縮          → 無感知處理
圖片格式錯誤          → 清晰錯誤提示      → 知道如何修正
網路連線失敗          → 保留預覽狀態      → 可重試上傳
Avatar 載入失敗       → 顯示 Fallback     → 不影響使用
```

### 4. 效能優先 (Performance First)

```
優化策略              → 技術實作           → 效能提升
─────────────────────────────────────────────────────
Lazy Loading          → Intersection Observer → 減少初始載入 60%
圖片壓縮              → Canvas API         → 減少檔案大小 70%
快取策略              → React Query        → 減少 API 請求 80%
Skeleton Loading      → CSS Animation      → 感知載入更快
```

---

## 🖼️ Avatar 顯示設計

### 設計概覽

Avatar 是業務員的視覺識別，需要在所有頁面保持一致的顯示風格。

### 顯示模式

#### Mode 1: 已上傳 Avatar (優先)

```
┌─────────────┐
│             │
│   [照片]    │  ← 使用者上傳的頭像
│             │     Data URL / HTTP URL
└─────────────┘
```

**特點**:
- 圓形顯示 (`rounded-full`)
- 白色邊框 (`border-2 border-white`)
- 淺陰影 (`shadow-sm`)
- 物件適配 (`object-cover`)

#### Mode 2: 姓名縮寫 Fallback (次要)

```
┌─────────────┐
│             │
│     王小     │  ← 姓名前兩字
│             │     漸層背景
└─────────────┘
```

**特點**:
- 漸層背景 (`from-primary-400 to-secondary-400`)
- 白色文字 (`text-white font-bold`)
- 置中對齊 (`flex items-center justify-center`)
- 字體大小根據 Avatar 尺寸調整

#### Mode 3: 預設圖示 Fallback (最後)

```
┌─────────────┐
│             │
│   [👤]      │  ← User Icon (Lucide)
│             │     灰色背景
└─────────────┘
```

**特點**:
- 淺灰背景 (`bg-slate-100`)
- 圖示顏色 (`text-slate-400`)
- 用於無姓名的情境

### 尺寸系統

根據使用情境定義 6 種尺寸：

| 尺寸 | 像素 | Tailwind | 使用場景 |
|------|------|----------|----------|
| **xs** | 32px (8×8) | `h-8 w-8` | 評論、標籤 |
| **sm** | 40px (10×10) | `h-10 w-10` | 列表項目、次要顯示 |
| **md** | 48px (12×12) | `h-12 w-12` | 搜尋結果卡片 ⭐ |
| **lg** | 64px (16×16) | `h-16 w-16` | 詳細頁面側邊欄 |
| **xl** | 80px (20×20) | `h-20 w-20` | 個人檔案頁 |
| **2xl** | 96px (24×24) | `h-24 w-24` | Dashboard 編輯 ⭐ |

**圖示尺寸對應**:
```typescript
xs: h-4 w-4   (16px)
sm: h-5 w-5   (20px)
md: h-6 w-6   (24px)
lg: h-8 w-8   (32px)
xl: h-10 w-10 (40px)
2xl: h-12 w-12 (48px)
```

### 狀態指示器 (Optional)

用於顯示業務員的線上狀態（未來功能）：

```
┌─────────────┐
│             │
│   [照片]    │
│          ●  │ ← 狀態點 (右下角)
└─────────────┘

狀態色彩:
- Online: bg-success-500 (綠色)
- Offline: bg-slate-400 (灰色)
- Away: bg-warning-500 (橘色)
- Busy: bg-error-500 (紅色)
```

### 頁面應用規範

#### 1. 搜尋列表頁 (`/search`)

**使用情境**: 顯示 20 個業務員的 Avatar

**尺寸**: `md` (48px)

**佈局**:
```tsx
┌───────────────────────────────────────┐
│  [●]  王小明                          │
│       業務經理 | 台北市               │
│       ★★★★☆ 4.2                    │
└───────────────────────────────────────┘

卡片佈局:
- Avatar: 左側，md 尺寸
- 姓名: 右側，text-lg font-semibold
- 間距: gap-4
```

**效能優化**:
- 使用 Lazy Loading (Intersection Observer)
- 初始載入前 5 個
- 滾動時動態載入剩餘

**Skeleton Loading**:
```tsx
<div className="h-12 w-12 rounded-full bg-slate-200 animate-pulse" />
```

#### 2. 業務員詳細頁 (`/salesperson/[id]`)

**使用情境**: 顯示單一業務員的完整資料

**尺寸**: `2xl` (96px)

**佈局**:
```tsx
┌───────────────────────────────────────┐
│  [  大頭像  ]  王小明                 │
│               業務經理                │
│               三商美邦人壽            │
│               專長: 保險、理財        │
└───────────────────────────────────────┘

佈局:
- Avatar: 左側，2xl 尺寸
- 資訊: 右側，垂直排列
- 間距: gap-6
- 對齊: items-center
```

**載入策略**:
- 高優先級載入 (無 Lazy Loading)
- 使用 Next.js Image 優化
- 預載入較大尺寸

#### 3. Dashboard Profile (`/dashboard`)

**使用情境**: 編輯個人頭像

**尺寸**: `2xl` (96px)

**佈局**:
```tsx
┌───────────────────┐
│                   │
│   [  Avatar  ]    │
│        📷         │ ← 相機按鈕 (右下角)
│                   │
└───────────────────┘

相機按鈕:
- 位置: absolute bottom-0 right-0
- 尺寸: 32×32 px
- 背景: bg-primary-600
- 圓形: rounded-full
- 陰影: shadow-md
- Hover: bg-primary-700 + scale-110
```

**互動設計**:
1. Hover Avatar → 顯示提示「點擊更換頭像」
2. 點擊相機按鈕 → 開啟檔案選擇
3. 選擇檔案 → 顯示預覽
4. 儲存變更 → 更新頭像

---

## 📤 上傳流程設計

### 完整流程圖

```
[開始]
  ↓
┌──────────────────┐
│ 點擊相機按鈕      │ ← Hover 提示
└──────────────────┘
  ↓
┌──────────────────┐
│ 開啟檔案選擇器    │ ← 系統對話框
└──────────────────┘
  ↓
┌──────────────────┐
│ 選擇檔案          │ ← JPG/PNG/WebP/GIF
└──────────────────┘
  ↓
╔══════════════════╗
║ 檔案驗證          ║ ← 前端驗證
╚══════════════════╝
  ↓        ↓
  ✓        ✗
  ↓        └─→ [顯示錯誤訊息] → [返回選擇]
  ↓
┌──────────────────┐
│ 即時預覽          │ ← 顯示縮圖
└──────────────────┘
  ↓
╔══════════════════╗
║ 圖片壓縮          ║ ← Canvas API
╚══════════════════╝
  ↓
┌──────────────────┐
│ 顯示壓縮資訊      │ ← "已壓縮至 300KB"
└──────────────────┘
  ↓
┌──────────────────┐
│ 點擊「儲存變更」  │ ← 確認上傳
└──────────────────┘
  ↓
╔══════════════════╗
║ 上傳到 Backend    ║ ← Base64 + API
╚══════════════════╝
  ↓        ↓
  ✓        ✗
  ↓        └─→ [顯示錯誤] → [可重試]
  ↓
┌──────────────────┐
│ 更新成功          │ ← Toast 通知
└──────────────────┘
  ↓
╔══════════════════╗
║ 更新快取          ║ ← React Query
╚══════════════════╝
  ↓
┌──────────────────┐
│ 跨頁面同步        │ ← 所有頁面立即更新
└──────────────────┘
  ↓
[完成]
```

### UI 設計細節

#### Step 1: 觸發上傳

**視覺設計**:
```tsx
<div className="relative">
  {/* Avatar */}
  <Avatar src={currentAvatar} size="2xl" />

  {/* 相機按鈕 */}
  <button
    type="button"
    className="
      absolute bottom-0 right-0
      p-2
      bg-primary-600 text-white
      rounded-full
      shadow-md
      hover:bg-primary-700
      hover:scale-110
      transition-all duration-200
      focus:outline-none
      focus:ring-2
      focus:ring-primary-500
      focus:ring-offset-2
    "
    aria-label="更換頭像"
  >
    <Camera className="h-4 w-4" />
  </button>

  {/* Hidden File Input */}
  <input
    ref={fileInputRef}
    type="file"
    accept="image/jpeg,image/jpg,image/png,image/webp,image/gif"
    className="hidden"
  />
</div>

{/* 提示文字 */}
<div className="mt-2">
  <p className="text-sm text-slate-600">
    點擊相機圖示上傳頭像
  </p>
  <p className="text-xs text-slate-500">
    支援 JPG、PNG、WebP、GIF，最大 2MB
  </p>
</div>
```

**Hover 效果**:
```css
/* 相機按鈕 Hover */
button:hover {
  background-color: #0284c7; /* primary-700 */
  transform: scale(1.1);
  box-shadow: 0 4px 6px rgba(0,0,0,0.15);
}

/* Avatar Hover (可選) */
.avatar-container:hover {
  opacity: 0.9;
  cursor: pointer;
}
```

#### Step 2: 檔案選擇

**系統對話框**:
```
檔案選擇器
├─ 標題: "選擇圖片"
├─ 篩選: 圖片檔案 (*.jpg, *.png, *.webp, *.gif)
└─ 預覽: 系統提供的縮圖預覽
```

**支援格式**:
- `image/jpeg`
- `image/jpg`
- `image/png`
- `image/webp`
- `image/gif`

#### Step 3: 檔案驗證

**驗證項目**:

1. **檔案類型**
   ```typescript
   if (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(file.type)) {
     throw new Error('不支援的檔案格式，請選擇 JPG、PNG、WebP 或 GIF 圖片');
   }
   ```

2. **檔案大小**
   ```typescript
   if (file.size > 2 * 1024 * 1024) {
     // 嘗試壓縮，稍後處理
   }
   ```

3. **檔案完整性**
   ```typescript
   // 嘗試載入圖片
   const img = new Image();
   img.src = URL.createObjectURL(file);
   img.onerror = () => {
     throw new Error('圖片檔案損壞，無法讀取');
   };
   ```

**錯誤提示 UI**:
```tsx
{/* 錯誤提示 Toast */}
toast.error(
  '不支援的檔案格式',
  {
    description: '請選擇 JPG、PNG、WebP 或 GIF 圖片',
    duration: 5000,
  }
);
```

#### Step 4: 即時預覽

**預覽 UI**:
```tsx
<div className="space-y-4">
  {/* 預覽區域 */}
  <div className="flex items-center gap-6">
    {/* 當前頭像 */}
    <div className="text-center">
      <p className="text-xs text-slate-500 mb-2">目前頭像</p>
      <Avatar src={currentAvatar} size="2xl" />
    </div>

    {/* 箭頭 */}
    <div className="text-slate-400">
      <ArrowRight className="h-6 w-6" />
    </div>

    {/* 新頭像 */}
    <div className="text-center">
      <p className="text-xs text-slate-500 mb-2">新頭像</p>
      <Avatar src={avatarPreview} size="2xl" />
    </div>
  </div>

  {/* 檔案資訊 */}
  <div className="bg-slate-50 rounded-lg p-4">
    <div className="flex items-center justify-between text-sm">
      <span className="text-slate-600">檔案名稱</span>
      <span className="text-slate-900 font-medium">{fileName}</span>
    </div>
    <div className="flex items-center justify-between text-sm mt-2">
      <span className="text-slate-600">原始大小</span>
      <span className="text-slate-900">{formatFileSize(originalSize)}</span>
    </div>
  </div>
</div>
```

#### Step 5: 圖片壓縮

**壓縮中 UI**:
```tsx
{isCompressing && (
  <div className="flex items-center gap-3 text-sm text-slate-600">
    <Loader2 className="h-4 w-4 animate-spin" />
    <span>壓縮圖片中...</span>
  </div>
)}
```

**壓縮完成提示**:
```tsx
{compressionComplete && (
  <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
    <div className="flex items-start gap-3">
      <CheckCircle2 className="h-5 w-5 text-primary-600 flex-shrink-0 mt-0.5" />
      <div className="flex-1">
        <p className="text-sm font-medium text-primary-900">
          圖片已壓縮
        </p>
        <p className="text-xs text-primary-700 mt-1">
          {formatFileSize(originalSize)} → {formatFileSize(compressedSize)}
          <span className="ml-2 text-primary-600">
            (減少 {compressionRatio}%)
          </span>
        </p>
      </div>
    </div>
  </div>
)}
```

#### Step 6: 確認上傳

**按鈕狀態**:
```tsx
<div className="flex gap-3 pt-4">
  {/* 儲存按鈕 */}
  <Button
    type="submit"
    isLoading={isUploading}
    disabled={!avatarPreview || isUploading}
  >
    <Save className="mr-2 h-4 w-4" />
    {isUploading ? '上傳中...' : '儲存變更'}
  </Button>

  {/* 取消按鈕 */}
  <Button
    type="button"
    variant="outline"
    onClick={handleCancel}
    disabled={isUploading}
  >
    取消
  </Button>
</div>
```

**上傳中 UI**:
```tsx
{isUploading && (
  <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <Card className="w-80">
      <CardContent className="p-6">
        <div className="text-center">
          <Loader2 className="h-12 w-12 animate-spin text-primary-600 mx-auto mb-4" />
          <h3 className="text-lg font-semibold text-slate-900 mb-2">
            上傳中
          </h3>
          <p className="text-sm text-slate-600">
            請稍候，正在儲存您的頭像...
          </p>
        </div>
      </CardContent>
    </Card>
  </div>
)}
```

#### Step 7: 上傳完成

**成功提示**:
```tsx
toast.success(
  '頭像已更新',
  {
    description: '您的新頭像已成功儲存',
    duration: 3000,
  }
);
```

**失敗提示**:
```tsx
toast.error(
  '上傳失敗',
  {
    description: error.message || '請檢查網路連線後重試',
    action: {
      label: '重試',
      onClick: handleRetry,
    },
    duration: 5000,
  }
);
```

---

## 🎭 狀態設計

### 1. Loading 狀態

#### Skeleton Loading (列表頁)

```tsx
<div className="flex items-center gap-4">
  {/* Avatar Skeleton */}
  <div className="h-12 w-12 rounded-full bg-slate-200 animate-pulse" />

  {/* 內容 Skeleton */}
  <div className="flex-1 space-y-2">
    <div className="h-4 w-32 bg-slate-200 rounded animate-pulse" />
    <div className="h-3 w-24 bg-slate-200 rounded animate-pulse" />
  </div>
</div>
```

**動畫**:
```css
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
```

#### 淡入載入 (詳細頁)

```tsx
<Avatar
  src={avatar}
  className={cn(
    'transition-opacity duration-300',
    imageLoaded ? 'opacity-100' : 'opacity-0'
  )}
  onLoad={() => setImageLoaded(true)}
/>
```

### 2. Error 狀態

#### 載入錯誤

**顯示 Fallback**:
```tsx
const [imageError, setImageError] = useState(false);

{!src || imageError ? (
  <FallbackAvatar fallback={getAvatarFallback(user)} />
) : (
  <img
    src={src}
    onError={() => setImageError(true)}
  />
)}
```

#### 上傳錯誤

**錯誤訊息對照表**:

| 錯誤情境 | 錯誤訊息 | 解決方案提示 |
|---------|---------|-------------|
| 檔案過大 (> 2MB) | "檔案大小不能超過 2MB" | "請選擇較小的圖片" |
| 格式錯誤 | "不支援的檔案格式" | "請選擇 JPG、PNG、WebP 或 GIF 圖片" |
| 檔案損壞 | "圖片檔案損壞，無法讀取" | "請選擇其他圖片" |
| 網路錯誤 | "上傳失敗，請檢查網路連線" | 提供「重試」按鈕 |
| 伺服器錯誤 | "伺服器錯誤，請稍後再試" | "如持續發生，請聯絡客服" |

**UI 範例**:
```tsx
<Alert variant="error">
  <AlertCircle className="h-4 w-4" />
  <AlertTitle>上傳失敗</AlertTitle>
  <AlertDescription>
    {errorMessage}
    {canRetry && (
      <Button
        variant="link"
        size="sm"
        onClick={handleRetry}
        className="ml-2"
      >
        重試
      </Button>
    )}
  </AlertDescription>
</Alert>
```

### 3. Success 狀態

#### Toast 通知

```tsx
toast.success('頭像已更新', {
  description: '您的新頭像已在所有頁面同步更新',
  icon: <CheckCircle2 className="h-5 w-5" />,
});
```

#### 視覺確認

```tsx
{/* 成功動畫 */}
<motion.div
  initial={{ scale: 0 }}
  animate={{ scale: 1 }}
  transition={{ type: 'spring', stiffness: 200 }}
>
  <CheckCircle2 className="h-12 w-12 text-success-500" />
</motion.div>
```

### 4. Empty 狀態

#### 無頭像 (Fallback)

```tsx
<div className="
  flex items-center justify-center
  h-full w-full
  bg-gradient-to-br from-primary-400 to-secondary-400
  text-white font-bold text-sm
">
  {getAvatarFallback({ full_name: '王小明' })}
  {/* 顯示: "王小" */}
</div>
```

---

## 📱 響應式設計

### 斷點定義

```typescript
const breakpoints = {
  mobile: '< 640px',    // 手機
  tablet: '640px - 1023px',  // 平板
  desktop: '≥ 1024px',   // 桌面
};
```

### Avatar 尺寸調整

#### Dashboard Profile

```tsx
{/* Desktop: 2xl (96px) */}
<Avatar
  src={avatar}
  size="2xl"
  className="hidden md:block"
/>

{/* Mobile: xl (80px) */}
<Avatar
  src={avatar}
  size="xl"
  className="md:hidden"
/>
```

#### 搜尋列表

```tsx
{/* Desktop: md (48px) */}
<Avatar
  src={avatar}
  size="md"
  className="hidden sm:block"
/>

{/* Mobile: sm (40px) */}
<Avatar
  src={avatar}
  size="sm"
  className="sm:hidden"
/>
```

### 相機按鈕調整

```tsx
<button className={cn(
  'absolute bottom-0 right-0 rounded-full',
  'bg-primary-600 text-white',
  // Mobile: 較小
  'p-1.5',
  // Desktop: 較大
  'md:p-2'
)}>
  <Camera className={cn(
    'h-3 w-3',      // Mobile
    'md:h-4 md:w-4' // Desktop
  )} />
</button>
```

### 上傳流程響應式

#### Desktop 佈局

```tsx
<div className="flex items-center gap-6">
  {/* 當前頭像 */}
  <div>當前</div>
  {/* 箭頭 */}
  <ArrowRight />
  {/* 新頭像 */}
  <div>新的</div>
</div>
```

#### Mobile 佈局

```tsx
<div className="flex flex-col items-center gap-4">
  {/* 當前頭像 */}
  <div>當前</div>
  {/* 箭頭 (向下) */}
  <ArrowDown />
  {/* 新頭像 */}
  <div>新的</div>
</div>
```

### 觸控優化

**最小觸控區域**: 44×44 px

```tsx
<button className={cn(
  'p-2',           // 視覺尺寸
  'min-h-[44px]',  // 觸控區域
  'min-w-[44px]'
)}>
  <Camera />
</button>
```

---

## ♿ 無障礙設計

### ARIA 屬性

```tsx
{/* Avatar 圖片 */}
<img
  src={avatar}
  alt={`${fullName}的頭像`}
  role="img"
/>

{/* Fallback */}
<div
  role="img"
  aria-label={`${fullName}的頭像 (姓名縮寫)`}
>
  {fallback}
</div>

{/* 上傳按鈕 */}
<button
  type="button"
  aria-label="更換頭像"
  aria-describedby="avatar-help-text"
>
  <Camera />
</button>

<span id="avatar-help-text" className="sr-only">
  點擊上傳新頭像，支援 JPG、PNG、WebP、GIF 格式，最大 2MB
</span>

{/* Loading 狀態 */}
<div
  role="status"
  aria-live="polite"
  aria-busy="true"
>
  <span className="sr-only">載入頭像中</span>
  <Loader2 className="animate-spin" />
</div>
```

### 鍵盤導航

```tsx
{/* 相機按鈕 */}
<button
  onKeyDown={(e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      handleClick();
    }
  }}
  className="
    focus:outline-none
    focus:ring-2
    focus:ring-primary-500
    focus:ring-offset-2
  "
>
  <Camera />
</button>
```

### Screen Reader 友善

```tsx
{/* 上傳進度 */}
<div aria-live="polite" aria-atomic="true">
  {isCompressing && (
    <span className="sr-only">
      正在壓縮圖片，請稍候
    </span>
  )}
  {isUploading && (
    <span className="sr-only">
      正在上傳頭像，請稍候
    </span>
  )}
  {uploadSuccess && (
    <span className="sr-only">
      頭像已成功更新
    </span>
  )}
</div>
```

### 色彩對比

確保所有文字符合 WCAG AA 標準：

```typescript
// 檢查對比度
const contrastRatios = {
  'text-slate-900 on bg-white': 19.64,     // ✅ Pass
  'text-slate-700 on bg-white': 10.69,     // ✅ Pass
  'text-slate-600 on bg-white': 7.74,      // ✅ Pass
  'text-white on bg-primary-600': 4.93,    // ✅ Pass
  'text-white on primary gradient': 5.21,  // ✅ Pass
};
```

---

## 🎯 互動設計

### Hover 效果

#### Avatar Hover (可點擊)

```tsx
<div className={cn(
  'relative cursor-pointer',
  'transition-transform duration-200',
  'hover:scale-105',
  'hover:shadow-lg'
)}>
  <Avatar src={avatar} />
</div>
```

#### 相機按鈕 Hover

```tsx
<button className={cn(
  'bg-primary-600',
  'hover:bg-primary-700',
  'hover:scale-110',
  'transition-all duration-200',
  'hover:shadow-lg'
)}>
  <Camera />
</button>
```

### 點擊回饋

```tsx
<button className={cn(
  'active:scale-95',       // 按下時縮小
  'transition-transform',
  'duration-100'
)}>
  上傳
</button>
```

### Drag & Drop (未來功能)

```tsx
<div
  onDrop={handleDrop}
  onDragOver={handleDragOver}
  onDragEnter={handleDragEnter}
  onDragLeave={handleDragLeave}
  className={cn(
    'border-2 border-dashed rounded-lg p-8',
    isDragging
      ? 'border-primary-500 bg-primary-50'
      : 'border-slate-300 bg-white'
  )}
>
  <Upload className="h-12 w-12 mx-auto text-slate-400" />
  <p className="text-sm text-slate-600 mt-4 text-center">
    拖放圖片到這裡，或點擊上傳
  </p>
</div>
```

---

## 🎨 視覺規範

### 色彩運用

```typescript
// Avatar 相關色彩
const colors = {
  // Fallback 漸層
  gradient: 'from-primary-400 to-secondary-400',

  // 邊框
  border: 'border-white',
  borderThickness: 'border-2',

  // 背景
  defaultBg: 'bg-slate-100',

  // 圖示
  iconColor: 'text-slate-400',

  // 相機按鈕
  cameraBg: 'bg-primary-600',
  cameraHover: 'bg-primary-700',
  cameraText: 'text-white',
};
```

### 陰影系統

```typescript
const shadows = {
  avatar: 'shadow-sm',           // Avatar 預設陰影
  avatarHover: 'shadow-lg',      // Hover 增強陰影
  cameraButton: 'shadow-md',     // 相機按鈕陰影
};
```

### 動畫時長

```typescript
const durations = {
  fast: 'duration-100',      // 點擊回饋
  normal: 'duration-200',    // Hover 效果
  slow: 'duration-300',      // 淡入淡出
};
```

### 字體規範

```typescript
// Fallback 文字
const typography = {
  size: 'text-sm',        // 根據 Avatar 尺寸調整
  weight: 'font-bold',
  color: 'text-white',
};
```

---

## 📊 效能指標

### 目標指標 (參考 metrics-standards.md)

| 指標 | 目標值 | 測量方式 |
|------|--------|---------|
| **LCP** | < 2.5s | Lighthouse |
| **FCP** | < 1.8s | Lighthouse |
| **Avatar 載入** | < 500ms (單張) | Network Tab |
| **列表載入** | < 2s (20 個) | Performance API |
| **CLS** | < 0.1 | Lighthouse |
| **Bundle Size** | 增加 < 50KB | webpack-bundle-analyzer |

### 優化策略

1. **Lazy Loading**
   - 減少初始載入 60%
   - viewport 外的 Avatar 延遲載入

2. **圖片壓縮**
   - 自動壓縮到 400×400
   - 檔案大小減少 70%

3. **React Query Cache**
   - staleTime: 5 分鐘
   - cacheTime: 10 分鐘
   - 減少 API 請求 80%

4. **Skeleton Loading**
   - 感知載入時間減少 30%
   - 視覺持續性更佳

---

## 🧪 使用者測試計畫

### 測試任務

1. **上傳頭像** (5 分鐘)
   - 找到上傳按鈕
   - 選擇並上傳圖片
   - 確認上傳成功

2. **瀏覽列表** (3 分鐘)
   - 瀏覽 20 個業務員
   - 觀察載入速度
   - 檢查顯示品質

3. **跨頁面驗證** (2 分鐘)
   - 上傳頭像後
   - 前往搜尋頁面
   - 確認頭像已更新

### 成功標準

- 任務完成率 > 95%
- 平均完成時間 < 10 分鐘
- 使用者滿意度 > 4.5/5
- 錯誤率 < 5%

---

## 📋 設計檢核清單

### 視覺設計

- [ ] Avatar 尺寸系統定義完整 (6 種)
- [ ] Fallback 設計清晰美觀
- [ ] 色彩對比符合 WCAG AA
- [ ] 陰影層次合理
- [ ] 圓角統一 (rounded-full)

### 互動設計

- [ ] Hover 效果流暢
- [ ] 點擊回饋明確
- [ ] 上傳流程直觀
- [ ] 錯誤提示友善
- [ ] 成功回饋清楚

### 響應式設計

- [ ] Mobile 佈局優化
- [ ] Tablet 佈局優化
- [ ] Desktop 佈局優化
- [ ] 觸控區域足夠大 (≥ 44px)
- [ ] 跨裝置一致性

### 效能優化

- [ ] Lazy Loading 實作
- [ ] 圖片壓縮自動化
- [ ] Skeleton Loading 流暢
- [ ] React Query Cache 配置
- [ ] Bundle Size 控制

### 無障礙性

- [ ] ARIA 屬性完整
- [ ] 鍵盤導航支援
- [ ] Screen Reader 友善
- [ ] Focus 狀態清楚
- [ ] 色彩對比達標

---

**版本**: 1.0
**最後更新**: 2026-01-21
**設計師**: Product Design Team
**審核者**: Frontend Team Lead
