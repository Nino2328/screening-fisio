const { test, expect } = require('@playwright/test');
const { note, shot, statePath, watchPage } = require('../lib');

const BASE = 'http://127.0.0.1:8000';

/** Baca kartu overview berdasarkan labelnya. */
async function overviewCard(page, label) {
  const card = page.locator('article.overview-card', { has: page.locator('.overview-label', { hasText: label }) }).first();
  if ((await card.count()) === 0) return null;
  const strong = card.locator('strong').first();
  return (await strong.count()) ? (await strong.innerText()).trim() : null;
}

test.describe('F. Dashboard', () => {
  test('F1 admin: /dashboard tampil, heading & kartu overview terisi', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'F1');
    const res = await page.goto('/dashboard');
    const status = res ? res.status() : 0;
    const heading = await page.locator('#dashboard-heading').innerText().catch(() => '');
    const atletAktif = await overviewCard(page, 'Atlet aktif');
    const kelengkapan = await overviewCard(page, 'Kelengkapan');
    const jenisTes = await overviewCard(page, 'Modul screening');
    const adaMental = await overviewCard(page, 'Mental hari ini');
    const adaStatFisik = await page.getByText('Rata-rata YoYo').count();
    const { consoleMsgs, failed } = w.flush();
    const angkaOk = Number(atletAktif) > 0 && Number(String(kelengkapan).replace(/\D/g, '')) <= 100;
    const ok = status === 200 && heading.includes('Screening Fisioterapi') && angkaOk && jenisTes === '12' && adaMental !== null && adaStatFisik > 0;
    note({
      id: 'F1',
      scenario: 'Dashboard admin',
      role: 'admin',
      expected: '200, heading "Screening Fisioterapi Sepak Bola", kartu terisi (modul=12, atlet>0, kelengkapan<=100), 4 kartu tes fisik tampil',
      actual: `HTTP ${status}, heading="${heading}", jenisTes=${jenisTes}, atletAktif=${atletAktif}, kelengkapan=${kelengkapan}, mentalHariIni=${adaMental}, kartuFisik=${adaStatFisik}, consoleErr=${consoleMsgs.filter((c) => c.type !== 'warning').length}, reqGagal=${failed.map((f) => f.status).join(',') || '-'}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/F1-dashboard-admin.png',
    });
    await shot(page, 'F1-dashboard-admin');
    await ctx.close();
    expect(status).toBe(200);
    expect(heading).toContain('Screening Fisioterapi');
    expect(angkaOk, `atlet=${atletAktif} kelengkapan=${kelengkapan}`).toBe(true);
    expect(jenisTes, 'Modul screening untuk admin').toBe('12');
    expect(adaStatFisik, 'kartu stat fisik tampil').toBeGreaterThan(0);
  });

  test('F2 pelatih: /dashboard mode pelatih (2 jenis assessment, tanpa tes fisik)', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('pelatih'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'F2');
    const res = await page.goto('/dashboard');
    const status = res ? res.status() : 0;
    const heading = await page.locator('#dashboard-heading').innerText().catch(() => '');
    const jenisAssessment = await overviewCard(page, 'Jenis assessment');
    const kartuFisik = await page.getByText('Rata-rata YoYo').count();
    const kartuMental = await page.getByText('Rata-rata mental').count();
    const { consoleMsgs } = w.flush();
    const ok = status === 200 && heading.includes('Assessment Pelatih') && jenisAssessment === '2' && kartuFisik === 0 && kartuMental > 0;
    note({
      id: 'F2',
      scenario: 'Dashboard pelatih (hanya mental+taktik)',
      role: 'pelatih',
      expected: '200, heading "Assessment Pelatih Sepak Bola", jenis assessment=2, kartu tes fisik tidak tampil',
      actual: `HTTP ${status}, heading="${heading}", jenisAssessment=${jenisAssessment}, kartuFisik=${kartuFisik}, kartuMental=${kartuMental}, consoleErr=${consoleMsgs.filter((c) => c.type !== 'warning').length}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/F2-dashboard-pelatih.png',
    });
    await shot(page, 'F2-dashboard-pelatih');
    await ctx.close();
    expect(status).toBe(200);
    expect(heading).toContain('Assessment Pelatih');
    expect(jenisAssessment).toBe('2');
    expect(kartuFisik, 'kartu tes fisik bocor ke pelatih').toBe(0);
  });

  test('F3 konsistensi: nilai "Atlet aktif" identik di 3 role', async ({ browser }) => {
    const nilai = {};
    const detail = [];
    for (const role of ['admin', 'fisio', 'pelatih']) {
      const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath(role) });
      const page = await ctx.newPage();
      const res = await page.goto('/dashboard');
      nilai[role] = await overviewCard(page, 'Atlet aktif');
      detail.push(`${role}=${res ? res.status() : 0}/${nilai[role]}`);
      await ctx.close();
    }
    const unik = [...new Set(Object.values(nilai))];
    const ok = unik.length === 1 && Number(unik[0]) > 0;
    note({
      id: 'F3',
      scenario: 'Konsistensi angka dashboard antar role',
      role: 'admin + fisio + pelatih',
      expected: 'nilai Atlet aktif sama untuk semua role dan > 0',
      actual: detail.join(', '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'GET /dashboard × 3',
    });
    expect(ok, detail.join(', ')).toBe(true);
  });

  test('F4 navigasi dari dashboard (sidebar) ke halaman utama', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'F4');
    await page.goto('/dashboard');
    const hasil = [];
    let ok = true;
    for (const [nama, url] of [['Data Atlet', '/athletes'], ['Dashboard', '/dashboard']]) {
      const link = page.getByRole('link', { name: nama }).first();
      if ((await link.count()) === 0) { hasil.push(`${nama}: link tidak ada`); ok = false; continue; }
      await link.click();
      await page.waitForLoadState('networkidle').catch(() => {});
      const status = page.url();
      const cocok = status.includes(url);
      if (!cocok) ok = false;
      hasil.push(`${nama}→${status.replace(BASE, '')}${cocok ? ' ✓' : ' ✗'}`);
    }
    const { consoleMsgs } = w.flush();
    note({
      id: 'F4',
      scenario: 'Navigasi sidebar dari dashboard',
      role: 'admin',
      expected: 'link sidebar menuju halaman yang benar (200)',
      actual: `${hasil.join(' ; ')}, consoleErr=${consoleMsgs.filter((c) => c.type !== 'warning').length}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshot F4-nav.png',
    });
    await shot(page, 'F4-nav');
    await ctx.close();
    expect(ok, hasil.join(' ; ')).toBe(true);
  });
});

test.describe('H. Console, viewport & dependensi eksternal', () => {
  const HALAMAN = ['/dashboard', '/athletes', '/mental', '/taktik', '/report'];

  test('H1 tidak ada error console (hanya warning CDN) pada 5 halaman utama', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const ringkasan = [];
    let jumlahError = 0;
    let jumlahWarning = 0;
    const contohError = [];
    for (const url of HALAMAN) {
      const w = watchPage(page, 'H1');
      const res = await page.goto(url);
      await page.waitForLoadState('networkidle').catch(() => {});
      const { consoleMsgs, failed } = w.flush();
      const errors = consoleMsgs.filter((c) => c.type === 'error');
      const warnings = consoleMsgs.filter((c) => c.type === 'warning');
      jumlahError += errors.length;
      jumlahWarning += warnings.length;
      errors.slice(0, 2).forEach((e) => contohError.push(`${url}: ${e.text.slice(0, 120)}`));
      const errReq = failed.filter((f) => f.status >= 400 && !String(f.url).includes('favicon'));
      ringkasan.push(`${url}[${res ? res.status() : 0}] err=${errors.length} warn=${warnings.length} reqGagal=${errReq.map((f) => f.status).join(',') || '-'}`);
    }
    await ctx.close();
    const ok = jumlahError === 0;
    note({
      id: 'H1',
      scenario: 'Konsol browser bersih (5 halaman utama)',
      role: 'admin',
      expected: '0 error console; warning CDN dicatat terpisah',
      actual: `${ringkasan.join(' ; ')} || totalError=${jumlahError}, totalWarning=${jumlahWarning}, contohError=${contohError.join(' | ') || '-'}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'konsol Playwright per halaman',
    });
    expect(jumlahError, contohError.join(' | ')).toBe(0);
  });

  test('H2 viewport mobile 375x812: tanpa overflow horizontal di 3 halaman', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin'), viewport: { width: 375, height: 812 } });
    const page = await ctx.newPage();
    const detail = [];
    let ok = true;
    for (const url of ['/dashboard', '/athletes', '/report']) {
      const res = await page.goto(url);
      await page.waitForLoadState('networkidle').catch(() => {});
      const m = await page.evaluate(() => {
        const de = document.documentElement;
        const lebar = [];
        document.querySelectorAll('body *').forEach((el) => {
          const r = el.getBoundingClientRect();
          if (r.right > window.innerWidth + 2 && r.width > 40) {
            lebar.push(`${el.tagName.toLowerCase()}.${String(el.className || '').split(' ')[0]}:${Math.round(r.right)}`);
          }
        });
        return { scrollWidth: de.scrollWidth, innerWidth: window.innerWidth, offender: lebar.slice(0, 3) };
      });
      const adaOverflow = m.scrollWidth > m.innerWidth + 2;
      if (adaOverflow) ok = false;
      detail.push(`${url}[${res ? res.status() : 0}] scrollW=${m.scrollWidth}/innerW=${m.innerWidth}${adaOverflow ? ' OVERFLOW' : ''} ${m.offender.join(', ')}`);
      await shot(page, `H2-mobile-${url.replace(/\W+/g, '-')}`);
    }
    await ctx.close();
    note({
      id: 'H2',
      scenario: 'Layout mobile 375x812',
      role: 'admin',
      expected: 'tidak ada scroll horizontal pada document (tiap halaman 200)',
      actual: detail.join(' ; '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/H2-mobile-*.png',
    });
    expect(ok, detail.join(' ; ')).toBe(true);
  });

  test('H3 viewport desktop 1366x768: render penuh tanpa overflow', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const detail = [];
    let ok = true;
    for (const url of ['/dashboard', '/athletes', '/report']) {
      const res = await page.goto(url);
      await page.waitForLoadState('networkidle').catch(() => {});
      const m = await page.evaluate(() => ({ sw: document.documentElement.scrollWidth, iw: window.innerWidth }));
      const adaOverflow = m.sw > m.iw + 2;
      if (adaOverflow) ok = false;
      detail.push(`${url}[${res ? res.status() : 0}] scrollW=${m.sw}/innerW=${m.iw}${adaOverflow ? ' OVERFLOW' : ''}`);
      await shot(page, `H3-desktop-${url.replace(/\W+/g, '-')}`);
    }
    await ctx.close();
    note({
      id: 'H3',
      scenario: 'Layout desktop 1366x768',
      role: 'admin',
      expected: 'tanpa overflow horizontal, halaman 200',
      actual: detail.join(' ; '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/H3-desktop-*.png',
    });
    expect(ok, detail.join(' ; ')).toBe(true);
  });

  test('H4 ketergantungan CDN cdn.tailwindcss.com', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: BASE, storageState: statePath('admin') });
    const page = await ctx.newPage();
    const host = {};
    page.on('request', (r) => {
      try {
        const h = new URL(r.url()).host;
        if (h && h !== '127.0.0.1:8000') host[h] = (host[h] || 0) + 1;
      } catch (e) { /* url tidak valid */ }
    });
    const w = watchPage(page, 'H4');
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle').catch(() => {});
    const { consoleMsgs } = w.flush();
    const cdnCount = host['cdn.tailwindcss.com'] || 0;
    const warningCdn = consoleMsgs.find((c) => /cdn\.tailwindcss\.com/.test(c.text));
    await ctx.close();
    const eksternal = Object.entries(host).map(([h, n]) => `${h}(${n})`).join(', ') || '-';
    const ok = cdnCount > 0 && !!warningCdn;
    note({
      id: 'H4',
      scenario: 'Dependensi eksternal CDN di halaman aplikasi',
      role: 'admin',
      expected: 'mencatat host eksternal yang dipanggil + warning terkait production',
      actual: `hostEksternal=${eksternal}, requestsKeCdnTailwind=${cdnCount}, warningCdn="${warningCdn ? warningCdn.text.slice(0, 160) : '-'}"`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'lib.js layouts/app.blade.php:11 (cdn.tailwindcss.com)',
    });
    expect(cdnCount, 'CDN tidak dipanggil?').toBeGreaterThan(0);
  });
});
