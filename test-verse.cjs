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
    await page.waitForTimeout(1000);
    
    try {
        const checkbox = await page.waitForSelector('input[x-model="state.verse_check"]');
        if (checkbox) {
            console.log('Found verse checkbox, clicking...');
            await checkbox.click();
            await page.waitForTimeout(2000);
            console.log('Successfully clicked verse checkbox!');
        }
    } catch(e) {
        console.log('Error clicking:', e.message);
    }
    await browser.close();
})();
