const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    page.on('console', msg => console.log('Console:', msg.text()));
    page.on('pageerror', err => console.log('Error:', err.message));
    
    await page.goto('http://localhost:8888/login');
    await page.fill('input[name="login"]', 'testuser');
    await page.fill('input[name="password"]', '12345');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    await page.goto('http://localhost:8888/jurnal-scholarship-teenager');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);

    const btn = (await page.$$('button:has-text("Sudah")'))[0];
    
    // Check classes before
    const classesBefore = await btn.evaluate(el => el.className);
    console.log('Before click:', classesBefore);
    
    // Click it!
    await btn.click();
    console.log('Clicked Sudah');
    
    await page.waitForTimeout(500); // wait for fetch and UI update
    
    // Check classes after
    const classesAfter = await btn.evaluate(el => el.className);
    console.log('After click:', classesAfter);
    
    // Check toast
    const toastText = await page.evaluate(() => {
        const toast = document.querySelector('#toast-msg');
        return toast ? toast.innerText : 'No toast element found';
    });
    console.log('Toast:', toastText);
    
    await browser.close();
})();
