const { chromium } = require('playwright');

// Target configuration
const TARGET_URL = 'http://localhost:3001';
const TEST_CREDENTIALS = {
  email: 'test@example.com',
  password: 'password123'
};

(async () => {
  const browser = await chromium.launch({
    headless: false,
    slowMo: 100
  });

  const context = await browser.newContext({
    viewport: { width: 1920, height: 1080 }
  });

  const page = await context.newPage();

  console.log('🚀 Starting dashboard pages test...\n');

  try {
    // Step 1: Check if authentication is needed
    console.log('📍 Navigating to /dashboard/experiences...');
    await page.goto(`${TARGET_URL}/dashboard/experiences`, {
      waitUntil: 'networkidle',
      timeout: 15000
    });

    const currentUrl = page.url();
    console.log('Current URL:', currentUrl);

    // Step 2: Login if redirected to login page
    if (currentUrl.includes('/login') || currentUrl.includes('/auth')) {
      console.log('🔐 Authentication required, logging in...');

      // Fill login form
      await page.fill('input[type="email"], input[name="email"]', TEST_CREDENTIALS.email);
      await page.fill('input[type="password"], input[name="password"]', TEST_CREDENTIALS.password);

      // Click submit button
      await page.click('button[type="submit"]');

      // Wait for redirect after login
      await page.waitForURL('**/dashboard/**', { timeout: 10000 });
      console.log('✅ Login successful\n');
    } else {
      console.log('✅ Already authenticated\n');
    }

    // Test 1: Experiences Page
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('TEST 1: /dashboard/experiences');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    await page.goto(`${TARGET_URL}/dashboard/experiences`, {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    // Check page title
    const experiencesTitle = await page.title();
    console.log('📄 Page Title:', experiencesTitle);

    // Check for main content elements
    const hasExperiencesHeading = await page.locator('h1, h2').filter({ hasText: /經驗|Experience/i }).count() > 0;
    console.log('✓ Experiences heading found:', hasExperiencesHeading);

    // Check for experiences list or empty state
    const hasExperiencesList = await page.locator('[data-testid*="experience"], .experience-card, .experience-item').count() > 0;
    const hasEmptyState = await page.locator('text=/沒有|尚未|No experiences/i').count() > 0;
    console.log('✓ Experiences list or empty state:', hasExperiencesList || hasEmptyState);

    // Check for approval status badges
    const statusBadges = await page.locator('.badge, [class*="badge"], [class*="status"]').all();
    console.log('✓ Status badges found:', statusBadges.length);

    // List any visible status text
    const statusTexts = await page.locator('text=/已驗證|審核中|已拒絕|approved|pending|rejected/i').allTextContents();
    if (statusTexts.length > 0) {
      console.log('  Status indicators:', statusTexts.slice(0, 5).join(', '));
    }

    // Take screenshot
    await page.screenshot({
      path: '/tmp/experiences-page.png',
      fullPage: true
    });
    console.log('📸 Screenshot saved: /tmp/experiences-page.png\n');

    // Test 2: Certifications Page
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('TEST 2: /dashboard/certifications');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    await page.goto(`${TARGET_URL}/dashboard/certifications`, {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    // Check page title
    const certificationsTitle = await page.title();
    console.log('📄 Page Title:', certificationsTitle);

    // Check for main content elements
    const hasCertificationsHeading = await page.locator('h1, h2').filter({ hasText: /證照|Certification/i }).count() > 0;
    console.log('✓ Certifications heading found:', hasCertificationsHeading);

    // Check for certifications list or empty state
    const hasCertificationsList = await page.locator('[data-testid*="certification"], .certification-card, .certification-item').count() > 0;
    const hasCertEmptyState = await page.locator('text=/沒有|尚未|No certifications/i').count() > 0;
    console.log('✓ Certifications list or empty state:', hasCertificationsList || hasCertEmptyState);

    // Check for file upload capability
    const hasUploadButton = await page.locator('button').filter({ hasText: /上傳|Upload|新增|Add/i }).count() > 0;
    const hasFileInput = await page.locator('input[type="file"]').count() > 0;
    console.log('✓ Upload/Add button found:', hasUploadButton);
    console.log('✓ File input capability:', hasFileInput || hasUploadButton);

    // Check for certification images/files
    const certImages = await page.locator('img[src*="certification"], img[alt*="證照"], img[alt*="certificate"]').count();
    console.log('✓ Certificate images displayed:', certImages);

    // Take screenshot
    await page.screenshot({
      path: '/tmp/certifications-page.png',
      fullPage: true
    });
    console.log('📸 Screenshot saved: /tmp/certifications-page.png\n');

    // Test 3: Approval Status Page
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('TEST 3: /dashboard/approval-status');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    await page.goto(`${TARGET_URL}/dashboard/approval-status`, {
      waitUntil: 'networkidle',
      timeout: 10000
    });

    // Check page title
    const approvalTitle = await page.title();
    console.log('📄 Page Title:', approvalTitle);

    // Check for main content elements
    const hasApprovalHeading = await page.locator('h1, h2').filter({ hasText: /審核|Approval|狀態|Status/i }).count() > 0;
    console.log('✓ Approval status heading found:', hasApprovalHeading);

    // Check for aggregated status sections
    const hasProfileStatus = await page.locator('text=/個人資料|Profile|基本資料/i').count() > 0;
    const hasCompanyStatus = await page.locator('text=/公司|Company/i').count() > 0;
    const hasExperiencesStatus = await page.locator('text=/經驗|Experience/i').count() > 0;
    const hasCertificationsStatus = await page.locator('text=/證照|Certification/i').count() > 0;

    console.log('✓ Profile status section:', hasProfileStatus);
    console.log('✓ Company status section:', hasCompanyStatus);
    console.log('✓ Experiences status section:', hasExperiencesStatus);
    console.log('✓ Certifications status section:', hasCertificationsStatus);

    // Count status indicators
    const allStatusBadges = await page.locator('.badge, [class*="badge"], [class*="status"]').count();
    console.log('✓ Total status badges:', allStatusBadges);

    // Check for approval status values
    const approvalStatusTexts = await page.locator('text=/已驗證|審核中|已拒絕|待審核|approved|pending|rejected/i').allTextContents();
    if (approvalStatusTexts.length > 0) {
      console.log('  Approval statuses found:', approvalStatusTexts.length);
      console.log('  Sample statuses:', approvalStatusTexts.slice(0, 5).join(', '));
    }

    // Take screenshot
    await page.screenshot({
      path: '/tmp/approval-status-page.png',
      fullPage: true
    });
    console.log('📸 Screenshot saved: /tmp/approval-status-page.png\n');

    // Summary
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('TEST SUMMARY');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('✅ All 3 pages loaded successfully');
    console.log('✅ Authentication handled correctly');
    console.log('✅ Screenshots saved to /tmp/');
    console.log('\nScreenshots:');
    console.log('  - /tmp/experiences-page.png');
    console.log('  - /tmp/certifications-page.png');
    console.log('  - /tmp/approval-status-page.png');

  } catch (error) {
    console.error('\n❌ TEST FAILED');
    console.error('Error:', error.message);

    // Take error screenshot
    try {
      await page.screenshot({
        path: '/tmp/error-screenshot.png',
        fullPage: true
      });
      console.log('📸 Error screenshot saved: /tmp/error-screenshot.png');
    } catch (screenshotError) {
      console.error('Could not take error screenshot');
    }
  } finally {
    await browser.close();
    console.log('\n🏁 Test execution completed');
  }
})();
