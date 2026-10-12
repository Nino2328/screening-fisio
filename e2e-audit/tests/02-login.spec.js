const { test, expect } = require('@playwright/test');
const { note, shot, loginAndSave, statePath } = require('../lib');

const ACCOUNTS = [
  ['admin', 'admin@footscreen.com'],
  ['fisio', 'fisio@footscreen.com'],
  ['pelatih', 'pelatih@footscreen.com'],
];

test.describe('A. Autentikasi — login', () => {
  test('A2 login dengan password salah → pesan error, tidak masuk', async ({ page }) => {
    await page.goto('/login');
    await page.fill('#email', 'admin@footscreen.com');
    await page.fill('#password', 'passwordSALAH');
    const [resp] = await Promise.all([
      page.waitForResponse((r) => r.url().includes('/login') && r.request().method() === 'POST'),
      page.click('button[type="submit"]'),
    ]);
    await page.waitForLoadState('domcontentloaded');
    const status = resp.status();
    const url = page.url();
    const errVisible = await page.locator('text=Kredensial').or(page.locator('text=salah')).or(page.locator('text=credentials')).first().isVisible().catch(() => false);
    const errText = (await page.locator('[style*="255,68,68"]').first().textContent().catch(() => '')) || '';
    await shot(page, 'A2-login-salah');
    const ok = status === 302 && url.includes('/login') && errVisible;
    note({
      id: 'A2', scenario: 'Login password salah', role: 'admin', expected: 'tetap di /login + pesan error',
      actual: `POST ${status}, url=${url.replace('http://127.0.0.1:8000', '')}, errVisible=${errVisible}, err="${errText.trim().slice(0, 80)}"`,
      status: ok ? 'LULUS' : 'GAGAL', evidence: 'screenshots/A2-login-salah.png',
    });
    expect(status).toBe(302);
    expect(url).toContain('/login');
  });

  for (const [role, email] of ACCOUNTS) {
    test(`A3 login ${role} → dashboard + simpan sesi`, async ({ browser }) => {
      const r = await loginAndSave(browser, role, email, 'password123');
      const ok = r.http === 302 && r.onDash;
      note({
        id: `A3 ${role}`, scenario: `Login ${role}`, role, expected: '302 → /dashboard, sesi tersimpan',
        actual: `POST ${r.http} → ${r.landed.replace('http://127.0.0.1:8000', '')}`,
        status: ok ? 'LULUS' : 'GAGAL', evidence: `state/${role}.json`,
      });
      expect(ok).toBe(true);
    });
  }

  test('A3b verifikasi sesi tersimpan: GET /dashboard memakai state tiap role', async ({ browser }) => {
    for (const [role] of ACCOUNTS) {
      const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath(role), viewport: { width: 1366, height: 768 } });
      const page = await ctx.newPage();
      const res = await page.goto('/dashboard');
      const status = res ? res.status() : 0;
      await shot(page, `A3b-dashboard-${role}`);
      note({ id: `A3b ${role}`, scenario: `GET /dashboard dengan sesi ${role}`, role, expected: '200', actual: `HTTP ${status}`, status: status === 200 ? 'LULUS' : 'GAGAL', evidence: `screenshots/A3b-dashboard-${role}.png` });
      expect(status).toBe(200);
      await ctx.close();
    }
  });
});
