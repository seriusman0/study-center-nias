const puppeteer = require('puppeteer');

(async () => {
    console.log('Launching browser...');
    const browser = await puppeteer.launch({
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--use-fake-ui-for-media-stream', '--use-fake-device-for-media-stream']
    });
    const page = await browser.newPage();

    page.on('console', msg => console.log('BROWSER LOG:', msg.text()));
    page.on('pageerror', err => console.log('BROWSER ERROR:', err.toString()));
    
    console.log('Navigating to home...');
    await page.goto('http://localhost:8888/');
    
    console.log('Waiting for button...');
    try {
        await page.waitForXPath('//button[contains(text(), "Isi Jurnal via Scan QR")]', { timeout: 5000 });
        const [button] = await page.$x('//button[contains(text(), "Isi Jurnal via Scan QR")]');
        if (button) {
            console.log('Button found! Clicking...');
            await button.click();
            await page.waitForTimeout(2000);
            console.log('Clicked and waited 2 seconds.');
            
            // check if modal is visible
            const modal = await page.$('#publicScannerModal');
            const className = await page.evaluate(el => el.className, modal);
            console.log('Modal class:', className);
        } else {
            console.log('Button not found by XPath.');
        }
    } catch (e) {
        console.log('Error during test:', e.message);
    }
    
    await browser.close();
})();
