const { chromium } = require('playwright');
const fs = require('fs');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
  await page.fill('input[name="email"]', 'admin@studycenter.com');
  await page.fill('input[name="password"]', 'password'); // Assuming this password based on .env maybe? Or wait, usually it's password
  await page.click('button[type="submit"]');
  await page.waitForTimeout(3000);

  await page.goto('https://studycenter.nanoprojectdevindonesia.com/admin/collected-emails');
  
  console.log("Looking for toggle button...");
  const btn = await page.waitForSelector('.btn-toggle-invite', { timeout: 5000 }).catch(() => null);
  if (!btn) {
    console.log("No button found.");
  } else {
    console.log("Clicking button...");
    
    // Catch response
    page.on('response', async response => {
      if (response.url().includes('toggle-invite')) {
         console.log(response.status());
         console.log(await response.text());
      }
    });

    await btn.click();
    await page.waitForTimeout(3000);
  }

  await browser.close();
})();
