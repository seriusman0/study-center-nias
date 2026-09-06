const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();
    
    await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
    const html = await page.content();
    console.log(html.includes('action="https://') ? 'Login form is HTTPS' : 'Login form is HTTP');
    await browser.close();
})();
