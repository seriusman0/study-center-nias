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
    await page.waitForSelector('button:has-text("Sudah")');
    
    // Evaluate in page to see if `state.life` actually changes
    const btnSudah = (await page.$$('button:has-text("Sudah")'))[2];
    const btnBelum = (await page.$$('button:has-text("Belum")'))[2];
    
    console.log('Before: Sudah:', await btnSudah.evaluate(el => el.className), 'Belum:', await btnBelum.evaluate(el => el.className));
    
    await btnSudah.click();
    await page.waitForTimeout(500);
    console.log('After Sudah click: Sudah:', await btnSudah.evaluate(el => el.className), 'Belum:', await btnBelum.evaluate(el => el.className));

    await btnBelum.click();
    await page.waitForTimeout(500);
    console.log('After Belum click: Sudah:', await btnSudah.evaluate(el => el.className), 'Belum:', await btnBelum.evaluate(el => el.className));
    
    await browser.close();
})();
