# YAMU Design System

> 活潑親和的業務員搜尋平台 UI 設計規範

## 設計理念

YAMU 採用**活潑親和（Friendly）**的設計風格，強調：
- 🎨 **親切可愛**：圓潤的圓角、柔和的色彩
- ✨ **輕鬆愉快**：適度的動畫、活潑的配色
- 📱 **清晰易用**：良好的對比度、清晰的層次

---

## 1. 色彩系統

### 主色調（Primary - Sky Blue）
```
primary-50:  #f0f9ff  // 淺藍背景
primary-100: #e0f2fe  // 懸停背景
primary-200: #bae6fd  // 輕量元素
primary-300: #7dd3fc  // 禁用狀態
primary-400: #38bdf8  // 懸停狀態
primary-500: #0ea5e9  // 主要品牌色 ⭐
primary-600: #0284c7  // 按下狀態
primary-700: #0369a1  // 深色文字
primary-800: #075985  // 強調文字
primary-900: #0c4a6e  // 標題文字
```

### 次要色（Secondary - Teal）
```
secondary-50:  #f0fdfa  // 淺綠背景
secondary-100: #ccfbf1  // 成功通知背景
secondary-200: #99f6e4  // 輕量元素
secondary-300: #5eead4  // 裝飾元素
secondary-400: #2dd4bf  // 懸停狀態
secondary-500: #14b8a6  // 次要品牌色 ⭐
secondary-600: #0d9488  // 按下狀態
secondary-700: #0f766e  // 深色文字
secondary-800: #115e59  // 強調文字
secondary-900: #134e4a  // 標題文字
```

### 功能色

#### 成功（Success - Green）
```
success-50:  #f0fdf4
success-100: #dcfce7
success-500: #22c55e  // 主要成功色 ✅
success-600: #16a34a
success-700: #15803d
```

#### 警告（Warning - Amber）
```
warning-50:  #fffbeb
warning-100: #fef3c7
warning-500: #f59e0b  // 主要警告色 ⚠️
warning-600: #d97706
warning-700: #b45309
```

#### 錯誤（Error - Red）
```
error-50:  #fef2f2
error-100: #fee2e2
error-500: #ef4444  // 主要錯誤色 ❌
error-600: #dc2626
error-700: #b91c1c
```

#### 資訊（Info - Blue）
```
info-50:  #eff6ff
info-100: #dbeafe
info-500: #3b82f6  // 主要資訊色 ℹ️
info-600: #2563eb
info-700: #1d4ed8
```

### 中性色（Neutral - Slate）
```
slate-50:  #f8fafc  // 頁面背景
slate-100: #f1f5f9  // 卡片背景
slate-200: #e2e8f0  // 邊框
slate-300: #cbd5e1  // 分隔線
slate-400: #94a3b8  // 占位符
slate-500: #64748b  // 輔助文字
slate-600: #475569  // 次要文字
slate-700: #334155  // 主要文字
slate-800: #1e293b  // 標題
slate-900: #0f172a  // 重要標題
```

### 漸層（Gradients）
```
gradient-primary:   from-primary-400 to-primary-600
gradient-secondary: from-secondary-400 to-secondary-600
gradient-sunset:    from-primary-400 via-pink-400 to-secondary-400
gradient-ocean:     from-primary-500 via-blue-500 to-secondary-500
```

---

## 2. 字體系統

### 字體家族
```
sans: 'Inter', 'Noto Sans TC', sans-serif  // 主要字體
mono: 'Fira Code', monospace                // 程式碼字體
```

### 字體大小
```
xs:   0.75rem   (12px)  // 小標籤、時間戳記
sm:   0.875rem  (14px)  // 輔助文字、說明
base: 1rem      (16px)  // 正文 ⭐
lg:   1.125rem  (18px)  // 重點文字
xl:   1.25rem   (20px)  // 次標題
2xl:  1.5rem    (24px)  // 卡片標題
3xl:  1.875rem  (30px)  // 區塊標題
4xl:  2.25rem   (36px)  // 頁面標題
5xl:  3rem      (48px)  // 主要標題
```

### 字重
```
light:    300  // 輕量文字
normal:   400  // 正文 ⭐
medium:   500  // 強調
semibold: 600  // 次標題
bold:     700  // 標題、按鈕
```

### 行高
```
tight:   1.25   // 標題
normal:  1.5    // 正文 ⭐
relaxed: 1.75   // 長文閱讀
```

### 字距
```
tighter: -0.05em
tight:   -0.025em
normal:  0em      ⭐
wide:    0.025em
wider:   0.05em
```

---

## 3. 間距系統

基於 **4px 網格**系統：

```
0:    0px
0.5:  2px     // 超緊密間距
1:    4px     // 圖示間距
2:    8px     // 小間距
3:    12px    // 元素內間距
4:    16px    // 標準間距 ⭐
5:    20px    // 寬鬆間距
6:    24px    // 區塊間距
8:    32px    // 大區塊間距
10:   40px    // 區段間距
12:   48px    // 主要區段間距
16:   64px    // 超大間距
20:   80px    // 頁面級間距
```

---

## 4. 圓角系統

**親和風格**採用較大的圓角：

```
none: 0px
sm:   0.25rem  (4px)   // 小元素
base: 0.5rem   (8px)   // 標準按鈕
md:   0.75rem  (12px)  // 輸入框
lg:   1rem     (16px)  // 卡片 ⭐
xl:   1.25rem  (20px)  // 大卡片
2xl:  1.5rem   (24px)  // 對話框
3xl:  2rem     (32px)  // 主要容器
full: 9999px           // 圓形元素
```

---

## 5. 陰影系統

柔和、有層次的陰影：

```css
/* 微陰影 - 懸停提示 */
shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05)

/* 標準陰影 - 卡片 ⭐ */
shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -2px rgba(0, 0, 0, 0.1)

/* 中等陰影 - 懸浮卡片 */
shadow-md: 0 8px 12px -2px rgba(0, 0, 0, 0.1),
           0 4px 8px -4px rgba(0, 0, 0, 0.1)

/* 大陰影 - 對話框 */
shadow-lg: 0 16px 24px -4px rgba(0, 0, 0, 0.1),
           0 8px 16px -8px rgba(0, 0, 0, 0.1)

/* 超大陰影 - 模態框 */
shadow-xl: 0 24px 48px -12px rgba(0, 0, 0, 0.25)

/* 彩色陰影 - 按鈕 */
shadow-primary: 0 10px 25px -5px rgba(14, 165, 233, 0.4)
shadow-success: 0 10px 25px -5px rgba(34, 197, 94, 0.4)
shadow-error:   0 10px 25px -5px rgba(239, 68, 68, 0.4)
```

---

## 6. 動畫系統

### 過渡時間
```
duration-75:   75ms    // 極快
duration-100:  100ms   // 快速
duration-150:  150ms   // 標準 ⭐
duration-200:  200ms   // 中速
duration-300:  300ms   // 慢速
duration-500:  500ms   // 很慢
```

### 緩動函數
```
ease-linear:     linear
ease-in:         cubic-bezier(0.4, 0, 1, 1)
ease-out:        cubic-bezier(0, 0, 0.2, 1)  ⭐
ease-in-out:     cubic-bezier(0.4, 0, 0.2, 1)
```

### 動畫效果
```css
/* 浮起效果 */
hover:translate-y-[-2px] transition-all duration-200

/* 縮放效果 */
hover:scale-105 transition-transform duration-200

/* 淡入淡出 */
opacity-0 transition-opacity duration-300

/* 滑入 */
translate-x-[-100%] transition-transform duration-300
```

---

## 7. 響應式斷點

```
sm:  640px   // 手機橫屏
md:  768px   // 平板
lg:  1024px  // 小筆電
xl:  1280px  // 桌機 ⭐
2xl: 1536px  // 大螢幕
```

---

## 8. Z-Index 層級

```
z-0:     0     // 基礎層
z-10:    10    // 卡片
z-20:    20    // Dropdown
z-30:    30    // Sticky Header
z-40:    40    // Modal Overlay
z-50:    50    // Modal Content ⭐
z-60:    60    // Toast/Notification
z-70:    70    // Tooltip
```

---

## 9. 組件設計原則

### 按鈕
- 最小高度：40px (觸控友好)
- 圓角：lg (16px)
- 陰影：有彩色光暈
- Hover：向上浮起 + 變亮

### 輸入框
- 高度：44px
- 圓角：xl (20px)
- 邊框：2px (易識別)
- Focus：藍色光環

### 卡片
- 圓角：2xl (24px)
- 陰影：md (中等)
- 背景：白色
- Hover：陰影加深

### 頭像
- 圓形：rounded-full
- 尺寸：sm(32px), md(40px), lg(48px), xl(64px)
- 邊框：2px 白色邊框

---

## 10. 圖示系統

使用 **Lucide React**：
- 大小：sm(16px), base(20px), lg(24px)
- 線條粗細：2px
- 顏色：繼承父元素

---

## 使用範例

```tsx
// 主要按鈕
<button className="h-12 px-6 bg-gradient-to-r from-primary-500 to-primary-600
                   text-white font-semibold rounded-xl shadow-primary
                   hover:-translate-y-0.5 hover:brightness-110
                   transition-all duration-200">
  立即開始
</button>

// 卡片
<div className="bg-white rounded-2xl shadow-md p-6
                hover:shadow-lg transition-shadow duration-200">
  卡片內容
</div>

// 輸入框
<input className="h-11 w-full px-4 border-2 border-slate-200 rounded-xl
                  focus:border-primary-500 focus:ring-2 focus:ring-primary-500
                  transition-all duration-200" />
```

---

**版本**: 1.0.0
**更新日期**: 2026-01-09
**維護者**: YAMU Design Team
