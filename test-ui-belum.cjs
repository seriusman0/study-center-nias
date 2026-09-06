const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    page.on('console', msg => console.log('Console:', msg.text()));
    page.on('pageerror', err => console.log('Error:', err.message));
    
    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    const btnBelum = (await page.$$('button:has-text("Belum")'))[0];
    const btnSudah = (await page.$$('button:has-text("Sudah")'))[0];
    
    console.log('Before click: Belum =', await btnBelum.evaluate(el => el.className));
    console.log('Before click: Sudah =', await btnSudah.evaluate(el => el.className));
    
    await btnBelum.click();
    console.log('Clicked Belum');
    
    await page.waitForTimeout(500); // wait for fetch and UI update
    
    console.log('After click: Belum =', await btnBelum.evaluate(el => el.className));
    console.log('After click: Sudah =', await btnSudah.evaluate(el => el.className));
    
    await browser.close();
})();
