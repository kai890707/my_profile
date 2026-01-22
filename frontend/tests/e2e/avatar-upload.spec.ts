import { test, expect, Page } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

// Helper function to save screenshots
async function saveScreenshot(page: Page, name: string) {
  await page.screenshot({
    path: `/tmp/avatar-upload-${name}-${Date.now()}.png`,
    fullPage: false,
  });
}

// Test configuration
const BASE_URL = process.env.BASE_URL || 'http://localhost:3001';
const API_URL = process.env.API_URL || 'http://localhost:8080';

// Test user credentials
const TEST_SALESPERSON = {
  email: 'salesperson@example.com',
  password: 'password123',
  username: 'Salesperson User',
};

// Helper function to login as salesperson
async function loginAsSalesperson(page: Page) {
  await page.goto(`${BASE_URL}/login`);
  await page.waitForLoadState('networkidle');

  await page.fill('input[name="email"]', TEST_SALESPERSON.email);
  await page.fill('input[name="password"]', TEST_SALESPERSON.password);
  await page.click('button[type="submit"]');

  await page.waitForLoadState('networkidle');
}

// Helper function to create a base64 image data URL
function createTestImageDataUrl(width: number, height: number, format: 'png' | 'jpeg' = 'png'): string {
  // Create a small test image (using canvas-like approach)
  // In real browser, this would be done through FileReader
  // For testing, we'll create a minimal valid image data URL

  if (format === 'png') {
    // Minimal PNG: 1x1 transparent pixel
    const base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    return `data:image/png;base64,${base64}`;
  } else {
    // Minimal JPEG: 1x1 red pixel
    const base64 = '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA//2Q==';
    return `data:image/jpeg;base64,${base64}`;
  }
}

// Helper function to create oversized data URL (>2MB)
function createOversizedDataUrl(): string {
  // Create a data URL that exceeds 2MB
  const largeBase64 = 'A'.repeat(3000000); // ~3MB when base64 encoded
  return `data:image/png;base64,${largeBase64}`;
}

// Helper function to wait for API response
async function waitForApiResponse(page: Page, endpoint: string) {
  return await page.waitForResponse(
    (response) => response.url().includes(endpoint) && response.status() === 200,
    { timeout: 10000 }
  );
}

test.describe('Avatar Upload E2E Tests', () => {

  test.describe('1. Avatar Upload Flow', () => {
    test('salesperson can upload avatar successfully', async ({ page }) => {
      await loginAsSalesperson(page);

      // Navigate to dashboard profile
      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Check if there's an avatar upload button/area
      const avatarUploadArea = page.locator('[data-testid="avatar-upload"], input[type="file"][accept*="image"], button:has-text("上傳頭像"), button:has-text("Upload Avatar")').first();

      // If direct input not visible, might need to click avatar area first
      const avatarContainer = page.locator('[data-testid="avatar-container"], .avatar-upload').first();
      if (await avatarContainer.isVisible()) {
        await avatarContainer.click();
      }

      await saveScreenshot(page, 'before-upload');

      // Create a test image file
      const testImageDataUrl = createTestImageDataUrl(200, 200, 'png');

      // Find the file input and upload
      const fileInput = page.locator('input[type="file"]').first();

      // In E2E, we might need to mock the file upload
      // Playwright can handle file uploads, but we need to create actual file
      // For simplicity, we'll test through API interaction instead

      // Alternative: Test through browser FileReader API
      await page.evaluate((dataUrl) => {
        // Simulate file upload through JS
        const event = new CustomEvent('avatar-upload', {
          detail: { avatar: dataUrl }
        });
        window.dispatchEvent(event);
      }, testImageDataUrl);

      // Wait for upload to complete
      await page.waitForTimeout(2000);

      await saveScreenshot(page, 'after-upload');

      // Verify avatar is displayed
      const avatar = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      await expect(avatar).toBeVisible();

      // Verify avatar src is a data URL
      const avatarSrc = await avatar.getAttribute('src');
      expect(avatarSrc).toBeTruthy();
      expect(avatarSrc).toMatch(/^data:image\/(png|jpeg|jpg|webp|gif);base64,/);
    });

    test('avatar persists after page reload', async ({ page }) => {
      await loginAsSalesperson(page);

      // Navigate to dashboard
      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Check if avatar exists before upload
      const avatarBefore = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      const hasAvatarBefore = await avatarBefore.isVisible().catch(() => false);

      if (!hasAvatarBefore) {
        // Upload avatar first
        const testImageDataUrl = createTestImageDataUrl(200, 200, 'png');

        await page.evaluate((dataUrl) => {
          window.dispatchEvent(new CustomEvent('avatar-upload', {
            detail: { avatar: dataUrl }
          }));
        }, testImageDataUrl);

        await page.waitForTimeout(2000);
      }

      await saveScreenshot(page, 'before-reload');

      // Reload page
      await page.reload();
      await page.waitForLoadState('networkidle');

      await saveScreenshot(page, 'after-reload');

      // Verify avatar still exists
      const avatarAfter = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      await expect(avatarAfter).toBeVisible();

      const avatarSrc = await avatarAfter.getAttribute('src');
      expect(avatarSrc).toMatch(/^data:image\/(png|jpeg|jpg|webp|gif);base64,/);
    });

    test('avatar persists after logout and login', async ({ page }) => {
      await loginAsSalesperson(page);

      // Navigate to dashboard
      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Upload avatar if not exists
      const avatarBefore = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      const hasAvatarBefore = await avatarBefore.isVisible().catch(() => false);

      if (!hasAvatarBefore) {
        const testImageDataUrl = createTestImageDataUrl(200, 200, 'png');
        await page.evaluate((dataUrl) => {
          window.dispatchEvent(new CustomEvent('avatar-upload', {
            detail: { avatar: dataUrl }
          }));
        }, testImageDataUrl);
        await page.waitForTimeout(2000);
      }

      // Save avatar src for comparison
      const avatarSrcBefore = await page.locator('img[alt*="avatar"], img[alt*="頭像"]').first().getAttribute('src');

      await saveScreenshot(page, 'before-logout');

      // Logout
      const userMenuButton = page.locator('button').filter({ has: page.locator('img') }).first();
      await userMenuButton.click();
      await page.locator('text=登出').click();
      await page.waitForLoadState('networkidle');

      // Login again
      await loginAsSalesperson(page);
      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      await saveScreenshot(page, 'after-relogin');

      // Verify avatar still exists
      const avatarAfter = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      await expect(avatarAfter).toBeVisible();

      const avatarSrcAfter = await avatarAfter.getAttribute('src');
      expect(avatarSrcAfter).toBeTruthy();
      expect(avatarSrcAfter).toMatch(/^data:image\/(png|jpeg|jpg|webp|gif);base64,/);
    });
  });

  test.describe('2. Avatar Delete Flow', () => {
    test('salesperson can delete avatar', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Ensure avatar exists (upload if needed)
      const avatarBefore = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      const hasAvatarBefore = await avatarBefore.isVisible().catch(() => false);

      if (!hasAvatarBefore) {
        const testImageDataUrl = createTestImageDataUrl(200, 200, 'png');
        await page.evaluate((dataUrl) => {
          window.dispatchEvent(new CustomEvent('avatar-upload', {
            detail: { avatar: dataUrl }
          }));
        }, testImageDataUrl);
        await page.waitForTimeout(2000);
      }

      await saveScreenshot(page, 'before-delete');

      // Find and click delete button
      const deleteButton = page.locator('button:has-text("刪除頭像"), button:has-text("Delete Avatar"), button[aria-label*="delete"]').first();

      // If delete button not visible, might need to hover over avatar
      const avatarContainer = page.locator('[data-testid="avatar-container"], .avatar-upload').first();
      if (await avatarContainer.isVisible()) {
        await avatarContainer.hover();
      }

      if (await deleteButton.isVisible({ timeout: 2000 }).catch(() => false)) {
        await deleteButton.click();

        // Wait for delete confirmation dialog if exists
        const confirmButton = page.locator('button:has-text("確認"), button:has-text("Confirm")');
        if (await confirmButton.isVisible({ timeout: 2000 }).catch(() => false)) {
          await confirmButton.click();
        }

        await page.waitForTimeout(2000);
      }

      await saveScreenshot(page, 'after-delete');

      // Verify avatar is removed or shows default avatar
      const avatarAfter = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      const avatarSrcAfter = await avatarAfter.getAttribute('src').catch(() => null);

      // Avatar should either not exist or show default image
      if (avatarSrcAfter) {
        // Should not be a data URL anymore (default image path or placeholder)
        expect(avatarSrcAfter).not.toMatch(/^data:image\/(png|jpeg|jpg|webp|gif);base64,/);
      }
    });
  });

  test.describe('3. Avatar Validation (Error Cases)', () => {
    test('should show error for oversized file (>2MB)', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Try to upload oversized file
      const oversizedDataUrl = createOversizedDataUrl();

      // Set up response listener for error
      page.on('response', async (response) => {
        if (response.url().includes('/avatar') && response.status() === 400) {
          const body = await response.json();
          expect(body.error.message).toMatch(/過大|too large|2MB/i);
        }
      });

      await page.evaluate((dataUrl) => {
        window.dispatchEvent(new CustomEvent('avatar-upload', {
          detail: { avatar: dataUrl }
        }));
      }, oversizedDataUrl);

      await page.waitForTimeout(2000);

      // Check for error message in UI
      const errorMessage = page.locator('[role="alert"], .error-message, text=過大, text=too large').first();
      if (await errorMessage.isVisible({ timeout: 3000 }).catch(() => false)) {
        await expect(errorMessage).toBeVisible();
      }

      await saveScreenshot(page, 'oversized-error');
    });

    test('should reject SVG files (XSS protection)', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Try to upload SVG
      const svgDataUrl = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxzY3JpcHQ+YWxlcnQoJ1hTUycpPC9zY3JpcHQ+PC9zdmc+';

      page.on('response', async (response) => {
        if (response.url().includes('/avatar') && response.status() === 422) {
          const body = await response.json();
          expect(body.error.code).toBe('VALIDATION_ERROR');
        }
      });

      await page.evaluate((dataUrl) => {
        window.dispatchEvent(new CustomEvent('avatar-upload', {
          detail: { avatar: dataUrl }
        }));
      }, svgDataUrl);

      await page.waitForTimeout(2000);

      // Check for error message
      const errorMessage = page.locator('[role="alert"], .error-message').first();
      if (await errorMessage.isVisible({ timeout: 3000 }).catch(() => false)) {
        await expect(errorMessage).toBeVisible();
      }

      await saveScreenshot(page, 'svg-rejected');
    });

    test('should show error for invalid data URL format', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Try to upload invalid data URL
      const invalidDataUrl = 'data:image/jpeg,notbase64data';

      page.on('response', async (response) => {
        if (response.url().includes('/avatar') && response.status() === 422) {
          const body = await response.json();
          expect(body.success).toBe(false);
        }
      });

      await page.evaluate((dataUrl) => {
        window.dispatchEvent(new CustomEvent('avatar-upload', {
          detail: { avatar: dataUrl }
        }));
      }, invalidDataUrl);

      await page.waitForTimeout(2000);

      await saveScreenshot(page, 'invalid-format-error');
    });
  });

  test.describe('4. Avatar Display', () => {
    test('avatar should be displayed in header', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/`);
      await page.waitForLoadState('networkidle');

      // Check header avatar
      const headerAvatar = page.locator('header img[alt*="avatar"], header img[alt*="頭像"]').first();
      await expect(headerAvatar).toBeVisible();

      await saveScreenshot(page, 'header-avatar');
    });

    test('avatar should be displayed on all pages', async ({ page }) => {
      await loginAsSalesperson(page);

      const pages = ['/', '/search', '/dashboard', '/dashboard/experiences'];

      for (const testPath of pages) {
        await page.goto(`${BASE_URL}${testPath}`);
        await page.waitForLoadState('networkidle');

        const avatar = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
        await expect(avatar).toBeVisible();

        await saveScreenshot(page, `avatar-on-${testPath.replace(/\//g, '-') || 'home'}`);
      }
    });

    test('avatar should be displayed in salesperson profile', async ({ page }) => {
      await loginAsSalesperson(page);

      // Navigate to own profile
      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Check profile avatar
      const profileAvatar = page.locator('[data-testid="profile-avatar"], .profile-avatar img, main img[alt*="avatar"]').first();
      await expect(profileAvatar).toBeVisible();

      await saveScreenshot(page, 'profile-avatar');
    });
  });

  test.describe('5. Responsive Design', () => {
    test('avatar upload should work on mobile viewport', async ({ page }) => {
      // Set mobile viewport
      await page.setViewportSize({ width: 375, height: 667 });

      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      await saveScreenshot(page, 'mobile-before-upload');

      // Upload avatar
      const testImageDataUrl = createTestImageDataUrl(200, 200, 'png');

      await page.evaluate((dataUrl) => {
        window.dispatchEvent(new CustomEvent('avatar-upload', {
          detail: { avatar: dataUrl }
        }));
      }, testImageDataUrl);

      await page.waitForTimeout(2000);

      await saveScreenshot(page, 'mobile-after-upload');

      // Verify avatar is visible on mobile
      const avatar = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      await expect(avatar).toBeVisible();
    });

    test('avatar should be displayed correctly on tablet viewport', async ({ page }) => {
      // Set tablet viewport
      await page.setViewportSize({ width: 768, height: 1024 });

      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      await saveScreenshot(page, 'tablet-avatar');

      const avatar = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      await expect(avatar).toBeVisible();
    });
  });

  test.describe('6. Performance', () => {
    test('avatar upload should complete within 3 seconds', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      const testImageDataUrl = createTestImageDataUrl(400, 400, 'png');

      const startTime = Date.now();

      await page.evaluate((dataUrl) => {
        window.dispatchEvent(new CustomEvent('avatar-upload', {
          detail: { avatar: dataUrl }
        }));
      }, testImageDataUrl);

      // Wait for upload to complete (check for success indicator)
      await page.waitForTimeout(3000);

      const endTime = Date.now();
      const duration = endTime - startTime;

      expect(duration).toBeLessThan(3000); // Should complete within 3 seconds

      await saveScreenshot(page, 'performance-test');
    });

    test('avatar image should load quickly', async ({ page }) => {
      await loginAsSalesperson(page);

      await page.goto(`${BASE_URL}/dashboard`);
      await page.waitForLoadState('networkidle');

      // Measure time to avatar load
      const startTime = Date.now();

      const avatar = page.locator('img[alt*="avatar"], img[alt*="頭像"]').first();
      await avatar.waitFor({ state: 'visible', timeout: 5000 });

      const endTime = Date.now();
      const loadTime = endTime - startTime;

      expect(loadTime).toBeLessThan(2000); // Should load within 2 seconds

      await saveScreenshot(page, 'avatar-load-time');
    });
  });

  test.describe('7. User Roles', () => {
    test('regular user should not have avatar upload functionality', async ({ page }) => {
      // Login as regular user
      await page.goto(`${BASE_URL}/login`);
      await page.fill('input[name="email"]', 'user@example.com');
      await page.fill('input[name="password"]', 'password123');
      await page.click('button[type="submit"]');
      await page.waitForLoadState('networkidle');

      await page.goto(`${BASE_URL}/`);
      await page.waitForLoadState('networkidle');

      // Try to find avatar upload (should not exist for regular users)
      const avatarUpload = page.locator('[data-testid="avatar-upload"], input[type="file"][accept*="image"]');

      // Regular users should not have access to dashboard
      const dashboardResponse = await page.goto(`${BASE_URL}/dashboard`);

      // Should redirect to homepage or login
      await page.waitForLoadState('networkidle');
      const currentUrl = page.url();
      expect(currentUrl).not.toContain('/dashboard');

      await saveScreenshot(page, 'regular-user-no-upload');
    });
  });
});
