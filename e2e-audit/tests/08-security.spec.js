const { test, expect, request: requestFactory } = require('@playwright/test');
const { note, shot, statePath } = require('../lib');

const BASE = 'http://127.0.0.1:8000';

test.describe('G. Keamanan: CSRF, cookie, & tampilan error', () => {
  test('G1 POST tanpa token CSRF → 419 (ditolak)', async ({ browser }) => {
    const api = await requestFactory.newContext({ baseURL: BASE, storageState: statePath('admin') });
    const tanpaToken = await api.post('/athletes', { form: { name: 'QA_TEST_TanpaCSRF' }, maxRedirects: 0 });
    const tamu = await requestFactory.newContext({ baseURL: BASE });
    const tamuPost = await tamu.post('/athletes', { form: { name: 'QA_TEST_TamuCSRF' }, maxRedirects: 0 });
    await api.dispose();
    await tamu.dispose();
    const ok = tanpaToken.status() === 419;
    note({
      id: 'G1',
      scenario: 'POST /athletes tanpa _token (sah login, tapi tanpa CSRF)',
      role: 'admin',
      expected: '419 (TokenMismatch) & data tidak tersimpan',
      actual: `POST tanpa token=${tanpaToken.status()}; POST sebagai tamu=${tamuPost.status()} (redirect ke login bila 302)`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'APIRequestContext tanpa kolom _token',
    });
    expect(tanpaToken.status(), 'POST tanpa CSRF harus 419').toBe(419);
  });

  test('G2 flag cookie sesi: HttpOnly & SameSite', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin') });
    const cookies = await ctx.cookies(BASE);
    await ctx.close();
    const sesi = cookies.filter((c) => /session|laravel_session/i.test(c.name));
    const utama = sesi[0] || null;
    const detail = cookies.map((c) => `${c.name}[httpOnly=${c.httpOnly},sameSite=${c.sameSite || '-'},secure=${c.secure},path=${c.path}]`).join(' | ');
    const ok = !!utama && utama.httpOnly === true && (utama.sameSite === 'Lax' || utama.sameSite === 'Strict');
    note({
      id: 'G2',
      scenario: 'Flag cookie sesi',
      role: 'admin',
      expected: 'cookie sesi HttpOnly=true & SameSite=Lax/Strict',
      actual: `session=${utama ? `${utama.name} httpOnly=${utama.httpOnly} sameSite=${utama.sameSite} secure=${utama.secure} expires=${utama.expires}` : 'TIDAK ADA'} || semua=${detail}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'browserContext.cookies()',
    });
    expect(utama, 'cookie sesi tidak ditemukan').toBeTruthy();
    expect(utama.httpOnly, 'HttpOnly').toBe(true);
    expect(['Lax', 'Strict'], 'SameSite').toContain(utama.sameSite);
  });

  test('G3 /reports pelatih tak lagi 500 & pantau kebocoran APP_DEBUG', async ({ browser }) => {
    const api = await requestFactory.newContext({ baseURL: BASE, storageState: statePath('pelatih') });
    const res = await api.get('/reports', { maxRedirects: 0, timeout: 60000 });
    const status = res.status();
    const body = status >= 500 ? await res.text() : '';
    await api.dispose();
    const adaStackTrace = /Stack Trace/i.test(body);
    const adaPath = /resources[\\/]+views|[\\/]+app[\\/]+Http[\\/]+Controllers/i.test(body);
    const adaEnv = /DB_PASSWORD|APP_KEY=/i.test(body);
    const potongan = body
      .replace(/<style[\s\S]*?<\/style>/gi, ' ')
      .replace(/<[^>]+>/g, ' ')
      .replace(/\s+/g, ' ')
      .trim()
      .slice(0, 320);
    // Pemicu 500 lama (assessment yatim di /reports) sudah diperbaiki T1, dan tidak ada
    // pemicu 500 lain yang bisa dibuat tanpa mengubah kode/data. Selama APP_DEBUG=true,
    // setiap 500 yang muncul masih menampilkan stack trace (pemilik memantau manual).
    const bocor = status >= 500 && (adaStackTrace || adaPath || adaEnv);
    note({
      id: 'G3',
      scenario: 'Isi halaman error 500 (/reports) & kebocoran APP_DEBUG',
      role: 'pelatih',
      expected: '/reports tidak lagi 500 (assessment yatim diperbaiki T1); bila kelak ada 500 dan APP_DEBUG masih true, stack trace tampil = kebocoran (pemilik menyetel APP_DEBUG=false)',
      actual: `HTTP ${status}, adaStackTrace=${adaStackTrace}, adaPathInternal=${adaPath}, bocorSecret=${adaEnv}, pemicu500Lain=tidak ada tanpa mengubah kode/data, isi="${potongan}"`,
      status: bocor ? 'GAGAL' : 'LULUS',
      evidence: 'GET /reports (pelatih) — sebelumnya 500 + stack trace, kini ' + status,
    });
    expect(status, '/reports masih 500').toBeLessThan(500);
  });

  test('G4 halaman 404 & route tak dikenal aman (tanpa stack trace)', async ({ browser }) => {
    const api = await requestFactory.newContext({ baseURL: BASE, storageState: statePath('admin') });
    const urls = ['/halaman-tidak-ada-qa-test', '/athletes/999999', '/taktik/999999', '/api/tidak-ada'];
    const detail = [];
    let ok = true;
    for (const url of urls) {
      const res = await api.get(url, { maxRedirects: 0, timeout: 30000 });
      const body = await res.text();
      const adaStackTrace = /Stack Trace/i.test(body) || /Whoops/i.test(body);
      const adaPath = /vendor[\\/]+laravel|[\\/]+app[\\/]+Http[\\/]+Controllers/i.test(body);
      if (adaStackTrace || adaPath) ok = false;
      detail.push(`${url}=${res.status()} stack=${adaStackTrace} path=${adaPath}`);
    }
    await api.dispose();
    note({
      id: 'G4',
      scenario: '404/route tak dikenal tidak membocorkan debug',
      role: 'admin',
      expected: '404 tanpa stack trace & tanpa path internal',
      actual: detail.join(' ; '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'GET 4 URL tak valid',
    });
    expect(ok, detail.join(' ; ')).toBe(true);
  });

  test('G5 akses langsung route terproteksi tanpa sesi (guest)', async ({ browser }) => {
    const tamu = await requestFactory.newContext({ baseURL: BASE });
    const detail = [];
    let ok = true;
    for (const url of ['/dashboard', '/athletes', '/mental', '/taktik', '/report', '/reports']) {
      const res = await tamu.get(url, { maxRedirects: 0 });
      const s = res.status();
      const lokasi = res.headers()['location'] || '-';
      if (s !== 302 && s !== 401 && s !== 403) ok = false;
      detail.push(`${url}=${s}→${lokasi}`);
    }
    await tamu.dispose();
    note({
      id: 'G5',
      scenario: 'Tamu membuka route terproteksi langsung via URL',
      role: 'guest',
      expected: 'dialihkan ke /login (302) atau 401/403 — bukan 200',
      actual: detail.join(' ; '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'APIRequestContext tanpa cookie',
    });
    expect(ok, detail.join(' ; ')).toBe(true);
  });
});
