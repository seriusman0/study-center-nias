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

    page.on('response', res => {
        if (res.url().includes('toggle')) {
            console.log('Toggle response status:', res.status());
            res.text().then(t => console.log('Toggle body:', t)).catch(e=>e);
        }
    });

    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    
    const checkbox = await page.$('input[type="checkbox"]');
    if (checkbox) {
        await checkbox.click({ force: true });
        await page.waitForTimeout(2000); // wait for request
    }
    
    await browser.close();
})();
