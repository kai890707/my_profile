# Proposal: 修復 Header 使用者選單 404 錯誤與缺失 Dashboard 選項

## 1. 背景與目標

### 業務背景

**問題描述**:
業務員登入後，右上角的使用者選單（Dropdown Menu）存在以下問題：

1. **404 錯誤**: 點擊「設定」選項跳轉到 `/settings` 頁面，但此頁面不存在，導致 404 錯誤
2. **功能缺失**: 使用者選單缺少「Dashboard」選項，業務員需要透過其他方式（如側邊欄、URL 直接輸入）才能回到 Dashboard 首頁

**當前狀況**:
```
┌─────────────────────┐
│  使用者名稱          │
│  業務員              │
├─────────────────────┤
│ 我的帳號             │
│ 📊 個人中心          │  ← 跳到 /dashboard
│ 👤 個人資料          │  ← 跳到 /dashboard/profile
├─────────────────────┤
│ ⚙ 設定              │  ← 跳到 /settings (404 錯誤!)
│ 🚪 登出             │
└─────────────────────┘
```

**預期狀況**:
```
┌─────────────────────┐
│  使用者名稱          │
│  業務員              │
├─────────────────────┤
│ 我的帳號             │
│ 📊 Dashboard        │  ← 跳到 /dashboard (新增)
│ 👤 個人資料          │  ← 跳到 /dashboard/profile
├─────────────────────┤
│ 🚪 登出             │  ← 移除「設定」選項
└─────────────────────┘
```

### 目標使用者

- **主要使用者**: 業務員 (role: salesperson)
- **次要使用者**: 管理員 (role: admin)
- **不受影響**: 一般訪客 (未登入)

### 成功指標

1. ✅ 業務員點擊選單內的任何選項都能正確跳轉，無 404 錯誤
2. ✅ 業務員能從使用者選單快速返回 Dashboard 首頁
3. ✅ 管理員選單保持正常運作（Admin 有獨立的 /admin/settings 頁面）
4. ✅ 程式碼符合現有設計系統和路由結構

### 不做這個功能會有什麼影響

- **使用者體驗差**: 點擊設定後看到 404 頁面，讓使用者誤以為系統有問題
- **導航困難**: 業務員無法從選單快速回到 Dashboard 首頁
- **信任度降低**: 404 錯誤會降低使用者對系統的信任
- **支援成本增加**: 使用者可能因此聯繫客服詢問問題

---

## 2. 功能描述

### 核心功能

修復 Header 組件中的使用者選單（User Dropdown Menu），確保所有選項都能正確跳轉到存在的頁面。

### 使用流程

**修復前**:
1. 使用者（業務員）登入系統
2. 點擊右上角頭像，展開選單
3. 看到「設定」選項
4. 點擊「設定」→ 跳轉到 `/settings`
5. **結果**: 顯示 404 頁面 ❌

**修復後**:
1. 使用者（業務員）登入系統
2. 點擊右上角頭像，展開選單
3. 看到「Dashboard」選項（位於「我的帳號」下方）
4. 點擊「Dashboard」→ 跳轉到 `/dashboard`
5. **結果**: 正確顯示 Dashboard 頁面 ✅

**管理員流程（不受影響）**:
1. 管理員登入系統
2. 點擊右上角頭像，展開選單
3. 看到「管理後台」、「設定」等選項
4. 點擊「設定」→ 跳轉到 `/admin/settings`
5. **結果**: 正確顯示管理員設定頁面 ✅

### 使用情境範例

**情境 1: 業務員瀏覽個人資料後想回到 Dashboard 首頁**
- **使用者**: 業務員在 `/dashboard/profile` 頁面編輯完個人資料
- **操作**: 點擊右上角頭像 → 選擇「Dashboard」
- **系統**: 跳轉到 `/dashboard` 首頁
- **結果**: 快速返回 Dashboard 概覽頁面

**情境 2: 業務員誤點選單中不存在的頁面**
- **使用者**: 業務員在任何頁面
- **操作**: 點擊右上角頭像 → 看到選單
- **系統**: 選單中只顯示存在的頁面選項
- **結果**: 所有選項都能正確導航，無 404 錯誤

**情境 3: 管理員使用系統設定**
- **使用者**: 管理員需要管理產業類別和地區
- **操作**: 點擊右上角頭像 → 選擇「設定」
- **系統**: 跳轉到 `/admin/settings`
- **結果**: 正確顯示系統設定頁面（產業類別、地區管理）

---

## 3. 功能範圍

### In Scope（本次實作）

#### ✅ Frontend 修復
- ✅ **修改 Header 組件** (`frontend/components/layout/header.tsx`)
  - 移除業務員選單中的「設定」選項（指向不存在的 `/settings`）
  - 保留管理員選單中的「設定」選項（指向 `/admin/settings`）
  - 確保 `getDashboardLinks()` 返回的所有連結都指向存在的頁面

#### ✅ 路由驗證
- ✅ 確認業務員可訪問的所有路由都存在
  - `/dashboard` - Dashboard 首頁 ✅ (已存在)
  - `/dashboard/profile` - 個人資料 ✅ (已存在)
  - `/dashboard/experiences` - 經歷管理 ✅ (已存在)
  - `/dashboard/certifications` - 證照管理 ✅ (已存在)
  - `/dashboard/approval-status` - 審核狀態 ✅ (已存在)

#### ✅ UI/UX 調整
- ✅ 業務員選單結構優化
  - 「我的帳號」標籤
  - 「Dashboard」選項（新增，使用 LayoutDashboard icon）
  - 「個人資料」選項（保留，使用 User icon）
  - 分隔線
  - 「登出」選項（保留）

### Out of Scope（不在範圍內）

#### ❌ 新增業務員設定頁面
- **原因**: 目前業務員沒有系統設定需求，所有可編輯的資料都已經在「個人資料」頁面
- **未來規劃**: 如果需要業務員專屬的系統設定（如通知偏好、隱私設定），可在未來版本新增

#### ❌ 修改管理員選單
- **原因**: 管理員的 `/admin/settings` 頁面已存在且運作正常
- **現狀**: 管理員選單保持不變

#### ❌ 新增 404 頁面優化
- **原因**: 404 頁面優化是獨立的功能改進，不在本次修復範圍
- **現狀**: 使用 Next.js 預設 404 頁面

#### ❌ Header 組件重構
- **原因**: 本次僅修復選單連結問題，不進行大規模重構
- **現狀**: 保持現有 Header 組件結構和樣式

---

## 4. 詳細需求

### 4.1 功能需求

#### FR-001: 移除業務員選單中的無效「設定」選項
**描述**: 業務員的使用者選單不應顯示指向不存在頁面的「設定」選項

**優先級**: Must Have (P0)

**驗收標準**:
- [ ] 業務員登入後，點擊右上角頭像展開選單
- [ ] 選單中不顯示「設定」選項
- [ ] 選單中只顯示「我的帳號」標籤、Dashboard 相關選項、登出選項
- [ ] 選單中的所有選項都能正確跳轉

**實作位置**: `frontend/components/layout/header.tsx` (Line 126-131)

**修改內容**:
```tsx
// 移除這段程式碼（僅針對業務員）
<DropdownMenuItem asChild>
  <Link href="/settings" className="cursor-pointer">
    <Settings className="mr-2 h-4 w-4" />
    設定
  </Link>
</DropdownMenuItem>
```

#### FR-002: 保留管理員選單中的「設定」選項
**描述**: 管理員的使用者選單應保留「設定」選項，因為 `/admin/settings` 頁面存在

**優先級**: Must Have (P0)

**驗收標準**:
- [ ] 管理員登入後，點擊右上角頭像展開選單
- [ ] 選單中顯示「設定」選項
- [ ] 點擊「設定」跳轉到 `/admin/settings`
- [ ] 設定頁面正常顯示（產業類別、地區管理）

**實作方式**:
- 根據 `user.role` 條件渲染「設定」選項
- 僅在 `user.role === 'admin'` 時顯示

#### FR-003: 優化選單選項順序和分組
**描述**: 改進選單結構，讓 Dashboard 相關選項更容易找到

**優先級**: Should Have (P1)

**驗收標準**:
- [ ] 選單結構清晰，相關功能分組明確
- [ ] Dashboard 選項位於顯眼位置
- [ ] 使用適當的分隔線區分不同功能群組

**建議結構**:
```
┌─────────────────────┐
│ 我的帳號             │  ← DropdownMenuLabel
├─────────────────────┤  ← DropdownMenuSeparator
│ 📊 Dashboard        │  ← Dashboard 相關選項
│ 👤 個人資料          │
├─────────────────────┤  ← DropdownMenuSeparator
│ ⚙ 設定 (僅 Admin)   │  ← 系統管理選項（條件渲染）
├─────────────────────┤  ← DropdownMenuSeparator (僅 Admin)
│ 🚪 登出             │  ← 帳號操作
└─────────────────────┘
```

### 4.2 資料需求

#### 不需要新增資料

本次修復不涉及資料庫變更，僅修改前端組件邏輯。

#### 使用現有資料

- `user.role` - 判斷使用者角色（admin / salesperson）
- `user.full_name` - 顯示使用者名稱
- `user.avatar` - 顯示使用者頭像

### 4.3 權限需求

| 角色 | 可見選項 | 可訪問頁面 |
|-----|---------|-----------|
| **Salesperson** | Dashboard, 個人資料, 登出 | `/dashboard`, `/dashboard/profile`, `/dashboard/*` |
| **Admin** | 管理後台, 審核管理, 使用者管理, 統計報表, 設定, 登出 | `/admin`, `/admin/approvals`, `/admin/users`, `/admin/statistics`, `/admin/settings` |
| **Guest** | 登入, 註冊 | `/`, `/search`, `/login`, `/register` |

**權限檢查時機**:
- Middleware (`frontend/middleware.ts`) - 路由層級權限檢查
- Header 組件 - 根據 `user.role` 條件渲染選單

### 4.4 UI/UX 需求

#### 選單佈局

**業務員選單** (Salesperson):
```tsx
<DropdownMenuContent align="end" className="w-56">
  <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
  <DropdownMenuSeparator />

  {/* Dashboard 相關選項 */}
  <DropdownMenuItem asChild>
    <Link href="/dashboard">
      <LayoutDashboard className="mr-2 h-4 w-4" />
      Dashboard
    </Link>
  </DropdownMenuItem>
  <DropdownMenuItem asChild>
    <Link href="/dashboard/profile">
      <User className="mr-2 h-4 w-4" />
      個人資料
    </Link>
  </DropdownMenuItem>

  <DropdownMenuSeparator />

  {/* 登出 */}
  <DropdownMenuItem onClick={onLogout}>
    <LogOut className="mr-2 h-4 w-4" />
    登出
  </DropdownMenuItem>
</DropdownMenuContent>
```

**管理員選單** (Admin):
```tsx
<DropdownMenuContent align="end" className="w-56">
  <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
  <DropdownMenuSeparator />

  {/* 管理後台選項 */}
  <DropdownMenuItem asChild>
    <Link href="/admin">
      <LayoutDashboard className="mr-2 h-4 w-4" />
      管理後台
    </Link>
  </DropdownMenuItem>
  <DropdownMenuItem asChild>
    <Link href="/admin/approvals">審核管理</Link>
  </DropdownMenuItem>
  <DropdownMenuItem asChild>
    <Link href="/admin/users">使用者管理</Link>
  </DropdownMenuItem>
  <DropdownMenuItem asChild>
    <Link href="/admin/statistics">統計報表</Link>
  </DropdownMenuItem>

  <DropdownMenuSeparator />

  {/* 系統設定 */}
  <DropdownMenuItem asChild>
    <Link href="/admin/settings">
      <Settings className="mr-2 h-4 w-4" />
      設定
    </Link>
  </DropdownMenuItem>

  <DropdownMenuSeparator />

  {/* 登出 */}
  <DropdownMenuItem onClick={onLogout}>
    <LogOut className="mr-2 h-4 w-4" />
    登出
  </DropdownMenuItem>
</DropdownMenuContent>
```

#### 互動設計

1. **點擊頭像**: 展開/收起選單
2. **點擊選項**: 跳轉到對應頁面，選單自動收起
3. **點擊登出**: 執行登出邏輯，跳轉到登入頁
4. **點擊選單外區域**: 自動收起選單

#### 視覺設計

- **選單寬度**: `w-56` (224px)
- **對齊方式**: `align="end"` (右對齊)
- **圓角**: 使用 Radix UI Dropdown 預設樣式
- **間距**: 選單項目之間使用 DropdownMenuSeparator
- **Icon 大小**: `h-4 w-4`
- **Icon 間距**: `mr-2`

#### 響應式需求

- **Desktop**: 顯示完整的 Dropdown Menu
- **Mobile**:
  - 隱藏 Dropdown Menu
  - 使用 Mobile Menu (已存在於 Header 組件)
  - Mobile Menu 同樣需要移除「設定」選項

---

## 5. 邊界情境處理

### 異常情況處理

| 情境 | 系統行為 | 錯誤訊息 |
|-----|---------|---------|
| **使用者未登入** | 不顯示使用者選單 | N/A (顯示登入/註冊按鈕) |
| **使用者角色為空** | 預設為 Guest，不顯示選單 | N/A |
| **Admin 訪問不存在的 `/settings`** | Middleware 重定向到 `/admin/settings` | N/A (靜默重定向) |
| **Salesperson 嘗試訪問 `/admin`** | Middleware 重定向到 `/403` | "您沒有權限訪問此頁面" |
| **選單項目無 Icon** | 僅顯示文字 | N/A (不影響功能) |

### Edge Cases

#### 情境 1: 使用者角色在使用過程中被變更
- **描述**: 使用者登入為 Salesperson，管理員在後台將其升級為 Admin
- **預期行為**:
  - 前端仍顯示舊角色選單（直到重新登入或 Token 刷新）
  - 如果嘗試訪問新角色頁面，Middleware 會根據 Cookie 中的新角色允許訪問
- **錯誤處理**: 建議使用者重新登入以更新選單

#### 情境 2: 使用者同時開啟多個分頁
- **描述**: 使用者在分頁 A 登出，分頁 B 仍保留登入狀態
- **預期行為**:
  - 分頁 B 的 Header 仍顯示使用者選單
  - 但點擊任何需要認證的選項時，API 會返回 401
  - `useAuth` Hook 會自動導向登入頁
- **錯誤處理**: React Query 的 `onError` 處理 401 錯誤

#### 情境 3: 選單顯示時路由變更
- **描述**: 使用者展開選單，同時頁面進行路由跳轉
- **預期行為**:
  - 選單自動收起（Radix UI 預設行為）
  - 新頁面的 Header 根據當前路由顯示正確狀態
- **錯誤處理**: 無需特殊處理

#### 情境 4: 網路中斷導致 `useAuth` 失敗
- **描述**: Header 組件依賴 `useAuth` 獲取使用者資訊，網路中斷時無法獲取
- **預期行為**:
  - `useAuth` 返回 `isLoading: true` 或 `error`
  - Header 顯示載入狀態或預設為 Guest 模式
  - 顯示登入/註冊按鈕
- **錯誤處理**: React Query 的重試機制

#### 情境 5: Mobile Menu 同步問題
- **描述**: 修復 Dropdown Menu 後，忘記同步修改 Mobile Menu
- **預期行為**:
  - Mobile Menu 和 Dropdown Menu 應顯示相同的選項
  - 都需要移除「設定」選項（針對 Salesperson）
- **錯誤處理**:
  - Code Review 檢查
  - 測試時同時測試 Desktop 和 Mobile 視圖

---

## 6. 技術考量

### 技術限制

1. **路由系統**: Next.js App Router 檔案系統路由
   - `/settings` 頁面不存在於 `frontend/app/` 目錄
   - `/admin/settings` 頁面存在於 `frontend/app/(admin)/admin/settings/page.tsx`

2. **權限控制**: Middleware 層級 + 組件層級
   - Middleware: 阻止未授權訪問
   - 組件: 根據角色條件渲染

3. **狀態管理**: React Query + useAuth Hook
   - 使用者資料來自 `useAuth()` Hook
   - 無法即時偵測角色變更（需重新登入）

### 效能考量

- **影響**: 極小（僅修改組件邏輯，不影響效能）
- **選單渲染**: 根據角色條件渲染，無額外 API 請求
- **路由跳轉**: Next.js Link 組件，預載優化

### 安全性考量

1. **前端權限檢查**: 僅用於 UI 顯示，不可作為唯一安全機制
2. **後端驗證**: 所有 API 請求都需要後端驗證 Token 和權限
3. **Middleware**: 作為第一層防護，阻止未授權路由訪問

### 第三方整合

- **無**: 本次修復不涉及第三方整合

---

## 7. 驗收標準

### 功能驗收

#### 業務員 (Salesperson)
- [ ] 登入系統後，右上角顯示使用者頭像和名稱
- [ ] 點擊頭像，展開選單
- [ ] 選單包含以下選項（按順序）：
  - [ ] 「我的帳號」標籤
  - [ ] 「Dashboard」選項
  - [ ] 「個人資料」選項
  - [ ] 分隔線
  - [ ] 「登出」選項
- [ ] **不包含**「設定」選項
- [ ] 點擊「Dashboard」跳轉到 `/dashboard`，頁面正常顯示
- [ ] 點擊「個人資料」跳轉到 `/dashboard/profile`，頁面正常顯示
- [ ] 點擊「登出」執行登出，跳轉到登入頁

#### 管理員 (Admin)
- [ ] 登入系統後，右上角顯示使用者頭像和名稱
- [ ] 點擊頭像，展開選單
- [ ] 選單包含以下選項：
  - [ ] 「我的帳號」標籤
  - [ ] 「管理後台」選項
  - [ ] 「審核管理」選項
  - [ ] 「使用者管理」選項
  - [ ] 「統計報表」選項
  - [ ] 分隔線
  - [ ] 「設定」選項
  - [ ] 分隔線
  - [ ] 「登出」選項
- [ ] 點擊「設定」跳轉到 `/admin/settings`，頁面正常顯示
- [ ] 其他選項功能正常

#### 訪客 (Guest)
- [ ] 未登入時，右上角顯示「登入」和「註冊」按鈕
- [ ] 不顯示使用者選單

### 非功能驗收

#### UI/UX
- [ ] 選單樣式符合設計系統
- [ ] 選單項目有適當的 Icon
- [ ] 選單項目 Hover 效果正常
- [ ] 選單動畫流暢

#### 響應式設計
- [ ] Desktop (>= 768px): 顯示 Dropdown Menu
- [ ] Mobile (< 768px): 使用 Mobile Menu
- [ ] Mobile Menu 選項與 Dropdown Menu 一致

#### 無障礙性 (A11y)
- [ ] 選單可使用鍵盤導航 (Tab, Enter, Escape)
- [ ] 選單項目有適當的 ARIA 屬性（Radix UI 預設）
- [ ] 螢幕閱讀器可正確朗讀選單內容

#### 瀏覽器兼容性
- [ ] Chrome (最新版本)
- [ ] Firefox (最新版本)
- [ ] Safari (最新版本)
- [ ] Edge (最新版本)

---

## 8. 風險與依賴

### 潛在風險

#### 風險 1: 修改 Header 組件影響其他頁面
- **描述**: Header 組件在所有頁面共用，修改可能影響未測試的頁面
- **機率**: 低
- **影響**: 中
- **緩解措施**:
  - 僅修改選單邏輯，不改變 Header 結構
  - 全面測試所有角色和頁面

#### 風險 2: 使用者習慣「設定」選項
- **描述**: 業務員可能已習慣點擊「設定」選項，移除後找不到
- **機率**: 低
- **影響**: 低
- **緩解措施**:
  - 「設定」選項原本就導向 404，使用者實際上無法使用
  - 新增「Dashboard」選項提供更好的導航

#### 風險 3: Mobile Menu 同步遺漏
- **描述**: 修改 Dropdown Menu 後忘記同步修改 Mobile Menu
- **機率**: 中
- **影響**: 中
- **緩解措施**:
  - Code Review 時檢查
  - 測試時同時測試 Desktop 和 Mobile
  - 將 Mobile Menu 和 Dropdown Menu 的選項邏輯共用

### 依賴項目

#### 依賴 1: Header 組件
- **路徑**: `frontend/components/layout/header.tsx`
- **版本**: 當前版本
- **狀態**: 已存在

#### 依賴 2: Radix UI Dropdown Menu
- **套件**: `@radix-ui/react-dropdown-menu`
- **版本**: 已安裝
- **狀態**: 穩定

#### 依賴 3: Next.js Link 組件
- **套件**: `next/link`
- **版本**: Next.js 16.1.1
- **狀態**: 穩定

#### 依賴 4: useAuth Hook
- **路徑**: `frontend/hooks/useAuth.ts`
- **用途**: 獲取使用者資料和角色
- **狀態**: 已存在

---

## 9. 實作計劃（參考）

### Phase 1: 分析與規劃 ✅ (已完成)
- ✅ 閱讀相關程式碼
- ✅ 確認問題範圍
- ✅ 撰寫 Proposal

### Phase 2: 修改程式碼
- [ ] 修改 `header.tsx` 組件
  - [ ] 調整 `getDashboardLinks()` 函數
  - [ ] 移除業務員選單中的「設定」選項
  - [ ] 確保管理員選單保留「設定」選項
  - [ ] 同步修改 Mobile Menu
- [ ] 程式碼 Review

### Phase 3: 測試
- [ ] 本地測試
  - [ ] 業務員選單功能測試
  - [ ] 管理員選單功能測試
  - [ ] 訪客顯示測試
- [ ] 響應式測試
  - [ ] Desktop 測試
  - [ ] Mobile 測試
  - [ ] Tablet 測試
- [ ] 瀏覽器兼容性測試

### Phase 4: 部署與驗收
- [ ] 提交 Pull Request
- [ ] Code Review
- [ ] 合併到主分支
- [ ] 部署到測試環境
- [ ] 最終驗收

**預估時間**: 2-4 小時

---

## 10. 附錄

### 參考資料

#### 程式碼檔案
- `frontend/components/layout/header.tsx` - Header 組件
- `frontend/middleware.ts` - 路由權限控制
- `frontend/app/(dashboard)/layout.tsx` - Dashboard Layout
- `frontend/app/(admin)/admin/settings/page.tsx` - 管理員設定頁面
- `frontend/hooks/useAuth.ts` - 認證 Hook

#### 相關 Issue
- 使用者回報: 「設定」頁面 404 錯誤
- 使用者建議: 新增 Dashboard 快速導航

#### 設計規範
- `frontend/docs/design-system.md` - 設計系統規範
- Radix UI Dropdown Menu: https://www.radix-ui.com/primitives/docs/components/dropdown-menu

### 未來規劃

#### 業務員設定頁面 (Future)
如果未來需要業務員專屬的設定頁面，可以考慮以下功能：
- **通知偏好**: Email 通知、推播通知
- **隱私設定**: 個人資料顯示範圍、檔案公開狀態
- **帳號安全**: 變更密碼、雙因素驗證
- **語言與地區**: 介面語言、時區設定

實作路徑: `frontend/app/(dashboard)/dashboard/settings/page.tsx`

#### 選單結構優化 (Future)
- **分組標籤**: 使用 DropdownMenuGroup 將相關選項分組
- **子選單**: 使用 DropdownMenuSub 建立層級選單
- **快捷鍵**: 為常用選項新增鍵盤快捷鍵提示

---

**需求分析完成時間**: 2026-01-11
**分析者**: Claude (Product Manager Agent)
**狀態**: 待審核

---

## 需求訪談記錄

### 問題 1: 功能範圍確認

**Q**: 此修復是否只針對業務員，還是需要檢查所有角色的選單？

**A**:
- **業務員**: 移除「設定」選項（因 `/settings` 不存在）
- **管理員**: 保留「設定」選項（因 `/admin/settings` 存在）
- **訪客**: 不受影響（無選單）

### 問題 2: 是否需要新增業務員設定頁面

**Q**: 是否應該建立 `/dashboard/settings` 頁面，而不是移除選項？

**A**:
- **本次修復**: 移除選項（Out of Scope）
- **原因**: 目前業務員的所有可編輯資料都在「個人資料」頁面，沒有額外設定需求
- **未來**: 如需新增通知偏好、隱私設定等功能，可建立專屬設定頁面

### 問題 3: Mobile Menu 是否需要同步修改

**Q**: 手機版的選單是否也需要移除「設定」選項？

**A**:
- **是的**: Mobile Menu 應與 Dropdown Menu 保持一致
- **實作**: 檢查並修改 `header.tsx` 中的 Mobile Menu 區塊（Line 171-226）

### 問題 4: 是否需要重定向處理

**Q**: 如果使用者直接訪問 `/settings`，是否需要重定向？

**A**:
- **不需要**: Middleware 已處理權限控制，未授權訪問會導向 `/403` 或 `/login`
- **現狀**: `/settings` 頁面不存在，Next.js 會自動顯示 404
- **建議**: 保持現狀，不新增額外重定向邏輯

### 問題 5: 驗收標準優先級

**Q**: 哪些驗收標準是 Must Have，哪些是 Nice to Have？

**A**:
- **Must Have (P0)**:
  - 業務員選單移除「設定」選項
  - 管理員選單保留「設定」選項
  - 所有選項都能正確跳轉
- **Should Have (P1)**:
  - Dashboard 選項位於顯眼位置
  - 選單結構清晰分組
- **Nice to Have (P2)**:
  - 鍵盤導航優化
  - 無障礙性增強

---

**Proposal 版本**: 1.0
**最後更新**: 2026-01-11
