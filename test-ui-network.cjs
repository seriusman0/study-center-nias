const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    page.on('response', async response => {
        if (response.url().includes('/toggle')) {
            console.log('Toggle API status:', response.status());
            const text = await response.text();
            console.log('Toggle API response:', text);
        }
    });

    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    const btnBelum = (await page.$$('button:has-text("Belum")'))[0];
    await btnBelum.click();
    await page.waitForTimeout(1000);
    
    await browser.close();
})();
