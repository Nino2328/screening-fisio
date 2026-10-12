const { test, expect } = require('@playwright/test');
const { note, shot, watchPage } = require('../lib');

const PROTECTED = ['/dashboard', '/athletes', '/tests', '/reports'];

test.describe('A. Autentikasi — tamu & halaman publik', () => {
  for (const url of PROTECTED) {
    test(`A1 tamu GET ${url} → redirect ke /login`, async ({ request }) => {
      const res = await request.get(url, { maxRedirects: 0 });
      const status = res.status();
      const loc = res.headers()['location'] || '';
      const ok = status === 302 && loc.includes('/login');
      note({ id: `A1 ${url}`, scenario: `Tamu membuka ${url}`, role: 'tamu', expected: '302 → /login', actual: `${status} ${loc}`, status: ok ? 'LULUS' : 'GAGAL', evidence: 'HTTP status + Location header' });
      expect(ok).toBe(true);
    });
  }

  test('A7 /register ditutup — route 404 & tidak ada link daftar', async ({ page, request }) => {
    const w = watchPage(page, 'A7-register');
    const getRes = await request.get('/register', { maxRedirects: 0 });
    const postRes = await request.post('/register', {
      maxRedirects: 0,
      form: { name: 'QA Test User', email: 'qa_test_user@example.com', password: 'Password123!', password_confirmation: 'Password123!' },
    });
    await page.goto('/login');
    await page.waitForLoadState('domcontentloaded');
    const halLogin = await page.content();
    const adaLinkDaftar = halLogin.includes('/register');
    await shot(page, 'A7-register-ditutup');
    const ok = getRes.status() === 404 && postRes.status() === 404 && !adaLinkDaftar;
    note({
      id: 'A7', scenario: 'Registrasi publik ditutup (T2)', role: 'tamu',
      expected: 'GET & POST /register = 404, tidak ada link "Daftar" di /login',
      actual: `GET ${getRes.status()}, POST ${postRes.status()}, linkDaftar=${adaLinkDaftar}, akun qa_test_user@example.com tidak dibuat`,
      status: ok ? 'LULUS' : 'GAGAL', evidence: 'screenshots/A7-register-ditutup.png',
    });
    w.flush();
    expect(getRes.status(), 'GET /register harus 404').toBe(404);
    expect(postRes.status(), 'POST /register masih bisa dipanggil').toBe(404);
    expect(adaLinkDaftar, 'masih ada tautan ke /register di /login').toBe(false);
  });

  test('A8 /forgot-password ditutup — 404 & teks statis di login', async ({ page, request }) => {
    const res = await request.get('/forgot-password', { maxRedirects: 0 });
    const status = res.status();
    await page.goto('/login');
    const halLogin = await page.content();
    const adaTeks = halLogin.includes('Hubungi administrator');
    const ok = status === 404 && adaTeks;
    note({
      id: 'A8', scenario: 'Tamu membuka /forgot-password (T4)', role: 'tamu',
      expected: '404 (route dihapus) & /login menampilkan teks statis "Lupa password? Hubungi administrator."',
      actual: `HTTP ${status}, teksStatis=${adaTeks}`, status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'GET /forgot-password',
    });
    expect(status, '/forgot-password harus 404').toBe(404);
    expect(adaTeks, 'teks statis lupa password tidak ada').toBe(true);
  });
});
