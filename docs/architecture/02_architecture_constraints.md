# 2. Batasan Sistem (Architecture Constraints)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `2fce49d` (branch: `main`)
> - **Verified Timestamp**: `2026-10-29T17:40:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini menjelaskan batas-batas teknologi, aturan hukum keuangan, dan infrastruktur server yang menjadi landasan pembuatan sistem Dokter-V.

---

## 2.1 Batasan Teknologi dan Perangkat Lunak

| ID | Batasan | Mengapa Menggunakan Ini? | Dampak Nyata pada Sistem |
|:---|:---|:---|:---|
| **CON-01** | **PHP 8.4 & Laravel 13** | Bahasa pemrograman dan kerangka kerja web standar yang stabil, modern, dan didukung penuh oleh komunitas open-source. | Kode program ditulis dengan tipe data yang jelas dan rapi sehingga kesalahan ketik program langsung terdeteksi sebelum aplikasi dijalankan. |
| **CON-02** | **FrankenPHP / Mode Worker** | Server aplikasi modern yang menjaga aplikasi tetap siap siaga di memori, bukan menyalakan framework dari nol setiap kali halaman web diklik. | Setiap klik tombol dan buka menu terasa instan (<10 milidetik), namun pengembang dilarang menyimpan data sementara di variabel global agar data antarpengguna tidak tertukar. |
| **CON-03** | **Filament v5 Admin Panel** | Pustaka antarmuka web yang sudah menyediakan tabel data, formulir isian, dan tombol aksi yang rapi dan seragam. | Tampilan sistem otomatis responsif (enak dilihat dan mudah dipakai baik di layar monitor komputer maupun di layar HP). |
| **CON-04** | **Database Utama MySQL 8.0** | Database relasional yang menjamin keutuhan data (data tidak hilang saat mati lampu dan perubahan multi-tabel disimpan secara serentak). | Seluruh penyimpanan penting (penomoran surat tugas, status pembayaran honor, dan pembuatan SPK) dipastikan tersimpan utuh atau dibatalkan semua jika terjadi gangguan di tengah jalan. |
| **CON-05** | **Pengujian Otomatis Mandiri di Memori** | Menjalankan tes otomatis menggunakan database sementara di dalam memori (SQLite in-memory dengan pelindung `force="true"`). | Pengembang bisa menguji ratusan skenario sistem dalam hitungan detik tanpa perlu koneksi internet dan tanpa risiko mengotori atau merusak data asli di kantor. |

---

## 2.2 Batasan Aturan Keuangan dan Organisasi

| ID | Batasan | Aturan Acuan | Penerapan Nyata di Sistem |
|:---|:---|:---|:---|
| **CON-06** | **Aturan Batas Honor (SBM 2026)** | Peraturan Menteri Keuangan tentang Standar Biaya Masukan Tahun Anggaran 2026. | Sistem otomatis menolak input honor jika total yang diterima mitra dalam satu bulan kalender melebihi **Rp 3.353.000** (untuk survei) atau **Rp 4.694.000** (untuk sensus). |
| **CON-07** | **Pemisahan Peran Pengelola Keuangan** | Undang-Undang Perbendaharaan Negara. | Pejabat yang membuat komitmen pengeluaran (PPK), yang menguji berkas (PPSPM), dan yang mentransfer uang (Bendahara) harus orang yang berbeda. Satu orang tidak boleh menyetujui tahapannya sendiri. |
| **CON-08** | **Satu Patokan Tanggal Kontrak Mitra** | Standar Pemeriksaan Keuangan BPS & Audit BPK. | Kolom `tanggal_akhir_kegiatan` pada data honor adalah satu-satunya patokan untuk menentukan bulan honor, batas SBM, tanggal kontrak (SPK), dan tanggal berita acara (BAST). |
| **CON-09** | **Format Berkas Resmi SPK & BAST** | Prosedur Operasional Standar (SOP) Pengadaan Jasa BPS. | Dokumen kontrak SPK dan BAST wajib memiliki nomor surat yang sah dan bisa langsung dicetak dalam format PDF resmi instansi pemerintah. |
| **CON-13** | **Pemisahan Invarian vs Varian Multi-Satker** | Standar Generalisasi Arsitektur Dokter-V BPS se-Indonesia. | Logika bisnis inti (state machine, SBM, antarbentrok) adalah invarian universal. Data satker (kode satker, NIP pejabat, master wilayah SLS, mitra) dikelola sebagai varian terisolasi via `config/satker.php` & template CSV. |
| **CON-14** | **Fleksibilitas Autentikasi Satker** | Kebutuhan Onboarding Satker Tanpa SSO Mandiri. | Sistem wajib mendukung mode autentikasi ganda via `AUTH_MODE`: SSO SAML 2.0 (rIDM BPS) untuk satker dengan integrasi SSO, atau akun lokal (`email_password`) untuk satker yang belum memiliki sertifikat SSO. |

---

## 2.3 Batasan Server dan Infrastruktur

| ID | Batasan | Penjelasan |
|:---|:---|:---|
| **CON-10** | **Server ThinkStation & Coolify** | Aplikasi berjalan di server kantor ThinkStation P320 menggunakan pengelola kontainer otomatis Coolify. Sistem berjalan terisolasi di wadahnya sendiri agar tidak mengganggu aplikasi lain. |
| **CON-11** | **Jalur Akses Aman Traefik (HTTPS)** | Semua akses dari luar wajib melalui gerbang aman (HTTPS/SSL otomatis) sehingga kata sandi dan data pribadi pegawai/mitra tidak bisa diintip orang lain di jaringan. |
| **CON-12** | **Kunci Rahasia Tersimpan Aman** | Token WhatsApp, kunci AI Google Gemini, dan sertifikat SSO disimpan terpusat di brankas kunci rahasia (*Infisical*), tidak pernah ditulis terang-terangan di dalam kode program. |
| **CON-15** | **Batas Waktu Deployment Maksimal 10 Menit** | Durasi proses build Docker dan rilis di Coolify dibatasi secara ketat maksimal **10 menit** (target optimal $\le 2$ menit). Pipeline Dockerfile wajib menerapkan layer caching BuildKit (`COMPOSER_CACHE_DIR=/tmp/cache`), penangguhan autoloader (`--no-autoloader`), pemangkasan pustaka Google API di Stage 1, dan versi installer ekstensi statis agar build tidak pernah menggantung atau timeout. |
