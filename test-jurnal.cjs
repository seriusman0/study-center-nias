const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    page.on('console', msg => console.log('Browser console:', msg.text()));
    page.on('pageerror', err => console.log('Browser error:', err.message));

    await page.goto('http://localhost:8888/jurnal');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    try {
        const belumBtn = await page.$('button:has-text("Belum")');
        if (belumBtn) {
            console.log('Found Belum, clicking...');
            await belumBtn.click();
            await page.waitForTimeout(500);
            const bClass = await belumBtn.evaluate(el => el.className);
            console.log('Belum class:', bClass.includes('red'));
        }
    } catch (e) {
        console.log('Error:', e.message);
    }
    await browser.close();
})();
