import {defineConfig, devices} from '@playwright/test';

export default defineConfig({
    testDir: './tests/E2E',
    fullyParallel: false,
    retries: process.env.CI ? 2 : 0,
    reporter: process.env.CI ? [['html', {open: 'never'}], ['line']] : 'list',
    use: {
        baseURL: process.env.E2E_BASE_URL || 'http://127.0.0.1:8000',
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {name: 'chrome', use: {...devices['Desktop Chrome'], channel: 'chrome'}},
        {name: 'edge', use: {...devices['Desktop Edge'], channel: 'msedge'}},
        {name: 'firefox', use: {...devices['Desktop Firefox']}},
    ],
});
