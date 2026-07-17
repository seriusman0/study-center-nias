# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: debug-login.spec.js >> debug login
- Location: e2e/debug-login.spec.js:3:1

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: locator.fill: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('input[name="email"]')

```

# Page snapshot

```yaml
- generic [active] [ref=e1]: File not found.
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | test('debug login', async ({ page }) => {
  4  |     await page.goto('http://localhost/login');
  5  |     await page.screenshot({ path: '/tmp/login-before.png' });
  6  | 
  7  |     const emailInput = page.locator('input[name="email"]');
  8  |     const passInput  = page.locator('input[name="password"]');
  9  | 
  10 |     console.log('email count:', await emailInput.count());
  11 |     console.log('pass count:', await passInput.count());
  12 | 
> 13 |     await emailInput.fill('admin@studycenter.com');
     |                      ^ Error: locator.fill: Test timeout of 30000ms exceeded.
  14 |     await passInput.fill('password');
  15 | 
  16 |     await page.screenshot({ path: '/tmp/login-filled.png' });
  17 | 
  18 |     await page.click('button[type="submit"]');
  19 |     await page.waitForTimeout(3000);
  20 | 
  21 |     console.log('After submit URL:', page.url());
  22 |     await page.screenshot({ path: '/tmp/login-after.png' });
  23 | });
  24 | 
```