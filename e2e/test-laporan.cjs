const { chromium, devices } = require('playwright');
const iPhone = devices['iPhone 12'];

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({
        ...iPhone,
        viewport: iPhone.viewport
    });
    const page = await context.newPage();
    
    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'admin@studycenter.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForNavigation();
    
    await page.goto('http://localhost:8888/admin/jurnal/reports');
    
    const scrollW = await page.evaluate(() => document.documentElement.scrollWidth);
    const innerW = await page.evaluate(() => window.innerWidth);
    console.log(`Laporan page - scrollWidth: ${scrollW}, innerWidth: ${innerW}`);
    
    await browser.close();
})();
