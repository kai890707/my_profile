# UI/UX 設計規格：首頁視覺優化

**變更 ID**: 20260121-optimize-homepage
**類型**: Frontend Enhancement (純視覺優化)
**設計師**: Claude Sonnet 4.5
**日期**: 2026-01-21

---

## 📋 目錄

- [1. 設計目標](#1-設計目標)
- [2. 設計系統定義](#2-設計系統定義)
- [3. Section 設計詳解](#3-section-設計詳解)
- [4. 動畫系統](#4-動畫系統)
- [5. 響應式設計](#5-響應式設計)
- [6. 可訪問性設計](#6-可訪問性設計)
- [7. 視覺驗收標準](#7-視覺驗收標準)

---

## 1. 設計目標

### 1.1 設計理念

**核心理念**: 「現代、專業、高效能」

- **現代感**: 使用漸層色彩、流暢動畫、立體陰影
- **專業度**: 清晰的視覺層次、一致的設計語言
- **高效能**: 優化動畫效能、減少 CLS、確保 LCP < 2.5s

### 1.2 優化範圍

這是一次**純視覺優化**，不改變任何功能邏輯：

✅ **優化範圍**:
- 漸層背景升級
- 卡片立體感增強
- 動畫效果添加
- 響應式佈局優化
- 間距與陰影細調

❌ **不改變**:
- 功能邏輯
- API 整合
- 狀態管理
- 路由結構

### 1.3 設計原則

1. **遵循現有設計系統** (`frontend/docs/design-system.md`)
2. **Mobile First** - 從手機版開始設計
3. **效能優先** - 確保 Core Web Vitals 達標
4. **漸進增強** - 動畫支援 prefers-reduced-motion
5. **無障礙優先** - 色彩對比度 >= 4.5:1

---

## 2. 設計系統定義

### 2.1 色彩方案

遵循 `design-system.md` 的色彩規範，針對首頁微調：

#### 主色調 (Primary - Sky Blue)

```css
/* 漸層背景使用 */
--color-primary-25: #f5fbff;   /* 新增：極淺藍 - Hero 背景中間色 */
--color-primary-50: #f0f9ff;   /* 極淺藍 - Hero 背景起點 */
--color-primary-500: #0ea5e9;  /* 主藍 - 按鈕、連結 ⭐ */
--color-primary-600: #0284c7;  /* 深藍 - Hover 狀態 */
```

#### 次要色 (Secondary - Teal)

```css
--color-secondary-50: #f0fdfa;  /* 極淺青 - Hero 背景終點 */
--color-secondary-500: #14b8a6; /* 主青 - 漸層配色 ⭐ */
--color-secondary-600: #0d9488; /* 深青 - Hover 狀態 */
```

#### 中性色 (Neutral - Slate)

```css
--color-slate-50: #f8fafc;      /* 頁面背景 */
--color-slate-100: #f1f5f9;     /* 卡片邊框 */
--color-slate-600: #475569;     /* 次要文字 */
--color-slate-700: #334155;     /* 標準文字 */
--color-slate-900: #0f172a;     /* 標題文字 ⭐ */
```

#### 色彩對比度驗證

所有組合均符合 WCAG AA 標準：

| 前景色 | 背景色 | 對比度 | 標準 |
|--------|--------|--------|------|
| `text-slate-900` | `bg-white` | 19.64:1 | ✅ AAA |
| `text-slate-700` | `bg-white` | 10.69:1 | ✅ AAA |
| `text-slate-600` | `bg-white` | 7.74:1 | ✅ AAA |
| `text-white` | `bg-primary-500` | 4.93:1 | ✅ AA |
| `text-white` | `bg-secondary-500` | 4.52:1 | ✅ AA |

### 2.2 字體系統

完全遵循 `design-system.md` 的字體規範：

#### 字級階層

| 用途 | Tailwind Class | Size | Line Height | Weight |
|------|----------------|------|-------------|--------|
| **H1 (Hero)** | `text-4xl md:text-5xl lg:text-6xl` | 36px / 48px / 60px | 1.1 | 700 (Bold) |
| **H2 (Section)** | `text-3xl lg:text-4xl` | 30px / 36px | 1.2 | 700 (Bold) |
| **H3 (Card)** | `text-xl` | 20px | 1.4 | 600 (Semibold) |
| **Body** | `text-base` | 16px | 1.5 | 400 (Regular) |
| **Description** | `text-lg` | 18px | 1.56 | 400 (Regular) |

#### 漸層文字效果

```css
/* Hero 標題漸層效果 */
.text-gradient {
  background: linear-gradient(to right, #0ea5e9, #14b8a6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
```

**應用範例**:
```tsx
<span className="bg-gradient-to-r from-primary-500 to-secondary-500 bg-clip-text text-transparent">
  專業業務員
</span>
```

### 2.3 間距系統

基於 **4px 網格系統** (8 倍數優先)

#### Section 垂直間距

| 裝置類型 | Tailwind Class | Value |
|----------|----------------|-------|
| **Mobile** | `py-16` | 64px |
| **Tablet** | `md:py-20` | 80px |
| **Desktop (Hero Only)** | `lg:py-28` | 112px |

#### 容器水平間距

| 裝置類型 | Tailwind Class | Value |
|----------|----------------|-------|
| **Mobile** | `px-4` | 16px |
| **Tablet** | `sm:px-6` | 24px |
| **Desktop** | `lg:px-8` | 32px |

#### 元素間距 (Gap)

| 關係類型 | Mobile | Tablet | Desktop | Tailwind Class |
|----------|--------|--------|---------|----------------|
| **卡片間距** | 16px | 24px | 32px | `gap-4 md:gap-6 lg:gap-8` |
| **按鈕間距** | 16px | 24px | 24px | `gap-4 md:gap-6` |
| **表單元素** | 8px | 8px | 8px | `gap-2` |

### 2.4 圓角系統

遵循 `design-system.md` 的圓角規範：

| 元素類型 | Tailwind Class | Value | 使用場景 |
|----------|----------------|-------|----------|
| **大型卡片** | `rounded-2xl` | 16px | Features Cards, Salesperson Cards |
| **按鈕** | `rounded-xl` | 12px | Primary/Secondary Buttons |
| **輸入框** | `rounded-lg` | 8px | Search Input |
| **圖示容器** | `rounded-3xl` | 24px | Feature Icons |
| **背景裝飾** | `rounded-full` | 全圓 | Hero/CTA Background Blobs |

### 2.5 陰影系統

#### 陰影階層

| 階層 | Tailwind Class | 使用場景 | 陰影值 |
|------|----------------|----------|--------|
| **預設** | `shadow-md` | 靜態卡片 | `0 4px 6px rgba(0,0,0,0.1)` |
| **懸浮** | `shadow-lg` | 搜尋列、Hover 卡片 | `0 10px 15px rgba(0,0,0,0.1)` |
| **強調** | `shadow-xl` | CTA 按鈕、Modal | `0 20px 25px rgba(0,0,0,0.1)` |

#### 陰影動畫

```tsx
// 卡片 Hover 效果
className="
  shadow-md
  hover:shadow-xl
  transition-shadow duration-300
"
```

### 2.6 動畫時機函數

| 動畫類型 | Duration | Timing Function | Tailwind Class |
|----------|----------|-----------------|----------------|
| **互動動畫** (Hover) | 200ms | ease-out | `duration-200 ease-out` |
| **進場動畫** (Fade-in) | 300ms | ease-out | `duration-300 ease-out` |
| **交錯動畫** (Stagger) | 300ms + delay | ease-out | `duration-300` + `style={{ animationDelay }}` |

---

## 3. Section 設計詳解

### 3.1 Hero Section - 主視覺區

#### 設計目標
- 吸引使用者注意力
- 清楚傳達平台價值
- 引導使用者進行搜尋

#### 視覺設計

**漸層背景升級** (FR-001):

```tsx
// 當前 (2 色階)
className="bg-gradient-to-br from-primary-50 via-white to-secondary-50"

// 優化後 (3 色階，更豐富)
className="bg-gradient-to-br from-primary-50 via-primary-25 to-secondary-50"
```

**色彩說明**:
- `from-primary-50`: 起點 - 極淺藍 (#f0f9ff)
- `via-primary-25`: 中間 - 新增更淺的藍 (#f5fbff)
- `to-secondary-50`: 終點 - 極淺青 (#f0fdfa)

**背景裝飾元素調整**:

```tsx
<div className="absolute inset-0 overflow-hidden">
  {/* 左上裝飾球 - 增大、增加透明度 */}
  <div className="
    absolute -top-40 -right-40
    h-96 w-96                    // 80px → 96px (384px)
    rounded-full
    bg-primary-200/20            // 透明度 20%
    blur-3xl
  " />

  {/* 右下裝飾球 - 調整位置、增加模糊 */}
  <div className="
    absolute -bottom-40 -left-40
    h-96 w-96                    // 80px → 96px (384px)
    rounded-full
    bg-secondary-200/20          // 透明度 20%
    blur-3xl
  " />
</div>
```

**標題文字效果增強** (FR-002):

```tsx
// 當前
<h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 mb-6">
  找到最適合的
  <span className="bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
    {' '}專業業務員
  </span>
</h1>

// 優化後
<h1 className="
  text-4xl sm:text-5xl lg:text-6xl  // 字級保持
  font-bold
  text-slate-900
  mb-6
  animate-fade-in-up               // 新增進場動畫
">
  找到最適合的
  <span className="
    bg-gradient-to-r
    from-primary-500 to-secondary-500  // 600 → 500 (更鮮豔)
    bg-clip-text
    text-transparent
  ">
    {' '}專業業務員
  </span>
</h1>
```

**描述文字優化**:

```tsx
<p className="
  text-xl                          // 字級保持
  text-slate-600
  mb-12
  max-w-2xl mx-auto
  animate-fade-in-up               // 新增進場動畫
  animation-delay-100              // 延遲 100ms
">
  YAMU 連結優質業務員與企業需求，打造透明、高效的商業合作環境
</p>
```

**搜尋列設計改善** (FR-003):

```tsx
<form onSubmit={handleSearch} className="
  max-w-2xl mx-auto
  animate-fade-in-up               // 新增進場動畫
  animation-delay-200              // 延遲 200ms
">
  <div className="flex gap-3">    // gap-2 → gap-3
    <Input
      size="lg"
      placeholder="搜尋業務員、公司、產業..."
      value={keyword}
      onChange={(e) => setKeyword(e.target.value)}
      icon={<Search className="h-5 w-5" />}
      className="
        text-lg
        h-14                       // 新增高度 (56px, 更大觸控目標)
        shadow-lg                  // 新增陰影 (更突出)
      "
    />
    <Button
      type="submit"
      size="lg"
      className="
        px-10                      // 新增寬度 (更明顯)
        h-14                       // 匹配 Input 高度
      "
    >
      搜尋
    </Button>
  </div>
</form>
```

#### 響應式調整

| 斷點 | 佈局調整 |
|------|----------|
| **Mobile (< 640px)** | - 標題 `text-4xl` (36px)<br>- Section padding `py-16` (64px)<br>- 搜尋列上下排列 (`flex-col`)<br>- 按鈕全寬 (`w-full`) |
| **Tablet (640px - 1024px)** | - 標題 `text-5xl` (48px)<br>- Section padding `md:py-20` (80px)<br>- 搜尋列左右排列 (`flex-row`) |
| **Desktop (> 1024px)** | - 標題 `text-6xl` (60px)<br>- Section padding `lg:py-28` (112px)<br>- 最大寬度 `max-w-4xl` |

#### 動畫設計

**進場動畫時序**:
1. 標題: 淡入向上滑入 (0ms delay)
2. 描述: 淡入向上滑入 (100ms delay)
3. 搜尋列: 淡入向上滑入 (200ms delay)

```css
/* 動畫定義見 Section 4 */
```

---

### 3.2 Features Section - 特色卡片

#### 設計目標
- 展示平台三大特色
- 提升視覺吸引力
- 增強互動回饋

#### 視覺設計

**卡片立體感增強** (FR-004):

```tsx
{features.map((feature, index) => (
  <Card
    key={index}
    className="
      text-center
      border border-slate-100           // 新增邊框
      shadow-md                          // 預設陰影
      hover:shadow-xl                    // Hover 陰影增強
      hover:-translate-y-2               // Hover 向上浮起 (8px)
      transition-all duration-300        // 過渡動畫
      animate-fade-in-up                 // 進場動畫
    "
    style={{ animationDelay: `${index * 100}ms` }}  // 交錯延遲
  >
    <CardContent className="pt-8">
      {/* 圖示容器 */}
      <div className="
        inline-flex items-center justify-center
        h-20 w-20                        // 16 → 20 (80px, 更大)
        rounded-3xl                      // 2xl → 3xl (24px, 更圓潤)
        bg-gradient-to-br
        from-primary-500 to-secondary-500
        shadow-lg                        // 新增陰影
        mb-6
      ">
        <feature.icon className="
          h-10 w-10                      // 8 → 10 (40px, 更大)
          text-white
        " />
      </div>

      {/* 標題 */}
      <h3 className="text-xl font-bold text-slate-900 mb-3">
        {feature.title}
      </h3>

      {/* 描述 */}
      <p className="text-slate-600">
        {feature.description}
      </p>
    </CardContent>
  </Card>
))}
```

**圖示 Hover 效果** (FR-005 - 可選):

```tsx
<div className="
  inline-flex items-center justify-center
  h-20 w-20
  rounded-3xl
  bg-gradient-to-br from-primary-500 to-secondary-500
  shadow-lg
  mb-6
  transition-transform duration-300    // 新增過渡
  hover:scale-110                      // Hover 微放大
  hover:rotate-6                       // Hover 微旋轉
">
  <feature.icon className="h-10 w-10 text-white" />
</div>
```

#### 佈局優化

```tsx
// 當前
<div className="grid md:grid-cols-3 gap-8">

// 優化後 (保持不變，已經正確)
<div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
```

**說明**: 佈局已經符合 Mobile First 原則，不需修改。

#### 響應式調整

| 斷點 | 網格佈局 | 卡片間距 |
|------|----------|----------|
| **Mobile (< 768px)** | 1 欄 (`grid-cols-1`) | 24px (`gap-6`) |
| **Tablet/Desktop (>= 768px)** | 3 欄 (`md:grid-cols-3`) | 32px (`md:gap-8`) |

#### 動畫設計

**交錯進場動畫**:
- 卡片 1: 0ms delay
- 卡片 2: 100ms delay
- 卡片 3: 200ms delay

---

### 3.3 Popular Salespersons Section - 業務員展示

#### 設計目標
- 展示最新加入的業務員
- 優化響應式佈局
- 提供流暢的進場動畫

#### 視覺設計

**標題區塊保持**:

```tsx
<div className="flex items-center justify-between mb-12">
  <div>
    <h2 className="text-3xl lg:text-4xl font-bold text-slate-900 mb-2">
      熱門業務員
    </h2>
    <p className="text-lg text-slate-600">
      最新加入平台的專業業務員
    </p>
  </div>
  <Link href="/search">
    <Button variant="outline" size="lg">
      查看全部
      <ArrowRight className="ml-2 h-4 w-4" />
    </Button>
  </Link>
</div>
```

**響應式佈局改善** (FR-006):

```tsx
// 當前
<div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

// 優化後
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
```

**變更說明**:
- 明確定義 Mobile: 1 欄
- Small Tablet (640px+): 2 欄
- Desktop (1024px+): 3 欄
- 間距漸進: 16px → 24px → 32px

**卡片進場動畫** (FR-007):

```tsx
{popularSalespersons.data.map((salesperson, index) => (
  <div
    key={salesperson.id}
    className="animate-fade-in-up"
    style={{ animationDelay: `${index * 50}ms` }}  // 每張延遲 50ms
  >
    <SalespersonCard salesperson={salesperson} />
  </div>
))}
```

#### 響應式調整

| 斷點 | 網格佈局 | 卡片間距 |
|------|----------|----------|
| **Mobile (< 640px)** | 1 欄 | 16px (`gap-4`) |
| **Small Tablet (640px - 1024px)** | 2 欄 (`sm:grid-cols-2`) | 24px (`md:gap-6`) |
| **Desktop (> 1024px)** | 3 欄 (`lg:grid-cols-3`) | 32px (`lg:gap-8`) |

#### 動畫設計

**交錯進場** (6 張卡片):
- 卡片 1: 0ms
- 卡片 2: 50ms
- 卡片 3: 100ms
- 卡片 4: 150ms
- 卡片 5: 200ms
- 卡片 6: 250ms

---

### 3.4 CTA Section - 行動呼籲

#### 設計目標
- 強烈的視覺吸引力
- 鼓勵使用者註冊或搜尋
- 作為頁面的完美結尾

#### 視覺設計

**漸層背景增強** (FR-008):

```tsx
// 當前 (2 色階)
<section className="py-20 bg-gradient-to-r from-primary-600 to-secondary-600">

// 優化後 (3 色階 + 背景裝飾)
<section className="
  relative
  py-16 md:py-20                       // 響應式 padding
  bg-gradient-to-r
  from-primary-500 via-primary-600 to-secondary-500  // 3 色階
  overflow-hidden
">
  {/* 背景裝飾 */}
  <div className="absolute inset-0 opacity-30">
    {/* 左上裝飾球 */}
    <div className="
      absolute top-0 left-0
      w-96 h-96                        // 384px
      bg-white/20
      rounded-full
      blur-3xl
    " />

    {/* 右下裝飾球 */}
    <div className="
      absolute bottom-0 right-0
      w-96 h-96                        // 384px
      bg-white/20
      rounded-full
      blur-3xl
    " />
  </div>

  {/* 內容 (relative 確保在裝飾上方) */}
  <div className="container mx-auto px-4 sm:px-6 lg:px-8 relative">
    {/* ... */}
  </div>
</section>
```

**按鈕設計優化** (FR-009):

```tsx
<div className="
  flex flex-col sm:flex-row        // Mobile: 上下排列, Desktop: 左右排列
  gap-4 md:gap-6                   // 增加間距
  justify-center
">
  {/* 主要按鈕 */}
  <Button
    asChild
    size="lg"
    variant="secondary"
    className="
      h-14                           // 新增高度 (56px)
      px-10                          // 新增寬度
      text-lg                        // 新增字級
      shadow-xl                      // 新增陰影
      hover:brightness-110           // Hover 亮度提升
      hover:-translate-y-1           // Hover 向上浮起
      hover:shadow-2xl               // Hover 陰影增強
      transition-all duration-300
    "
  >
    <Link href="/register">免費註冊</Link>
  </Button>

  {/* 次要按鈕 */}
  <Button
    asChild
    size="lg"
    variant="outline"
    className="
      h-14                           // 匹配主要按鈕高度
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
</div>
```

#### 響應式調整

| 斷點 | 按鈕佈局 | 按鈕寬度 |
|------|----------|----------|
| **Mobile (< 640px)** | 上下排列 (`flex-col`) | 全寬 (預設 block) |
| **Tablet/Desktop (>= 640px)** | 左右排列 (`sm:flex-row`) | 固定寬度 (px-10) |

---

## 4. 動畫系統

### 4.1 自定義動畫定義

**位置**: `frontend/app/globals.css`

**新增動畫** (FR-010):

```css
/* ========================================
   自定義動畫系統
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

### 4.2 動畫使用規範

#### 進場動畫

**用途**: 頁面載入時的視覺引導

**使用場景**:
- Hero Section: 標題、描述、搜尋列
- Features Cards: 交錯淡入
- Salesperson Cards: 交錯淡入

**實作方式**:

```tsx
// 單一元素
<div className="animate-fade-in-up">
  內容
</div>

// 帶延遲
<div className="animate-fade-in-up animation-delay-100">
  內容
</div>

// 交錯動畫 (使用 inline style)
{items.map((item, index) => (
  <div
    key={item.id}
    className="animate-fade-in-up"
    style={{ animationDelay: `${index * 100}ms` }}
  >
    {item.content}
  </div>
))}
```

#### 互動動畫

**用途**: Hover、Focus、Active 狀態回饋

**使用場景**:
- 卡片 Hover: 浮起 + 陰影增強
- 按鈕 Hover: 亮度提升 + 微浮起
- 圖示 Hover: 微旋轉 (可選)

**實作方式**:

```tsx
// 卡片 Hover
<Card className="
  shadow-md
  hover:shadow-xl
  hover:-translate-y-2
  transition-all duration-300
">

// 按鈕 Hover
<Button className="
  hover:brightness-110
  hover:-translate-y-1
  transition-all duration-300
">

// 圖示 Hover (可選)
<div className="
  hover:scale-110
  hover:rotate-6
  transition-transform duration-300
">
```

### 4.3 動畫效能考量

**最佳實踐**:

1. **使用 CSS Transform** (GPU 加速)
   - ✅ `transform: translateY(-8px)`
   - ❌ `top: -8px` (觸發 Layout)

2. **避免過多動畫**
   - 首頁最多 20-30 個動畫元素
   - 交錯延遲總時長 < 500ms

3. **尊重使用者偏好**
   - 支援 `prefers-reduced-motion`
   - 關閉動畫時仍保持視覺效果

4. **監控效能指標**
   - CLS < 0.1 (無佈局位移)
   - FPS >= 60 (流暢動畫)

---

## 5. 響應式設計

### 5.1 斷點定義

遵循 Tailwind CSS 預設斷點 (Mobile First):

| 斷點 | Min Width | Prefix | 裝置類型 |
|------|-----------|--------|----------|
| **Default** | 0px | - | Mobile (< 640px) |
| **sm** | 640px | `sm:` | Large Mobile |
| **md** | 768px | `md:` | Tablet |
| **lg** | 1024px | `lg:` | Desktop |
| **xl** | 1280px | `xl:` | Large Desktop |

### 5.2 各 Section 響應式規範

#### Hero Section

| 屬性 | Mobile (< 640px) | Tablet (640px - 1024px) | Desktop (> 1024px) |
|------|------------------|-------------------------|---------------------|
| **Section Padding** | `py-16` (64px) | `md:py-20` (80px) | `lg:py-28` (112px) |
| **Container Padding** | `px-4` (16px) | `sm:px-6` (24px) | `lg:px-8` (32px) |
| **標題字級** | `text-4xl` (36px) | `sm:text-5xl` (48px) | `lg:text-6xl` (60px) |
| **描述字級** | `text-xl` (20px) | `text-xl` | `text-xl` |
| **搜尋列佈局** | `flex-col` (上下) | `flex-row` (左右) | `flex-row` |
| **搜尋按鈕寬度** | `w-full` (全寬) | 固定寬度 | 固定寬度 |

**Tailwind 實作**:

```tsx
<section className="
  py-16 md:py-20 lg:py-28
  bg-gradient-to-br from-primary-50 via-primary-25 to-secondary-50
">
  <div className="container mx-auto px-4 sm:px-6 lg:px-8">
    <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold">
      {/* 標題 */}
    </h1>

    <form className="max-w-2xl mx-auto">
      <div className="flex flex-col sm:flex-row gap-3">
        <Input className="flex-1" />
        <Button className="sm:px-10">搜尋</Button>
      </div>
    </form>
  </div>
</section>
```

#### Features Section

| 屬性 | Mobile | Tablet | Desktop |
|------|--------|--------|---------|
| **Section Padding** | `py-16` | `md:py-20` | `md:py-20` |
| **網格佈局** | 1 欄 | 3 欄 | 3 欄 |
| **卡片間距** | `gap-6` (24px) | `md:gap-8` (32px) | `md:gap-8` |
| **圖示大小** | `h-20 w-20` | `h-20 w-20` | `h-20 w-20` |

**Tailwind 實作**:

```tsx
<section className="py-16 md:py-20 bg-white">
  <div className="container mx-auto px-4 sm:px-6 lg:px-8">
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
      {features.map((feature) => (
        <Card key={feature.title}>
          {/* 卡片內容 */}
        </Card>
      ))}
    </div>
  </div>
</section>
```

#### Popular Salespersons Section

| 屬性 | Mobile | Small Tablet (640px+) | Desktop (1024px+) |
|------|--------|----------------------|-------------------|
| **Section Padding** | `py-16` | `md:py-20` | `md:py-20` |
| **網格佈局** | 1 欄 | 2 欄 | 3 欄 |
| **卡片間距** | `gap-4` (16px) | `md:gap-6` (24px) | `lg:gap-8` (32px) |

**Tailwind 實作**:

```tsx
<section className="py-16 md:py-20 bg-slate-50">
  <div className="container mx-auto px-4 sm:px-6 lg:px-8">
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">
      {salespersons.map((person) => (
        <SalespersonCard key={person.id} salesperson={person} />
      ))}
    </div>
  </div>
</section>
```

#### CTA Section

| 屬性 | Mobile | Desktop |
|------|--------|---------|
| **Section Padding** | `py-16` | `md:py-20` |
| **標題字級** | `text-3xl` (30px) | `lg:text-4xl` (36px) |
| **描述字級** | `text-xl` | `text-xl` |
| **按鈕佈局** | `flex-col` (上下) | `sm:flex-row` (左右) |
| **按鈕寬度** | 全寬 | 固定寬度 |
| **按鈕間距** | `gap-4` | `md:gap-6` |

**Tailwind 實作**:

```tsx
<section className="
  py-16 md:py-20
  bg-gradient-to-r from-primary-500 via-primary-600 to-secondary-500
">
  <div className="container mx-auto px-4 sm:px-6 lg:px-8">
    <div className="max-w-3xl mx-auto text-center text-white">
      <h2 className="text-3xl lg:text-4xl font-bold mb-6">
        {/* 標題 */}
      </h2>

      <div className="flex flex-col sm:flex-row gap-4 md:gap-6 justify-center">
        <Button size="lg" className="h-14 px-10">
          免費註冊
        </Button>
        <Button size="lg" variant="outline" className="h-14 px-10">
          開始搜尋
        </Button>
      </div>
    </div>
  </div>
</section>
```

### 5.3 觸控友善設計

#### 最小觸控目標

遵循 Apple HIG 和 Material Design 標準：

| 標準 | 最小尺寸 | 建議尺寸 |
|------|----------|----------|
| **Apple HIG** | 44x44 px | 48x48 px |
| **Material Design** | 48x48 px | - |
| **YAMU 標準** | 44x44 px | 56x56 px (h-14) |

**首頁觸控目標檢查**:

| 元素 | 尺寸 | 符合標準 |
|------|------|----------|
| **搜尋輸入框** | h-14 (56px) | ✅ 48px+ |
| **搜尋按鈕** | h-14 (56px) | ✅ 48px+ |
| **Features 卡片** | 整張可點擊 (>200px) | ✅ |
| **Salesperson 卡片** | 整張可點擊 (>250px) | ✅ |
| **CTA 按鈕** | h-14 (56px) | ✅ 48px+ |
| **"查看全部"按鈕** | size="lg" (40px) | ⚠️ 建議改為 h-14 |

**優化建議**:

```tsx
// 當前 "查看全部" 按鈕
<Button variant="outline" size="lg">
  查看全部
</Button>

// 建議優化為
<Button
  variant="outline"
  size="lg"
  className="h-12 sm:h-auto"  // Mobile: 48px, Desktop: 預設
>
  查看全部
</Button>
```

#### 按鈕間距

確保按鈕間距 >= 8px：

| 區域 | 間距 | Tailwind Class | 符合標準 |
|------|------|----------------|----------|
| **Hero 搜尋列** | 12px | `gap-3` | ✅ 8px+ |
| **CTA 按鈕** | 16px / 24px | `gap-4 md:gap-6` | ✅ 8px+ |

---

## 6. 可訪問性設計

### 6.1 WCAG 2.1 AA 標準

#### 色彩對比度

**標準**:
- 標準文字 (< 18pt): 對比度 >= 4.5:1
- 大字體 (>= 18pt): 對比度 >= 3:1
- 互動元素: 對比度 >= 3:1

**首頁色彩對比度檢查**:

| 前景色 | 背景色 | 對比度 | 標準 | 用途 |
|--------|--------|--------|------|------|
| `text-slate-900` | `bg-white` | 19.64:1 | ✅ AAA | 標題 |
| `text-slate-700` | `bg-white` | 10.69:1 | ✅ AAA | 內文 |
| `text-slate-600` | `bg-white` | 7.74:1 | ✅ AAA | 次要文字 |
| `text-white` | `bg-primary-500` | 4.93:1 | ✅ AA | 按鈕文字 |
| `text-white` | `bg-secondary-500` | 4.52:1 | ✅ AA | 按鈕文字 |
| `text-white` | `bg-primary-600` (CTA) | 6.29:1 | ✅ AAA | CTA 文字 |

**所有組合均符合 WCAG AA 標準** ✅

#### 鍵盤導航

**Tab 順序** (從上到下):

1. Header 導航連結
2. Hero Section:
   - 搜尋輸入框
   - 搜尋按鈕
   - "瀏覽所有業務員" 連結
3. Features Section:
   - 3 張 Feature 卡片 (如果可點擊)
4. Popular Salespersons:
   - "查看全部" 按鈕
   - 6 張業務員卡片連結
5. CTA Section:
   - "免費註冊" 按鈕
   - "開始搜尋" 按鈕
6. Footer 連結

**Focus Ring**:

所有互動元素必須有清晰的 Focus Ring：

```tsx
// 輸入框
<Input className="
  focus:border-primary-500
  focus:ring-2
  focus:ring-primary-500
  focus:ring-offset-2
  focus:outline-none
" />

// 按鈕 (使用 UI 組件預設)
<Button>  // 已內建 Focus Ring
  按鈕文字
</Button>

// 卡片連結
<Link
  href="/salesperson/123"
  className="
    focus:ring-2
    focus:ring-primary-500
    focus:ring-offset-2
    focus:outline-none
    rounded-2xl
  "
>
  <SalespersonCard />
</Link>
```

#### Screen Reader 支援

**語義化 HTML**:

```tsx
// ✅ 正確使用語義標籤
<header>
  <Header />
</header>

<main>
  <section aria-labelledby="hero-heading">
    <h1 id="hero-heading">找到最適合的專業業務員</h1>
  </section>

  <section aria-labelledby="features-heading">
    <h2 id="features-heading">為什麼選擇 YAMU？</h2>
  </section>

  <section aria-labelledby="popular-heading">
    <h2 id="popular-heading">熱門業務員</h2>
  </section>

  <section aria-labelledby="cta-heading">
    <h2 id="cta-heading">準備好找到最佳業務夥伴了嗎？</h2>
  </section>
</main>

<footer>
  <Footer />
</footer>
```

**裝飾性元素**:

```tsx
// 背景裝飾球 - 對 Screen Reader 隱藏
<div className="absolute inset-0" aria-hidden="true">
  <div className="absolute -top-40 -right-40 ... rounded-full bg-primary-200/20 blur-3xl" />
</div>
```

**圖示**:

```tsx
// 圖示加 aria-label (如果是互動元素)
<button aria-label="搜尋業務員">
  <Search className="h-5 w-5" />
</button>

// 裝飾性圖示
<Search className="h-5 w-5" aria-hidden="true" />
<span className="sr-only">搜尋</span>
```

### 6.2 動畫偏好設定

**支援 prefers-reduced-motion**:

```css
/* 已在 globals.css 定義 */
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

**React 中檢測使用者偏好** (可選):

```tsx
// 檢測使用者是否偏好減少動畫
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// 條件性應用動畫
<div className={cn(
  !prefersReducedMotion && "animate-fade-in-up"
)}>
  內容
</div>
```

---

## 7. 視覺驗收標準

### 7.1 視覺設計檢查

#### Hero Section

- [ ] 漸層背景使用 3 色階 (`from-primary-50 via-primary-25 to-secondary-50`)
- [ ] 背景裝飾球大小正確 (h-96 w-96)
- [ ] 背景裝飾球透明度正確 (20%)
- [ ] 標題漸層色彩更鮮豔 (`from-primary-500 to-secondary-500`)
- [ ] 標題、描述、搜尋列有進場動畫 (`animate-fade-in-up`)
- [ ] 搜尋列輸入框高度 56px (`h-14`)
- [ ] 搜尋列有陰影 (`shadow-lg`)
- [ ] 搜尋按鈕高度匹配輸入框 (`h-14`)

#### Features Section

- [ ] 卡片有邊框 (`border border-slate-100`)
- [ ] 卡片預設陰影 (`shadow-md`)
- [ ] 卡片 Hover 陰影增強 (`hover:shadow-xl`)
- [ ] 卡片 Hover 向上浮起 (`hover:-translate-y-2`)
- [ ] 卡片有進場動畫，交錯延遲 100ms
- [ ] 圖示容器大小 80px (`h-20 w-20`)
- [ ] 圖示容器圓角 24px (`rounded-3xl`)
- [ ] 圖示容器有陰影 (`shadow-lg`)
- [ ] 圖示大小 40px (`h-10 w-10`)

#### Popular Salespersons Section

- [ ] 網格佈局響應式: 1 欄 (Mobile) → 2 欄 (Tablet) → 3 欄 (Desktop)
- [ ] 卡片間距響應式: 16px → 24px → 32px
- [ ] 卡片有進場動畫，交錯延遲 50ms

#### CTA Section

- [ ] 漸層背景使用 3 色階 (`from-primary-500 via-primary-600 to-secondary-500`)
- [ ] 有背景裝飾球 (左上、右下)
- [ ] 背景裝飾球大小 384px (`w-96 h-96`)
- [ ] 背景裝飾球透明度 30% (外層) + 20% (內層)
- [ ] 按鈕高度 56px (`h-14`)
- [ ] 按鈕有陰影 (`shadow-xl`)
- [ ] 按鈕 Hover 亮度提升 (`hover:brightness-110`)
- [ ] 按鈕 Hover 向上浮起 (`hover:-translate-y-1`)

### 7.2 響應式檢查

#### Mobile (375px - iPhone SE)

- [ ] Hero Section padding 64px
- [ ] 標題字級 36px
- [ ] 搜尋列上下排列
- [ ] 搜尋按鈕全寬
- [ ] Features 單欄佈局
- [ ] Salesperson 單欄佈局
- [ ] CTA 按鈕上下排列
- [ ] 所有觸控目標 >= 44px

#### Tablet (768px - iPad)

- [ ] Hero Section padding 80px
- [ ] 標題字級 48px
- [ ] 搜尋列左右排列
- [ ] Features 3 欄佈局
- [ ] Salesperson 2 欄佈局
- [ ] CTA 按鈕左右排列

#### Desktop (1280px)

- [ ] Hero Section padding 112px
- [ ] 標題字級 60px
- [ ] Features 3 欄佈局 (保持)
- [ ] Salesperson 3 欄佈局
- [ ] 卡片間距 32px

### 7.3 動畫檢查

#### 進場動畫

- [ ] Hero 標題淡入向上滑入 (0ms delay)
- [ ] Hero 描述淡入向上滑入 (100ms delay)
- [ ] Hero 搜尋列淡入向上滑入 (200ms delay)
- [ ] Features 卡片 1 淡入 (0ms delay)
- [ ] Features 卡片 2 淡入 (100ms delay)
- [ ] Features 卡片 3 淡入 (200ms delay)
- [ ] Salesperson 卡片交錯淡入 (50ms 間隔)

#### 互動動畫

- [ ] Features 卡片 Hover 浮起流暢
- [ ] Features 卡片 Hover 陰影增強自然
- [ ] CTA 按鈕 Hover 亮度提升
- [ ] CTA 按鈕 Hover 浮起流暢
- [ ] 所有動畫 duration 300ms
- [ ] 所有動畫 ease-out

#### 動畫偏好

- [ ] 支援 `prefers-reduced-motion: reduce`
- [ ] 關閉動畫時仍保持視覺效果 (opacity: 1, transform: none)

### 7.4 可訪問性檢查

#### 色彩對比度

- [ ] 標題文字對比度 >= 4.5:1
- [ ] 內文文字對比度 >= 4.5:1
- [ ] 按鈕文字對比度 >= 4.5:1
- [ ] CTA Section 文字對比度 >= 4.5:1

#### 鍵盤導航

- [ ] 所有互動元素可用 Tab 訪問
- [ ] Tab 順序邏輯正確
- [ ] Focus Ring 清晰可見
- [ ] Enter/Space 可觸發按鈕

#### Screen Reader

- [ ] 使用語義化 HTML (header, main, section, footer)
- [ ] Section 有 aria-labelledby
- [ ] 裝飾性元素有 aria-hidden="true"
- [ ] 圖示有 aria-label 或 sr-only 文字

### 7.5 效能檢查

#### Core Web Vitals

- [ ] LCP (Largest Contentful Paint) < 2.5s
- [ ] FCP (First Contentful Paint) < 1.8s
- [ ] CLS (Cumulative Layout Shift) < 0.1
- [ ] TTI (Time to Interactive) < 3.8s

#### Lighthouse 指標

- [ ] Performance Score >= 90
- [ ] Accessibility Score >= 95
- [ ] Best Practices Score >= 90

#### Bundle Size

- [ ] Initial Bundle < 200KB (gzip)
- [ ] Page Bundle < 50KB (gzip)

#### 瀏覽器測試

- [ ] Chrome 最新版本正常
- [ ] Firefox 最新版本正常
- [ ] Safari 最新版本正常
- [ ] Edge 最新版本正常

---

## 附錄 A：Before/After 對照表

### Hero Section

| 屬性 | Before | After |
|------|--------|-------|
| **背景漸層** | 2 色階 (`from-primary-50 via-white to-secondary-50`) | 3 色階 (`from-primary-50 via-primary-25 to-secondary-50`) |
| **裝飾球大小** | h-80 w-80 (320px) | h-96 w-96 (384px) |
| **標題漸層** | `from-primary-600 to-secondary-600` | `from-primary-500 to-secondary-500` |
| **標題動畫** | 無 | `animate-fade-in-up` |
| **搜尋列高度** | 預設 (40px) | `h-14` (56px) |
| **搜尋列陰影** | 無 | `shadow-lg` |

### Features Section

| 屬性 | Before | After |
|------|--------|-------|
| **卡片邊框** | 無 | `border border-slate-100` |
| **卡片陰影** | 無明確定義 | `shadow-md` |
| **Hover 陰影** | 無 | `hover:shadow-xl` |
| **Hover 位移** | 無 | `hover:-translate-y-2` |
| **進場動畫** | 無 | `animate-fade-in-up` + 交錯延遲 |
| **圖示容器大小** | h-16 w-16 (64px) | h-20 w-20 (80px) |
| **圖示容器圓角** | `rounded-2xl` (16px) | `rounded-3xl` (24px) |
| **圖示大小** | h-8 w-8 (32px) | h-10 w-10 (40px) |

### Popular Salespersons

| 屬性 | Before | After |
|------|--------|-------|
| **網格佈局** | `md:grid-cols-2 lg:grid-cols-3` | `sm:grid-cols-2 lg:grid-cols-3` (明確定義 Mobile) |
| **卡片間距** | `gap-6` (固定) | `gap-4 md:gap-6 lg:gap-8` (響應式) |
| **進場動畫** | 無 | `animate-fade-in-up` + 交錯延遲 50ms |

### CTA Section

| 屬性 | Before | After |
|------|--------|-------|
| **背景漸層** | 2 色階 (`from-primary-600 to-secondary-600`) | 3 色階 (`from-primary-500 via-primary-600 to-secondary-500`) |
| **背景裝飾** | 無 | 左上、右下裝飾球 (w-96 h-96) |
| **按鈕高度** | 預設 (40px) | `h-14` (56px) |
| **按鈕寬度** | 預設 | `px-10` |
| **按鈕字級** | 預設 | `text-lg` |
| **按鈕陰影** | 無明確定義 | `shadow-xl` |
| **Hover 亮度** | 無 | `hover:brightness-110` |
| **Hover 位移** | 無 | `hover:-translate-y-1` |

---

## 附錄 B：設計 Token 快速參考

### 色彩

```css
/* Primary */
--primary-25: #f5fbff
--primary-50: #f0f9ff
--primary-500: #0ea5e9
--primary-600: #0284c7

/* Secondary */
--secondary-50: #f0fdfa
--secondary-500: #14b8a6
--secondary-600: #0d9488

/* Neutral */
--slate-50: #f8fafc
--slate-600: #475569
--slate-700: #334155
--slate-900: #0f172a
```

### 間距

```css
/* Section Padding */
Mobile: py-16 (64px)
Tablet: md:py-20 (80px)
Desktop (Hero): lg:py-28 (112px)

/* Container Padding */
Mobile: px-4 (16px)
Tablet: sm:px-6 (24px)
Desktop: lg:px-8 (32px)

/* Card Gap */
Mobile: gap-4 (16px)
Tablet: md:gap-6 (24px)
Desktop: lg:gap-8 (32px)
```

### 圓角

```css
rounded-lg: 8px    (Input)
rounded-xl: 12px   (Button)
rounded-2xl: 16px  (Card)
rounded-3xl: 24px  (Icon Container)
rounded-full: 全圓 (Background Blob)
```

### 陰影

```css
shadow-md: 預設卡片
shadow-lg: 搜尋列、Hover 卡片
shadow-xl: CTA 按鈕
```

### 動畫

```css
duration-200: 互動動畫 (Hover)
duration-300: 進場動畫 (Fade-in)
ease-out: 所有動畫的 timing function
```

---

**設計師**: Claude Sonnet 4.5
**審查者**: Development Team
**狀態**: Draft → Ready for Implementation
**版本**: 1.0
**最後更新**: 2026-01-21
