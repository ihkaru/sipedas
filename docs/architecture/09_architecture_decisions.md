# 9. Keputusan Penting Pembuatan Sistem (Architecture Decisions)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `e02307b` (branch: `main`)
> - **Verified Timestamp**: `2026-10-29T12:40:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini mencatat keputusan-keputusan teknis penting yang diambil selama merancang sistem Dokter-V, alasan di baliknya, serta konsekuensinya.

---

## ADR-01: Menjaga Aplikasi Tetap Aktif di Memori (FrankenPHP) daripada Menyalakannya dari Nol Tiap Halaman Dibuka

- **Status**: Diterima dan Diterapkan
- **Mengapa Keputusan Ini Diambil?**:
  Sistem Dokter-V sering dibuka bersamaan oleh banyak pegawai untuk mengurus surat tugas dan memeriksa ribuan data honor mitra. Jika menggunakan cara lama di mana server harus membaca dan menyiapkan seluruh program dari nol setiap kali ada yang mengklik menu, halaman web akan terasa lambat (butuh waktu 100 milidetik lebih per klik) dan komputer server cepat kehabisan tenaga CPU.
- **Keputusan yang Diambil**:
  Aplikasi dijalankan di atas server modern **FrankenPHP**. Aplikasi disiapkan sekali di awal dan terus siap siaga di memori server. Begitu ada pegawai yang mengklik menu, sistem langsung merespons dalam waktu kurang dari 10 milidetik.
- **Manfaat Nyata**:
  - Tampilan web terbuka sangat cepat dan nyaman digunakan.
  - Komputer server kantor tidak bekerja berat meskipun banyak yang mengakses bersamaan.
  - Pengunduhan berkas laporan berukuran besar menjadi lancar tanpa terputus di tengah jalan.
- **Risiko yang Muncul / Risks created**: RISK-01

---

## ADR-02: Menetapkan Tanggal Akhir Kegiatan sebagai Satu-Satunya Patokan Resmi

- **Status**: Diterima dan Diterapkan
- **Mengapa Keputusan Ini Diambil?**:
  Sebelumnya, tanggal surat, tanggal kegiatan, tanggal kontrak (SPK), dan tanggal serah terima (BAST) diisi di kolom terpisah yang tidak saling terhubung. Sering kali terjadi kesalahan manusia di mana tanggal kerja masuk bulan September tetapi tanggal bayar tertulis Oktober, sehingga menjadi masalah saat ada audit keuangan.
- **Keputusan yang Diambil**:
  Menjadikan kolom **`tanggal_akhir_kegiatan`** pada data honor sebagai patokan tunggal. Tanggal ini yang otomatis menentukan bulan honor, batas aturan honor bulanan, tanggal kontrak kerja sama, dan tanggal serah terima hasil kerja.
- **Manfaat Nyata**:
  - Semua dokumen (SPK, BAST, dan laporan honor) selalu memiliki tanggal yang serasi dan tertib.
  - Sangat mudah dipertanggungjawabkan saat diperiksa oleh pemeriksa keuangan (auditor BPK atau Itwil).
  - Tampilan tabel monitoring honor mitra selalu menunjukkan keadaan lapangan yang sebenarnya.
- **Risiko yang Muncul / Risks created**: RISK-02

---

## ADR-03: Mengunci Susunan Bab Laporan Hasil AI agar Rapi dan Tidak Mengarang Bebas

- **Status**: Diterima dan Diterapkan
- **Mengapa Keputusan Ini Diambil?**:
  Pegawai membutuhkan bantuan untuk membuat naskah laporan perjalanan dinas yang formal dan rapi. Namun, jika AI dibiarkan menulis teks bebas tanpa batasan, format laporannya sering berantakan, tata letaknya berubah-ubah, atau AI bisa mengarang informasi yang tidak ada di lapangan.
- **Keputusan yang Diambil**:
  Hasil dari AI dikunci dalam kotak-kotak isian baku (bab latar belakang, maksud kegiatan, tempat/waktu, hasil kegiatan, dan simpulan). AI diwajibkan hanya merapikan catatan asli dari pegawai dan dilarang mengubah fakta atau angka yang ditulis pegawai.
- **Manfaat Nyata**:
  - Laporan dinas tersusun rapi sesuai format standar instansi pemerintah (PUEBI/EYD).
  - Waktu pegawai untuk membuat laporan terpangkas dari yang biasanya berjam-jam menjadi beberapa menit saja.
  - Server kantor tidak perlu bekerja berat karena pemrosesan kalimat dikerjakan oleh server AI luar.
- **Risiko yang Muncul / Risks created**: RISK-03

---

## ADR-04: Menguji Program di Memori Terpisah agar Data Kantor Tidak Tercemar

- **Status**: Diterima dan Diterapkan
- **Mengapa Keputusan Ini Diambil?**:
  Saat pengembang sedang memperbaiki sistem atau menambah fitur baru, pengembang perlu menjalankan ratusan tes otomatis. Jika tes ini tanpa sengaja terhubung ke database kantor, ada bahaya data asli terhapus atau tercampur dengan data uji coba.
- **Keputusan yang Diambil**:
  Pengujian otomatis dikunci di dalam konfigurasi tes (`phpunit.xml`) agar **selalu menggunakan ruang memori sementara** (SQLite di memori dengan atribut pengaman `force="true"`).
- **Manfaat Nyata**:
  - Ratusan tes selesai diperiksa hanya dalam hitungan detik.
  - Database kantor yang asli 100% aman dan tidak akan pernah terotak-atik oleh proses uji coba program.
  - Memberikan kepastian bahwa logika aturan honor dan batas SBM bekerja dengan benar sebelum program dirilis ke pegawai.
- **Risiko yang Muncul / Risks created**: RISK-04

---

## ADR-05: Pemisahan Invarian vs Varian untuk Generalisasi Framework Multi-Satker

- **Status**: Diterima dan Diterapkan
- **Mengapa Keputusan Ini Diambil?**:
  Aplikasi awalnya dibangun untuk BPS Kabupaten Mempawah (kode 6104). Ketika satker BPS lain ingin mengadopsi Dokter-V, mereka menemui kesulitan karena nama satker, NIP pejabat, dan data wilayah tercampur dalam kode program.
- **Keputusan yang Diambil**:
  Memisahkan sistem menjadi **Invarian** (logika proses bisnis universal, state machines, kepatuhan batas honor SBM 2026, single date anchoring) dan **Varian** (konfigurasi satker via `config/satker.php`, template data master CSV di `storage/app/templates/`, serta CLI onboarding `php artisan satker:*`). Autentikasi juga mendukung mode ganda (`sso_saml` dan `email_password`).
- **Manfaat Nyata**:
  - Satker baru dapat mengadopsi Dokter-V dalam waktu kurang dari 30 menit tanpa menyentuh kode PHP.
  - Kode program inti tetap 100% bersih, portabel, dan mudah diperbarui via upstream git pull.
- **Risiko yang Muncul / Risks created**: RISK-05

---

## ADR-06: Pengesahan Kilat Surat Tugas via Temporary Signed Route dengan Mitigasi Bot Crawler

- **Status**: Diterima dan Diterapkan
- **Mengapa Keputusan Ini Diambil?**:
  Pimpinan kantor sering berada di lapangan dan membutuhkan cara pengesahan surat tugas yang cepat tanpa harus login ke dashboard web dari laptop. Namun, membuat endpoint persetujuan berbasis URL publik rawan dipicu secara prematur oleh automated link previewer/crawler (misalnya crawler WhatsApp `facebookexternalhit/1.1`) saat pesan WA masuk.
- **Keputusan yang Diambil**:
  Menggunakan mekanisme **Laravel Temporary Signed Route** (`URL::temporarySignedRoute`) dengan tanda tangan digital HMAC-SHA256 berbatas waktu 14 hari yang mengikat `grup_id` dan NIP pengesah. Endpoint dilengkapi inspeksi `User-Agent` untuk mendeteksi bot/crawler dan hanya menyajikan OpenGraph preview tanpa mengubah status penugasan. Persetujuan hanya diproses dalam database transaction ketika dibuka oleh manusia di browser.
- **Manfaat Nyata**:
  - Pimpinan dapat menyetujui surat tugas secara instan (1-klik) dari layar smartphone tanpa perlu login.
  - Aman dari eksekusi tidak sengaja akibat link preview WhatsApp.
  - Stateless: tidak memerlukan tabel database tambahan untuk token sementara.
- **Risiko yang Muncul / Risks created**: RISK-06
