# TUGAS 3A + TUGAS 4 — Audit E2E & Perbaikan Bug Aplikasi `screening-fisio`

**Basis URL:** `http://127.0.0.1:8000` (server `php -S 127.0.0.1:8000` untuk project ini — bukan server yang dijalankan agent, tidak dimatikan; `http://localhost/screening-fisio` via Apache menghasilkan 404).
**Alat:** Playwright + Chromium (`@playwright/test`), konfigurasi `workers: 1`, `retries: 0`, reporter `list` + `json` (`e2e-audit/report.json`).
**Lingkup TUGAS 3A (audit):** menguji & melaporkan saja — tidak ada satu pun kode aplikasi yang diubah, tidak ada commit/push.
**Lingkup TUGAS 4 (perbaikan):** 9 bug diperbaiki pada branch `fix/audit-tahap-1`, **satu commit per tugas** (T1–T9), lalu diverifikasi ulang dengan suite ini. Tidak ada `git push`, tidak ada `migrate`/`seed`, `.env` tidak dibaca/diubah.
**Bukti:** `e2e-audit/tests/00-probe … 09-throttle`, `e2e-audit/notes.json`, `e2e-audit/screenshots/` (A*, B*, C*, D*, E*, F*, H*), `e2e-audit/report.json`, `e2e-audit/hasil.md` (dihasilkan `node buat-hasil.js`).
=====

Eksekusi **TUGAS 3A (audit, tanpa mengubah kode)** menghasilkan **54 test lulus / 8 test gagal** — kedelapan kegagalan semuanya temuan bug aplikasi, bukan kegagalan harness: `/reports` (pelatih) beserta export PDF/CSV-nya 500 karena ada assessment yang merujuk atlet tidak ada, `/forgot-password` 500 karena view tidak ada, tanggal lahir masa depan diterima, upload nama file khusus membuat 500, rating buatan sendiri tidak dikenali sehingga radar hanya terisi 7/10, layout halaman laporan meluber di layar 375 px, pesan validasi tak terlihat, dan halaman 500 menampilkan stack trace. Setelah kesembilan perbaikan **TUGAS 4 (T1–T9)** di-commit, suite yang sama (dengan harapan A7/A8/B1/G3 disesuaikan karena route memang ditutup) kini menghasilkan **62 test lulus / 0 test gagal**, ditambah **{LULUS} catatan skenario LULUS, {GAGAL} GAGAL, dan {INFO} INFO** yang tersimpan di `e2e-audit/notes.json` (dedupe *last-wins*, jadi skenario yang harapannya berubah sudah tercatat dengan harapan barunya).

**Status sebelum / sesudah per bug (T1–T9):**

| Bug | Temuan | Sebelum (TUGAS 3A) | Sesudah (TUGAS 4) | Tugas & commit |
|---|---|---|---|---|
| B-01 | `/reports` + export pelatih = 500 | 500, menu laporan pelatih mati | **200** — halaman, PDF 879.603 byte, CSV 615 byte; baris assessment yatim tampil `-` | T1 `f980cd1` |
| B-09 | Export CSV pelatih 500 di tengah stream | CSV terpotong, `content-type: text/html` | **200 `text/csv; charset=UTF-8`**, file utuh | T1 `f980cd1` |
| B-12 | `/register` terbuka & dapat role `fisio` | GET 200 / POST 302 + auto-login, link "Daftar" ada | **GET 404, POST 404**, link "Daftar" dihapus | T2 `fc7a8cd` |
| B-04 | Upload nama file `a"b'c<d>.png` → 500 | POST 500 `FileException`, atlet tak tersimpan | **302 sukses** — nama file diganti UUID, foto lama di-unlink hanya di folder `uploads/athletes/` | T3 `d169088` |
| B-02 | `/forgot-password` = 500 | 500 untuk keempat role + tamu | **404** (route + komentar dihapus), link jadi teks statis "Lupa password? Hubungi administrator." | T4 `888806e` |
| B-05 | Pesan validasi tak tampil (C3/C4/C6/C7) | ditolak benar tapi `pesanErrorTerlihat=false` | **pesan Bahasa Indonesia tampil** di `#errorAlert`, modal terbuka lagi, `old()` mengisi ulang field | T5 `24856f4` |
| B-06 | Tanggal lahir masa depan diterima | `C5` `tersimpan=true`, tanpa pesan | `before_or_equal:today` → **ditolak**, `C5` LULUS | T5 `24856f4` |
| B-07 | Rating `Sempurna/Fair/Weak/Kurang Sekali` tak dikenali | `D11` `7/10`, hanya `Kurang Sekali` tampil | **`D11` LULUS — 10/10 dinilai**, keempat rating tampil di tabel hasil (`Hexagonal (rating)`, `Push-up (rating)`, dst.) | T6 `b998293` |
| B-08 | `gender/height/weight/vital_*` tak tersimpan | `height`/`weight` NULL semua, `gender` NULL di 5 form, `vital_*` selalu NULL | satu `$identityRules` dipakai **10/10 `store_*`**; kolom & `$fillable` sudah ada (tanpa migrasi baru) | T7 `a9b9019` |
| B-10 | Overflow horizontal `/report` @375 px | `scrollWidth=507` vs `innerWidth=375` | **`H2` LULUS** — baris kontrol, filter, dan tombol export kini `flex-wrap` | T9 `3dbbeb9` |
| — | Statistik fisik dashboard bocor ke non-fisio | `$includePhysical = !$isCoach` (tidak tegas) | **`in_array(role, ['admin','fisio'], true)`**; `F2` (pelatih tanpa kartu fisik) tetap LULUS | T8 `987daf3` |
| B-03 | `APP_DEBUG` → stack trace di halaman 500 | `G3` memperoleh 500 + stack trace di `/reports` | **tidak bisa diverifikasi lagi**: pemicu 500-nya sudah diperbaiki dan tidak ada pemicu 500 lain tanpa mengubah kode/data; `G3` kini mencatat `/reports < 500` + status kebocoran | tidak dikerjakan (keputusan user) |
| B-11 | Tailwind CDN + Google Fonts | `H4` mencatat 2 request CDN | **tidak dikerjakan** (dilewati sesuai keputusan); `H4` tetap LULUS | tidak dikerjakan (keputusan user) |

Alur inti lulus sejak audit awal dan tetap lulus sesudah perbaikan: login 3 role, matriks otorisasi 132 sel (kini `/register` & `/forgot-password` = 404), CRUD atlet + modal, 10 form tes fisik, assessment mental/taktik + penolakan IDOR 403, export fisio PDF + XLSX, dashboard ketiga role, CSRF 419, cookie HttpOnly + SameSite=Lax, dan throttle 5 login/menit (percobaan ke-6 = 429, lalu login normal kembali jalan setelah jeda 60+ detik). Dari 12 temuan: **9 diperbaiki (T1–T9)**, **1 menunggu keputusan user** (B-03 `APP_DEBUG`), **1 dilewati** (B-11 Tailwind CDN); rincian severity tetap 1 CRITICAL, 2 HIGH, 6 MEDIUM, 2 LOW, 1 INFO. Pembersihan akhir menghapus seluruh data uji (`QA_TEST_`) pada dua putaran (sebelum & sesudah suite akhir: total 30 atlet, 59 test_results, 5 mental, 5 taktik, 6 file foto) sehingga sisa `QA_TEST_ = 0` di semua tabel dan folder foto kembali hanya 2 file lama; `git status` hanya menunjukkan direktori `e2e-audit/` (belum di-commit) di samping 9 commit perbaikan.
=====

### Ringkasan 12 temuan (severity · repro · file dugaan · status audit · status perbaikan TUGAS 4)

| ID | Severity | Temuan singkat | File/baris dugaan | Status audit | Status sesudah TUGAS 4 |
|---|---|---|---|---|---|
| B-01 | CRITICAL | `/reports` + export pelatih = 500 (assessment rujuk atlet tak ada) | `resources/views/reports/index.blade.php:39`, `app/Http/Controllers/ReportsController.php:78,111` | SUDAH DICAKUP | **DIPERBAIKI** — T1 `f980cd1` (null-guard `?->name ?? '-'`), suite `E5`/`B1 pelatih` = 200 |
| B-02 | HIGH | `/forgot-password` = 500 (view tidak ada) | `resources/views/auth/` (hanya login & register) | SUDAH DICAKUP | **DITUTUP** — T4 `888806e` (route dihapus), `A8`/`B1 … /forgot-password` = 404 |
| B-03 | HIGH | Stack trace + path internal tampil di semua 500 | `APP_DEBUG` aktif; debug renderer Laravel | BARU | **BELUM** — dilewati: `APP_DEBUG` belum diubah (keputusan user) & pemicu 500 sudah diperbaiki (lihat B-01), `G3` kini mencatat `/reports < 500` |
| B-04 | MEDIUM | Upload nama file `a"b'c<d>.png` → 500 `FileException` | `app/Http/Controllers/AthleteController.php:36`, `UploadedFile.php:197` | BARU | **DIPERBAIKI** — T3 `d169088` (nama file UUID + guard unlink), `C10`/`C11` = LULUS |
| B-05 | MEDIUM | Pesan validasi tidak tampil (4 kasus) | modal `resources/views/athletes/index.blade.php` + `$errors` | BARU | **DIPERBAIKI** — T5 `24856f4` (render `$errors`, modal terbuka lagi, `old()`), `C3/C4/C6/C7` = LULUS |
| B-06 | MEDIUM | Tanggal lahir masa depan diterima | `AthleteController@store` (tanpa `before:today`) | BARU | **DIPERBAIKI** — T5 `24856f4` (`before_or_equal:today` + pesan Indonesia), `C5` = LULUS |
| B-07 | MEDIUM | Radar hanya 7/10, rating `Sempurna/Fair/Weak` tak dikenali | `AthleteController.php:98` (`default => null`), `TestResult.php:143` | BARU | **DIPERBAIKI** — T6 `b998293` (skala 5/4/3/2/1 + `display_results` memuat rating), `D11` = LULUS 10/10 |
| B-08 | MEDIUM | `gender/height/weight/vital_*` tidak tersimpan | `TestController.php` validasi (5 form saja), `TestResult::$fillable` | SUDAH DICAKUP | **DIPERBAIKI** — T7 `a9b9019` (`$identityRules` dipakai 10/10 `store_*`; kolom + `$fillable` sudah ada, tanpa migrasi baru) |
| B-09 | MEDIUM | Export CSV pelatih 500 di tengah stream (file rusak) | `ReportsController.php:78` | SUDAH DICAKUP | **DIPERBAIKI** — T1 `f980cd1`, `E5 /reports/export/excel` = 200 `text/csv` |
| B-10 | LOW | Overflow horizontal `/report` di 375 px | `resources/views/report/index.blade.php:51` (tanpa `flex-wrap`) | BARU | **DIPERBAIKI** — T9 `3dbbeb9`, `H2` = LULUS |
| B-11 | LOW | Tailwind via `cdn.tailwindcss.com` + Google Fonts | `resources/views/layouts/app.blade.php:11` | BARU | **DILEWATI** (keputusan user), `H4` tetap LULUS |
| B-12 | INFO/MED | `/register` terbuka & langsung role `fisio` | `register.blade.php`, default role di controller registrasi | SUDAH DICAKUP | **DITUTUP** — T2 `fc7a8cd` (route + link daftar dihapus), `A7` = 404, akun tak bisa dibuat |

**Tambahan T8 (di luar 12 temuan):** aturan `$includePhysical` di `DashboardController` dibuat tegas (`in_array(role, ['admin','fisio'], true)`) — commit `987daf3`, diverifikasi `F2` + skrip `e2e-audit/verify-t4.js`.

**B-01 · CRITICAL · SUDAH DICAKUP audit statis (terkonfirmasi E2E)** — `/reports` dan export laporan pelatih = HTTP 500
- **Repro:** login `pelatih@footscreen.com` → GET `/reports`, `/reports/export/pdf`, `/reports/export/excel`. Selalu terjadi, tidak tergantung data uji.
- **Bukti E2E:** `B1 pelatih /reports` = 500; `E5 /reports` & `E5 /reports/export/pdf` = 500 (body 1,1 MB halaman error); `E5 /reports/export/excel` = 500 tetapi header `content-disposition: attachment; filename="laporan_assessment_pelatih_Coach Pelatih_2026-10-08.csv"` sudah terkirim bersama potongan CSV 1.001.979 byte (header kolom + baris sampai sebelum data yatim) lalu stream terputus.
- **Akar (file & baris):** `resources/views/reports/index.blade.php:39` → `$assessment->athlete->name` (relasi `athlete` bernilai null); `app/Http/Controllers/ReportsController.php:78` (fputcsv) dan `:111` (export PDF) pada ekspresi yang sama.
- **Akar data:** `mental_assessments` id 5 → `athlete_id` 11 dan id 6 → `athlete_id` 9, `taktik_assessments` id 3 → 11 dan id 4 → 9 — keempatnya tidak ada di tabel `athletes` (hanya id 6 & 7). Tidak ada FK/cascade, dan view tanpa null-guard (`?? '-'`).
- **Dampak:** seluruh menu "Laporan Assessment" untuk pelatih mati (halaman + kedua export).
- **Perbaikan T1 `f980cd1` (TUGAS 4):** null-guard `?->name ?? '-'` di `reports/index.blade.php` dan `ReportsController` (CSV & PDF). Verifikasi: halaman **200**, PDF 879.603 byte, CSV 615 byte, baris assessment yatim tampil `-`; `E5` ×3 dan `B1 pelatih /reports|/reports/export/*` kini LULUS.

**B-02 · HIGH · SUDAH DICAKUP audit statis (terkonfirmasi E2E)** — `/forgot-password` = HTTP 500
- **Repro:** buka `http://127.0.0.1:8000/forgot-password` tanpa sesi (juga `B1 <role> /forgot-password` untuk keempat role = 500).
- **Bukti E2E:** `A8` = HTTP 500; `B1 tamu /forgot-password` = GAGAL (satu-satunya route guest yang bukan 302).
- **Akar:** `resources/views/auth/` hanya berisi `login.blade.php` dan `register.blade.php` — view lupa password tidak ada, tetapi route-nya tetanam.
- **Dampak:** alur reset password tidak dapat dipakai sama sekali.
- **Perbaikan T4 `888806e` (TUGAS 4):** `GET /forgot-password` (+ komentar) dihapus dari `routes/web.php`; tautan di `login.blade.php` diganti teks statis "Lupa password? Hubungi administrator." Verifikasi: **404** untuk keempat role + tamu, teks statis ada; `A8` & `B1` LULUS.

**B-03 · HIGH · BARU** — `APP_DEBUG` aktif: stack trace + path internal tampil ke user
- **Repro:** GET `/reports` sebagai pelatih, baca body respons.
- **Bukti E2E:** `G3` — body memuat `## Stack Trace`, `resources\views\reports\index.blade.php:39`, `vendor\laravel\framework\...`, versi `PHP 8.2.12 / Laravel 12.53.0`. Tidak ada kebocoran `APP_KEY`/`DB_PASSWORD` (`bocorSecret=false`).
- **Akar:** nilai `APP_DEBUG` di lingkungan berjalan; halaman 500 memakai debug renderer bawaan.
- **Dampak:** informasi versi & struktur file memudahkan eskalasi serangan; tidak boleh aktif di produksi.
- **Status TUGAS 4:** **tidak dikerjakan** — `APP_DEBUG` sengaja tidak diubah (keputusan user). Setelah B-01/B-02 diperbaiki tidak ada lagi rute yang memicu 500 (diuji: 404/419 tidak membocorkan stack trace), sehingga `G3` tak lagi bisa memperoleh stack trace; tes `G3` dirombak menjadi pemeriksaan `/reports < 500` + pencatatan status kebocoran (otomatis GAGAL bila kelak ada 500 yang bocor).

**B-04 · MEDIUM · BARU** — Upload foto bernama khusus → HTTP 500 `FileException`
- **Repro:** modal Tambah Atlet → pilih foto dengan nama `a"b'c<d>.png` → submit. Dibandingkan dengan nama aman `xss_test.png` (C11) yang **lulus** (302, tersimpan, tanpa injeksi) sehingga terbukti 500 murni karena nama file.
- **Bukti E2E:** `C10` = POST 500, `tersimpan=false`, pesan error terekam: `Symfony\Component\HttpFoundation\File\Exception\FileException — Could not move the file "C:\xampp\tmp\php5877.tmp" to "...public/uploads/athletes/1791494036_a%22b'c<d>.png" (move_uploaded_file(): Unable to move …)`.
- **Akar:** `app/Http/Controllers/AthleteController.php:36` memakai `getClientOriginalName()` apa adanya lalu `move()`; `vendor/symfony/http-foundation/File/UploadedFile.php:197` adalah titik lempar. Karakter `< > "` ilegal di Windows.
- **Dampak:** input pengguna memicu 500 (dan sekaligus menutup jalur XSS via nama foto di lingkungan Windows — lihat bagian 5).
- **Perbaikan T3 `d169088` (TUGAS 4):** nama file disimpan sebagai `Str::uuid() . '.' . ext`, unlink foto lama hanya bila berada di `uploads/athletes/`; label foto di `index.blade.php` (sink `innerHTML`) diganti `textContent`/`createElement`. Verifikasi: `C10` (nama `a"b'c<d>.png`) dan `C11` (`xss_test.png`) LULUS, foto uji ikut dibersihkan.

**B-05 · MEDIUM · BARU** — Pesan validasi tidak tampil sama sekali (4 kasus)
- **Repro:** modal Tambah Atlet → (a) submit kosong, (b) nama 101 karakter, (c) file bukan gambar, (d) file > 2 MB → submit.
- **Bukti E2E:** `C3`, `C4`, `C6`, `C7` — server **menolak dengan benar** (jumlah baris tidak bertambah, data tidak berubah), tetapi `pesanErrorTerlihat=false`: halaman kembali bersih tanpa satu pun indikator error; `screenshots/C3-validasi.png`.
- **Akar dugaan:** form atlet dimuat lewat modal (`resources/views/athletes/index.blade.php`), pesan `$errors` kemungkinan dirender di posisi yang tidak terlihat/dibuka kembali — perlu konfirmasi pemasangan saat memperbaiki.
- **Dampak:** user mengira form gagal diam-diam/tidak ada umpan balik; risiko submit berulang.
- **Perbaikan T5 `24856f4` (TUGAS 4):** `#errorAlert` hanya `hidden` bila `$errors` kosong dan diisi dari `$errors->all()`; input/select diisi ulang `old()`; blok `@if($errors->any())` di akhir script membuka modal kembali (mode edit bila `old('athlete_id')` ada, tanpa `resetForm()`); pesan validasi diberi teks **Bahasa Indonesia** karena `locale` aplikasi `en` dan tidak ada folder `lang`. Verifikasi: POST tak-valid → 302 + `Nama lengkap wajib diisi.` tampil + `old()` terisi; `C3/C4/C6/C7` LULUS.

**B-06 · MEDIUM · BARU** — Tanggal lahir di masa depan diterima & tersimpan
- **Repro:** Tambah Atlet dengan `birth_date = 2099-12-31` → submit.
- **Bukti E2E:** `C5` — POST 302, `baris 4→5`, `tersimpan=true`, `adaMarker=true`, tanpa error validasi.
- **Akar dugaan:** rule `required|date` tanpa `before:today` pada store/update `AthleteController` (validasi usia/`age` juga tidak dikaitkan ke `birth_date`).
- **Dampak:** data atlet tidak konsisten (usia & tanggal lahir saling bertentangan) merusak laporan.
- **Perbaikan T5 `24856f4` (TUGAS 4):** rule `birth_date` menjadi `nullable|date|before_or_equal:today` pada `store()` & `update()` + pesan "Tanggal lahir tidak boleh melewati hari ini." Verifikasi: `C5` (2099-12-31) kini ditolak dan LULUS.

**B-07 · MEDIUM · BARU** — Label rating buatan aplikasi sendiri tidak dikenali → radar hanya 7/10
- **Repro:** isi 10 form tes fisik dengan rating wajib `Sempurna`, `Fair`, `Weak`, `Kurang Sekali` → buka detail atlet → lihat "Tes selesai: 7/10" dan grafik radar.
- **Bukti E2E:** `D11` — `dinilai=7/10`, `ratingTampil=[Kurang Sekali]`, baris **Y Balance = "Detail hasil belum tersedia"**; verifikasi DB: keempat label **tersimpan** di kolom `rating` (`Sempurna`, `Fair`, `Weak`, `Kurang Sekali`) tetapi skor sumbu tetap null.
- **Akar:** `app/Http/Controllers/AthleteController.php:70-100` (`$ratingScore()`) hanya mengenali `elite/excellent/luar biasa/baik sekali/optimal/good/baik/cukup/kurang/…` dan jatuh ke `default => null` pada baris 98 untuk `Sempurna`, `Fair`, `Weak` → sumbu **Kekuatan, Akurasi, Fleksibilitas** kosong. Sumbu **Koordinasi** memakai `steps50_result/steps100_result` yang bukan input rating.
- **Tambahan:** tabel hasil hanya menampilkan rating untuk tipe `speed` saja (`app/Models/TestResult.php:143`, accessor `display_results`; `display_ratings` di `:157` tidak dirender) sehingga `Sempurna/Fair/Weak` tak pernah terlihat di UI.
- **Dampak:** dashboard/radar salah melaporkan kesiapan atlet; angka "7/10" menyesatkan.
- **Perbaikan T6 `b998293` (TUGAS 4) — skala rating yang dipakai (1–5):** `5 = sempurna/elite/excellent/luar biasa/baik sekali/optimal`, `4 = good/above average/diatas rata/baik`, `3 = average/rata-rata/cukup/**fair**`, `2 = below/di bawah/dibawah/kurang/**weak**`, `1 = **kurang sekali** (dicek SEBELUM 'kurang')/poor/miskin/rendah/abnormal`. `getDisplayResultsAttribute` kini menambahkan hasil `display_ratings` untuk semua tipe tes (label `Hexagonal (rating)`, `Push-up (rating)`, `Rating`, dst.; tipe `speed` sudah punya `Kriteria` sehingga tidak digandakan). Verifikasi: `D11` **LULUS — 10/10 dinilai** dengan `Sempurna, Fair, Weak, Kurang Sekali` tampil; baris terbaca `Hexagonal (rating): Diatas Rata-rata`, `Illinois (rating): …`, `Push-up (rating): Baik Sekali`, `Sit-up (rating): Baik`.

**B-08 · MEDIUM · SUDAH DICAKUP audit statis (terkonfirmasi E2E)** — Identitas & vital tidak tersimpan di `test_results`
- **Repro:** isi form tes (mis. Endurance) dengan tinggi/berat/jenis kelamin + tekanan/suhu/nadi/napas → periksa baris di `test_results`.
- **Bukti E2E (tinker, baca saja):** 18 baris uji — `height` **NULL di semua baris**, `weight` **NULL di semua baris**, `gender` NULL pada 5 dari 10 tipe (accuracy, reaction, coordination, flexibility, balance), kolom `vital_td/vital_suhu/vital_nadi/vital_respirasi` **ada tetapi selalu NULL**.
- **Akar:** `app/Http/Controllers/TestController.php` hanya memvalidasi `gender` untuk 5 form (baris 53, 87, 121, 158, 193) dan **tidak pernah** memvalidasi `height`/`weight`/`vital_*`, sehingga tidak masuk `$validated`; `vital_*` juga tidak ada di `$fillable` `TestResult` sehingga `create($validated)` melemparnya.
- **Dampak:** data antropometri & vital yang diketik user hilang tanpa pesan; laporan/PDF tidak memakainya.
- **Perbaikan T7 `a9b9019` (TUGAS 4):** properti `$identityRules` (`gender` **required** `in:putra,putri` sesuai `<select>` di `x-athlete-identity`, `height`/`weight` `nullable|numeric|min:0`, `vital_td|vital_suhu|vital_nadi|vital_respirasi` `nullable|string`) dispread ke **10/10** `store_*`. `gender/height/weight/vital_*` sudah ada di `$fillable` `TestResult` **dan** di migrasi `create_test_results_table` → **tidak ada migrasi baru yang diperlukan/ditulis**. Cek gender di `athletes/show.blade.php` (4 tempat + 1 JS): semuanya memakai `$athlete->gender` (`M`/`F` → "Laki-laki"/"Perempuan") dan halaman itu tidak merender gender hasil tes (putra/putri), jadi tampilan "Laki-laki" tidak berubah.

**B-09 · MEDIUM · SUDAH DICAKUP audit statis (terkonfirmasi E2E, hasil sedikit berbeda dari dugaan)** — Export CSV pelatih rusak (500 di tengah stream)
- **Repro:** GET `/reports/export/excel` (tombol "📊 Export CSV").
- **Bukti E2E:** `E5 /reports/export/excel` — HTTP **500**, `content-type: text/html; charset=utf-8` (bukan `text/csv`), `content-disposition` tetap `attachment … .csv`, isi = baris header kolom lengkap + data sampai sebelum baris yatim lalu terputus (audit statis menduga "kolom kosong"; kenyataannya kolom terisi, file tetap rusak).
- **Akar:** `app/Http/Controllers/ReportsController.php:78` (lihat B-01).
- **Dampak:** unduhan CSV tidak dapat dipakai; nama tombol (CSV) berbeda dengan endpoint (`/reports/export/excel`).
- **Perbaikan T1 `f980cd1` (TUGAS 4):** ekspresi `fputcsv` memakai null-guard yang sama. Verifikasi: `E5 /reports/export/excel` = **200 `text/csv; charset=UTF-8` 615 byte**, isi utuh sampai baris terakhir (baris yatim tampil `-`).

**B-10 · LOW · BARU** — Overflow horizontal di `/report` pada viewport 375 px
- **Repro:** viewport 375×812 → buka `/report`.
- **Bukti E2E:** `H2` — `scrollWidth=507` vs `innerWidth=375`; pelaku `div.flex` (baris tombol export, `a.px-4` mentok ke 507). `/dashboard` dan `/athletes` aman (375/375), desktop 1366×768 aman (`H3`).
- **Akar dugaan:** `resources/views/report/index.blade.php:51` — deret tombol export tanpa `flex-wrap`.
- **Dampak:** user harus scroll mendatar di ponsel.
- **Perbaikan T9 `3dbbeb9` (TUGAS 4):** `resources/views/report/index.blade.php` — baris kontrol (`justify-between`), pembungkus filter `<form>`, dan baris tombol export kini memakai `flex-wrap` + `gap-2`. Verifikasi: `H2` @375 px **LULUS** (tidak ada elemen melewati `innerWidth`).

**B-11 · LOW · BARU** — Ketergantungan CDN eksternal untuk styling
- **Repro:** buka `/dashboard` sambil memantau permintaan jaringan.
- **Bukti E2E:** `H4` — `cdn.tailwindcss.com` (2 request), `fonts.googleapis.com` (1), `fonts.gstatic.com` (4) + console: *"cdn.tailwindcss.com should not be used in production…"*; `H1` mencatat tepat 1 warning per halaman (5 halaman) dan **0 error console**.
- **Akar:** `resources/views/layouts/app.blade.php:11` (`<script src="https://cdn.tailwindcss.com">`).
- **Dampak:** tampilan rusak bila offline/CDN terlambat; build dev-tool masuk produksi.
- **Status TUGAS 4:** **dilewati** atas keputusan user (migrasi Tailwind CDN → Vite di luar lingkup). `H4` tetap LULUS dan tetap mencatat ketergantungan CDN.

**B-12 · INFO/MEDIUM · SUDAH DICAKUP audit statis (menunggu keputusan)** — `/register` terbuka dan langsung mendapat role `fisio`
- **Repro:** POST `/register` dengan email & password baru → 302 langsung ke `/dashboard` (auto-login).
- **Bukti E2E:** `A7` lulus; verifikasi DB menunjukkan `qa_test_user@example.com` → `role = "fisio"` (bukan `user`); form `resources/views/auth/register.blade.php` tidak punya pemilihan role sama sekali, jadi role berasal dari default controller/seeder.
- **Dampak:** siapa pun di jaringan dapat membuat akun berrole fisio (akses penuh data atlet & tes). Pilihan: tutup `/register`, atau ubah default role menjadi peran paling rendah + persetujuan admin.
- **Perbaikan T2 `fc7a8cd` (TUGAS 4):** `GET/POST /register` dihapus dari `routes/web.php` dan seluruh blok "Belum punya akun? … Daftar" dihapus dari `login.blade.php`. View `auth/register.blade.php` dan `RegisterController` **tidak dihapus** (menunggu keputusan user). Verifikasi: `GET /register` = **404**, `POST /register` = **404**, akun `qa_test_user@example.com` tak dapat dibuat (users kini tetap 3); `A7` LULUS.

**Pola yang terkonfirmasi bekerja (bukan bug):** penolakan CSRF 419 (`G1`), flag cookie (`G2`), 404 tanpa debug (`G4`), seluruh route terproteksi dialihkan ke `/login` untuk tamu (`G5`), IDOR assessment ditolak 403 (`E4`), upaya tulis pelatih ke `/athletes` ditolak 403 tanpa efek samping (`B2`), ownership `/taktik/1` (admin 200 vs pelatih 403), throttle login (`I1`), logout + bukti sesi mati (`I2`).
=====

1. **XSS lewat nama file foto (sink `resources/views/athletes/index.blade.php:301`, `innerHTML`)** — sebelum T4 payload nama ber-`<>"` justru memicu 500 saat `move()` (B-04); sesudah **T3** (`d169088`) nama file diganti UUID sehingga payload tak pernah sampai ke `move()` dan `C10`/`C11` LULUS. Sink `innerHTML` yang tersisa (`showError()`, `form.innerHTML`, `photoLabel`) hanya menerima string statis/kontrol dari developer, tetapi kebersihan `innerHTML` menyeluruh tidak diverifikasi otomatis.
2. **Alur lupa password / reset token** — tetap tidak dapat diuji: sesudah **T4** (`888806e`) route `/forgot-password` memang sengaja dihapus (404), jadi tidak ada alur reset sama sekali sampai user memutuskan cara pemulihan akun yang diinginkan.
3. **Isi CSV laporan pelatih sampai baris terakhir** — sebelumnya stream terputus 500 (B-09); sesudah **T1** (`f980cd1`) CSV 615 byte terbaca utuh (baris assessment yatim tampil `-`), jadi kini **bisa diperiksa penuh**.
3a. **Pemetaan skor radar/rata-rata untuk label `Fair`** — skala `fair → 3 (sedang)` dipilih sesuai instruksi; pada form Fleksibilitas opsi `Fair` sebenarnya urutan ke-4 dari 5 (di bawah `Average`). Perbedaan semantik ini dicatat, bukan diubah, dan menunggu keputusan user.
4. **Data atlet non-uji (Sabran id 7, Wardiansyah id 6)** — hanya dibaca lewat GET untuk matriks & export; tidak pernah diubah/dihapus sesuai aturan "jangan sentuh data yang sudah ada".
5. **Browser selain Chromium (Firefox/WebKit) dan perangkat fisik** — sesuai izin hanya Chromium yang dipasang; pengujian responsif dilakukan lewat emulation 375×812 dan 1366×768.
6. **Throttle berbasis user/role dan endpoint lain (register, reset)** — hanya `POST /login` per-IP (127.0.0.1) yang diuji sesuai instruksi; endpoint lain sengaja tidak dipicu agar tidak memblokir rate limit sesi.
7. **HTTPS / `Secure` cookie** — aplikasi berjalan murni HTTP localhost sehingga `secure=false` (wajar untuk lingkungan ini) dan tidak dapat dinilai sebagai kerentanan.
8. **`php artisan test` (bawaan aplikasi)** — selalu 1 gagal: `ExampleTest` mengharapkan 200 tetapi root mengembalikan 302 ke `/login`; ini perilaku test bawaan, bukan temuan E2E.
9. **Kompatibilitas ekstensi PHP `intl`** — tidak aktif sehingga `php artisan db:table` gagal; verifikasi skema dilakukan lewat `Schema::hasColumn` via tinker (baca-saja).
=====

## Lampiran A — Pembersihan data uji (LANGKAH 3)

Semua data uji memakai awalan `QA_TEST_` dan akun `qa_test_user@example.com`.

**Putaran TUGAS 3A** (putaran 1 setelah skenario E, putaran 2 setelah suite penuh) + self-cleanup `beforeAll` di setiap spesifikasi:

| Objek | Putaran 1 | Putaran 2 | Total dihapus | Sisa |
|---|---|---|---|---|
| Atlet (`athletes` nama `QA_TEST%`) | 2 | 2 | 4 | 0 |
| Hasil tes (`test_results` nama `QA_TEST%` / yatim) | 18 | 10 | 28 | 0 |
| Assessment mental (`QA_TEST%`) | 1 | 0 | 1 | 0 |
| Assessment taktik (`QA_TEST%`) | 1 | 0 | 1 | 0 |
| Akun (`qa_test_user@example.com`) | 1 | 1 | 2 | 0 |
| File foto `public/uploads/athletes` | 4 | 2 | 6 | 0 (tersisa 2 foto lama) |

**Putaran TUGAS 4** — skrip `e2e-audit/bersihkan-qa.php` (hanya hard-delete baris berawalan `QA_TEST_` + akun `qa_test_user@example.com`, plus file foto di luar 2 foto lama):

| Objek | Putaran 3 (sebelum suite final) | Putaran 4 (sesudah suite final) | Total dihapus | Sisa |
|---|---|---|---|---|
| Atlet (`athletes` nama `QA_TEST%`) | 26 | 4 | 30 | 0 |
| Hasil tes (`test_results` `QA_TEST%` / milik atlet uji) | 49 | 10 | 59 | 0 |
| Assessment mental (`QA_TEST%`) | 4 | 1 | 5 | 0 |
| Assessment taktik (`QA_TEST%`) | 4 | 1 | 5 | 0 |
| Akun (`qa_test_user@example.com`) | 0 | 0 | 0 | 0 (A7 sudah 404, akun tak terbuat lagi) |
| File foto `public/uploads/athletes` | 3 (nama UUID sisa T3) | 3 (nama UUID dari suite) | 6 | 0 (tersisa 2 foto lama) |

Verifikasi akhir: `php e2e-audit/lihat-data.php` (bootstrap Laravel, baca-saya) memuat `sisa QA_TEST_ = 0` di semua tabel dan **total aplikasi kembali seperti keadaan awal audit: 2 atlet aktif (12 baris termasuk soft-delete historis), 33 test_results, 4 mental, 4 taktik, 3 user** — sisa assessment yatim (mental id 5,6 → `athlete_id` 11,9 dan taktik id 3,4 → 11,9 yang kini *soft-deleted*) **tidak dihapus**, hanya dilaporkan. Isi folder foto kembali persis 2 file lama: `1773847515_whatsapp-image-2025-01-10-at-19-20250110075854.jpeg` dan `1790878768_WhatsApp Image 2026-10-02 at 01.18.57.jpeg`. (Verifikasi via `php artisan tinker --execute` tidak bisa dipakai karena PowerShell membuang tanda `$`/tanda kutip — sudah dicoba, gagal parse; dipakai skrip PHP yang mem-bootstrap Laravel.)

## Lampiran B — Kepatuhan & artefak

- `.gitignore` di `e2e-audit/` berisi `node_modules/`, `test-results/`, `state/`, `screenshots/` sesuai instruksi.
- `package.json` / `package-lock.json` **root tidak disentuh**; dependency hanya terpasang di `e2e-audit/`.
- **TUGAS 3A:** tidak ada `migrate:fresh/rollback/reset`, tidak ada `db:seed`, tidak ada commit/push, tidak ada pembacaan `.env`.
- **TUGAS 4:** 9 commit pada branch `fix/audit-tahap-1` (T1 `f980cd1`, T2 `fc7a8cd`, T3 `d169088`, T4 `888806e`, T5 `24856f4`, T6 `b998293`, T7 `a9b9019`, T8 `987daf3`, T9 `3dbbeb9`) — masing-masing hanya memuat file tugasnya; **tidak ada `git push`**, tidak ada `migrate`/`db:seed` (tidak ada migrasi baru yang diperlukan), `.env` tidak dibaca/diubah, data non-uji tidak disentuh. Setiap tugas: `php -l` → `view:clear` → `view:cache` → `view:clear` → commit; line ending CRLF dipertahankan di semua file yang disentuh.
- Throttle dihormati: hanya 4 `POST /login` dipakai pada `02-login`, diikuti jeda sebelum dan sesudah uji throttle (`I1` menunggu 62 dtk, `I2` menunggu 62 dtk lagi); hasil akhir `#1–#5 = 302`, `#6 = 429`, lalu login sukses kembali. Verifikasi manual (`verify-t4.js`, 3 login) dijeda ≥62 detik sebelum suite dijalankan.
- Login dilakukan sekali per role dan selanjutnya memakai `storageState` (`e2e-audit/state/*.json`).
- Server `php -S 127.0.0.1:8000` tidak dimatikan/dimodifikasi (bukan milik agent).
