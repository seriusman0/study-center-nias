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
        const checkbox = await page.$('input[type="checkbox"][x-model="state.life"]');
        if (checkbox) {
            console.log('Clicking checkbox...');
            await checkbox.click();
            await page.waitForTimeout(500);
        }
        
        const belumBtn = await page.$('button:has-text("Belum")');
        const sudahBtn = await page.$('button:has-text("Sudah")');
        
        if (belumBtn) {
            console.log('Clicking Belum...');
            await belumBtn.click();
            await page.waitForTimeout(500);
            const bClass = await belumBtn.evaluate(el => el.className);
            console.log('Belum class:', bClass.includes('teal'));
        }
        if (sudahBtn) {
            console.log('Clicking Sudah...');
            await sudahBtn.click();
            await page.waitForTimeout(500);
            const sClass = await sudahBtn.evaluate(el => el.className);
            console.log('Sudah class:', sClass.includes('teal'));
        }
    } catch (e) {
        console.log('Error:', e.message);
    }
    await browser.close();
})();
