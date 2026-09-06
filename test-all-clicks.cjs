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

    const buttons = await page.$$('button:has-text("Sudah")');
    console.log(`Found ${buttons.length} Sudah buttons`);
    
    for (let i = 0; i < buttons.length; i++) {
        const btn = buttons[i];
        await btn.click();
        console.log(`Clicked Sudah button ${i}`);
        await page.waitForTimeout(500);
    }
    
    const toast = await page.evaluate(() => {
        const t = document.querySelector('#toast-msg');
        return t ? t.innerText : 'No toast';
    });
    console.log('Toast after all:', toast);
    
    await browser.close();
})();
