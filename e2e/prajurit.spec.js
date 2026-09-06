// @ts-check
import { test, expect } from '@playwright/test';

const BASE = 'http://localhost:8888';
const PRAJURIT_USER = 'testprajurit';
const PRAJURIT_PASS = 'password123';
const ADMIN_USER = 'admin'; // wait, what is the admin username?
const ADMIN_PASS = 'password';

test.describe('Prajurit Role E2E', () => {

    test('Prajurit can access their jurnal', async ({ page }) => {
        // Login as prajurit
        await page.goto(BASE + '/login');
        await page.fill('input[name="login"]', PRAJURIT_USER);
        await page.fill('input[name="password"]', PRAJURIT_PASS);
        await page.click('button[type="submit"]');

        // Wait for redirect
        await page.waitForURL(/.*beranda/, { timeout: 10000 });
        await expect(page).toHaveURL(/.*beranda/);
        
        await page.screenshot({ path: "test-results/prajurit-login.png" });
        
        // Ensure CTA button is visible
        await expect(page.locator('text=Mulai Isi Jurnal Prajurit')).toBeVisible();

        // Go to jurnal prajurit
        await page.goto(BASE + '/jurnal-prajurit');
        await page.waitForURL(/.*jurnal-prajurit/);
        
        // Ensure the items are displayed
        await expect(page.locator('text=Tidak Memaki')).toBeVisible();
        await expect(page.locator('text=Membaca Alkitab di Sekolah')).toBeVisible();
        await expect(page.locator('text=Jumlah Salah Ayat Hafalan')).toBeVisible();
    });

});
