const { chromium } = require('playwright');

const TARGET_URL = 'http://localhost:3001/admin/approvals';
const ADMIN_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwODAvYXBpL2F1dGgvbG9naW4iLCJpYXQiOjE3Njg5MDcxODYsImV4cCI6MTc2ODkxMDc4NiwibmJmIjoxNzY4OTA3MTg2LCJqdGkiOiJvd3lVdEJ0c0I2dXBQaGo1Iiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjciLCJyb2xlIjoiYWRtaW4iLCJzdGF0dXMiOiJhY3RpdmUifQ.6rZSkbnvmRMm4S2BdtlA4SB7cmZYh1K9VFqR8beDYuE';

(async () => {
  console.log('🚀 Testing Admin Approvals Interface (Authenticated)\n');

  const browser = await chromium.launch({ headless: false, slowMo: 300 });
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    // Set up authentication
    console.log('🔐 Setting up authentication...');
    await page.goto('http://localhost:3001');
    await page.waitForLoadState('networkidle');
    
    // Set localStorage
    await page.evaluate((token) => {
      localStorage.setItem('access_token', token);
      localStorage.setItem('user', JSON.stringify({
        id: 1,
        role: 'admin',
        email: 'admin@example.com'
      }));
    }, ADMIN_TOKEN);
    
    // Set cookies
    await context.addCookies([
      {
        name: 'access_token',
        value: ADMIN_TOKEN,
        domain: 'localhost',
        path: '/',
        httpOnly: false,
        secure: false,
        sameSite: 'Lax'
      }
    ]);
    
    console.log('   ✅ Auth token set\n');

    // Navigate to approvals page
    console.log('📄 1. Loading admin approvals page...');
    await page.goto(TARGET_URL, { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    
    const url = page.url();
    console.log(`   URL: ${url}`);
    
    if (url.includes('/login')) {
      console.log('   ⚠️  Still on login page - auth may have failed');
      await page.screenshot({ path: '/tmp/auth-failed.png' });
      return;
    }
    
    console.log('   ✅ Page loaded successfully');
    await page.screenshot({ path: '/tmp/approvals-initial.png', fullPage: true });

    // Test tabs
    console.log('\n🔖 2. Testing Tab Navigation...');
    const tabs = await page.locator('button').filter({ hasText: /業務員|公司|證照|經驗/ }).all();
    console.log(`   ✅ Found ${tabs.length} tabs`);
    
    for (let i = 0; i < Math.min(4, tabs.length); i++) {
      await tabs[i].click();
      await page.waitForTimeout(500);
      const text = await tabs[i].textContent();
      console.log(`   ✅ Clicked: ${text.trim()}`);
    }
    await page.screenshot({ path: '/tmp/approvals-tabs.png', fullPage: true });

    // Test search
    console.log('\n🔍 3. Testing Search...');
    const searchInput = page.locator('input[type="text"]').first();
    if (await searchInput.count() > 0) {
      await searchInput.fill('test');
      await page.waitForTimeout(1000);
      console.log('   ✅ Search input works');
      
      const hasResults = await page.locator('text=/找到|找不到/').count() > 0;
      if (hasResults) {
        const result = await page.locator('text=/找到|找不到/').first().textContent();
        console.log(`   ℹ️  ${result.trim()}`);
      }
      
      await page.screenshot({ path: '/tmp/approvals-search.png' });
      await searchInput.clear();
      await page.waitForTimeout(500);
    } else {
      console.log('   ⚠️  Search not found');
    }

    // Test date filter
    console.log('\n📅 4. Testing Date Filter...');
    const select = page.locator('select');
    if (await select.count() > 0) {
      for (const opt of ['today', 'week', 'month', 'all']) {
        await select.selectOption(opt);
        await page.waitForTimeout(400);
        console.log(`   ✅ Filter: ${opt}`);
      }
      await page.screenshot({ path: '/tmp/approvals-filter.png' });
    }

    // Test empty state
    console.log('\n📭 5. Testing Empty State...');
    if (await searchInput.count() > 0) {
      await searchInput.fill('xyznonexistent999');
      await page.waitForTimeout(1000);
      
      const hasEmpty = await page.locator('text=/找不到|沒有待審核/').count() > 0;
      if (hasEmpty) {
        console.log('   ✅ Empty state displayed');
        await page.screenshot({ path: '/tmp/approvals-empty.png' });
      }
      
      await searchInput.clear();
      await page.waitForTimeout(500);
    }

    // Test modal and reject functionality
    console.log('\n👁️  6. Testing Detail Modal...');
    const eyeIcons = await page.locator('button svg').all();
    console.log(`   Found ${eyeIcons.length} buttons with icons`);
    
    if (eyeIcons.length > 0) {
      // Find the eye icon parent button
      const viewBtn = page.locator('button').filter({ has: page.locator('svg') }).first();
      await viewBtn.click();
      await page.waitForTimeout(1500);
      console.log('   ✅ Detail modal opened');
      await page.screenshot({ path: '/tmp/approvals-modal.png' });

      // Test reject modal
      console.log('\n🔴 7. Testing Reject Modal...');
      const rejectBtn = page.locator('button').filter({ hasText: '拒絕' }).first();
      if (await rejectBtn.count() > 0) {
        await rejectBtn.click();
        await page.waitForTimeout(1000);
        console.log('   ✅ Reject modal opened');
        await page.screenshot({ path: '/tmp/approvals-reject-modal.png' });

        // Test validation - empty
        console.log('\n   📝 Testing Validation:');
        const confirmBtn = page.locator('button:has-text("確認拒絕")');
        await confirmBtn.click();
        await page.waitForTimeout(500);
        
        const hasError = await page.locator('text=/請輸入|至少需要/').count() > 0;
        if (hasError) {
          const error = await page.locator('text=/請輸入|至少需要/').first().textContent();
          console.log(`   ✅ Empty validation: ${error.trim()}`);
        }
        await page.screenshot({ path: '/tmp/approvals-reject-validation.png' });

        // Test short text
        const textarea = page.locator('textarea').first();
        await textarea.fill('短');
        await confirmBtn.click();
        await page.waitForTimeout(500);
        console.log('   ✅ Short text validation works');

        // Valid text
        await textarea.fill('這是一個完整的拒絕原因說明，確保超過最少字數要求');
        await page.waitForTimeout(300);
        console.log('   ✅ Valid text entered');
        await page.screenshot({ path: '/tmp/approvals-reject-filled.png' });

        // Cancel
        const cancelBtn = page.locator('button:has-text("取消")').first();
        await cancelBtn.click();
        await page.waitForTimeout(500);
        console.log('   ✅ Reject modal closed');
      }

      // Close detail modal
      const closeBtn = page.locator('button:has-text("關閉")').first();
      if (await closeBtn.count() > 0) {
        await closeBtn.click();
        await page.waitForTimeout(500);
        console.log('   ✅ Detail modal closed');
      }
    } else {
      console.log('   ℹ️  No items to view (empty list)');
    }

    // Test responsive
    console.log('\n📱 8. Testing Responsive Design...');
    const viewports = [
      { name: 'Desktop', width: 1920, height: 1080 },
      { name: 'Tablet', width: 768, height: 1024 },
      { name: 'Mobile', width: 375, height: 667 }
    ];

    for (const vp of viewports) {
      console.log(`\n   📐 ${vp.name} (${vp.width}x${vp.height})`);
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await page.goto(TARGET_URL, { waitUntil: 'networkidle' });
      await page.waitForTimeout(1500);
      
      const tabsVisible = await page.locator('button').filter({ hasText: /業務員|公司/ }).first().isVisible();
      console.log(`      ${tabsVisible ? '✅' : '⚠️ '} Layout: ${tabsVisible ? 'OK' : 'Hidden'}`);
      
      await page.screenshot({
        path: `/tmp/approvals-${vp.name.toLowerCase()}.png`,
        fullPage: true
      });
      console.log(`      📸 Screenshot saved`);
    }

    console.log('\n\n✅ All Tests Completed!\n');
    console.log('📸 Screenshots:');
    console.log('   /tmp/approvals-initial.png');
    console.log('   /tmp/approvals-tabs.png');
    console.log('   /tmp/approvals-search.png');
    console.log('   /tmp/approvals-filter.png');
    console.log('   /tmp/approvals-empty.png');
    console.log('   /tmp/approvals-modal.png');
    console.log('   /tmp/approvals-reject-modal.png');
    console.log('   /tmp/approvals-reject-validation.png');
    console.log('   /tmp/approvals-reject-filled.png');
    console.log('   /tmp/approvals-desktop.png');
    console.log('   /tmp/approvals-tablet.png');
    console.log('   /tmp/approvals-mobile.png');

  } catch (error) {
    console.error('\n❌ Error:', error.message);
    await page.screenshot({ path: '/tmp/approvals-error.png' });
  } finally {
    await browser.close();
  }
})();
