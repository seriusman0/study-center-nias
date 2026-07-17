# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: nametag-qr.spec.js >> Nametag Template QR Code >> template editor preview includes QR SVG
- Location: e2e/nametag-qr.spec.js:39:5

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: page.fill: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('input[name="login"]')

```

# Page snapshot

```yaml
- generic [active] [ref=e1]: File not found.
```

# Test source

```ts
  1   | // @ts-check
  2   | import { test, expect } from '@playwright/test';
  3   | 
  4   | const BASE = 'http://dev.seriusman.shop';
  5   | const ADMIN_EMAIL = 'admin@studycenter.com';
  6   | const ADMIN_PASS  = 'password';
  7   | 
  8   | async function login(page) {
  9   |     await page.goto(BASE + '/login');
> 10  |     await page.fill('input[name="login"]', ADMIN_EMAIL);
      |                ^ Error: page.fill: Test timeout of 30000ms exceeded.
  11  |     await page.fill('input[name="password"]', ADMIN_PASS);
  12  |     await page.click('button[type="submit"]');
  13  |     await page.waitForURL(/\//, { timeout: 10000 });
  14  |     // Confirm not back on login page
  15  |     await expect(page).not.toHaveURL(/\/login/);
  16  | }
  17  | 
  18  | test.describe('Nametag Template QR Code', () => {
  19  | 
  20  |     test('template index loads system templates', async ({ page }) => {
  21  |         await login(page);
  22  |         await page.goto(BASE + '/admin/nametag-templates');
  23  |         await expect(page.locator('.card').filter({ has: page.locator('h6') })).toHaveCount(5);
  24  |         await expect(page.locator('h6:has-text("Standard")')).toBeVisible();
  25  |         await expect(page.locator('h6:has-text("Dengan Foto")')).toBeVisible();
  26  |         await expect(page.locator('h6:has-text("Landscape")')).toBeVisible();
  27  |         await expect(page.locator('h6').filter({ hasText: 'Portrait Besar' }).first()).toBeVisible();
  28  |     });
  29  | 
  30  |     test('template editor shows {qr_html} in placeholder reference', async ({ page }) => {
  31  |         await login(page);
  32  |         await page.goto(BASE + '/admin/nametag-templates');
  33  |         // Click edit on first template
  34  |         await page.locator('a:has-text("Edit")').first().click();
  35  |         await page.waitForLoadState('domcontentloaded');
  36  |         await expect(page.locator('code:has-text("{qr_html}")')).toBeVisible();
  37  |     });
  38  | 
  39  |     test('template editor preview includes QR SVG', async ({ page }) => {
  40  |         await login(page);
  41  |         await page.goto(BASE + '/admin/nametag-templates');
  42  |         await page.locator('a:has-text("Edit")').first().click();
  43  |         await page.waitForLoadState('domcontentloaded');
  44  |         // Wait for CodeMirror to init
  45  |         await page.waitForSelector('.CodeMirror', { timeout: 5000 });
  46  |         // Click preview
  47  |         await page.click('#btnPreview');
  48  |         // Preview frame should contain SVG (QR code)
  49  |         await page.waitForFunction(() => {
  50  |             const frame = document.getElementById('previewFrame');
  51  |             return frame && frame.querySelector('svg') !== null;
  52  |         }, { timeout: 8000 });
  53  |         const svgCount = await page.locator('#previewFrame svg').count();
  54  |         expect(svgCount).toBeGreaterThan(0);
  55  |     });
  56  | 
  57  |     test('nametag index shows template cards from DB', async ({ page }) => {
  58  |         await login(page);
  59  |         await page.goto(BASE + '/admin/nametags');
  60  |         await expect(page.locator('.tpl-card')).toHaveCount(5);
  61  |         await expect(page.locator('a:has-text("Kelola / Edit Template")')).toBeVisible();
  62  |     });
  63  | 
  64  |     test('generate nametag includes QR SVG in output', async ({ page, context }) => {
  65  |         await login(page);
  66  |         await page.goto(BASE + '/admin/nametags');
  67  | 
  68  |         // Select first student checkbox
  69  |         const firstCb = page.locator('.user-cb').first();
  70  |         await firstCb.check();
  71  | 
  72  |         // Open new tab for generate (form target=_blank)
  73  |         const [newPage] = await Promise.all([
  74  |             context.waitForEvent('page'),
  75  |             page.click('#genBtn'),
  76  |         ]);
  77  | 
  78  |         await newPage.waitForLoadState('domcontentloaded');
  79  | 
  80  |         // Should contain SVG QR code
  81  |         const svgs = await newPage.locator('svg').count();
  82  |         expect(svgs).toBeGreaterThan(0);
  83  | 
  84  |         // Toolbar should show card count
  85  |         await expect(newPage.locator('.toolbar')).toBeVisible();
  86  |         await newPage.close();
  87  |     });
  88  | 
  89  |     test('template html_content in DB contains {qr_html}', async ({ page }) => {
  90  |         await login(page);
  91  |         // Hit preview endpoint directly for template 1
  92  |         const resp = await page.request.post(BASE + '/admin/nametag-templates/1/preview', {
  93  |             data: { html_content: '{qr_html}', width: 8.5, height: 5.5 },
  94  |             headers: {
  95  |                 'X-CSRF-TOKEN': await page.evaluate(async (url) => {
  96  |                     const r = await fetch(url + '/admin/nametags');
  97  |                     const html = await r.text();
  98  |                     const m = html.match(/content="csrf-token"[^>]*content="([^"]+)"|name="csrf-token"[^>]*content="([^"]+)"/);
  99  |                     return (m && (m[1] || m[2])) || '';
  100 |                 }, BASE),
  101 |                 'Content-Type': 'application/json',
  102 |             },
  103 |         });
  104 |         // If CSRF fails, just check that route resolves (not 404)
  105 |         expect([200, 419, 302]).toContain(resp.status());
  106 |     });
  107 | 
  108 | });
  109 | 
```