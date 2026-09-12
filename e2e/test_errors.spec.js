import { test, expect } from '@playwright/test';

test('Check for console errors', async ({ page }) => {
  const logs = [];
  page.on('console', msg => logs.push(`PAGE LOG: ${msg.type()} - ${msg.text()}`));
  page.on('pageerror', err => logs.push(`PAGE ERROR: ${err.message}`));

  await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
  await page.fill('input[name="email"]', 's20002@prajurit.com');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');

  try {
    await page.waitForURL(/.*beranda/, { timeout: 15000 });
  } catch (e) {
    console.log("Login failed or no redirect. Logs:");
    console.log(logs);
    return;
  }
  
  await page.goto('https://studycenter.nanoprojectdevindonesia.com/jurnal-prajurit');
  await page.waitForTimeout(2000);
  
  console.log('--- BROWSER LOGS ---');
  logs.forEach(l => console.log(l));
  console.log('--------------------');
});
