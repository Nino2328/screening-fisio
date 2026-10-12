# e2e-audit — Suite uji E2E (Playwright)

Audit end-to-end aplikasi FootScreen Pro. Hasil audit, temuan bug, dan status perbaikan ada di
[`hasil.md`](hasil.md) (versi ringkas: [`laporan-teks.md`](laporan-teks.md)).

## Prasyarat

- Server aplikasi berjalan di `http://127.0.0.1:8000` (mis. `php artisan serve --host=127.0.0.1 --port=8000`).
  Suite **tidak** menyalakan atau mematikan server sendiri.
- Database sudah terisi seeder (`php artisan db:seed`), minimal akun:
  - `admin@footscreen.com`
  - `fisio@footscreen.com`
  - `pelatih@footscreen.com`
  - password ketiganya: `password123`
- Node.js, dan Chromium untuk Playwright.

## Menjalankan

```bash
cd e2e-audit
npm install
npx playwright install chromium
npx playwright test
```

Satu file saja: `npx playwright test tests/05-physical.spec.js`.

### Menyegarkan sesi (`state/`)

Semua spec memakai sesi tersimpan di `state/<role>.json`. Jika sesi sudah kedaluwarsa (tes mendarat
kembali di `/login`), segarkan dengan login UI sekali:

```bash
npx playwright test tests/02-login.spec.js -g "A3 login"
```

### Throttle login

`POST /login` dibatasi **5 percobaan per menit** (`throttle:5,1`). Beri jeda **±62 detik** antara
putaran yang berdekatan, dan jangan mengulang login berkali-kali saat menyelidiki kegagalan
(1 percobaan = 1 hitungan).

## Peringatan: pembersihan data

`php bersihkan-qa.php` melakukan **hard delete permanen** untuk semua baris berawalan `QA_TEST_`
(atlet, hasil tes, assessment mental & taktik, user uji) plus foto di `public/uploads/athletes/`
di luar 2 foto lama.

**JANGAN jalankan skrip ini di database produksi.**

## Berkas lain

- `verify-perbaikan.js` — verifikasi HTTP cepat perbaikan T1-T9: `node verify-perbaikan.js`
  (ingat throttle login: jeda ±62 detik dari putaran suite).
- `buat-hasil.js` — generator `hasil.md` dari `notes.json` (jalankan setelah suite).
- Artefak yang di-ignore (`node_modules/`, `test-results/`, `state/`, `screenshots/`,
  `report.json`, `notes-backup.json`, `network-log.json`, `qa-athletes.json`) berubah setiap run.
  `notes.json` tetap dilacak karena menjadi bukti laporan; file ini bertambah tiap run dan
  dedupe last-wins dilakukan oleh `buat-hasil.js`.
