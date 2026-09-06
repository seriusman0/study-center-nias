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
    try {
        await page.waitForSelector('button:has-text("Sudah")', { timeout: 5000 });
        const buttons = await page.$$('button:has-text("Sudah")');
        console.log(`Found ${buttons.length} 'Sudah' buttons`);
        
        if (buttons.length > 2) {
            try {
                await buttons[2].click({timeout: 3000}); // No force!
                console.log('Button 2 clicked naturally!');
            } catch (e) {
                console.log('Failed to click naturally:', e.message);
            }
        }
    } catch(e) {
        console.log('Error waiting for selector:', e.message);
    }

    await browser.close();
})();
