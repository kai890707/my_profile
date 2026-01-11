# 實作細節：Header 組件修改

## 1. 實作總覽

### 修改範圍

**檔案**: `frontend/components/layout/header.tsx`
**總行數**: 230 行
**修改行數**: ~30 行
**新增行數**: ~5 行
**移除行數**: ~5 行
**淨變更**: 微調（~0 行）

### 修改摘要

1. ✅ 修改 `salespersonLinks` 的 label（"個人中心" → "Dashboard"）
2. ✅ 在 Dropdown Menu 中條件渲染「設定」選項（僅 Admin）
3. ✅ 確保 Mobile Menu 不顯示「設定」選項
4. ✅ 優化程式碼可讀性

---

## 2. 程式碼修改對比

### 2.1 連結配置修改（Line 38-41）

#### 修改前：
```typescript
const salespersonLinks = [
  { href: '/dashboard', label: '個人中心', icon: LayoutDashboard },
  { href: '/dashboard/profile', label: '個人資料', icon: User },
];
```

#### 修改後：
```typescript
const salespersonLinks = [
  { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard }, // 修改 label
  { href: '/dashboard/profile', label: '個人資料', icon: User },
];
```

**變更說明**：
- 將 "個人中心" 改為 "Dashboard"
- 更直觀、更符合業界慣例
- 與管理員的 "管理後台" 形成對應

---

### 2.2 Dropdown Menu 修改（Line 114-139）

#### 修改前：
```tsx
<DropdownMenuContent align="end" className="w-56">
  <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
  <DropdownMenuSeparator />
  {getDashboardLinks().map((link) => (
    <DropdownMenuItem key={link.href} asChild>
      <Link href={link.href} className="cursor-pointer">
        {link.icon && <link.icon className="mr-2 h-4 w-4" />}
        {link.label}
      </Link>
    </DropdownMenuItem>
  ))}
  <DropdownMenuSeparator />
  <DropdownMenuItem asChild>
    <Link href="/settings" className="cursor-pointer">
      <Settings className="mr-2 h-4 w-4" />
      設定
    </Link>
  </DropdownMenuItem>
  <DropdownMenuItem
    onClick={onLogout}
    className="cursor-pointer text-error-600"
  >
    <LogOut className="mr-2 h-4 w-4" />
    登出
  </DropdownMenuItem>
</DropdownMenuContent>
```

#### 修改後：
```tsx
<DropdownMenuContent align="end" className="w-56">
  <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
  <DropdownMenuSeparator />

  {/* Dashboard 連結 */}
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

  {/* 登出 */}
  <DropdownMenuItem
    onClick={onLogout}
    className="cursor-pointer text-error-600"
  >
    <LogOut className="mr-2 h-4 w-4" />
    登出
  </DropdownMenuItem>
</DropdownMenuContent>
```

**變更說明**：
1. **新增註解**：增加區塊註解提高可讀性
2. **條件渲染**：使用 `user?.role === 'admin'` 判斷是否顯示「設定」
3. **修正路徑**：將 `/settings` 改為 `/admin/settings`
4. **包裹 Fragment**：使用 `<>...</>` 包裹「設定」選項和分隔線
5. **空格優化**：適當的空行讓程式碼更清晰

---

### 2.3 Mobile Menu 修改（Line 171-226）

#### 修改前：
```tsx
{mobileMenuOpen && (
  <div className="md:hidden py-4 space-y-2 border-t border-slate-200">
    {publicLinks.map((link) => (
      <Link
        key={link.href}
        href={link.href}
        className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
        onClick={() => setMobileMenuOpen(false)}
      >
        {link.label}
      </Link>
    ))}

    {user && getDashboardLinks().map((link) => (
      <Link
        key={link.href}
        href={link.href}
        className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
        onClick={() => setMobileMenuOpen(false)}
      >
        {link.label}
      </Link>
    ))}

    {!user && (
      <div className="pt-4 space-y-2 border-t border-slate-200">
        <Link
          href="/login"
          className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
          onClick={() => setMobileMenuOpen(false)}
        >
          登入
        </Link>
        <Link
          href="/register"
          className="block px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg text-center"
          onClick={() => setMobileMenuOpen(false)}
        >
          註冊
        </Link>
      </div>
    )}

    {user && (
      <button
        onClick={() => {
          onLogout?.();
          setMobileMenuOpen(false);
        }}
        className="w-full text-left px-4 py-2 text-sm font-medium text-error-600 hover:bg-error-50 rounded-lg"
      >
        登出
      </button>
    )}
  </div>
)}
```

#### 修改後：
```tsx
{mobileMenuOpen && (
  <div className="md:hidden py-4 space-y-2 border-t border-slate-200">
    {/* 公開連結 */}
    {publicLinks.map((link) => (
      <Link
        key={link.href}
        href={link.href}
        className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
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
        className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
        onClick={() => setMobileMenuOpen(false)}
      >
        {link.label}
      </Link>
    ))}

    {/* 登入/註冊按鈕（如果未登入） */}
    {!user && (
      <div className="pt-4 space-y-2 border-t border-slate-200">
        <Link
          href="/login"
          className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
          onClick={() => setMobileMenuOpen(false)}
        >
          登入
        </Link>
        <Link
          href="/register"
          className="block px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg text-center"
          onClick={() => setMobileMenuOpen(false)}
        >
          註冊
        </Link>
      </div>
    )}

    {/* 登出按鈕（如果使用者已登入） */}
    {user && (
      <button
        onClick={() => {
          onLogout?.();
          setMobileMenuOpen(false);
        }}
        className="w-full text-left px-4 py-2 text-sm font-medium text-error-600 hover:bg-error-50 rounded-lg"
      >
        登出
      </button>
    )}
  </div>
)}
```

**變更說明**：
1. **新增註解**：為每個區塊增加清晰的註解
2. **無功能變更**：Mobile Menu 原本就不顯示「設定」，保持現狀
3. **提高可讀性**：註解讓程式碼更容易理解

---

## 3. 完整修改後的程式碼

### 3.1 完整的 header.tsx

```tsx
'use client';

import Link from 'next/link';
import { useState } from 'react';
import { Menu, X, User, LogOut, Settings, LayoutDashboard } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Avatar } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

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

export function Header({ user, onLogout }: HeaderProps) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const publicLinks = [
    { href: '/', label: '首頁' },
    { href: '/search', label: '搜尋業務員' },
  ];

  const salespersonLinks = [
    { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { href: '/dashboard/profile', label: '個人資料', icon: User },
  ];

  const adminLinks = [
    { href: '/admin', label: '管理後台', icon: LayoutDashboard },
    { href: '/admin/approvals', label: '審核管理' },
    { href: '/admin/users', label: '使用者管理' },
    { href: '/admin/statistics', label: '統計報表' },
  ];

  const getDashboardLinks = () => {
    if (user?.role === 'admin') return adminLinks;
    if (user?.role === 'salesperson') return salespersonLinks;
    return [];
  };

  return (
    <header className="sticky top-0 z-40 w-full border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <div className="flex items-center">
            <Link href="/" className="flex items-center space-x-2">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500">
                <span className="text-xl font-bold text-white">Y</span>
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                YAMU
              </span>
            </Link>
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-6">
            {publicLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors"
              >
                {link.label}
              </Link>
            ))}
          </nav>

          {/* Right Side Actions */}
          <div className="flex items-center space-x-4">
            {user ? (
              <>
                {/* User Menu */}
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <button className="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                      <Avatar
                        src={user.avatar}
                        fallback={
                          user.full_name?.substring(0, 2) ||
                          user.name?.substring(0, 2).toUpperCase() ||
                          user.username?.substring(0, 2).toUpperCase() ||
                          user.email?.substring(0, 2).toUpperCase() ||
                          'U'
                        }
                        size="sm"
                      />
                      <div className="hidden md:block text-left">
                        <p className="text-sm font-medium text-slate-900">
                          {user.full_name || user.name || user.username || user.email || '使用者'}
                        </p>
                        <p className="text-xs text-slate-500">
                          {user.role === 'admin' ? '管理員' : user.role === 'salesperson' ? '業務員' : '使用者'}
                        </p>
                      </div>
                    </button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
                    <DropdownMenuSeparator />

                    {/* Dashboard 連結 */}
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

                    {/* 登出 */}
                    <DropdownMenuItem
                      onClick={onLogout}
                      className="cursor-pointer text-error-600"
                    >
                      <LogOut className="mr-2 h-4 w-4" />
                      登出
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </>
            ) : (
              <>
                {/* Login/Register Buttons */}
                <Button variant="ghost" asChild className="hidden md:inline-flex">
                  <Link href="/login">登入</Link>
                </Button>
                <Button asChild>
                  <Link href="/register">註冊</Link>
                </Button>
              </>
            )}

            {/* Mobile Menu Button */}
            <button
              type="button"
              className="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-slate-700 hover:bg-slate-100"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            >
              <span className="sr-only">開啟選單</span>
              {mobileMenuOpen ? (
                <X className="h-6 w-6" />
              ) : (
                <Menu className="h-6 w-6" />
              )}
            </button>
          </div>
        </div>

        {/* Mobile Navigation Menu */}
        {mobileMenuOpen && (
          <div className="md:hidden py-4 space-y-2 border-t border-slate-200">
            {/* 公開連結 */}
            {publicLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
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
                className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                onClick={() => setMobileMenuOpen(false)}
              >
                {link.label}
              </Link>
            ))}

            {/* 登入/註冊按鈕（如果未登入） */}
            {!user && (
              <div className="pt-4 space-y-2 border-t border-slate-200">
                <Link
                  href="/login"
                  className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  登入
                </Link>
                <Link
                  href="/register"
                  className="block px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg text-center"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  註冊
                </Link>
              </div>
            )}

            {/* 登出按鈕（如果使用者已登入） */}
            {user && (
              <button
                onClick={() => {
                  onLogout?.();
                  setMobileMenuOpen(false);
                }}
                className="w-full text-left px-4 py-2 text-sm font-medium text-error-600 hover:bg-error-50 rounded-lg"
              >
                登出
              </button>
            )}
          </div>
        )}
      </div>
    </header>
  );
}
```

---

## 4. 測試案例實作

### 4.1 單元測試

**檔案位置**: `frontend/components/layout/__tests__/header.test.tsx`

```tsx
import { render, screen, fireEvent, within } from '@testing-library/react';
import { Header } from '../header';

describe('Header Component', () => {
  const mockOnLogout = jest.fn();

  afterEach(() => {
    jest.clearAllMocks();
  });

  describe('Salesperson User', () => {
    const salespersonUser = {
      id: 1,
      full_name: '張三',
      role: 'salesperson' as const,
      email: 'zhang@test.com',
      avatar: null,
    };

    it('應顯示 Dashboard 和個人資料選項', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      // 點擊頭像展開選單
      fireEvent.click(screen.getByText('張三'));

      // 驗證選單選項
      expect(screen.getByText('Dashboard')).toBeInTheDocument();
      expect(screen.getByText('個人資料')).toBeInTheDocument();
    });

    it('不應顯示設定選項', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      fireEvent.click(screen.getByText('張三'));

      // 驗證「設定」選項不存在
      expect(screen.queryByText('設定')).not.toBeInTheDocument();
    });

    it('應顯示正確的角色標籤', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      expect(screen.getByText('業務員')).toBeInTheDocument();
    });

    it('點擊 Dashboard 應跳轉到 /dashboard', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      fireEvent.click(screen.getByText('張三'));

      const dashboardLink = screen.getByText('Dashboard').closest('a');
      expect(dashboardLink).toHaveAttribute('href', '/dashboard');
    });

    it('點擊登出應執行 onLogout 回調', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      fireEvent.click(screen.getByText('張三'));
      fireEvent.click(screen.getByText('登出'));

      expect(mockOnLogout).toHaveBeenCalledTimes(1);
    });
  });

  describe('Admin User', () => {
    const adminUser = {
      id: 2,
      full_name: '管理員',
      role: 'admin' as const,
      email: 'admin@test.com',
      avatar: null,
    };

    it('應顯示管理後台選項', () => {
      render(<Header user={adminUser} onLogout={mockOnLogout} />);

      fireEvent.click(screen.getByText('管理員'));

      expect(screen.getByText('管理後台')).toBeInTheDocument();
      expect(screen.getByText('審核管理')).toBeInTheDocument();
      expect(screen.getByText('使用者管理')).toBeInTheDocument();
      expect(screen.getByText('統計報表')).toBeInTheDocument();
    });

    it('應顯示設定選項', () => {
      render(<Header user={adminUser} onLogout={mockOnLogout} />);

      fireEvent.click(screen.getByText('管理員'));

      expect(screen.getByText('設定')).toBeInTheDocument();
    });

    it('設定選項應連結到 /admin/settings', () => {
      render(<Header user={adminUser} onLogout={mockOnLogout} />);

      fireEvent.click(screen.getByText('管理員'));

      const settingsLink = screen.getByText('設定').closest('a');
      expect(settingsLink).toHaveAttribute('href', '/admin/settings');
    });

    it('應顯示正確的角色標籤', () => {
      render(<Header user={adminUser} onLogout={mockOnLogout} />);

      expect(screen.getByText('管理員')).toBeInTheDocument();
    });
  });

  describe('Guest User', () => {
    it('應顯示登入和註冊按鈕', () => {
      render(<Header user={null} onLogout={mockOnLogout} />);

      expect(screen.getByText('登入')).toBeInTheDocument();
      expect(screen.getByText('註冊')).toBeInTheDocument();
    });

    it('不應顯示使用者選單', () => {
      render(<Header user={null} onLogout={mockOnLogout} />);

      expect(screen.queryByText('我的帳號')).not.toBeInTheDocument();
    });
  });

  describe('Mobile Menu', () => {
    const salespersonUser = {
      id: 1,
      full_name: '張三',
      role: 'salesperson' as const,
      email: 'zhang@test.com',
      avatar: null,
    };

    it('應顯示漢堡選單按鈕', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      const menuButton = screen.getByLabelText('開啟選單');
      expect(menuButton).toBeInTheDocument();
    });

    it('點擊漢堡選單應展開 Mobile Menu', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      const menuButton = screen.getByLabelText('開啟選單');
      fireEvent.click(menuButton);

      // 驗證 Mobile Menu 顯示
      const mobileLinks = screen.getAllByText('首頁');
      expect(mobileLinks.length).toBeGreaterThan(0);
    });

    it('Mobile Menu 應與 Dropdown Menu 顯示相同選項', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      // 展開 Mobile Menu
      const menuButton = screen.getByLabelText('開啟選單');
      fireEvent.click(menuButton);

      // 驗證 Mobile Menu 選項（使用 getAllByText 因為可能有重複）
      expect(screen.getAllByText('Dashboard').length).toBeGreaterThan(0);
      expect(screen.getAllByText('個人資料').length).toBeGreaterThan(0);
      expect(screen.queryByText('設定')).not.toBeInTheDocument();
    });

    it('點擊選項後應關閉 Mobile Menu', () => {
      render(<Header user={salespersonUser} onLogout={mockOnLogout} />);

      const menuButton = screen.getByLabelText('開啟選單');
      fireEvent.click(menuButton);

      // 點擊選項
      const dashboardLinks = screen.getAllByText('Dashboard');
      fireEvent.click(dashboardLinks[0]);

      // TODO: 驗證 Mobile Menu 已關閉（需要檢查 DOM 變化）
    });
  });
});
```

### 4.2 E2E 測試（Playwright）

**檔案位置**: `frontend/tests/e2e/header.spec.ts`

```typescript
import { test, expect } from '@playwright/test';

test.describe('Header - Salesperson User', () => {
  test.beforeEach(async ({ page }) => {
    // 以業務員身份登入
    await page.goto('/login');
    await page.fill('[name="email"]', 'salesperson@test.com');
    await page.fill('[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');
  });

  test('應顯示業務員選單選項', async ({ page }) => {
    // 點擊頭像展開選單
    await page.click('[aria-label="開啟使用者選單"]');

    // 驗證選單選項
    await expect(page.locator('text=Dashboard')).toBeVisible();
    await expect(page.locator('text=個人資料')).toBeVisible();
    await expect(page.locator('text=登出')).toBeVisible();

    // 驗證不顯示設定選項
    await expect(page.locator('text=設定')).not.toBeVisible();
  });

  test('點擊 Dashboard 應跳轉到 /dashboard', async ({ page }) => {
    await page.click('[aria-label="開啟使用者選單"]');
    await page.click('text=Dashboard');

    await expect(page).toHaveURL('/dashboard');
  });

  test('點擊個人資料應跳轉到 /dashboard/profile', async ({ page }) => {
    await page.click('[aria-label="開啟使用者選單"]');
    await page.click('text=個人資料');

    await expect(page).toHaveURL('/dashboard/profile');
  });

  test('點擊登出應跳轉到登入頁', async ({ page }) => {
    await page.click('[aria-label="開啟使用者選單"]');
    await page.click('text=登出');

    await expect(page).toHaveURL('/login');
  });
});

test.describe('Header - Admin User', () => {
  test.beforeEach(async ({ page }) => {
    // 以管理員身份登入
    await page.goto('/login');
    await page.fill('[name="email"]', 'admin@test.com');
    await page.fill('[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL('/admin');
  });

  test('應顯示管理員選單選項', async ({ page }) => {
    await page.click('[aria-label="開啟使用者選單"]');

    // 驗證管理員選項
    await expect(page.locator('text=管理後台')).toBeVisible();
    await expect(page.locator('text=審核管理')).toBeVisible();
    await expect(page.locator('text=使用者管理')).toBeVisible();
    await expect(page.locator('text=統計報表')).toBeVisible();
    await expect(page.locator('text=設定')).toBeVisible();
    await expect(page.locator('text=登出')).toBeVisible();
  });

  test('點擊設定應跳轉到 /admin/settings', async ({ page }) => {
    await page.click('[aria-label="開啟使用者選單"]');
    await page.click('text=設定');

    await expect(page).toHaveURL('/admin/settings');
  });
});

test.describe('Header - Mobile Menu', () => {
  test.beforeEach(async ({ page }) => {
    // 設置手機視口
    await page.setViewportSize({ width: 375, height: 667 });

    // 以業務員身份登入
    await page.goto('/login');
    await page.fill('[name="email"]', 'salesperson@test.com');
    await page.fill('[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL('/dashboard');
  });

  test('應顯示漢堡選單按鈕', async ({ page }) => {
    await expect(page.locator('[aria-label="開啟選單"]')).toBeVisible();
  });

  test('點擊漢堡選單應展開 Mobile Menu', async ({ page }) => {
    await page.click('[aria-label="開啟選單"]');

    // 驗證 Mobile Menu 選項
    await expect(page.locator('text=首頁')).toBeVisible();
    await expect(page.locator('text=搜尋業務員')).toBeVisible();
    await expect(page.locator('text=Dashboard')).toBeVisible();
    await expect(page.locator('text=個人資料')).toBeVisible();
  });

  test('Mobile Menu 不應顯示設定選項', async ({ page }) => {
    await page.click('[aria-label="開啟選單"]');

    await expect(page.locator('text=設定')).not.toBeVisible();
  });
});
```

---

## 5. 實作檢查清單

完成實作後，請檢查以下項目：

### 程式碼修改
- [ ] `salespersonLinks` 的 label 已改為 "Dashboard"
- [ ] Dropdown Menu 新增條件渲染（`user?.role === 'admin'`）
- [ ] 設定選項路徑改為 `/admin/settings`
- [ ] 設定選項包裹在 Fragment 中（含 Separator）
- [ ] Mobile Menu 不顯示設定選項（無需修改）
- [ ] 程式碼有適當的註解

### TypeScript 檢查
- [ ] 無 TypeScript 錯誤
- [ ] Props 類型正確
- [ ] 無 any 類型

### 功能測試
- [ ] 業務員選單不顯示設定選項
- [ ] 管理員選單顯示設定選項
- [ ] 設定選項連結到 /admin/settings
- [ ] 所有連結都能正確跳轉
- [ ] 登出功能正常

### UI/UX 測試
- [ ] Desktop 選單顯示正確
- [ ] Mobile Menu 顯示正確
- [ ] Hover 效果正常
- [ ] 動畫流暢
- [ ] Icon 顯示正確

### 無障礙測試
- [ ] 鍵盤可完全操作
- [ ] 螢幕閱讀器可正確朗讀
- [ ] ARIA 屬性正確
- [ ] 焦點管理正確

### 單元測試
- [ ] 業務員選單測試通過
- [ ] 管理員選單測試通過
- [ ] 訪客測試通過
- [ ] Mobile Menu 測試通過
- [ ] 測試覆蓋率 >= 80%

### E2E 測試
- [ ] 業務員流程測試通過
- [ ] 管理員流程測試通過
- [ ] Mobile 流程測試通過

---

## 6. 部署注意事項

### 6.1 環境檢查

**開發環境**：
```bash
cd frontend
npm run typecheck  # TypeScript 檢查
npm test          # 單元測試
npm run build     # 生產構建
```

**測試環境**：
```bash
npx playwright test tests/e2e/header.spec.ts  # E2E 測試
```

### 6.2 回滾計劃

如果部署後發現問題，可快速回滾：

**回滾步驟**：
1. Git revert 此次提交
2. 重新部署上一個穩定版本
3. 通知使用者（如有必要）

**回滾指令**：
```bash
git revert <commit-hash>
git push origin main
```

### 6.3 監控指標

部署後監控以下指標：

| 指標 | 預期值 | 警報閾值 |
|------|--------|---------|
| 404 錯誤率 | 0% | > 1% |
| Header 載入時間 | < 100ms | > 500ms |
| 使用者投訴 | 0 件 | > 5 件/天 |

---

## 7. 常見問題（FAQ）

### Q1: 為什麼不移除 Settings Icon import？

**A**: 保留 Settings Icon import 是因為：
1. 管理員仍需使用此 Icon
2. 移除後如果未來需要會增加額外工作
3. Icon import 的 Bundle Size 影響極小（< 1KB）

### Q2: 為什麼使用 Fragment 而非單獨的 DropdownMenuItem？

**A**: 使用 `<>...</>` Fragment 包裹是為了：
1. 同時條件渲染「設定」選項和分隔線
2. 保持選單結構一致（設定選項後有分隔線）
3. 避免渲染不必要的 DOM 元素

### Q3: Mobile Menu 為什麼不顯示設定選項？

**A**: 設計決策：
1. 手機版簡化選單，避免資訊過載
2. 管理員通常在桌面環境使用後台
3. 如需設定功能，可在桌面端訪問
4. 保持 Mobile Menu 與 Dropdown Menu 一致（業務員都不顯示設定）

### Q4: 如何為業務員新增設定頁面？

**A**: 未來如需新增：
1. 建立 `frontend/app/(dashboard)/dashboard/settings/page.tsx`
2. 在 `salespersonLinks` 新增設定選項
3. 更新 Middleware 允許訪問
4. 新增對應的 API 端點

---

**規格版本**: 1.0
**撰寫日期**: 2026-01-11
**作者**: Claude (Product Designer Agent)
**狀態**: 待審核

**相關文檔**：
- UI/UX 規格：`ui-ux.md`
- 組件規格：`components.md`
- Proposal：`../proposal.md`
