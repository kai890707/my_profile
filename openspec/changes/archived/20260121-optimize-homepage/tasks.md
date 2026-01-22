# 實作任務清單

**變更 ID**: 20260121-optimize-homepage
**類型**: Frontend Enhancement (純視覺優化)
**預估時間**: 1.5-2 小時

---

## 📋 任務總覽

| Phase | 任務數 | 預估時間 | 狀態 |
|-------|--------|----------|------|
| **Phase 1** | 2 | 15 分鐘 | 待執行 |
| **Phase 2** | 9 | 20 分鐘 | 待執行 |
| **Phase 3** | 6 | 20 分鐘 | 待執行 |
| **Phase 4** | 4 | 15 分鐘 | 待執行 |
| **Phase 5** | 5 | 20 分鐘 | 待執行 |
| **Phase 6** | 10 | 20 分鐘 | 待執行 |
| **總計** | **36** | **110 分鐘** | - |

---

## 🔧 Phase 1: 設置動畫系統 (15 分鐘)

### Task 1.1: 新增動畫 @keyframes
- [ ] 打開 `frontend/app/globals.css`
- [ ] 在檔案最後新增 fade-in-up 動畫定義
- [ ] 新增 fade-in-right 動畫定義 (可選)
- [ ] 新增 scale-in 動畫定義 (可選)
- [ ] 驗證: CSS 無錯誤

**檔案**: `frontend/app/globals.css`
**行數**: +60 行
**預估**: 10 分鐘

### Task 1.2: 新增動畫 Utility Classes
- [ ] 新增 .animate-fade-in-up 類別
- [ ] 新增 .animate-delay-100 ~ .animate-delay-600 類別
- [ ] 新增 prefers-reduced-motion 支援
- [ ] 驗證: npm run dev 啟動正常

**檔案**: `frontend/app/globals.css`
**行數**: +20 行
**預估**: 5 分鐘

---

## 🎨 Phase 2: Hero Section 優化 (20 分鐘)

### Task 2.1: 漸層背景升級
- [ ] Section 容器: 改為 3 色階漸層
  - 變更: `from-primary-50 via-white to-secondary-50`
  - 至: `from-primary-50 via-primary-25 to-secondary-50`
- [ ] 驗證: 漸層更豐富

**檔案**: `frontend/app/page.tsx`
**行號**: Line 68
**預估**: 2 分鐘

### Task 2.2: 背景裝飾球調整
- [ ] 右上裝飾球: 改為 h-96 w-96
- [ ] 左下裝飾球: 改為 h-96 w-96
- [ ] 驗證: 裝飾球更突出

**檔案**: `frontend/app/page.tsx`
**行號**: Line 71, 72
**預估**: 2 分鐘

### Task 2.3: 標題容器新增動畫
- [ ] max-w-4xl mx-auto text-center: 新增動畫類別
  - `animate-fade-in-up`
- [ ] 驗證: 標題有淡入向上動畫

**檔案**: `frontend/app/page.tsx`
**行號**: Line 76
**預估**: 2 分鐘

### Task 2.4: 標題文字優化
- [ ] h1: 新增更大的行高
  - 新增: `leading-tight`
- [ ] 驗證: 標題更清晰

**檔案**: `frontend/app/page.tsx`
**行號**: Line 77
**預估**: 1 分鐘

### Task 2.5: 標題漸層優化
- [ ] span (專業業務員): 改為 2 色漸層
  - 變更: `from-primary-600 to-secondary-600`
  - 至: `from-primary-500 to-secondary-500`
- [ ] 驗證: 漸層更鮮豔

**檔案**: `frontend/app/page.tsx`
**行號**: Line 79
**預估**: 2 分鐘

### Task 2.6: 描述文字新增動畫
- [ ] p (描述): 新增動畫 + 延遲
  - 新增: `animate-fade-in-up animate-delay-100`
- [ ] 驗證: 描述有延遲動畫

**檔案**: `frontend/app/page.tsx`
**行號**: Line 83
**預估**: 2 分鐘

### Task 2.7: 搜尋列容器新增動畫
- [ ] form: 新增動畫 + 延遲
  - 新增: `animate-fade-in-up animate-delay-200`
- [ ] 驗證: 搜尋列最後淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 88
**預估**: 2 分鐘

### Task 2.8: 搜尋列佈局調整
- [ ] div.flex: 改為響應式方向
  - 變更: `gap-2`
  - 至: `flex-col sm:flex-row gap-3`
- [ ] 驗證: Mobile 垂直排列

**檔案**: `frontend/app/page.tsx`
**行號**: Line 89
**預估**: 3 分鐘

### Task 2.9: Input 與 Button 優化
- [ ] Input: 新增高度和陰影
  - 新增: `h-14 shadow-lg`
- [ ] Button: 新增高度和響應式寬度
  - 新增: `h-14 w-full sm:w-auto sm:px-8`
- [ ] 驗證: 搜尋列更突出

**檔案**: `frontend/app/page.tsx`
**行號**: Line 90-91, 98-100
**預估**: 4 分鐘

---

## 🎴 Phase 3: Features Section 優化 (20 分鐘)

### Task 3.1: Section 標題新增動畫
- [ ] div.text-center: 新增動畫
  - 新增: `animate-fade-in-up`
- [ ] 驗證: 標題淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 116
**預估**: 2 分鐘

### Task 3.2: 卡片容器新增動畫
- [ ] div.grid: 新增動畫
  - 新增: `animate-fade-in-up animate-delay-100`
- [ ] 驗證: 卡片延遲淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 125
**預估**: 2 分鐘

### Task 3.3: Card 新增立體感
- [ ] Card: 新增邊框和陰影
  - 新增: `border border-slate-100 shadow-md`
  - 新增: `hover:shadow-xl hover:-translate-y-1`
  - 新增: `transition-all duration-300`
- [ ] 驗證: 卡片有立體感

**檔案**: `frontend/app/page.tsx`
**行號**: Line 127
**預估**: 4 分鐘

### Task 3.4: 卡片內容間距優化
- [ ] CardContent: 增加內距
  - 變更: `pt-8`
  - 至: `pt-10 pb-8 px-6`
- [ ] 驗證: 卡片內容不擁擠

**檔案**: `frontend/app/page.tsx`
**行號**: Line 128
**預估**: 2 分鐘

### Task 3.5: 圖示容器優化
- [ ] div (圖示容器): 增大尺寸和圓角
  - 變更: `h-16 w-16 rounded-2xl`
  - 至: `h-20 w-20 rounded-3xl`
- [ ] 驗證: 圖示更突出

**檔案**: `frontend/app/page.tsx`
**行號**: Line 129-130
**預估**: 3 分鐘

### Task 3.6: 圖示大小優化
- [ ] feature.icon: 增大圖示
  - 變更: `h-8 w-8`
  - 至: `h-10 w-10`
- [ ] 驗證: 圖示清晰可見

**檔案**: `frontend/app/page.tsx`
**行號**: Line 131
**預估**: 2 分鐘

### Task 3.7: 卡片標題優化
- [ ] h3: 增大字級和間距
  - 變更: `text-xl font-bold text-slate-900 mb-3`
  - 至: `text-2xl font-bold text-slate-900 mb-4`
- [ ] 驗證: 標題更醒目

**檔案**: `frontend/app/page.tsx`
**行號**: Line 132-133
**預估**: 2 分鐘

### Task 3.8: 卡片描述優化
- [ ] p: 增大字級
  - 變更: `text-slate-600`
  - 至: `text-base text-slate-600 leading-relaxed`
- [ ] 驗證: 描述更易讀

**檔案**: `frontend/app/page.tsx`
**行號**: Line 135-136
**預估**: 2 分鐘

### Task 3.9: 新增交錯動畫 (進階)
- [ ] 為每個 Card 新增不同的延遲
  - Card 1: `animate-delay-100`
  - Card 2: `animate-delay-200`
  - Card 3: `animate-delay-300`
- [ ] 驗證: 卡片交錯淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 126-140 (map function)
**預估**: 5 分鐘 (需要修改 map 邏輯)

---

## 👥 Phase 4: Popular Salespersons 優化 (15 分鐘)

### Task 4.1: Section 標題新增動畫
- [ ] div.flex: 新增動畫
  - 新增: `animate-fade-in-up`
- [ ] 驗證: 標題淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 148
**預估**: 2 分鐘

### Task 4.2: 網格容器響應式優化
- [ ] div.grid: 調整響應式欄數
  - 變更: `md:grid-cols-2 lg:grid-cols-3`
  - 至: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`
- [ ] 驗證: Mobile 單欄、Tablet 雙欄

**檔案**: `frontend/app/page.tsx`
**行號**: Line 166-167, 172-173
**預估**: 3 分鐘

### Task 4.3: 網格間距優化
- [ ] div.grid: 增加間距
  - 變更: `gap-6`
  - 至: `gap-6 md:gap-8`
- [ ] 驗證: Desktop 間距更寬敞

**檔案**: `frontend/app/page.tsx`
**行號**: Line 166-167, 172-173
**預估**: 2 分鐘

### Task 4.4: 卡片容器新增動畫
- [ ] div.grid (業務員列表): 新增動畫
  - 新增: `animate-fade-in-up animate-delay-100`
- [ ] 驗證: 卡片延遲淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 172
**預估**: 2 分鐘

### Task 4.5: SalespersonCard 新增交錯動畫 (進階)
- [ ] 為每個 SalespersonCard 新增不同延遲
  - 使用 index * 50ms 的方式
- [ ] 驗證: 卡片交錯淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 173-178 (map function)
**預估**: 6 分鐘 (需要修改 map 邏輯)

---

## 🎯 Phase 5: CTA Section 優化 (20 分鐘)

### Task 5.1: 漸層背景升級
- [ ] Section: 維持漸層不變 (已是雙色)
  - 檢查: `from-primary-600 to-secondary-600`
- [ ] 驗證: 漸層正常

**檔案**: `frontend/app/page.tsx`
**行號**: Line 192
**預估**: 1 分鐘

### Task 5.2: 新增背景裝飾
- [ ] Section 內新增背景裝飾元素
  - 在 container 前新增 2 個裝飾球
- [ ] 驗證: 背景更豐富

**檔案**: `frontend/app/page.tsx`
**行號**: Line 193 (container 前)
**預估**: 5 分鐘

### Task 5.3: 標題新增動畫
- [ ] h2: 新增動畫
  - 新增: `animate-fade-in-up`
- [ ] 驗證: 標題淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 195-196
**預估**: 2 分鐘

### Task 5.4: 描述新增動畫
- [ ] p: 新增動畫 + 延遲
  - 新增: `animate-fade-in-up animate-delay-100`
- [ ] 驗證: 描述延遲淡入

**檔案**: `frontend/app/page.tsx`
**行號**: Line 198-200
**預估**: 2 分鐘

### Task 5.5: 按鈕容器佈局優化
- [ ] div.flex: 改為響應式方向 + 增加間距
  - 變更: `gap-4`
  - 至: `gap-4 md:gap-6`
  - 變更: `justify-center`
  - 新增: `flex-col sm:flex-row`
- [ ] 驗證: Mobile 垂直排列

**檔案**: `frontend/app/page.tsx`
**行號**: Line 201
**預估**: 3 分鐘

### Task 5.6: 按鈕優化
- [ ] Button (主要按鈕): 增大尺寸 + Hover 效果
  - 新增: `h-14 px-10 text-lg shadow-xl`
  - 新增: `hover:brightness-110 hover:-translate-y-1 hover:shadow-2xl`
  - 新增: `transition-all duration-300`
  - 新增: `w-full sm:w-auto`
- [ ] Button (次要按鈕): 匹配主要按鈕
  - 新增: `h-14 px-10 text-lg`
  - 新增: `hover:-translate-y-1`
  - 新增: `w-full sm:w-auto`
- [ ] 驗證: 按鈕更突出

**檔案**: `frontend/app/page.tsx`
**行號**: Line 202-208
**預估**: 7 分鐘

---

## 🧪 Phase 6: 測試與驗證 (20 分鐘)

### Task 6.1: 視覺測試
- [ ] 檢查 Hero Section 視覺效果
- [ ] 檢查 Features Section 卡片立體感
- [ ] 檢查 Popular Salespersons 網格佈局
- [ ] 檢查 CTA Section 按鈕效果
- [ ] 驗證: 所有 Section 視覺正常

**工具**: 瀏覽器目視檢查
**預估**: 5 分鐘

### Task 6.2: 響應式測試
- [ ] Mobile (< 640px): 單欄、垂直按鈕
- [ ] Tablet (640px - 1024px): 雙欄
- [ ] Desktop (>= 1024px): 三欄
- [ ] 驗證: 3 個斷點正常

**工具**: Chrome DevTools (Responsive Mode)
**預估**: 5 分鐘

### Task 6.3: 動畫測試
- [ ] 檢查進場動畫流暢度
- [ ] 檢查交錯動畫效果
- [ ] 檢查 Hover 互動動畫
- [ ] 測試 prefers-reduced-motion
- [ ] 驗證: 動畫流暢無卡頓

**工具**: 瀏覽器 + 系統動畫設定
**預估**: 4 分鐘

### Task 6.4: TypeScript 與 ESLint 檢查
- [ ] 執行 `npm run typecheck`
- [ ] 執行 `npm run lint`
- [ ] 修復任何錯誤
- [ ] 驗證: 0 errors

**工具**: npm 指令
**預估**: 3 分鐘

### Task 6.5: 效能測試
- [ ] 執行 Lighthouse (Desktop)
- [ ] 執行 Lighthouse (Mobile)
- [ ] 檢查 LCP < 2.5s
- [ ] 檢查 FCP < 1.8s
- [ ] 檢查 CLS < 0.1
- [ ] 驗證: 效能指標達標

**工具**: Chrome DevTools > Lighthouse
**預估**: 3 分鐘

---

## 📝 任務執行順序建議

### 順序 A: 按 Phase 執行 (推薦)
```
Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5 → Phase 6
```

適合：系統化開發、團隊協作

### 順序 B: 按 Section 執行
```
Task 1.1-1.2 → Task 2.1-2.9 → Task 6.1
Task 3.1-3.9 → Task 6.1
Task 4.1-4.5 → Task 6.1
Task 5.1-5.6 → Task 6.1
Task 6.2-6.5
```

適合：快速看到每個 Section 的效果

---

## ✅ 完成檢查清單

### 程式碼檢查
- [ ] 所有 className 變更已完成
- [ ] 動畫系統已新增到 globals.css
- [ ] TypeScript 無錯誤
- [ ] ESLint 無錯誤

### 視覺檢查
- [ ] 4 個 Section 視覺效果正確
- [ ] 動畫流暢無卡頓
- [ ] 響應式 3 個斷點正常
- [ ] Hover 互動效果正常

### 效能檢查
- [ ] LCP < 2.5s
- [ ] FCP < 1.8s
- [ ] CLS < 0.1
- [ ] Lighthouse Performance >= 90

### 可訪問性檢查
- [ ] 色彩對比度 >= 4.5:1
- [ ] 鍵盤導航正常
- [ ] prefers-reduced-motion 支援
- [ ] Screen Reader 友善

---

## 📂 相關文件

- **UI/UX 規格**: `specs/frontend/ui-ux.md`
- **組件規格**: `specs/frontend/components.md`
- **實作指南**: `specs/frontend/implementation-guide.md`
- **驗證報告**: `specs/validation-report.md`

---

**建立時間**: 2026-01-21
**預估總時間**: 110 分鐘 (1.8 小時)
**實際時間**: (待填寫)
