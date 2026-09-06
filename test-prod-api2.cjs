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

    await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('https://studycenter.nanoprojectdevindonesia.com/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    const btns = await page.$$('button:has-text("Sudah")');
    console.log('Found Sudah buttons:', btns.length);
    if (btns.length > 0) {
        await btns[0].click();
        console.log('Clicked first Sudah button');
    }
    
    await page.waitForTimeout(2000);
    await browser.close();
})();
