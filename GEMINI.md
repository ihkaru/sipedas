# GEMINI.md — Architecture Standards, Development Protocol & Quality Gates

> [!NOTE] **Repository Ground Truth & Naming Taxonomy**
> - **Official Application Name**: **Dokter V** — Nama resmi dari **keseluruhan aplikasi dan platform** ini.
> - **Repository Codename**: **SiPEDAS** — Nama lama historis yang saat ini **hanya berfungsi sebagai codename repositori** Git (`ihkaru/sipedas`).
> - **Module Codename**: **Sipancong** — Hanyalah **codename internal untuk modul alur pengajuan & verifikasi pembayaran** keuangan (`app/Models/Sipancong/`, rute pembayaran).
> - **Domain**: Tata Kelola Penugasan, Perjalanan Dinas (SPPD), Verifikasi Berkas Pembayaran Keuangan, dan Pembayaran Honor Mitra Statistik (SBM 2026) di lingkungan Badan Pusat Statistik (BPS).
> - **Framework Roadmap**: [docs/ROADMAP.md](docs/ROADMAP.md) | [Rencana Pelaksanaan Fase 2](docs/roadmap/phase-02/README.md)
> - **Portability Contract**: File ini berkomitmen 100% portabel dan bebas dari referensi path lokal maupun kredensial mesin tertentu.

---

## 1. Arsitektur Inti: Pemisahan Invarian vs. Varian

Aplikasi Dokter-V dirancang untuk dapat diadopsi oleh seluruh Satuan Kerja BPS Kabupaten/Kota di Indonesia. Setiap pengembangan fitur baru wajib mematuhi pemisahan berikut:

### 1.1 Komponen Invarian (Core Framework — Tidak Boleh Diubah per Satker)
1. **Workflows & State Machines**:
   - Siklus hidup Surat Tugas: `DRAFT` $\rightarrow$ `DIKIRIM` $\rightarrow$ `DISETUJUI` / `PERLU_REVISI` / `DITOLAK` $\rightarrow$ `DICAIRKAN` / `DIBATALKAN`.
   - Pipeline verifikasi berkas pembayaran: Bendahara Pengeluaran $\rightarrow$ Penguji SPM (PPSPM) $\rightarrow$ Pejabat Pembuat Komitmen (PPK) $\rightarrow$ Pencairan.
   - Mode delegasi approval pimpinan (`st_approval_mode`: `delegasi_ppspm`, `kepala_only`, `ppspm_only`, `auto_approve`) dan kebijakan khusus Non-SPPD (`st_non_sppd_mode`: `auto_approve_notify` dengan notifikasi info Kasubbag TU, `follow_global`, `auto_approve_silent`).
2. **Aturan Bisnis & Kepatuhan Regulasi**:
   - Validasi batas maksimal honor bulanan mitra sesuai Peraturan Menteri Keuangan Standar Biaya Masukan (PMK SBM 2026).
   - Konsolidasi kontrak bulanan untuk kegiatan survei dan kontrak per kegiatan untuk kegiatan sensus.
   - *Single Source of Truth Date Anchoring*: Tanggal akhir penugasan menjadi jangkar tunggal untuk tanggal SPK, BAST, dan bulan honor.
   - Algoritma pencegahan jadwal bertabrakan (*anti-schedule collision*).
3. **Infrastruktur & Notifikasi**:
   - Adapter WhatsApp Notifier dengan normalisasi nomor telepon (`628...`) dan sistem preferensi opt-out per user/event.
   - Defensive bootstrapping: pemeriksaan `class_exists()` untuk package opsional (misal: Sentry) guna mencegah fatal crash loop.
   - Kebijakan rotasi log wajib `daily` dengan pemangkasan otomatis file log >50MB.

### 1.2 Komponen Varian (Konfigurasi Satker — Dikelola via Config & DB)
1. **Identitas Satker**: Kode 4 digit BPS (`SATKER_KODE`), nama instansi, level (`kako`/`provinsi`), nama kabupaten/kota, ibu kota kedudukan, logo, dan kop surat.
2. **Pejabat Definitif & PLH**: NIP Kepala Satker, NIP Kasubbag TU, NIP PPK, dan NIP Bendahara.
3. **Master Wilayah & Rute**: Daftar kecamatan, kelurahan/desa, dan SLS lokal serta preset rute transport lokal.
4. **Data Master**: Database pegawai organik dan mitra statistik lokal.

---

## 2. Struktur Repositori & Peta Kode

```text
├── app/
│   ├── Filament/                # Antarmuka Admin & Dashboard (Filament v5)
│   │   ├── Pages/               # Halaman khusus (Pengaturan, LaporanPerjadinPage, dll)
│   │   └── Resources/           # CRUD Resources (Penugasan, Mitra, Honor, Pembayaran)
│   ├── Http/
│   │   ├── Controllers/Api/     # REST API & AI Skill Endpoints
│   │   └── Middleware/          # Auth, ApiKey, CSRF, SAML Proxy
│   ├── Models/                  # Eloquent Entities & Domain Aggregates
│   │   ├── Penugasan.php        # State machine persetujuan & otorisasi tugas
│   │   ├── NomorSurat.php       # Mesin penomoran surat resmi BPS
│   │   ├── PresetRutePerjadin.php # Rute perjalanan dinas & estimasi waktu
│   │   ├── Sipancong/           # Model entitas verifikasi berkas pembayaran
│   │   └── User.php             # Akun, role, dan relasi pegawai
│   ├── Services/                # Domain & Application Logic
│   │   ├── SuratTugas/          # Layanan validasi, penerbitan, & notifikasi tugas
│   │   ├── AIService.php        # Integrasi LPD AI penyusunan laporan dinas
│   │   ├── HonorService.php     # Perhitungan & pembatasan honor SBM
│   │   └── WhatsappNotifier.php # Gateway notifikasi WA multi-provider
│   └── Supports/Constants.php   # Konstanta global status, jenis tugas, & peran
├── config/                      # Konfigurasi aplikasi & satker
├── database/                    # Migrasi, factories, dan seeders
├── docker/                      # Caddyfile dan konfigurasi PHP OPcache
├── docs/                        # Dokumentasi resmi
│   ├── ROADMAP.md               # Roadmap multi-fase Dokter-V
│   ├── roadmap/                 # Rencana kerja bernomor per paket folder (phase-01/, phase-02/, dst)
│   └── architecture/            # Dokumentasi arsitektur enterprise arc42 (01_ s.d. 12_)
├── routes/                      # Web, API, dan Console scheduled tasks
└── tests/                       # Unit dan Feature test suite
```

---

## 3. Standar Rekayasa Perangkat Lunak (Engineering Standards)

1. **Prinsip SOLID & Single Responsibility (SRP)**:
   - Pisahkan logika kalkulasi/bisnis dari controller dan Livewire component ke dalam Service classes (`app/Services/`).
   - Tidak meletakkan query SQL mentah atau HTTP client eksternal langsung di dalam komponen UI.
2. **Fail-Fast & Zero Silent Fallbacks**:
   - Jika ada method atau kontrak yang belum terimplementasi, lemparkan typed exception eksplisit, jangan mengembalikan nilai *dummy/silent fake*.
3. **Stateless & Hermetic Testing**:
   - Seluruh pengujian otomatis di `tests/` wajib dapat dieksekusi secara terisolasi tanpa bergantung pada urutan atau modifikasi data persisten produksi.
   - Pengujian penugasan dan surat tugas menggunakan transaksi in-memory SQLite (`:memory:`).
4. **Pencegahan Pembengkakan Storage (Storage Safety)**:
   - Konfigurasi log default menggunakan `LOG_STACK=daily` dengan `LOG_DAILY_DAYS=14`.
   - File log berukuran >50MB wajib dipangkas (*truncate*) otomatis melalui `docker-entrypoint.sh` saat container start dan melalui schedule harian di [routes/console.php](routes/console.php).
5. **Optimalisasi Build Docker & Invarian Deployment Coolify (Pencegahan Timeout 26 Menit)**:
   - **Sinkronisasi Cache Mount Composer**: `COMPOSER_CACHE_DIR=/tmp/cache` wajib dideklarasikan secara eksplisit di Stage 1 (`composer-builder`) agar sejalan dengan `--mount=type=cache,target=/tmp/cache`. Tanpa ini, Composer default ke `/root/.cache/composer` dan mengabaikan BuildKit cache (0% cache hit).
   - **Penangguhan Autoloader di Stage 1 (`--no-autoloader`)**: Stage 1 murni mengunduh dan mengekstrak pustaka. Autoloader tidak boleh di-generate di Stage 1 karena `pre-autoload-dump` terlewati saat `--no-scripts` aktif, memicu pemindaian lambat ke 36.282 berkas `google/apiclient-services`. Autoloader tunggal teroptimasi di-generate pada Stage 5 (`prod`).
   - **Pemangkasan 328 Service Google API**: Jalankan pemangkasan berkas di Stage 1 via `find vendor/google/apiclient-services/src -mindepth 1 -maxdepth 1 ! -name 'Sheets*' ! -name 'Drive*' -exec rm -rf {} +` agar vendor tetap ramping dan `composer dump-autoload` di Stage 5 tuntas dalam ~3 detik.
   - **Ketahanan Jaringan & Exit Code 100 (`TransportException`)**: Gunakan fallback retry `|| (sleep 5 && composer install ...)` untuk mengatasi fluktuasi koneksi WAN saat mengunduh package dari GitHub/Packagist.
   - **Pinning Statis `install-php-extensions`**: Wajib menggunakan rilis bertag statis (misal: `releases/download/2.12.6/install-php-extensions`), dilarang menggunakan URL `/releases/latest/` untuk mencegah pembatalan cache layer Docker saat Coolify melakukan build dengan `--pull`.

---

## 4. Gerbang Kualitas & Perintah Verifikasi (Verification Gauntlet)

Setiap pengembang atau agen CI wajib memverifikasi kode dengan perintah-perintah berikut sebelum melakukan commit/push:

### Menjalankan Seluruh Unit & Feature Test:
```bash
php artisan test
```

### Menjalankan Pengujian Spesifik Surat Tugas & Delegasi Approval:
```bash
php artisan test --filter=SuratTugasApprovalDelegationTest
```

### Menjalankan Pengujian API Feature Penugasan:
```bash
php artisan test tests/Feature/Api/PenugasanApiTest.php
```

### Menjalankan Pemeriksaan Statis Tipe Data (PHPStan Level 5):
```bash
./vendor/bin/phpstan analyse --memory-limit=2G
```

### Memeriksa Kerapian Kode (Laravel Pint):
```bash
./vendor/bin/pint --test
```

### Menjalankan Lingkungan Lokal via Docker Compose:
```bash
docker compose -f docker-compose.dev.yml up -d
```

---

## 5. Rujukan Dokumen Penting
- [Roadmap Pengembangan Dokter-V](docs/ROADMAP.md)
- [Katalog Rencana Pelaksanaan & Standar Roadmap](docs/roadmap/README.md)
- [Rencana Pelaksanaan Fase 2: Generalisasi Framework Multi-Satker](docs/roadmap/phase-02/README.md)
- [Arsitektur Sistem arc42 (01 Pengantar s.d. 12 Glosarium)](docs/architecture/01_introduction_and_goals.md)
- [Aturan Bisnis & Kontrak Keuangan](docs/BUSINESS_RULES.md)
- [Panduan Produksi & Deployment](PRODUCTION_GUIDE.md)
