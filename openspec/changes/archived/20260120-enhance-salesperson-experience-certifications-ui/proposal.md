# Proposal: 改善業務員頁面工作經驗與專業證照呈現

**日期**: 2026-01-20
**類型**: Frontend UI/UX 改善
**優先級**: High
**預估工時**: 2-3 小時

---

## 📋 需求摘要

改善業務員公開頁面 (`/salesperson/[id]`) 上工作經驗和專業證照的呈現方式，提升視覺吸引力、互動體驗和可讀性。

---

## 🎯 問題診斷

### 當前問題

基於需求訪談，當前呈現方式存在以下問題：

1. **排版不清晰，難以閱讀**
   - 工作經驗: 使用簡單的左邊框列表，缺乏視覺層次
   - 資訊密度過高，難以快速掃描關鍵資訊

2. **缺少視覺吸引力**
   - 設計過於樸素，缺乏現代化的視覺元素
   - 缺乏設計感，無法突出專業形象

3. **缺少互動功能**
   - 無法展開/收合詳細資訊
   - 缺少篩選和排序功能
   - 資訊呈現方式單一

4. **載入體驗不佳**
   - 缺少 Loading 狀態處理
   - 缺少骨架屏 (Skeleton Screen)
   - 缺少空狀態處理

---

## ✨ 改善目標

### 視覺設計目標

1. **時間軸設計 (工作經驗)**
   - 垂直時間軸呈現職涯發展歷程
   - 清晰的視覺層次和連接線
   - 現代化的卡片設計

2. **卡片列表設計 (專業證照)**
   - 精緻的證照卡片
   - 清晰的資訊架構
   - 視覺化的審核狀態

3. **完整的狀態處理**
   - Loading 狀態: 骨架屏動畫
   - 空狀態: 友善的提示訊息
   - Error 狀態: 清晰的錯誤提示

### 互動體驗目標

1. **展開/收合功能**
   - 預設顯示摘要資訊
   - 點擊展開查看完整內容
   - 流暢的過渡動畫

2. **篩選和排序**
   - 工作經驗: 依時間排序
   - 專業證照: 依發證日期排序
   - 可篩選已驗證/未驗證項目

3. **響應式設計**
   - Mobile: 單欄佈局
   - Tablet: 適應性佈局
   - Desktop: 最佳閱讀體驗

---

## 🔍 當前狀態分析

### 頁面位置
- 路徑: `frontend/app/salesperson/[id]/page.tsx`
- 類型: Next.js App Router 頁面

### 已渲染的資料

#### 工作經驗 (experiences)
```typescript
interface Experience {
  id: number;
  user_id: number;
  company: string;
  position: string;
  start_date: string;      // YYYY-MM-DD
  end_date: string | null;  // YYYY-MM-DD or null (至今)
  description: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}
```

**當前呈現方式**:
- 簡單的左邊框列表 (`border-l-2 border-primary-200`)
- 顯示: 職位、公司、起訖日期、描述、審核狀態
- 位置: Line 186-230

#### 專業證照 (certifications)
```typescript
interface Certification {
  id: number;
  user_id: number;
  name: string;
  issuer: string;
  issue_date: string;      // YYYY-MM-DD
  expiry_date: string | null;
  description: string | null;
  file_url: string | null;
  approval_status: 'pending' | 'approved' | 'rejected';
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}
```

**當前呈現方式**:
- 2欄網格卡片 (`grid md:grid-cols-2`)
- 顯示: 名稱、發證機構、日期、描述、審核狀態
- 位置: Line 232-280

### API 資料來源
- Endpoint: `/api/search/salesperson/{id}` (推測)
- Hook: `useSalespersonDetail(id)`
- 資料已包含 experiences 和 certifications 陣列
- **結論**: 不需要調整 Backend API

---

## 🎨 設計方案

### 1. 工作經驗 - 時間軸設計 (Timeline)

#### 設計特點
- 垂直時間軸，清晰呈現職涯發展
- 左側時間軸線，右側內容卡片
- 時間節點標記 (圓點)
- 現在/過去工作視覺區分

#### 視覺元素
```
[時間軸線]
   ├─ [圓點] ── [卡片] 職位 | 公司 | 日期
   │             └─ [展開按鈕] 顯示/隱藏詳細描述
   │
   ├─ [圓點] ── [卡片] ...
   │
   └─ [圓點] ── [卡片] ...
```

#### 互動功能
- **展開/收合**: 點擊展開按鈕顯示完整描述
- **驗證標記**: 已驗證項目顯示綠色勾選圖標
- **排序**: 依起始日期倒序排列 (最新在上)
- **空狀態**: "尚無工作經驗"

#### 響應式行為
- **Desktop (≥1024px)**: 時間軸在左，內容在右
- **Tablet (≥768px)**: 時間軸縮小，內容調整
- **Mobile (<768px)**: 時間軸簡化，內容全寬

### 2. 專業證照 - 卡片列表設計 (Card List)

#### 設計特點
- 精緻的證照卡片設計
- 徽章風格的視覺元素
- 清晰的資訊層級
- Hover 效果增加互動感

#### 視覺元素
```
┌─────────────────────────────────┐
│ [徽章圖標] 證照名稱       [✓已驗證]│
│                                 │
│ 發證機構                        │
│ 📅 發證日期 - 到期日期           │
│                                 │
│ [展開] 證照描述...               │
│                                 │
│ [查看證書] (如有 file_url)       │
└─────────────────────────────────┘
```

#### 互動功能
- **展開/收合**: 點擊展開按鈕顯示完整描述
- **證書查看**: 如有 file_url，提供查看按鈕
- **驗證標記**: 已驗證項目顯示徽章
- **排序**: 依發證日期倒序排列
- **篩選**: 可篩選已驗證/未驗證
- **空狀態**: "尚無專業證照"

#### 響應式行為
- **Desktop (≥1024px)**: 2欄網格
- **Tablet (≥768px)**: 2欄網格
- **Mobile (<768px)**: 單欄列表

### 3. 載入與狀態處理

#### Loading 狀態
```tsx
// 骨架屏動畫
<div className="animate-pulse">
  <div className="h-4 bg-slate-200 rounded w-3/4 mb-2" />
  <div className="h-3 bg-slate-200 rounded w-1/2" />
</div>
```

#### 空狀態
```tsx
// 友善的空狀態提示
<div className="text-center py-12">
  <Briefcase className="mx-auto h-12 w-12 text-slate-300 mb-4" />
  <p className="text-slate-500">尚無工作經驗</p>
</div>
```

#### Error 狀態
- 顯示錯誤訊息
- 提供重試按鈕
- 保持頁面結構完整

---

## 📊 開發範圍

### ✅ 需要開發

#### Frontend UI/UX 改善 (主要工作)
- **工作經驗時間軸組件** (`components/features/salesperson/experience-timeline.tsx`)
  - 時間軸佈局
  - 展開/收合邏輯
  - 排序功能
  - 空狀態處理
  - 骨架屏

- **專業證照卡片組件** (`components/features/salesperson/certification-cards.tsx`)
  - 卡片佈局
  - 展開/收合邏輯
  - 篩選功能
  - 排序功能
  - 空狀態處理
  - 骨架屏

- **更新業務員頁面** (`app/salesperson/[id]/page.tsx`)
  - 整合新組件
  - 保持現有功能
  - 響應式佈局
  - 測試

#### UI 設計變更
- 時間軸視覺設計
- 卡片樣式設計
- 互動動畫
- 響應式適配

### ❌ 不需要開發

- **Backend API**: 現有 API 已提供完整資料，不需調整
- **資料庫**: 不需要新增或修改資料表
- **架構調整**: 不涉及架構變更

---

## 📏 量化標準 (參考 metrics-standards.md)

### 效能指標

#### Frontend 效能
- **LCP (Largest Contentful Paint)**: < 2.5s
  - 業務員頁面載入到看到主要內容的時間
- **FCP (First Contentful Paint)**: < 1.8s
  - 首次渲染任何內容的時間
- **TTI (Time to Interactive)**: < 3.8s
  - 頁面可完全互動的時間
- **CLS (Cumulative Layout Shift)**: < 0.1
  - 避免佈局抖動

#### Bundle Size
- **Initial JS**: < 200KB (gzip)
  - 新組件不應顯著增加 bundle size
- **Initial CSS**: < 50KB (gzip)

### 程式碼品質

#### TypeScript
- **嚴格模式**: 啟用 strict mode
- **類型覆蓋**: 100% (所有 props 和 state 有類型定義)
- **編譯錯誤**: 0 errors

#### 組件品質
- **單一職責**: 每個組件只做一件事
- **可複用性**: 組件可在其他頁面使用
- **Props 驗證**: 使用 TypeScript interface 定義
- **Cyclomatic Complexity**: <= 10

### 測試覆蓋

#### Component Tests
- **覆蓋率目標**: >= 80%
- **測試項目**:
  - 渲染測試 (有/無資料)
  - 展開/收合互動測試
  - 排序功能測試
  - 篩選功能測試
  - 空狀態測試
  - Loading 狀態測試

#### 響應式測試
- **測試裝置**:
  - Mobile: 375px, 414px
  - Tablet: 768px, 1024px
  - Desktop: 1280px, 1920px
- **測試項目**:
  - 佈局是否正確
  - 內容是否可見
  - 互動是否正常

### 可訪問性 (Accessibility)

#### WCAG AA 標準
- **色彩對比**: >= 4.5:1 (文字與背景)
- **鍵盤導航**: 100% 可操作
  - Tab 鍵可訪問所有互動元素
  - Enter/Space 可觸發按鈕
- **Screen Reader**: 完全支援
  - 使用語義化 HTML
  - 提供 ARIA 標籤

#### 互動回饋
- **Hover 狀態**: 所有可點擊元素有 hover 效果
- **Focus 狀態**: 所有互動元素有 focus ring
- **Loading 狀態**: 提供視覺回饋
- **Error 狀態**: 清晰的錯誤訊息

---

## 🎯 成功標準 (可測量、可驗證)

### 功能完整性

#### 必須實現
- ✅ 工作經驗以時間軸形式呈現
- ✅ 專業證照以卡片列表呈現
- ✅ 支援展開/收合詳細資訊
- ✅ 提供 Loading 狀態和骨架屏
- ✅ 提供空狀態處理
- ✅ 支援篩選和排序
- ✅ 完全響應式 (Mobile/Tablet/Desktop)

#### 視覺設計
- ✅ 設計符合 Design System 規範 (參考 `frontend/docs/design-system.md`)
- ✅ 視覺層次清晰，易於閱讀
- ✅ 現代化、專業的視覺呈現
- ✅ 流暢的過渡動畫

#### 使用者體驗
- ✅ 互動流暢，無卡頓
- ✅ 載入體驗優雅
- ✅ 錯誤處理友善
- ✅ 空狀態提示清晰

### 效能達標

- ✅ LCP < 2.5s
- ✅ FCP < 1.8s
- ✅ TTI < 3.8s
- ✅ CLS < 0.1
- ✅ Bundle Size 增加 < 50KB

### 品質達標

- ✅ TypeScript: 0 errors
- ✅ ESLint: 0 errors
- ✅ Component Tests: >= 80% 覆蓋率
- ✅ 響應式測試: 所有裝置通過
- ✅ 可訪問性: WCAG AA 100% 符合

---

## 📂 產出清單

### 新增檔案

1. **工作經驗時間軸組件**
   - `components/features/salesperson/experience-timeline.tsx`
   - `components/features/salesperson/experience-item.tsx` (時間軸項目)

2. **專業證照卡片組件**
   - `components/features/salesperson/certification-cards.tsx`
   - `components/features/salesperson/certification-card.tsx` (單張卡片)

3. **共用組件** (如需要)
   - `components/ui/timeline.tsx` (可複用的時間軸組件)

4. **測試檔案**
   - `components/features/salesperson/__tests__/experience-timeline.test.tsx`
   - `components/features/salesperson/__tests__/certification-cards.test.tsx`

### 修改檔案

1. **業務員頁面**
   - `app/salesperson/[id]/page.tsx`
   - 整合新組件
   - 保持現有功能

2. **樣式檔案** (如需要)
   - 新增自定義動畫
   - 調整響應式斷點

---

## 🔄 開發流程

### Phase 1: 規格撰寫 (30-45 分鐘)
1. UI/UX 設計規格
2. 組件規格 (Props, State, 互動)
3. API 整合規格 (確認資料流)
4. 狀態管理規格 (Local State)

### Phase 2: 開發實作 (1.5-2 小時)
1. 開發工作經驗時間軸組件
2. 開發專業證照卡片組件
3. 整合到業務員頁面
4. 響應式調整

### Phase 3: 測試驗證 (30 分鐘)
1. Component Tests
2. 響應式測試
3. 可訪問性測試
4. 效能測試

### Phase 4: 規格歸檔 (10 分鐘)
1. 歸檔到 `openspec/specs/frontend/`
2. 更新 UI 組件規格
3. 移動到 archived

---

## ⚠️ 風險與限制

### 技術風險

1. **效能風險**: 如果經驗/證照數量過多，可能影響效能
   - 緩解: 實作虛擬滾動或分頁
   - 緩解: 預設只顯示前 N 項，其餘展開查看

2. **相容性風險**: 舊版瀏覽器可能不支援某些 CSS 特性
   - 緩解: 提供 fallback 樣式
   - 緩解: 漸進式增強 (Progressive Enhancement)

### 開發限制

1. **不改動 Backend**: 必須使用現有 API 資料結構
2. **保持向後相容**: 現有功能不能受影響
3. **遵循 Design System**: 不能偏離既定設計規範

---

## 📝 備註

### 設計參考
- 參考 LinkedIn 工作經驗時間軸
- 參考 Dribbble 證照卡片設計
- 遵循 Material Design 互動原則

### 技術考量
- 使用 Tailwind CSS 實現樣式
- 使用 Framer Motion 實現動畫 (可選)
- 使用 React Hook Form 處理篩選表單 (可選)

### 後續優化方向
- 新增分享功能
- 新增列印友善版本
- 新增 PDF 匯出功能

---

## ✅ 開發範圍判斷結果

```json
{
  "backend": false,
  "frontend": true,
  "ui_design": true,
  "architecture": false,
  "database": false,
  "priority": "high",
  "estimated_hours": "2-3",
  "complexity": "medium"
}
```

### 說明

- **Backend**: ❌ 不需要，API 資料已完整
- **Frontend**: ✅ 主要工作，UI/UX 重新設計
- **UI Design**: ✅ 需要時間軸和卡片設計
- **Architecture**: ❌ 不涉及架構變更
- **Database**: ❌ 不需要資料庫變更
- **Priority**: High (提升使用者體驗)
- **Estimated Hours**: 2-3 小時 (純前端開發)
- **Complexity**: Medium (涉及組件設計和互動)

---

## 📞 審核與確認

### 需要用戶確認

1. ✅ 時間軸設計是否符合期望？
2. ✅ 卡片設計是否符合期望？
3. ✅ 互動功能是否完整？
4. ✅ 響應式設計是否必要？
5. ✅ 是否需要篩選和排序功能？

### 下一步

用戶確認 Proposal 後，將執行：
1. **Phase 2: 自動規格化** (Frontend UI/UX 規格)
2. **Phase 3: 自動開發** (React 組件實作)
3. **Phase 4: 自動測試** (Component + E2E 測試)
4. **Phase 5: 自動歸檔** (規格歸檔)
5. **Phase 6: 自動 Git 操作** (Commit + PR)

---

**建立日期**: 2026-01-20
**建立者**: Claude Sonnet 4.5 (AUTO-DEVELOP)
**版本**: 1.0
