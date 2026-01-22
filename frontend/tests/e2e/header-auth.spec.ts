import { test, expect, Page } from '@playwright/test';

// Helper function to save screenshots
async function saveScreenshot(page: Page, name: string) {
  await page.screenshot({
    path: `/tmp/header-auth-${name}-${Date.now()}.png`,
    fullPage: false,
  });
}

// Test configuration
const BASE_URL = process.env.BASE_URL || 'http://localhost:3001';
const API_URL = process.env.API_URL || 'http://localhost:8080';

// Test user credentials (these should match your database seeder)
const TEST_USERS = {
  regular: {
    email: 'user@example.com',
    password: 'password123',
    username: 'Regular User',
  },
  salesperson: {
    email: 'salesperson@example.com',
    password: 'password123',
    username: 'Salesperson User',
  },
  admin: {
    email: 'admin@example.com',
    password: 'password123',
    username: 'Admin User',
  },
};

// Helper function to login
async function login(page: Page, credentials: { email: string; password: string }) {
  await page.goto(`${BASE_URL}/login`);
  await page.waitForLoadState('networkidle');

  // Fill in login form
  await page.fill('input[name="email"]', credentials.email);
  await page.fill('input[name="password"]', credentials.password);

  // Click login button
  await page.click('button[type="submit"]');

  // Wait for navigation
  await page.waitForLoadState('networkidle');
}

// Helper function to logout
async function logout(page: Page) {
  // Open user menu dropdown
  await page.click('button:has-text("登出")').catch(async () => {
    // If dropdown button doesn't have text, try clicking avatar
    await page.locator('button').filter({ has: page.locator('img[alt*="avatar"]') }).first().click();
    await page.click('text=登出');
  });

  // Wait for redirect
  await page.waitForLoadState('networkidle');
}

test.describe('Header Authentication Tests', () => {
  test.describe('1. Unauthenticated State', () => {
    test('should show login and register buttons on homepage', async ({ page }) => {
      await page.goto(`${BASE_URL}/`);
      await page.waitForLoadState('networkidle');

      // Check for login and register buttons
      const loginButton = page.locator('a[href="/login"]', { hasText: '登入' }).first();
      const registerButton = page.locator('a[href="/register"]', { hasText: '註冊' }).first();

      await expect(loginButton).toBeVisible();
      await expect(registerButton).toBeVisible();

      // Should NOT show user avatar
      const avatar = page.locator('img[alt*="avatar"]').first();
      await expect(avatar).not.toBeVisible();

      await saveScreenshot(page, 'unauthenticated-home');
    });

    test('should show login/register on search page', async ({ page }) => {
      await page.goto(`${BASE_URL}/search`);
      await page.waitForLoadState('networkidle');

      const loginButton = page.locator('a[href="/login"]', { hasText: '登入' }).first();
      const registerButton = page.locator('a[href="/register"]', { hasText: '註冊' }).first();

      await expect(loginButton).toBeVisible();
      await expect(registerButton).toBeVisible();

      await saveScreenshot(page, 'unauthenticated-search');
    });

    test('should show login/register on salesperson detail page', async ({ page }) => {
      // Assuming there's a salesperson with ID 1
      await page.goto(`${BASE_URL}/salesperson/1`);
      await page.waitForLoadState('networkidle');

      const loginButton = page.locator('a[href="/login"]', { hasText: '登入' }).first();
      const registerButton = page.locator('a[href="/register"]', { hasText: '註冊' }).first();

      await expect(loginButton).toBeVisible();
      await expect(registerButton).toBeVisible();

      await saveScreenshot(page, 'unauthenticated-salesperson-detail');
    });
  });

  test.describe('2. Loading State', () => {
    test('should show skeleton loading during auth check', async ({ page }) => {
      // Slow down network to catch loading state
      await page.route('**/api/auth/me', async (route) => {
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await route.continue();
      });

      await page.goto(`${BASE_URL}/`);

      // Check for skeleton (animated pulse background)
      const skeleton = page.locator('.animate-pulse').first();
      await expect(skeleton).toBeVisible();

      await saveScreenshot(page, 'loading-skeleton');

      // Wait for loading to complete
      await page.waitForLoadState('networkidle');
    });
  });

  test.describe('3. Authenticated - Regular User', () => {
    test('should show user info in header after login', async ({ page }) => {
      await login(page, TEST_USERS.regular);

      // Should be on homepage after login
      await expect(page).toHaveURL(new RegExp(`${BASE_URL}/?`));

      // Should show user avatar
      const avatar = page.locator('button').filter({ has: page.locator('img') }).first();
      await expect(avatar).toBeVisible();

      // Click to open dropdown
      await avatar.click();

      // Check dropdown menu items for regular user
      await expect(page.locator('text=我的帳號')).toBeVisible();
      await expect(page.locator('a[href="/"]', { hasText: '首頁' })).toBeVisible();
      await expect(page.locator('a[href="/search"]', { hasText: '搜尋業務員' })).toBeVisible();
      await expect(page.locator('text=登出')).toBeVisible();

      await saveScreenshot(page, 'regular-user-dropdown');
    });

    test('should show user info on all pages', async ({ page }) => {
      await login(page, TEST_USERS.regular);

      // Test on multiple pages
      const pages = ['/', '/search', '/salesperson/1'];

      for (const testPath of pages) {
        await page.goto(`${BASE_URL}${testPath}`);
        await page.waitForLoadState('networkidle');

        // Should show user avatar
        const avatar = page.locator('button').filter({ has: page.locator('img') }).first();
        await expect(avatar).toBeVisible();

        await saveScreenshot(page, `regular-user-${testPath.replace(/\//g, '-')}`);
      }
    });
  });

  test.describe('4. Authenticated - Salesperson', () => {
    test('should show salesperson menu items', async ({ page }) => {
      await login(page, TEST_USERS.salesperson);

      // Should be on dashboard after login
      await expect(page).toHaveURL(new RegExp(`${BASE_URL}/dashboard`));

      // Navigate to public page to test header
      await page.goto(`${BASE_URL}/`);
      await page.waitForLoadState('networkidle');

      // Click user avatar
      const avatar = page.locator('button').filter({ has: page.locator('img') }).first();
      await expect(avatar).toBeVisible();
      await avatar.click();

      // Check dropdown menu items for salesperson
      await expect(page.locator('text=我的帳號')).toBeVisible();
      await expect(page.locator('a[href="/dashboard"]')).toBeVisible();
      await expect(page.locator('text=個人中心')).toBeVisible();
      await expect(page.locator('text=工作經驗')).toBeVisible();
      await expect(page.locator('text=登出')).toBeVisible();

      await saveScreenshot(page, 'salesperson-dropdown');
    });

    test('should access dashboard pages', async ({ page }) => {
      await login(page, TEST_USERS.salesperson);

      // Test dashboard pages
      const dashboardPages = [
        '/dashboard',
        '/dashboard/experiences',
        '/dashboard/certifications',
        '/dashboard/approval-status',
      ];

      for (const testPath of dashboardPages) {
        await page.goto(`${BASE_URL}${testPath}`);
        await page.waitForLoadState('networkidle');

        // Should not redirect
        await expect(page).toHaveURL(new RegExp(`${BASE_URL}${testPath}`));

        await saveScreenshot(page, `salesperson-${testPath.replace(/\//g, '-')}`);
      }
    });
  });

  test.describe('5. Authenticated - Admin', () => {
    test('should show admin menu items', async ({ page }) => {
      await login(page, TEST_USERS.admin);

      // Should be on admin dashboard after login
      await expect(page).toHaveURL(new RegExp(`${BASE_URL}/admin`));

      // Navigate to public page to test header
      await page.goto(`${BASE_URL}/`);
      await page.waitForLoadState('networkidle');

      // Click user avatar
      const avatar = page.locator('button').filter({ has: page.locator('img') }).first();
      await expect(avatar).toBeVisible();
      await avatar.click();

      // Check dropdown menu items for admin
      await expect(page.locator('text=我的帳號')).toBeVisible();
      await expect(page.locator('text=管理後台')).toBeVisible();
      await expect(page.locator('text=審核管理')).toBeVisible();
      await expect(page.locator('text=使用者管理')).toBeVisible();
      await expect(page.locator('text=統計報表')).toBeVisible();
      await expect(page.locator('text=設定')).toBeVisible();
      await expect(page.locator('text=登出')).toBeVisible();

      await saveScreenshot(page, 'admin-dropdown');
    });

    test('should access admin pages', async ({ page }) => {
      await login(page, TEST_USERS.admin);

      // Test admin pages
      const adminPages = [
        '/admin',
        '/admin/approvals',
        '/admin/users',
        '/admin/statistics',
      ];

      for (const testPath of adminPages) {
        await page.goto(`${BASE_URL}${testPath}`);
        await page.waitForLoadState('networkidle');

        // Should not redirect
        await expect(page).toHaveURL(new RegExp(`${BASE_URL}${testPath}`));

        await saveScreenshot(page, `admin-${testPath.replace(/\//g, '-')}`);
      }
    });
  });

  test.describe('6. Logout Flow', () => {
    test('should logout and show login/register buttons', async ({ page }) => {
      await login(page, TEST_USERS.regular);

      // Verify logged in
      const avatarBefore = page.locator('button').filter({ has: page.locator('img') }).first();
      await expect(avatarBefore).toBeVisible();

      // Click avatar to open dropdown
      await avatarBefore.click();

      // Click logout
      await page.locator('text=登出').click();

      // Wait for redirect
      await page.waitForLoadState('networkidle');

      // Should show login/register buttons
      const loginButton = page.locator('a[href="/login"]', { hasText: '登入' }).first();
      const registerButton = page.locator('a[href="/register"]', { hasText: '註冊' }).first();

      await expect(loginButton).toBeVisible();
      await expect(registerButton).toBeVisible();

      // Should NOT show avatar
      const avatarAfter = page.locator('button').filter({ has: page.locator('img') }).first();
      await expect(avatarAfter).not.toBeVisible();

      await saveScreenshot(page, 'after-logout');
    });

    test('should logout from salesperson page', async ({ page }) => {
      await login(page, TEST_USERS.salesperson);

      await page.goto(`${BASE_URL}/salesperson/1`);
      await page.waitForLoadState('networkidle');

      // Click avatar to open dropdown
      const avatar = page.locator('button').filter({ has: page.locator('img') }).first();
      await avatar.click();

      // Click logout
      await page.locator('text=登出').click();

      // Wait for auth state to update
      await page.waitForLoadState('networkidle');

      // Should show login/register buttons
      const loginButton = page.locator('a[href="/login"]', { hasText: '登入' }).first();
      await expect(loginButton).toBeVisible();

      await saveScreenshot(page, 'logout-from-salesperson-page');
    });
  });

  test.describe('7. Role-based Redirects', () => {
    test('regular user should not access dashboard', async ({ page }) => {
      await login(page, TEST_USERS.regular);

      // Try to access dashboard
      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Should redirect to homepage
      await expect(page).toHaveURL(new RegExp(`${BASE_URL}/?`));

      await saveScreenshot(page, 'regular-user-dashboard-redirect');
    });

    test('regular user should not access admin', async ({ page }) => {
      await login(page, TEST_USERS.regular);

      // Try to access admin
      await page.goto(`${BASE_URL}/admin`);
      await page.waitForLoadState('networkidle');

      // Should redirect to login
      await expect(page).toHaveURL(new RegExp(`${BASE_URL}/login`));

      await saveScreenshot(page, 'regular-user-admin-redirect');
    });

    test('salesperson should not access admin', async ({ page }) => {
      await login(page, TEST_USERS.salesperson);

      // Try to access admin
      await page.goto(`${BASE_URL}/admin`);
      await page.waitForLoadState('networkidle');

      // Should redirect to login
      await expect(page).toHaveURL(new RegExp(`${BASE_URL}/login`));

      await saveScreenshot(page, 'salesperson-admin-redirect');
    });
  });

  test.describe('8. Responsive Design', () => {
    test('should work on mobile viewport', async ({ page }) => {
      // Set mobile viewport
      await page.setViewportSize({ width: 375, height: 667 });

      await page.goto(`${BASE_URL}/`);
      await page.waitForLoadState('networkidle');

      // Should show mobile menu button
      const mobileMenuButton = page.locator('button[aria-label*="menu"], button:has-text("開啟選單")').first();
      await expect(mobileMenuButton).toBeVisible();

      await saveScreenshot(page, 'mobile-unauthenticated');

      // Login
      await login(page, TEST_USERS.regular);

      // Should still show mobile menu button
      await expect(mobileMenuButton).toBeVisible();

      // Click to open mobile menu
      await mobileMenuButton.click();

      // Should show menu items
      await expect(page.locator('text=首頁')).toBeVisible();
      await expect(page.locator('text=搜尋業務員')).toBeVisible();

      await saveScreenshot(page, 'mobile-authenticated');
    });
  });
});
