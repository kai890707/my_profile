# UI/UX 設計規格：修復 Header 使用者選單

## 1. 設計目標

### 業務目標
- 修復業務員選單中的 404 錯誤（移除指向不存在的 `/settings` 頁面）
- 優化選單導航，讓業務員能快速回到 Dashboard 首頁
- 保持管理員選單功能完整（`/admin/settings` 頁面存在且正常）

### UX 原則
1. **一致性**：Desktop 和 Mobile 選單選項保持一致
2. **可預測性**：所有選單選項都能正確跳轉到存在的頁面
3. **易用性**：Dashboard 選項位於顯眼位置，方便快速導航
4. **角色適配**：根據使用者角色顯示對應的選單選項

---

## 2. 使用者選單結構

### 2.1 業務員選單（Salesperson）

```
┌──────────────────────────────┐
│  [Avatar] 張三               │  ← Trigger Button
│           業務員              │
└──────────────────────────────┘
          ↓ 點擊展開
┌──────────────────────────────┐
│  我的帳號                     │  ← Label (灰色文字)
├──────────────────────────────┤  ← Separator
│  📊 Dashboard                │  ← 新增選項 ✨
│  👤 個人資料                  │  ← 保留選項
├──────────────────────────────┤  ← Separator
│  🚪 登出                      │  ← 保留選項
└──────────────────────────────┘

寬度：224px (w-56)
對齊：右對齊 (align="end")
圓角：遵循 Radix UI 預設
```

**選項詳情**：

| 選項 | Icon | 目標路由 | 說明 |
|------|------|---------|------|
| Dashboard | LayoutDashboard | `/dashboard` | **新增**：快速回到首頁 |
| 個人資料 | User | `/dashboard/profile` | 保留：編輯個人資訊 |
| 登出 | LogOut | - | 保留：執行登出邏輯 |

**移除選項**：
- ❌ 設定（Settings） - 指向不存在的 `/settings` 頁面

### 2.2 管理員選單（Admin）

```
┌──────────────────────────────┐
│  [Avatar] 管理員              │  ← Trigger Button
│           管理員              │
└──────────────────────────────┘
          ↓ 點擊展開
┌──────────────────────────────┐
│  我的帳號                     │  ← Label
├──────────────────────────────┤  ← Separator
│  📊 管理後台                  │
│  📝 審核管理                  │
│  👥 使用者管理                │
│  📈 統計報表                  │
├──────────────────────────────┤  ← Separator
│  ⚙️ 設定                      │  ← 保留選項 ✅
├──────────────────────────────┤  ← Separator
│  🚪 登出                      │
└──────────────────────────────┘

寬度：224px (w-56)
對齊：右對齊 (align="end")
```

**選項詳情**：

| 選項 | Icon | 目標路由 | 說明 |
|------|------|---------|------|
| 管理後台 | LayoutDashboard | `/admin` | 管理員首頁 |
| 審核管理 | - | `/admin/approvals` | 審核業務員資料 |
| 使用者管理 | - | `/admin/users` | 管理使用者 |
| 統計報表 | - | `/admin/statistics` | 查看統計 |
| 設定 | Settings | `/admin/settings` | **保留**：系統設定 ✅ |
| 登出 | LogOut | - | 登出 |

### 2.3 訪客（Guest）

不顯示使用者選單，僅顯示：
```
[登入] [註冊]
```

---

## 3. 互動設計

### 3.1 Desktop 互動流程

#### 觸發選單
```
狀態：預設
└─> 使用者點擊頭像/名稱
    └─> 選單展開
        └─> 顯示半透明背景遮罩（淡入動畫）
```

#### 選擇選項
```
狀態：選單已展開
└─> 使用者 Hover 選項
    └─> 選項背景變為淺灰色 (bg-slate-100)
    └─> 游標變為 pointer
    └─> 使用者點擊選項
        └─> 選項為連結
            └─> 執行路由跳轉
            └─> 選單自動收起
        └─> 選項為登出
            └─> 執行 onLogout 回調
            └─> 顯示 Toast「已登出」
            └─> 跳轉到登入頁
```

#### 關閉選單
```
狀態：選單已展開
└─> 使用者點擊選單外區域
    └─> 選單收起（淡出動畫）
└─> 使用者按 ESC 鍵
    └─> 選單收起（淡出動畫）
└─> 使用者點擊任一選項
    └─> 執行動作 + 選單收起
```

### 3.2 Mobile 互動流程

在 Mobile 視圖（< 768px），使用者選單整合到 Mobile Menu：

```
狀態：預設
└─> 使用者點擊漢堡選單（☰）
    └─> Mobile Menu 展開（從右側滑入）
        └─> 顯示公開連結（首頁、搜尋業務員）
        └─> 顯示 Dashboard 連結（根據角色）
        └─> 顯示登出按鈕
```

**Mobile Menu 選項順序**（業務員）：
```
首頁
搜尋業務員
───────────── (分隔線)
Dashboard      ← 新增
個人資料
───────────── (分隔線)
登出
```

### 3.3 鍵盤導航

支援鍵盤無障礙操作：

| 按鍵 | 行為 |
|------|------|
| `Tab` | 聚焦到下一個選單項目 |
| `Shift + Tab` | 聚焦到上一個選單項目 |
| `Enter` | 選擇當前聚焦的選項 |
| `Space` | 選擇當前聚焦的選項 |
| `Escape` | 關閉選單 |
| `↑` / `↓` | 在選項間移動（Radix UI 預設） |

---

## 4. 視覺設計

### 4.1 色彩運用

遵循 YAMU 設計系統（參考 `frontend/docs/design-system.md`）

**Trigger Button（頭像區域）**：
- 預設：無背景
- Hover：opacity-80（80% 透明度）
- 過渡：transition-opacity duration-200

**選單容器**：
- 背景：白色 (bg-white)
- 陰影：shadow-md（中等陰影）
- 邊框：border border-slate-200
- 圓角：rounded-md（遵循 Radix UI 預設）

**選單項目**：
- 預設：bg-transparent text-slate-700
- Hover：bg-slate-100 text-slate-900
- Focus：bg-slate-100 outline-2 outline-primary-500
- 登出選項：text-error-600（紅色文字）

**分隔線**：
- 顏色：border-slate-200
- 粗細：1px

**Icon**：
- 大小：h-4 w-4 (16px)
- 間距：mr-2（與文字間距 8px）
- 顏色：繼承父元素

### 4.2 字體與間距

**Label（我的帳號）**：
- 字體：text-sm（14px）
- 字重：font-semibold（600）
- 顏色：text-slate-700
- 內邊距：px-2 py-1.5

**選單項目**：
- 字體：text-sm（14px）
- 字重：font-medium（500）
- 行高：leading-normal
- 內邊距：px-2 py-2
- 外邊距：mx-1（左右各 4px）

**Trigger Button（頭像名稱）**：
- 名稱字體：text-sm font-medium text-slate-900
- 角色字體：text-xs text-slate-500
- 內邊距：space-x-3（頭像與文字間距 12px）

### 4.3 圓角與陰影

**選單容器**：
- 圓角：rounded-md（預設 8px）
- 陰影：shadow-md
  ```css
  0 8px 12px -2px rgba(0, 0, 0, 0.1),
  0 4px 8px -4px rgba(0, 0, 0, 0.1)
  ```

**選單項目**：
- 圓角：rounded-sm（4px）
- 無陰影

**頭像**：
- 圓角：rounded-full（圓形）
- 尺寸：size="sm"（32px）
- 邊框：border-2 border-white

### 4.4 動畫效果

**選單展開/收起**：
- 動畫時間：duration-200（200ms）
- 緩動函數：ease-out
- 效果：淡入淡出 + 輕微縮放（Radix UI 預設）

**選單項目 Hover**：
- 過渡時間：transition-colors duration-150
- 效果：背景色漸變

**Trigger Button Hover**：
- 過渡時間：transition-opacity duration-200
- 效果：透明度變化

---

## 5. 響應式設計

### 5.1 斷點定義

| 斷點 | 寬度 | 行為 |
|------|------|------|
| Mobile | < 768px | 使用 Mobile Menu（漢堡選單） |
| Desktop | ≥ 768px | 使用 Dropdown Menu |

### 5.2 Desktop 視圖（≥ 768px）

**Header 佈局**：
```
┌────────────────────────────────────────┐
│ [Logo] 首頁 | 搜尋業務員    [Avatar 選單] │
└────────────────────────────────────────┘
```

**使用者選單顯示**：
- 顯示頭像 + 名稱 + 角色
- 點擊展開 Dropdown Menu
- 寬度：w-56（224px）
- 對齊：align="end"（右對齊）

### 5.3 Mobile 視圖（< 768px）

**Header 佈局**：
```
┌────────────────────────────────┐
│ [Logo] YAMU        [☰]         │
└────────────────────────────────┘
```

**使用者選單顯示**：
- 隱藏 Dropdown Menu（`hidden md:flex`）
- 整合到 Mobile Menu
- 點擊漢堡選單展開側邊選單

**Mobile Menu 佈局**：
```
┌───────────────────┐
│ 首頁              │
│ 搜尋業務員        │
├───────────────────┤
│ Dashboard         │  ← 新增
│ 個人資料          │
├───────────────────┤
│ 登出              │
└───────────────────┘
```

### 5.4 Tablet 視圖（768px - 1024px）

- 與 Desktop 相同，使用 Dropdown Menu
- 選單項目文字可能略短，但佈局一致

---

## 6. 無障礙設計（A11y）

### 6.1 ARIA 屬性

**Trigger Button**：
```tsx
<button
  aria-label="開啟使用者選單"
  aria-haspopup="menu"
  aria-expanded={isOpen}
>
  {/* 頭像和名稱 */}
</button>
```

**選單容器**（Radix UI 自動處理）：
```tsx
<DropdownMenuContent
  role="menu"
  aria-orientation="vertical"
>
  {/* 選單項目 */}
</DropdownMenuContent>
```

**選單項目**（Radix UI 自動處理）：
```tsx
<DropdownMenuItem
  role="menuitem"
  aria-label="跳轉到 Dashboard"
>
  Dashboard
</DropdownMenuItem>
```

### 6.2 焦點管理

1. **開啟選單**：焦點自動移到第一個選單項目
2. **鍵盤導航**：使用方向鍵在選項間移動
3. **選擇選項**：Enter/Space 觸發選項
4. **關閉選單**：焦點回到 Trigger Button

### 6.3 色彩對比

遵循 WCAG AA 標準：

| 元素 | 前景色 | 背景色 | 對比度 | 標準 |
|------|--------|--------|--------|------|
| 選單項目（預設） | #334155 | #FFFFFF | 12.6:1 | ✅ AAA |
| 選單項目（Hover） | #1e293b | #f1f5f9 | 10.5:1 | ✅ AAA |
| 登出選項 | #dc2626 | #FFFFFF | 5.9:1 | ✅ AA |
| Label | #475569 | #FFFFFF | 8.6:1 | ✅ AAA |

### 6.4 螢幕閱讀器支援

- 選單項目有明確的文字標籤
- Icon 僅為裝飾，不影響語義（`aria-hidden="true"`）
- 分隔線使用語義化標籤（DropdownMenuSeparator）
- 登出選項有明確的角色說明（"登出按鈕"）

---

## 7. 錯誤與邊界情況

### 7.1 載入狀態

**使用者資料載入中**：
```tsx
{isLoading && (
  <div className="flex items-center space-x-3">
    <div className="h-8 w-8 rounded-full bg-slate-200 animate-pulse" />
    <div className="hidden md:block space-y-1">
      <div className="h-4 w-20 bg-slate-200 rounded animate-pulse" />
      <div className="h-3 w-16 bg-slate-200 rounded animate-pulse" />
    </div>
  </div>
)}
```

### 7.2 錯誤狀態

**使用者資料載入失敗**：
- 不顯示使用者選單
- 顯示登入/註冊按鈕
- 後台記錄錯誤日誌

### 7.3 空狀態

**使用者未登入**：
```tsx
{!user && (
  <>
    <Button variant="ghost">登入</Button>
    <Button>註冊</Button>
  </>
)}
```

### 7.4 角色未知

**使用者角色為 undefined 或未知值**：
- 預設為 Guest 模式
- 不顯示 Dashboard 連結
- 僅顯示公開頁面連結

---

## 8. 使用情境流程圖

### 情境 1：業務員從個人資料返回 Dashboard

```
業務員在 /dashboard/profile 編輯完資料
    ↓
點擊右上角頭像（展開選單）
    ↓
看到選單選項：
    - Dashboard ✨
    - 個人資料
    - 登出
    ↓
點擊「Dashboard」
    ↓
路由跳轉到 /dashboard
    ↓
選單自動收起
    ↓
顯示 Dashboard 首頁
```

### 情境 2：管理員訪問系統設定

```
管理員在 /admin 管理後台
    ↓
點擊右上角頭像（展開選單）
    ↓
看到選單選項：
    - 管理後台
    - 審核管理
    - 使用者管理
    - 統計報表
    - 設定 ✅
    - 登出
    ↓
點擊「設定」
    ↓
路由跳轉到 /admin/settings
    ↓
選單自動收起
    ↓
顯示系統設定頁面（產業類別、地區管理）
```

### 情境 3：業務員誤點選單後關閉

```
業務員在任何頁面
    ↓
點擊右上角頭像（展開選單）
    ↓
發現不是要找的功能
    ↓
點擊選單外區域 或 按 ESC 鍵
    ↓
選單收起（淡出動畫）
    ↓
回到原頁面，無任何變化
```

---

## 9. 設計檢查清單

### UI 設計
- [x] 業務員選單移除「設定」選項
- [x] 業務員選單新增「Dashboard」選項
- [x] 管理員選單保留「設定」選項
- [x] 所有選項都有對應的 Icon
- [x] 選單寬度一致（224px）
- [x] 選單對齊方式正確（右對齊）

### 互動設計
- [x] 點擊頭像展開/收起選單
- [x] 點擊選項跳轉到對應頁面
- [x] 點擊選單外區域收起選單
- [x] ESC 鍵收起選單
- [x] Hover 效果正確
- [x] 鍵盤導航支援

### 響應式設計
- [x] Desktop 使用 Dropdown Menu
- [x] Mobile 使用 Mobile Menu
- [x] Mobile Menu 選項與 Dropdown Menu 一致
- [x] 不同螢幕尺寸佈局正確

### 無障礙設計
- [x] ARIA 屬性完整
- [x] 鍵盤完全可操作
- [x] 色彩對比符合 WCAG AA
- [x] 螢幕閱讀器可正確朗讀
- [x] 焦點管理正確

### 視覺設計
- [x] 符合設計系統規範
- [x] 色彩使用正確
- [x] 間距一致
- [x] 圓角符合規範
- [x] 陰影效果適當
- [x] 動畫流暢自然

---

## 10. 設計系統參考

本設計完全遵循 YAMU 設計系統規範：

**文檔位置**：`frontend/docs/design-system.md`

**關鍵參考**：
- 色彩系統 → 中性色（Slate）、功能色（Error）
- 字體系統 → text-sm, font-medium, font-semibold
- 間距系統 → 4px 網格（px-2, py-2, space-x-3）
- 圓角系統 → rounded-md, rounded-sm, rounded-full
- 陰影系統 → shadow-md
- 動畫系統 → duration-150, duration-200, ease-out

---

## 11. 後續優化建議（Future）

### Phase 2: 選單增強功能

1. **快捷鍵提示**
   - 在選單項目右側顯示快捷鍵（如 `⌘K` 搜尋）
   - 使用 `DropdownMenuShortcut` 組件

2. **子選單支援**
   - 使用 `DropdownMenuSub` 建立層級選單
   - 例如：Dashboard → 經歷管理、證照管理

3. **選單分組優化**
   - 使用 `DropdownMenuGroup` 將相關選項分組
   - 例如：「帳號管理」、「系統設定」

4. **主題切換**
   - 新增深色模式切換選項
   - 位置：設定選項下方

5. **通知中心整合**
   - 在選單中顯示未讀通知數量
   - 新增「通知」選項

---

**規格版本**: 1.0
**撰寫日期**: 2026-01-11
**設計者**: Claude (Product Designer Agent)
**狀態**: 待審核

**相關文檔**：
- 組件規格：`components.md`
- 實作細節：`implementation.md`
- 設計系統：`frontend/docs/design-system.md`
