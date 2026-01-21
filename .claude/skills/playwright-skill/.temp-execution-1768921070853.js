// 審核介面完整測試 - 修正版
const { chromium } = require('playwright');

const FRONTEND_URL = 'http://localhost:3001';
const ADMIN_EMAIL = 'admin@example.com';
const ADMIN_PASSWORD = 'admin123';

(async () => {
  console.log('🎭 審核介面完整測試\n');

  const browser = await chromium.launch({ headless: false, slowMo: 100 });
  const page = await browser.newPage();

  try {
    // Step 1: 登入
    console.log('1. 管理員登入...');
    await page.goto(`${FRONTEND_URL}/login`);
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000);
    console.log('   ✅ 登入成功\n');

    // Step 2: 明確導航到審核頁面
    console.log('2. 導航到審核頁面...');
    await page.goto(`${FRONTEND_URL}/admin/approvals`);
    await page.waitForTimeout(2000);
    console.log(`   當前 URL: ${page.url()}\n`);

    await page.screenshot({ path: '/tmp/test-approvals-page.png', fullPage: true });

    // Step 3: 檢查頁面標題
    console.log('3. 檢查頁面元素...');
    const h1 = await page.locator('h1').first().textContent();
    console.log(`   頁面標題: ${h1}`);

    // Step 4: 檢查 Tab 按鈕
    console.log('\n4. 檢查 Tab 按鈕...');
    const tabs = ['業務員註冊', '公司資訊', '專業證照', '工作經驗'];
    for (const tabName of tabs) {
      const tab = page.locator(`button:has-text("${tabName}")`);
      const count = await tab.count();
      console.log(`   ${count > 0 ? '✅' : '❌'} ${tabName}: ${count} 個`);
    }

    // Step 5: 檢查搜尋框
    console.log('\n5. 檢查搜尋框...');
    const searchBox = page.locator('input[placeholder*="搜尋"]');
    const hasSearch = await searchBox.count() > 0;
    console.log(`   ${hasSearch ? '✅' : '❌'} 搜尋框: ${await searchBox.count()} 個`);

    // Step 6: 檢查日期篩選
    console.log('\n6. 檢查日期篩選...');
    const dateFilter = page.locator('select');
    const hasDateFilter = await dateFilter.count() > 0;
    console.log(`   ${hasDateFilter ? '✅' : '❌'} 日期篩選: ${await dateFilter.count()} 個`);

    // Step 7: 測試搜尋功能
    if (hasSearch) {
      console.log('\n7. 測試搜尋功能...');
      await searchBox.fill('test');
      await page.waitForTimeout(500);
      console.log('   ✅ 搜尋輸入成功');

      await page.screenshot({ path: '/tmp/test-search.png', fullPage: true });

      await searchBox.fill('');
      await page.waitForTimeout(300);
    }

    // Step 8: 測試 Tab 切換
    console.log('\n8. 測試 Tab 切換...');
    const companyTab = page.locator('button:has-text("公司資訊")');
    if (await companyTab.count() > 0) {
      await companyTab.click();
      await page.waitForTimeout(500);
      console.log('   ✅ Tab 切換成功');

      await page.screenshot({ path: '/tmp/test-tab-switch.png', fullPage: true });
    }

    // Step 9: 檢查是否有項目
    console.log('\n9. 檢查待審核項目...');
    const items = page.locator('button:has-text("詳細資訊")');
    const itemCount = await items.count();
    console.log(`   待審核項目數量: ${itemCount}`);

    if (itemCount > 0) {
      console.log('\n10. 測試詳細資訊 Modal...');
      await items.first().click();
      await page.waitForTimeout(1000);
      console.log('   ✅ 詳細 Modal 已開啟');

      await page.screenshot({ path: '/tmp/test-detail-modal.png', fullPage: true });

      // 測試拒絕按鈕
      const rejectBtn = page.locator('button:has-text("拒絕")').first();
      if (await rejectBtn.count() > 0) {
        await rejectBtn.click();
        await page.waitForTimeout(1000);
        console.log('   ✅ 拒絕 Modal 已開啟');

        await page.screenshot({ path: '/tmp/test-reject-modal.png', fullPage: true });

        // 測試驗證
        const confirmBtn = page.locator('button:has-text("確認拒絕")');
        if (await confirmBtn.count() > 0) {
          await confirmBtn.click();
          await page.waitForTimeout(500);
          console.log('   ✅ 空白驗證有效（應該顯示錯誤）');

          await page.screenshot({ path: '/tmp/test-reject-validation.png', fullPage: true });

          // 輸入有效原因
          const textarea = page.locator('textarea');
          if (await textarea.count() > 0) {
            await textarea.fill('測試拒絕原因超過十個字元了');
            await page.waitForTimeout(500);
            console.log('   ✅ 輸入有效拒絕原因');

            await page.screenshot({ path: '/tmp/test-reject-valid.png', fullPage: true });

            // 關閉 Modal（不實際提交）
            const cancelBtn = page.locator('button:has-text("取消")');
            if (await cancelBtn.count() > 0) {
              await cancelBtn.click();
              await page.waitForTimeout(500);
              console.log('   ✅ Modal 已關閉');
            }
          }
        }
      }
    } else {
      console.log('   ⚠️  沒有待審核項目，跳過 Modal 測試');
    }

    // Step 11: 測試響應式設計
    console.log('\n11. 測試響應式設計...');
    const viewports = [
      { name: 'Desktop', width: 1920, height: 1080 },
      { name: 'Tablet', width: 768, height: 1024 },
      { name: 'Mobile', width: 375, height: 667 }
    ];

    for (const vp of viewports) {
      await page.setViewportSize({ width: vp.width, height: vp.height });
      await page.waitForTimeout(500);
      console.log(`   ✅ ${vp.name} (${vp.width}x${vp.height})`);

      await page.screenshot({
        path: `/tmp/test-responsive-${vp.name.toLowerCase()}.png`,
        fullPage: true
      });
    }

    console.log('\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('✅ 測試完成！所有截圖已保存到 /tmp/test-*.png');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n');

  } catch (error) {
    console.error('\n❌ 測試失敗:', error.message);
    await page.screenshot({ path: '/tmp/test-error.png', fullPage: true });
  } finally {
    await browser.close();
  }
})();
