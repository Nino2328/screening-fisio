const { test, expect } = require('@playwright/test');
const { note, shot, statePath, watchPage } = require('../lib');
const fs = require('fs');
const path = require('path');

const QA_FILE = path.join(__dirname, '..', 'qa-athletes.json');
const loadQA = () => (fs.existsSync(QA_FILE) ? JSON.parse(fs.readFileSync(QA_FILE, 'utf8')) : {});
const saveQA = (o) => fs.writeFileSync(QA_FILE, JSON.stringify(o, null, 2));

const PNG_1PX = Buffer.from(
  'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
  'base64'
);

async function openAddModal(page) {
  await page.goto('/athletes');
  await page.click('button[onclick="openAddAthlete()"]');
  await page.waitForSelector('#athleteModal:not(.hidden)');
}

async function fillAthleteModal(page, data) {
  if (data.name !== undefined) await page.fill('#fieldName', data.name);
  if (data.position !== undefined) await page.selectOption('#fieldPosition', data.position);
  if (data.birthPlace !== undefined) await page.fill('#fieldBirthPlace', data.birthPlace);
  if (data.birthDate !== undefined) await page.fill('#fieldBirthDate', data.birthDate);
  if (data.age !== undefined) await page.fill('#fieldAge', String(data.age));
  if (data.gender !== undefined) await page.selectOption('#fieldGender', data.gender);
  if (data.height !== undefined) await page.fill('#fieldHeight', String(data.height));
  if (data.weight !== undefined) await page.fill('#fieldWeight', String(data.weight));
  if (data.phone !== undefined) await page.fill('#fieldPhone', data.phone);
  if (data.photo !== undefined) await page.setInputFiles('#fieldPhoto', data.photo);
}

async function submitModal(page) {
  const [resp] = await Promise.all([
    page.waitForResponse((r) => r.request().method() === 'POST' && /\/athletes(\/\d+)?(\?|$)/.test(r.url())),
    page.click('#submitBtn'),
  ]);
  await page.waitForLoadState('networkidle').catch(() => {});
  return resp;
}

async function rowCount(page) {
  await page.goto('/athletes');
  await page.waitForLoadState('domcontentloaded');
  return page.locator('tbody tr').count();
}

async function hasAthlete(page, name) {
  await page.goto('/athletes');
  return (await page.locator('tr', { hasText: name }).count()) > 0;
}

/** Buat atlet bila belum ada (self-heal bila data terhapus di tengah run). */
async function ensureAthlete(page, data) {
  if (await hasAthlete(page, data.name)) return 'ada';
  await openAddModal(page);
  await fillAthleteModal(page, data);
  await submitModal(page);
  return (await hasAthlete(page, data.name)) ? 'dibuat' : 'gagal';
}

async function openEditFor(page, name) {
  await page.goto('/athletes');
  const row = page.locator('tr', { hasText: name }).first();
  await row.locator('button[onclick^="editAthlete"]').click();
  await page.waitForSelector('#athleteModal:not(.hidden)');
  await page.waitForFunction(() => (document.getElementById('fieldName').value || '').length > 0);
}

const SATU = { name: 'QA_TEST_Satu', position: 'Gelandang', birthPlace: 'QA Kota', birthDate: '2005-04-12', age: 21, gender: 'M', height: 176, weight: 70, phone: '081111111111' };

test.describe('C. Fitur atlet (admin & fisio)', () => {
  const qa = loadQA();

  test.beforeAll(async ({ browser }) => {
    note({ id: 'C beforeAll', scenario: 'Hook beforeAll skenario C dijalankan', role: 'admin', expected: 'sekali per run', actual: 'mulai', status: 'INFO', evidence: 'notes.json' });
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    page.on('dialog', (d) => d.accept());
    let deleted = 0;
    for (let i = 0; i < 25; i++) {
      await page.goto('/athletes');
      const row = page.locator('tr', { hasText: 'QA_TEST_' }).first();
      if ((await row.count()) === 0) break;
      const respP = page.waitForResponse((r) => r.request().method() === 'POST' && /\/athletes\/\d+/.test(r.url()), { timeout: 20000 });
      await row.locator('button[onclick^="deleteAthlete"]').click();
      if (await respP) deleted++;
      await page.waitForLoadState('networkidle').catch(() => {});
    }
    await ctx.close();
    note({ id: 'C beforeAll selesai', scenario: 'Bukti beforeAll kedua (deteksi worker restart)', role: 'admin', expected: '-', actual: `dihapus=${deleted}`, status: 'INFO', evidence: 'notes.json' });
  });

  test('C1 admin: tambah atlet QA_TEST_Satu tanpa foto', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'C1');
    const before = await rowCount(page);
    await openAddModal(page);
    await shot(page, 'C1-modal-tambah');
    await fillAthleteModal(page, SATU);
    const resp = await submitModal(page);
    const after = await page.locator('tbody tr').count();
    const html1 = await page.content();
    const marker = html1.includes('QA_TEST_Satu');
    const toast = html1.includes('Atlet berhasil ditambahkan');
    const { consoleMsgs, failed } = w.flush();
    const created = after === before + 1 && marker;
    qa.satu = { name: 'QA_TEST_Satu', created };
    saveQA(qa);
    note({ id: 'C1', scenario: 'Tambah atlet tanpa foto', role: 'admin', expected: '302, baris bertambah 1, toast sukses', actual: `POST ${resp.status()}, baris ${before}→${after}, tampil=${marker}, toast=${toast}, consoleErr=${consoleMsgs.length}, reqGagal=${failed.length}`, status: created && resp.status() === 302 ? 'LULUS' : 'GAGAL', evidence: 'screenshots/C1-modal-tambah.png' });
    await shot(page, 'C1-hasil');
    await ctx.close();
    expect(created, `baris ${before}→${after}, marker=${marker}`).toBe(true);
  });

  test('C2 fisio: tambah atlet QA_TEST_Dua dengan foto & verifikasi foto tersimpan', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('fisio'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const before = await rowCount(page);
    await openAddModal(page);
    await fillAthleteModal(page, { ...SATU, name: 'QA_TEST_Dua', position: 'Penyerang', birthPlace: 'QA Bandung', birthDate: '2004-09-10', age: 22, height: 180, weight: 74, phone: '082222222222', photo: { name: 'QA_TEST_Dua.png', mimeType: 'image/png', buffer: PNG_1PX } });
    const resp = await submitModal(page);
    const after = await page.locator('tbody tr').count();
    const created = after === before + 1;

    let photoPath = 'n/a';
    let labelHtml = 'n/a';
    if (created) {
      await openEditFor(page, 'QA_TEST_Dua');
      photoPath = await page.getAttribute('#fieldPhoto', 'data-current-photo');
      labelHtml = await page.evaluate(() => document.querySelector('#fieldPhoto + p')?.innerHTML || '');
      await shot(page, 'C2-modal-edit-foto');
    }
    qa.dua = { name: 'QA_TEST_Dua', created, photoPath };
    saveQA(qa);
    note({ id: 'C2', scenario: 'Tambah atlet dengan foto (fisio)', role: 'fisio', expected: 'tersimpan & path foto terisi di DB', actual: `POST ${resp.status()}, baris ${before}→${after}, photoPath=${photoPath}, label="${String(labelHtml).slice(0, 90)}"`, status: created && photoPath && photoPath.startsWith('uploads/athletes/') ? 'LULUS' : 'GAGAL', evidence: 'screenshots/C2-modal-edit-foto.png' });
    await ctx.close();
    expect(created).toBe(true);
    expect(String(photoPath)).toContain('uploads/athletes/');
  });

  for (const tc of [
    { id: 'C3', label: 'submit kosong', data: { name: '' }, expectReject: true },
    { id: 'C4', label: 'nama > 100 karakter', data: { name: 'QA_TEST_' + 'x'.repeat(110) }, expectReject: true },
    { id: 'C5', label: 'tanggal lahir masa depan', data: { name: 'QA_TEST_MasaDepan', birthDate: '2099-12-31' }, expectReject: true },
    { id: 'C6', label: 'file bukan gambar', data: { name: 'QA_TEST_BukanGambar', photo: { name: 'QA_TEST_bukan.txt', mimeType: 'text/plain', buffer: Buffer.from('bukan gambar') } }, expectReject: true },
    { id: 'C7', label: 'file > 2 MB', data: { name: 'QA_TEST_Kebesaran', photo: { name: 'QA_TEST_besar.png', mimeType: 'image/png', buffer: Buffer.concat([PNG_1PX, Buffer.alloc(2.2 * 1024 * 1024, 0)]) } }, expectReject: true },
  ]) {
    test(`${tc.id} validasi: ${tc.label}`, async ({ browser }) => {
      const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
      const page = await ctx.newPage();
      const w = watchPage(page, tc.id);
      const countBefore = await rowCount(page);
      await openAddModal(page);
      await page.evaluate(() => document.querySelectorAll('form [required]').forEach((e) => e.removeAttribute('required')));
      const base = { position: 'Bek', birthPlace: 'QA', age: 20, gender: 'M', height: 170, weight: 65, phone: '083333333333' };
      await fillAthleteModal(page, { ...base, ...tc.data });
      const resp = await submitModal(page);
      const status = resp.status();
      await page.waitForLoadState('domcontentloaded').catch(() => {});
      const countAfter = await page.locator('tbody tr').count();
      const html = await page.content();
      const created = countAfter > countBefore;
      const marker = (tc.data.name || '').slice(0, 20);
      const adaMarker = marker.length > 0 && html.includes(marker);
      const errVisible = await page.evaluate(() => {
        const box = document.getElementById('errorAlert');
        if (box && box.getClientRects().length > 0 && box.textContent.trim().length > 3) return true;
        const cands = [...document.querySelectorAll('.list-disc, .rounded-xl, .rounded-2xl, p, div')];
        return cands.some((e) => e.getClientRects().length > 0 && e.children.length === 0
          && /(wajib|harus diisi|minimal|maksimal|tidak valid|format file|berupa angka|setelah|sebelum)/i.test(e.textContent));
      });
      const { consoleMsgs, failed } = w.flush();
      const rejected = status === 302 && !created;
      const sesuaiHarapan = tc.expectReject ? rejected && errVisible : !rejected;
      note({
        id: tc.id, scenario: `Validasi ${tc.label}`, role: 'admin',
        expected: 'pesan validasi tampil & data tidak tersimpan, tanpa 500',
        actual: `POST ${status}, baris ${countBefore}→${countAfter}, tersimpan=${created}, adaMarker=${adaMarker}, pesanErrorTerlihat=${errVisible}, consoleErr=${consoleMsgs.length}, reqGagal=${failed.map((f) => f.status).join(',') || '-'}`,
        status: status >= 500 ? 'GAGAL' : (sesuaiHarapan ? 'LULUS' : 'GAGAL'),
        evidence: 'POST /athletes + GET /athletes + screenshots/' + tc.id + '-validasi.png',
      });
      await shot(page, `${tc.id}-validasi`);
      await ctx.close();
      expect(status).toBeLessThan(500);
      if (tc.expectReject) expect(created, `${tc.label} seharusnya ditolak tetapi tersimpan`).toBe(false);
      else expect(created, `${tc.label} seharusnya diterima`).toBe(true);
    });
  }

  test('C8 admin: edit atlet QA_TEST_Satu dan verifikasi tersimpan', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const heal = await ensureAthlete(page, SATU);
    await openEditFor(page, 'QA_TEST_Satu');
    const namaSebelum = await page.inputValue('#fieldName');
    await shot(page, 'C8-modal-edit');
    await page.fill('#fieldPhone', '089999999999');
    await page.fill('#fieldWeight', '71.5');
    const resp = await submitModal(page);
    await page.goto('/athletes');
    const tersimpan = (await page.content()).includes('089999999999');
    await openEditFor(page, 'QA_TEST_Satu');
    const phoneSetelah = await page.inputValue('#fieldPhone');
    const weightSetelah = await page.inputValue('#fieldWeight');
    const ok = phoneSetelah === '089999999999' && weightSetelah === '71.5';
    note({ id: 'C8', scenario: 'Edit atlet (ubah phone & berat)', role: 'admin', expected: 'perubahan tersimpan & terbaca ulang', actual: `heal=${heal}, POST ${resp.status()}, namaForm="${namaSebelum}", phoneDB="${phoneSetelah}", weightDB="${weightSetelah}", tampilDiTabel=${tersimpan}`, status: ok ? 'LULUS' : 'GAGAL', evidence: 'screenshots/C8-modal-edit.png' });
    await ctx.close();
    expect(ok).toBe(true);
  });

  test('C9 buka detail atlet QA_TEST_Satu (tampilan gender)', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const w = watchPage(page, 'C9');
    const heal = await ensureAthlete(page, SATU);
    await page.goto('/athletes');
    const row = page.locator('tr', { hasText: 'QA_TEST_Satu' }).first();
    await row.locator('a').first().click();
    await page.waitForLoadState('networkidle').catch(() => {});
    const url = page.url();
    const body = (await page.content()).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');
    const adaLaki = body.includes('Laki-laki');
    qa.satuId = (url.match(/\/athletes\/(\d+)/) || [])[1] || null;
    saveQA(qa);
    const { consoleMsgs, failed } = w.flush();
    await shot(page, 'C9-detail-atlet');
    note({ id: 'C9', scenario: 'Halaman detail atlet', role: 'admin', expected: '200, nama & gender "Laki-laki" tampil', actual: `heal=${heal}, url=${url.replace('http://127.0.0.1:8000', '')}, genderLakiLaki=${adaLaki}, consoleErr=${consoleMsgs.length}, reqGagal=${failed.map((f) => `${f.status} ${f.url}`).join(' | ') || '-'}`, status: adaLaki ? 'LULUS' : 'GAGAL', evidence: 'screenshots/C9-detail-atlet.png' });
    await ctx.close();
    expect(url).toContain('/athletes/');
    expect(adaLaki, 'gender tidak tampil sebagai Laki-laki').toBe(true);
  });

  test('C10 upload foto nama file khusus + buka modal Edit (uji XSS)', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const dialogs = [];
    page.on('dialog', async (d) => { dialogs.push(d.message()); await d.dismiss(); });
    const w = watchPage(page, 'C10');

    await openAddModal(page);
    await fillAthleteModal(page, { ...SATU, name: 'QA_TEST_XSS', position: 'Kiper', birthDate: '2006-01-01', phone: '085555555555', photo: { name: 'a"b\'c<d>.png', mimeType: 'image/png', buffer: PNG_1PX } });
    const resp = await submitModal(page);
    const status = resp.status();
    let errBody = '';
    if (status >= 500) {
      try {
        errBody = (await resp.text())
          .replace(/<style[\s\S]*?<\/style>/gi, ' ')
          .replace(/<[^>]+>/g, ' ')
          .replace(/\s+/g, ' ')
          .trim()
          .slice(0, 700);
      } catch (e) {
        errBody = 'gagal membaca body: ' + e.message;
      }
    }
    await page.goto('/athletes');
    const created = (await page.locator('tr', { hasText: 'QA_TEST_XSS' }).count()) > 0;

    let sinkInfo = 'tidak ada atlet/foto untuk dibuka';
    let injected = false;
    if (created) {
      await openEditFor(page, 'QA_TEST_XSS');
      await page.waitForTimeout(600);
      sinkInfo = await page.evaluate(() => {
        const label = document.querySelector('#fieldPhoto + p');
        return JSON.stringify({
          labelHtml: label ? label.innerHTML.slice(0, 240) : null,
          adaElemenD: !!document.querySelector('d'),
          adaElemenImgPalsu: !!document.querySelector('#fieldPhoto + p img'),
          photoPath: document.querySelector('#fieldPhoto')?.getAttribute('data-current-photo') || '',
        });
      });
      injected = sinkInfo.includes('"adaElemenD":true') || sinkInfo.includes('"adaElemenImgPalsu":true');
      await shot(page, 'C10-modal-edit-xss');
    }
    const { consoleMsgs } = w.flush();
    qa.xss = { name: 'QA_TEST_XSS', created };
    saveQA(qa);
    note({ id: 'C10', scenario: 'Upload foto bernama a"b\'c<d>.png lalu buka modal Edit', role: 'admin', expected: 'tanpa 500, tanpa alert, tanpa HTML tersuntik', actual: `POST ${status}, tersimpan=${created}, dialogs=${JSON.stringify(dialogs)}, sink=${sinkInfo}, injected=${injected}, consoleErr=${consoleMsgs.map((c) => c.text).join(' | ').slice(0, 160) || '-'}, ERRBODY="${errBody}"`, status: status >= 500 || injected || dialogs.length ? 'GAGAL' : 'LULUS', evidence: status >= 500 ? 'POST /athletes → 500 (lihat ERRBODY)' : 'screenshots/C10-modal-edit-xss.png' });
    await ctx.close();
    expect(status, 'upload nama file khusus memicu 500').toBeLessThan(500);
  });

  test('C11 perbandingan: upload nama aman xss_test.png → sukses & tanpa injeksi HTML', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    const dialogs = [];
    page.on('dialog', async (d) => { dialogs.push(d.message()); await d.dismiss(); });
    const w = watchPage(page, 'C11');
    const before = await rowCount(page);

    await openAddModal(page);
    await fillAthleteModal(page, { ...SATU, name: 'QA_TEST_Aman', position: 'Bek', birthPlace: 'QA', birthDate: '2006-01-01', age: 20, height: 170, weight: 66, phone: '086666666666', photo: { name: 'xss_test.png', mimeType: 'image/png', buffer: PNG_1PX } });
    const resp = await submitModal(page);
    const status = resp.status();
    const after = await page.locator('tbody tr').count();
    const created = after > before;

    let sink = 'tidak dibuka';
    let injected = false;
    let photoPath = '';
    if (status < 400 && created) {
      await openEditFor(page, 'QA_TEST_Aman');
      await page.waitForTimeout(500);
      const s = await page.evaluate(() => {
        const label = document.querySelector('#fieldPhoto + p');
        return {
          html: label ? label.innerHTML.slice(0, 200) : null,
          adaElemenD: !!document.querySelector('d'),
          adaImgPalsu: !!document.querySelector('#fieldPhoto + p img'),
          path: document.querySelector('#fieldPhoto')?.getAttribute('data-current-photo') || '',
        };
      });
      sink = JSON.stringify(s);
      injected = s.adaElemenD || s.adaImgPalsu;
      photoPath = s.path;
      await shot(page, 'C11-modal-edit-aman');
    }
    const { consoleMsgs } = w.flush();
    const ok = status < 400 && created && !injected;
    note({
      id: 'C11',
      scenario: 'Upload nama aman xss_test.png (pembanding C10)',
      role: 'admin',
      expected: '302 tersimpan, modal Edit menampilkan path foto, tanpa injeksi',
      actual: `POST ${status}, baris ${before}→${after}, tersimpan=${created}, photoPath=${photoPath}, injected=${injected}, dialogs=${JSON.stringify(dialogs)}, sink=${sink}, consoleErr=${consoleMsgs.length}`,
      status: ok ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/C11-modal-edit-aman.png',
    });
    await ctx.close();
    expect(status, `upload nama aman gagal: HTTP ${status}`).toBeLessThan(400);
    expect(created).toBe(true);
    expect(injected, 'HTML tersuntik pada nama aman').toBe(false);
  });
});
