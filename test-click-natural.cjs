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
        const checkbox = await page.$('input[type="checkbox"]');
        if (checkbox) {
            console.log('Found checkbox, attempting natural click...');
            // no force: true
            await checkbox.click({ timeout: 3000 });
            console.log('Successfully clicked naturally!');
        }
    } catch(e) {
        console.log('Error clicking naturally:', e.message);
    }
    await browser.close();
})();
