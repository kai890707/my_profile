# 實作指南：首頁視覺優化

**變更 ID**: 20260121-optimize-homepage
**類型**: Frontend Enhancement (純視覺優化)
**預估時間**: 1.5-2 小時
**日期**: 2026-01-21

---

## 📋 目錄

- [1. 實作總覽](#1-實作總覽)
- [2. 實作順序](#2-實作順序)
- [3. 詳細步驟](#3-詳細步驟)
- [4. 測試驗證](#4-測試驗證)
- [5. 疑難排解](#5-疑難排解)

---

## 1. 實作總覽

### 1.1 變更範圍

**修改的檔案** (2 個):
1. ✅ `frontend/app/globals.css` - 新增動畫系統 (~80 行)
2. ✅ `frontend/app/page.tsx` - 首頁視覺優化 (~50 處 className 變更)

**不修改的檔案**:
- ❌ UI 組件 (Button, Input, Card)
- ❌ Feature 組件 (SalespersonCard)
- ❌ Layout 組件 (Header, Footer)
- ❌ Hooks (useAuth, useSearch)
- ❌ Tailwind 配置

### 1.2 技術考量

**效能要求**:
- LCP (Largest Contentful Paint) < 2.5s
- FCP (First Contentful Paint) < 1.8s
- CLS (Cumulative Layout Shift) < 0.1
- Bundle Size 增加 < 2KB

**瀏覽器兼容性**:
- Chrome >= 90
- Firefox >= 88
- Safari >= 14
- Edge >= 90

**可訪問性**:
- 支援 `prefers-reduced-motion`
- 色彩對比度 >= 4.5:1
- 鍵盤導航正常
- Screen Reader 友善

---

## 2. 實作順序

### Phase 1: 設置動畫系統 (15 分鐘)

**目標**: 在 `globals.css` 定義動畫

**檔案**: `frontend/app/globals.css`

**步驟**:
1. 打開 `globals.css`
2. 在檔案**最後**新增動畫定義
3. 儲存並測試

**驗證**:
```bash
npm run dev
# 檢查 console 無 CSS 錯誤
```

---

### Phase 2: Hero Section 優化 (20 分鐘)

**目標**: 漸層升級 + 搜尋列優化 + 進場動畫

**檔案**: `frontend/app/page.tsx`

**步驟**:
1. Section 容器 → 漸層 3 色階
2. 背景裝飾球 → 大小調整
3. 標題 → 新增動畫
4. 標題漸層 → 色彩調整
5. 描述 → 新增動畫 + 延遲
6. 搜尋列 → 新增動畫 + 延遲
7. 搜尋列佈局 → 響應式方向
8. Input → 高度 + 陰影
9. Button → 高度 + 響應式寬度

**驗證**:
- Hero Section 顯示正常
- 漸層背景更豐富
- 搜尋列更突出
- 進場動畫流暢 (標題 → 描述 → 搜尋列)

---

### Phase 3: Features Section 優化 (20 分鐘)

**目標**: 卡片立體感 + Hover 動畫 + 圖示優化

**檔案**: `frontend/app/page.tsx`

**步驟**:
1. Section 容器 → 響應式 padding
2. 網格佈局 → 明確 Mobile + 響應式間距
3. Card 組件 → 邊框 + 陰影 + Hover 效果 + 動畫
4. 圖示容器 → 大小 + 圓角 + 陰影
5. 圖示 → 大小調整

**驗證**:
- 卡片有立體感 (邊框 + 陰影)
- Hover 浮起流暢
- 卡片交錯淡入 (0ms, 100ms, 200ms)
- 圖示更大更清晰

---

### Phase 4: Popular Salespersons 優化 (15 分鐘)

**目標**: 響應式佈局 + 卡片進場動畫

**檔案**: `frontend/app/page.tsx`

**步驟**:
1. Section 容器 → 響應式 padding
2. 網格佈局 → 明確 Mobile + 響應式間距
3. 卡片 Wrapper → 新增 div + 動畫

**驗證**:
- Mobile: 1 欄佈局
- Tablet: 2 欄佈局
- Desktop: 3 欄佈局
- 卡片交錯淡入 (50ms 間隔)

---

### Phase 5: CTA Section 優化 (20 分鐘)

**目標**: 漸層升級 + 背景裝飾 + 按鈕優化

**檔案**: `frontend/app/page.tsx`

**步驟**:
1. Section 容器 → 定位 + 漸層 3 色階 + 響應式 padding
2. 背景裝飾 → 新增左上、右下裝飾球
3. Container → 新增定位
4. 按鈕容器 → 響應式間距
5. 主要按鈕 → 高度 + Hover 效果
6. 次要按鈕 → 高度 + Hover 位移

**驗證**:
- 漸層背景更豐富
- 背景裝飾顯示正常 (不遮擋文字)
- 按鈕更大更突出
- Hover 效果流暢 (亮度提升 + 浮起)

---

### Phase 6: 測試與調整 (20 分鐘)

**目標**: 全面測試並調整細節

**測試項目**:
1. 視覺檢查 (4 個 Section)
2. 響應式測試 (3 個斷點)
3. 動畫測試 (進場 + 互動)
4. 效能測試 (Lighthouse)
5. 可訪問性測試

---

## 3. 詳細步驟

### Step 1: 設置動畫系統

#### 1.1 打開 `globals.css`

**檔案路徑**: `frontend/app/globals.css`

#### 1.2 新增動畫定義

**位置**: 在檔案**最後**新增 (現有 Tailwind 指令之後)

**內容**:

```css
/* ========================================
   自定義動畫系統 - 首頁優化
   ======================================== */

/* 1. 淡入向上滑入 (最常用) */
@keyframes fade-in-up {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 2. 淡入向右滑入 (可選) */
@keyframes fade-in-right {
  0% {
    opacity: 0;
    transform: translateX(-20px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

/* 3. 縮放淡入 (可選) */
@keyframes scale-in {
  0% {
    opacity: 0;
    transform: scale(0.95);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* ========================================
   動畫類別
   ======================================== */

.animate-fade-in-up {
  animation: fade-in-up 0.3s ease-out forwards;
}

.animate-fade-in-right {
  animation: fade-in-right 0.3s ease-out forwards;
}

.animate-scale-in {
  animation: scale-in 0.3s ease-out forwards;
}

/* ========================================
   動畫延遲工具類別 (可選)
   ======================================== */

.animation-delay-100 {
  animation-delay: 100ms;
}

.animation-delay-200 {
  animation-delay: 200ms;
}

.animation-delay-300 {
  animation-delay: 300ms;
}

/* ========================================
   尊重使用者偏好設定
   ======================================== */

@media (prefers-reduced-motion: reduce) {
  .animate-fade-in-up,
  .animate-fade-in-right,
  .animate-scale-in {
    animation: none;
    opacity: 1;
    transform: none;
  }
}
```

#### 1.3 驗證

```bash
# 啟動開發伺服器
npm run dev

# 訪問首頁
# http://localhost:3001

# 檢查 Console
# 確認無 CSS 錯誤
```

---

### Step 2: Hero Section 優化

#### 2.1 找到 Hero Section

**檔案**: `frontend/app/page.tsx`

**行數**: 約第 68-111 行

**識別標記**:
```tsx
{/* Hero Section */}
<section className="relative bg-gradient-to-br from-primary-50 via-white to-secondary-50 ...">
```

#### 2.2 修改 Section 容器

**Before** (第 68 行):
```tsx
<section className="relative bg-gradient-to-br from-primary-50 via-white to-secondary-50 py-20 lg:py-32 overflow-hidden">
```

**After**:
```tsx
<section className="
  relative
  bg-gradient-to-br from-primary-50 via-primary-25 to-secondary-50
  py-16 md:py-20 lg:py-28
  overflow-hidden
">
```

**變更**:
- ✏️ `via-white` → `via-primary-25`
- ✏️ `py-20 lg:py-32` → `py-16 md:py-20 lg:py-28`

---

#### 2.3 修改背景裝飾球

**Before** (第 71 行):
```tsx
<div className="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-primary-200/20 blur-3xl" />
```

**After**:
```tsx
<div className="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-primary-200/20 blur-3xl" />
```

**變更**:
- ✏️ `h-80 w-80` → `h-96 w-96`

**Before** (第 72 行):
```tsx
<div className="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-secondary-200/20 blur-3xl" />
```

**After**:
```tsx
<div className="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-secondary-200/20 blur-3xl" />
```

**變更**:
- ✏️ `h-80 w-80` → `h-96 w-96`

---

#### 2.4 修改標題

**Before** (第 77 行):
```tsx
<h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 mb-6">
```

**After**:
```tsx
<h1 className="
  text-4xl sm:text-5xl lg:text-6xl
  font-bold
  text-slate-900
  mb-6
  animate-fade-in-up
">
```

**變更**:
- ➕ `animate-fade-in-up`

---

#### 2.5 修改標題漸層 Span

**Before** (第 79 行):
```tsx
<span className="bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
```

**After**:
```tsx
<span className="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
```

**變更**:
- ✏️ `from-primary-600 to-secondary-600` → `from-primary-500 to-secondary-500`

---

#### 2.6 修改描述文字

**Before** (第 83 行):
```tsx
<p className="text-xl text-slate-600 mb-12 max-w-2xl mx-auto">
```

**After**:
```tsx
<p className="
  text-xl
  text-slate-600
  mb-12
  max-w-2xl mx-auto
  animate-fade-in-up
  animation-delay-100
">
```

**變更**:
- ➕ `animate-fade-in-up`
- ➕ `animation-delay-100`

---

#### 2.7 修改搜尋列容器

**Before** (第 88 行):
```tsx
<form onSubmit={handleSearch} className="max-w-2xl mx-auto">
```

**After**:
```tsx
<form
  onSubmit={handleSearch}
  className="
    max-w-2xl mx-auto
    animate-fade-in-up
    animation-delay-200
  "
>
```

**變更**:
- ➕ `animate-fade-in-up`
- ➕ `animation-delay-200`

---

#### 2.8 修改搜尋列內部佈局

**Before** (第 89 行):
```tsx
<div className="flex gap-2">
```

**After**:
```tsx
<div className="flex flex-col sm:flex-row gap-3">
```

**變更**:
- ✏️ `flex` → `flex flex-col sm:flex-row`
- ✏️ `gap-2` → `gap-3`

---

#### 2.9 修改 Input 組件

**Before** (第 90-96 行):
```tsx
<Input
  size="lg"
  placeholder="搜尋業務員、公司、產業..."
  value={keyword}
  onChange={(e) => setKeyword(e.target.value)}
  icon={<Search className="h-5 w-5" />}
  className="text-lg"
/>
```

**After**:
```tsx
<Input
  size="lg"
  placeholder="搜尋業務員、公司、產業..."
  value={keyword}
  onChange={(e) => setKeyword(e.target.value)}
  icon={<Search className="h-5 w-5" />}
  className="
    text-lg
    h-14
    shadow-lg
    flex-1
  "
/>
```

**變更**:
- ➕ `h-14`
- ➕ `shadow-lg`
- ➕ `flex-1`

---

#### 2.10 修改 Button 組件

**Before** (第 98 行):
```tsx
<Button type="submit" size="lg" className="px-8">
```

**After**:
```tsx
<Button
  type="submit"
  size="lg"
  className="
    px-10
    h-14
    w-full sm:w-auto
  "
>
```

**變更**:
- ✏️ `px-8` → `px-10`
- ➕ `h-14`
- ➕ `w-full sm:w-auto`

---

### Step 3: Features Section 優化

#### 3.1 找到 Features Section

**檔案**: `frontend/app/page.tsx`

**行數**: 約第 113-143 行

**識別標記**:
```tsx
{/* Features Section */}
<section className="py-20 bg-white">
```

#### 3.2 修改 Section 容器

**Before** (第 114 行):
```tsx
<section className="py-20 bg-white">
```

**After**:
```tsx
<section className="py-16 md:py-20 bg-white">
```

**變更**:
- ✏️ `py-20` → `py-16 md:py-20`

---

#### 3.3 修改網格佈局

**Before** (第 125 行):
```tsx
<div className="grid md:grid-cols-3 gap-8">
```

**After**:
```tsx
<div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
```

**變更**:
- ➕ `grid-cols-1`
- ✏️ `gap-8` → `gap-6 md:gap-8`

---

#### 3.4 修改 Card 組件

**Before** (第 127 行):
```tsx
<Card key={index} hover className="text-center">
```

**After**:
```tsx
<Card
  key={index}
  hover
  className="
    text-center
    border border-slate-100
    shadow-md
    hover:shadow-xl
    hover:-translate-y-2
    transition-all duration-300
    animate-fade-in-up
  "
  style={{ animationDelay: `${index * 100}ms` }}
>
```

**變更**:
- ➕ `border border-slate-100`
- ➕ `shadow-md`
- ➕ `hover:shadow-xl`
- ➕ `hover:-translate-y-2`
- ➕ `transition-all duration-300`
- ➕ `animate-fade-in-up`
- ➕ `style={{ animationDelay: ... }}`

---

#### 3.5 修改圖示容器

**Before** (第 129 行):
```tsx
<div className="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-primary-500 to-secondary-500 mb-6">
```

**After**:
```tsx
<div className="
  inline-flex items-center justify-center
  h-20 w-20
  rounded-3xl
  bg-gradient-to-br from-primary-500 to-secondary-500
  shadow-lg
  mb-6
  transition-transform duration-300
  hover:scale-110
  hover:rotate-6
">
```

**變更**:
- ✏️ `h-16 w-16` → `h-20 w-20`
- ✏️ `rounded-2xl` → `rounded-3xl`
- ➕ `shadow-lg`
- ➕ `transition-transform duration-300`
- ➕ `hover:scale-110`
- ➕ `hover:rotate-6`

**注意**: `hover:scale-110` 和 `hover:rotate-6` 是可選的微互動效果。

---

#### 3.6 修改圖示大小

**Before** (第 130 行):
```tsx
<feature.icon className="h-8 w-8 text-white" />
```

**After**:
```tsx
<feature.icon className="h-10 w-10 text-white" />
```

**變更**:
- ✏️ `h-8 w-8` → `h-10 w-10`

---

### Step 4: Popular Salespersons 優化

#### 4.1 找到 Popular Salespersons Section

**檔案**: `frontend/app/page.tsx`

**行數**: 約第 145-189 行

**識別標記**:
```tsx
{/* Popular Salespersons Section */}
<section className="py-20 bg-slate-50">
```

#### 4.2 修改 Section 容器

**Before** (第 146 行):
```tsx
<section className="py-20 bg-slate-50">
```

**After**:
```tsx
<section className="py-16 md:py-20 bg-slate-50">
```

**變更**:
- ✏️ `py-20` → `py-16 md:py-20`

---

#### 4.3 修改網格佈局

**Before** (第 166 行):
```tsx
<div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
```

**After**:
```tsx
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
```

**變更**:
- ➕ `grid-cols-1`
- ✏️ `md:grid-cols-2` → `sm:grid-cols-2`
- ✏️ `gap-6` → `gap-4 md:gap-6 lg:gap-8`

---

#### 4.4 修改卡片 Wrapper

**Before** (第 172-177 行):
```tsx
<div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
  {popularSalespersons.data.map((salesperson) => (
    <SalespersonCard
      key={salesperson.id}
      salesperson={salesperson}
    />
  ))}
</div>
```

**After**:
```tsx
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
  {popularSalespersons.data.map((salesperson, index) => (
    <div
      key={salesperson.id}
      className="animate-fade-in-up"
      style={{ animationDelay: `${index * 50}ms` }}
    >
      <SalespersonCard salesperson={salesperson} />
    </div>
  ))}
</div>
```

**變更**:
- ➕ 新增 wrapper `<div>`
- ➕ `className="animate-fade-in-up"`
- ➕ `style={{ animationDelay: ... }}`
- ➕ `index` 參數到 map

---

### Step 5: CTA Section 優化

#### 5.1 找到 CTA Section

**檔案**: `frontend/app/page.tsx`

**行數**: 約第 191-211 行

**識別標記**:
```tsx
{/* CTA Section */}
<section className="py-20 bg-gradient-to-r from-primary-600 to-secondary-600">
```

#### 5.2 修改 Section 容器

**Before** (第 192 行):
```tsx
<section className="py-20 bg-gradient-to-r from-primary-600 to-secondary-600">
```

**After**:
```tsx
<section className="
  relative
  py-16 md:py-20
  bg-gradient-to-r from-primary-500 via-primary-600 to-secondary-500
  overflow-hidden
">
```

**變更**:
- ➕ `relative`
- ✏️ `py-20` → `py-16 md:py-20`
- ✏️ 漸層: `from-primary-600 to-secondary-600` → `from-primary-500 via-primary-600 to-secondary-500`
- ➕ `overflow-hidden`

---

#### 5.3 新增背景裝飾

**位置**: 在 Section 內部、Container 之前

**新增內容**:
```tsx
{/* 背景裝飾 */}
<div className="absolute inset-0 opacity-30">
  <div className="absolute top-0 left-0 w-96 h-96 bg-white/20 rounded-full blur-3xl" />
  <div className="absolute bottom-0 right-0 w-96 h-96 bg-white/20 rounded-full blur-3xl" />
</div>
```

**插入位置**:
```tsx
<section className="...">
  {/* 新增這裡 👆 */}

  <div className="container mx-auto px-4 sm:px-6 lg:px-8">
    ...
  </div>
</section>
```

---

#### 5.4 修改 Container

**Before** (第 193 行):
```tsx
<div className="container mx-auto px-4 sm:px-6 lg:px-8">
```

**After**:
```tsx
<div className="container mx-auto px-4 sm:px-6 lg:px-8 relative">
```

**變更**:
- ➕ `relative`

---

#### 5.5 修改按鈕容器

**Before** (第 201 行):
```tsx
<div className="flex flex-col sm:flex-row gap-4 justify-center">
```

**After**:
```tsx
<div className="flex flex-col sm:flex-row gap-4 md:gap-6 justify-center">
```

**變更**:
- ✏️ `gap-4` → `gap-4 md:gap-6`

---

#### 5.6 修改主要按鈕

**Before** (第 202-204 行):
```tsx
<Button asChild size="lg" variant="secondary">
  <Link href="/register">免費註冊</Link>
</Button>
```

**After**:
```tsx
<Button
  asChild
  size="lg"
  variant="secondary"
  className="
    h-14
    px-10
    text-lg
    shadow-xl
    hover:brightness-110
    hover:-translate-y-1
    hover:shadow-2xl
    transition-all duration-300
  "
>
  <Link href="/register">免費註冊</Link>
</Button>
```

**變更**:
- ➕ `h-14`
- ➕ `px-10`
- ➕ `text-lg`
- ➕ `shadow-xl`
- ➕ `hover:brightness-110`
- ➕ `hover:-translate-y-1`
- ➕ `hover:shadow-2xl`
- ➕ `transition-all duration-300`

---

#### 5.7 修改次要按鈕

**Before** (第 205-207 行):
```tsx
<Button asChild size="lg" variant="outline" className="bg-white/10 border-white text-white hover:bg-white/20">
  <Link href="/search">開始搜尋</Link>
</Button>
```

**After**:
```tsx
<Button
  asChild
  size="lg"
  variant="outline"
  className="
    h-14
    px-10
    text-lg
    bg-white/10
    border-white
    text-white
    hover:bg-white/20
    hover:-translate-y-1
    transition-all duration-300
  "
>
  <Link href="/search">開始搜尋</Link>
</Button>
```

**變更**:
- ➕ `h-14`
- ➕ `px-10`
- ➕ `text-lg`
- ➕ `hover:-translate-y-1`
- ➕ `transition-all duration-300`

---

## 4. 測試驗證

### 4.1 視覺測試

#### 啟動開發伺服器

```bash
cd frontend
npm run dev
```

#### 訪問首頁

```
http://localhost:3001
```

#### 檢查項目

**Hero Section**:
- [ ] 漸層背景更豐富 (3 色階)
- [ ] 背景裝飾球顯示正常
- [ ] 標題、描述、搜尋列依序淡入
- [ ] 搜尋列更大更突出 (h-14)
- [ ] 搜尋列有陰影

**Features Section**:
- [ ] 卡片有邊框
- [ ] 卡片有陰影
- [ ] Hover 卡片時浮起 + 陰影增強
- [ ] 卡片交錯淡入 (0ms, 100ms, 200ms)
- [ ] 圖示更大 (80px 容器, 40px 圖示)
- [ ] 圖示容器圓角更大 (24px)

**Popular Salespersons**:
- [ ] 卡片交錯淡入 (50ms 間隔)
- [ ] 佈局響應式正確 (1/2/3 欄)

**CTA Section**:
- [ ] 漸層背景更豐富 (3 色階)
- [ ] 背景裝飾球顯示正常
- [ ] 按鈕更大 (h-14)
- [ ] Hover 按鈕時亮度提升 + 浮起

---

### 4.2 響應式測試

#### 測試方法

```bash
# Chrome DevTools
1. F12 打開 DevTools
2. Ctrl+Shift+M (或 Cmd+Shift+M on Mac) 切換 Device Toolbar
3. 選擇裝置或自定義尺寸
```

#### 測試斷點

**Mobile (375px - iPhone SE)**:

Hero Section:
- [ ] Section padding 64px (`py-16`)
- [ ] 標題字級 36px (`text-4xl`)
- [ ] 搜尋列上下排列 (`flex-col`)
- [ ] 搜尋按鈕全寬 (`w-full`)

Features Section:
- [ ] 單欄佈局 (`grid-cols-1`)
- [ ] 卡片間距 24px (`gap-6`)

Salespersons:
- [ ] 單欄佈局 (`grid-cols-1`)
- [ ] 卡片間距 16px (`gap-4`)

CTA:
- [ ] 按鈕上下排列 (`flex-col`)
- [ ] 按鈕全寬

---

**Tablet (768px - iPad)**:

Hero Section:
- [ ] Section padding 80px (`md:py-20`)
- [ ] 標題字級 48px (`sm:text-5xl`)
- [ ] 搜尋列左右排列 (`sm:flex-row`)

Features Section:
- [ ] 3 欄佈局 (`md:grid-cols-3`)
- [ ] 卡片間距 32px (`md:gap-8`)

Salespersons:
- [ ] 2 欄佈局 (`sm:grid-cols-2`)
- [ ] 卡片間距 24px (`md:gap-6`)

CTA:
- [ ] 按鈕左右排列 (`sm:flex-row`)
- [ ] 按鈕固定寬度

---

**Desktop (1280px)**:

Hero Section:
- [ ] Section padding 112px (`lg:py-28`)
- [ ] 標題字級 60px (`lg:text-6xl`)

Salespersons:
- [ ] 3 欄佈局 (`lg:grid-cols-3`)
- [ ] 卡片間距 32px (`lg:gap-8`)

---

### 4.3 動畫測試

#### 進場動畫測試

**測試方法**:
```bash
1. 訪問首頁
2. 觀察元素淡入順序
3. 刷新頁面重新測試
```

**檢查項目**:
- [ ] Hero 標題先淡入
- [ ] Hero 描述接著淡入 (100ms 後)
- [ ] Hero 搜尋列最後淡入 (200ms 後)
- [ ] Features 卡片交錯淡入 (0ms, 100ms, 200ms)
- [ ] Salesperson 卡片交錯淡入 (50ms 間隔)

#### 互動動畫測試

**Features 卡片 Hover**:
- [ ] Hover 時向上浮起 (8px)
- [ ] Hover 時陰影增強 (shadow-md → shadow-xl)
- [ ] 動畫流暢 (duration-300)
- [ ] 滑鼠移開後復位

**CTA 按鈕 Hover**:
- [ ] Hover 時亮度提升 (brightness-110)
- [ ] Hover 時向上浮起 (4px)
- [ ] Hover 時陰影增強 (shadow-xl → shadow-2xl)
- [ ] 動畫流暢 (duration-300)

#### 動畫偏好測試 (可選)

**啟用 prefers-reduced-motion**:

```bash
# Chrome DevTools
1. F12 → More tools → Rendering
2. 勾選 "Emulate CSS media feature prefers-reduced-motion: reduce"
```

**檢查項目**:
- [ ] 所有進場動畫停用
- [ ] 元素仍然顯示 (opacity: 1)
- [ ] 無佈局位移
- [ ] Hover 動畫保持 (因為是使用者觸發)

---

### 4.4 效能測試

#### Lighthouse 測試

**測試方法**:
```bash
# Chrome DevTools
1. F12 → Lighthouse 面板
2. 選擇 "Mobile" 或 "Desktop"
3. 取消勾選不需要的類別 (只測 Performance)
4. 點擊 "Analyze page load"
```

**目標指標**:
- [ ] LCP (Largest Contentful Paint) < 2.5s
- [ ] FCP (First Contentful Paint) < 1.8s
- [ ] CLS (Cumulative Layout Shift) < 0.1
- [ ] TTI (Time to Interactive) < 3.8s
- [ ] Performance Score >= 90

**如果未達標**:
- 檢查圖片是否 Lazy Loading
- 檢查動畫是否過多
- 檢查 Bundle Size

#### Bundle Size 檢查

**測試方法**:
```bash
cd frontend

# 構建生產版本
npm run build

# 檢查 Bundle Size
# 查看終端輸出的 "First Load JS"
```

**目標**:
- [ ] Initial Bundle < 200KB (gzip)
- [ ] Page Bundle < 50KB (gzip)

---

### 4.5 可訪問性測試

#### 色彩對比度

**工具**: [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

**檢查項目**:
- [ ] Hero 標題 (text-slate-900 on bg-white) >= 4.5:1
- [ ] Hero 描述 (text-slate-600 on bg-white) >= 4.5:1
- [ ] CTA 文字 (text-white on bg-primary-600) >= 4.5:1

#### 鍵盤導航

**測試方法**:
```bash
1. 訪問首頁
2. 只使用 Tab 鍵導航
3. 不使用滑鼠
```

**檢查項目**:
- [ ] 所有按鈕可用 Tab 訪問
- [ ] Tab 順序邏輯正確 (從上到下)
- [ ] Focus Ring 清晰可見
- [ ] Enter/Space 可觸發按鈕

#### Screen Reader 測試 (可選)

**工具**:
- Mac: VoiceOver (Cmd+F5)
- Windows: NVDA (免費)

**檢查項目**:
- [ ] Section 有正確的語義標籤 (header, main, section, footer)
- [ ] 標題有正確的層級 (H1, H2)
- [ ] 裝飾性元素被隱藏 (aria-hidden="true")

---

### 4.6 瀏覽器測試

#### 測試瀏覽器

**必測**:
- [ ] Chrome 最新版本
- [ ] Firefox 最新版本
- [ ] Safari 最新版本 (Mac/iOS)
- [ ] Edge 最新版本

**檢查項目**:
- [ ] 視覺顯示正常
- [ ] 動畫流暢
- [ ] Hover 效果正常
- [ ] 無 console 錯誤

---

## 5. 疑難排解

### 5.1 動畫相關問題

#### Q1: 動畫不顯示

**可能原因**:
1. globals.css 未正確載入
2. 動畫類別名稱拼寫錯誤
3. 元素被其他 CSS 覆蓋

**解決方法**:
```bash
# 1. 檢查 globals.css 是否載入
# 在 app/layout.tsx 中確認
import './globals.css'

# 2. 檢查 Console 是否有 CSS 錯誤
# F12 → Console

# 3. 檢查元素的 computed styles
# F12 → Elements → Computed
# 查找 animation 屬性
```

---

#### Q2: 交錯動畫延遲不正確

**可能原因**:
1. 未使用 inline style
2. index 參數未傳遞
3. animationDelay 計算錯誤

**解決方法**:
```tsx
// ❌ 錯誤: 使用 Tailwind class (無法動態)
<div className="animate-fade-in-up delay-100">

// ✅ 正確: 使用 inline style
<div
  className="animate-fade-in-up"
  style={{ animationDelay: `${index * 100}ms` }}
>

// 確認 map 有 index 參數
{items.map((item, index) => ...)}
```

---

#### Q3: Hover 動畫卡頓

**可能原因**:
1. 使用了觸發 Layout 的屬性 (top, left, width, height)
2. 未添加 transition
3. 瀏覽器效能問題

**解決方法**:
```tsx
// ❌ 觸發 Layout (慢)
<div className="hover:top-[-8px]">

// ✅ 使用 Transform (快, GPU 加速)
<div className="hover:-translate-y-2 transition-all duration-300">

// 確保添加 transition
className="transition-all duration-300"
```

---

### 5.2 響應式問題

#### Q4: Mobile 佈局錯亂

**可能原因**:
1. 未使用 Mobile First
2. 斷點使用錯誤
3. 缺少必要的響應式類別

**解決方法**:
```tsx
// ❌ Desktop First (錯誤)
<div className="w-1/2 md:w-full">

// ✅ Mobile First (正確)
<div className="w-full md:w-1/2">

// 明確定義 Mobile 樣式
<div className="grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
```

---

#### Q5: 搜尋列 Mobile 顯示異常

**可能原因**:
1. flex-col 未生效
2. 按鈕寬度未設為 w-full
3. gap 過大導致溢出

**解決方法**:
```tsx
// 確認結構
<div className="flex flex-col sm:flex-row gap-3">
  <Input className="flex-1" />
  <Button className="w-full sm:w-auto">搜尋</Button>
</div>

// Mobile: 上下排列, 按鈕全寬
// Desktop: 左右排列, 按鈕固定寬度
```

---

### 5.3 效能問題

#### Q6: LCP > 2.5s

**可能原因**:
1. Hero Section 圖片未優化
2. 字體載入阻塞
3. JS Bundle 過大

**解決方法**:
```bash
# 1. 檢查是否有大圖片
# 首頁沒有圖片，略過

# 2. 檢查字體載入
# 確認使用 font-display: swap

# 3. 檢查 Bundle Size
npm run build
# 查看 First Load JS

# 4. 使用 Lighthouse 找出瓶頸
# F12 → Lighthouse → Performance
```

---

#### Q7: CLS > 0.1

**可能原因**:
1. 動畫使用 margin/top 而非 transform
2. 圖片未設定尺寸
3. Loading Skeleton 尺寸不一致

**解決方法**:
```tsx
// ✅ 使用 transform (無佈局位移)
<div className="hover:-translate-y-2">

// ❌ 使用 margin (有佈局位移)
<div className="hover:mb-2">

// 確認 SalespersonCard 有固定尺寸
// 已由組件處理，無需修改
```

---

### 5.4 可訪問性問題

#### Q8: Focus Ring 不清晰

**可能原因**:
1. 使用 outline-none 移除了預設 Focus Ring
2. 自定義 Focus Ring 對比度不足

**解決方法**:
```tsx
// Button 和 Input 組件已內建 Focus Ring
// 如需自定義:
<div className="
  focus:ring-2
  focus:ring-primary-500
  focus:ring-offset-2
  focus:outline-none
">
```

---

#### Q9: prefers-reduced-motion 不生效

**可能原因**:
1. CSS 媒體查詢順序錯誤
2. 動畫類別覆蓋了媒體查詢

**解決方法**:
```css
/* 確認 @media 在最後 */
@media (prefers-reduced-motion: reduce) {
  .animate-fade-in-up {
    animation: none;
    opacity: 1;
    transform: none;
  }
}

/* 測試方法 */
/* Chrome DevTools → Rendering → Emulate prefers-reduced-motion */
```

---

### 5.5 TypeScript 錯誤

#### Q10: style 屬性類型錯誤

**錯誤訊息**:
```
Type '{ animationDelay: string }' is not assignable to type 'CSSProperties'
```

**解決方法**:
```tsx
// ✅ 正確: 使用 React.CSSProperties
<div
  style={{ animationDelay: `${index * 100}ms` } as React.CSSProperties}
>

// 或者不需要 type assertion (應該可以直接使用)
<div
  style={{ animationDelay: `${index * 100}ms` }}
>
```

---

## 6. 最終檢查清單

### 6.1 程式碼檢查

- [ ] globals.css 新增動畫系統 (~80 行)
- [ ] page.tsx Hero Section 優化 (~9 處變更)
- [ ] page.tsx Features Section 優化 (~6 處變更)
- [ ] page.tsx Salespersons Section 優化 (~3 處變更)
- [ ] page.tsx CTA Section 優化 (~7 處變更)
- [ ] 無 TypeScript 錯誤
- [ ] 無 ESLint 警告

### 6.2 視覺檢查

- [ ] Hero Section 漸層更豐富
- [ ] 搜尋列更突出
- [ ] Features 卡片有立體感
- [ ] 卡片 Hover 浮起流暢
- [ ] Salesperson 佈局正確
- [ ] CTA 按鈕更大更明顯

### 6.3 響應式檢查

- [ ] Mobile (375px) 佈局正確
- [ ] Tablet (768px) 佈局正確
- [ ] Desktop (1280px) 佈局正確
- [ ] 所有斷點間距合理

### 6.4 動畫檢查

- [ ] 進場動畫流暢
- [ ] 交錯動畫正確
- [ ] Hover 動畫流暢
- [ ] 支援 prefers-reduced-motion

### 6.5 效能檢查

- [ ] LCP < 2.5s
- [ ] FCP < 1.8s
- [ ] CLS < 0.1
- [ ] Performance Score >= 90

### 6.6 可訪問性檢查

- [ ] 色彩對比度 >= 4.5:1
- [ ] 鍵盤導航正常
- [ ] Focus Ring 清晰可見
- [ ] Screen Reader 友善

### 6.7 瀏覽器檢查

- [ ] Chrome 正常
- [ ] Firefox 正常
- [ ] Safari 正常
- [ ] Edge 正常

---

## 7. 完成後步驟

### 7.1 提交變更

```bash
cd frontend

# 檢查變更
git status
git diff

# 提交
git add app/globals.css app/page.tsx
git commit -m "feat: optimize homepage visual design

- Add custom animation system (fade-in-up, scale-in)
- Enhance Hero Section gradient and search bar
- Add card shadows and hover effects to Features Section
- Improve responsive layout for Salespersons Section
- Upgrade CTA Section with background decorations
- Improve button sizes and hover animations
- Add staggered entrance animations
- Support prefers-reduced-motion

Performance: LCP < 2.5s, CLS < 0.1
Accessibility: WCAG AA compliant

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```

### 7.2 更新文檔

```bash
# 如果需要更新文檔
# 1. 更新 frontend/CHANGELOG.md
# 2. 更新 frontend/docs/design-system.md (如有新增設計 token)
```

### 7.3 通知團隊

```bash
# 通知團隊已完成首頁視覺優化
# 提供 Before/After 截圖
# 分享 Lighthouse 效能報告
```

---

**設計師**: Claude Sonnet 4.5
**實作者**: Development Team
**狀態**: Draft → Ready for Implementation
**版本**: 1.0
**最後更新**: 2026-01-21
