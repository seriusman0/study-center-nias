# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: test_errors.spec.js >> Check for console errors
- Location: e2e/test_errors.spec.js:3:1

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: page.fill: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('input[name="email"]')

```

# Page snapshot

```yaml
- generic [ref=e1]:
  - navigation [ref=e2]:
    - generic [ref=e3]:
      - link "SC Nias" [ref=e4] [cursor=pointer]:
        - /url: https://studycenter.nanoprojectdevindonesia.com
        - generic [ref=e5]: SC
        - generic [ref=e6]: Nias
      - generic [ref=e7]:
        - link "Masuk" [ref=e8] [cursor=pointer]:
          - /url: https://studycenter.nanoprojectdevindonesia.com/login
        - link "Daftar" [ref=e9] [cursor=pointer]:
          - /url: https://studycenter.nanoprojectdevindonesia.com/daftar
  - main [ref=e10]:
    - generic [ref=e12]:
      - heading "Masuk" [level=1] [ref=e13]
      - paragraph [ref=e14]: Masuk ke akun Study Center Nias
      - generic [ref=e15]:
        - generic [ref=e16]:
          - generic [ref=e17]: Email atau Username
          - textbox "email@contoh.com / username" [active] [ref=e18]
        - generic [ref=e19]:
          - generic [ref=e20]: Password
          - textbox "••••••••" [ref=e21]
        - generic [ref=e23]:
          - checkbox "Simpan info login" [checked] [ref=e24]
          - text: Simpan info login
        - button "Masuk" [ref=e25] [cursor=pointer]
      - paragraph [ref=e26]:
        - text: Belum punya akun?
        - link "Daftar" [ref=e27] [cursor=pointer]:
          - /url: https://studycenter.nanoprojectdevindonesia.com/daftar
  - contentinfo [ref=e28]:
    - generic [ref=e29]:
      - paragraph [ref=e30]: Study Center Nias
      - paragraph [ref=e31]: Gunungsitoli · Kab. Nias · Kab. Nias Selatan · Kab. Nias Utara
      - paragraph [ref=e32]:
        - link "Download Aplikasi Android" [ref=e33] [cursor=pointer]:
          - /url: https://studycenter.nanoprojectdevindonesia.com/download-android
      - paragraph [ref=e34]: © 2026 Study Center Nias
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | test('Check for console errors', async ({ page }) => {
  4  |   const logs = [];
  5  |   page.on('console', msg => logs.push(`PAGE LOG: ${msg.type()} - ${msg.text()}`));
  6  |   page.on('pageerror', err => logs.push(`PAGE ERROR: ${err.message}`));
  7  | 
  8  |   await page.goto('https://studycenter.nanoprojectdevindonesia.com/login');
> 9  |   await page.fill('input[name="email"]', 's20002@prajurit.com');
     |              ^ Error: page.fill: Test timeout of 30000ms exceeded.
  10 |   await page.fill('input[name="password"]', 'password');
  11 |   await page.click('button[type="submit"]');
  12 | 
  13 |   try {
  14 |     await page.waitForURL(/.*beranda/, { timeout: 15000 });
  15 |   } catch (e) {
  16 |     console.log("Login failed or no redirect. Logs:");
  17 |     console.log(logs);
  18 |     return;
  19 |   }
  20 |   
  21 |   await page.goto('https://studycenter.nanoprojectdevindonesia.com/jurnal-prajurit');
  22 |   await page.waitForTimeout(2000);
  23 |   
  24 |   console.log('--- BROWSER LOGS ---');
  25 |   logs.forEach(l => console.log(l));
  26 |   console.log('--------------------');
  27 | });
  28 | 
```