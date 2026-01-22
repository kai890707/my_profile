const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false, slowMo: 500 });
  const page = await browser.newPage();

  try {
    console.log('Loading page...');
    await page.goto('http://localhost:3001/admin/approvals', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);

    const title = await page.title();
    const url = page.url();
    
    console.log('Title:', title);
    console.log('URL:', url);

    // Get page text content
    const text = await page.locator('body').textContent();
    console.log('\nPage content preview:');
    console.log(text.substring(0, 500));

    // Check for login page
    const hasLogin = await page.locator('text=/登入|Login/i').count() > 0;
    console.log('\nHas login form:', hasLogin);

    // Check for auth page
    const hasAuth = await page.locator('text=/認證|Authentication/i').count() > 0;
    console.log('Has auth page:', hasAuth);

    await page.screenshot({ path: '/tmp/page-state.png', fullPage: true });
    console.log('\n📸 Screenshot saved to: /tmp/page-state.png');

    console.log('\nPress Ctrl+C to close browser...');
    await page.waitForTimeout(30000);

  } catch (error) {
    console.error('Error:', error.message);
    await page.screenshot({ path: '/tmp/error-state.png' });
  } finally {
    await browser.close();
  }
})();
