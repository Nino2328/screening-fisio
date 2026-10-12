const { test, expect } = require('@playwright/test');
const { note, statePath } = require('../lib');

/** Probe diagnostik (bukan skenario uji) — menggali penyebab 500/403. */
test.describe('Probe diagnostik', () => {
  test('P1 akar error /reports & /taktik/1 untuk pelatih + daftar atlet', async ({ browser }) => {
    const pelatih = await (require('@playwright/test')).request.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih') });
    const admin = await (require('@playwright/test')).request.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin') });

    for (const url of ['/reports', '/taktik/1', '/mental/1', '/reports/export/excel']) {
      const res = await pelatih.get(url, { maxRedirects: 0, timeout: 45000 });
      const body = await res.text();
      const snippet = body
        .replace(/<style[\s\S]*?<\/style>/gi, ' ')
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, 400);
      note({
        id: `P1 ${url}`, scenario: `Probe GET ${url} (pelatih)`, role: 'pelatih',
        expected: '-', actual: `HTTP ${res.status()} :: ${snippet}`,
        status: 'INFO', evidence: 'body response',
      });
    }

    const ath = await admin.get('/athletes');
    const athHtml = await ath.text();
    const total = (athHtml.match(/Total Atlet[^0-9]*(\d+)/) || [])[1] || '?';
    const noData = athHtml.includes('Belum ada data atlet');
    const sampleIds = [...athHtml.matchAll(/\/athletes\/(\d+)/g)].map((m) => m[1]);
    note({ id: 'P1 daftar atlet', scenario: 'Jumlah & id atlet yang ada', role: 'admin', expected: '-', actual: `total=${total}, kosong=${noData}, idDitemukan=${[...new Set(sampleIds)].slice(0, 10).join(',')}`, status: 'INFO', evidence: 'GET /athletes' });

    const mk = await admin.get('/mental/1', { maxRedirects: 0 });
    const tk = await admin.get('/taktik/1', { maxRedirects: 0 });
    note({ id: 'P1 admin mental/taktik id=1', scenario: 'Status admin untuk mental/1 & taktik/1', role: 'admin', expected: '-', actual: `mental/1=${mk.status()}, taktik/1=${tk.status()}`, status: 'INFO', evidence: 'GET' });

    await pelatih.dispose();
    await admin.dispose();
    expect(true).toBe(true);
  });
});
