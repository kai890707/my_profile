const { chromium } = require('playwright');

const TARGET_URL = 'http://localhost:3001/search';

(async () => {
  const browser = await chromium.launch({
    headless: false,
    slowMo: 300
  });

  const context = await browser.newContext({
    storageState: undefined // 清除 cookies 模擬未登入
  });

  const page = await context.newPage();

  console.log('='.repeat(70));
  console.log('🎯 測試業務員卡片點擊跳轉修復結果');
  console.log('='.repeat(70));

  try {
    console.log('\n📍 步驟 1: 導航到 Search 頁面...');
    await page.goto(TARGET_URL, { waitUntil: 'domcontentloaded', timeout: 15000 });
    console.log('✅ Search 頁面載入成功');

    // 等待業務員卡片載入
    await page.waitForTimeout(3000);

    console.log('\n📍 步驟 2: 獲取第一個業務員卡片資訊...');
    const firstCard = await page.locator('a[href^="/salesperson/"]').first();

    // 獲取卡片上顯示的業務員姓名
    const cardName = await firstCard.locator('h3').textContent();
    console.log(`   📋 卡片顯示姓名: "${cardName}"`);

    // 獲取卡片連結的 ID
    const href = await firstCard.getAttribute('href');
    const cardId = href.match(/\/salesperson\/(\d+)/)?.[1];
    console.log(`   🔗 卡片連結 ID: ${cardId}`);

    console.log('\n📍 步驟 3: 點擊卡片跳轉到詳情頁...');
    await firstCard.click();

    // 等待頁面跳轉完成
    await page.waitForURL(`**/salesperson/${cardId}`, { timeout: 10000 });
    console.log(`✅ 成功跳轉到 /salesperson/${cardId}`);

    // 等待詳情頁載入
    await page.waitForTimeout(2000);

    console.log('\n📍 步驟 4: 檢查詳情頁業務員姓名...');
    const detailPageName = await page.locator('h1').textContent();
    console.log(`   📋 詳情頁顯示姓名: "${detailPageName}"`);

    console.log('\n📍 步驟 5: 驗證姓名是否匹配...');
    const namesMatch = cardName.trim() === detailPageName.trim();

    if (namesMatch) {
      console.log(`✅ 姓名匹配成功！`);
      console.log(`   卡片: "${cardName.trim()}"`);
      console.log(`   詳情頁: "${detailPageName.trim()}"`);
    } else {
      console.log(`❌ 姓名不匹配！`);
      console.log(`   卡片顯示: "${cardName.trim()}"`);
      console.log(`   詳情頁顯示: "${detailPageName.trim()}"`);
    }

    console.log('\n📍 步驟 6: 測試多個業務員卡片...');

    // 返回 search 頁面
    await page.goto(TARGET_URL);
    await page.waitForTimeout(2000);

    const allCards = await page.locator('a[href^="/salesperson/"]').all();
    console.log(`   找到 ${allCards.length} 個業務員卡片`);

    let matchCount = 0;
    let mismatchCount = 0;
    const testResults = [];

    // 測試前 3 個卡片
    const cardsToTest = Math.min(3, allCards.length);

    for (let i = 0; i < cardsToTest; i++) {
      const card = allCards[i];
      const name = await card.locator('h3').textContent();
      const cardHref = await card.getAttribute('href');
      const id = cardHref.match(/\/salesperson\/(\d+)/)?.[1];

      // 點擊卡片
      await card.click();
      await page.waitForURL(`**/salesperson/${id}`, { timeout: 10000 });
      await page.waitForTimeout(1500);

      const detailName = await page.locator('h1').textContent();
      const match = name.trim() === detailName.trim();

      testResults.push({
        index: i + 1,
        id,
        cardName: name.trim(),
        detailName: detailName.trim(),
        match
      });

      if (match) {
        matchCount++;
        console.log(`   ✅ 測試 ${i + 1}: ID ${id} - 姓名匹配`);
      } else {
        mismatchCount++;
        console.log(`   ❌ 測試 ${i + 1}: ID ${id} - 姓名不匹配`);
        console.log(`      卡片: "${name.trim()}" vs 詳情頁: "${detailName.trim()}"`);
      }

      // 返回 search 頁面繼續測試
      if (i < cardsToTest - 1) {
        await page.goto(TARGET_URL);
        await page.waitForTimeout(1500);
      }
    }

    console.log('\n📍 步驟 7: 截圖保存...');
    await page.screenshot({ path: '/tmp/salesperson-detail-final.png', fullPage: true });
    console.log('✅ 截圖: /tmp/salesperson-detail-final.png');

    console.log('\n' + '='.repeat(70));
    console.log('📊 測試結果總結');
    console.log('='.repeat(70));

    console.log(`\n總測試數: ${cardsToTest}`);
    console.log(`✅ 匹配成功: ${matchCount}`);
    console.log(`❌ 匹配失敗: ${mismatchCount}`);
    console.log(`📊 成功率: ${Math.round(matchCount / cardsToTest * 100)}%`);

    if (mismatchCount === 0) {
      console.log('\n🎉 所有測試通過！業務員卡片跳轉修復成功！');
    } else {
      console.log('\n⚠️  部分測試未通過，需要進一步檢查');
      console.log('\n詳細結果:');
      testResults.forEach(r => {
        console.log(`   ${r.match ? '✅' : '❌'} ID ${r.id}: "${r.cardName}" → "${r.detailName}"`);
      });
    }

    console.log('\n' + '='.repeat(70));
    console.log('⏳ 瀏覽器將保持開啟 8 秒以便檢視...');
    await page.waitForTimeout(8000);

  } catch (error) {
    console.error('\n❌ 測試失敗:', error.message);
    await page.screenshot({ path: '/tmp/error-redirect-test.png', fullPage: true });
    console.log('📸 錯誤截圖: /tmp/error-redirect-test.png');
  } finally {
    await browser.close();
    console.log('\n✅ 測試完成');
  }
})();
