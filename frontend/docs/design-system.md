# YAMU 設計系統規範

**版本**: 1.0
**最後更新**: 2026-01-20
**框架**: Next.js 16 + Tailwind CSS 3.4.1

---

## 📋 目錄

- [設計原則](#設計原則)
- [色彩系統](#色彩系統)
- [字體系統](#字體系統)
- [間距系統](#間距系統)
- [圓角系統](#圓角系統)
- [陰影系統](#陰影系統)
- [動畫系統](#動畫系統)
- [響應式設計](#響應式設計)
- [無障礙設計](#無障礙設計)
- [組件規範](#組件規範)

---

## 🎨 設計原則

### 1. 簡潔專業 (Clean & Professional)
- 清晰的視覺層次
- 充足的留白空間
- 精準的對齊
- 一致的設計語言

### 2. 現代活潑 (Modern & Vibrant)
- 漸層色彩運用
- 流暢的動畫效果
- 圓潤的圓角設計
- 立體的陰影層次

### 3. 使用者優先 (User-Centric)
- 直覺的互動回饋
- 清楚的狀態指示
- 友善的錯誤訊息
- 快速的載入體驗

### 4. 響應式友善 (Responsive-Friendly)
- Mobile First 設計
- 流暢的斷點切換
- 適應性佈局
- 觸控友善

---

## 🎨 色彩系統

### 主色調 (Primary) - Sky Blue
業務員平台的主要品牌色，傳達專業、信任、科技感。

```css
--color-primary-50: #f0f9ff;   /* 極淺藍 - 背景 */
--color-primary-100: #e0f2fe;  /* 淺藍 - 輕量背景 */
--color-primary-200: #bae6fd;  /* 淡藍 - Hover 背景 */
--color-primary-300: #7dd3fc;  /* 中淡藍 - 次要元素 */
--color-primary-400: #38bdf8;  /* 中藍 - 互動元素 */
--color-primary-500: #0ea5e9;  /* 主藍 - 主要按鈕、連結 ⭐ */
--color-primary-600: #0284c7;  /* 深藍 - Hover 狀態 */
--color-primary-700: #0369a1;  /* 較深藍 - Active 狀態 */
--color-primary-800: #075985;  /* 深藍 - 深色文字 */
--color-primary-900: #0c4a6e;  /* 極深藍 - 標題文字 */
```

**使用場景**:
- 主要按鈕背景: `primary-500`
- 主要按鈕 Hover: `primary-600`
- 連結顏色: `primary-500`
- Focus Ring: `primary-500`
- 品牌識別: `primary-500`

### 次要色 (Secondary) - Teal
輔助主色調，用於次要操作和強調元素。

```css
--color-secondary-50: #f0fdfa;   /* 極淺青 */
--color-secondary-100: #ccfbf1;  /* 淺青 */
--color-secondary-200: #99f6e4;  /* 淡青 */
--color-secondary-300: #5eead4;  /* 中淡青 */
--color-secondary-400: #2dd4bf;  /* 中青 */
--color-secondary-500: #14b8a6;  /* 主青 - 次要按鈕 ⭐ */
--color-secondary-600: #0d9488;  /* 深青 - Hover */
--color-secondary-700: #0f766e;  /* 較深青 */
--color-secondary-800: #115e59;  /* 深青 */
--color-secondary-900: #134e4a;  /* 極深青 */
```

**使用場景**:
- 次要按鈕背景: `secondary-500`
- 標籤、徽章: `secondary-100` 背景 + `secondary-700` 文字
- 強調元素: `secondary-500`

### 語義色 (Semantic Colors)

#### 成功 (Success) - Green
```css
--color-success-500: #22c55e;  /* 主綠 ⭐ */
--color-success-600: #16a34a;  /* 深綠 - Hover */
--color-success-100: #dcfce7;  /* 淺綠 - 背景 */
--color-success-700: #15803d;  /* 深綠 - 文字 */
```

**使用場景**: 成功訊息、已審核標記、完成狀態

#### 警告 (Warning) - Amber
```css
--color-warning-500: #f59e0b;  /* 主橘 ⭐ */
--color-warning-600: #d97706;  /* 深橘 - Hover */
--color-warning-100: #fef3c7;  /* 淺橘 - 背景 */
--color-warning-700: #b45309;  /* 深橘 - 文字 */
```

**使用場景**: 警告訊息、審核中標記、需要注意的狀態

#### 錯誤 (Error) - Red
```css
--color-error-500: #ef4444;    /* 主紅 ⭐ */
--color-error-600: #dc2626;    /* 深紅 - Hover */
--color-error-100: #fee2e2;    /* 淺紅 - 背景 */
--color-error-700: #b91c1c;    /* 深紅 - 文字 */
```

**使用場景**: 錯誤訊息、已拒絕標記、危險操作

#### 資訊 (Info) - Blue
```css
--color-info-500: #3b82f6;     /* 主藍 ⭐ */
--color-info-600: #2563eb;     /* 深藍 - Hover */
--color-info-100: #dbeafe;     /* 淺藍 - 背景 */
--color-info-700: #1d4ed8;     /* 深藍 - 文字 */
```

**使用場景**: 資訊提示、說明文字、中性通知

### 中性色 (Neutral) - Slate
用於文字、邊框、背景等基礎元素。

```css
--color-slate-50: #f8fafc;     /* 頁面背景 ⭐ */
--color-slate-100: #f1f5f9;    /* 卡片邊框、分隔線 */
--color-slate-200: #e2e8f0;    /* 輕量邊框 */
--color-slate-300: #cbd5e1;    /* 禁用狀態邊框 */
--color-slate-400: #94a3b8;    /* Placeholder 文字 */
--color-slate-500: #64748b;    /* 次要文字 */
--color-slate-600: #475569;    /* 說明文字 */
--color-slate-700: #334155;    /* 標準文字 */
--color-slate-800: #1e293b;    /* 重要文字 */
--color-slate-900: #0f172a;    /* 標題文字 ⭐ */
```

**文字色階建議**:
- H1/H2 標題: `slate-900`
- H3/H4 標題: `slate-800`
- 內文: `slate-700`
- 次要文字: `slate-600`
- 輔助文字: `slate-500`
- Placeholder: `slate-400`

### 色彩對比度要求 (WCAG AA)
- 標準文字 (< 18pt): 對比度 ≥ 4.5:1
- 大字體 (≥ 18pt): 對比度 ≥ 3:1
- 互動元素: 對比度 ≥ 3:1

**推薦組合**:
- ✅ `text-slate-900` on `bg-white` (對比度 19.64:1)
- ✅ `text-slate-700` on `bg-white` (對比度 10.69:1)
- ✅ `text-slate-600` on `bg-white` (對比度 7.74:1)
- ✅ `text-white` on `bg-primary-500` (對比度 4.93:1)

---

## 📝 字體系統

### 字體選擇

#### 英文字體
```css
font-family: ui-sans-serif, system-ui, sans-serif;
```

**順序**: Inter → SF Pro → Helvetica → Arial → 系統預設

#### 中文字體
```css
font-family: "PingFang TC", "Microsoft JhengHei", "Noto Sans TC", sans-serif;
```

**順序**: PingFang TC → 微軟正黑體→ Noto Sans TC → 系統預設

### 字級階層 (Type Scale)

基於 1.25 倍率 (Major Third Scale)

| 用途 | Class | Size | Line Height | Weight | 使用場景 |
|------|-------|------|-------------|--------|----------|
| **H1** | `text-4xl` | 36px (2.25rem) | 40px (1.1) | 700 (Bold) | 頁面主標題 |
| **H2** | `text-3xl` | 30px (1.875rem) | 36px (1.2) | 700 (Bold) | 區塊標題 |
| **H3** | `text-2xl` | 24px (1.5rem) | 32px (1.33) | 600 (Semibold) | 小節標題 |
| **H4** | `text-xl` | 20px (1.25rem) | 28px (1.4) | 600 (Semibold) | 次標題 |
| **H5** | `text-lg` | 18px (1.125rem) | 28px (1.56) | 600 (Semibold) | 強調文字 |
| **Body** | `text-base` | 16px (1rem) | 24px (1.5) | 400 (Regular) | 內文 ⭐ |
| **Small** | `text-sm` | 14px (0.875rem) | 20px (1.43) | 400 (Regular) | 次要文字 |
| **Tiny** | `text-xs` | 12px (0.75rem) | 16px (1.33) | 400 (Regular) | 標籤、提示 |

### 字重 (Font Weight)

| Weight | Value | Class | 使用場景 |
|--------|-------|-------|----------|
| **Bold** | 700 | `font-bold` | H1, H2 標題 |
| **Semibold** | 600 | `font-semibold` | H3, H4, H5 標題、按鈕 |
| **Medium** | 500 | `font-medium` | 強調文字、標籤 |
| **Regular** | 400 | `font-normal` | 內文 ⭐ |
| **Light** | 300 | `font-light` | 輔助文字 (少用) |

### 行高 (Line Height)

| 類型 | Line Height | 使用場景 |
|------|-------------|----------|
| **緊密** | 1.1 - 1.2 | H1, H2 大標題 |
| **標準** | 1.3 - 1.4 | H3, H4 小標題 |
| **舒適** | 1.5 - 1.6 | 內文、段落 ⭐ |
| **寬鬆** | 1.75 - 2.0 | 長篇閱讀 |

### 字體應用範例

```tsx
// H1 頁面標題
<h1 className="text-4xl font-bold text-slate-900">
  業務員詳情
</h1>

// H2 區塊標題
<h2 className="text-3xl font-bold text-slate-900 mb-6">
  工作經驗
</h2>

// H3 卡片標題
<h3 className="text-2xl font-semibold text-slate-900">
  專業證照
</h3>

// 內文
<p className="text-base text-slate-700 leading-relaxed">
  這是一段內文範例...
</p>

// 次要文字
<p className="text-sm text-slate-600">
  這是次要說明文字
</p>

// 標籤文字
<span className="text-xs text-slate-500">
  提示訊息
</span>
```

---

## 📐 間距系統

基於 **4px 網格系統** (8 倍數優先)

### Tailwind Spacing Scale

| Class | Value | 使用場景 |
|-------|-------|----------|
| `p-1` / `m-1` | 4px | 極小間距 |
| `p-2` / `m-2` | 8px | 小間距 ⭐ |
| `p-3` / `m-3` | 12px | 中小間距 |
| `p-4` / `m-4` | 16px | 標準間距 ⭐ |
| `p-6` / `m-6` | 24px | 中間距 ⭐ |
| `p-8` / `m-8` | 32px | 大間距 ⭐ |
| `p-12` / `m-12` | 48px | 區塊間距 |
| `p-16` / `m-16` | 64px | 章節間距 |

### 組件內邊距 (Padding)

| 組件類型 | Padding | Tailwind Class |
|----------|---------|----------------|
| **按鈕 (Small)** | 8px 12px | `px-3 py-2` |
| **按鈕 (Medium)** | 12px 16px | `px-4 py-3` |
| **按鈕 (Large)** | 16px 32px | `px-8 py-4` |
| **輸入框** | 12px 16px | `px-4 py-3` |
| **卡片 (Small)** | 16px | `p-4` |
| **卡片 (Medium)** | 24px | `p-6` ⭐ |
| **卡片 (Large)** | 32px | `p-8` |
| **容器 (Mobile)** | 16px | `px-4` |
| **容器 (Desktop)** | 24px | `px-6` |

### 元素間距 (Margin/Gap)

| 關係類型 | Spacing | Tailwind Class | 使用場景 |
|----------|---------|----------------|----------|
| **極緊密** | 4px | `gap-1` / `mb-1` | 圖示與文字 |
| **緊密** | 8px | `gap-2` / `mb-2` | 標籤、小元素 ⭐ |
| **相關** | 16px | `gap-4` / `mb-4` | 相關內容 ⭐ |
| **獨立** | 24px | `gap-6` / `mb-6` | 獨立區塊 ⭐ |
| **分隔** | 32px | `gap-8` / `mb-8` | 明確分隔 |
| **區塊** | 48px | `gap-12` / `mb-12` | 大區塊 |
| **章節** | 64px | `gap-16` / `mb-16` | 章節分隔 |

### 響應式間距

```tsx
// Mobile: 小間距，Desktop: 大間距
<div className="p-4 md:p-6 lg:p-8">
  {/* 內容 */}
</div>

// Mobile: 緊湊，Desktop: 寬鬆
<div className="space-y-4 md:space-y-6 lg:space-y-8">
  {/* 子元素 */}
</div>
```

---

## 🔲 圓角系統

### Tailwind Border Radius Scale

| Class | Value | 使用場景 |
|-------|-------|----------|
| `rounded-none` | 0px | 無圓角 (少用) |
| `rounded-sm` | 2px | 極小元素 |
| `rounded` | 4px | 小型元素 |
| `rounded-md` | 6px | 輸入框、小按鈕 |
| `rounded-lg` | 8px | 按鈕、標籤 ⭐ |
| `rounded-xl` | 12px | 按鈕 (大)、小卡片 ⭐ |
| `rounded-2xl` | 16px | 卡片、容器 ⭐ |
| `rounded-3xl` | 24px | 大型卡片、Modal |
| `rounded-full` | 9999px | 圓形按鈕、頭像 ⭐ |

### 組件圓角規範

| 組件類型 | 圓角 | Tailwind Class |
|----------|------|----------------|
| **按鈕 (Small)** | 8px | `rounded-lg` |
| **按鈕 (Medium/Large)** | 12px | `rounded-xl` ⭐ |
| **輸入框** | 8px | `rounded-lg` |
| **卡片** | 16px | `rounded-2xl` ⭐ |
| **Modal** | 24px | `rounded-3xl` |
| **標籤 (Badge)** | 全圓角 | `rounded-full` ⭐ |
| **頭像 (Avatar)** | 全圓角 | `rounded-full` ⭐ |
| **圖片** | 8-12px | `rounded-lg` / `rounded-xl` |

### 圓角應用原則

1. **一致性**: 同類型組件使用相同圓角
2. **層次**: 較大組件使用較大圓角
3. **品牌**: 全站統一圓角風格
4. **避免混用**: 不要在同一畫面使用太多不同的圓角值

---

## 🌑 陰影系統

### Tailwind Shadow Scale

| Class | 陰影值 | 使用場景 |
|-------|--------|----------|
| `shadow-none` | none | 無陰影 |
| `shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | 微陰影、分隔線 |
| `shadow` | `0 1px 3px rgba(0,0,0,0.1)` | 懸浮元素 |
| `shadow-md` | `0 4px 6px rgba(0,0,0,0.1)` | 卡片 ⭐ |
| `shadow-lg` | `0 10px 15px rgba(0,0,0,0.1)` | 懸浮卡片、Dropdown |
| `shadow-xl` | `0 20px 25px rgba(0,0,0,0.1)` | Modal、重要元素 |
| `shadow-2xl` | `0 25px 50px rgba(0,0,0,0.25)` | 最高層級 (少用) |

### 自定義陰影 (按鈕專用)

```css
/* Primary Button 陰影 */
box-shadow:
  0 10px 25px -5px rgba(14, 165, 233, 0.4),
  0 8px 10px -6px rgba(14, 165, 233, 0.4);

/* Secondary Button 陰影 */
box-shadow:
  0 10px 25px -5px rgba(20, 184, 166, 0.4);
```

### 陰影應用規範

| 組件類型 | 預設陰影 | Hover 陰影 | 使用場景 |
|----------|----------|-----------|----------|
| **卡片 (靜態)** | `shadow-md` | `shadow-lg` | 一般卡片 ⭐ |
| **卡片 (可點擊)** | `shadow` | `shadow-lg` | 互動卡片 |
| **按鈕** | 自定義陰影 | 增強陰影 | 主要按鈕 |
| **Dropdown** | `shadow-lg` | - | 下拉選單 |
| **Modal** | `shadow-xl` | - | 對話框 |
| **Tooltip** | `shadow-md` | - | 提示框 |

### 陰影動畫

```tsx
// 卡片 Hover 效果
<div className="shadow-md hover:shadow-lg transition-shadow duration-200">
  {/* 卡片內容 */}
</div>

// 按鈕 Hover 效果 (配合位移)
<button className="hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
  {/* 按鈕文字 */}
</button>
```

---

## 🎬 動畫系統

### Tailwind Transition Classes

| Class | Duration | Timing Function | 使用場景 |
|-------|----------|-----------------|----------|
| `transition` | 150ms | ease | 快速互動 ⭐ |
| `transition-all` | 150ms | ease | 多屬性變化 |
| `transition-colors` | 150ms | ease | 顏色變化 |
| `transition-transform` | 150ms | ease | 位移、縮放 |
| `transition-opacity` | 150ms | ease | 淡入淡出 |

### Duration (持續時間)

| Class | Value | 使用場景 |
|-------|-------|----------|
| `duration-75` | 75ms | 極快速 |
| `duration-100` | 100ms | 快速 |
| `duration-150` | 150ms | 標準 ⭐ |
| `duration-200` | 200ms | 中速 ⭐ |
| `duration-300` | 300ms | 慢速 ⭐ |
| `duration-500` | 500ms | 強調 |

### Timing Function (緩動函數)

| Class | Cubic Bezier | 使用場景 |
|-------|--------------|----------|
| `ease-linear` | linear | 等速 (Loading) |
| `ease-in` | ease-in | 淡出、消失 |
| `ease-out` | ease-out | 淡入、出現 ⭐ |
| `ease-in-out` | ease-in-out | 平滑過渡 |

### 自定義動畫

```css
/* 淡入 (globals.css 已定義) */
@keyframes fade-in {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

/* 右側滑入 */
@keyframes slide-in-right {
  0% { transform: translateX(100%); opacity: 0; }
  100% { transform: translateX(0); opacity: 1; }
}

/* 底部滑入 */
@keyframes slide-in-bottom {
  0% { transform: translateY(100%); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}

/* 縮放淡入 */
@keyframes scale-in {
  0% { transform: scale(0.9); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

/* 彈跳淡入 */
@keyframes bounce-in {
  0% { transform: scale(0.3); opacity: 0; }
  50% { transform: scale(1.05); }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); opacity: 1; }
}
```

### 互動動畫規範

#### 按鈕 Hover 動畫
```tsx
<button className="
  hover:brightness-110      // 亮度提升
  hover:-translate-y-0.5    // 向上浮起
  active:translate-y-0      // 按下復位
  transition-all duration-300 ease-out
">
  點擊我
</button>
```

#### 卡片 Hover 動畫
```tsx
<div className="
  hover:shadow-lg           // 陰影增強
  hover:-translate-y-1      // 向上浮起
  transition-all duration-200
">
  卡片內容
</div>
```

#### 展開/收合動畫
```tsx
<div className={cn(
  "overflow-hidden transition-all duration-300 ease-out",
  isExpanded ? "max-h-96" : "max-h-0"
)}>
  可展開的內容
</div>
```

#### 淡入動畫
```tsx
// 使用自定義動畫
<div className="animate-fade-in">
  淡入的內容
</div>

// 或使用 Tailwind
<div className="animate-in fade-in duration-300">
  淡入的內容
</div>
```

### 動畫使用原則

1. **適度使用**: 不要過度動畫，避免干擾使用者
2. **快速回應**: 互動動畫應該快速 (150-200ms)
3. **流暢過渡**: 頁面切換使用較長動畫 (300-500ms)
4. **一致性**: 同類型操作使用相同動畫
5. **可關閉**: 尊重使用者的 `prefers-reduced-motion` 設定

```tsx
// 尊重使用者偏好
<div className="motion-safe:animate-fade-in motion-reduce:animate-none">
  內容
</div>
```

---

## 📱 響應式設計

### 斷點 (Breakpoints)

基於 Tailwind CSS 預設斷點 (Mobile First)

| 斷點 | Min Width | Tailwind Prefix | 裝置類型 | 使用場景 |
|------|-----------|-----------------|----------|----------|
| **Default** | 0px | - | Mobile | 預設樣式 (< 640px) ⭐ |
| **sm** | 640px | `sm:` | Large Mobile | 大手機、小平板 |
| **md** | 768px | `md:` | Tablet | 平板 ⭐ |
| **lg** | 1024px | `lg:` | Desktop | 桌面 ⭐ |
| **xl** | 1280px | `xl:` | Large Desktop | 大桌面 |
| **2xl** | 1536px | `2xl:` | Extra Large | 超大螢幕 |

### Mobile First 設計原則

```tsx
// ❌ 錯誤 (Desktop First)
<div className="w-full lg:w-1/2">
  內容
</div>

// ✅ 正確 (Mobile First)
<div className="w-full md:w-1/2">
  內容
</div>
```

### 響應式模式

#### 1. 欄位重排 (Column Drop)

```tsx
// Mobile: 1 欄, Tablet: 2 欄, Desktop: 3 欄
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <div>Column 1</div>
  <div>Column 2</div>
  <div>Column 3</div>
</div>
```

#### 2. 內容優先級 (Content Priority)

```tsx
// Mobile: 隱藏次要內容
<div className="hidden md:block">
  次要內容 (僅平板和桌面顯示)
</div>

// Desktop: 顯示側邊欄
<div className="lg:col-span-1 hidden lg:block">
  側邊欄
</div>
```

#### 3. 導航變化 (Navigation Transformation)

```tsx
// Desktop: 橫向導航
<nav className="hidden md:flex items-center gap-6">
  <a href="/home">首頁</a>
  <a href="/search">搜尋</a>
</nav>

// Mobile: 漢堡選單
<button className="md:hidden">
  <MenuIcon />
</button>
```

#### 4. 間距調整 (Spacing Adjustment)

```tsx
// Mobile: 小間距, Desktop: 大間距
<div className="p-4 md:p-6 lg:p-8">
  <h1 className="text-2xl md:text-3xl lg:text-4xl">標題</h1>
  <div className="space-y-4 md:space-y-6 lg:space-y-8">
    子元素
  </div>
</div>
```

### 常用響應式模式

#### 容器寬度
```tsx
<div className="container mx-auto px-4 sm:px-6 lg:px-8">
  {/* 內容 */}
</div>
```

#### 文字大小
```tsx
<h1 className="text-2xl md:text-3xl lg:text-4xl font-bold">
  響應式標題
</h1>
```

#### 網格佈局
```tsx
// 證照卡片: Mobile 1欄, Tablet+ 2欄
<div className="grid grid-cols-1 md:grid-cols-2 gap-4">
  {certifications.map(cert => <CertCard key={cert.id} />)}
</div>
```

#### Flexbox 方向
```tsx
<div className="flex flex-col md:flex-row gap-4">
  <div className="flex-1">左側</div>
  <div className="flex-1">右側</div>
</div>
```

### 觸控友善設計

#### 最小觸控目標
- **標準**: 44x44 px (Apple HIG)
- **建議**: 48x48 px (Material Design)

```tsx
// 確保按鈕足夠大
<button className="min-h-[44px] min-w-[44px] px-4 py-2">
  點擊
</button>

// 圖示按鈕加大觸控區域
<button className="p-3">  {/* 48x48 px */}
  <Icon className="h-6 w-6" />
</button>
```

#### 間距充足
```tsx
// 按鈕間距至少 8px
<div className="flex gap-2">
  <button>按鈕 1</button>
  <button>按鈕 2</button>
</div>
```

---

## ♿ 無障礙設計 (Accessibility)

### WCAG 2.1 AA 標準

#### 1. 可感知 (Perceivable)

**色彩對比**
- 標準文字: 對比度 ≥ 4.5:1
- 大字體 (18pt+): 對比度 ≥ 3:1
- 圖形元件: 對比度 ≥ 3:1

```tsx
// ✅ 良好對比
<p className="text-slate-900 bg-white">  {/* 19.64:1 */}
  內文
</p>

// ❌ 對比不足
<p className="text-slate-400 bg-white">  {/* 2.88:1 */}
  內文
</p>
```

**文字替代**
```tsx
// 圖片加 alt
<img src="avatar.jpg" alt="使用者頭像" />

// 圖示加 aria-label
<button aria-label="關閉對話框">
  <XIcon />
</button>

// 裝飾性圖示用 aria-hidden
<span aria-hidden="true">
  <StarIcon />
</span>
```

#### 2. 可操作 (Operable)

**鍵盤導航**
```tsx
// 所有互動元素可用 Tab 鍵訪問
<button className="focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
  按鈕
</button>

// 自定義元件要有 tabIndex
<div
  role="button"
  tabIndex={0}
  onKeyDown={(e) => e.key === 'Enter' && handleClick()}
>
  自定義按鈕
</div>
```

**焦點指示**
```tsx
// 清楚的 Focus Ring
<input className="
  border border-slate-300
  focus:border-primary-500
  focus:ring-2
  focus:ring-primary-500
  focus:ring-offset-2
  focus:outline-none
" />
```

**跳過連結**
```tsx
<a
  href="#main-content"
  className="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50"
>
  跳到主要內容
</a>
```

#### 3. 可理解 (Understandable)

**表單標籤**
```tsx
<div>
  <label htmlFor="email" className="block mb-2">
    Email 地址
  </label>
  <input
    id="email"
    type="email"
    aria-describedby="email-helper"
    aria-invalid={hasError}
  />
  <p id="email-helper" className="text-sm text-slate-600 mt-1">
    我們不會分享您的 Email
  </p>
</div>
```

**錯誤訊息**
```tsx
<div>
  <input
    aria-invalid="true"
    aria-describedby="email-error"
  />
  <p
    id="email-error"
    role="alert"
    className="text-error-600 text-sm mt-1"
  >
    請輸入有效的 Email 地址
  </p>
</div>
```

#### 4. 穩健 (Robust)

**語義化 HTML**
```tsx
// ✅ 使用語義標籤
<header>...</header>
<nav>...</nav>
<main>...</main>
<article>...</article>
<aside>...</aside>
<footer>...</footer>

// ❌ 過度使用 div
<div className="header">...</div>
<div className="navigation">...</div>
```

**ARIA 屬性**
```tsx
// 按鈕
<button aria-label="關閉">
  <XIcon />
</button>

// 載入狀態
<div role="status" aria-live="polite" aria-busy="true">
  載入中...
</div>

// 標籤頁
<div role="tablist">
  <button role="tab" aria-selected="true" aria-controls="panel-1">
    標籤 1
  </button>
</div>
<div role="tabpanel" id="panel-1">
  內容 1
</div>
```

### Screen Reader 友善

```tsx
// 隱藏視覺但保留給 Screen Reader
<span className="sr-only">
  新訊息: 5 則
</span>

// 隱藏裝飾性元素
<svg aria-hidden="true">
  <path d="..." />
</svg>

// Live Region (動態更新)
<div aria-live="polite" aria-atomic="true">
  {successMessage}
</div>
```

---

## 🧩 組件規範

### Button (按鈕)

#### Variants
- `default`: 主要按鈕 (藍色漸層 + 陰影)
- `secondary`: 次要按鈕 (青色漸層)
- `outline`: 邊框按鈕 (白底藍框)
- `ghost`: 幽靈按鈕 (透明底)
- `link`: 連結按鈕 (底線)

#### Sizes
- `sm`: 32px 高度 (小按鈕)
- `default`: 40px 高度 (標準按鈕) ⭐
- `lg`: 56px 高度 (大按鈕)
- `icon`: 40x40 px (圖示按鈕)

#### States
- Default
- Hover: 亮度提升 + 向上浮起
- Active: 復位
- Disabled: 50% 透明度
- Loading: 顯示 Spinner

```tsx
<Button variant="default" size="lg" isLoading={false}>
  提交
</Button>
```

### Card (卡片)

#### Props
- `shadow`: `none` | `sm` | `md` | `lg` | `xl`
- `padding`: `none` | `sm` | `md` | `lg`
- `hover`: boolean (是否有 Hover 效果)

#### Default Style
- 背景: `bg-white`
- 圓角: `rounded-2xl` (16px) ⭐
- 邊框: `border border-slate-100`
- 陰影: `shadow-md`

```tsx
<Card shadow="md" padding="md" hover={true}>
  <CardHeader>
    <CardTitle>標題</CardTitle>
  </CardHeader>
  <CardContent>
    內容
  </CardContent>
</Card>
```

### Badge (徽章)

#### Variants
- `default`: 灰色
- `primary`: 藍色
- `secondary`: 青色
- `success`: 綠色 (已審核)
- `warning`: 橘色 (審核中)
- `error`: 紅色 (已拒絕)
- `info`: 藍色

#### Sizes
- `sm`: 12px 文字
- `md`: 14px 文字 ⭐
- `lg`: 16px 文字

#### Features
- `dot`: 顯示狀態點

```tsx
<Badge variant="success" size="sm" dot>
  已審核
</Badge>
```

### Input (輸入框)

#### States
- Default: 灰色邊框
- Focus: 藍色邊框 + Ring
- Error: 紅色邊框 + 錯誤訊息
- Disabled: 灰色背景

#### Default Style
- 圓角: `rounded-lg` (8px)
- 高度: 40px (py-3)
- 邊框: `border border-slate-300`
- Focus Ring: `ring-2 ring-primary-500`

```tsx
<Input
  label="Email"
  type="email"
  placeholder="example@email.com"
  error={errorMessage}
  disabled={false}
/>
```

---

## 📚 使用範例

### 完整表單範例

```tsx
<Card shadow="md" padding="lg">
  <CardHeader>
    <CardTitle>新增工作經驗</CardTitle>
    <CardDescription>請填寫您的工作經驗詳情</CardDescription>
  </CardHeader>

  <CardContent>
    <form className="space-y-6">
      <div>
        <label htmlFor="company" className="block text-sm font-medium text-slate-700 mb-2">
          公司名稱 *
        </label>
        <input
          id="company"
          type="text"
          className="
            w-full px-4 py-3 rounded-lg
            border border-slate-300
            focus:border-primary-500
            focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
            focus:outline-none
            transition-all duration-200
          "
          placeholder="請輸入公司名稱"
        />
      </div>

      <div>
        <label htmlFor="position" className="block text-sm font-medium text-slate-700 mb-2">
          職位 *
        </label>
        <input
          id="position"
          type="text"
          className="
            w-full px-4 py-3 rounded-lg
            border border-slate-300
            focus:border-primary-500
            focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
            focus:outline-none
            transition-all duration-200
          "
          placeholder="請輸入職位"
        />
      </div>

      <div className="flex gap-4">
        <Button type="button" variant="outline">
          取消
        </Button>
        <Button type="submit" variant="default">
          提交
        </Button>
      </div>
    </form>
  </CardContent>
</Card>
```

### 狀態徽章範例

```tsx
// 審核狀態徽章
const ApprovalBadge = ({ status }: { status: 'pending' | 'approved' | 'rejected' }) => {
  const variants = {
    pending: { variant: 'warning' as const, label: '審核中', icon: Clock },
    approved: { variant: 'success' as const, label: '已審核', icon: CheckCircle2 },
    rejected: { variant: 'error' as const, label: '已拒絕', icon: XCircle },
  };

  const config = variants[status];
  const Icon = config.icon;

  return (
    <Badge variant={config.variant} size="sm">
      <Icon className="mr-1 h-3 w-3" />
      {config.label}
    </Badge>
  );
};
```

---

## 📖 參考資源

### 官方文檔
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Radix UI](https://www.radix-ui.com/)
- [Lucide Icons](https://lucide.dev/)

### 設計參考
- [Material Design](https://material.io/design)
- [Apple Human Interface Guidelines](https://developer.apple.com/design/human-interface-guidelines/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

### 工具
- [Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Color Hunt](https://colorhunt.co/)
- [Easing Functions](https://easings.net/)

---

**版本**: 1.0
**最後更新**: 2026-01-20
**維護者**: YAMU Design Team
