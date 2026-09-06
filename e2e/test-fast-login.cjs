const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    
    page.on('console', msg => console.log('LOG:', msg.text()));
    
    // Listen to network responses
    page.on('response', response => {
        if (response.url().includes('login')) {
            console.log('API Status:', response.url(), response.status());
        }
    });

    await page.goto('http://localhost:8888/login');
    
    // Wait for the CSRF token
    const csrf = await page.$eval('input[name="_token"]', el => el.value);
    console.log('CSRF:', csrf);
    
    // Execute fastLogin directly in page context
    await page.evaluate(async () => {
        try {
            const res = await fetch('/login/fast', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: 'invalid' })
            });
            console.log('Fetch ok:', res.ok, 'status:', res.status);
            const data = await res.json();
            console.log('Fetch data:', JSON.stringify(data));
        } catch (e) {
            console.error('Fetch exception:', e.message);
        }
    });
    
    await browser.close();
})();
