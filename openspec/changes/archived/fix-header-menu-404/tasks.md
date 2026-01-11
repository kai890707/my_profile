# Implementation Tasks: 修復 Header 使用者選單 404 錯誤

## 任務總覽

**功能**: 修復業務員選單「設定」選項導向 404，並優化選單結構
**預估時間**: 2-4 小時
**檔案數**: 1 個 Frontend 檔案

---

## Phase 1: 準備與分析 ✅ (已完成)

- [x] Task 1.1: 閱讀 Proposal 和 Specs
- [x] Task 1.2: 確認修改範圍和影響

---

## Phase 2: 程式碼修改

### Task 2.1: 修改 Header 組件 - 連結配置
**檔案**: `frontend/components/layout/header.tsx`
**行數**: Line 38-41
**描述**: 更新 `salespersonLinks` 配置，移除指向不存在的 `/settings` 連結

**修改內容**:
```tsx
// 移除:
{ href: '/settings', label: '設定', icon: Settings }

// 確保保留:
{ href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard }
{ href: '/dashboard/profile', label: '個人資料', icon: User }
```

**驗證**:
- [ ] `salespersonLinks` 只包含存在的頁面連結
- [ ] `adminLinks` 保持完整（包含 `/admin/settings`）

---

### Task 2.2: 修改 Desktop Dropdown Menu
**檔案**: `frontend/components/layout/header.tsx`
**行數**: Line 114-139
**描述**: 根據 `user.role` 條件渲染「設定」選項

**修改方式**: 使用條件渲染

**修改內容**:
```tsx
{/* 設定選項 - 僅 Admin 顯示 */}
{user.role === 'admin' && (
  <>
    <DropdownMenuSeparator />
    <DropdownMenuItem asChild>
      <Link href="/admin/settings" className="cursor-pointer">
        <Settings className="mr-2 h-4 w-4" />
        設定
      </Link>
    </DropdownMenuItem>
  </>
)}
```

**驗證**:
- [ ] 業務員登入時，選單不顯示「設定」選項
- [ ] 管理員登入時，選單顯示「設定」選項
- [ ] 「設定」選項有 Icon 和正確的連結

---

### Task 2.3: 同步修改 Mobile Menu
**檔案**: `frontend/components/layout/header.tsx`
**行數**: Line 171-226
**描述**: Mobile Menu 使用相同的條件渲染邏輯

**修改內容**:
```tsx
{/* Mobile 設定選項 - 僅 Admin 顯示 */}
{user.role === 'admin' && (
  <Link
    href="/admin/settings"
    className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
    onClick={() => setMobileMenuOpen(false)}
  >
    <Settings className="mr-2 h-4 w-4 inline" />
    設定
  </Link>
)}
```

**驗證**:
- [ ] Mobile Menu 與 Dropdown Menu 選項一致
- [ ] 點擊選項後 Mobile Menu 自動關閉

---

## Phase 3: 測試

### Task 3.1: 本地功能測試
**描述**: 在本地環境測試所有角色的選單功能

**測試案例**:
- [ ] **業務員測試**:
  - [ ] 登入業務員帳號
  - [ ] 點擊右上角頭像，展開選單
  - [ ] 確認選單包含：Dashboard、個人資料、登出
  - [ ] 確認選單**不包含**「設定」選項
  - [ ] 點擊「Dashboard」跳轉到 `/dashboard`，頁面正常
  - [ ] 點擊「個人資料」跳轉到 `/dashboard/profile`，頁面正常
  - [ ] 點擊「登出」，成功登出並跳轉到登入頁

- [ ] **管理員測試**:
  - [ ] 登入管理員帳號
  - [ ] 點擊右上角頭像，展開選單
  - [ ] 確認選單包含：管理後台、審核管理、使用者管理、統計報表、設定、登出
  - [ ] 點擊「設定」跳轉到 `/admin/settings`，頁面正常
  - [ ] 其他選項功能正常

- [ ] **訪客測試**:
  - [ ] 清除登入狀態
  - [ ] 確認右上角顯示「登入」和「註冊」按鈕
  - [ ] 不顯示使用者選單

---

### Task 3.2: 響應式測試
**描述**: 測試不同視口大小的選單顯示

**測試案例**:
- [ ] **Desktop (>= 768px)**:
  - [ ] 顯示 Dropdown Menu
  - [ ] 選單位置正確（右對齊）
  - [ ] 選單寬度正確（w-56）

- [ ] **Mobile (< 768px)**:
  - [ ] 隱藏 Dropdown Menu
  - [ ] 顯示 Mobile Menu Icon（漢堡選單）
  - [ ] 點擊 Icon 展開 Mobile Menu
  - [ ] Mobile Menu 選項與 Dropdown Menu 一致

- [ ] **Tablet (768px - 1024px)**:
  - [ ] 顯示 Dropdown Menu
  - [ ] 選單適應螢幕寬度

---

### Task 3.3: 瀏覽器兼容性測試
**描述**: 測試主流瀏覽器的兼容性

**測試案例**:
- [ ] Chrome (最新版本): 功能正常
- [ ] Firefox (最新版本): 功能正常
- [ ] Safari (最新版本): 功能正常
- [ ] Edge (最新版本): 功能正常

---

### Task 3.4: 無障礙性測試
**描述**: 測試鍵盤導航和螢幕閱讀器

**測試案例**:
- [ ] **鍵盤導航**:
  - [ ] Tab 可聚焦到頭像
  - [ ] Enter 或 Space 展開選單
  - [ ] 方向鍵可在選單項目間移動
  - [ ] Enter 選擇項目
  - [ ] Escape 關閉選單

- [ ] **螢幕閱讀器** (VoiceOver / NVDA):
  - [ ] 選單項目可被正確朗讀
  - [ ] 選單狀態（展開/收起）可被識別
  - [ ] Icon 有適當的 aria-label

---

## Phase 4: 程式碼品質

### Task 4.1: TypeScript 類型檢查
**命令**: `npm run typecheck`

**驗證**:
- [ ] 無 TypeScript 錯誤
- [ ] 所有類型定義正確

---

### Task 4.2: ESLint 檢查
**命令**: `npm run lint`

**驗證**:
- [ ] 無 ESLint 錯誤或警告
- [ ] 程式碼符合專案規範

---

## Phase 5: 文檔與提交

### Task 5.1: 更新相關文檔
**描述**: 如果需要，更新相關文檔

**檢查項目**:
- [ ] 檢查是否需要更新 `frontend/docs/` 文檔
- [ ] 本次修復為 Bug Fix，通常不需要更新文檔

---

### Task 5.2: 提交變更
**描述**: 提交程式碼變更

**步驟**:
- [ ] 檢查 git status
- [ ] Stage 變更: `git add frontend/components/layout/header.tsx`
- [ ] Commit: 使用規範的 commit message
- [ ] Push: 推送到遠端分支

**Commit Message 格式**:
```
fix: Remove non-existent settings link from salesperson menu

修復業務員選單「設定」選項導向 404 錯誤

變更內容:
- 移除業務員選單中的「設定」選項（/settings 頁面不存在）
- 保留管理員選單中的「設定」選項（/admin/settings 頁面存在）
- 同步修改 Desktop Dropdown Menu 和 Mobile Menu
- 根據 user.role 條件渲染「設定」選項

影響範圍:
- 業務員: 選單簡化，移除無效連結
- 管理員: 功能不受影響
- 訪客: 功能不受影響

測試:
✅ 業務員選單不顯示「設定」選項
✅ 管理員選單正常顯示「設定」選項
✅ 所有連結正確跳轉，無 404 錯誤
✅ Desktop 和 Mobile 選單同步
✅ TypeScript 類型檢查通過
✅ 響應式設計正常
```

---

## Phase 6: 歸檔

### Task 6.1: 歸檔到 OpenSpec 規範庫
**描述**: 將完成的變更歸檔

**步驟**:
- [ ] 移動到 `openspec/changes/archived/fix-header-menu-404/`
- [ ] 更新 `openspec/CHANGELOG.md`
- [ ] 創建歸檔報告

---

## 任務摘要

**總任務數**: 15 個任務
- Phase 1 (準備): 2 個 ✅
- Phase 2 (實作): 3 個
- Phase 3 (測試): 4 個
- Phase 4 (品質): 2 個
- Phase 5 (文檔): 2 個
- Phase 6 (歸檔): 1 個

**關鍵路徑**:
1. 修改 Header 組件連結配置
2. 條件渲染「設定」選項（Desktop + Mobile）
3. 全面測試（業務員 + 管理員）
4. 提交變更

**預估時間**: 2-4 小時
- 實作: 30-60 分鐘
- 測試: 60-90 分鐘
- 品質檢查: 15-30 分鐘
- 文檔與提交: 15-30 分鐘

---

**文檔版本**: 1.0
**最後更新**: 2026-01-11
