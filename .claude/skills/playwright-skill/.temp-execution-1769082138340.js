const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false, slowMo: 500 });
  const page = await browser.newPage();

  try {
    console.log('Loading login page...');
    await page.goto('http://localhost:3001/login');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    console.log('URL:', page.url());
    
    const emailCount = await page.locator('input[type="email"]').count();
    const passCount = await page.locator('input[type="password"]').count();
    const btnCount = await page.locator('button[type="submit"]').count();
    
    console.log('Email inputs:', emailCount);
    console.log('Password inputs:', passCount);
    console.log('Submit buttons:', btnCount);
    
    if (emailCount > 0 && passCount > 0 && btnCount > 0) {
      console.log('\nFilling form...');
      await page.fill('input[type="email"]', 'admin@example.com');
      await page.fill('input[type="password"]', 'admin123');
      await page.screenshot({ path: '/tmp/before-submit.png' });
      
      console.log('Submitting...');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(3000);
      
      console.log('After submit URL:', page.url());
      await page.screenshot({ path: '/tmp/after-submit.png' });
      
      if (page.url().includes('/admin') || page.url().includes('/dashboard')) {
        console.log('✅ Login successful!');
      } else {
        console.log('⚠️  Still on login page');
      }
    }
    
    console.log('\nWait 20 seconds...');
    await page.waitForTimeout(20000);
    
  } catch (error) {
    console.error('Error:', error.message);
  } finally {
    await browser.close();
  }
})();
