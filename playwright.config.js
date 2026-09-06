// @ts-check
import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './e2e',
    timeout: 30000,
    use: {
        baseURL: 'http://localhost:8888',
        headless: true,
        viewport: { width: 1280, height: 900 },
        ignoreHTTPSErrors: true,
    },
    reporter: [['list'], ['html', { open: 'never' }]],
    workers: 1,
});
