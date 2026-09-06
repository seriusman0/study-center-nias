const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    
    let isCheckedBefore = await page.$eval('input[type="checkbox"]', el => el.checked);
    console.log('Checked before:', isCheckedBefore);
    
    await page.click('input[type="checkbox"]', { force: true });
    await page.waitForTimeout(500);
    
    let isCheckedAfter = await page.$eval('input[type="checkbox"]', el => el.checked);
    console.log('Checked after:', isCheckedAfter);

    await browser.close();
})();
