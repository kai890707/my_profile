# 組件規格：Header 組件修改

## 1. 組件總覽

### 修改範圍

**組件名稱**: Header
**檔案路徑**: `frontend/components/layout/header.tsx`
**修改類型**: Bug Fix + Feature Enhancement
**影響範圍**: Desktop Dropdown Menu + Mobile Menu

### 修改摘要

1. ✅ 移除業務員（salesperson）Dropdown Menu 中的「設定」選項
2. ✅ 保留管理員（admin）Dropdown Menu 中的「設定」選項
3. ✅ 確保 Mobile Menu 與 Dropdown Menu 選項一致
4. ✅ 優化選單結構和可讀性

---

## 2. Header 組件規格

### 2.1 組件介面（Props）

```typescript
interface HeaderProps {
  user?: {
    id: number;
    username?: string;
    name?: string;
    email?: string;
    role: 'admin' | 'salesperson' | 'user';
    full_name?: string;
    avatar?: string | null;
  } | null;
  onLogout?: () => void;
}
```

**Props 說明**：

| Prop | 類型 | 必填 | 說明 |
|------|------|------|------|
| `user` | `User \| null` | 否 | 使用者資料，null 表示未登入 |
| `user.role` | `'admin' \| 'salesperson' \| 'user'` | 是 | 使用者角色，影響選單顯示 |
| `onLogout` | `() => void` | 否 | 登出回調函數 |

### 2.2 組件狀態（State）

```typescript
const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
```

| State | 類型 | 初始值 | 說明 |
|-------|------|--------|------|
| `mobileMenuOpen` | `boolean` | `false` | Mobile Menu 開啟/關閉狀態 |

---

## 3. Dropdown Menu 組件規格

### 3.1 Dropdown Menu 結構

#### 業務員選單（Salesperson）

```tsx
<DropdownMenu>
  <DropdownMenuTrigger asChild>
    {/* 頭像 + 名稱 + 角色 */}
  </DropdownMenuTrigger>

  <DropdownMenuContent align="end" className="w-56">
    {/* Label */}
    <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
    <DropdownMenuSeparator />

    {/* Dashboard 連結 */}
    <DropdownMenuItem asChild>
      <Link href="/dashboard" className="cursor-pointer">
        <LayoutDashboard className="mr-2 h-4 w-4" />
        Dashboard
      </Link>
    </DropdownMenuItem>

    {/* 個人資料連結 */}
    <DropdownMenuItem asChild>
      <Link href="/dashboard/profile" className="cursor-pointer">
        <User className="mr-2 h-4 w-4" />
        個人資料
      </Link>
    </DropdownMenuItem>

    <DropdownMenuSeparator />

    {/* 登出按鈕 */}
    <DropdownMenuItem
      onClick={onLogout}
      className="cursor-pointer text-error-600"
    >
      <LogOut className="mr-2 h-4 w-4" />
      登出
    </DropdownMenuItem>
  </DropdownMenuContent>
</DropdownMenu>
```

#### 管理員選單（Admin）

```tsx
<DropdownMenu>
  <DropdownMenuTrigger asChild>
    {/* 頭像 + 名稱 + 角色 */}
  </DropdownMenuTrigger>

  <DropdownMenuContent align="end" className="w-56">
    {/* Label */}
    <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
    <DropdownMenuSeparator />

    {/* 管理後台連結 */}
    <DropdownMenuItem asChild>
      <Link href="/admin" className="cursor-pointer">
        <LayoutDashboard className="mr-2 h-4 w-4" />
        管理後台
      </Link>
    </DropdownMenuItem>

    {/* 其他管理員連結 */}
    <DropdownMenuItem asChild>
      <Link href="/admin/approvals" className="cursor-pointer">
        審核管理
      </Link>
    </DropdownMenuItem>

    <DropdownMenuItem asChild>
      <Link href="/admin/users" className="cursor-pointer">
        使用者管理
      </Link>
    </DropdownMenuItem>

    <DropdownMenuItem asChild>
      <Link href="/admin/statistics" className="cursor-pointer">
        統計報表
      </Link>
    </DropdownMenuItem>

    <DropdownMenuSeparator />

    {/* 設定連結 - 僅管理員顯示 */}
    <DropdownMenuItem asChild>
      <Link href="/admin/settings" className="cursor-pointer">
        <Settings className="mr-2 h-4 w-4" />
        設定
      </Link>
    </DropdownMenuItem>

    <DropdownMenuSeparator />

    {/* 登出按鈕 */}
    <DropdownMenuItem
      onClick={onLogout}
      className="cursor-pointer text-error-600"
    >
      <LogOut className="mr-2 h-4 w-4" />
      登出
    </DropdownMenuItem>
  </DropdownMenuContent>
</DropdownMenu>
```

### 3.2 條件渲染邏輯

#### 核心邏輯：根據角色渲染選單

**方案 1：在 JSX 中條件渲染（推薦）**

```tsx
{/* Dashboard 連結區塊 */}
{getDashboardLinks().map((link) => (
  <DropdownMenuItem key={link.href} asChild>
    <Link href={link.href} className="cursor-pointer">
      {link.icon && <link.icon className="mr-2 h-4 w-4" />}
      {link.label}
    </Link>
  </DropdownMenuItem>
))}

<DropdownMenuSeparator />

{/* 設定選項 - 僅管理員顯示 */}
{user?.role === 'admin' && (
  <>
    <DropdownMenuItem asChild>
      <Link href="/admin/settings" className="cursor-pointer">
        <Settings className="mr-2 h-4 w-4" />
        設定
      </Link>
    </DropdownMenuItem>
    <DropdownMenuSeparator />
  </>
)}

{/* 登出按鈕 */}
<DropdownMenuItem
  onClick={onLogout}
  className="cursor-pointer text-error-600"
>
  <LogOut className="mr-2 h-4 w-4" />
  登出
</DropdownMenuItem>
```

**方案 2：擴展 getDashboardLinks() 函數**

```tsx
const getDashboardLinks = () => {
  const baseLinks = [];

  if (user?.role === 'admin') {
    baseLinks.push(...adminLinks);
  } else if (user?.role === 'salesperson') {
    baseLinks.push(...salespersonLinks);
  }

  return baseLinks;
};

const getSettingsLink = () => {
  if (user?.role === 'admin') {
    return { href: '/admin/settings', label: '設定', icon: Settings };
  }
  return null;
};
```

**推薦**: 方案 1（直接在 JSX 中條件渲染），因為：
- 更清晰易讀
- 減少函數複雜度
- 與現有程式碼風格一致

---

## 4. Mobile Menu 組件規格

### 4.1 Mobile Menu 結構

#### 業務員 Mobile Menu（Salesperson）

```tsx
{mobileMenuOpen && (
  <div className="md:hidden py-4 space-y-2 border-t border-slate-200">
    {/* 公開連結 */}
    {publicLinks.map((link) => (
      <Link
        key={link.href}
        href={link.href}
        className="block px-4 py-2 text-sm font-medium text-slate-700
                   hover:bg-slate-100 rounded-lg"
        onClick={() => setMobileMenuOpen(false)}
      >
        {link.label}
      </Link>
    ))}

    {/* Dashboard 連結（如果使用者已登入） */}
    {user && getDashboardLinks().map((link) => (
      <Link
        key={link.href}
        href={link.href}
        className="block px-4 py-2 text-sm font-medium text-slate-700
                   hover:bg-slate-100 rounded-lg"
        onClick={() => setMobileMenuOpen(false)}
      >
        {link.label}
      </Link>
    ))}

    {/* 登出按鈕（如果使用者已登入） */}
    {user && (
      <button
        onClick={() => {
          onLogout?.();
          setMobileMenuOpen(false);
        }}
        className="w-full text-left px-4 py-2 text-sm font-medium
                   text-error-600 hover:bg-error-50 rounded-lg"
      >
        登出
      </button>
    )}

    {/* 登入/註冊按鈕（如果未登入） */}
    {!user && (
      <div className="pt-4 space-y-2 border-t border-slate-200">
        <Link
          href="/login"
          className="block px-4 py-2 text-sm font-medium text-slate-700
                     hover:bg-slate-100 rounded-lg"
          onClick={() => setMobileMenuOpen(false)}
        >
          登入
        </Link>
        <Link
          href="/register"
          className="block px-4 py-2 text-sm font-medium text-white
                     bg-primary-600 hover:bg-primary-700 rounded-lg text-center"
          onClick={() => setMobileMenuOpen(false)}
        >
          註冊
        </Link>
      </div>
    )}
  </div>
)}
```

### 4.2 Mobile Menu 條件渲染

**關鍵點**：
1. Mobile Menu **不顯示「設定」選項**（無論角色）
2. 僅顯示 `getDashboardLinks()` 返回的連結
3. 與 Dropdown Menu 保持一致

**原因**：
- 管理員通常在桌面環境使用後台
- 手機版簡化選單，避免資訊過載
- 如需設定功能，可在桌面端訪問

**如果未來需要在 Mobile 顯示設定**：

```tsx
{/* 在登出按鈕前新增 */}
{user?.role === 'admin' && (
  <Link
    href="/admin/settings"
    className="block px-4 py-2 text-sm font-medium text-slate-700
               hover:bg-slate-100 rounded-lg"
    onClick={() => setMobileMenuOpen(false)}
  >
    設定
  </Link>
)}
```

---

## 5. DropdownMenuItem 組件規格

### 5.1 連結項目（Link Menu Item）

```tsx
<DropdownMenuItem asChild>
  <Link href="/dashboard" className="cursor-pointer">
    <LayoutDashboard className="mr-2 h-4 w-4" />
    Dashboard
  </Link>
</DropdownMenuItem>
```

**Props**：
- `asChild`: 使用 `Link` 組件作為底層元素
- `className="cursor-pointer"`: 顯示點擊游標

**結構**：
- Icon（選填）：`className="mr-2 h-4 w-4"`（右邊距 8px，尺寸 16px）
- 文字：選單項目名稱

### 5.2 按鈕項目（Button Menu Item）

```tsx
<DropdownMenuItem
  onClick={onLogout}
  className="cursor-pointer text-error-600"
>
  <LogOut className="mr-2 h-4 w-4" />
  登出
</DropdownMenuItem>
```

**Props**：
- `onClick`: 點擊事件處理函數
- `className`:
  - `cursor-pointer`: 顯示點擊游標
  - `text-error-600`: 紅色文字（表示危險操作）

### 5.3 DropdownMenuLabel 組件

```tsx
<DropdownMenuLabel>我的帳號</DropdownMenuLabel>
```

**用途**: 選單標題，提供語義化分組

**樣式**:
- 字體：text-sm font-semibold
- 顏色：text-slate-700
- 內邊距：px-2 py-1.5

### 5.4 DropdownMenuSeparator 組件

```tsx
<DropdownMenuSeparator />
```

**用途**: 分隔不同功能群組的選單項目

**樣式**:
- 顏色：border-slate-200
- 粗細：1px
- 邊距：my-1（上下各 4px）

---

## 6. 連結配置（Link Configuration）

### 6.1 公開連結（Public Links）

```typescript
const publicLinks = [
  { href: '/', label: '首頁' },
  { href: '/search', label: '搜尋業務員' },
];
```

**用途**: 所有使用者都可見的連結（包括未登入）

### 6.2 業務員連結（Salesperson Links）

**修改前**：
```typescript
const salespersonLinks = [
  { href: '/dashboard', label: '個人中心', icon: LayoutDashboard },
  { href: '/dashboard/profile', label: '個人資料', icon: User },
];
```

**修改後**：
```typescript
const salespersonLinks = [
  { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard }, // 修改 label
  { href: '/dashboard/profile', label: '個人資料', icon: User },
];
```

**變更說明**：
- 將 "個人中心" 改為 "Dashboard"（更直觀）
- 保留 Icon 配置

### 6.3 管理員連結（Admin Links）

```typescript
const adminLinks = [
  { href: '/admin', label: '管理後台', icon: LayoutDashboard },
  { href: '/admin/approvals', label: '審核管理' },
  { href: '/admin/users', label: '使用者管理' },
  { href: '/admin/statistics', label: '統計報表' },
];
```

**說明**：
- 不包含「設定」選項（單獨條件渲染）
- 僅第一個選項有 Icon（其他選項簡化顯示）

### 6.4 連結類型定義

```typescript
interface Link {
  href: string;
  label: string;
  icon?: React.ComponentType<{ className?: string }>;
}
```

---

## 7. Icon 組件規格

### 7.1 使用的 Icon

```typescript
import {
  Menu,              // 漢堡選單 Icon
  X,                 // 關閉選單 Icon
  User,              // 使用者/個人資料 Icon
  LogOut,            // 登出 Icon
  Settings,          // 設定 Icon
  LayoutDashboard,   // Dashboard Icon
} from 'lucide-react';
```

### 7.2 Icon 尺寸與樣式

**選單 Icon**：
```tsx
<LayoutDashboard className="mr-2 h-4 w-4" />
```

- 尺寸：`h-4 w-4`（16px x 16px）
- 右邊距：`mr-2`（8px）
- 顏色：繼承父元素（text-slate-700 或 text-error-600）

**漢堡選單 Icon**：
```tsx
<Menu className="h-6 w-6" />
<X className="h-6 w-6" />
```

- 尺寸：`h-6 w-6`（24px x 24px）
- 顏色：繼承父元素（text-slate-700）

---

## 8. 樣式規格（Styling）

### 8.1 Dropdown Menu 樣式

**DropdownMenuContent**：
```tsx
className="w-56"
```
- 寬度：224px（固定）
- 對齊：`align="end"`（右對齊）
- 陰影：Radix UI 預設（shadow-md）
- 圓角：Radix UI 預設（rounded-md）

**DropdownMenuItem**：
```tsx
// 預設樣式（Radix UI）
className="cursor-pointer"

// 登出選項
className="cursor-pointer text-error-600"
```

- Hover：bg-slate-100（淺灰背景）
- Focus：outline-primary-500（藍色外框）
- 圓角：rounded-sm（4px）
- 內邊距：px-2 py-2

### 8.2 Mobile Menu 樣式

**容器**：
```tsx
className="md:hidden py-4 space-y-2 border-t border-slate-200"
```
- 顯示：僅在 Mobile（< 768px）
- 內邊距：py-4（上下 16px）
- 間距：space-y-2（項目間距 8px）
- 頂部邊框：border-t border-slate-200

**連結項目**：
```tsx
className="block px-4 py-2 text-sm font-medium text-slate-700
           hover:bg-slate-100 rounded-lg"
```
- 顯示：block（佔滿寬度）
- 內邊距：px-4 py-2
- 字體：text-sm font-medium
- Hover：bg-slate-100
- 圓角：rounded-lg（16px）

**登出按鈕**：
```tsx
className="w-full text-left px-4 py-2 text-sm font-medium
           text-error-600 hover:bg-error-50 rounded-lg"
```
- 寬度：w-full（100%）
- 對齊：text-left
- 顏色：text-error-600（紅色）
- Hover：bg-error-50（淺紅背景）

---

## 9. 行為規格（Behavior）

### 9.1 Dropdown Menu 行為

**展開選單**：
- 觸發：點擊頭像或名稱
- 效果：選單淡入（200ms）
- 焦點：移到第一個選單項目

**選擇選項**：
- 連結項目：執行路由跳轉，選單自動收起
- 登出按鈕：執行 `onLogout()` 回調，選單自動收起

**關閉選單**：
- 點擊選單外區域：選單淡出（200ms）
- 按 ESC 鍵：選單淡出（200ms）
- 選擇任一選項：選單自動收起

### 9.2 Mobile Menu 行為

**展開選單**：
- 觸發：點擊漢堡選單（☰）
- 效果：選單從右側滑入
- 狀態：`setMobileMenuOpen(true)`

**選擇選項**：
- 任何選項：執行對應動作，選單收起
- 回調：`setMobileMenuOpen(false)`

**關閉選單**：
- 點擊關閉按鈕（X）：選單滑出
- 選擇任一選項：選單自動收起

---

## 10. 無障礙規格（Accessibility）

### 10.1 ARIA 屬性

**DropdownMenuTrigger**（Radix UI 自動處理）：
```tsx
aria-haspopup="menu"
aria-expanded={isOpen}
```

**DropdownMenuContent**（Radix UI 自動處理）：
```tsx
role="menu"
aria-orientation="vertical"
```

**DropdownMenuItem**（Radix UI 自動處理）：
```tsx
role="menuitem"
tabindex={-1}
```

### 10.2 鍵盤導航

| 按鍵 | 行為 |
|------|------|
| `Enter` / `Space` | 開啟選單（焦點在 Trigger） |
| `↓` | 移到下一個選項 |
| `↑` | 移到上一個選項 |
| `Enter` / `Space` | 選擇當前選項 |
| `Escape` | 關閉選單 |
| `Tab` | 關閉選單，移到下一個可聚焦元素 |

### 10.3 螢幕閱讀器

**Trigger Button**：
```tsx
<button className="...">
  <Avatar ... />
  <div>
    <p>{user.full_name}</p>  {/* 會被朗讀 */}
    <p>{user.role}</p>        {/* 會被朗讀 */}
  </div>
</button>
```

朗讀為："張三, 業務員, 按鈕, 有彈出式選單"

**選單項目**：
```tsx
<DropdownMenuItem asChild>
  <Link href="/dashboard">
    <LayoutDashboard className="mr-2 h-4 w-4" aria-hidden="true" />
    Dashboard
  </Link>
</DropdownMenuItem>
```

朗讀為："Dashboard, 連結"

---

## 11. 測試案例

### 11.1 單元測試（Component Tests）

```typescript
describe('Header Component', () => {
  describe('Salesperson User', () => {
    it('應顯示 Dashboard 和個人資料選項', () => {
      const user = { role: 'salesperson', full_name: '張三' };
      render(<Header user={user} />);

      fireEvent.click(screen.getByText('張三'));

      expect(screen.getByText('Dashboard')).toBeInTheDocument();
      expect(screen.getByText('個人資料')).toBeInTheDocument();
    });

    it('不應顯示設定選項', () => {
      const user = { role: 'salesperson', full_name: '張三' };
      render(<Header user={user} />);

      fireEvent.click(screen.getByText('張三'));

      expect(screen.queryByText('設定')).not.toBeInTheDocument();
    });
  });

  describe('Admin User', () => {
    it('應顯示管理後台選項', () => {
      const user = { role: 'admin', full_name: '管理員' };
      render(<Header user={user} />);

      fireEvent.click(screen.getByText('管理員'));

      expect(screen.getByText('管理後台')).toBeInTheDocument();
      expect(screen.getByText('設定')).toBeInTheDocument();
    });

    it('設定選項應連結到 /admin/settings', () => {
      const user = { role: 'admin', full_name: '管理員' };
      render(<Header user={user} />);

      fireEvent.click(screen.getByText('管理員'));

      const settingsLink = screen.getByText('設定').closest('a');
      expect(settingsLink).toHaveAttribute('href', '/admin/settings');
    });
  });

  describe('Mobile Menu', () => {
    it('應與 Dropdown Menu 顯示相同選項', () => {
      const user = { role: 'salesperson', full_name: '張三' };
      render(<Header user={user} />);

      // 開啟 Mobile Menu
      fireEvent.click(screen.getByLabelText('開啟選單'));

      // 檢查 Mobile Menu 選項
      const mobileMenu = screen.getByRole('navigation');
      expect(within(mobileMenu).getByText('Dashboard')).toBeInTheDocument();
      expect(within(mobileMenu).getByText('個人資料')).toBeInTheDocument();
      expect(within(mobileMenu).queryByText('設定')).not.toBeInTheDocument();
    });
  });
});
```

### 11.2 整合測試（E2E Tests）

```typescript
// Playwright E2E Test
test('業務員選單不顯示設定選項', async ({ page }) => {
  // 以業務員身份登入
  await page.goto('/login');
  await page.fill('[name="email"]', 'salesperson@test.com');
  await page.fill('[name="password"]', 'password123');
  await page.click('button[type="submit"]');

  // 等待跳轉到 Dashboard
  await page.waitForURL('/dashboard');

  // 點擊頭像展開選單
  await page.click('[aria-label="開啟使用者選單"]');

  // 驗證選單選項
  await expect(page.locator('text=Dashboard')).toBeVisible();
  await expect(page.locator('text=個人資料')).toBeVisible();
  await expect(page.locator('text=設定')).not.toBeVisible();

  // 點擊 Dashboard
  await page.click('text=Dashboard');

  // 驗證路由跳轉
  await expect(page).toHaveURL('/dashboard');
});

test('管理員選單顯示設定選項', async ({ page }) => {
  // 以管理員身份登入
  await page.goto('/login');
  await page.fill('[name="email"]', 'admin@test.com');
  await page.fill('[name="password"]', 'password123');
  await page.click('button[type="submit"]');

  // 等待跳轉到管理後台
  await page.waitForURL('/admin');

  // 點擊頭像展開選單
  await page.click('[aria-label="開啟使用者選單"]');

  // 驗證選單選項
  await expect(page.locator('text=設定')).toBeVisible();

  // 點擊設定
  await page.click('text=設定');

  // 驗證路由跳轉
  await expect(page).toHaveURL('/admin/settings');
});
```

### 11.3 視覺回歸測試（Visual Regression）

```typescript
// Percy Visual Testing
test('業務員選單視覺快照', async ({ page }) => {
  await page.goto('/dashboard');
  await page.click('[aria-label="開啟使用者選單"]');

  await percySnapshot(page, 'Salesperson Dropdown Menu');
});

test('管理員選單視覺快照', async ({ page }) => {
  await page.goto('/admin');
  await page.click('[aria-label="開啟使用者選單"]');

  await percySnapshot(page, 'Admin Dropdown Menu');
});
```

---

## 12. 效能考量

### 12.1 優化策略

1. **避免不必要的重渲染**
   - `getDashboardLinks()` 函數不依賴外部狀態
   - 僅當 `user.role` 變更時重新計算

2. **條件渲染優化**
   - 使用 `&&` 短路運算避免渲染空元素
   - 不使用三元運算符避免渲染 `null`

3. **事件處理優化**
   - `onClick` 使用箭頭函數避免建立新函數
   - Mobile Menu 關閉時使用回調避免延遲

### 12.2 Bundle Size 影響

- Icon Import：6 個 Icon，總計 ~2KB（已壓縮）
- Radix UI Dropdown：~15KB（已包含在現有專案）
- **淨增加**：0KB（移除程式碼）

---

## 13. 組件檢查清單

開發完成後檢查：

### 功能檢查
- [ ] 業務員選單不顯示「設定」選項
- [ ] 管理員選單顯示「設定」選項
- [ ] Mobile Menu 與 Dropdown Menu 一致
- [ ] 所有連結都能正確跳轉
- [ ] 登出功能正常

### UI/UX 檢查
- [ ] 選單寬度正確（224px）
- [ ] 選單右對齊
- [ ] Icon 尺寸和間距正確
- [ ] Hover 效果正常
- [ ] 動畫流暢

### 響應式檢查
- [ ] Desktop 顯示 Dropdown Menu
- [ ] Mobile 顯示 Mobile Menu
- [ ] 不同螢幕尺寸佈局正確

### 無障礙檢查
- [ ] 鍵盤可完全操作
- [ ] ARIA 屬性正確
- [ ] 螢幕閱讀器可正確朗讀
- [ ] 焦點管理正確

### 測試檢查
- [ ] 單元測試通過
- [ ] E2E 測試通過
- [ ] 視覺回歸測試通過
- [ ] 無 TypeScript 錯誤

---

**規格版本**: 1.0
**撰寫日期**: 2026-01-11
**作者**: Claude (Product Designer Agent)
**狀態**: 待審核

**相關文檔**：
- UI/UX 規格：`ui-ux.md`
- 實作細節：`implementation.md`
