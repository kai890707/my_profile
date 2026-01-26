# UI/UX 設計規格 - Analytics Dashboard

**Feature**: Analytics Dashboard
**Version**: 1.0
**Last Updated**: 2026-01-24
**Designer**: Senior Product Designer

---

## 📋 目錄

- [設計目標](#設計目標)
- [使用者研究](#使用者研究)
- [設計原則](#設計原則)
- [資訊架構](#資訊架構)
- [頁面設計](#頁面設計)
- [互動設計](#互動設計)
- [視覺設計](#視覺設計)
- [響應式設計](#響應式設計)
- [無障礙設計](#無障礙設計)
- [效能要求](#效能要求)

---

## 🎯 設計目標

### 對業務員

**核心價值**: 「一眼看懂自己的表現，快速發現改善機會」

**設計目標**:
1. **簡潔明瞭** - 不超過 3 個核心指標，避免資訊過載
2. **即時反饋** - 今日數據即時更新，看到努力成果
3. **趨勢可視** - 清楚的折線圖展示進步曲線
4. **行動導向** - 看到數據後知道下一步該做什麼

### 對管理員

**核心價值**: 「全面掌握平台健康度，數據驅動決策」

**設計目標**:
1. **數據密度** - 提供豐富但不擁擠的數據視圖
2. **識別問題** - 快速發現熱門與低活躍業務員
3. **趨勢分析** - 平台成長曲線一目了然
4. **可操作性** - 點擊數據可深入探索

---

## 👥 使用者研究

### 業務員角色 (Persona)

**Alex - 保險業務員**

**基本資訊**:
- 年齡: 30 歲
- 經驗: 3 年
- 技術能力: 中等
- 主要裝置: iPhone 13 (80% 時間)

**目標**:
- 知道檔案有多少人看過
- 追蹤有多少潛在客戶聯繫我
- 了解哪些時間段曝光最多
- 與上週比較看是否進步

**痛點**:
- 不確定平台是否真的有效
- 不知道如何優化檔案
- 擔心沒有客戶看到我
- 無法衡量投資回報

**使用情境**:
- 早上上班前查看昨日數據 (7-8 AM)
- 客戶拜訪後查看是否有新聯繫 (隨時)
- 週五下午查看本週表現 (5-6 PM)

**期望**:
- 打開 App 立即看到今日瀏覽數
- 清楚的趨勢圖（不需要專業解讀）
- 快速存取最新聯繫請求
- 手機操作流暢

### 管理員角色 (Persona)

**Jenny - 平台營運主管**

**基本資訊**:
- 年齡: 35 歲
- 經驗: 8 年平台營運
- 技術能力: 高
- 主要裝置: MacBook Pro + iPad

**目標**:
- 監控平台整體健康度
- 識別表現優異的業務員（推廣案例）
- 發現低活躍業務員（提供協助）
- 追蹤平台成長趨勢

**痛點**:
- 不知道哪些業務員需要幫助
- 無法量化平台價值
- 難以向老闆報告成效
- 缺乏數據支持決策

**使用情境**:
- 每天早上 9:00 查看平台概況
- 週一會議前準備數據報告
- 發現異常時深入分析

**期望**:
- 快速掌握關鍵指標
- 可點擊數據查看詳情
- 可匯出報表（未來功能）
- 數據準確可信

### 使用者旅程地圖

#### 業務員查看數據旅程

```
階段 1: 觸發
- 情境: 早上起床，想知道昨天檔案表現
- 行為: 打開 YAMU App
- 情緒: 😊 期待
- 痛點: 需要登入（如果未登入）

階段 2: 進入 Dashboard
- 情境: 點擊「數據分析」選單
- 行為: 進入 Analytics 頁面
- 情緒: 😊 期待看到數據
- 痛點: Loading 時間不能太長

階段 3: 查看核心指標
- 情境: 頁面載入完成
- 行為: 看到 3 個大數字卡片
- 情緒: 😃 滿足（如果數據好）/ 😟 擔心（如果數據差）
- 痛點: 不確定數字是好是壞

階段 4: 查看趨勢
- 情境: 想了解長期趨勢
- 行為: 切換到「過去 7 天」Tab
- 情緒: 🤔 分析中
- 痛點: 圖表太複雜看不懂

階段 5: 查看聯繫請求
- 情境: 想知道有哪些潛在客戶
- 行為: 滾動到「最近聯繫」區塊
- 情緒: 😊 期待有新客戶
- 痛點: 聯繫訊息太長看不清楚

階段 6: 決定下一步
- 情境: 看完數據後
- 行為: 決定是否更新檔案或回覆客戶
- 情緒: 💪 有動力
- 成功: 清楚知道下一步該做什麼
```

#### 管理員查看平台數據旅程

```
階段 1: 每日檢查
- 情境: 每天早上 9:00 例行檢查
- 行為: 進入管理員 Analytics
- 情緒: 😐 中性
- 痛點: 需要快速掌握重點

階段 2: 查看平台概況
- 情境: 頁面載入
- 行為: 看到 4 個 KPI 卡片
- 情緒: 😊 平台運作正常 / 😰 數據下降
- 痛點: 想知道為什麼數據變化

階段 3: 識別熱門業務員
- 情境: 想了解誰表現好
- 行為: 查看 Top 10 列表
- 情緒: 😃 有優秀案例可學習
- 痛點: 想點擊查看詳情

階段 4: 關注低活躍業務員
- 情境: 擔心業務員流失
- 行為: 查看活躍度卡片
- 情緒: 😟 16 個低活躍需要關注
- 痛點: 不知道如何聯繫他們

階段 5: 分析趨勢
- 情境: 準備週會報告
- 行為: 查看 30 天趨勢圖
- 情緒: 😌 平台穩定成長
- 痛點: 想匯出圖表（未來功能）
```

### 競品分析

#### Google Analytics Dashboard

**優點**:
- ✅ 清晰的 KPI 卡片設計
- ✅ 多種圖表類型
- ✅ 時間範圍選擇靈活

**缺點**:
- ❌ 過於複雜，需要學習
- ❌ 資訊過載
- ❌ 手機體驗不佳

**借鑑**:
- 採用大數字 KPI 卡片
- 使用折線圖展示趨勢
- 提供時間範圍切換

#### Notion Analytics

**優點**:
- ✅ 簡潔優雅的設計
- ✅ 清楚的視覺層次
- ✅ 快速載入

**缺點**:
- ❌ 功能較簡單
- ❌ 缺少詳細數據

**借鑑**:
- 簡潔的卡片設計
- 柔和的色彩運用
- 快速的互動回饋

#### Linear Insights

**優點**:
- ✅ 現代化設計
- ✅ 流暢的動畫
- ✅ 清楚的增長率顯示

**缺點**:
- ❌ 針對團隊協作，不適合個人

**借鑑**:
- 增長率百分比顯示
- 簡潔的色彩指示（綠色 ↑ / 紅色 ↓）
- 卡片 Hover 效果

### 設計決策

基於以上分析，我們選擇：

1. **KPI 展示**: 大數字 + 增長率百分比（類似 Google Analytics + Linear）
2. **圖表類型**: 簡單的折線圖（避免複雜圖表如 Notion 過於簡單）
3. **色彩系統**: 使用 YAMU 品牌色（Sky-500）+ 語義色
4. **時間範圍**: 提供 3 個預設 Tab（今日/7天/30天），避免自訂日期選擇器
5. **互動回饋**: 快速的 Loading 狀態（< 300ms）
6. **響應式**: Mobile First 設計，卡片堆疊排列

---

## 🎨 設計原則

### 1. 簡潔至上 (Simplicity First)

**理念**: 「數據可視化的敵人是複雜性」

**應用**:
- 業務員 Dashboard 只顯示 3 個核心 KPI
- 管理員 Dashboard 只顯示 4 個核心 KPI
- 避免圓餅圖、雷達圖等複雜圖表
- 每個數字都有明確的標籤

**案例**:
```
❌ 不良設計:
┌─────────────────────────────────┐
│ 檔案瀏覽數: 342 (過去 7 天)     │
│ 較上週成長: +14.77% (↑44)      │
│ 每日平均: 48.86                 │
│ 最高單日: 61 (2026-01-21)       │
│ 最低單日: 32 (2026-01-24)       │
└─────────────────────────────────┘
資訊過載，不知道重點

✅ 良好設計:
┌─────────────────────────────────┐
│        檔案瀏覽數               │
│                                 │
│          342                    │
│        +14.77% ↑                │
└─────────────────────────────────┘
聚焦核心數字，增長率一目了然
```

### 2. 即時反饋 (Instant Feedback)

**理念**: 「使用者操作應該立即有回應」

**應用**:
- Tab 切換 < 300ms
- Loading 狀態使用 Skeleton Screen（比轉圈圈感覺更快）
- Hover 卡片立即顯示陰影變化
- 點擊按鈕立即改變狀態

**案例**:
```tsx
// ❌ 不良: 無 Loading 狀態
{data && <TrendChart data={data} />}

// ✅ 良好: Skeleton Screen
{isLoading ? (
  <div className="animate-pulse">
    <div className="h-64 bg-slate-200 rounded-xl" />
  </div>
) : (
  <TrendChart data={data} />
)}
```

### 3. 視覺引導 (Visual Guidance)

**理念**: 「使用者應該知道下一步該看哪裡」

**應用**:
- 使用 F 型閱讀模式（左上 → 右 → 下）
- 重要數據用大字體和鮮豔顏色
- 增長率用綠色 ↑ / 紅色 ↓ 清楚標示
- 卡片用陰影建立視覺層次

**案例**:
```
視覺層次:
1. 最重要 - KPI 大數字 (text-4xl, primary-600)
2. 次重要 - 增長率 (text-lg, success/error-600)
3. 輔助 - 標籤文字 (text-sm, slate-600)
```

### 4. 容錯設計 (Fault Tolerance)

**理念**: 「數據可能為空或異常，設計要寬容」

**應用**:
- 無數據時顯示友善提示（不是「Error」）
- API 失敗時顯示重試按鈕
- 上個時段數據為 0 時增長率顯示「—」
- Loading 失敗後可手動重新整理

**案例**:
```tsx
// 空狀態
{contacts.length === 0 && (
  <div className="text-center py-12">
    <MessageSquareIcon className="h-12 w-12 text-slate-300 mx-auto mb-4" />
    <p className="text-slate-600">尚無聯繫記錄</p>
    <p className="text-sm text-slate-500 mt-2">
      當有客戶聯繫時，會顯示在這裡
    </p>
  </div>
)}

// 錯誤狀態
{isError && (
  <div className="text-center py-12">
    <AlertCircleIcon className="h-12 w-12 text-error-500 mx-auto mb-4" />
    <p className="text-slate-900 font-semibold mb-2">載入失敗</p>
    <p className="text-sm text-slate-600 mb-4">請檢查網路連線</p>
    <Button onClick={refetch} variant="outline">
      <RefreshCwIcon className="mr-2 h-4 w-4" />
      重試
    </Button>
  </div>
)}
```

### 5. 一致性 (Consistency)

**理念**: 「相同的設計模式降低學習成本」

**應用**:
- 所有 KPI 卡片使用相同設計
- 時間範圍 Tab 使用統一樣式
- 色彩語義一致（綠色 = 成長，紅色 = 下降）
- 圓角、間距、字體統一

---

## 🗺️ 資訊架構

### 網站地圖 (Sitemap)

```
YAMU 業務員推廣系統
├─ 首頁 /
├─ 搜尋 /search
├─ 業務員專區 /dashboard
│  ├─ 個人檔案 /dashboard/profile
│  ├─ 我的評分 /dashboard/ratings
│  ├─ 聯繫請求 /dashboard/contacts
│  └─ 數據分析 /dashboard/analytics ⭐ (新增)
│
└─ 管理員後台 /admin
   ├─ 使用者管理 /admin/users
   ├─ 評分審核 /admin/ratings
   └─ 平台數據 /admin/dashboard/analytics ⭐ (新增)
```

### 頁面層級

**業務員 Dashboard** (`/dashboard/analytics`):

```
Level 1: 頁面標題
  └─ "數據分析 Dashboard"

Level 2: 時間範圍選擇
  └─ Tab 組件 (今日 / 過去 7 天 / 過去 30 天)

Level 3: KPI 卡片區域
  ├─ 卡片 1: 總瀏覽數
  ├─ 卡片 2: 總聯繫數
  └─ 卡片 3: 增長率

Level 4: 趨勢圖表
  └─ 折線圖 (瀏覽數 + 聯繫數雙線)

Level 5: 最近聯繫列表
  └─ 最新 10 筆聯繫請求
```

**管理員 Dashboard** (`/admin/dashboard/analytics`):

```
Level 1: 頁面標題
  └─ "平台數據分析 Dashboard"

Level 2: KPI 卡片區域
  ├─ 卡片 1: 總業務員數
  ├─ 卡片 2: 總瀏覽數
  ├─ 卡片 3: 總聯繫數
  └─ 卡片 4: 平台轉換率

Level 3: 兩欄佈局
  ├─ 左: 熱門業務員 Top 10
  └─ 右: 業務員活躍度

Level 4: 平台成長趨勢圖
  └─ 折線圖 (過去 30 天)
```

### 導航設計

#### 業務員側邊欄導航 (更新)

```tsx
// components/layout/SalespersonNav.tsx

const navItems = [
  {
    title: '個人檔案',
    href: '/dashboard/profile',
    icon: UserIcon,
  },
  {
    title: '我的評分',
    href: '/dashboard/ratings',
    icon: StarIcon,
  },
  {
    title: '聯繫請求',
    href: '/dashboard/contacts',
    icon: MailIcon,
  },
  {
    title: '數據分析', // ⭐ 新增
    href: '/dashboard/analytics',
    icon: BarChart3Icon,
  },
];
```

#### 管理員側邊欄導航 (更新)

```tsx
// components/layout/AdminNav.tsx

const navItems = [
  {
    title: '使用者管理',
    href: '/admin/users',
    icon: UsersIcon,
  },
  {
    title: '評分審核',
    href: '/admin/ratings',
    icon: CheckCircle2Icon,
  },
  {
    title: '平台數據', // ⭐ 新增
    href: '/admin/dashboard/analytics',
    icon: TrendingUpIcon,
  },
];
```

### 內容優先級

#### 業務員 Dashboard 優先級

```
視覺金字塔:

        ┌─────────────────┐
        │  KPI 大數字     │ ← 最重要 (一眼看到)
        └─────────────────┘
       ┌───────────────────┐
       │  增長率百分比     │ ← 次重要 (了解趨勢)
       └───────────────────┘
      ┌─────────────────────┐
      │  趨勢圖表           │ ← 中等 (詳細分析)
      └─────────────────────┘
     ┌───────────────────────┐
     │  最近聯繫列表         │ ← 輔助 (查看詳情)
     └───────────────────────┘
```

#### 管理員 Dashboard 優先級

```
視覺金字塔:

        ┌─────────────────┐
        │  平台 KPI       │ ← 最重要
        └─────────────────┘
       ┌───────────────────┐
       │  Top 10 + 活躍度 │ ← 次重要
       └───────────────────┘
      ┌─────────────────────┐
      │  成長趨勢圖         │ ← 輔助
      └─────────────────────┘
```

---

## 🎨 頁面設計

### 業務員 Dashboard 設計

#### 桌面版佈局 (1280px+)

```
┌────────────────────────────────────────────────────────────────────┐
│  [Logo]  個人檔案 | 我的評分 | 聯繫請求 | 數據分析🔵 | [使用者]  │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  📊 數據分析 Dashboard                                             │
│  ────────────────────────────────────────────────────────────────  │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ [今日] [過去 7 天🔵] [過去 30 天]                             │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                    │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │ 📈 檔案瀏覽數│  │ 📞 聯繫請求數│  │ 📊 轉換率    │            │
│  │              │  │              │  │              │            │
│  │     342      │  │      28      │  │    8.19%     │            │
│  │              │  │              │  │              │            │
│  │  +14.77% ↑   │  │  +27.27% ↑   │  │  +10.98% ↑   │            │
│  │  較上週      │  │  較上週      │  │  較上週      │            │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ 📈 趨勢分析 (過去 7 天)                                       │  │
│  │                                                               │  │
│  │   60│                              ●瀏覽數                  │  │
│  │     │            ●                 ●聯繫數                  │  │
│  │   50│      ●           ●                                    │  │
│  │     │ ●         ●                                           │  │
│  │   40│                        ●                              │  │
│  │     │                                                       │  │
│  │   30│                                    ●                  │  │
│  │     │                                                       │  │
│  │   20│                                                       │  │
│  │     │                                                       │  │
│  │   10│    ●   ●      ●    ●   ●   ●   ●                     │  │
│  │     │                                                       │  │
│  │    0└───┬───┬───┬───┬───┬───┬───                           │  │
│  │        1/18 1/19 1/20 1/21 1/22 1/23 1/24                  │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ 💬 最近聯繫請求 (10)                                          │  │
│  │                                                               │  │
│  │  ┌──────────────────────────────────────────────────────┐   │  │
│  │  │ 👤 王小明  •  9:15 AM                                 │   │  │
│  │  │ ✉️  wang@example.com  •  📞 0912345678                │   │  │
│  │  │ 💬 您好，我對您的產品很有興趣，想了解更多詳情...    │   │  │
│  │  │ 🏷️ [待處理]                                           │   │  │
│  │  └──────────────────────────────────────────────────────┘   │  │
│  │                                                               │  │
│  │  ┌──────────────────────────────────────────────────────┐   │  │
│  │  │ 👤 李大華  •  昨天 4:22 PM                            │   │  │
│  │  │ ✉️  lee@example.com                                   │   │  │
│  │  │ 💬 請問貴公司有提供企業方案嗎？我們需要...          │   │  │
│  │  │ 🏷️ [已聯繫]                                           │   │  │
│  │  └──────────────────────────────────────────────────────┘   │  │
│  │                                                               │  │
│  │  [查看所有聯繫請求 →]                                        │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

#### 平板版佈局 (768px - 1023px)

```
┌──────────────────────────────────────┐
│  [☰] 數據分析      [🔔] [👤]        │
├──────────────────────────────────────┤
│                                      │
│  📊 數據分析 Dashboard               │
│  ──────────────────────────────────  │
│                                      │
│  [今日] [過去 7 天🔵] [過去 30 天]   │
│                                      │
│  ┌────────────┐  ┌────────────┐     │
│  │檔案瀏覽數  │  │聯繫請求數  │     │
│  │   342      │  │    28      │     │
│  │ +14.77% ↑  │  │ +27.27% ↑  │     │
│  └────────────┘  └────────────┘     │
│                                      │
│  ┌──────────────────────────┐       │
│  │     轉換率                │       │
│  │     8.19%                 │       │
│  │   +10.98% ↑               │       │
│  └──────────────────────────┘       │
│                                      │
│  ┌──────────────────────────────┐   │
│  │  趨勢圖表 (較小)              │   │
│  │                              │   │
│  └──────────────────────────────┘   │
│                                      │
│  ┌──────────────────────────────┐   │
│  │  最近聯繫 (卡片式)            │   │
│  └──────────────────────────────┘   │
│                                      │
└──────────────────────────────────────┘
```

#### 手機版佈局 (< 768px)

```
┌──────────────────────┐
│ [☰] 數據分析  [👤]  │
├──────────────────────┤
│                      │
│ 📊 數據分析          │
│                      │
│ [今日] [7天🔵] [30天]│
│                      │
│ ┌──────────────────┐ │
│ │  檔案瀏覽數      │ │
│ │     342          │ │
│ │  +14.77% ↑       │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │  聯繫請求數      │ │
│ │      28          │ │
│ │  +27.27% ↑       │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │     轉換率       │ │
│ │     8.19%        │ │
│ │   +10.98% ↑      │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │  趨勢圖表        │ │
│ │  (壓縮高度)      │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │ 💬 王小明        │ │
│ │ 9:15 AM          │ │
│ │ 您好，我對...    │ │
│ │ [待處理]         │ │
│ └──────────────────┘ │
│                      │
│ [查看所有 →]        │
│                      │
└──────────────────────┘
```

### 管理員 Dashboard 設計

#### 桌面版佈局 (1280px+)

```
┌────────────────────────────────────────────────────────────────────┐
│  [Logo]  使用者管理 | 評分審核 | 平台數據🔵 | [Admin👤]           │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  📊 平台數據分析 Dashboard                                         │
│  ────────────────────────────────────────────────────────────────  │
│                                                                    │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐     │
│  │總業務員數  │ │總瀏覽數    │ │總聯繫數    │ │平台轉換率  │     │
│  │            │ │            │ │            │ │            │     │
│  │    148     │ │   12,547   │ │    892     │ │   7.11%    │     │
│  │            │ │            │ │            │ │            │     │
│  │   +3% ↑    │ │  +12% ↑    │ │  +11.8% ↑  │ │  -0.14% ↓  │     │
│  │  較上月    │ │  較上月    │ │  較上月    │ │  較上月    │     │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘     │
│                                                                    │
│  ┌──────────────────────────────┐  ┌─────────────────────────┐   │
│  │ 🏆 熱門業務員 Top 10          │  │ 📊 業務員活躍度         │   │
│  │                              │  │                         │   │
│  │ 1. 張三豐     523 瀏覽 9.2%  │  │ 總業務員: 148           │   │
│  │    ⭐⭐⭐⭐⭐              │  │                         │   │
│  │                              │  │ ✅ 活躍: 132 (89.2%)    │   │
│  │ 2. 李四海     487 瀏覽 8.4%  │  │ ❌ 低活躍: 16 (10.8%)   │   │
│  │    ⭐⭐⭐⭐⭐              │  │                         │   │
│  │                              │  │ ┌────────────────────┐ │   │
│  │ 3. 王五郎     456 瀏覽 7.7%  │  │ │ 活躍度分布         │ │   │
│  │    ⭐⭐⭐⭐                │  │ │ 0: 16人            │ │   │
│  │                              │  │ │ 1-10: 28人         │ │   │
│  │ ...                          │  │ │ 11-50: 45人        │ │   │
│  │                              │  │ │ 51-100: 32人       │ │   │
│  │ [查看完整排名 →]             │  │ │ 100+: 27人         │ │   │
│  └──────────────────────────────┘  │ └────────────────────┘ │   │
│                                     └─────────────────────────┘   │
│                                                                    │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ 📈 平台成長趨勢 (過去 30 天)                                  │  │
│  │                                                               │  │
│  │  500│                                                        │  │
│  │     │                    ●瀏覽數                            │  │
│  │  400│              ●                                        │  │
│  │     │         ●                                             │  │
│  │  300│    ●                                                  │  │
│  │     │                                                       │  │
│  │  200│                                                       │  │
│  │     │                                                       │  │
│  │  100│                                                       │  │
│  │     │                                                       │  │
│  │   50│  ●   ●   ●   ●   ●聯繫數                            │  │
│  │     │                                                       │  │
│  │    0└───┬───┬───┬───┬───┬───┬───                           │  │
│  │       12/26 1/2  1/9  1/16 1/23 1/30                        │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

#### 平板版佈局 (768px - 1023px)

```
┌──────────────────────────────────────┐
│  [☰] 平台數據      [🔔] [Admin👤]   │
├──────────────────────────────────────┤
│                                      │
│  📊 平台數據分析                     │
│  ──────────────────────────────────  │
│                                      │
│  ┌────────────┐  ┌────────────┐     │
│  │總業務員數  │  │總瀏覽數    │     │
│  │   148      │  │  12,547    │     │
│  │  +3% ↑     │  │  +12% ↑    │     │
│  └────────────┘  └────────────┘     │
│                                      │
│  ┌────────────┐  ┌────────────┐     │
│  │總聯繫數    │  │平台轉換率  │     │
│  │   892      │  │   7.11%    │     │
│  │ +11.8% ↑   │  │  -0.14% ↓  │     │
│  └────────────┘  └────────────┘     │
│                                      │
│  ┌──────────────────────────────┐   │
│  │  熱門業務員 (堆疊)            │   │
│  └──────────────────────────────┘   │
│                                      │
│  ┌──────────────────────────────┐   │
│  │  業務員活躍度                │   │
│  └──────────────────────────────┘   │
│                                      │
│  ┌──────────────────────────────┐   │
│  │  成長趨勢圖                  │   │
│  └──────────────────────────────┘   │
│                                      │
└──────────────────────────────────────┘
```

#### 手機版佈局 (< 768px)

```
┌──────────────────────┐
│ [☰] 平台數據 [Admin] │
├──────────────────────┤
│                      │
│ 📊 平台數據          │
│                      │
│ ┌──────────────────┐ │
│ │ 總業務員數       │ │
│ │     148          │ │
│ │   +3% ↑          │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │  總瀏覽數        │ │
│ │   12,547         │ │
│ │  +12% ↑          │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │  總聯繫數        │ │
│ │     892          │ │
│ │  +11.8% ↑        │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │ 平台轉換率       │ │
│ │    7.11%         │ │
│ │  -0.14% ↓        │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │ 🏆 Top 10        │ │
│ │ (壓縮卡片式)     │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │ 📊 活躍度        │ │
│ └──────────────────┘ │
│                      │
│ ┌──────────────────┐ │
│ │ 📈 趨勢圖        │ │
│ └──────────────────┘ │
│                      │
└──────────────────────┘
```

---

## 🖱️ 互動設計

### Tab 切換互動

**使用者流程**:
1. 使用者進入頁面 → 預設顯示「過去 7 天」
2. 點擊「今日」Tab → 立即切換
3. 頁面顯示 Loading 狀態（Skeleton Screen）
4. 數據載入完成 → 更新所有 KPI 和圖表

**設計規範**:

```tsx
// Tab 狀態
const [selectedRange, setSelectedRange] = useState<'today' | '7days' | '30days'>('7days');

// Tab 樣式
const tabStyles = {
  default: "px-4 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors",
  active: "px-4 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg border-b-2 border-primary-500"
};

// Tab 互動
<button
  onClick={() => setSelectedRange('today')}
  className={selectedRange === 'today' ? tabStyles.active : tabStyles.default}
  aria-selected={selectedRange === 'today'}
  role="tab"
>
  今日
</button>
```

**動畫**:
- Tab 切換: `transition-colors duration-200`
- 底部指示線滑動: `transition-transform duration-300`
- 內容淡入: `animate-in fade-in duration-300`

### KPI 卡片 Hover 互動

**Hover 效果**:
1. 卡片向上浮起 2px
2. 陰影從 `shadow-md` 變為 `shadow-lg`
3. 顯示詳細 Tooltip（可選）

```tsx
<div className="
  p-6 bg-white rounded-2xl border border-slate-100
  shadow-md hover:shadow-lg
  hover:-translate-y-0.5
  transition-all duration-200
  cursor-pointer
">
  {/* KPI 內容 */}
</div>
```

### 趨勢圖互動

**Recharts 配置**:

```tsx
<LineChart>
  <Tooltip
    content={({ active, payload }) => {
      if (!active || !payload) return null;
      return (
        <div className="bg-white p-3 rounded-lg shadow-lg border border-slate-200">
          <p className="text-sm text-slate-600 mb-1">
            {payload[0]?.payload.date}
          </p>
          <p className="text-sm font-semibold text-primary-600">
            瀏覽數: {payload[0]?.value}
          </p>
          <p className="text-sm font-semibold text-secondary-600">
            聯繫數: {payload[1]?.value}
          </p>
        </div>
      );
    }}
  />
</LineChart>
```

**互動行為**:
- Hover 圖表 → 顯示當日詳細數據
- Tooltip 跟隨滑鼠移動
- 圖表點（Dot）放大顯示

### 聯繫請求列表互動

**點擊行為**:
```tsx
<div
  onClick={() => openContactModal(contact.id)}
  className="
    p-4 bg-white rounded-xl border border-slate-100
    hover:border-primary-200 hover:bg-primary-50
    transition-all duration-200
    cursor-pointer
  "
>
  {/* 聯繫內容 */}
</div>
```

**狀態顏色**:
- `pending`: 黃色 Badge (`bg-warning-100 text-warning-700`)
- `contacted`: 藍色 Badge (`bg-info-100 text-info-700`)
- `closed`: 灰色 Badge (`bg-slate-100 text-slate-600`)

### Loading 狀態設計

**Skeleton Screen** (比轉圈圈感覺更快):

```tsx
{isLoading && (
  <div className="animate-pulse space-y-4">
    {/* KPI Skeleton */}
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
      {[1, 2, 3].map(i => (
        <div key={i} className="p-6 bg-slate-100 rounded-2xl h-32" />
      ))}
    </div>

    {/* Chart Skeleton */}
    <div className="p-6 bg-slate-100 rounded-2xl h-64" />
  </div>
)}
```

### 錯誤狀態設計

**友善的錯誤提示**:

```tsx
{isError && (
  <div className="flex flex-col items-center justify-center py-12">
    <AlertCircleIcon className="h-16 w-16 text-error-500 mb-4" />
    <h3 className="text-lg font-semibold text-slate-900 mb-2">
      糟糕！載入失敗
    </h3>
    <p className="text-sm text-slate-600 mb-6 text-center max-w-md">
      請檢查網路連線，或稍後再試
    </p>
    <Button onClick={refetch} variant="outline">
      <RefreshCwIcon className="mr-2 h-4 w-4" />
      重新整理
    </Button>
  </div>
)}
```

### 空狀態設計

**無數據時的友善提示**:

```tsx
{contacts.length === 0 && (
  <div className="flex flex-col items-center justify-center py-12">
    <MessageSquareIcon className="h-16 w-16 text-slate-300 mb-4" />
    <h3 className="text-lg font-semibold text-slate-900 mb-2">
      尚無聯繫記錄
    </h3>
    <p className="text-sm text-slate-600 text-center max-w-md">
      當有客戶透過您的檔案聯繫時，會顯示在這裡
    </p>
  </div>
)}
```

---

## 🎨 視覺設計

### 色彩系統

#### KPI 卡片配色

```tsx
// 瀏覽數卡片
{
  background: 'bg-sky-50',        // 淺藍背景
  icon: 'text-sky-600',           // 藍色圖示
  number: 'text-sky-900',         // 深藍數字
  label: 'text-slate-600',        // 灰色標籤
}

// 聯繫數卡片
{
  background: 'bg-teal-50',       // 淺青背景
  icon: 'text-teal-600',          // 青色圖示
  number: 'text-teal-900',        // 深青數字
  label: 'text-slate-600',
}

// 增長率卡片 (正成長)
{
  background: 'bg-green-50',      // 淺綠背景
  icon: 'text-green-600',         // 綠色圖示
  number: 'text-green-900',       // 深綠數字
  arrow: 'text-green-600',        // 綠色箭頭 ↑
}

// 增長率卡片 (負成長)
{
  background: 'bg-red-50',        // 淺紅背景
  icon: 'text-red-600',           // 紅色圖示
  number: 'text-red-900',         // 深紅數字
  arrow: 'text-red-600',          // 紅色箭頭 ↓
}
```

#### 圖表配色 (Recharts)

```tsx
<LineChart>
  <Line
    type="monotone"
    dataKey="profile_views"
    stroke="#0ea5e9"      // Sky-500 瀏覽數
    strokeWidth={2}
    dot={{ fill: '#0ea5e9', r: 4 }}
  />
  <Line
    type="monotone"
    dataKey="contact_requests"
    stroke="#14b8a6"      // Teal-500 聯繫數
    strokeWidth={2}
    dot={{ fill: '#14b8a6', r: 4 }}
  />
  <CartesianGrid
    stroke="#e2e8f0"      // Slate-200 網格線
    strokeDasharray="3 3"
  />
  <XAxis
    stroke="#94a3b8"      // Slate-400 軸線
    style={{ fontSize: 12 }}
  />
  <YAxis
    stroke="#94a3b8"
    style={{ fontSize: 12 }}
  />
</LineChart>
```

#### 狀態徽章配色

```tsx
// 待處理 (Pending)
<Badge className="bg-warning-100 text-warning-700 border-warning-200">
  待處理
</Badge>

// 已聯繫 (Contacted)
<Badge className="bg-info-100 text-info-700 border-info-200">
  已聯繫
</Badge>

// 已結案 (Closed)
<Badge className="bg-slate-100 text-slate-600 border-slate-200">
  已結案
</Badge>
```

### 字體階層

```tsx
// 頁面標題
<h1 className="text-3xl font-bold text-slate-900 mb-6">
  數據分析 Dashboard
</h1>

// KPI 大數字
<p className="text-4xl font-bold text-sky-900">
  342
</p>

// KPI 標籤
<p className="text-sm font-medium text-slate-600">
  檔案瀏覽數
</p>

// 增長率
<p className="text-lg font-semibold text-green-600">
  +14.77% ↑
</p>

// 增長率說明
<p className="text-xs text-slate-500">
  較上週
</p>

// 圖表標題
<h3 className="text-xl font-semibold text-slate-900 mb-4">
  趨勢分析
</h3>

// 列表項目標題
<p className="text-base font-medium text-slate-900">
  王小明
</p>

// 列表項目內文
<p className="text-sm text-slate-600">
  您好，我對您的產品很有興趣...
</p>
```

### 間距系統

```tsx
// 頁面容器
<div className="container mx-auto px-4 md:px-6 lg:px-8 py-8">

// KPI 卡片間距
<div className="grid grid-cols-1 md:grid-cols-3 gap-6">

// 卡片內邊距
<div className="p-6 rounded-2xl">

// 區塊間距
<div className="space-y-8">

// 標題與內容間距
<div>
  <h2 className="text-2xl font-bold mb-6">標題</h2>
  <div>內容</div>
</div>

// 圖示與文字間距
<div className="flex items-center gap-2">
  <Icon className="h-5 w-5" />
  <span>文字</span>
</div>
```

### 圓角系統

```tsx
// KPI 卡片
className="rounded-2xl"  // 16px

// 小卡片、輸入框
className="rounded-xl"   // 12px

// 按鈕
className="rounded-lg"   // 8px

// Badge
className="rounded-full" // 完全圓角
```

### 陰影系統

```tsx
// KPI 卡片預設陰影
className="shadow-md"    // 中等陰影

// KPI 卡片 Hover 陰影
className="hover:shadow-lg" // 大陰影

// 聯繫卡片
className="shadow-sm"    // 小陰影

// Modal (未來)
className="shadow-xl"    // 特大陰影
```

---

## 📱 響應式設計

### 斷點策略

```tsx
// Tailwind 斷點
{
  sm: '640px',   // 大手機
  md: '768px',   // 平板 ⭐
  lg: '1024px',  // 桌面 ⭐
  xl: '1280px',  // 大桌面
  '2xl': '1536px'
}
```

### 業務員 Dashboard 響應式

#### KPI 卡片佈局

```tsx
<div className="
  grid
  grid-cols-1        // Mobile: 1 欄
  md:grid-cols-2     // Tablet: 2 欄
  lg:grid-cols-3     // Desktop: 3 欄
  gap-4 md:gap-6
">
  <StatCard title="檔案瀏覽數" value={342} />
  <StatCard title="聯繫請求數" value={28} />
  <StatCard title="轉換率" value="8.19%" />
</div>
```

#### 時間範圍 Tab

```tsx
<div className="
  flex flex-wrap gap-2
  md:inline-flex md:gap-1
  md:bg-slate-100 md:p-1 md:rounded-lg
">
  <Tab>今日</Tab>
  <Tab>過去 7 天</Tab>
  <Tab>過去 30 天</Tab>
</div>
```

#### 趨勢圖表

```tsx
<ResponsiveContainer
  width="100%"
  height={300}              // Mobile: 300px
  className="md:h-[400px]"  // Tablet+: 400px
>
  <LineChart data={trends}>
    {/* ... */}
  </LineChart>
</ResponsiveContainer>
```

#### 聯繫列表

```tsx
// Mobile: 壓縮卡片
<div className="
  p-3                       // Mobile: 小內邊距
  md:p-4                    // Tablet+: 標準內邊距
  rounded-xl
  bg-white
">
  <div className="flex flex-col md:flex-row md:items-center">
    <div className="flex-1">
      <p className="text-sm md:text-base font-medium">王小明</p>
      <p className="text-xs md:text-sm text-slate-600 line-clamp-2 md:line-clamp-1">
        訊息內容...
      </p>
    </div>
    <Badge size="sm" className="mt-2 md:mt-0 md:ml-4">
      待處理
    </Badge>
  </div>
</div>
```

### 管理員 Dashboard 響應式

#### KPI 卡片佈局

```tsx
<div className="
  grid
  grid-cols-1        // Mobile: 1 欄
  md:grid-cols-2     // Tablet: 2 欄
  lg:grid-cols-4     // Desktop: 4 欄
  gap-4 md:gap-6
">
  <StatCard title="總業務員數" value={148} />
  <StatCard title="總瀏覽數" value="12,547" />
  <StatCard title="總聯繫數" value={892} />
  <StatCard title="平台轉換率" value="7.11%" />
</div>
```

#### 兩欄佈局

```tsx
<div className="
  grid
  grid-cols-1        // Mobile: 堆疊
  lg:grid-cols-2     // Desktop: 並排
  gap-6
">
  <TopSalespersonsCard />
  <ActivityCard />
</div>
```

### 觸控優化

#### 最小觸控目標

```tsx
// 按鈕最小尺寸: 44x44 px
<button className="
  min-h-[44px] min-w-[44px]
  px-4 py-2
">
  按鈕
</button>

// Tab 觸控區域
<button className="
  px-4 py-3              // 足夠的觸控區域
  text-sm font-medium
">
  今日
</button>

// 卡片點擊區域
<div className="
  p-4 md:p-6            // Mobile 較大內邊距
  cursor-pointer
  touch-manipulation    // 優化觸控回應
">
  卡片內容
</div>
```

#### 間距充足

```tsx
// 按鈕間距至少 8px
<div className="flex gap-2 md:gap-3">
  <button>取消</button>
  <button>確認</button>
</div>

// 列表項目間距
<div className="space-y-3 md:space-y-4">
  {items.map(item => <Item key={item.id} />)}
</div>
```

---

## ♿ 無障礙設計

### ARIA 屬性

#### Tab 組件

```tsx
<div role="tablist" aria-label="時間範圍選擇">
  <button
    role="tab"
    aria-selected={selectedRange === 'today'}
    aria-controls="stats-panel"
    id="tab-today"
  >
    今日
  </button>
  <button
    role="tab"
    aria-selected={selectedRange === '7days'}
    aria-controls="stats-panel"
    id="tab-7days"
  >
    過去 7 天
  </button>
</div>

<div
  role="tabpanel"
  id="stats-panel"
  aria-labelledby={`tab-${selectedRange}`}
>
  {/* Stats Content */}
</div>
```

#### KPI 卡片

```tsx
<div
  role="region"
  aria-labelledby="profile-views-label"
>
  <h3 id="profile-views-label" className="sr-only">
    檔案瀏覽數統計
  </h3>
  <p className="text-sm font-medium text-slate-600">
    檔案瀏覽數
  </p>
  <p
    className="text-4xl font-bold text-sky-900"
    aria-label="342 次瀏覽"
  >
    342
  </p>
  <p
    className="text-lg text-green-600"
    aria-label="較上週成長 14.77%"
  >
    +14.77% ↑
  </p>
</div>
```

#### Loading 狀態

```tsx
<div
  role="status"
  aria-live="polite"
  aria-busy={isLoading}
>
  {isLoading ? (
    <>
      <span className="sr-only">載入中...</span>
      <SkeletonScreen />
    </>
  ) : (
    <StatsContent />
  )}
</div>
```

#### 錯誤訊息

```tsx
<div
  role="alert"
  aria-live="assertive"
>
  <p className="text-error-600">
    載入失敗，請重試
  </p>
</div>
```

### 鍵盤導航

#### Tab 順序

```tsx
// 確保邏輯的 Tab 順序
<div>
  <button tabIndex={0}>今日</button>      {/* Tab 1 */}
  <button tabIndex={0}>過去 7 天</button>  {/* Tab 2 */}
  <button tabIndex={0}>過去 30 天</button> {/* Tab 3 */}
</div>
```

#### Focus 指示

```tsx
<button className="
  focus:outline-none
  focus:ring-2
  focus:ring-primary-500
  focus:ring-offset-2
  transition-all
">
  按鈕
</button>
```

#### 跳過連結

```tsx
<a
  href="#main-content"
  className="
    sr-only
    focus:not-sr-only
    focus:fixed focus:top-4 focus:left-4
    focus:z-50
    focus:px-4 focus:py-2
    focus:bg-primary-600 focus:text-white
    focus:rounded-lg
  "
>
  跳到主要內容
</a>

<main id="main-content">
  {/* Dashboard Content */}
</main>
```

### 色彩對比

所有文字和背景組合符合 WCAG AA 標準:

```tsx
// ✅ 良好對比 (符合 AA)
<p className="text-slate-900 bg-white">      // 19.64:1
<p className="text-slate-700 bg-white">      // 10.69:1
<p className="text-slate-600 bg-white">      // 7.74:1
<p className="text-white bg-primary-500">    // 4.93:1

// ❌ 對比不足 (不使用)
<p className="text-slate-400 bg-white">      // 2.88:1
<p className="text-slate-300 bg-white">      // 1.98:1
```

### Screen Reader 友善

```tsx
// 隱藏裝飾性元素
<svg aria-hidden="true">
  <path d="..." />
</svg>

// 提供額外說明
<span className="sr-only">
  檔案瀏覽數 342 次，較上週成長 14.77%
</span>
<div aria-hidden="true">
  342
  <span>+14.77% ↑</span>
</div>

// Live Region (動態更新)
<div aria-live="polite" aria-atomic="true">
  {successMessage && <p>{successMessage}</p>}
</div>
```

---

## ⚡ 效能要求

### Core Web Vitals 目標

```
LCP (Largest Contentful Paint) - 最大內容繪製
目標: < 2.0 秒
優化:
- 使用 Next.js SSR 預渲染 KPI 卡片
- 圖表使用 Lazy Loading
- 優化字體載入

FID (First Input Delay) - 首次輸入延遲
目標: < 100 毫秒
優化:
- Tab 切換使用 React.memo
- Debounce 處理使用者輸入

CLS (Cumulative Layout Shift) - 累積版面配置位移
目標: < 0.1
優化:
- Skeleton Screen 與實際內容尺寸一致
- 圖表設定固定高度
- 避免插入動態內容
```

### API 回應時間

```
業務員端點:
- P50: < 100ms
- P95: < 200ms
- P99: < 500ms

管理員端點:
- P50: < 200ms
- P95: < 500ms
- P99: < 1000ms
```

### React Query 快取策略

```tsx
// 業務員統計數據
useQuery({
  queryKey: ['salesperson-stats', range],
  queryFn: () => getSalespersonStats(range),
  staleTime: 5 * 60 * 1000,      // 5 分鐘
  cacheTime: 10 * 60 * 1000,     // 10 分鐘
  refetchOnWindowFocus: true,    // 視窗 Focus 時重新整理
  refetchOnMount: true,
});

// 管理員平台概覽
useQuery({
  queryKey: ['admin-overview', range],
  queryFn: () => getAdminOverview(range),
  staleTime: 5 * 60 * 1000,
  cacheTime: 10 * 60 * 1000,
  refetchInterval: 60 * 1000,    // 每分鐘自動重新整理
});
```

### 圖片與資源優化

```tsx
// Recharts Lazy Loading
import dynamic from 'next/dynamic';

const TrendChart = dynamic(() => import('@/components/charts/TrendChart'), {
  loading: () => <SkeletonChart />,
  ssr: false,  // 圖表不需要 SSR
});

// 圖示優化
import { BarChart3Icon } from 'lucide-react';  // Tree-shakeable icons
```

### Bundle Size 優化

```tsx
// ❌ 不良: Barrel imports
import { Button, Card, Badge } from '@/components';

// ✅ 良好: Direct imports
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
```

---

## 📋 設計檢查清單

### 開發前檢查

- [ ] 閱讀完整的 UI/UX 規格
- [ ] 了解使用者需求和痛點
- [ ] 確認設計系統規範
- [ ] 準備所需的 API 端點

### 開發中檢查

- [ ] 遵循設計系統色彩、字體、間距
- [ ] 實作 Loading / Empty / Error 狀態
- [ ] 響應式設計 (Mobile / Tablet / Desktop)
- [ ] 無障礙性屬性 (ARIA)
- [ ] 效能優化 (React Query, Lazy Loading)

### 開發後檢查

- [ ] 所有斷點測試通過
- [ ] 鍵盤導航正常
- [ ] Screen Reader 可讀
- [ ] 色彩對比符合 WCAG AA
- [ ] 效能指標達標 (LCP < 2s)
- [ ] 互動動畫流暢 (60fps)
- [ ] 錯誤處理完善

---

**文檔版本**: 1.0
**最後更新**: 2026-01-24
**設計師**: Senior Product Designer
**審查者**: Development Team
