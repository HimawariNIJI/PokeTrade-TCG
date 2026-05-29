import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.BASE_URL || 'http://127.0.0.1:8123';

export default defineConfig({
  testDir: './tests/e2e',
  testMatch: '**/*.{spec,setup}.ts',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: 1,
  // PHP's built-in server is light on concurrency even with worker
  // processes, so keep parallelism modest to avoid request timeouts.
  workers: 3,
  reporter: [['list'], ['html', { open: 'never', outputFolder: 'playwright-report' }]],
  timeout: 30000,
  expect: { timeout: 7000 },

  use: {
    baseURL,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },

  projects: [
    { name: 'setup', testMatch: /auth\.setup\.ts/ },
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1366, height: 900 } },
      testIgnore: [/auth\.setup\.ts/, /authed\.spec\.ts/, /responsive\.spec\.ts/],
    },
    {
      name: 'mobile',
      use: { ...devices['Pixel 5'] },
      testMatch: /responsive\.spec\.ts/,
    },
    {
      name: 'authed',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1366, height: 900 }, storageState: '.auth/user.json' },
      dependencies: ['setup'],
      testMatch: /authed\.spec\.ts/,
    },
  ],

  webServer: {
    command: 'rm -f public/hot && npm run build && PHP_CLI_SERVER_WORKERS=8 php artisan serve --port=8123',
    url: baseURL,
    reuseExistingServer: true,
    timeout: 120000,
  },
});
