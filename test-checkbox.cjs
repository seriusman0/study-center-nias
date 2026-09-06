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

    // get console messages
    page.on('console', msg => console.log('Browser console:', msg.text()));
    page.on('pageerror', err => console.log('Browser error:', err.message));

    // Go to /jurnal-scholarship-teenager
    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');

    // Wait and evaluate if checkbox is disabled or has pointer-events-none
    const checkboxStatus = await page.evaluate(() => {
        const cb = document.querySelector('input[type="checkbox"]');
        if (!cb) return 'No checkbox found';
        
        let parent = cb.closest('div.bg-white');
        return {
            disabled: cb.disabled,
            pointerEvents: window.getComputedStyle(cb).pointerEvents,
            parentPointerEvents: window.getComputedStyle(parent).pointerEvents,
            classes: parent.className
        };
    });

    console.log("Scholarship Jurnal Checkbox:", checkboxStatus);
    
    await browser.close();
})();
