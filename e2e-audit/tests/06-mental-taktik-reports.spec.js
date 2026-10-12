const { test, expect, request: requestFactory } = require('@playwright/test');
const { note, shot, statePath, watchPage, fillForm, mainForm } = require('../lib');
const fs = require('fs');
const path = require('path');

const QA_FILE = path.join(__dirname, '..', 'qa-athletes.json');
const loadQA = () => (fs.existsSync(QA_FILE) ? JSON.parse(fs.readFileSync(QA_FILE, 'utf8')) : {});
const saveQA = (o) => fs.writeFileSync(QA_FILE, JSON.stringify(o, null, 2));

const SATU = {
  name: 'QA_TEST_Satu',
  position: 'Gelandang',
  birthPlace: 'QA Kota',
  birthDate: '2005-04-12',
  age: 21,
  gender: 'M',
  height: 176,
  weight: 70,
  phone: '081111111111',
};

async function ensureAthleteId(page) {
  await page.goto('/athletes');
  let row = page.locator('tr', { hasText: 'QA_TEST_Satu' }).first();
  if ((await row.count()) === 0) {
    await page.click('button[onclick="openAddAthlete()"]');
    await page.waitForSelector('#athleteModal:not(.hidden)');
    await page.fill('#fieldName', SATU.name);
    await page.selectOption('#fieldPosition', SATU.position);
    await page.fill('#fieldBirthPlace', SATU.birthPlace);
    await page.fill('#fieldBirthDate', SATU.birthDate);
    await page.fill('#fieldAge', String(SATU.age));
    await page.selectOption('#fieldGender', SATU.gender);
    await page.fill('#fieldHeight', String(SATU.height));
    await page.fill('#fieldWeight', String(SATU.weight));
    await page.fill('#fieldPhone', SATU.phone);
    const respP = page.waitForResponse(
      (r) => r.request().method() === 'POST' && /\/athletes(\?|$)/.test(r.url())
    );
    await page.click('#submitBtn');
    await respP.catch(() => {});
    await page.waitForLoadState('networkidle').catch(() => {});
    await page.goto('/athletes');
    row = page.locator('tr', { hasText: 'QA_TEST_Satu' }).first();
  }
  const onclick = await row.locator('button[onclick^="editAthlete"]').getAttribute('onclick');
  const m = String(onclick).match(/editAthlete\((\d+)\)/);
  return m ? m[1] : null;
}

async function submitMain(page, urlPart) {
  const btn = mainForm(page).locator('button[type="submit"], input[type="submit"]').first();
  const [resp] = await Promise.all([
    page.waitForResponse((r) => r.request().method() === 'POST' && r.url().includes(urlPart)),
    btn.click(),
  ]);
  await page.waitForLoadState('networkidle').catch(() => {});
  return resp;
}

/** Ambil id assessment tertentu dari halaman index berdasarkan teks atlet. */
async function findAssessmentId(page, basePath, needle) {
  await page.goto(basePath);
  const row = page.locator('tr', { hasText: needle }).first();
  if ((await row.count()) === 0) return null;
  const href = await row.locator('a[href*="' + basePath + '/"]').first().getAttribute('href');
  const m = String(href).match(new RegExp(basePath + '/(\\d+)'));
  return m ? m[1] : null;
}

async function describeDownload(api, url, role) {
  const res = await api.get(url, { maxRedirects: 0, timeout: 60000 });
  const status = res.status();
  const headers = res.headers();
  const buf = await res.body().catch(() => Buffer.alloc(0));
  const head = buf.slice(0, 300).toString('utf8').replace(/\r/g, '\\r').replace(/\n/g, ' | ');
  return {
    role,
    url,
    status,
    contentType: headers['content-type'] || '-',
    disposition: headers['content-disposition'] || '-',
    bytes: buf.length,
    head,
  };
}

test.describe('E. Mental/Taktik, export laporan & IDOR', () => {
  test('E1 pelatih: buat assessment mental atas nama QA_TEST_', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'E1');
    const adminCtx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin') });
    const athleteId = await ensureAthleteId(await adminCtx.newPage());
    await adminCtx.close();

    await page.goto(`/mental/create?athlete_id=${athleteId}`);
    await page.waitForLoadState('domcontentloaded');
    await fillForm(page, {
      values: {
        overall_assessment: 'QA_TEST_ penilaian mental awal',
        recommendations: 'QA_TEST_ rekomendasi awal',
      },
    });
    const resp = await submitMain(page, '/mental');
    const status = resp.status();
    const id = await findAssessmentId(page, '/mental', /QA_TEST_/);
    const { consoleMsgs, failed } = w.flush();
    const qa = loadQA();
    qa.mentalId = id;
    saveQA(qa);
    const ok = status === 302 && !!id;
    note({
      id: 'E1',
      scenario: 'Buat assessment mental (pelatih)',
      role: 'pelatih',
      expected: '302, muncul di daftar /mental',
      actual: `POST ${status}, id=${id}, consoleErr=${consoleMsgs.length}, reqGagal=${failed.map((f) => f.status).join(',') || '-'}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/E1-mental-create.png',
    });
    await shot(page, 'E1-mental-create');
    await ctx.close();
    expect(ok, `POST ${status}, id=${id}`).toBe(true);
  });

  test('E2 pelatih: lihat & ubah assessment mental sendiri', async ({ browser }) => {
    const qa = loadQA();
    const id = qa.mentalId;
    expect(id, 'id mental belum ada (E1 gagal?)').toBeTruthy();
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'E2');

    const showRes = await page.goto(`/mental/${id}`);
    const showStatus = showRes ? showRes.status() : 0;
    const showText = (await page.content()).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
    const adaIsi = showText.includes('QA_TEST_ penilaian mental awal');
    await shot(page, 'E2-mental-show');

    await page.goto(`/mental/${id}/edit`);
    await page.waitForLoadState('domcontentloaded');
    await fillForm(page, { values: { overall_assessment: 'QA_TEST_ penilaian mental UBAHAN' } });
    const resp = await submitMain(page, '/mental/' + id);
    const updateStatus = resp.status();

    await page.goto(`/mental/${id}`);
    const teksSetelah = (await page.content()).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
    const tersimpan = teksSetelah.includes('QA_TEST_ penilaian mental UBAHAN');
    const { consoleMsgs } = w.flush();
    const ok = showStatus === 200 && adaIsi && updateStatus === 302 && tersimpan;
    note({
      id: 'E2',
      scenario: 'Lihat + ubah assessment mental',
      role: 'pelatih',
      expected: 'GET 200 & isi tampil; PUT 302 & perubahan tersimpan',
      actual: `show=${showStatus}, isiAwalTampil=${adaIsi}, PUT ${updateStatus}, tersimpanSetelahUbah=${tersimpan}, consoleErr=${consoleMsgs.length}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/E2-mental-show.png',
    });
    await ctx.close();
    expect(showStatus).toBe(200);
    expect(updateStatus).toBe(302);
    expect(tersimpan).toBe(true);
  });

  test('E3 admin: buat assessment taktik atas nama QA_TEST_', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'E3');
    const athleteId = await ensureAthleteId(page);

    await page.goto(`/taktik/create?athlete_id=${athleteId}`);
    await page.waitForLoadState('domcontentloaded');
    await fillForm(page, {
      values: {
        overall_assessment: 'QA_TEST_ analisa taktik awal',
        recommendations: 'QA_TEST_ saran taktik',
        formation_notes: 'QA_TEST_ catatan formasi 4-3-3',
      },
    });
    const resp = await submitMain(page, '/taktik');
    const status = resp.status();
    const id = await findAssessmentId(page, '/taktik', /QA_TEST_/);
    const { consoleMsgs } = w.flush();
    const qa = loadQA();
    qa.taktikId = id;
    saveQA(qa);
    const ok = status === 302 && !!id;
    note({
      id: 'E3',
      scenario: 'Buat assessment taktik (admin, pemilik = admin)',
      role: 'admin',
      expected: '302, muncul di daftar /taktik',
      actual: `POST ${status}, id=${id}, consoleErr=${consoleMsgs.length}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/E3-taktik-create.png',
    });
    await shot(page, 'E3-taktik-create');
    await ctx.close();
    expect(ok, `POST ${status}, id=${id}`).toBe(true);
  });

  test('E4 IDOR: pelatih buka/ubah/hapus taktik milik admin → ditolak 403', async ({ browser }) => {
    const qa = loadQA();
    const id = qa.taktikId;
    expect(id, 'id taktik belum ada').toBeTruthy();

    const pageCtx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih') });
    const page = await pageCtx.newPage();
    await page.goto('/taktik');
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    await pageCtx.close();

    const api = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih') });
    const getRes = await api.get(`/taktik/${id}`, { maxRedirects: 0 });
    const putRes = await api.put(`/taktik/${id}`, { form: { _token: token, _method: 'PUT', overall_assessment: 'QA_TEST_IDOR_HACK' }, maxRedirects: 0 });
    const delRes = await api.delete(`/taktik/${id}`, { form: { _token: token }, maxRedirects: 0 });
    await api.dispose();

    const adminApi = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin') });
    const ownerView = await adminApi.get(`/taktik/${id}`);
    const ownerText = (await ownerView.text()).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
    await adminApi.dispose();

    const tidakBerubah = ownerText.includes('QA_TEST_ analisa taktik awal') && !ownerText.includes('QA_TEST_IDOR_HACK');
    const ok = getRes.status() === 403 && putRes.status() === 403 && delRes.status() === 403 && tidakBerubah;
    note({
      id: 'E4',
      scenario: 'IDOR: pelatih akses assessment milik admin',
      role: 'pelatih',
      expected: 'GET/PUT/DELETE → 403, data pemilik tidak berubah',
      actual: `GET ${getRes.status()}, PUT ${putRes.status()}, DELETE ${delRes.status()}, dataPemilikTidakBerubah=${tidakBerubah}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'API request dengan token CSRF sah',
    });
    expect(getRes.status(), 'GET taktik orang lain').toBe(403);
    expect(putRes.status(), 'PUT taktik orang lain').toBe(403);
    expect(delRes.status(), 'DELETE taktik orang lain').toBe(403);
    expect(tidakBerubah, 'data pemilik berubah setelah ditolak').toBe(true);
  });

  test('E5 export dari /reports (pelatih): halaman, PDF, CSV', async ({ browser }) => {
    const api = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih') });
    const hasil = [];
    for (const url of ['/reports', '/reports/export/pdf', '/reports/export/excel']) {
      const d = await describeDownload(api, url, 'pelatih');
      hasil.push(d);
      note({
        id: `E5 ${url}`,
        scenario: `Export/akses ${url}`,
        role: 'pelatih',
        expected: '200 dengan file berisi kolom + baris data',
        actual: `HTTP ${d.status}, type=${d.contentType}, disp=${d.disposition}, bytes=${d.bytes}, isi="${d.head.slice(0, 220)}"`,
        status: d.status === 200 && d.bytes > 0 ? 'LULUS' : 'GAGAL',
        evidence: `GET ${url}`,
      });
    }
    await api.dispose();
    const semuaOk = hasil.every((h) => h.status === 200 && h.bytes > 0);
    expect(semuaOk, JSON.stringify(hasil.map((h) => ({ u: h.url, s: h.status, b: h.bytes })))).toBe(true);
  });

  test('E6 export dari /report (fisio): halaman, PDF, Excel', async ({ browser }) => {
    const api = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('fisio') });
    const hasil = [];
    for (const url of ['/report', '/report/export/pdf', '/report/export/excel']) {
      const d = await describeDownload(api, url, 'fisio');
      hasil.push(d);
      note({
        id: `E6 ${url}`,
        scenario: `Export/akses ${url}`,
        role: 'fisio',
        expected: '200, file terunduh (PDF valid, Excel berisi kolom)',
        actual: `HTTP ${d.status}, type=${d.contentType}, disp=${d.disposition}, bytes=${d.bytes}, isi="${d.head.slice(0, 220)}"`,
        status: d.status === 200 && d.bytes > 0 ? 'LULUS' : 'GAGAL',
        evidence: `GET ${url}`,
      });
    }
    await api.dispose();

    // unduhan lewat UI (tombol Export Excel di /report)
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('fisio'), viewport: { width: 1366, height: 768 }, acceptDownloads: true });
    const page = await ctx.newPage();
    await page.goto('/report');
    let uiInfo = 'tombol tidak ditemukan';
    const btn = page.getByRole('link', { name: /Export Excel/i }).or(page.getByRole('button', { name: /Export Excel/i })).first();
    if ((await btn.count()) > 0) {
      try {
        const [dl] = await Promise.all([page.waitForEvent('download', { timeout: 30000 }), btn.click()]);
        const p = await dl.path();
        const size = p ? fs.statSync(p).size : 0;
        uiInfo = `suggestedFilename=${dl.suggestedFilename()}, bytes=${size}`;
      } catch (e) {
        uiInfo = 'gagal: ' + e.message.slice(0, 120);
      }
    }
    note({
      id: 'E6 UI export Excel',
      scenario: 'Klik tombol Export Excel di /report (fisio)',
      role: 'fisio',
      expected: 'file terunduh dengan nama & isi wajar',
      actual: uiInfo,
      status: /bytes=\d+/.test(uiInfo) && !/bytes=0/.test(uiInfo) ? 'LULUS' : 'GAGAL',
      evidence: 'screenshot E6-report-fisio.png',
    });
    await shot(page, 'E6-report-fisio');
    await ctx.close();

    const semuaOk = hasil.every((h) => h.status === 200 && h.bytes > 0);
    expect(semuaOk, JSON.stringify(hasil.map((h) => ({ u: h.url, s: h.status, b: h.bytes })))).toBe(true);
  });

  test('E7 halaman laporan setelah data sendiri ada (pelatih & fisio)', async ({ browser }) => {
    const pelatih = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih') });
    const fisio = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('fisio') });
    const p = await pelatih.get('/reports', { maxRedirects: 0 });
    const f = await fisio.get('/report', { maxRedirects: 0 });
    let pBody = '';
    if (p.status() >= 500) {
      pBody = (await p.text()).replace(/<style[\s\S]*?<\/style>/gi, ' ').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 400);
    }
    await pelatih.dispose();
    await fisio.dispose();
    const ok = p.status() === 200 && f.status() === 200;
    note({
      id: 'E7',
      scenario: 'Halaman laporan dengan data sendiri',
      role: 'pelatih + fisio',
      expected: '/reports dan /report → 200',
      actual: `/reports=${p.status()} :: ${pBody}, /report=${f.status()}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'GET /reports & /report',
    });
    expect(p.status(), 'GET /reports (pelatih)').toBe(200);
    expect(f.status(), 'GET /report (fisio)').toBe(200);
  });

  test('E8 hapus assessment mental & taktik QA_TEST_', async ({ browser }) => {
    const qa = loadQA();
    const hasil = [];
    for (const [role, basePath, id] of [
      ['pelatih', '/mental', qa.mentalId],
      ['admin', '/taktik', qa.taktikId],
    ]) {
      if (!id) { hasil.push(`${basePath}: id tidak ada`); continue; }
      const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath(role) });
      const page = await ctx.newPage();
      page.on('dialog', (d) => d.accept());
      await page.goto(`${basePath}/${id}`);
      const delBtn = page.locator('form[action*="' + basePath + '"] button[type="submit"]').first();
      let delStatus = 0;
      if ((await delBtn.count()) > 0) {
        const respP = page.waitForResponse((r) => r.request().method() === 'POST' && r.url().includes(`${basePath}/${id}`), { timeout: 20000 });
        await delBtn.click();
        const resp = await respP.catch(() => null);
        delStatus = resp ? resp.status() : 0;
        await page.waitForLoadState('networkidle').catch(() => {});
      }
      const check = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath(role) });
      const after = await check.get(`${basePath}/${id}`, { maxRedirects: 0 });
      await check.dispose();
      hasil.push(`${basePath}/${id}: DELETE ${delStatus}, GET setelah=${after.status()}`);
      await ctx.close();
    }
    const ok = hasil.every((h) => /GET setelah=404/.test(h));
    note({
      id: 'E8',
      scenario: 'Hapus assessment mental & taktik',
      role: 'pelatih + admin',
      expected: 'terhapus (GET setelah → 404)',
      actual: hasil.join(' ; '),
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'UI delete + GET ulang',
    });
    expect(ok, hasil.join(' ; ')).toBe(true);
  });
});
