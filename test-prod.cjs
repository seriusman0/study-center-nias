const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('https://studycenter.nanoprojectdevindonesia.com/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    
    const html = await page.content();
    console.log(html.includes('hasLife') ? 'hasLife is present on PROD!' : 'hasLife is MISSING on PROD!');
    
    await browser.close();
})();
