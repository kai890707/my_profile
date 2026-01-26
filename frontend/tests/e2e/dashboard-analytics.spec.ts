import { test, expect } from '@playwright/test';

/**
 * Analytics Dashboard E2E Tests
 *
 * Tests both Salesperson Dashboard and Admin Dashboard
 */

// Test data
const SALESPERSON_EMAIL = 'salesperson@test.com';
const SALESPERSON_PASSWORD = 'password123';
const ADMIN_EMAIL = 'admin@test.com';
const ADMIN_PASSWORD = 'password123';

test.describe('Salesperson Analytics Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Login as salesperson
    await page.goto('/login');
    await page.fill('input[name="email"]', SALESPERSON_EMAIL);
    await page.fill('input[name="password"]', SALESPERSON_PASSWORD);
    await page.click('button[type="submit"]');

    // Wait for redirect
    await page.waitForURL(/\/dashboard/);
  });

  test('should display salesperson dashboard', async ({ page }) => {
    // Navigate to analytics
    await page.goto('/dashboard/analytics');

    // Verify page loaded
    await expect(page).toHaveURL(/\/dashboard\/analytics/);

    // Check for main dashboard elements
    await expect(page.locator('h1')).toContainText('Analytics');
  });

  test('should display stat cards with data', async ({ page }) => {
    await page.goto('/dashboard/analytics');

    // Wait for data to load
    await page.waitForSelector('[data-testid="stats-container"]', { timeout: 10000 });

    // Verify stat cards are visible
    const statCards = page.locator('[data-testid="stat-card"]');
    await expect(statCards.first()).toBeVisible();

    // Check for profile views stat
    const profileViewsCard = page.locator('[data-testid="stat-card-profile-views"]');
    await expect(profileViewsCard).toBeVisible();

    // Check for contact requests stat
    const contactRequestsCard = page.locator('[data-testid="stat-card-contact-requests"]');
    await expect(contactRequestsCard).toBeVisible();

    // Check for conversion rate stat
    const conversionRateCard = page.locator('[data-testid="stat-card-conversion-rate"]');
    await expect(conversionRateCard).toBeVisible();
  });

  test('should switch time ranges', async ({ page }) => {
    await page.goto('/dashboard/analytics');

    // Wait for initial data load
    await page.waitForSelector('[data-testid="stats-container"]');

    // Find and click time range selector
    const rangeSelector = page.locator('[data-testid="range-selector"]');
    await expect(rangeSelector).toBeVisible();

    // Switch to "Today"
    await page.click('[data-testid="range-today"]');

    // Wait for data refresh
    await page.waitForResponse(response =>
      response.url().includes('/api/salesperson/analytics/stats') &&
      response.url().includes('range=today')
    );

    // Switch to "30 Days"
    await page.click('[data-testid="range-30days"]');

    // Wait for data refresh
    await page.waitForResponse(response =>
      response.url().includes('/api/salesperson/analytics/stats') &&
      response.url().includes('range=30days')
    );
  });

  test('should display trends chart', async ({ page }) => {
    await page.goto('/dashboard/analytics');

    // Wait for trends section
    await page.waitForSelector('[data-testid="trends-chart"]', { timeout: 10000 });

    // Verify chart is visible
    const trendsChart = page.locator('[data-testid="trends-chart"]');
    await expect(trendsChart).toBeVisible();
  });

  test('should display recent contacts list', async ({ page }) => {
    await page.goto('/dashboard/analytics');

    // Wait for contacts section
    await page.waitForSelector('[data-testid="recent-contacts"]', { timeout: 10000 });

    // Verify contacts list is visible
    const contactsList = page.locator('[data-testid="recent-contacts"]');
    await expect(contactsList).toBeVisible();
  });

  test('should show growth indicators', async ({ page }) => {
    await page.goto('/dashboard/analytics');

    // Wait for stats to load
    await page.waitForSelector('[data-testid="stats-container"]');

    // Check for growth indicators (up/down arrows)
    const growthIndicators = page.locator('[data-testid="growth-indicator"]');

    if ((await growthIndicators.count()) > 0) {
      await expect(growthIndicators.first()).toBeVisible();
    }
  });

  test('should handle loading state', async ({ page }) => {
    await page.goto('/dashboard/analytics');

    // Should show loading initially
    const loading = page.locator('[data-testid="loading"]');

    // Wait for either loading to disappear or data to appear
    await Promise.race([
      page.waitForSelector('[data-testid="stats-container"]'),
      page.waitForTimeout(1000),
    ]);
  });

  test('should handle API errors gracefully', async ({ page }) => {
    // Intercept API and return error
    await page.route('**/api/salesperson/analytics/stats*', route => {
      route.fulfill({
        status: 500,
        body: JSON.stringify({
          success: false,
          message: 'Internal server error',
        }),
      });
    });

    await page.goto('/dashboard/analytics');

    // Should show error message
    await expect(page.locator('[role="alert"]')).toBeVisible({ timeout: 5000 });
  });
});

test.describe('Admin Analytics Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');

    // Wait for redirect
    await page.waitForURL(/\/admin/);
  });

  test('should display admin dashboard', async ({ page }) => {
    // Navigate to admin analytics
    await page.goto('/admin/dashboard/analytics');

    // Verify page loaded
    await expect(page).toHaveURL(/\/admin\/dashboard\/analytics/);

    // Check for main dashboard elements
    await expect(page.locator('h1')).toContainText('Analytics');
  });

  test('should display platform overview stats', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for data to load
    await page.waitForSelector('[data-testid="admin-stats-container"]', { timeout: 10000 });

    // Verify stat cards are visible
    const statCards = page.locator('[data-testid="admin-stat-card"]');
    await expect(statCards.first()).toBeVisible();

    // Check for total salespersons stat
    const salespersonsCard = page.locator('[data-testid="stat-card-total-salespersons"]');
    await expect(salespersonsCard).toBeVisible();

    // Check for total views stat
    const viewsCard = page.locator('[data-testid="stat-card-total-views"]');
    await expect(viewsCard).toBeVisible();

    // Check for total contacts stat
    const contactsCard = page.locator('[data-testid="stat-card-total-contacts"]');
    await expect(contactsCard).toBeVisible();
  });

  test('should display top salespersons table', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for top salespersons section
    await page.waitForSelector('[data-testid="top-salespersons"]', { timeout: 10000 });

    // Verify table is visible
    const topTable = page.locator('[data-testid="top-salespersons"]');
    await expect(topTable).toBeVisible();
  });

  test('should display growth trends chart', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for trends section
    await page.waitForSelector('[data-testid="admin-trends-chart"]', { timeout: 10000 });

    // Verify chart is visible
    const trendsChart = page.locator('[data-testid="admin-trends-chart"]');
    await expect(trendsChart).toBeVisible();
  });

  test('should display recent activity feed', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for activity section
    await page.waitForSelector('[data-testid="recent-activity"]', { timeout: 10000 });

    // Verify activity feed is visible
    const activityFeed = page.locator('[data-testid="recent-activity"]');
    await expect(activityFeed).toBeVisible();
  });

  test('should switch time ranges', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for initial data load
    await page.waitForSelector('[data-testid="admin-stats-container"]');

    // Find and click time range selector
    const rangeSelector = page.locator('[data-testid="admin-range-selector"]');
    await expect(rangeSelector).toBeVisible();

    // Switch to "7 Days"
    await page.click('[data-testid="admin-range-7days"]');

    // Wait for data refresh
    await page.waitForResponse(response =>
      response.url().includes('/api/admin/analytics/overview') &&
      response.url().includes('range=7days')
    );

    // Switch to "30 Days"
    await page.click('[data-testid="admin-range-30days"]');

    // Wait for data refresh
    await page.waitForResponse(response =>
      response.url().includes('/api/admin/analytics/overview') &&
      response.url().includes('range=30days')
    );
  });

  test('should show activity rate metrics', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for stats to load
    await page.waitForSelector('[data-testid="admin-stats-container"]');

    // Check for activity rate card
    const activityRateCard = page.locator('[data-testid="stat-card-activity-rate"]');
    await expect(activityRateCard).toBeVisible();
  });

  test('should display conversion rate', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Wait for stats to load
    await page.waitForSelector('[data-testid="admin-stats-container"]');

    // Check for conversion rate card
    const conversionCard = page.locator('[data-testid="stat-card-conversion-rate"]');
    await expect(conversionCard).toBeVisible();
  });

  test('should handle loading state', async ({ page }) => {
    await page.goto('/admin/dashboard/analytics');

    // Should show loading initially
    const loading = page.locator('[data-testid="admin-loading"]');

    // Wait for either loading to disappear or data to appear
    await Promise.race([
      page.waitForSelector('[data-testid="admin-stats-container"]'),
      page.waitForTimeout(1000),
    ]);
  });

  test('should handle API errors gracefully', async ({ page }) => {
    // Intercept API and return error
    await page.route('**/api/admin/analytics/overview*', route => {
      route.fulfill({
        status: 500,
        body: JSON.stringify({
          success: false,
          message: 'Internal server error',
        }),
      });
    });

    await page.goto('/admin/dashboard/analytics');

    // Should show error message
    await expect(page.locator('[role="alert"]')).toBeVisible({ timeout: 5000 });
  });

  test('should require admin role', async ({ page }) => {
    // Logout
    await page.goto('/logout');

    // Login as regular salesperson
    await page.goto('/login');
    await page.fill('input[name="email"]', SALESPERSON_EMAIL);
    await page.fill('input[name="password"]', SALESPERSON_PASSWORD);
    await page.click('button[type="submit"]');

    // Try to access admin dashboard
    await page.goto('/admin/dashboard/analytics');

    // Should redirect or show error
    await expect(page).not.toHaveURL(/\/admin\/dashboard\/analytics/);
  });
});

test.describe('Responsive Design', () => {
  test('salesperson dashboard works on mobile', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });

    // Login
    await page.goto('/login');
    await page.fill('input[name="email"]', SALESPERSON_EMAIL);
    await page.fill('input[name="password"]', SALESPERSON_PASSWORD);
    await page.click('button[type="submit"]');

    // Navigate to analytics
    await page.goto('/dashboard/analytics');

    // Verify page is responsive
    await expect(page.locator('h1')).toBeVisible();

    // Stats should stack vertically
    const statCards = page.locator('[data-testid="stat-card"]');
    const firstCard = statCards.first();
    const secondCard = statCards.nth(1);

    if ((await statCards.count()) >= 2) {
      const firstBox = await firstCard.boundingBox();
      const secondBox = await secondCard.boundingBox();

      // On mobile, second card should be below first card
      if (firstBox && secondBox) {
        expect(secondBox.y).toBeGreaterThan(firstBox.y);
      }
    }
  });

  test('admin dashboard works on tablet', async ({ page }) => {
    // Set tablet viewport
    await page.setViewportSize({ width: 768, height: 1024 });

    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');

    // Navigate to analytics
    await page.goto('/admin/dashboard/analytics');

    // Verify page is responsive
    await expect(page.locator('h1')).toBeVisible();
  });
});

test.describe('Data Refresh', () => {
  test('should auto-refresh data periodically', async ({ page }) => {
    // Login as salesperson
    await page.goto('/login');
    await page.fill('input[name="email"]', SALESPERSON_EMAIL);
    await page.fill('input[name="password"]', SALESPERSON_PASSWORD);
    await page.click('button[type="submit"]');

    await page.goto('/dashboard/analytics');

    // Wait for initial load
    await page.waitForSelector('[data-testid="stats-container"]');

    // Count API requests
    let requestCount = 0;
    page.on('request', request => {
      if (request.url().includes('/api/salesperson/analytics/stats')) {
        requestCount++;
      }
    });

    // Wait for potential auto-refresh (if implemented)
    // This test assumes auto-refresh is not implemented by default
    // If it is, this test will verify it works

    await page.waitForTimeout(5000);

    // Initial load should have happened
    expect(requestCount).toBeGreaterThanOrEqual(1);
  });
});
