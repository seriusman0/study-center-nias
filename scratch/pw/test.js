const { chromium } = require('playwright');

(async () => {
    console.log('Launching browser...');
    const browser = await chromium.launch({
        args: [
            '--no-sandbox', 
            '--disable-setuid-sandbox',
            '--use-fake-ui-for-media-stream',
            '--use-fake-device-for-media-stream'
        ]
    });
    
    // Create context with camera permissions granted
    const context = await browser.newContext({
        permissions: ['camera'],
    });
    
    const page = await context.newPage();

    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', error => console.log('PAGE ERROR:', error.message));

    console.log('Navigating to http://localhost:8888 ...');
    await page.goto('http://localhost:8888', { waitUntil: 'networkidle' });

    console.log('Waiting for button...');
    const button = page.locator('button:has-text("Isi Jurnal via Scan QR")');
    await button.waitFor({ state: 'visible', timeout: 5000 });
    
    console.log('Clicking button...');
    await button.click();

    console.log('Waiting 2 seconds for modal to appear and scanner to initialize...');
    await page.waitForTimeout(2000);

    const modal = page.locator('#publicScannerModal');
    const isVisible = await modal.isVisible();
    console.log('Modal visible:', isVisible);

    const scanStatus = await page.locator('#scan-status').innerText();
    console.log('Scan status text:', scanStatus);
    
    const qrReaderHTML = await page.locator('#qr-reader').innerHTML();
    console.log('qr-reader inner HTML length:', qrReaderHTML.length);
    if (qrReaderHTML.includes('<video')) {
        console.log('Video element found inside qr-reader!');
    } else {
        console.log('NO video element found inside qr-reader.');
    }

    await browser.close();
})();
