// @ts-check
import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './e2e',
    timeout: 30000,
    use: {
        baseURL: 'http://dev.seriusman.shop',
        headless: true,
        viewport: { width: 1280, height: 900 },
        ignoreHTTPSErrors: true,
    },
    reporter: [['list'], ['html', { open: 'never' }]],
    workers: 1,
});
