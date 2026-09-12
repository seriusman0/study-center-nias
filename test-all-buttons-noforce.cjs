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
    console.log(`Found ${buttons.length} 'Sudah' buttons`);
    
    // click the second button without force
    if (buttons.length > 1) {
        try {
            await buttons[1].click({ timeout: 2000 });
            console.log("Successfully clicked without force");
        } catch (e) {
            console.log("Failed to click without force:", e.message);
        }
    }
    await browser.close();
})();
