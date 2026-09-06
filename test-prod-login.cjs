const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    page.on('response', response => {
        if (response.url().includes('/login')) {
            console.log(response.request().method(), response.url(), response.status());
        }
    });

    await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await browser.close();
})();
