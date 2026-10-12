const { test, expect } = require('@playwright/test');
const { note, shot, statePath, watchPage, fillForm, mainForm } = require('../lib');

const FORMS = [
  { key: 'endurance', rating: null },
  { key: 'speed', rating: 'Kurang Sekali' },
  { key: 'agility', rating: null },
  { key: 'strength', rating: 'Sempurna' },
  { key: 'power', rating: null },
  { key: 'accuracy', rating: 'Weak' },
  { key: 'reaction', rating: null },
  { key: 'coordination', rating: null },
  { key: 'flexibility', rating: 'Fair' },
  { key: 'balance', rating: null },
];

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

/** Id atlet QA_TEST_ yang dipilih fillForm (opsi pertama pada dropdown form tes). */
async function idAtletFormTes(page) {
  await page.goto('/forms/endurance');
  const sel = page.locator('form select[name="athlete_id"]').first();
  await sel.waitFor({ timeout: 20000 }).catch(() => {});
  const opt = sel.locator('option', { hasText: 'QA_TEST_' }).first();
  if ((await opt.count()) === 0) return null;
  return await opt.getAttribute('value');
}

/** Self-heal: pastikan QA_TEST_Satu ada, kembalikan id-nya. */
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

async function detectErrorVisible(page) {
  return page.evaluate(() => {
    const cands = [...document.querySelectorAll('.list-disc, p, div, span')];
    return cands.some(
      (e) =>
        e.getClientRects().length > 0 &&
        e.children.length === 0 &&
        /(wajib|harus diisi|minimal|maksimal|tidak valid|harus berupa|field)/i.test(e.textContent)
    );
  });
}

test.describe('D. Tes fisik — 10 form (fisio)', () => {
  FORMS.forEach((f, idx) => {
    test(`D${idx + 1} form ${f.key}${f.rating ? ` — rating "${f.rating}"` : ''}`, async ({ browser }) => {
      const ctx = await browser.newContext({
        baseURL: 'http://127.0.0.1:8000',
        storageState: statePath('fisio'),
        viewport: { width: 1366, height: 768 },
      });
      const page = await ctx.newPage();
      const w = watchPage(page, `D-${f.key}`);
      const athleteId = await ensureAthleteId(page);
      expect(athleteId, 'QA_TEST_Satu tidak bisa dibuat').toBeTruthy();

      await page.goto(`/forms/${f.key}?athlete_id=${athleteId}`);
      await page.waitForLoadState('domcontentloaded');
      const sudahDipilih = await page.locator('select[name="athlete_id"]').first().inputValue();
      await fillForm(page, { rating: f.rating, marker: `QA_NOTE_${f.key}` });
      const submitBtn = mainForm(page).locator('button[type="submit"], input[type="submit"]').first();

      const [resp] = await Promise.all([
        page.waitForResponse((r) => r.request().method() === 'POST' && r.url().includes(`/forms/${f.key}`)),
        submitBtn.click(),
      ]);
      await page.waitForLoadState('networkidle').catch(() => {});
      const status = resp.status();
      const html = await page.content();
      const sukses = /berhasil disimpan/i.test(html);
      const markerPersist = html.includes(`QA_NOTE_${f.key}`);
      const errVisible = await detectErrorVisible(page);
      const { consoleMsgs, failed } = w.flush();

      note({
        id: `D${idx + 1} ${f.key}`,
        scenario: `Submit form tes ${f.key}${f.rating ? ` (rating ${f.rating})` : ''}`,
        role: 'fisio',
        expected: '302 + flash "berhasil disimpan", data tersimpan',
        actual: `POST ${status}, athleteId=${athleteId}, selectTerisi=${sudahDipilih}, sukses=${sukses}, oldInputTersisa=${markerPersist}, pesanErrorTerlihat=${errVisible}, consoleErr=${consoleMsgs.filter((c) => c.type !== 'warning').length}, reqGagal=${failed.map((x) => x.status).join(',') || '-'}`,
        status: status >= 500 ? 'GAGAL' : (sukses ? 'LULUS' : 'GAGAL'),
        evidence: `screenshots/D-${f.key}.png`,
      });
      await shot(page, `D-${f.key}`);
      await ctx.close();

      expect(status, `POST /forms/${f.key} = ${status}`).toBeLessThan(500);
      expect(
        sukses,
        `form ${f.key} tidak menampilkan flash sukses (markerTersisa=${markerPersist}, errVisible=${errVisible})`
      ).toBe(true);
    });
  });

  test('D11 verifikasi hasil tes tersimpan di detail atlet + radar', async ({ browser }) => {
    const ctx = await browser.newContext({
      baseURL: 'http://127.0.0.1:8000',
      storageState: statePath('fisio'),
      viewport: { width: 1366, height: 768 },
    });
    const page = await ctx.newPage();
    const w = watchPage(page, 'D11');
    await ensureAthleteId(page); // pastikan minimal ada satu atlet QA_TEST_
    // pakai atlet yang sama dengan yang dipilih fillForm pada D1–D10 (opsi QA_TEST_ pertama)
    const athleteId = (await idAtletFormTes(page)) || (await ensureAthleteId(page));
    await page.goto(`/athletes/${athleteId}`);
    await page.waitForLoadState('networkidle').catch(() => {});
    const body = (await page.content()).replace(/<script[\s\S]*?<\/script>/g, ' ');
    const text = body.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ');

    const dinilai = (text.match(/(\d+)\/10 dinilai/) || [])[1] || '0';
    const jumlahBaris = await page.locator('table tbody tr').count();
    const adaLaki = text.includes('Laki-laki');
    const ratings = ['Sempurna', 'Fair', 'Weak', 'Kurang Sekali'];
    const ratingAda = ratings.filter((r) => text.includes(r));
    const adaRadar = /polygon|<svg/i.test(body);
    const { consoleMsgs, failed } = w.flush();
    await shot(page, 'D11-detail-hasil-tes');

    note({
      id: 'D11',
      scenario: 'Detail atlet: hasil tes, gender, radar',
      role: 'fisio',
      expected: '10/10 sumbu radar bernilai & 4 rating uji (Sempurna/Fair/Weak/Kurang Sekali) tampil',
      actual: `dinilai=${dinilai}/10, barisTabel=${jumlahBaris}, genderLakiLaki=${adaLaki}, ratingTampil=[${ratingAda.join(', ')}], adaSvgRadar=${adaRadar}, consoleErr=${consoleMsgs.filter((c) => c.type !== 'warning').length}, reqGagal=${failed.map((x) => `${x.status} ${x.url}`).join(' | ') || '-'} || AKAR (bukti DB tinker): rating tersimpan di kolom test_results (Sempurna/Fair/Weak/Kurang Sekali) tetapi AthleteController.php:98 default=>null tidak mengenali "Sempurna","Fair","Weak" → skor sumbu Kekuatan/Akurasi/Fleksibilitas null; tampilan tabel hanya menampilkan rating utk speed (TestResult.php:143)`,
      status: Number(dinilai) >= 10 && adaLaki && ratingAda.length === 4 ? 'LULUS' : 'GAGAL',
      evidence: 'screenshots/D11-detail-hasil-tes.png + tinker SELECT test_results',
    });
    await ctx.close();

    expect(Number(dinilai), `hanya ${dinilai}/10 tes dinilai`).toBeGreaterThanOrEqual(10);
    expect(adaLaki, 'gender di detail bukan Laki-laki').toBe(true);
    expect(ratingAda.length, `rating tampil: ${ratingAda.join(',')}`).toBe(4);
  });
});
