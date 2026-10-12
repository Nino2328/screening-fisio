# TUGAS 3A + TUGAS 4 — Audit E2E & Perbaikan Bug Aplikasi `screening-fisio`

**Basis URL:** `http://127.0.0.1:8000` (server `php -S 127.0.0.1:8000` untuk project ini — bukan server yang dijalankan agent, tidak dimatikan; `http://localhost/screening-fisio` via Apache menghasilkan 404).
**Alat:** Playwright + Chromium (`@playwright/test`), konfigurasi `workers: 1`, `retries: 0`, reporter `list` + `json` (`e2e-audit/report.json`).
**Lingkup TUGAS 3A (audit):** menguji & melaporkan saja — tidak ada satu pun kode aplikasi yang diubah, tidak ada commit/push.
**Lingkup TUGAS 4 (perbaikan):** 9 bug diperbaiki pada branch `fix/audit-tahap-1`, **satu commit per tugas** (T1–T9), lalu diverifikasi ulang dengan suite ini. Tidak ada `git push`, tidak ada `migrate`/`seed`, `.env` tidak dibaca/diubah.
**Bukti:** `e2e-audit/tests/00-probe … 09-throttle`, `e2e-audit/notes.json`, `e2e-audit/screenshots/` (A*, B*, C*, D*, E*, F*, H*), `e2e-audit/report.json`, `e2e-audit/hasil.md` (dihasilkan `node buat-hasil.js`).

## 1. Ringkasan eksekutif

Eksekusi **TUGAS 3A (audit, tanpa mengubah kode)** menghasilkan **54 test lulus / 8 test gagal** — kedelapan kegagalan semuanya temuan bug aplikasi, bukan kegagalan harness: `/reports` (pelatih) beserta export PDF/CSV-nya 500 karena ada assessment yang merujuk atlet tidak ada, `/forgot-password` 500 karena view tidak ada, tanggal lahir masa depan diterima, upload nama file khusus membuat 500, rating buatan sendiri tidak dikenali sehingga radar hanya terisi 7/10, layout halaman laporan meluber di layar 375 px, pesan validasi tak terlihat, dan halaman 500 menampilkan stack trace. Setelah kesembilan perbaikan **TUGAS 4 (T1–T9)** di-commit, suite yang sama (dengan harapan A7/A8/B1/G3 disesuaikan karena route memang ditutup) kini menghasilkan **62 test lulus / 0 test gagal**, ditambah **203 catatan skenario LULUS, 0 GAGAL, dan 8 INFO** yang tersimpan di `e2e-audit/notes.json` (dedupe *last-wins*, jadi skenario yang harapannya berubah sudah tercatat dengan harapan barunya).

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

## 2. Tabel hasil per skenario

Total baris skenario: 79 (LULUS 71 / GAGAL 0 / INFO 8). Rincian matriks rute × role ada di bagian 4.

| ID | Skenario | Role | Harapan | Hasil | Status | Bukti |
|---|---|---|---|---|---|---|
| P1 /reports | Probe GET /reports (pelatih) | pelatih | - | HTTP 200 :: Laporan Assessment — Screening Fisioterapi tailwind.config = { theme: { extend: { colors: { brand: { green: '#00E676', gdark: '#00C853', bg: '#080C10', surf: '#0E1419', surf2: '#141B22', surf3: '#1C252F', border: 'rgba(0,230,118,0.15)', } }, fontFamily: { display: ['Bebas Neue', 'sans-serif'], mono2: ['Space Mono', 'monospace'], body: ['DM Sans', 'sans-serif'], } } } } ⚽ FootScreen Physiotherapy P | INFO | body response |
| P1 /taktik/1 | Probe GET /taktik/1 (pelatih) | pelatih | - | HTTP 403 :: Forbidden 403 Forbidden | INFO | body response |
| P1 /mental/1 | Probe GET /mental/1 (pelatih) | pelatih | - | HTTP 404 :: Not Found 404 Not Found | INFO | body response |
| P1 /reports/export/excel | Probe GET /reports/export/excel (pelatih) | pelatih | - | HTTP 200 :: "Mental Assessments" Atlet,"Tanggal Assessment",Fokus,Motivasi,Konsentrasi,Tekanan,Keyakinan,Kepemimpinan,"Rata-rata Skor","Penilaian Keseluruhan",Rekomendasi -,"07 Oct 2026 15:49",,,,,,,4,, -,"07 Oct 2026 15:49",,,,,,,4.83,, Sabran,"06 Oct 2026 13:28",,,,,,,3.6,adsfdgh,nbvfgdbvf "Taktik Assessments" Atlet,"Tanggal Assessment","Pengambilan Keputusan","Pemahaman Taktik",Adaptasi,Komunikasi,"Visi Be | INFO | body response |
| P1 daftar atlet | Jumlah & id atlet yang ada | admin | - | total=5, kosong=false, idDitemukan=7,6 | INFO | GET /athletes |
| P1 admin mental/taktik id=1 | Status admin untuk mental/1 & taktik/1 | admin | - | mental/1=404, taktik/1=200 | INFO | GET |
| A1 /dashboard | Tamu membuka /dashboard | tamu | 302 → /login | 302 http://127.0.0.1:8000/login | LULUS | HTTP status + Location header |
| A1 /athletes | Tamu membuka /athletes | tamu | 302 → /login | 302 http://127.0.0.1:8000/login | LULUS | HTTP status + Location header |
| A1 /tests | Tamu membuka /tests | tamu | 302 → /login | 302 http://127.0.0.1:8000/login | LULUS | HTTP status + Location header |
| A1 /reports | Tamu membuka /reports | tamu | 302 → /login | 302 http://127.0.0.1:8000/login | LULUS | HTTP status + Location header |
| A7 | Registrasi publik ditutup (T2) | tamu | GET & POST /register = 404, tidak ada link "Daftar" di /login | GET 404, POST 404, linkDaftar=false, akun qa_test_user@example.com tidak dibuat | LULUS | screenshots/A7-register-ditutup.png |
| A8 | Tamu membuka /forgot-password (T4) | tamu | 404 (route dihapus) & /login menampilkan teks statis "Lupa password? Hubungi administrator." | HTTP 404, teksStatis=true | LULUS | GET /forgot-password |
| A2 | Login password salah | admin | tetap di /login + pesan error | POST 302, url=/login, errVisible=true, err="Email atau password salah." | LULUS | screenshots/A2-login-salah.png |
| A3 admin | Login admin | admin | 302 → /dashboard, sesi tersimpan | POST 302 → /dashboard | LULUS | state/admin.json |
| A3 fisio | Login fisio | fisio | 302 → /dashboard, sesi tersimpan | POST 302 → /dashboard | LULUS | state/fisio.json |
| A3 pelatih | Login pelatih | pelatih | 302 → /dashboard, sesi tersimpan | POST 302 → /dashboard | LULUS | state/pelatih.json |
| A3b admin | GET /dashboard dengan sesi admin | admin | 200 | HTTP 200 | LULUS | screenshots/A3b-dashboard-admin.png |
| A3b fisio | GET /dashboard dengan sesi fisio | fisio | 200 | HTTP 200 | LULUS | screenshots/A3b-dashboard-fisio.png |
| A3b pelatih | GET /dashboard dengan sesi pelatih | pelatih | 200 | HTTP 200 | LULUS | screenshots/A3b-dashboard-pelatih.png |
| B1 MATRIX tamu | Rekap matriks otorisasi | tamu | semua sesuai harapan | [{"url":"/","group":"root","expected":"REDIRECT_LOGIN","actual":302,"loc":"/login","ok":true},{"url":"/dashboard","group":"dashboard","expected":"REDIRECT_LOGIN","actual":302,"loc":"/login","ok":true},{"url":"/athletes","group":"athletes","expected":"REDIRECT_LOGIN","actual":302,"loc":"/login","ok":true},{"url":"/athletes/1","group":"athletes","expected":"REDIRECT_LOGIN","actual":302,"loc":"/login","ok":true},{"url":… | LULUS | matrix |
| B1 MATRIX admin | Rekap matriks otorisasi | admin | semua sesuai harapan | [{"url":"/","group":"root","expected":"AKSES","actual":302,"loc":"/dashboard","ok":true},{"url":"/dashboard","group":"dashboard","expected":"AKSES","actual":200,"loc":"","ok":true},{"url":"/athletes","group":"athletes","expected":"AKSES","actual":200,"loc":"","ok":true},{"url":"/athletes/1","group":"athletes","expected":"AKSES","actual":404,"loc":"","ok":true},{"url":"/tests","group":"tests","expected":"AKSES","actua… | LULUS | matrix |
| B1 MATRIX fisio | Rekap matriks otorisasi | fisio | semua sesuai harapan | [{"url":"/","group":"root","expected":"AKSES","actual":302,"loc":"/dashboard","ok":true},{"url":"/dashboard","group":"dashboard","expected":"AKSES","actual":200,"loc":"","ok":true},{"url":"/athletes","group":"athletes","expected":"AKSES","actual":200,"loc":"","ok":true},{"url":"/athletes/1","group":"athletes","expected":"AKSES","actual":404,"loc":"","ok":true},{"url":"/tests","group":"tests","expected":"AKSES","actua… | LULUS | matrix |
| B1 MATRIX pelatih | Rekap matriks otorisasi | pelatih | semua sesuai harapan | [{"url":"/","group":"root","expected":"AKSES","actual":302,"loc":"/dashboard","ok":true},{"url":"/dashboard","group":"dashboard","expected":"AKSES","actual":200,"loc":"","ok":true},{"url":"/athletes","group":"athletes","expected":"AKSES","actual":200,"loc":"","ok":true},{"url":"/athletes/1","group":"athletes","expected":"AKSES","actual":404,"loc":"","ok":true},{"url":"/tests","group":"tests","expected":"403","actual"… | LULUS | matrix |
| B2 POST /athletes | POST /athletes oleh pelatih (CSRF sah) | pelatih | 403 | HTTP 403 | LULUS | API request dengan X-CSRF-TOKEN valid |
| B2 PUT /athletes/1 | PUT /athletes/1 oleh pelatih (CSRF sah) | pelatih | 403 | HTTP 403 | LULUS | API request dengan X-CSRF-TOKEN valid |
| B2 DELETE /athletes/1 | DELETE /athletes/1 oleh pelatih (CSRF sah) | pelatih | 403 | HTTP 403 | LULUS | API request dengan X-CSRF-TOKEN valid |
| B2 side-effect | Data tidak berubah setelah ditolak | pelatih | tidak ada atlet QA_TEST_Akses_Ditolak | created=false, GET /athletes/1 (admin) = 404, body={ "message": "No query results for model [App\\Models\\Athlete] 1", "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoun | LULUS | GET /athletes + GET /athletes/1 sebagai admin |
| C beforeAll | Hook beforeAll skenario C dijalankan | admin | sekali per run | mulai | INFO | notes.json |
| C beforeAll selesai | Bukti beforeAll kedua (deteksi worker restart) | admin | - | dihapus=0 | INFO | notes.json |
| C1 | Tambah atlet tanpa foto | admin | 302, baris bertambah 1, toast sukses | POST 302, baris 2→3, tampil=true, toast=true, consoleErr=3, reqGagal=0 | LULUS | screenshots/C1-modal-tambah.png |
| C2 | Tambah atlet dengan foto (fisio) | fisio | tersimpan & path foto terisi di DB | POST 302, baris 3→4, photoPath=uploads/athletes/e444d7ca-38c4-47e7-adc2-5917dedb3026.png, label="<span style="color: rgb(0, 230, 118);">Foto saat ini: e444d7ca-38c4-47e7-adc2-5917dedb3026" | LULUS | screenshots/C2-modal-edit-foto.png |
| C3 | Validasi submit kosong | admin | pesan validasi tampil & data tidak tersimpan, tanpa 500 | POST 302, baris 4→4, tersimpan=false, adaMarker=false, pesanErrorTerlihat=true, consoleErr=3, reqGagal=- | LULUS | POST /athletes + GET /athletes + screenshots/C3-validasi.png |
| C4 | Validasi nama > 100 karakter | admin | pesan validasi tampil & data tidak tersimpan, tanpa 500 | POST 302, baris 4→4, tersimpan=false, adaMarker=true, pesanErrorTerlihat=true, consoleErr=3, reqGagal=- | LULUS | POST /athletes + GET /athletes + screenshots/C4-validasi.png |
| C5 | Validasi tanggal lahir masa depan | admin | pesan validasi tampil & data tidak tersimpan, tanpa 500 | POST 302, baris 4→4, tersimpan=false, adaMarker=true, pesanErrorTerlihat=true, consoleErr=3, reqGagal=- | LULUS | POST /athletes + GET /athletes + screenshots/C5-validasi.png |
| C6 | Validasi file bukan gambar | admin | pesan validasi tampil & data tidak tersimpan, tanpa 500 | POST 302, baris 4→4, tersimpan=false, adaMarker=true, pesanErrorTerlihat=true, consoleErr=3, reqGagal=- | LULUS | POST /athletes + GET /athletes + screenshots/C6-validasi.png |
| C7 | Validasi file > 2 MB | admin | pesan validasi tampil & data tidak tersimpan, tanpa 500 | POST 302, baris 4→4, tersimpan=false, adaMarker=true, pesanErrorTerlihat=true, consoleErr=3, reqGagal=- | LULUS | POST /athletes + GET /athletes + screenshots/C7-validasi.png |
| C8 | Edit atlet (ubah phone & berat) | admin | perubahan tersimpan & terbaca ulang | heal=ada, POST 302, namaForm="QA_TEST_Satu", phoneDB="089999999999", weightDB="71.5", tampilDiTabel=true | LULUS | screenshots/C8-modal-edit.png |
| C9 | Halaman detail atlet | admin | 200, nama & gender "Laki-laki" tampil | heal=ada, url=/athletes/39, genderLakiLaki=true, consoleErr=3, reqGagal=- | LULUS | screenshots/C9-detail-atlet.png |
| C10 | Upload foto bernama a"b'c<d>.png lalu buka modal Edit | admin | tanpa 500, tanpa alert, tanpa HTML tersuntik | POST 302, tersimpan=true, dialogs=[], sink={"labelHtml":"<span style=\"color: rgb(0, 230, 118);\">Foto saat ini: c570bd5c-05e9-47f9-95d4-e06dc9b73164.png</span><br>Format: JPG, PNG, GIF. Maksimal 2MB.","adaElemenD":false,"adaElemenImgPalsu":false,"photoPath":"uploads/athletes/c570bd5c-05e9-47f9-95d4-e06dc9b73164.png"}, injected=false, consoleErr=cdn.tailwindcss.com should not be used in production. To use Tailwind CS… | LULUS | screenshots/C10-modal-edit-xss.png |
| C11 | Upload nama aman xss_test.png (pembanding C10) | admin | 302 tersimpan, modal Edit menampilkan path foto, tanpa injeksi | POST 302, baris 5→6, tersimpan=true, photoPath=uploads/athletes/72ca703a-c667-48f4-b191-cb13842bce86.png, injected=false, dialogs=[], sink={"html":"<span style=\"color: rgb(0, 230, 118);\">Foto saat ini: 72ca703a-c667-48f4-b191-cb13842bce86.png</span><br>Format: JPG, PNG, GIF. Maksimal 2MB.","adaElemenD":false,"adaImgPalsu":false,"path":"uploads/athletes/72ca703a-c667-48f4-b191-cb13842bce86.png"}, consoleErr=4 | LULUS | screenshots/C11-modal-edit-aman.png |
| D1 endurance | Submit form tes endurance | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-endurance.png |
| D2 speed | Submit form tes speed (rating Kurang Sekali) | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-speed.png |
| D3 agility | Submit form tes agility | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-agility.png |
| D4 strength | Submit form tes strength (rating Sempurna) | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-strength.png |
| D5 power | Submit form tes power | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-power.png |
| D6 accuracy | Submit form tes accuracy (rating Weak) | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-accuracy.png |
| D7 reaction | Submit form tes reaction | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-reaction.png |
| D8 coordination | Submit form tes coordination | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-coordination.png |
| D9 flexibility | Submit form tes flexibility (rating Fair) | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-flexibility.png |
| D10 balance | Submit form tes balance | fisio | 302 + flash "berhasil disimpan", data tersimpan | POST 302, athleteId=39, selectTerisi=39, sukses=true, oldInputTersisa=false, pesanErrorTerlihat=false, consoleErr=0, reqGagal=- | LULUS | screenshots/D-balance.png |
| D11 | Detail atlet: hasil tes, gender, radar | fisio | 10/10 sumbu radar bernilai & 4 rating uji (Sempurna/Fair/Weak/Kurang Sekali) tampil | dinilai=10/10, barisTabel=10, genderLakiLaki=true, ratingTampil=[Sempurna, Fair, Weak, Kurang Sekali], adaSvgRadar=true, consoleErr=0, reqGagal=- \|\| AKAR (bukti DB tinker): rating tersimpan di kolom test_results (Sempurna/Fair/Weak/Kurang Sekali) tetapi AthleteController.php:98 default=>null tidak mengenali "Sempurna","Fair","Weak" → skor sumbu Kekuatan/Akurasi/Fleksibilitas null; tampilan tabel hanya menampilkan r… | LULUS | screenshots/D11-detail-hasil-tes.png + tinker SELECT test_results |
| E1 | Buat assessment mental (pelatih) | pelatih | 302, muncul di daftar /mental | POST 302, id=11, consoleErr=3, reqGagal=- | LULUS | screenshots/E1-mental-create.png |
| E2 | Lihat + ubah assessment mental | pelatih | GET 200 & isi tampil; PUT 302 & perubahan tersimpan | show=200, isiAwalTampil=true, PUT 302, tersimpanSetelahUbah=true, consoleErr=4 | LULUS | screenshots/E2-mental-show.png |
| E3 | Buat assessment taktik (admin, pemilik = admin) | admin | 302, muncul di daftar /taktik | POST 302, id=9, consoleErr=4 | LULUS | screenshots/E3-taktik-create.png |
| E4 | IDOR: pelatih akses assessment milik admin | pelatih | GET/PUT/DELETE → 403, data pemilik tidak berubah | GET 403, PUT 403, DELETE 403, dataPemilikTidakBerubah=true | LULUS | API request dengan token CSRF sah |
| E5 /reports | Export/akses /reports | pelatih | 200 dengan file berisi kolom + baris data | HTTP 200, type=text/html; charset=utf-8, disp=-, bytes=19817, isi="<!DOCTYPE html>\r \| <html lang="id">\r \| \r \| <head>\r \| <meta charset="UTF-8">\r \| <meta name="viewport" content="width=device-width, initial-scale=1.0">\r \| <meta name="csrf-token" content="wCWclBkon0rfjWds" | LULUS | GET /reports |
| E5 /reports/export/pdf | Export/akses /reports/export/pdf | pelatih | 200 dengan file berisi kolom + baris data | HTTP 200, type=application/pdf, disp=attachment; filename="laporan_assessment_pelatih_Coach Pelatih_2026-10-08.pdf", bytes=879852, isi="%PDF-1.7 \| 1 0 obj \| << /Type /Catalog \| /Outlines 2 0 R \| /Pages 3 0 R >> \| endobj \| 2 0 obj \| << /Type /Outlines /Count 0 >> \| endobj \| 3 0 obj \| << /Type /Pages \| /Kids [6 0 R \| ] \| /Count 1 \| /Resources << \| /ProcSet" | LULUS | GET /reports/export/pdf |
| E5 /reports/export/excel | Export/akses /reports/export/excel | pelatih | 200 dengan file berisi kolom + baris data | HTTP 200, type=text/csv; charset=UTF-8, disp=attachment; filename="laporan_assessment_pelatih_Coach Pelatih_2026-10-08.csv", bytes=719, isi=""Mental Assessments" \| Atlet,"Tanggal Assessment",Fokus,Motivasi,Konsentrasi,Tekanan,Keyakinan,Kepemimpinan,"Rata-rata Skor","Penilaian Keseluruhan",Rekomendasi \| QA_TEST_Aman,"08 Oct 2026 22:59",,,,,,,5,"QA_TEST_ penila" | LULUS | GET /reports/export/excel |
| E6 /report | Export/akses /report | fisio | 200, file terunduh (PDF valid, Excel berisi kolom) | HTTP 200, type=text/html; charset=utf-8, disp=-, bytes=28570, isi="<!DOCTYPE html>\r \| <html lang="id">\r \| \r \| <head>\r \| <meta charset="UTF-8">\r \| <meta name="viewport" content="width=device-width, initial-scale=1.0">\r \| <meta name="csrf-token" content="DSmW2IbyhE6VRY9J" | LULUS | GET /report |
| E6 /report/export/pdf | Export/akses /report/export/pdf | fisio | 200, file terunduh (PDF valid, Excel berisi kolom) | HTTP 200, type=application/pdf, disp=attachment; filename=laporan-screening-semua-atlet.pdf, bytes=1012898, isi="%PDF-1.7 \| 1 0 obj \| << /Type /Catalog \| /Outlines 2 0 R \| /Pages 3 0 R >> \| endobj \| 2 0 obj \| << /Type /Outlines /Count 0 >> \| endobj \| 3 0 obj \| << /Type /Pages \| /Kids [6 0 R \| 20 0 R \| 22 0 R \| 24 0 R \| 26 0 R \| 28 " | LULUS | GET /report/export/pdf |
| E6 /report/export/excel | Export/akses /report/export/excel | fisio | 200, file terunduh (PDF valid, Excel berisi kolom) | HTTP 200, type=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, disp=attachment; filename=laporan-screening-2026-10-08.xlsx, bytes=4270, isi="PK   u/I]v��   �     [Content_Types].xml���N�0�_���r@%遟#p(�8�Ċ��uK��8i� \| ��d�;3�h�r;Z�I{W�Xs�N�F������3J�0�a�'$�����X�:�x�R���T�H��.OZ-�\|�� ��Pެ׷Ry�Х"��.���I�i���\rq�pά�CF+Hy.��E)N����zh�\�%̓" | LULUS | GET /report/export/excel |
| E6 UI export Excel | Klik tombol Export Excel di /report (fisio) | fisio | file terunduh dengan nama & isi wajar | suggestedFilename=laporan-screening-2026-10-08.xlsx, bytes=4270 | LULUS | screenshot E6-report-fisio.png |
| E7 | Halaman laporan dengan data sendiri | pelatih + fisio | /reports dan /report → 200 | /reports=200 :: , /report=200 | LULUS | GET /reports & /report |
| E8 | Hapus assessment mental & taktik | pelatih + admin | terhapus (GET setelah → 404) | /mental/11: DELETE 302, GET setelah=404 ; /taktik/9: DELETE 302, GET setelah=404 | LULUS | UI delete + GET ulang |
| F1 | Dashboard admin | admin | 200, heading "Screening Fisioterapi Sepak Bola", kartu terisi (modul=12, atlet>0, kelengkapan<=100), 4 kartu tes fisik tampil | HTTP 200, heading="Screening Fisioterapi Sepak Bola", jenisTes=12, atletAktif=6, kelengkapan=50%, mentalHariIni=0, kartuFisik=1, consoleErr=0, reqGagal=- | LULUS | screenshots/F1-dashboard-admin.png |
| F2 | Dashboard pelatih (hanya mental+taktik) | pelatih | 200, heading "Assessment Pelatih Sepak Bola", jenis assessment=2, kartu tes fisik tidak tampil | HTTP 200, heading="Assessment Pelatih Sepak Bola", jenisAssessment=2, kartuFisik=0, kartuMental=1, consoleErr=0 | LULUS | screenshots/F2-dashboard-pelatih.png |
| F3 | Konsistensi angka dashboard antar role | admin + fisio + pelatih | nilai Atlet aktif sama untuk semua role dan > 0 | admin=200/6, fisio=200/6, pelatih=200/6 | LULUS | GET /dashboard × 3 |
| F4 | Navigasi sidebar dari dashboard | admin | link sidebar menuju halaman yang benar (200) | Data Atlet→/athletes ✓ ; Dashboard→/dashboard ✓, consoleErr=0 | LULUS | screenshot F4-nav.png |
| H1 | Konsol browser bersih (5 halaman utama) | admin | 0 error console; warning CDN dicatat terpisah | /dashboard[200] err=0 warn=1 reqGagal=- ; /athletes[200] err=0 warn=1 reqGagal=- ; /mental[200] err=0 warn=1 reqGagal=- ; /taktik[200] err=0 warn=1 reqGagal=- ; /report[200] err=0 warn=1 reqGagal=- \|\| totalError=0, totalWarning=5, contohError=- | LULUS | konsol Playwright per halaman |
| H2 | Layout mobile 375x812 | admin | tidak ada scroll horizontal pada document (tiap halaman 200) | /dashboard[200] scrollW=375/innerW=375 table.dashboard-table:1213, thead.:1213, tr.:1213 ; /athletes[200] scrollW=375/innerW=375 table.w-full:873, thead.:873, tr.:873 ; /report[200] scrollW=375/innerW=375 table.w-full:1499, thead.:1499, tr.:1499 | LULUS | screenshots/H2-mobile-*.png |
| H3 | Layout desktop 1366x768 | admin | tanpa overflow horizontal, halaman 200 | /dashboard[200] scrollW=1366/innerW=1366 ; /athletes[200] scrollW=1366/innerW=1366 ; /report[200] scrollW=1366/innerW=1366 | LULUS | screenshots/H3-desktop-*.png |
| H4 | Dependensi eksternal CDN di halaman aplikasi | admin | mencatat host eksternal yang dipanggil + warning terkait production | hostEksternal=cdn.tailwindcss.com(2), fonts.googleapis.com(1), fonts.gstatic.com(4), requestsKeCdnTailwind=2, warningCdn="cdn.tailwindcss.com should not be used in production. To use Tailwind CSS in production, install it as a PostCSS plugin or use the Tailwind CLI: https://tailwin" | LULUS | lib.js layouts/app.blade.php:11 (cdn.tailwindcss.com) |
| G1 | POST /athletes tanpa _token (sah login, tapi tanpa CSRF) | admin | 419 (TokenMismatch) & data tidak tersimpan | POST tanpa token=419; POST sebagai tamu=419 (redirect ke login bila 302) | LULUS | APIRequestContext tanpa kolom _token |
| G2 | Flag cookie sesi | admin | cookie sesi HttpOnly=true & SameSite=Lax/Strict | session=laravel-session httpOnly=true sameSite=Lax secure=false expires=1791507398.56023 \|\| semua=XSRF-TOKEN[httpOnly=false,sameSite=Lax,secure=false,path=/] \| laravel-session[httpOnly=true,sameSite=Lax,secure=false,path=/] | LULUS | browserContext.cookies() |
| G3 | Isi halaman error 500 (/reports) & kebocoran APP_DEBUG | pelatih | /reports tidak lagi 500 (assessment yatim diperbaiki T1); bila kelak ada 500 dan APP_DEBUG masih true, stack trace tampil = kebocoran (pemilik menyetel APP_DEBUG=false) | HTTP 200, adaStackTrace=false, adaPathInternal=false, bocorSecret=false, pemicu500Lain=tidak ada tanpa mengubah kode/data, isi="" | LULUS | GET /reports (pelatih) — sebelumnya 500 + stack trace, kini 200 |
| G4 | 404/route tak dikenal tidak membocorkan debug | admin | 404 tanpa stack trace & tanpa path internal | /halaman-tidak-ada-qa-test=404 stack=false path=false ; /athletes/999999=404 stack=false path=false ; /taktik/999999=404 stack=false path=false ; /api/tidak-ada=404 stack=false path=false | LULUS | GET 4 URL tak valid |
| G5 | Tamu membuka route terproteksi langsung via URL | guest | dialihkan ke /login (302) atau 401/403 — bukan 200 | /dashboard=302→http://127.0.0.1:8000/login ; /athletes=302→http://127.0.0.1:8000/login ; /mental=302→http://127.0.0.1:8000/login ; /taktik=302→http://127.0.0.1:8000/login ; /report=302→http://127.0.0.1:8000/login ; /reports=302→http://127.0.0.1:8000/login | LULUS | APIRequestContext tanpa cookie |
| I1 | Throttle POST /login (5/menit): 6 login salah | guest | percobaan 1–5 ditolak biasa (302), percobaan ke-6 → 429 Too Many Requests | #1=302, #2=302, #3=302, #4=302, #5=302, #6=429 | LULUS | POST /login × 6 berturut-turut dari 127.0.0.1 |
| I2 | Login setelah jeda throttle, logout, & tombol Back | fisio | login 302→/dashboard (bukan 429); logout 302→/login + sesi habis; Back tidak menampilkan dashboard terautentikasi | login=302→/dashboard, logout=302→/login, cookieSesiSisa=1, Back→/dashboard (adaHeadingDashboard=false, stale-bfcache, setelah reload→/login), GET /dashboard sesudah logout=302→/login | LULUS | screenshots/I2-login-pasca-throttle.png |

## 3. Daftar bug / temuan

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

## 4. Matriks otorisasi (role × route)

Total sel: 132 (4 role × 33 route), sel tidak sesuai harapan: 0. Sel merah ditebalkan dan ditandai silang.

| Route | Harapan | tamu | admin | fisio | pelatih |
|---|---|---|---|---|---|
| / | REDIRECT_LOGIN | 302 → /login ✓ | 302 → /dashboard ✓ | 302 → /dashboard ✓ | 302 → /dashboard ✓ |
| /dashboard | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 200 ✓ |
| /athletes | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 200 ✓ |
| /athletes/1 | REDIRECT_LOGIN | 302 → /login ✓ | 404 ✓ | 404 ✓ | 404 ✓ |
| /tests | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/endurance | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/speed | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/agility | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/strength | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/power | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/accuracy | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/reaction | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/coordination | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/flexibility | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /forms/balance | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /report | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /report/athlete/1 | REDIRECT_LOGIN | 302 → /login ✓ | 404 ✓ | 404 ✓ | 403 ✓ |
| /report/export/pdf | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /report/export/excel | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 200 ✓ | 403 ✓ |
| /mental | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /mental/create | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /mental/1 | REDIRECT_LOGIN | 302 → /login ✓ | 404 ✓ | 403 ✓ | 404 ✓ |
| /mental/1/edit | REDIRECT_LOGIN | 302 → /login ✓ | 404 ✓ | 403 ✓ | 404 ✓ |
| /taktik | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /taktik/create | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /taktik/1 | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 403 ✓ |
| /taktik/1/edit | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 403 ✓ |
| /reports | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /reports/export/pdf | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /reports/export/excel | REDIRECT_LOGIN | 302 → /login ✓ | 200 ✓ | 403 ✓ | 200 ✓ |
| /login | AKSES | 200 ✓ | 302 → /dashboard ✓ | 302 → /dashboard ✓ | 302 → /dashboard ✓ |
| /register | 404 | 404 ✓ | 404 ✓ | 404 ✓ | 404 ✓ |
| /forgot-password | 404 | 404 ✓ | 404 ✓ | 404 ✓ | 404 ✓ |

Catatan: tidak ada sel yang menyimpang. Sebelum TUGAS 4, **pelatih** ditolak 500 pada `/reports`, `/reports/export/pdf`, `/reports/export/excel` (bug B-01, kini diperbaiki T1 `f980cd1`), dan `/register` + `/forgot-password` kini 404 untuk semua role (T2 `fc7a8cd`, T4 `888806e`).
Pelatih ditolak 403 pada route fisik (POST/PUT/DELETE /athletes — lihat B2) dan pada /taktik milik admin (E4).

## 5. Hal yang tidak bisa diuji

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
