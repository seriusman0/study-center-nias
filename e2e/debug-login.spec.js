import { test, expect } from '@playwright/test';

test('debug login', async ({ page }) => {
    await page.goto('http://localhost/login');
    await page.screenshot({ path: '/tmp/login-before.png' });

    const emailInput = page.locator('input[name="email"]');
    const passInput  = page.locator('input[name="password"]');

    console.log('email count:', await emailInput.count());
    console.log('pass count:', await passInput.count());

    await emailInput.fill('admin@studycenter.com');
    await passInput.fill('password');

    await page.screenshot({ path: '/tmp/login-filled.png' });

    await page.click('button[type="submit"]');
    await page.waitForTimeout(3000);

    console.log('After submit URL:', page.url());
    await page.screenshot({ path: '/tmp/login-after.png' });
});
