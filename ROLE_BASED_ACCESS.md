# 📋 Sistem Role-Based Access Control - FootScreen Pro

## Overview

Sistem screening fisioterapi sepak bola telah diperbarui dengan sistem role-based access control yang membagi pengguna menjadi 3 peran dengan akses dan menu yang berbeda.

---

## 🔐 Tiga Role Pengguna

### 1. **Admin** 👨‍💼

**Akses Penuh ke Semua Fitur**

- ✅ Screening Fisioterapi (semua tes fisik)
- ✅ Mental Assessment
- ✅ Taktik Assessment
- ✅ Laporan Fisioterapi
- ✅ Manajemen Atlet
- ✅ Dashboard Overview

**Login**: `admin@footscreen.com` / `password123`

---

### 2. **Fisioterapis** 🏥

**Fokus pada Screening & Assessment Fisik**

**Akses:**

- ✅ Dashboard
- ✅ Data Atlet
- ✅ **Screening Fisioterapi** (10 jenis tes):
    - 🏃 Endurance (YoYo Test)
    - ⚡ Speed 50M
    - 🔄 Agility Test
    - 💪 Strength (Push-up, Sit-up)
    - 🦵 Power Jump
    - 🎯 Akurasi Tendangan
    - 👁️ Kecepatan Reaksi
    - 🌀 Koordinasi
    - 🦶 Fleksibilitas Ankle
    - ⚖️ Y Balance Test
- ✅ **Laporan Fisioterapi**
- ❌ Tidak bisa akses Menu Mental & Taktik

**Login**: `fisio@footscreen.com` / `password123`

---

### 3. **Pelatih** ⚽

**Fokus pada Aspek Mental & Taktik**

**Akses:**

- ✅ Dashboard
- ✅ Data Atlet
- ✅ **Mental Assessment** - Penilaian kondisi mental atlet:
    - 🧠 Kepercayaan Diri (1-5)
    - 🎯 Konsentrasi (1-5)
    - 😰 Kecemasan (1-5)
    - 💪 Motivasi (1-5)
    - 🤝 Semangat Tim (1-5)
    - 🔥 Penanganan Tekanan (1-5)
- ✅ **Taktik Assessment** - Penilaian kemampuan taktik:
    - 🎮 Dribbling (1-5)
    - 🎯 Passing (1-5)
    - ⚽ Shooting (1-5)
    - 📍 Positioning (1-5)
    - 🧠 Pembacaan Permainan (1-5)
    - ⚡ Pengambilan Keputusan (1-5)
    - 🛡️ Pertahanan (1-5)
    - 🎲 Set Pieces (1-5)

- ❌ Tidak bisa akses Screening Fisioterapi
- ❌ Tidak bisa akses Laporan Fisioterapi

**Login**: `pelatih@footscreen.com` / `password123`

---

## 📊 Struktur Data

### Mental Assessment

Setiap penilaian mental mencakup:

- Pilihan atlet
- Tanggal assessment
- Skor untuk 6 kategori mental (skala 1-5)
- Catatan per kategori
- Penilaian keseluruhan (narasi)
- Rekomendasi peningkatan

### Taktik Assessment

Setiap penilaian taktik mencakup:

- Pilihan atlet
- Tanggal assessment
- Skor untuk 8 kategori taktik (skala 1-5)
- Catatan per kategori
- Penilaian keseluruhan (narasi)
- Rekomendasi peningkatan
- Catatan formasi & taktik khusus

---

## 🎯 Pembagian Tanggung Jawab

### Fisioterapis Menangani

- **Data Objektif**: Pengukuran fisik dengan alat & standar internasional
- **Tes Standar**: YoYo, Speed, Agility, Strength, Power, dll
- **Output**: Laporan fisioterapi dengan rekomendasi

### Pelatih Menangani

- **Data Kualitatif**: Penilaian subjektif berdasarkan observasi
- **Mental**: Kondisi psikis dan emosi atlet (skala 1-5)
- **Taktik**: Kemampuan teknik bermain (skala 1-5)
- **Narasi**: Deskripsi dan rekomendasi berbasis pengalaman pelatih

---

## 📱 Navigasi Berdasarkan Role

### Menu Fisioterapi (Fisio & Admin)

```
🏥 SCREENING FISIOTERAPI
├── 🧪 Semua Tes
├── 🏃 Endurance (YoYo)
├── ⚡ Speed 50M
├── 🔄 Agility Test
├── 💪 Strength
└── 🦵 Power Jump

📋 TES FUNGSIONAL
├── 🎯 Akurasi Tendangan
├── 👁️ Kecepatan Reaksi
├── 🌀 Koordinasi
├── 🦶 Fleksibilitas Ankle
└── ⚖️ Y Balance Test

📊 OUTPUT
└── 📋 Laporan Fisioterapi
```

### Menu Pelatih (Pelatih & Admin)

```
🧠 ASSESSMENT MENTAL
├── 🧠 Mental Assessment
└── ➕ Tambah Assessment

⚽ ASSESSMENT TAKTIK
├── ⚽ Taktik Assessment
└── ➕ Tambah Assessment
```

---

## 🔄 Workflow Contoh

### Skenario 1: Evaluasi Lengkap Atlet

1. **Fisio** melakukan screening fisioterapi (10 tes) → generate laporan
2. **Pelatih** mengisi mental assessment → evaluasi aspek psikis
3. **Pelatih** mengisi taktik assessment → evaluasi kemampuan bermain
4. **Admin** dapat melihat semua data untuk overview keseluruhan

### Skenario 2: Monitoring Progress

- Fisio tracking peningkatan fisik atlet melalui tes berkala
- Pelatih tracking peningkatan mental & taktik melalui assessment berkala
- Data berkumpul dalam sistem untuk analisis trend

---

## 🛡️ Fitur Keamanan

✅ **Role-based Middleware**: Setiap route dilindungi dengan middleware pengecekan role
✅ **Authorization Check**: Controller mengecek kepemilikan data sebelum edit/delete
✅ **Soft Deletes**: Data tidak benar-benar dihapus, hanya ditandai deleted_at
✅ **Encrypted Password**: Semua password dienkripsi dengan Bcrypt
✅ **CSRF Protection**: Semua form dilindungi dengan token CSRF

---

## 📝 Cara Menggunakan

### Untuk Fisioterapis

1. Login dengan `fisio@footscreen.com`
2. Klik menu Tes Fisik di sidebar
3. Pilih jenis tes yang ingin diisi
4. Isi form dan submit
5. Lihat laporan di menu Output

### Untuk Pelatih

1. Login dengan `pelatih@footscreen.com`
2. Klik menu Mental Assessment atau Taktik Assessment
3. Klik "+ Tambah Assessment"
4. Pilih atlet dan tanggal
5. Isi form penilaian (skala 1-5)
6. Tambahkan catatan dan rekomendasi
7. Submit dan lihat hasil di list assessments

### Untuk Admin

1. Login dengan `admin@footscreen.com`
2. Akses semua menu dari kedua role
3. Gunakan dashboard untuk overview keseluruhan

---

## 🔑 Password Default

- **Admin**: `password123`
- **Fisio**: `password123`
- **Pelatih**: `password123`

⚠️ **Rekomendasi**: Ubah password semua akun setelah setup awal!

---

## 📦 Teknologi yang Digunakan

- **Laravel 12** - Backend framework
- **Blade Templates** - Server-side rendering
- **TailwindCSS** - Styling responsif
- **MySQL** - Database
- **Eloquent ORM** - Database abstraction
- **Laravel Middleware** - Role-based access control

---

_Last Updated: April 23, 2026_
