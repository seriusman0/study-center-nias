const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    // login
    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    page.on('console', msg => console.log('Browser console:', msg.text()));
    page.on('pageerror', err => console.log('Browser error:', err.message));

    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000); // give alpine a moment

    try {
        const checkbox = await page.$('input[type="checkbox"]');
        if (checkbox) {
            console.log('Checkbox found, clicking...');
            await checkbox.click({ force: true });
            console.log('Clicked!');
            await page.waitForTimeout(1000);
        } else {
            console.log('No checkbox found');
        }
    } catch (e) {
        console.log('Error clicking:', e.message);
    }
    
    await browser.close();
})();
