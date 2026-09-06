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
        const sudahBtn = await page.$('button:has-text("Sudah")');
        const belumBtn = await page.$('button:has-text("Belum")');
        
        let sClass = await sudahBtn.evaluate(el => el.className);
        let bClass = await belumBtn.evaluate(el => el.className);
        console.log('BEFORE CLICK: Sudah =', sClass.includes('bg-sc-teal-600'), 'Belum =', bClass.includes('bg-sc-teal-600'));
        
        await sudahBtn.click();
        await page.waitForTimeout(500);
        
        sClass = await sudahBtn.evaluate(el => el.className);
        bClass = await belumBtn.evaluate(el => el.className);
        console.log('AFTER SUDAH CLICK: Sudah =', sClass.includes('bg-sc-teal-600'), 'Belum =', bClass.includes('bg-sc-teal-600'));
        
        await belumBtn.click();
        await page.waitForTimeout(500);
        
        sClass = await sudahBtn.evaluate(el => el.className);
        bClass = await belumBtn.evaluate(el => el.className);
        console.log('AFTER BELUM CLICK: Sudah =', sClass.includes('bg-sc-teal-600'), 'Belum =', bClass.includes('bg-sc-teal-600'));

    } catch (e) {
        console.log('Error:', e.message);
    }
    await browser.close();
})();
