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

    const button = (await page.$$('button:has-text("Sudah")'))[2];
    const isPointerEventsNone = await button.evaluate(el => window.getComputedStyle(el).pointerEvents === 'none');
    console.log('Is pointer-events: none on button?', isPointerEventsNone);
    
    // Check parent containers up to body
    let parentIsPointerEventsNone = await button.evaluate(el => {
        let node = el.parentElement;
        while(node && node !== document.body) {
            if(window.getComputedStyle(node).pointerEvents === 'none') return true;
            node = node.parentElement;
        }
        return false;
    });
    console.log('Is pointer-events: none on any parent?', parentIsPointerEventsNone);

    await browser.close();
})();
