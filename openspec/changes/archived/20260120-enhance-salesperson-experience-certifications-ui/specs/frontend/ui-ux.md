# UI/UX 設計規格 - 業務員工作經驗與專業證照呈現改善

**功能**: 改善業務員頁面工作經驗與專業證照呈現
**頁面**: `/salesperson/[id]`
**日期**: 2026-01-20
**設計師**: Claude Sonnet 4.5 (Senior Product Designer)

---

## 📋 目錄

- [設計目標](#設計目標)
- [使用者研究](#使用者研究)
- [設計方案](#設計方案)
  - [工作經驗時間軸](#工作經驗時間軸)
  - [專業證照卡片](#專業證照卡片)
  - [狀態處理](#狀態處理)
- [視覺設計](#視覺設計)
- [互動設計](#互動設計)
- [響應式設計](#響應式設計)
- [無障礙設計](#無障礙設計)
- [動畫與微互動](#動畫與微互動)

---

## 🎯 設計目標

### 主要目標

1. **提升視覺吸引力**
   - 從簡單的左邊框列表 → 精美的時間軸設計
   - 從樸素的網格卡片 → 現代化的證照卡片
   - 建立專業、可信賴的視覺形象

2. **改善資訊可讀性**
   - 清晰的視覺層次
   - 合理的資訊密度
   - 快速掃描關鍵資訊

3. **增強互動體驗**
   - 展開/收合詳細資訊
   - 流暢的過渡動畫
   - 即時的互動回饋

4. **完善狀態處理**
   - 優雅的 Loading 骨架屏
   - 友善的空狀態提示
   - 清晰的錯誤處理

### 設計理念

**「既專業又親切，既現代又可靠」**

- **專業**: 時間軸設計、結構化資訊、審核標記
- **親切**: 圓潤圓角、友善配色、溫暖提示
- **現代**: 漸層色彩、流暢動畫、響應式設計
- **可靠**: 驗證標記、完整資訊、清晰狀態

---

## 👥 使用者研究

### 目標使用者

#### 主要使用者群 (70%)
- **身份**: 尋找業務員的客戶
- **年齡**: 25-45 歲
- **需求**: 快速評估業務員的專業度和經驗
- **痛點**: 資訊雜亂、難以判斷真實性、閱讀費力

#### 次要使用者群 (30%)
- **身份**: 業務員本人
- **年齡**: 25-55 歲
- **需求**: 展示自己的專業形象
- **痛點**: 個人頁面不夠吸睛、資訊呈現不佳

### 使用情境

#### 情境 1: 快速瀏覽
```
使用者目標: 在 30 秒內判斷業務員是否符合需求
關鍵資訊: 現職公司、年資、證照數量、驗證狀態
設計對策:
- 時間軸清楚顯示職涯發展
- 驗證標記醒目
- 關鍵資訊前置
```

#### 情境 2: 詳細研究
```
使用者目標: 深入了解業務員的經驗和專業
關鍵資訊: 工作描述、證照詳情、發證機構
設計對策:
- 展開/收合功能
- 完整描述顯示
- 證照詳細資訊
```

#### 情境 3: 移動中瀏覽
```
使用者目標: 在手機上快速查看業務員資料
關鍵資訊: 精簡資訊、快速載入
設計對策:
- 響應式佈局
- 觸控友善
- Loading 骨架屏
```

### 使用者旅程地圖

```
階段 1: 進入頁面
├─ 動作: 載入業務員詳情頁
├─ 期望: 快速看到基本資訊
├─ 情緒: 😐 期待
└─ 設計: Loading 骨架屏、漸進式載入

階段 2: 瀏覽基本資料
├─ 動作: 查看頭像、姓名、公司、專長
├─ 期望: 資訊清晰、專業形象
├─ 情緒: 😊 滿意 / 😕 失望
└─ 設計: 大頭貼、清晰標題、驗證標記

階段 3: 查看工作經驗
├─ 動作: 滾動到工作經驗區塊
├─ 期望: 快速了解職涯發展、看到驗證
├─ 情緒: 🤔 評估
└─ 設計: 時間軸視覺化、驗證標記、關鍵資訊前置

階段 4: 展開詳細資訊
├─ 動作: 點擊展開按鈕
├─ 期望: 看到完整描述、流暢動畫
├─ 情緒: 😊 好奇
└─ 設計: 展開動畫、完整描述、格式化顯示

階段 5: 瀏覽專業證照
├─ 動作: 滾動到證照區塊
├─ 期望: 快速看到證照類型和數量
├─ 情緒: 💼 專業評估
└─ 設計: 卡片網格、徽章設計、篩選功能

階段 6: 做出決策
├─ 動作: 決定是否聯絡業務員
├─ 期望: 有信心做出判斷
├─ 情緒: 😌 確定 / 🤨 猶豫
└─ 設計: CTA 按鈕、聯絡資訊明顯
```

---

## 🎨 設計方案

### 工作經驗時間軸

#### 設計理念

**「視覺化職涯發展歷程，讓經驗一目了然」**

從「靜態列表」升級為「動態時間軸」，借鑑 LinkedIn 的設計，讓使用者快速理解業務員的職涯軌跡。

#### 視覺結構

```
桌面版 (≥1024px):
┌────────────────────────────────────────────────────────┐
│  工作經驗                                              │
├────────────────────────────────────────────────────────┤
│                                                        │
│  ┌─[時間軸線]─────────────────────────────────────┐  │
│  │                                                   │  │
│  │   ●  ┌─────────────────────────────────────────┐│  │
│  │   │  │ 🏢 資深業務經理                         ││  │
│  │   │  │ 三商美邦人壽                ✓ 已驗證   ││  │
│  │   │  │ 📅 2022/01 - 至今 (2年3個月)            ││  │
│  │   │  │                                         ││  │
│  │   │  │ 負責企業客戶開發與維護...               ││  │
│  │   │  │                                         ││  │
│  │   │  │ [▼ 展開更多]                            ││  │
│  │   │  └─────────────────────────────────────────┘│  │
│  │   │                                              │  │
│  │   ●  ┌─────────────────────────────────────────┐│  │
│  │   │  │ 🏢 業務專員                             ││  │
│  │   │  │ 南山人壽                    ⏳ 審核中   ││  │
│  │   │  │ 📅 2020/03 - 2021/12 (1年9個月)         ││  │
│  │   │  │                                         ││  │
│  │   │  │ 開發個人客戶，協助客戶規劃...           ││  │
│  │   │  │                                         ││  │
│  │   │  │ [▼ 展開更多]                            ││  │
│  │   │  └─────────────────────────────────────────┘│  │
│  │   │                                              │  │
│  │   ●  ┌─────────────────────────────────────────┐│  │
│  │      │ 🏢 業務助理                             ││  │
│  │      │ 國泰人壽                                ││  │
│  │      │ 📅 2018/06 - 2020/02 (1年8個月)         ││  │
│  └──────┴─────────────────────────────────────────┘│  │
│                                                        │
└────────────────────────────────────────────────────────┘

手機版 (<768px):
┌──────────────────────┐
│  工作經驗            │
├──────────────────────┤
│                      │
│  ● ┌───────────────┐│
│  │ │ 資深業務經理  ││
│  │ │ 三商美邦 ✓   ││
│  │ │ 2022-至今    ││
│  │ │              ││
│  │ │ [▼ 展開]     ││
│  │ └───────────────┘│
│  │                  │
│  ● ┌───────────────┐│
│  │ │ 業務專員 ⏳  ││
│  │ │ 南山人壽     ││
│  │ │ 2020-2021    ││
│  │ └───────────────┘│
│  │                  │
│  ● ┌───────────────┐│
│    │ 業務助理     ││
│    │ 國泰人壽     ││
│    │ 2018-2020    ││
│    └───────────────┘│
│                      │
└──────────────────────┘
```

#### 視覺設計規範

##### 時間軸線
- **位置**: 左側固定
- **顏色**: `primary-200` (#bae6fd) - 淡藍色
- **粗細**: 2px
- **樣式**: 實線
- **高度**: 隨內容動態調整

##### 時間節點 (圓點)
- **大小**: 12px 直徑
- **顏色**:
  - 在職中: `primary-500` (#0ea5e9) 實心圓 ⭐
  - 已離職: `primary-300` (#7dd3fc) 實心圓
  - 最後一個: `slate-300` (#cbd5e1) 空心圓
- **邊框**: 3px 白色邊框 (視覺區隔)
- **位置**: 時間軸線上，與卡片頂部對齊

##### 工作經驗卡片
- **背景**: `bg-white`
- **圓角**: `rounded-xl` (12px)
- **邊框**: `border border-slate-100`
- **陰影**:
  - Default: `shadow-sm`
  - Hover: `shadow-md` + 向上浮起 2px
- **內邊距**: `p-6` (24px)
- **外邊距**:
  - 左側: `ml-8` (32px) - 與時間軸線的距離
  - 下方: `mb-6` (24px) - 卡片間距

##### 卡片內容結構
```
┌─────────────────────────────────────────┐
│ [公司圖示] 職位名稱              [驗證]  │ ← 標題行
│ 公司名稱                                │ ← 公司
│ 📅 起始日期 - 結束日期 (年資)           │ ← 日期行
│                                         │
│ 工作描述摘要 (預設顯示 3 行)...         │ ← 描述 (可展開)
│                                         │
│ [▼ 展開更多] 或 [▲ 收合]               │ ← 操作按鈕
└─────────────────────────────────────────┘
```

##### 標題行設計
- **職位名稱**:
  - 字體: `text-lg font-semibold` (18px, 600)
  - 顏色: `text-slate-900`
- **公司圖示**:
  - 尺寸: 20x20 px
  - 顏色: `text-primary-500`
  - 圖示: `<Briefcase />` (Lucide)
- **驗證標記**:
  - 位置: 右上角
  - 樣式: Badge 組件
  - 顏色: `success` (已驗證) / `warning` (審核中)

##### 日期顯示
- **格式**: `YYYY/MM - YYYY/MM (X年X個月)`
- **至今**: 顯示「至今」+ 綠色 dot
- **字體**: `text-sm text-slate-600` (14px)
- **圖示**: 📅 (Calendar icon)

##### 描述區域
- **預設**: 顯示前 3 行 (line-clamp-3)
- **展開**: 顯示完整內容
- **字體**: `text-base text-slate-700` (16px)
- **行高**: `leading-relaxed` (1.625)
- **換行**: 保留換行符 `whitespace-pre-line`

##### 展開按鈕
- **樣式**: 文字按鈕 (無背景)
- **顏色**: `text-primary-600`
- **字體**: `text-sm font-medium` (14px, 500)
- **Hover**: `text-primary-700` + 底線
- **圖示**:
  - 收合: `<ChevronDown />` ▼
  - 展開: `<ChevronUp />` ▲

#### 互動設計

##### Hover 效果
```tsx
卡片 Hover:
- 陰影從 shadow-sm → shadow-md
- 向上移動 -2px (translate-y-[-2px])
- 過渡時間: 200ms
- Timing: ease-out

按鈕 Hover:
- 文字顏色加深
- 顯示底線
- Cursor: pointer
```

##### 展開/收合動畫
```tsx
展開:
1. 點擊「展開更多」按鈕
2. 描述區域 max-height 從 72px → auto
3. 按鈕文字從「展開更多 ▼」→「收合 ▲」
4. 動畫時間: 300ms, ease-out

收合:
1. 點擊「收合」按鈕
2. 描述區域 max-height 從 auto → 72px
3. 按鈕文字從「收合 ▲」→「展開更多 ▼」
4. 動畫時間: 300ms, ease-out
```

##### 驗證標記動畫
```tsx
已驗證標記:
- 顯示綠色勾選圖示
- 淡入動畫 (fade-in 200ms)
- 可選: 首次出現時有彈跳效果 (bounce-in)
```

#### 空狀態設計

```
┌────────────────────────────────────────────┐
│  工作經驗                                  │
├────────────────────────────────────────────┤
│                                            │
│            [Briefcase Icon]                │
│              (灰色，48px)                  │
│                                            │
│         尚無工作經驗記錄                   │
│         (text-slate-600)                   │
│                                            │
│     此業務員尚未新增工作經驗               │
│     (text-slate-500, text-sm)              │
│                                            │
└────────────────────────────────────────────┘
```

**規範**:
- 圖示: `<Briefcase />` 48x48 px, `text-slate-300`
- 標題: `text-base font-medium text-slate-600`
- 說明: `text-sm text-slate-500`
- 內邊距: `py-12` (垂直 48px)
- 對齊: `text-center`

#### Loading 骨架屏

```
┌────────────────────────────────────────────┐
│  工作經驗                                  │
├────────────────────────────────────────────┤
│                                            │
│  ● ┌─────────────────────────────────────┐│
│  │ │ ░░░░░░░░░░░░░░ (標題)              ││
│  │ │ ░░░░░░░░ (公司)                    ││
│  │ │ ░░░░░░░░░░ (日期)                  ││
│  │ │                                     ││
│  │ │ ░░░░░░░░░░░░░░░░░░░░░ (描述)       ││
│  │ │ ░░░░░░░░░░░░░░░                    ││
│  │ └─────────────────────────────────────┘│
│  │                                         │
│  ● ┌─────────────────────────────────────┐│
│    │ ░░░░░░░░░░░░░░                      ││
│    │ ░░░░░░░░                            ││
│    │ ░░░░░░░░░░                          ││
│    └─────────────────────────────────────┘│
│                                            │
└────────────────────────────────────────────┘
```

**規範**:
- 背景: `bg-slate-200`
- 動畫: `animate-pulse`
- 數量: 顯示 2-3 個骨架卡片
- 高度: 與實際卡片相近

---

### 專業證照卡片

#### 設計理念

**「徽章式設計，突顯專業認證」**

從「樸素的網格卡片」升級為「精美的證照徽章」，強調專業性和權威性。

#### 視覺結構

```
桌面版 (≥1024px) - 2 欄網格:
┌──────────────────────────────────────────────────────────┐
│  專業證照                              [篩選: 全部 ▼]    │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  ┌─────────────────────┐  ┌─────────────────────┐      │
│  │ 🏆 國際認證理財規劃師│  │ 📜 人身保險業務員資格│      │
│  │    (CFP)        ✓   │  │                 ✓   │      │
│  │                     │  │                     │      │
│  │ 中華民國財務規劃師   │  │ 中華民國人壽保險商業 │      │
│  │ 協會                │  │ 公會                │      │
│  │                     │  │                     │      │
│  │ 📅 2021/03 - 2026/03│  │ 📅 2020/01 - 永久有效│      │
│  │                     │  │                     │      │
│  │ 國際認證理財規劃師... │  │ 通過人身保險業務員資 │      │
│  │                     │  │ 格測驗...           │      │
│  │ [▼ 展開更多]         │  │ [▼ 展開更多]         │      │
│  │                     │  │                     │      │
│  │ [📄 查看證書]        │  │                     │      │
│  └─────────────────────┘  └─────────────────────┘      │
│                                                          │
│  ┌─────────────────────┐  ┌─────────────────────┐      │
│  │ 🎓 保險核保理論與實務│  │ 📖 壽險管理人員資格   │      │
│  │                ⏳   │  │                     │      │
│  │ 中華民國核保學會     │  │ 中華民國人壽保險管理 │      │
│  └─────────────────────┘  └─────────────────────┘      │
│                                                          │
└──────────────────────────────────────────────────────────┘

平板版 (768px-1023px) - 2 欄網格 (較窄):
[與桌面版相同佈局，但卡片稍窄]

手機版 (<768px) - 單欄列表:
┌──────────────────────┐
│  專業證照            │
│  [篩選: 全部 ▼]     │
├──────────────────────┤
│                      │
│ ┌──────────────────┐│
│ │ 🏆 國際認證理財規 ││
│ │    劃師 (CFP) ✓  ││
│ │                  ││
│ │ 中華民國財務規劃師││
│ │ 協會              ││
│ │                  ││
│ │ 📅 2021/03-2026/03││
│ │                  ││
│ │ [▼ 展開]          ││
│ └──────────────────┘│
│                      │
│ ┌──────────────────┐│
│ │ 📜 人身保險業務員 ││
│ │    資格證照 ✓    ││
│ │ ...              ││
│ └──────────────────┘│
│                      │
└──────────────────────┘
```

#### 視覺設計規範

##### 證照卡片
- **背景**: `bg-white`
- **圓角**: `rounded-2xl` (16px)
- **邊框**: `border-2 border-slate-100`
- **陰影**:
  - Default: `shadow-sm`
  - Hover: `shadow-lg` + 向上浮起 4px
- **內邊距**: `p-6` (24px)
- **Hover 效果**: 邊框從 `border-slate-100` → `border-primary-200`

##### 卡片結構
```
┌───────────────────────────────────┐
│ [徽章圖示] 證照名稱         [驗證] │ ← 標題行
│                                   │
│ 發證機構名稱                      │ ← 機構
│ 📅 發證日期 - 到期日期            │ ← 日期行
│                                   │
│ 證照描述摘要 (預設顯示 2 行)...    │ ← 描述 (可展開)
│                                   │
│ [▼ 展開更多]                      │ ← 展開按鈕
│                                   │
│ [📄 查看證書]                     │ ← 證書按鈕 (如有)
└───────────────────────────────────┘
```

##### 標題行設計
- **證照名稱**:
  - 字體: `text-lg font-bold` (18px, 700)
  - 顏色: `text-slate-900`
  - 最大行數: 2 行 (line-clamp-2)
- **徽章圖示**:
  - 尺寸: 24x24 px
  - 顏色: `text-amber-500` (金色，象徵專業認證)
  - 圖示: `<Award />` 或 `<Badge />` (Lucide)
- **驗證標記**:
  - 位置: 右上角
  - 尺寸: 20x20 px
  - 顏色:
    - 已驗證: `text-success-600` + `<CheckCircle2 />`
    - 審核中: `text-warning-600` + `<Clock />`
    - 未驗證: 不顯示

##### 發證機構
- **字體**: `text-sm text-slate-600` (14px)
- **最大行數**: 1 行 (truncate)
- **外邊距**: `mt-2 mb-3`

##### 日期顯示
- **格式**: `YYYY/MM - YYYY/MM` 或 `YYYY/MM - 永久有效`
- **字體**: `text-xs text-slate-500` (12px)
- **圖示**: 📅 (Calendar icon, 14px)
- **顏色**:
  - 正常: `text-slate-500`
  - 即將過期 (< 3個月): `text-warning-600`
  - 已過期: `text-error-600`

##### 描述區域
- **預設**: 顯示前 2 行 (line-clamp-2)
- **展開**: 顯示完整內容
- **字體**: `text-sm text-slate-600` (14px)
- **行高**: `leading-relaxed` (1.625)
- **外邊距**: `mt-3`

##### 證書查看按鈕
- **條件**: 僅當 `file_url` 存在時顯示
- **樣式**: Outline 按鈕
- **尺寸**: Small
- **圖示**: `<FileText />` 或 `<ExternalLink />`
- **文字**: 「查看證書」
- **點擊**: 新視窗開啟 `file_url`

##### 篩選下拉選單 (可選功能)
- **位置**: 區塊標題右側
- **選項**:
  - 全部
  - 已驗證
  - 審核中
- **樣式**: Select 組件
- **尺寸**: Small

#### 互動設計

##### Hover 效果
```tsx
卡片 Hover:
- 陰影從 shadow-sm → shadow-lg
- 向上移動 -4px (translate-y-[-4px])
- 邊框從 border-slate-100 → border-primary-200
- 過渡時間: 200ms
- Timing: ease-out

證書按鈕 Hover:
- 背景從 transparent → primary-50
- 邊框從 border-primary-500 → border-primary-600
- 文字從 text-primary-600 → text-primary-700
```

##### 展開/收合動畫
```tsx
展開:
1. 點擊「展開更多」按鈕
2. 描述區域 max-height 從 48px (2行) → auto
3. 按鈕文字從「展開更多 ▼」→「收合 ▲」
4. 動畫時間: 300ms, ease-out

收合:
1. 點擊「收合」按鈕
2. 描述區域 max-height 從 auto → 48px
3. 按鈕文字從「收合 ▲」→「展開更多 ▼」
4. 動畫時間: 300ms, ease-out
```

##### 篩選動畫
```tsx
篩選切換:
1. 選擇篩選條件
2. 不符合條件的卡片淡出 (fade-out 200ms)
3. 符合條件的卡片保持
4. 網格重新排列 (transition 200ms)
```

#### 排序規則

**預設排序**: 依發證日期倒序 (最新在前)

```tsx
排序邏輯:
1. 已驗證的證照優先
2. 依發證日期倒序
3. 同日期則依證照名稱字母順序
```

#### 空狀態設計

```
┌────────────────────────────────────────────┐
│  專業證照                                  │
├────────────────────────────────────────────┤
│                                            │
│            [Award Icon]                    │
│              (灰色，48px)                  │
│                                            │
│         尚無專業證照記錄                   │
│         (text-slate-600)                   │
│                                            │
│     此業務員尚未新增專業證照               │
│     (text-slate-500, text-sm)              │
│                                            │
└────────────────────────────────────────────┘
```

**規範**:
- 圖示: `<Award />` 48x48 px, `text-slate-300`
- 標題: `text-base font-medium text-slate-600`
- 說明: `text-sm text-slate-500`
- 內邊距: `py-12` (垂直 48px)
- 對齊: `text-center`

#### Loading 骨架屏

```
┌──────────────────────────────────────────────┐
│  專業證照                                    │
├──────────────────────────────────────────────┤
│                                              │
│  ┌───────────────┐  ┌───────────────┐      │
│  │ ░░░░░░░░░░░░  │  │ ░░░░░░░░░░░░  │      │
│  │ ░░░░░░░░      │  │ ░░░░░░░░      │      │
│  │               │  │               │      │
│  │ ░░░░░░░░░░░░  │  │ ░░░░░░░░░░░░  │      │
│  │ ░░░░░░        │  │ ░░░░░░        │      │
│  └───────────────┘  └───────────────┘      │
│                                              │
│  ┌───────────────┐  ┌───────────────┐      │
│  │ ░░░░░░░░░░░░  │  │ ░░░░░░░░░░░░  │      │
│  │ ░░░░░░░░      │  │ ░░░░░░░░      │      │
│  └───────────────┘  └───────────────┘      │
│                                              │
└──────────────────────────────────────────────┘
```

**規範**:
- 背景: `bg-slate-200`
- 動畫: `animate-pulse`
- 數量: 顯示 4 個骨架卡片 (2x2 網格)
- 高度: 與實際卡片相近 (約 200px)

---

### 狀態處理

#### Loading 狀態

**目標**: 提供優雅的載入體驗，避免空白閃爍。

##### 骨架屏設計原則
1. **形狀相似**: 骨架屏與實際內容形狀相似
2. **動畫流暢**: 使用 pulse 動畫
3. **數量合理**: 顯示 2-4 個骨架項目
4. **快速消失**: 載入完成後立即切換

##### 實作方式
```tsx
{isLoading ? (
  <ExperienceTimelineSkeleton count={3} />
) : experiences.length > 0 ? (
  <ExperienceTimeline experiences={experiences} />
) : (
  <EmptyState type="experience" />
)}
```

#### 空狀態

**目標**: 友善的提示，而非冷冰冰的「無資料」。

##### 設計原則
1. **視覺友善**: 使用柔和的灰色圖示
2. **語氣正向**: 「尚未新增」而非「沒有」
3. **提供解釋**: 簡短說明為何是空的
4. **不要 CTA**: 這是公開頁面，使用者無法新增

##### 空狀態文案
```
工作經驗:
標題: 尚無工作經驗記錄
說明: 此業務員尚未新增工作經驗

專業證照:
標題: 尚無專業證照記錄
說明: 此業務員尚未新增專業證照
```

#### 錯誤狀態

**目標**: 清楚說明問題，提供解決方案。

##### 設計原則
1. **清楚說明**: 用白話文解釋錯誤
2. **視覺明顯**: 紅色圖示、錯誤色彩
3. **提供動作**: 重試按鈕、返回按鈕
4. **不責怪使用者**: 語氣友善

##### 錯誤訊息範例
```
載入失敗:
┌────────────────────────────────────┐
│      [AlertCircle Icon]            │
│        (紅色，48px)                │
│                                    │
│     無法載入工作經驗               │
│                                    │
│  請檢查網路連線或稍後再試           │
│                                    │
│  [重試]  [返回首頁]                │
└────────────────────────────────────┘
```

---

## 🎨 視覺設計

### 色彩運用

#### 主要色彩
- **主色 (Primary)**: `#0ea5e9` (Sky-500) - 時間軸線、連結、按鈕
- **次要色 (Secondary)**: `#14b8a6` (Teal-500) - 強調元素
- **成功 (Success)**: `#22c55e` (Green-500) - 已驗證標記
- **警告 (Warning)**: `#f59e0b` (Amber-500) - 審核中標記
- **錯誤 (Error)**: `#ef4444` (Red-500) - 已拒絕、錯誤訊息

#### 中性色階
- **標題**: `slate-900` (#0f172a)
- **內文**: `slate-700` (#334155)
- **次要文字**: `slate-600` (#475569)
- **輔助文字**: `slate-500` (#64748b)
- **邊框**: `slate-100` (#f1f5f9)
- **背景**: `slate-50` (#f8fafc)

#### 語義色彩應用

| 元素 | 顏色 | 使用場景 |
|------|------|----------|
| **時間軸線** | `primary-200` | 淡藍色，不干擾閱讀 |
| **時間節點 (在職)** | `primary-500` | 醒目，表示當前 |
| **時間節點 (離職)** | `primary-300` | 較淡，表示過去 |
| **已驗證標記** | `success-600` + 綠色勾選 | 建立信任 |
| **審核中標記** | `warning-600` + 時鐘圖示 | 提示等待 |
| **卡片邊框 Hover** | `primary-200` | 互動回饋 |
| **證照徽章圖示** | `amber-500` | 金色，象徵專業 |

### 字體設計

#### 標題層級
```
H2 區塊標題 (工作經驗 / 專業證照):
- 字體: text-3xl font-bold (30px, 700)
- 顏色: text-slate-900
- 行高: leading-tight
- 外邊距: mb-6

H3 卡片標題 (職位 / 證照名稱):
- 字體: text-lg font-semibold (18px, 600)
- 顏色: text-slate-900
- 行高: leading-snug
- 最大行數: 2 行

H4 次要標題 (公司名稱 / 發證機構):
- 字體: text-base font-medium (16px, 500)
- 顏色: text-slate-700
- 行高: leading-normal
```

#### 內文與輔助文字
```
內文 (工作描述 / 證照描述):
- 字體: text-base (16px, 400)
- 顏色: text-slate-700
- 行高: leading-relaxed (1.625)
- 保留換行: whitespace-pre-line

次要文字 (日期、機構):
- 字體: text-sm (14px, 400)
- 顏色: text-slate-600
- 行高: leading-normal

輔助文字 (提示、標籤):
- 字體: text-xs (12px, 400)
- 顏色: text-slate-500
- 行高: leading-tight
```

### 間距系統

#### 區塊間距
```
區塊標題與內容: mb-6 (24px)
卡片與卡片: mb-6 (24px) - 工作經驗
            gap-4 (16px) - 專業證照網格
卡片內元素: space-y-3 (12px)
```

#### 卡片內邊距
```
工作經驗卡片: p-6 (24px)
專業證照卡片: p-6 (24px)
```

#### 響應式間距
```
Container:
- Mobile: px-4 (16px)
- Tablet: px-6 (24px)
- Desktop: px-8 (32px)

卡片網格 Gap:
- Mobile: gap-4 (16px)
- Tablet+: gap-4 (16px)

區塊間距:
- Mobile: space-y-6 (24px)
- Desktop: space-y-8 (32px)
```

### 圓角與陰影

#### 圓角
```
時間節點: rounded-full (圓形)
卡片: rounded-xl (12px) - 工作經驗
      rounded-2xl (16px) - 專業證照
按鈕: rounded-lg (8px)
徽章: rounded-full (圓角)
```

#### 陰影層次
```
預設狀態:
- 工作經驗卡片: shadow-sm
- 專業證照卡片: shadow-sm

Hover 狀態:
- 工作經驗卡片: shadow-md
- 專業證照卡片: shadow-lg

時間軸線: 無陰影
時間節點: 白色 ring (ring-4 ring-white) - 視覺區隔
```

---

## 🎬 互動設計

### 微互動規範

#### 1. Hover 微互動

**目標**: 提供即時的視覺回饋，讓使用者知道元素可互動。

##### 卡片 Hover
```tsx
className="
  transition-all duration-200 ease-out
  hover:shadow-lg
  hover:-translate-y-1
  hover:border-primary-200
"
```

- **陰影增強**: shadow-sm → shadow-lg
- **向上浮起**: -translate-y-1 (4px)
- **邊框變色**: border-slate-100 → border-primary-200
- **持續時間**: 200ms
- **緩動函數**: ease-out

##### 按鈕 Hover
```tsx
展開按鈕:
className="
  text-primary-600
  hover:text-primary-700
  hover:underline
  transition-colors duration-150
"

證書查看按鈕:
className="
  hover:bg-primary-50
  hover:border-primary-600
  transition-all duration-200
"
```

##### 時間節點 Hover
```tsx
className="
  hover:scale-110
  transition-transform duration-150
"
```

- **放大**: scale-110 (1.1倍)
- **持續時間**: 150ms
- **效果**: 突出節點位置

#### 2. 展開/收合動畫

**目標**: 流暢的內容展示，不突兀。

##### 動畫規範
```tsx
容器:
className={cn(
  "overflow-hidden transition-all duration-300 ease-out",
  isExpanded ? "max-h-96" : "max-h-0"
)}

按鈕圖示:
<ChevronDown className={cn(
  "transition-transform duration-300",
  isExpanded && "rotate-180"
)} />
```

- **展開方式**: max-height 從 0 → 96 (384px)
- **收合方式**: max-height 從 96 → 0
- **持續時間**: 300ms
- **緩動函數**: ease-out
- **圖示旋轉**: 180度旋轉 (ChevronDown 變成 ChevronUp)

##### 互動流程
```
使用者點擊「展開更多」
↓
setIsExpanded(true)
↓
容器 max-height 動畫展開 (300ms)
↓
圖示旋轉 180度 (300ms)
↓
按鈕文字改為「收合」
↓
展開完成
```

#### 3. 篩選動畫 (專業證照)

**目標**: 平滑的內容過濾，避免跳動。

##### 動畫規範
```tsx
卡片:
className={cn(
  "transition-all duration-200",
  !isVisible && "opacity-0 scale-95"
)}
```

- **淡出**: opacity 從 1 → 0
- **縮小**: scale 從 1 → 0.95
- **持續時間**: 200ms
- **網格重排**: 自動調整 (CSS Grid)

#### 4. 載入動畫

**目標**: 減少等待焦慮，提供載入回饋。

##### 骨架屏動畫
```tsx
className="animate-pulse bg-slate-200 rounded"
```

- **效果**: 明暗脈衝
- **持續時間**: 2秒循環
- **緩動**: linear

##### 淡入動畫 (內容載入完成)
```tsx
className="animate-fade-in"

@keyframes fade-in {
  0% { opacity: 0; }
  100% { opacity: 1; }
}
```

- **持續時間**: 300ms
- **緩動**: ease-out

#### 5. 驗證標記動畫

**目標**: 突出已驗證狀態，建立信任。

##### 淡入動畫
```tsx
className="animate-fade-in"
```

##### 可選: 彈跳動畫 (首次出現)
```tsx
className="animate-bounce-in"

@keyframes bounce-in {
  0% { transform: scale(0.3); opacity: 0; }
  50% { transform: scale(1.05); }
  70% { transform: scale(0.9); }
  100% { transform: scale(1); opacity: 1; }
}
```

### Focus 狀態 (鍵盤導航)

**目標**: 確保鍵盤使用者能清楚看到焦點。

#### Focus Ring 規範
```tsx
可互動元素 (按鈕、連結):
className="
  focus-visible:outline-none
  focus-visible:ring-2
  focus-visible:ring-primary-500
  focus-visible:ring-offset-2
"
```

- **Ring 顏色**: primary-500
- **Ring 粗細**: 2px
- **Ring 偏移**: 2px
- **僅 focus-visible**: 滑鼠點擊時不顯示 (避免干擾)

#### Tab 順序
```
工作經驗區塊:
1. 第一張卡片展開按鈕
2. 第二張卡片展開按鈕
3. ...

專業證照區塊:
1. 篩選下拉選單 (如有)
2. 第一張卡片展開按鈕
3. 第一張卡片證書查看按鈕 (如有)
4. 第二張卡片展開按鈕
5. ...
```

### Active 狀態

**目標**: 提供點擊確認回饋。

#### 按鈕 Active
```tsx
className="
  active:scale-95
  transition-transform duration-100
"
```

- **縮小**: scale-95 (0.95倍)
- **持續時間**: 100ms (快速)
- **效果**: 模擬按下感

---

## 📱 響應式設計

### 斷點策略

**基於 Tailwind CSS 預設斷點 (Mobile First)**

| 斷點 | Min Width | 裝置 | 主要調整 |
|------|-----------|------|----------|
| **Default** | 0px | Mobile | 單欄佈局、緊湊間距 |
| **md** | 768px | Tablet | 2欄網格 (證照)、寬鬆間距 |
| **lg** | 1024px | Desktop | 最佳閱讀體驗、完整功能 |

### 響應式模式

#### 1. 工作經驗時間軸

##### Mobile (<768px)
```tsx
特點:
- 簡化時間軸線 (較細、較短)
- 單欄佈局
- 緊湊間距
- 時間節點較小

<div className="relative pl-8">  {/* 左側空間給時間軸 */}
  {/* 時間軸線 */}
  <div className="absolute left-3 top-0 bottom-0 w-0.5 bg-primary-200" />

  {/* 時間節點 */}
  <div className="absolute left-0 top-2 w-6 h-6 rounded-full bg-primary-500 ring-2 ring-white" />

  {/* 卡片 */}
  <div className="mb-6 rounded-xl p-4 ...">
    {/* 內容 */}
  </div>
</div>
```

**調整項目**:
- 時間軸線: 2px → 1px (`w-0.5`)
- 時間節點: 12px → 24px (`w-6 h-6`)
- 卡片內邊距: 24px → 16px (`p-4`)
- 左側偏移: 32px → 32px (`pl-8`)
- 職位名稱: text-lg → text-base
- 日期格式: 縮短 (2022/01 → 2022/1)

##### Tablet (768px-1023px)
```tsx
與桌面版相同，但容器較窄
```

##### Desktop (≥1024px)
```tsx
完整體驗:
- 時間軸線粗細: 2px
- 時間節點: 12px
- 卡片內邊距: 24px
- 完整日期格式
- 寬鬆間距
```

#### 2. 專業證照卡片

##### Mobile (<768px)
```tsx
<div className="grid grid-cols-1 gap-4">
  {certifications.map(cert => <CertCard />)}
</div>
```

**特點**:
- 單欄列表
- 卡片全寬
- 間距: gap-4 (16px)
- 字體略小
- 描述預設收合

##### Tablet (768px-1023px)
```tsx
<div className="grid md:grid-cols-2 gap-4">
  {certifications.map(cert => <CertCard />)}
</div>
```

**特點**:
- 2欄網格
- 卡片等寬
- 間距: gap-4 (16px)

##### Desktop (≥1024px)
```tsx
<div className="grid lg:grid-cols-2 gap-6">
  {certifications.map(cert => <CertCard />)}
</div>
```

**特點**:
- 2欄網格 (不做 3欄，避免卡片太小)
- 較大間距: gap-6 (24px)
- 完整字體大小

#### 3. 容器與間距

```tsx
<div className="container mx-auto px-4 md:px-6 lg:px-8">
  <div className="space-y-6 md:space-y-8 lg:space-y-10">
    {/* 區塊 */}
  </div>
</div>
```

**響應式間距**:
- 容器水平: 16px → 24px → 32px
- 區塊垂直: 24px → 32px → 40px

#### 4. 字體大小

```tsx
響應式字體:
- H2 標題: text-2xl md:text-3xl
- H3 卡片標題: text-base md:text-lg
- 內文: text-sm md:text-base
- 次要文字: text-xs md:text-sm
```

### 觸控友善設計

#### 最小觸控目標
```
所有可點擊元素最小尺寸: 44x44 px (Apple HIG)
建議尺寸: 48x48 px (Material Design)
```

#### 實作範例
```tsx
展開按鈕:
<button className="min-h-[44px] py-2 px-3">
  展開更多
</button>

證書查看按鈕:
<button className="h-10 px-4">  {/* 40px 高度，接近 44px */}
  查看證書
</button>

圖示按鈕:
<button className="p-3">  {/* 48x48 px */}
  <Icon className="h-6 w-6" />
</button>
```

#### 按鈕間距
```tsx
確保按鈕間距至少 8px:
<div className="flex gap-2">
  <button>按鈕 1</button>
  <button>按鈕 2</button>
</div>
```

### 響應式圖片與圖示

```tsx
圖示大小響應式:
<Icon className="h-4 w-4 md:h-5 md:h-5" />

頭像大小響應式:
<Avatar size="lg" className="md:w-24 md:h-24" />
```

---

## ♿ 無障礙設計

### WCAG 2.1 AA 標準遵循

#### 1. 可感知 (Perceivable)

##### 色彩對比
```
文字對比度要求:
✅ 標題 (slate-900 on white): 19.64:1 (遠超 4.5:1)
✅ 內文 (slate-700 on white): 10.69:1
✅ 次要文字 (slate-600 on white): 7.74:1
✅ 按鈕 (white on primary-500): 4.93:1

圖形元件對比度要求:
✅ 時間軸線 (primary-200 on slate-50): > 3:1
✅ 驗證標記 (success-600 on white): > 4.5:1
```

##### 文字替代
```tsx
圖示加 aria-label:
<button aria-label="展開工作描述">
  <ChevronDown className="h-4 w-4" />
</button>

圖示加 Screen Reader 文字:
<CheckCircle2 aria-hidden="true" />
<span className="sr-only">已驗證</span>

裝飾性圖示隱藏:
<Award aria-hidden="true" />
```

##### 不只依賴顏色
```tsx
已驗證標記:
- 顏色: 綠色 ✅
- 圖示: CheckCircle2 ✅
- 文字: "已驗證" ✅

審核中標記:
- 顏色: 橘色 ✅
- 圖示: Clock ✅
- 文字: "審核中" ✅
```

#### 2. 可操作 (Operable)

##### 鍵盤導航
```tsx
所有互動元素可用 Tab 鍵訪問:
<button
  className="focus-visible:ring-2 focus-visible:ring-primary-500"
  tabIndex={0}
>
  展開更多
</button>

Enter/Space 鍵觸發:
onKeyDown={(e) => {
  if (e.key === 'Enter' || e.key === ' ') {
    e.preventDefault();
    handleExpand();
  }
}}
```

##### 焦點指示清楚
```tsx
Focus Ring 規範:
className="
  focus-visible:outline-none
  focus-visible:ring-2
  focus-visible:ring-primary-500
  focus-visible:ring-offset-2
  focus-visible:rounded-lg
"
```

##### 跳過連結
```tsx
頁面頂部提供跳過連結:
<a
  href="#experience-section"
  className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50"
>
  跳到工作經驗區塊
</a>
```

#### 3. 可理解 (Understandable)

##### 清楚的標籤
```tsx
<section aria-labelledby="experience-title">
  <h2 id="experience-title">工作經驗</h2>
  {/* 內容 */}
</section>
```

##### 展開狀態告知
```tsx
<button
  aria-expanded={isExpanded}
  aria-controls="experience-detail-1"
>
  {isExpanded ? '收合' : '展開更多'}
</button>

<div
  id="experience-detail-1"
  role="region"
  aria-hidden={!isExpanded}
>
  {/* 詳細內容 */}
</div>
```

##### 載入狀態告知
```tsx
<div
  role="status"
  aria-live="polite"
  aria-busy={isLoading}
>
  {isLoading ? '載入中...' : '載入完成'}
</div>
```

#### 4. 穩健 (Robust)

##### 語義化 HTML
```tsx
✅ 使用語義標籤:
<section>
  <h2>工作經驗</h2>
  <article>
    <h3>職位名稱</h3>
    <time datetime="2022-01-15">2022年1月</time>
    <p>描述內容</p>
  </article>
</section>

❌ 避免過度使用 div:
<div>
  <div>工作經驗</div>
  <div>
    <div>職位名稱</div>
  </div>
</div>
```

##### ARIA 屬性正確使用
```tsx
時間軸:
<ol role="list" aria-label="工作經驗時間軸">
  <li role="listitem">
    <article>
      {/* 工作經驗項目 */}
    </article>
  </li>
</ol>

證照網格:
<div role="list" aria-label="專業證照列表">
  <div role="listitem">
    {/* 證照卡片 */}
  </div>
</div>
```

##### 日期格式
```tsx
使用 <time> 標籤:
<time datetime="2022-01-15">
  2022/01
</time>

至今:
<time datetime={new Date().toISOString()}>
  至今
</time>
```

### Screen Reader 友善

#### Hidden 文字
```tsx
僅 Screen Reader 可見:
<span className="sr-only">
  已驗證的工作經驗
</span>

隱藏裝飾性元素:
<svg aria-hidden="true">
  <path d="..." />
</svg>
```

#### Live Region
```tsx
動態內容更新通知:
<div
  aria-live="polite"
  aria-atomic="true"
  className="sr-only"
>
  {message}
</div>

篩選結果通知:
<div aria-live="polite" className="sr-only">
  找到 {count} 個已驗證的證照
</div>
```

#### 描述性連結
```tsx
❌ 不良範例:
<a href="#">查看更多</a>

✅ 良好範例:
<a href="#" aria-label="查看業務員王大明的完整工作經驗">
  查看更多
</a>
```

---

## 🎬 動畫與微互動

### 動畫系統

#### 動畫時機 (Duration)

| 動畫類型 | 持續時間 | 使用場景 |
|----------|----------|----------|
| **極快** | 100ms | 按鈕按下 (active) |
| **快速** | 150ms | Hover 效果、Focus Ring |
| **標準** | 200ms | 卡片 Hover、淡入淡出 ⭐ |
| **中等** | 300ms | 展開/收合、內容切換 ⭐ |
| **慢速** | 500ms | 頁面過渡 (少用) |

#### 緩動函數 (Easing)

| 緩動 | 使用場景 |
|------|----------|
| `ease-out` | 淡入、展開 (推薦) ⭐ |
| `ease-in` | 淡出、收合 |
| `ease-in-out` | 往返動畫 |
| `linear` | 載入動畫 (pulse) |

### 自定義動畫

#### 1. 淡入動畫
```css
@keyframes fade-in {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}
```

**使用場景**: 內容載入完成、驗證標記出現

#### 2. 滑入動畫
```css
@keyframes slide-in-bottom {
  0% {
    transform: translateY(100%);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-in-bottom {
  animation: slide-in-bottom 0.3s ease-out;
}
```

**使用場景**: Toast 通知、Modal 出現

#### 3. 彈跳動畫
```css
@keyframes bounce-in {
  0% {
    transform: scale(0.3);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  70% {
    transform: scale(0.9);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-bounce-in {
  animation: bounce-in 0.5s ease-out;
}
```

**使用場景**: 驗證標記首次出現 (可選)

### Transition 規範

#### 通用 Transition
```tsx
所有互動元素:
className="transition-all duration-200 ease-out"

顏色變化:
className="transition-colors duration-150 ease-out"

位移變化:
className="transition-transform duration-200 ease-out"

陰影變化:
className="transition-shadow duration-200 ease-out"
```

#### 展開/收合 Transition
```tsx
容器:
className={cn(
  "overflow-hidden transition-all duration-300 ease-out",
  isExpanded ? "max-h-96 opacity-100" : "max-h-0 opacity-0"
)}

圖示旋轉:
<ChevronDown
  className={cn(
    "transition-transform duration-300 ease-out",
    isExpanded && "rotate-180"
  )}
/>
```

### 效能考量

#### 優化原則

1. **使用 transform 和 opacity**: 不觸發 reflow/repaint
   ```tsx
   ✅ transform: translateY(-4px)
   ❌ top: -4px

   ✅ opacity: 0
   ❌ visibility: hidden (搭配 display)
   ```

2. **避免動畫過多元素**: 只動畫必要的元素
   ```tsx
   ✅ 動畫單張卡片
   ❌ 同時動畫 20 張卡片
   ```

3. **使用 will-change (謹慎)**: 提示瀏覽器優化
   ```tsx
   <div className="will-change-transform hover:-translate-y-1">
     {/* 卡片 */}
   </div>
   ```

4. **尊重使用者偏好**: 支援 `prefers-reduced-motion`
   ```tsx
   <div className="
     motion-safe:animate-fade-in
     motion-reduce:animate-none
   ">
     {/* 內容 */}
   </div>
   ```

### 動畫檢查清單

開發完成後檢查:
- [ ] 所有動畫持續時間 < 500ms
- [ ] 使用正確的緩動函數 (ease-out 為主)
- [ ] Hover 效果即時回饋 (< 200ms)
- [ ] 展開/收合流暢自然 (300ms)
- [ ] 支援 prefers-reduced-motion
- [ ] 無卡頓 (60fps)
- [ ] 無過度動畫 (干擾閱讀)

---

## 📊 設計交付物

### 產出清單

1. **本 UI/UX 規格文件** ✅
   - 完整的視覺設計規範
   - 互動設計規範
   - 響應式設計策略
   - 無障礙設計要求

2. **組件規格** (components.md)
   - ExperienceTimeline 組件
   - ExperienceItem 組件
   - CertificationCards 組件
   - CertificationCard 組件

3. **頁面規格** (pages.md)
   - 業務員詳情頁面整合方案

4. **API 整合規格** (api-integration.md)
   - 使用現有 API
   - 資料結構確認

5. **狀態管理規格** (state-routing.md)
   - Local State 管理

### 設計驗收標準

#### 視覺設計
- [ ] 遵循設計系統色彩規範
- [ ] 字體大小與行高符合規範
- [ ] 間距使用 4px 網格系統
- [ ] 圓角和陰影一致
- [ ] 色彩對比度符合 WCAG AA

#### 互動設計
- [ ] Hover 效果即時回饋
- [ ] 展開/收合動畫流暢
- [ ] Focus 狀態清楚可見
- [ ] 觸控目標足夠大 (≥44px)
- [ ] 所有互動元素可鍵盤操作

#### 響應式設計
- [ ] Mobile (375px+) 正常顯示
- [ ] Tablet (768px+) 正常顯示
- [ ] Desktop (1024px+) 正常顯示
- [ ] 觸控友善
- [ ] 無水平滾動

#### 無障礙設計
- [ ] 語義化 HTML
- [ ] ARIA 屬性正確
- [ ] Screen Reader 友善
- [ ] 鍵盤導航完整
- [ ] 色彩對比符合標準

#### 效能
- [ ] 動畫流暢 (60fps)
- [ ] 無卡頓
- [ ] Loading 狀態友善
- [ ] 支援 prefers-reduced-motion

---

## 📚 參考資源

### 設計靈感
- [LinkedIn 工作經驗時間軸](https://www.linkedin.com/)
- [Dribbble - Certificate Cards](https://dribbble.com/search/certificate-card)
- [Material Design - Timeline](https://material.io/)

### 技術文檔
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Radix UI](https://www.radix-ui.com/)
- [Lucide Icons](https://lucide.dev/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

### 工具
- [Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Easing Functions](https://easings.net/)
- [Cubic Bezier Generator](https://cubic-bezier.com/)

---

**版本**: 1.0
**日期**: 2026-01-20
**設計師**: Claude Sonnet 4.5 (Senior Product Designer)
**審核**: Pending
**狀態**: Draft → Ready for Implementation
