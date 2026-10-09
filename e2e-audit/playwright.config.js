const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 120000,
  expect: { timeout: 15000 },
  fullyParallel: false,
  workers: 1,
  retries: 0,
  reporter: [['list'], ['json', { outputFile: 'report.json' }]],
  outputDir: 'test-results',
  use: {
    baseURL: 'http://127.0.0.1:8000',
    browserName: 'chromium',
    viewport: { width: 1366, height: 768 },
    locale: 'id-ID',
    actionTimeout: 20000,
    screenshot: 'off',
  },
});
