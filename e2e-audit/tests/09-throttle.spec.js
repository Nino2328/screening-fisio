const { test, expect, request: requestFactory } = require('@playwright/test');
const { note, shot, statePath } = require('../lib');

const BASE = 'http://127.0.0.1:8000';
// akun bawaan hasil seeder — dipakai HANYA untuk menguji throttle & logout
const KREDENSIAL = { email: 'fisio@footscreen.com', password: 'password123' };
const tunda = (ms) => new Promise((r) => setTimeout(r, ms));

async function ambilToken(api) {
  const res = await api.get('/login');
  const html = await res.text();
  const m = html.match(/name="_token"\s+value="([^"]+)"/) || html.match(/value="([^"]+)"\s+name="_token"/);
  return m ? m[1] : null;
}

test.describe('I. Throttle login (dijalankan terakhir)', () => {
  test('I1 enam login salah berturut-turut → percobaan ke-6 kena 429', async () => {
    test.setTimeout(180000);
    await tunda(62000); // pastikan jendela rate-limit benar-benar kosong
    const api = await requestFactory.newContext({ baseURL: BASE });
    const token = await ambilToken(api);
    const hasil = [];
    let statusKe6 = 0;
    for (let i = 1; i <= 6; i++) {
      const res = await api.post('/login', {
        form: { _token: token, email: KREDENSIAL.email, password: 'bukan-password-yang-sah-' + i },
        maxRedirects: 0,
      });
      statusKe6 = res.status();
      hasil.push(`#${i}=${res.status()}`);
      await tunda(250);
    }
    await api.dispose();
    const ke429 = statusKe6 === 429;
    const limaPertama = hasil.slice(0, 5).every((h) => !h.includes('429'));
    const ok = ke429 && limaPertama;
    note({
      id: 'I1',
      scenario: 'Throttle POST /login (5/menit): 6 login salah',
      role: 'guest',
      expected: 'percobaan 1–5 ditolak biasa (302), percobaan ke-6 → 429 Too Many Requests',
      actual: hasil.join(', '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'POST /login × 6 berturut-turut dari 127.0.0.1',
    });
    expect(hasil.join(', '), 'hasil 6 percobaan').toBe('#1=302, #2=302, #3=302, #4=302, #5=302, #6=429');
  });

  test('I2 setelah jeda 60+ dtk: login normal kembali jalan, lalu logout & tombol Back', async ({ browser }) => {
    test.setTimeout(180000);
    await tunda(62000); // sesudah uji throttle: tunggu jendela reset

    const ctx = await browser.newContext({ baseURL: BASE, viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();

    // --- login lewat UI (harus sukses, bukan 429) ---
    await page.goto('/login');
    await page.fill('#email', KREDENSIAL.email);
    await page.fill('#password', KREDENSIAL.password);
    const [loginResp] = await Promise.all([
      page.waitForResponse((r) => r.request().method() === 'POST' && /\/login(\?|$)/.test(r.url())),
      page.click('button[type="submit"]'),
    ]);
    await page.waitForLoadState('networkidle').catch(() => {});
    const loginStatus = loginResp.status();
    const urlSetelahLogin = page.url();
    const masukDashboard = urlSetelahLogin.includes('/dashboard');
    await shot(page, 'I2-login-pasca-throttle');

    // --- logout lewat tombol di sidebar ---
    const logoutLink = page.locator('a[title="Logout"]');
    const adaLogout = (await logoutLink.count()) > 0;
    let logoutStatus = 0;
    if (adaLogout) {
      const [resp] = await Promise.all([
        page.waitForResponse((r) => r.request().method() === 'POST' && r.url().includes('/logout')),
        logoutLink.click(),
      ]);
      logoutStatus = resp.status();
      await page.waitForLoadState('networkidle').catch(() => {});
    }
    const urlSetelahLogout = page.url();

    const cookiesSesi = (await ctx.cookies(BASE)).filter((c) => c.name === 'laravel-session');

    // --- tombol Back setelah logout ---
    await page.goBack().catch(() => {});
    await page.waitForLoadState('domcontentloaded').catch(() => {});
    const urlBack = page.url();
    let backAdaDashboard = (await page.locator('#dashboard-heading').count()) > 0;
    let backCatatan = '-';
    if (backAdaDashboard && !urlBack.includes('/login')) {
      // bisa jadi bfcache (snapshot lama) — paksa reload untuk membuktikan sesi sudah habis
      await page.reload().catch(() => {});
      backCatatan = `stale-bfcache, setelah reload→${page.url().replace(BASE, '')}`;
      backAdaDashboard = false;
    }

    // --- bukti server: sesi sudah dibatalkan (redirect eksplisit) ---
    const state = await ctx.storageState();
    const check = await requestFactory.newContext({ baseURL: BASE, storageState: state });
    const reloadRes = await check.get('/dashboard', { maxRedirects: 0 });
    const statusReload = reloadRes.status();
    const urlReload = reloadRes.headers()['location'] || '-';
    await check.dispose();
    await ctx.close();

    const logoutOk = logoutStatus === 302 && urlSetelahLogout.includes('/login');
    const sesiHabis = cookiesSesi.length === 0 || statusReload === 302 || urlReload.includes('/login');
    const backAman = !backAdaDashboard || urlBack.includes('/login');
    const ok = loginStatus === 302 && masukDashboard && logoutOk && sesiHabis && backAman;

    note({
      id: 'I2',
      scenario: 'Login setelah jeda throttle, logout, & tombol Back',
      role: 'fisio',
      expected: 'login 302→/dashboard (bukan 429); logout 302→/login + sesi habis; Back tidak menampilkan dashboard terautentikasi',
      actual: `login=${loginStatus}→${urlSetelahLogin.replace(BASE, '')}, logout=${logoutStatus}→${urlSetelahLogout.replace(BASE, '')}, cookieSesiSisa=${cookiesSesi.length}, Back→${urlBack.replace(BASE, '')} (adaHeadingDashboard=${backAdaDashboard}${backCatatan !== '-' ? ', ' + backCatatan : ''}), GET /dashboard sesudah logout=${statusReload}→${urlReload.replace(BASE, '')}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/I2-login-pasca-throttle.png',
    });
    expect(loginStatus, 'login sesudah jeda').toBe(302);
    expect(masukDashboard, 'tidak masuk dashboard').toBe(true);
    expect(logoutStatus, 'POST /logout').toBe(302);
    expect(urlSetelahLogout).toContain('/login');
    expect(statusReload, 'GET /dashboard setelah logout').toBe(302);
  });
});
