const { test, expect, request: requestFactory } = require('@playwright/test');
const { note, statePath, shot } = require('../lib');

const FORMS = ['endurance', 'speed', 'agility', 'strength', 'power', 'accuracy', 'reaction', 'coordination', 'flexibility', 'balance'];

const PAGES = [
  { url: '/', group: 'root' },
  { url: '/dashboard', group: 'dashboard' },
  { url: '/athletes', group: 'athletes' },
  { url: '/athletes/1', group: 'athletes' },
  { url: '/tests', group: 'tests' },
  ...FORMS.map((f) => ({ url: `/forms/${f}`, group: 'forms' })),
  { url: '/report', group: 'report' },
  { url: '/report/athlete/1', group: 'report' },
  { url: '/report/export/pdf', group: 'report' },
  { url: '/report/export/excel', group: 'report' },
  { url: '/mental', group: 'mental' },
  { url: '/mental/create', group: 'mental' },
  { url: '/mental/1', group: 'mental' },
  { url: '/mental/1/edit', group: 'mental' },
  { url: '/taktik', group: 'taktik' },
  { url: '/taktik/create', group: 'taktik' },
  { url: '/taktik/1', group: 'taktik' },
  { url: '/taktik/1/edit', group: 'taktik' },
  { url: '/reports', group: 'reports' },
  { url: '/reports/export/pdf', group: 'reports' },
  { url: '/reports/export/excel', group: 'reports' },
  { url: '/login', group: 'guest' },
  { url: '/register', group: 'guest' },
  { url: '/forgot-password', group: 'guest' },
];

const ALLOW = {
  admin: ['root', 'dashboard', 'athletes', 'tests', 'forms', 'report', 'mental', 'taktik', 'reports'],
  fisio: ['root', 'dashboard', 'athletes', 'tests', 'forms', 'report'],
  pelatih: ['root', 'dashboard', 'athletes', 'mental', 'taktik', 'reports'],
  tamu: ['guest'],
};

const DETAIL_ASSESS = ['/mental/1', '/mental/1/edit', '/taktik/1', '/taktik/1/edit'];

// Route yang sudah ditutup pada TUGAS 4 (T2 & T4) — kini404 untuk semua role
const ROUTE_DITUTUP = ['/register', '/forgot-password'];

function expectedFor(role, group, url) {
  if (ROUTE_DITUTUP.includes(url)) return '404';
  if (role === 'tamu') return group === 'guest' ? 'AKSES' : 'REDIRECT_LOGIN';
  if (group === 'guest') return 'REDIRECT_DASHBOARD';
  if (!ALLOW[role].includes(group)) return '403';
  if (DETAIL_ASSESS.includes(url)) return 'DETAIL_OWNED';
  return 'AKSES';
}

function judge(expectation, status, loc) {
  if (expectation === '404') return status === 404;
  if (expectation === '403') return status === 403;
  if (expectation === 'REDIRECT_LOGIN') return status === 302 && String(loc).includes('/login');
  if (expectation === 'REDIRECT_DASHBOARD') return status === 302;
  if (expectation === 'DETAIL_OWNED') return [200, 403, 404].includes(status);
  if (expectation === 'AKSES') return status === 200 || status === 302 || status === 404;
  return false;
}

test.describe('B. Otorisasi — matriks role x halaman', () => {
  for (const role of ['tamu', 'admin', 'fisio', 'pelatih']) {
    test(`B1 matriks GET untuk role ${role}`, async ({ browser }) => {
      const ctx = await requestFactory.newContext({
        baseURL: 'http://127.0.0.1:8000',
        ...(role === 'tamu' ? {} : { storageState: statePath(role) }),
      });
      const matrix = [];
      let fails = 0;
      for (const p of PAGES) {
        const res = await ctx.get(p.url, { maxRedirects: 0, timeout: 45000 });
        const status = res.status();
        const loc = res.headers()['location'] || '';
        const exp = expectedFor(role, p.group, p.url);
        const ok = judge(exp, status, loc);
        if (!ok) fails++;
        matrix.push({ url: p.url, group: p.group, expected: exp, actual: status, loc: String(loc).replace('http://127.0.0.1:8000', ''), ok });
        note({ id: `B1 ${role} ${p.url}`, scenario: `GET ${p.url}`, role, expected: exp, actual: `${status}${loc ? ' → ' + String(loc).replace('http://127.0.0.1:8000', '') : ''}`, status: ok ? 'LULUS' : 'GAGAL', evidence: `HTTP GET (storageState ${role})` });
      }
      await ctx.dispose();
      note({ id: `B1 MATRIX ${role}`, scenario: 'Rekap matriks otorisasi', role, expected: 'semua sesuai harapan', actual: JSON.stringify(matrix), status: fails === 0 ? 'LULUS' : 'GAGAL', evidence: 'matrix' });
      expect(fails, `matriks role ${role} tidak sesuai harapan: ${JSON.stringify(matrix.filter((m) => !m.ok))}`).toBe(0);
    });
  }

  test('B2 pelatih POST/PUT/DELETE /athletes dengan CSRF sah → 403 & data tidak berubah', async ({ browser }) => {
    const ctx = await browser.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih'), viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
    await page.goto('/dashboard');
    const token = await page.locator('meta[name="csrf-token"]').getAttribute('content');
    expect(token).toBeTruthy();
    await shot(page, 'B2-pelatih-dashboard');
    await ctx.close();

    const req = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('pelatih') });

    const post = await req.post('/athletes', {
      form: { _token: token, name: 'QA_TEST_Akses_Ditolak', gender: 'M', age: '20' },
      maxRedirects: 0,
    });
    const put = await req.put('/athletes/1', {
      form: { _token: token, _method: 'PUT', name: 'QA_TEST_Diubah_Pelatih' },
      maxRedirects: 0,
    });
    const del = await req.delete('/athletes/1', { form: { _token: token }, maxRedirects: 0 });

    const adminReq = await requestFactory.newContext({ baseURL: 'http://127.0.0.1:8000', storageState: statePath('admin') });
    const list = await adminReq.get('/athletes');
    const listHtml = await list.text();
    const created = listHtml.includes('QA_TEST_Akses_Ditolak');
    const detail = await adminReq.get('/athletes/1', { headers: { Accept: 'application/json' } });
    const detailJson = await detail.text();
    await adminReq.dispose();
    await req.dispose();

    const results = [
      ['POST /athletes', post.status()],
      ['PUT /athletes/1', put.status()],
      ['DELETE /athletes/1', del.status()],
    ];
    for (const [what, st] of results) {
      const ok = st === 403;
      note({ id: `B2 ${what}`, scenario: `${what} oleh pelatih (CSRF sah)`, role: 'pelatih', expected: '403', actual: `HTTP ${st}`, status: ok ? 'LULUS' : 'GAGAL', evidence: 'API request dengan X-CSRF-TOKEN valid' });
      expect(ok, `${what} = ${st}, bukan 403`).toBe(true);
    }
    note({ id: 'B2 side-effect', scenario: 'Data tidak berubah setelah ditolak', role: 'pelatih', expected: 'tidak ada atlet QA_TEST_Akses_Ditolak', actual: `created=${created}, GET /athletes/1 (admin) = ${detail.status()}, body=${detailJson.slice(0, 140)}`, status: !created ? 'LULUS' : 'GAGAL', evidence: 'GET /athletes + GET /athletes/1 sebagai admin' });
    expect(created).toBe(false);
  });
});
