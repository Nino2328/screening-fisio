// Verifikasi cepat perbaikan T1-T9 via HTTP (dijalankan dengan: node verify-perbaikan.js)
const { request } = require('@playwright/test');

const BASE = 'http://127.0.0.1:8000';
const AKUN = {
  pelatih: { email: 'pelatih@footscreen.com', password: 'password123' },
  admin: { email: 'admin@footscreen.com', password: 'password123' },
  fisio: { email: 'fisio@footscreen.com', password: 'password123' },
};

async function login(role) {
  const ctx = await request.newContext({ baseURL: BASE });
  const hal = await ctx.get('/login');
  const html = await hal.text();
  const m = html.match(/name="_token"\s+value="([^"]+)"/);
  await ctx.post('/login', { form: { _token: m ? m[1] : '', email: AKUN[role].email, password: AKUN[role].password } });
  return ctx;
}

function ambilTeks(html) {
  return html
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

(async () => {
  const hasil = [];
  const catat = (k, v) => { hasil.push(k + ' = ' + v); console.log(k + ' = ' + v); };

  // ---- T1: /reports pelatih + export-nya ----
  const pelatih = await login('pelatih');
  const r1 = await pelatih.get('/reports', { maxRedirects: 0 });
  const t1 = ambilTeks(await r1.text());
  catat('T1 /reports', r1.status() + ' | adaDeni=' + t1.includes('Deni Kurniawan') + ' | adaBudi=' + t1.includes('Budi Santoso') + ' | adaTandaDash=' + /\s-\s/.test(t1));

  const r2 = await pelatih.get('/reports/export/excel', { maxRedirects: 0 });
  const csv = await r2.body().catch(() => Buffer.alloc(0));
  const csvTeks = csv.slice(0, 1200).toString('utf8');
  catat('T1 /reports/export/excel', r2.status() + ' | type=' + (r2.headers()['content-type'] || '-') + ' | bytes=' + csv.length + ' | adaBarisYatim=' + csvTeks.includes('Deni Kurniawan'));

  const r3 = await pelatih.get('/reports/export/pdf', { maxRedirects: 0, timeout: 60000 });
  const pdf = await r3.body().catch(() => Buffer.alloc(0));
  catat('T1 /reports/export/pdf', r3.status() + ' | type=' + (r3.headers()['content-type'] || '-') + ' | bytes=' + pdf.length + ' | headerPDF=' + pdf.slice(0, 5).toString('latin1'));

  // ---- T8: dashboard pelatih tidak memuat statistik fisik ----
  const dashCoach = await (await pelatih.get('/dashboard')).text();
  const adaFisik = ['Endurance', 'Speed 50M', 'Rata-rata YoYo'].filter((k) => dashCoach.includes(k));
  catat('T8 dashboard pelatih', (dashCoach.includes('Assessment mental') && adaFisik.length === 0)
    ? 'statistik fisik disembunyikan'
    : 'MASIH ADA: ' + adaFisik.join(', '));
  await pelatih.dispose();

  // ---- T2 & T4: route ditutup (404) ----
  const tamu = await request.newContext({ baseURL: BASE });
  for (const url of ['/register', '/forgot-password']) {
    const res = await tamu.get(url, { maxRedirects: 0 });
    catat('T2/T4 ' + url, res.status());
  }
  const loginHtml = await (await tamu.get('/login')).text();
  catat('T2 link daftar di /login', (/\/register/.test(loginHtml) ? 'MASIH ADA' : 'sudah tidak ada'));
  catat('T4 teks lupa password', /Hubungi administrator/.test(loginHtml) ? 'ada' : 'TIDAK ADA');
  await tamu.dispose();

  // ---- T5: validasi atlet (pesan Indonesia + modal otomatis) ----
  const admin = await login('admin');
  const halAtlet = await (await admin.get('/athletes')).text();
  const token = halAtlet.match(/name="_token"\s+value="([^"]+)"/)[1];
  const resPost = await admin.post('/athletes', {
    maxRedirects: 0,
    headers: { Referer: BASE + '/athletes' },
    form: {
      _token: token, name: '', birth_place: 'X', birth_date: '2030-01-01',
      age: '', gender: '', position: '', height: '', weight: '', phone: '',
    },
  });
  const setelah = await (await admin.get('/athletes')).text();
  catat('T5 POST tidak valid', resPost.status() + ' | redirect=' + (resPost.headers()['location'] || '-'));
  catat('T5 pesan Indonesia', (setelah.includes('Nama lengkap wajib diisi.') && setelah.includes('Tanggal lahir tidak boleh melewati hari ini.')) ? 'ada' : 'TIDAK ADA');
  const alertClass = (setelah.match(/<div id="errorAlert" class="([^"]*)"/) || [null, ''])[1];
  catat('T5 errorAlert terlihat', (alertClass.includes('hidden') ? 'TIDAK (tersembunyi: ' + alertClass + ')' : 'ya (class="' + alertClass + '")'));
  catat('T5 old() terisi', setelah.includes('value="2030-01-01"') ? 'ya' : 'TIDAK');
  catat('T5 modal terbuka otomatis', setelah.includes('var editId =') && setelah.includes('Buka kembali modal') ? 'ya' : 'TIDAK');
  catat('T5 jumlah atlet tak bertambah', (setelah.match(/athletes\/\d+/g) || []).length + ' tautan detail');

  // ---- T6: rating tampil di tabel hasil + skor grafik ----
  const idUji = [6, 9, 11, 7];
  let terbaik = { id: null, label: 0, teks: '' };
  for (const id of idUji) {
    const halDetail = await (await admin.get('/athletes/' + id)).text();
    const label = (halDetail.match(/\(rating\)/g) || []).length;
    if (label > terbaik.label) {
      terbaik = {
        id, label,
        teks: ['Sempurna', 'Fair', 'Weak', 'Kurang Sekali'].filter((r) => halDetail.includes(r)).join(', '),
      };
    }
  }
  catat('T6 label "(rating)" di tabel', terbaik.label + ' buah pada atlet #' + terbaik.id);
  catat('T6 rating yang tampil', terbaik.teks || '-');

  // ---- T9: baris kontrol & tombol export /report membungkus (flex-wrap) ----
  const halReport = await (await admin.get('/report')).text();
  const jumlahFlex = (halReport.match(/flex-wrap/g) || []).length;
  const barisExportTerbungkus = /class="[^"]*flex-wrap[^"]*"[^>]*>\s*(<a[^>]*>\s*📄|<a)/.test(halReport);
  catat('T9 /report flex-wrap', jumlahFlex + ' kelas | baris tombol export terbungkus=' + barisExportTerbungkus);
  await admin.dispose();

  // ---- T7: identitas & vital ikut tersimpan pada tes yang dulu tanpa aturan ----
  const fisio = await login('fisio');
  const tokAth = (await (await fisio.get('/athletes')).text()).match(/name="_token"\s+value="([^"]+)"/)[1];
  const resAtlet = await fisio.post('/athletes', {
    maxRedirects: 0,
    headers: { Referer: BASE + '/athletes' },
    form: { _token: tokAth, name: 'QA_TEST_T7', age: '20', gender: 'M', position: 'Bek', height: '175', weight: '70', phone: '081122334455' },
  });
  const daftar2 = await (await fisio.get('/athletes')).text();
  const barisT7 = daftar2.split('<tr').find((b) => b.includes('QA_TEST_T7')) || '';
  const idT7 = (barisT7.match(/editAthlete\((\d+)\)/) || [])[1];
  const tokTes = (await (await fisio.get('/forms/accuracy')).text()).match(/name="_token"\s+value="([^"]+)"/)[1];
  const resTes = await fisio.post('/forms/accuracy', {
    maxRedirects: 0,
    headers: { Referer: BASE + '/forms/accuracy' },
    form: {
      _token: tokTes, athlete_id: idT7, name: 'QA_TEST_T7', age: '20', gender: 'putra', position: 'Bek',
      test_date: '2026-10-08', phone: '081122334455', height: '177.5', weight: '71.2',
      vital_td: '120/80', vital_suhu: '36.5', vital_nadi: '78', vital_respirasi: '18',
      complaint: 'QA_TEST_ tidak ada', shot1_score: '8', shot1_time: '1.2', shot2_score: '9',
      shot2_time: '1.1', shot3_score: '7', shot3_time: '1.3', total_score: '24', rating: 'Good',
      conclusion: 'QA_TEST_ uji T7',
    },
  });
  catat('T7 buat atlet uji', resAtlet.status() + ' | id=' + idT7);
  catat('T7 simpan tes akurasi', resTes.status() + ' | redirect=' + (resTes.headers()['location'] || '-'));
  globalThis.idT7 = idT7;
  await fisio.dispose();

  console.log('\n--- ringkas ---');
  console.log(hasil.join('\n'));
  console.log('ID_T7=' + idT7);
})();
