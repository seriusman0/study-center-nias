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
    for (let i = 0; i < buttons.length; i++) {
        const className = await buttons[i].evaluate(el => el.className);
        console.log(`Button ${i} class:`, className);
    }
    
    // click the second button
    if (buttons.length > 1) {
        await buttons[1].click({force: true});
        await page.waitForTimeout(1000);
        const className2 = await buttons[1].evaluate(el => el.className);
        console.log(`Button 1 class after click:`, className2);
    }

    await browser.close();
})();
