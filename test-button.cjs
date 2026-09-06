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

    // Go to /jurnal-scholarship-teenager
    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    try {
        const sudahBtn = await page.$('button:has-text("Sudah")');
        if (sudahBtn) {
            console.log('Found "Sudah" button, clicking...');
            await sudahBtn.click();
            await page.waitForTimeout(1000);
            
            const btnClass = await sudahBtn.evaluate(el => el.className);
            console.log('Button class after click:', btnClass);
        } else {
            console.log('No "Sudah" button found');
        }
    } catch (e) {
        console.log('Error clicking:', e.message);
    }
    
    await browser.close();
})();
