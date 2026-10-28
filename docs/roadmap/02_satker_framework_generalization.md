# 02. Rencana Pelaksanaan Fase 2: Generalisasi Framework Dokter-V Multi-Satker

> [!INFO] **Metadata Dokumen**
> - **ID Rencana**: `PLN-PHASE-02`
> - **Fase**: Fase 2 — *Satker Framework Generalization & Invariant/Variant Decoupling*
> - **Status**: `PROPOSED / READY FOR EXECUTION`
> - **Dokumen Induk**: [Roadmap Keseluruhan Dokter-V](../ROADMAP.md)
> - **Target Implementasi**: Satuan Kerja Badan Pusat Statistik (BPS) Kabupaten/Kota se-Indonesia

---

## 1. Latar Belakang dan Tujuan

Aplikasi **Dokter-V** berawal sebagai solusi otomasi administrasi, persuratan tugas, dan tata kelola honor mitra statistik di BPS Kabupaten Mempawah (Satker 6104). Seiring dengan terbuktinya efektivitas sistem ini dalam mencegah bentrok penugasan, memastikan kepatuhan batas honor SBM 2026, dan mempermudah approval pimpinan via WhatsApp, **banyak Satker BPS Kabupaten/Kota lain yang menyatakan minat untuk mereplikasi dan menerapkan Dokter-V di kantor mereka**.

Namun, pada basis kode saat ini masih terdapat sejumlah komponen dan data spesifik BPS Mempawah yang tertanam (*hardcoded* atau *tightly coupled*), seperti:
1. Kode satker `6104` dan `61041` yang tersebar pada logika format penomoran surat ([`NomorSurat.php`](file:///home/ihza/Projects/sipedas/app/Models/NomorSurat.php)) dan master SLS.
2. Daftar kecamatan, preset rute perjadin, dan koordinat perjalanan dinas yang masih mengunci 9 kecamatan di wilayah Kabupaten Mempawah ([`PresetRutePerjadin.php`](file:///home/ihza/Projects/sipedas/app/Models/PresetRutePerjadin.php)).
3. Seeder data awal yang menyertakan data pegawai organik Mempawah ([`PegawaiSeeder.php`](file:///home/ihza/Projects/sipedas/database/seeders/PegawaiSeeder.php)).
4. Teks antarmuka autentikasi dan template surat yang menyebutkan identitas "BPS Kabupaten Mempawah".

### Tujuan Fase 2:
Membangun abstraksi arsitektural yang **memisahkan secara tegas antara komponen INVARIAN (inti framework) dan VARIAN (parameter spesifik satker)**, serta menyediakan **mekanisme inisialisasi yang instan, ramah pengembang (*developer-friendly*), dan minim friksi** agar pengembang di satker BPS manapun dapat menjalankan Dokter-V dalam hitungan menit.

---

## 2. Pemisahan Komponen: Invarian vs. Varian

Prinsip utama modularisasi Dokter-V adalah: **"Tulis sekali logika tata kelolanya, sesuaikan parameternya per kantor."**

```mermaid
graph TD
    subgraph INVARIANT ["KOMPONEN INVARIAN (Inti Framework Dokter-V)"]
        direction TB
        INV1["State Machine Penugasan & SPPD<br/>(Draf -> Dikirim -> Disetujui -> Dicairkan)"]
        INV2["Sipancong Payment Verification Pipeline<br/>(Bendahara -> PPSPM -> PPK)"]
        INV3["Dokter-V SBM Engine & Aggregation<br/>(Validasi Batas Honor PMK SBM 2026 & SPK Bulanan)"]
        INV4["Algoritma Deteksi Bentrok Jadwal Penugasan"]
        INV5["WhatsApp Multi-Adapter Notifier<br/>(Aina / Whatsmeow / Fonnte / WPPConnect)"]
        INV6["Filament Shield RBAC & SSO SAML 2.0 Engine"]
        INV7["LPD AI Reporting Engine & Prompt Builder"]
        INV8["Runtime Container Production<br/>(FrankenPHP + Octane + OPcache + Auto Log Pruning)"]
    end

    subgraph CONFIG_BRIDGE ["ABSTRAKSI PENGHUBUNG (SSOT Config & Profile)"]
        direction TB
        BR1["config/satker.php (SSOT Environment & File Config)"]
        BR2["Tabel Database settings / pengaturans"]
        BR3["Template Tokenizer Penomoran Surat"]
        BR4["Dynamic Wilayah & Route Resolver Adapter"]
    end

    subgraph VARIANT ["KOMPONEN VARIAN (Spesifik per Satuan Kerja BPS)"]
        direction TB
        VAR1["Identitas Satker<br/>(Kode: 6104, Nama: BPS Kab. Mempawah, Kedudukan: Mempawah)"]
        VAR2["Pejabat Definitif & PLH<br/>(NIP Kepala, NIP Kasubbag TU, NIP PPK, NIP Bendahara)"]
        VAR3["Master Wilayah Administratif<br/>(Daftar Kecamatan, Desa/Kelurahan, SLS lokal)"]
        VAR4["Preset Rute & Biaya Transport Lokal<br/>(Titik keberangkatan, jarak, tarif SBU lokal)"]
        VAR5["Database Pegawai Organik & Mitra Statistik Lokal"]
        VAR6["Konfigurasi Gateway WhatsApp Satker & Akun DIPA"]
    end

    INVARIANT --> CONFIG_BRIDGE
    CONFIG_BRIDGE --> VARIANT
```

### Matriks Klasifikasi Rinci

| Dimensi | Invarian (Tetap Sama di Semua Satker BPS) | Varian (Dikonfigurasi per Satker BPS) |
|:---|:---|:---|
| **Alur Kerja (Workflow)** | Alur verifikasi pembayaran Sipancong, siklus hidup Surat Tugas, mode delegasi approval (Kasubbag TU / Kepala). | Penerima tugas definitif, pejabat penandatangan, nomor rekening tujuan pencairan. |
| **Aturan Honor & SPK** | Rumus batas SBM honor mitra (Survei Rp 3.353.000, Sensus Rp 4.694.000), aturan konsolidasi kontrak bulanan. | Daftar nama mitra, besaran target kegiatan, alokasi anggaran MAK/DIPA satker. |
| **Penomoran Surat** | Logika counter urut tahunan, pencegahan nomor ganda, reset tahun baru, format baku arsip BPS (KP, PL, VS). | Kode satker pada surat (misal: `61041` vs `32041`), kode seksi pembina teknis. |
| **Perjalanan Dinas (SPPD)** | Logika anti-bentrok tanggal, struktur formulir SPPD 1.2 dan 1.3 Perka BPS, template visum. | Titik kedudukan kantor, kecamatan tujuan, daftar desa, waktu tempuh, dan tarif transport lokal. |
| **Autentikasi** | Integrasi SAML 2.0 (SSO BPS rIDM), manajemen peran (RBAC) Filament Shield. | Base URL satker, entity ID SAML, nama instansi pada halaman login. |
| **Notifikasi** | Normalisasi nomor telepon (`628...`), sistem preferensi opt-out, template pesan WhatsApp dinamis. | Nomor WhatsApp bot satker, API Key provider WhatsApp (Whatsmeow/Aina/Fonnte). |
| **Infrastruktur** | Dockerfile multi-stage (FrankenPHP 8.4), auto-pruning log, rotasi log daily, Caddyfile reverse-proxy. | Domain produksi (misal: `admin.dvlpid.my.id` vs `sipedas.bps3204.id`), kredensial database. |

---

## 3. Struktur Dokumen Pelaksanaan Terinci (Numbered Sub-Plans)

Karena implementasi Fase 2 mencakup banyak aspek teknis dan dapat berkembang hingga puluhan modul, setiap komponen dipecah menjadi **berkas rencana bernomor mandiri (*numbered sub-plans*)** pada direktori [`docs/roadmap/phase-02/`](phase-02/):

| No. | Kode Dokumen | Rencana Kerja & Spesifikasi Teknis | Status | Prioritas |
|:---:|:---|:---|:---:|:---:|
| 01 | [**01_ssot_konfigurasi_satker.md**](phase-02/01_ssot_konfigurasi_satker.md) | Abstraksi SSOT Konfigurasi Satker (`config/satker.php` & Helper Fallback) | `Proposed` | P0 (Kritis) |
| 02 | [**02_tokenizer_penomoran_surat.md**](phase-02/02_tokenizer_penomoran_surat.md) | Mesin Penomoran Surat Berbasis Token (Surat Tugas, SPPD, BAST, SPK) | `Proposed` | P0 (Kritis) |
| 03 | [**03_desentralisasi_wilayah_dan_rute.md**](phase-02/03_desentralisasi_wilayah_dan_rute.md) | Desentralisasi Master Wilayah, Rute Perjadin, dan Prompt LPD AI | `Proposed` | P1 (Tinggi) |
| 04 | [**04_cli_setup_wizard_satker_init.md**](phase-02/04_cli_setup_wizard_satker_init.md) | Interactive CLI Setup Wizard (`php artisan satker:init`) | `Proposed` | P1 (Tinggi) |
| 05 | [**05_scaffolding_import_export_data.md**](phase-02/05_scaffolding_import_export_data.md) | Starter Pack: Data Pejabat, Pegawai, Mitra, & Master SLS (Template & Lokasi) | `Proposed` | P0 (Kritis) |
| 06 | [**06_developer_quickstart_kit.md**](phase-02/06_developer_quickstart_kit.md) | Developer Quickstart Kit, Docker Dev Starter, & Panduan Onboarding | `Proposed` | P1 (Tinggi) |
| 07 | [**07_branding_dan_kop_surat_dinamis.md**](phase-02/07_branding_dan_kop_surat_dinamis.md) | Branding, Logo Satker, dan Komponen Kop Naskah Dinas PDF Dinamis | `Proposed` | P2 (Menengah) |
| 08 | [**08_alokasi_anggaran_dipa_mak.md**](phase-02/08_alokasi_anggaran_dipa_mak.md) | Manajemen Akun Anggaran DIPA, Kode MAK, dan Validasi Pagu Belanja | `Proposed` | P2 (Menengah) |
| 09 | [**09_gateway_whatsapp_satker.md**](phase-02/09_gateway_whatsapp_satker.md) | Profil Multi-Gateway WhatsApp Satker (Whatsmeow/Aina/Fonnte/WAHA) | `Proposed` | P2 (Menengah) |
| 10 | [**10_qa_matrix_regresi_mempawah.md**](phase-02/10_qa_matrix_regresi_mempawah.md) | Matriks QA, Multi-Satker Test Suite, dan Garansi Zero-Regresi Mempawah | `Proposed` | P0 (Kritis) |
| 11 | [**11_fleksibilitas_autentikasi_dan_sso.md**](phase-02/11_fleksibilitas_autentikasi_dan_sso.md) | Fleksibilitas Autentikasi: Mode Lokal (Email/Password), SSO SAML, dan Hybrid | `Proposed` | P0 (Kritis) |

---

## 4. Rencana Tahapan Eksekusi (Execution Timeline)

Pelaksanaan 10 modul bernomor di atas diorganisasikan ke dalam timeline bertahap:

```mermaid
gantt
    title Roadmap Pelaksanaan Fase 2: Generalisasi Framework Dokter-V
    dateFormat  X
    axisFormat W%W
    section Milestones
    01. Abstraksi SSOT Konfigurasi Satker             :active, m1, 0, 3
    02. Mesin Penomoran Surat Berbasis Token          :m2, after m1, 3
    03. Desentralisasi Preset Rute & Wilayah          :m3, after m2, 4
    04. Interactive Satker Setup Wizard CLI           :m4, after m3, 3
    05. Scaffolding Import/Export Data Satker         :m5, after m4, 3
    06. Developer Quickstart Kit & Onboarding         :m6, after m5, 2
    07-09. Branding, DIPA, & WhatsApp Satker          :m7, after m6, 3
    10. QA Verification & Zero-Regression Gauntlet    :m8, after m7, 2
```

---

### Ringkasan Teknis Modul Inti:

#### Target:
Membuat konfigurasi terpusat yang menjadi *Single Source of Truth* (SSOT) untuk seluruh identitas dan parameter kantor.

#### Item Pekerjaan:
1. **Membuat berkas `config/satker.php`**:
   ```php
   return [
       'kode' => env('SATKER_KODE', '6104'),
       'kode_surat' => env('SATKER_KODE_SURAT', env('SATKER_KODE', '6104') . '1'),
       'nama' => env('SATKER_NAMA', 'BPS Kabupaten Mempawah'),
       'level' => env('SATKER_LEVEL', 'kako'), // 'kako' | 'provinsi'
       'kabkot' => env('SATKER_KABKOT', 'Kabupaten Mempawah'),
       'provinsi' => env('SATKER_PROVINSI', 'Kalimantan Barat'),
       'ibukota' => env('SATKER_IBUKOTA', 'Mempawah'),
       'alamat' => env('SATKER_ALAMAT', 'Jl. Raden Kusno No. 1, Mempawah'),
       'telepon' => env('SATKER_TELEPON', ''),
       'email' => env('SATKER_EMAIL', 'bps6104@bps.go.id'),
       'website' => env('SATKER_WEBSITE', 'https://mempawahkab.bps.go.id'),

       // Pejabat Definitif Default
       'pejabat' => [
           'nip_kepala' => env('SATKER_NIP_KEPALA', null),
           'nip_kasubbag' => env('SATKER_NIP_KASUBBAG', null),
           'nip_ppk' => env('SATKER_NIP_PPK', null),
           'nip_bendahara' => env('SATKER_NIP_BENDAHARA', null),
       ],

       // Preferensi Operasional
       'defaults' => [
           'approval_mode' => env('ST_APPROVAL_MODE', 'delegasi_ppspm'),
           'auto_approve_ppk' => env('PPK_AUTO_APPROVE', false),
       ],
   ];
   ```
2. **Sinkronisasi Helper `Pengaturan` & `Setting`**:
   Menghubungkan model `Pengaturan` dan `Setting` dengan `config('satker.*')` sebagai nilai bawaan (*fallback*), sehingga jika nilai belum ada di basis data, sistem otomatis membaca konfigurasi environment.
3. **Membersihkan Hardcoded UI**:
   Mengganti teks *"Masuk via SSO BPS Mempawah"* di [Login.php](file:///home/ihza/Projects/sipedas/app/Filament/Pages/Auth/Login.php) menjadi:
   `"Masuk via SSO " . config('satker.nama')`.

---

### Milestone 2.2: Mesin Penomoran Surat Berbasis Token (Template Tokenizer)

#### Target:
Menghilangkan string format hardcoded `/61041/...` pada model [`NomorSurat.php`](file:///home/ihza/Projects/sipedas/app/Models/NomorSurat.php) dan menggantinya dengan template token yang dinamis.

#### Item Pekerjaan:
1. **Pola Template Token**:
   Menyediakan placeholder standar:
   - `{nomor}`: Nomor urut (misal: `042`)
   - `{sub_nomor}`: Sub-nomor jika ada (misal: `.1`)
   - `{kode_satker}`: Kode satker dinamis (misal: `6104` atau `3204`)
   - `{kode_surat}`: Kode satker persuratan (misal: `61041` atau `32041`)
   - `{kode_klasifikasi}`: Kode klasifikasi persuratan BPS (misal: `KP.650`, `PL.300`, `VS.100`)
   - `{bulan}`: Angka bulan (misal: `03`) atau romawi (`III`)
   - `{tahun}`: Tahun 4 digit (misal: `2026`)
2. **Konfigurasi Format Surat di `config/satker.php` atau Tabel `Pengaturan`**:
   - `FORMAT_NOMOR_SURAT_TUGAS`: `B-{nomor}{sub_nomor}/{kode_surat}/{kode_klasifikasi}/{bulan}/{tahun}`
   - `FORMAT_NOMOR_SPPD`: `{nomor}{sub_nomor}/{kode_surat}/PL.300/{bulan}/{tahun}`
   - `FORMAT_NOMOR_BAST`: `{nomor}{sub_nomor}/{kode_satker}/BAST/{bulan}/{tahun}`
3. **Engine Pengganti Token**:
   Method privat `renderFormat(string $template, array $data)` di `NomorSurat.php` yang mengevaluasi token secara aman dan mengembalikan string nomor resmi.

---

### Milestone 2.3: Desentralisasi Preset Rute Perjalanan Dinas & Master Wilayah

#### Target:
Menghapus dependensi hardcoded 9 kecamatan Mempawah di [`PresetRutePerjadin.php`](file:///home/ihza/Projects/sipedas/app/Models/PresetRutePerjadin.php) dan menghubungkannya dengan data wilayah lokal yang dinamis.

#### Item Pekerjaan:
1. **Dynamic Departure Point (Kedudukan Kantor)**:
   Titik awal perjalanan dinas ("Mempawah") dijadikan variabel: `config('satker.ibukota')`.
2. **Generikasi Deskripsi AI & Narasi Lapangan**:
   Mengganti string prompt AI di [`AIService.php`](file:///home/ihza/Projects/sipedas/app/Services/AIService.php) dari:
   *"Perjalanan dari Mempawah menuju {$daerah}..."*
   menjadi:
   *"Perjalanan dari " . config('satker.ibukota') . " menuju {$daerah}..."*.
3. **Master Wilayah Seeder Dinamis**:
   Menyediakan `WilayahSatkerSeeder` yang dapat memuat data kecamatan, kelurahan/desa, dan SLS berdasarkan berkas CSV standar BPS (Master Wilayah Wilkerstat / MFD Online).

---

### Milestone 2.4: Interactive Satker Setup Wizard CLI (`php artisan satker:init`)

#### Target:
Membangun perintah CLI interaktif yang memandu pengembang satker lain menyiapkan konfigurasi awal dalam waktu kurang dari 3 menit tanpa perlu menyentuh kode program.

#### Blueprint Alur CLI:
```text
$ php artisan satker:init

 ┌──────────────────────────────────────────────────────────────┐
 │             DOKTER-V SATKER INITIALIZATION WIZARD            │
 │     Kerangka Kerja Tata Kelola & Otomasi BPS Kab/Kota        │
 └──────────────────────────────────────────────────────────────┘

 ? Masukkan 4 Digit Kode Satker BPS (misal: 3204, 6104): 3204
 ? Nama Satuan Kerja: BPS Kabupaten Bandung
 ? Level Satuan Kerja [KAKO/PROVINSI] [KAKO]: KAKO
 ? Nama Kabupaten/Kota: Kabupaten Bandung
 ? Nama Ibu Kota / Kedudukan Kantor: Soreang
 ? Nama Provinsi: Jawa Barat
 ? Alamat Kantor: Jl. Raya Soreang No. 123, Soreang
 ? Email Resmi Satker: bps3204@bps.go.id
 ? NIP Kepala Satker: 197501011998031001
 ? NIP Kasubbag Tata Usaha (PPSPM): 198205122006042002
 ? NIP Pembuat Komitmen (PPK): 199003152014021003
 ? NIP Bendahara Pengeluaran: 199508202018012004
 ? Pilih Mode Approval Surat Tugas Utama:
   [1] Delegasi Kasubbag TU / PPSPM (Rekomendasi - Pimpinan & Kasubbag)
   [2] Kepala Satker Saja
   [3] PPSPM Saja
   [4] Otomatis Disetujui (Auto-Approve)
 > 1

 [✓] File .env berhasil diperbarui dengan profil Satker 3204.
 [✓] Konfigurasi tabel pengaturan database berhasil disinkronkan.
 [✓] Format penomoran surat disesuaikan ke kode 32041.
 [✓] Akun Administrator awal berhasil dibuat (Email: admin.bps3204@bps.go.id).

 Selesai! Jalankan 'php artisan serve' atau akses aplikasi di browser Anda.
```

---

### Milestone 2.5: Scaffolding Import/Export Data Pegawai & Mitra Satker

#### Target:
Memudahkan pemindahan data pegawai dan mitra statistik dari satker lain ke dalam Dokter-V.

#### Item Pekerjaan:
1. **Template Excel/CSV Resmi**:
   - `pegawai_template.xlsx`: Kolom NIP, Nama, Gelar Depan, Gelar Belakang, Pangkat, Golongan, Jabatan, Email BPS, Nomor WhatsApp.
   - `mitra_template.xlsx`: Kolom ID Sobat, NIK, Nama Lengkap, Alamat, Kecamatan, Desa, Nomor HP/WA, Nomor Rekening, Nama Bank.
2. **Command CLI Import**:
   - `php artisan satker:import-pegawai {filepath}`
   - `php artisan satker:import-mitra {filepath}`
3. **UI Import Terintegrasi di Filament**:
   Menyediakan tombol *"Unggah Data Pegawai"* dan *"Unggah Data Mitra"* langsung pada tabel Filament dengan preview dan validasi sebelum penyimpanan.

---

### Milestone 2.6: Developer Quickstart Kit & Docker Dev Starter

#### Target:
Memastikan setiap pengembang lokal di satker lain dapat melakukan *one-command startup*.

#### Item Pekerjaan:
1. **Penyempurnaan `docker-compose.dev.yml`**:
   Menghubungkan container dengan variabel `.env` satker dan memastikan auto-sync dependency berjalan mulus.
2. **Pembuatan Dokumen Panduan Pengembang**:
   - `docs/DEVELOPER_QUICKSTART.md`: Langkah demi langkah instalasi untuk pengembang satker (Prasyarat: PHP 8.4 / Docker, MySQL/MariaDB, Composer).
   - Menjelaskan arsitektur, cara custom kop surat, dan cara menghubungkan WhatsApp bot satker.

---

## 4. Matriks Evaluasi & Quality Gate

Untuk memastikan transisi ke framework multi-satker tidak merusak stabilitas yang sudah ada di BPS Mempawah (*Zero Regression*):

| Gerbang Mutu (Quality Gate) | Kriteria Lolos (Passing Criteria) | Alat Verifikasi |
|:---|:---|:---|
| **Backward Compatibility** | Seluruh konfigurasi default tetap mengacu ke BPS Mempawah (6104) jika satker lain belum melakukan konfigurasi kustom. | `php artisan test` (100% lulus) |
| **Pemisahan Invarian/Varian** | 0 hardcoded string kode satker atau nama kecamatan di core service (`app/Services/`). Seluruhnya membaca dari `config('satker.*')`. | `grep_search` & PHPStan Level 5 |
| **CLI Idempotency** | Perintah `php artisan satker:init` aman dijalankan berulang kali tanpa merusak integritas basis data. | Unit Test khusus `SatkerInitCommandTest` |
| **Storage Safety** | Rotasi log `daily` dan pruning log >50MB tetap aktif di semua konfigurasi satker baru. | `StorageMaintenanceTest` |

---

## 5. Rujukan Dokumen Terkait
- [Dokter-V Overall Roadmap](../ROADMAP.md)
- [Arsitektur Sistem (arc42)](../architecture/01_introduction_and_goals.md)
- [Aturan Bisnis & Kontrak (Business Rules)](../BUSINESS_RULES.md)
- [Dokumentasi Panduan Produksi](../../PRODUCTION_GUIDE.md)
