// Generator e2e-audit/hasil.md — membaca notes.json (sudah dedupe) lalu menulis laporan.
const fs = require('fs');
const path = require('path');

const notes = JSON.parse(fs.readFileSync(path.join(__dirname, 'notes.json'), 'utf8'));
const byId = new Map();
notes.forEach((n) => byId.set(n.id, n));
// notes.json menumpuk antar putaran pemanggilan (dedupe last-wins): laporan memakai versi terbaru per id
const dedup = [...byId.values()];

const esc = (s) => String(s == null ? '-' : s).replace(/\|/g, '\\|').replace(/\r?\n/g, ' ').replace(/\s+/g, ' ').trim();
const potong = (s, n) => {
  const t = esc(s);
  return t.length > n ? t.slice(0, n) + '…' : t;
};

// ---------- Bagian 2: tabel skenario (baris B1 per-route dirangkum di bagian 4) ----------
const perRouteB1 = /^B1 \S+ \//;
const barisSkenario = dedup.filter((n) => !perRouteB1.test(n.id));
const tSkenario = ['| ID | Skenario | Role | Harapan | Hasil | Status | Bukti |', '|---|---|---|---|---|---|---|'];
barisSkenario.forEach((n) => {
  tSkenario.push(`| ${esc(n.id)} | ${esc(n.scenario)} | ${esc(n.role)} | ${potong(n.expected, 190)} | ${potong(n.actual, 420)} | ${n.status} | ${esc(n.evidence)} |`);
});

// ---------- Bagian 4: matriks otorisasi ----------
const peran = ['tamu', 'admin', 'fisio', 'pelatih'];
const matriks = {};
let totalSel = 0;
let selGagal = 0;
peran.forEach((p) => {
  const note = byId.get('B1 MATRIX ' + p);
  if (!note) return;
  let arr = [];
  try { arr = JSON.parse(note.actual); } catch (e) { arr = []; }
  arr.forEach((it) => {
    if (!matriks[it.url]) matriks[it.url] = { group: it.group, expected: it.expected, sel: {} };
    const v = it.actual + (it.loc ? ' → ' + it.loc.replace('http://127.0.0.1:8000', '') : '');
    matriks[it.url].sel[p] = (it.ok ? '' : '**') + v + (it.ok ? ' ✓' : ' ✗**');
    totalSel++;
    if (!it.ok) selGagal++;
  });
});
const tMatriks = ['| Route | Harapan | tamu | admin | fisio | pelatih |', '|---|---|---|---|---|---|'];
Object.entries(matriks).forEach(([url, v]) => {
  tMatriks.push(`| ${esc(url)} | ${esc(v.expected)} | ${v.sel.tamu || '-'} | ${v.sel.admin || '-'} | ${v.sel.fisio || '-'} | ${v.sel.pelatih || '-'} |`);
});
const rejeksipelatih = dedup.filter((n) => /^B1 pelatih \//.test(n.id) && n.status === 'GAGAL').map((n) => n.id.replace('B1 pelatih ', ''));

// ---------- Rekap ----------
const lulus = dedup.filter((n) => n.status === 'LULUS').length;
const gagal = dedup.filter((n) => n.status === 'GAGAL').length;
const info = dedup.filter((n) => n.status === 'INFO').length;

const prosa = fs.readFileSync(path.join(__dirname, 'laporan-teks.md'), 'utf8').replace(/\r\n/g, '\n');
const [
  judul, s1, s3, s5, lampiran,
] = prosa.split('\n=====\n');

const keluaran = [
  judul.trim(),
  '',
  '## 1. Ringkasan eksekutif',
  '',
  s1.trim().replace('{LULUS}', String(lulus)).replace('{GAGAL}', String(gagal)).replace('{INFO}', String(info)),
  '',
  '## 2. Tabel hasil per skenario',
  '',
  `Total baris skenario: ${barisSkenario.length} (LULUS ${barisSkenario.filter((n) => n.status === 'LULUS').length} / GAGAL ${barisSkenario.filter((n) => n.status === 'GAGAL').length} / INFO ${barisSkenario.filter((n) => n.status === 'INFO').length}). Rincian matriks rute × role ada di bagian 4.`,
  '',
  tSkenario.join('\n'),
  '',
  '## 3. Daftar bug / temuan',
  '',
  s3.trim(),
  '',
  '## 4. Matriks otorisasi (role × route)',
  '',
  `Total sel: ${totalSel} (4 role × ${Object.keys(matriks).length} route), sel tidak sesuai harapan: ${selGagal}. Sel merah ditebalkan dan ditandai silang.`,
  '',
  tMatriks.join('\n'),
  '',
  `Catatan: ${rejeksipelatih.length
    ? `satu-satunya role yang ditolak bukan karena login adalah **pelatih** pada ${rejeksipelatih.map((r) => '`' + r + '`').join(', ')} (kembali 500, lihat bug B-01) — bukan penolakan otorisasi.`
    : 'tidak ada sel yang menyimpang. Sebelum TUGAS 4, **pelatih** ditolak 500 pada `/reports`, `/reports/export/pdf`, `/reports/export/excel` (bug B-01, kini diperbaiki T1 `f980cd1`), dan `/register` + `/forgot-password` kini 404 untuk semua role (T2 `fc7a8cd`, T4 `888806e`).'}`,
    'Pelatih ditolak 403 pada route fisik (POST/PUT/DELETE /athletes — lihat B2) dan pada /taktik milik admin (E4).',
  '',
  '## 5. Hal yang tidak bisa diuji',
  '',
  s5.trim(),
  '',
  lampiran.trim(),
  '',
].join('\n');

fs.writeFileSync(path.join(__dirname, 'hasil.md'), keluaran, 'utf8');
console.log('hasil.md ditulis: ' + keluaran.length + ' karakter');
console.log('catatan: LULUS=' + lulus + ' GAGAL=' + gagal + ' INFO=' + info);
console.log('matriks: ' + Object.keys(matriks).length + ' route, ' + totalSel + ' sel, ' + selGagal + ' gagal');
