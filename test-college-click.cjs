const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    page.on('console', msg => console.log('Console:', msg.text()));
    page.on('pageerror', err => console.log('Error:', err.message));
    
    // Listen to network responses for /toggle
    page.on('response', async response => {
        if (response.url().includes('toggle') && response.request().method() === 'POST') {
            console.log('Toggle response status:', response.status());
            const text = await response.text();
            console.log('Toggle response body:', text);
        }
    });

    await page.goto('http://localhost:8888/test-login-college');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    const buttons = await page.$$('button:has-text("Sudah")');
    if (buttons.length > 0) {
        console.log(`Found ${buttons.length} 'Sudah' buttons. Clicking first one...`);
        await buttons[0].click();
        await page.waitForTimeout(2000);
    } else {
        console.log('No buttons found!');
    }

    await browser.close();
})();
