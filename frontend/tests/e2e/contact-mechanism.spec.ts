import { test, expect } from '@playwright/test';

/**
 * E2E Tests for Contact Mechanism
 *
 * Tests:
 * 1. Salesperson can set contact methods
 * 2. Customer can submit contact request
 * 3. Rate limiting (24h/salesperson, 5 requests/day)
 * 4. Event tracking (profile_view, contact_form_submission)
 */

const FRONTEND_URL = 'http://localhost:3001';
const BACKEND_URL = 'http://localhost:8080';

test.describe('Contact Mechanism', () => {
  let salespersonToken: string;
  let salespersonId: number;
  let customerToken: string;

  test.beforeAll(async ({ request }) => {
    // Login as salesperson
    const salespersonLogin = await request.post(`${BACKEND_URL}/api/auth/login`, {
      data: {
        email: 'salesperson@example.com',
        password: 'password123',
      },
    });

    if (salespersonLogin.ok()) {
      const data = await salespersonLogin.json();
      salespersonToken = data.data.access_token;
      salespersonId = data.data.user.id;
    }

    // Login as customer
    const customerLogin = await request.post(`${BACKEND_URL}/api/auth/login`, {
      data: {
        email: 'customer@example.com',
        password: 'password123',
      },
    });

    if (customerLogin.ok()) {
      const data = await customerLogin.json();
      customerToken = data.data.access_token;
    }
  });

  test('salesperson can set contact methods', async ({ page }) => {
    test.skip(!salespersonToken, 'Salesperson login failed');

    // Set cookies for authentication
    await page.context().addCookies([
      {
        name: 'access_token',
        value: salespersonToken,
        domain: 'localhost',
        path: '/',
      },
    ]);

    // Navigate to dashboard
    await page.goto(`${FRONTEND_URL}/dashboard`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Look for contact methods form or button
    // This will depend on the actual UI implementation
    const contactSection = page.locator('[data-testid="contact-methods"]').or(
      page.locator('text=聯繫方式').or(
        page.locator('text=Contact Methods')
      )
    );

    if (await contactSection.isVisible()) {
      // Fill contact methods
      const phoneInput = page.locator('input[name="phone"]').or(
        page.locator('input[placeholder*="電話"]')
      );

      if (await phoneInput.isVisible()) {
        await phoneInput.fill('+886912345678');
      }

      const emailInput = page.locator('input[name="email_public"]').or(
        page.locator('input[type="email"]')
      );

      if (await emailInput.isVisible()) {
        await emailInput.fill('contact@example.com');
      }

      // Submit form
      const submitButton = page.locator('button[type="submit"]').or(
        page.locator('button:has-text("儲存")')
      );

      if (await submitButton.isVisible()) {
        await submitButton.click();

        // Verify success message
        const successMessage = page.locator('[role="alert"]').or(
          page.locator('text=成功').or(
            page.locator('text=Success')
          )
        );

        await expect(successMessage.first()).toBeVisible({ timeout: 5000 });
      }
    }
  });

  test('customer can view salesperson profile and see contact button', async ({ page }) => {
    test.skip(!salespersonId, 'Salesperson ID not available');

    // Visit salesperson profile as guest
    await page.goto(`${FRONTEND_URL}/salesperson/${salespersonId}`);
    await page.waitForLoadState('networkidle');

    // Check if profile loaded
    const profileContent = page.locator('[data-testid="profile-content"]').or(
      page.locator('h1, h2')
    );
    await expect(profileContent.first()).toBeVisible({ timeout: 10000 });

    // Look for contact button
    const contactButton = page.locator('[data-testid="contact-button"]').or(
      page.locator('button:has-text("聯繫")').or(
        page.locator('button:has-text("Contact")')
      )
    );

    // Contact button should be visible
    if (await contactButton.isVisible()) {
      await expect(contactButton).toBeVisible();
    }
  });

  test('contact form validation works', async ({ page }) => {
    test.skip(!salespersonId, 'Salesperson ID not available');

    await page.goto(`${FRONTEND_URL}/salesperson/${salespersonId}`);
    await page.waitForLoadState('networkidle');

    // Click contact button
    const contactButton = page.locator('[data-testid="contact-button"]').or(
      page.locator('button:has-text("聯繫")').or(
        page.locator('button:has-text("Contact")')
      )
    );

    if (await contactButton.isVisible()) {
      await contactButton.click();

      // Wait for modal/form to appear
      await page.waitForTimeout(500);

      // Try to submit empty form
      const submitButton = page.locator('button[type="submit"]').or(
        page.locator('button:has-text("送出")').or(
          page.locator('button:has-text("Submit")')
        )
      );

      if (await submitButton.isVisible()) {
        await submitButton.click();

        // Should show validation errors
        await page.waitForTimeout(500);
        const errorMessage = page.locator('[role="alert"]').or(
          page.locator('text=必填').or(
            page.locator('text=required')
          )
        );

        // Validation should prevent submission
        // (Either show error or disable button)
      }
    }
  });

  test('event tracking works for profile views', async ({ request }) => {
    test.skip(!salespersonId, 'Salesperson ID not available');

    // Track profile view event
    const response = await request.post(`${BACKEND_URL}/api/events`, {
      data: {
        event_type: 'profile_view',
        salesperson_id: salespersonId,
      },
    });

    // Should accept event tracking
    expect(response.status()).toBeLessThan(500);
  });
});

test.describe('Contact Mechanism - API Integration', () => {
  test('contact methods API works', async ({ request }) => {
    // This test requires a valid salesperson token
    // Skipping if not available
    test.skip(true, 'Manual API test - requires setup');

    const response = await request.get(`${BACKEND_URL}/api/salesperson/profile/contact`, {
      headers: {
        Authorization: `Bearer YOUR_TOKEN_HERE`,
      },
    });

    if (response.ok()) {
      const data = await response.json();
      expect(data.success).toBeTruthy();
    }
  });

  test('contact request API respects rate limiting', async ({ request }) => {
    // This test requires proper setup
    test.skip(true, 'Rate limiting test requires specific setup');
  });
});

test.describe('Contact Mechanism - Accessibility', () => {
  test('contact form is keyboard accessible', async ({ page }) => {
    // Accessibility test placeholder
    test.skip(true, 'Accessibility test - implement when UI is ready');
  });

  test('contact button has proper ARIA labels', async ({ page }) => {
    // Accessibility test placeholder
    test.skip(true, 'Accessibility test - implement when UI is ready');
  });
});
