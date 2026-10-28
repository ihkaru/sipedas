# 01. Fase 1: Fondasi Inti, Kepatuhan Regulasi & Stabilitas Operasional

> [!INFO] **Metadata Dokumen**
> - **ID Rencana**: `PLN-PHASE-01`
> - **Fase**: Fase 1 — *Core Foundation, Business Rule Compliance & Operational Hardening*
> - **Status**: `SELESAI (COMPLETED)`
> - **Basis Implementasi**: BPS Kabupaten Mempawah (Satker 6104)
> - **Dokumen Induk**: [Roadmap Keseluruhan Dokter-V](../../ROADMAP.md) | [Katalog Roadmap](../README.md)

---

## 1. Latar Belakang & Ruang Lingkup

Fase 1 meletakkan fondasi arsitektur dan tata kelola inti untuk aplikasi **Dokter-V**, dengan fokus utama menyelesaikan masalah operasional persuratan dinas, jadwal perjalanan dinas yang saling tumpang tindih (*schedule collision*), kepatuhan batas honor mitra statistik sesuai standar biaya masukan nasional, dan verifikasi berkas pembayaran keuangan secara akuntabel.

---

## 2. Pencapaian & Deliverable Utama

### 2.1 Mesin Surat Tugas & SPPD Otomatis
- **State Machine Surat Tugas**: Alur siklus hidup penugasan (`DRAFT` $\rightarrow$ `DIKIRIM` $\rightarrow$ `DISETUJUI` / `PERLU_REVISI` / `DITOLAK` $\rightarrow$ `DICAIRKAN` / `DIBATALKAN`).
- **Deteksi Bentrok Jadwal (Anti-Collision Engine)**: Validasi otomatis mencegah pegawai ditugaskan pada rentang tanggal atau lokasi yang saling tumpang tindih.
- **Penerbitan Format Baku BPS**: Otomasi pembuatan berkas PDF Surat Tugas, SPPD 1.2, SPPD 1.3, dan lembar visum sesuai format resmi Perka BPS.

### 2.2 Mesin Validasi Honor Mitra (PMK SBM 2026)
- **Batas Honor Bulanan**: Pengecekan otomatis batas akumulasi honor per mitra dalam satu bulan kalender:
  - Survei: maksimal Rp 3.353.000 / bulan.
  - Sensus: maksimal Rp 4.694.000 / bulan.
- **Konsolidasi Kontrak & BAST**:
  - Survei: satu SPK dan BAST konsolidasi per mitra per bulan.
  - Sensus: SPK dan BAST per kegiatan/tahapan sensus.
- **Single Source of Truth Date Anchoring**: Tanggal akhir penugasan mengunci tanggal SPK, BAST, dan bulan honor secara konsisten.

### 2.3 Pipeline Verifikasi Berkas Pembayaran Keuangan
- Alur verifikasi berjenjang 3 lapis sebelum pencairan honor:
  1. **Bendahara Pengeluaran**: Verifikasi kelengkapan berkas fisik & administrasi kwitansi.
  2. **Penguji SPM (PPSPM)**: Verifikasi kesesuaian anggaran, pagu DIPA, dan SPK/BAST.
  3. **Pejabat Pembuat Komitmen (PPK)**: Otorisasi akhir persetujuan pencairan dana.

### 2.4 Notifikasi Otomatis WhatsApp Multi-Provider
- Gateway notifikasi WhatsApp real-time untuk pengajuan surat tugas, permohonan revisi, notifikasi penolakan, dan persetujuan pimpinan.
- Integrasi *quick approval link* (URL bertanda tangan digital) untuk memudahkan pimpinan menyetujui surat tugas dalam satu klik.
- Fitur *opt-out preference* per pegawai/nomor untuk menghormati privasi.

### 2.5 Mode Delegasi Approval Fleksibel
- Pengaturan delegasi wewenang pimpinan (`st_approval_mode`):
  - `delegasi_ppspm`: Persetujuan dapat dilakukan oleh Kepala Satker atau Kasubbag TU/PPSPM.
  - `kepala_only`: Persetujuan mutlak Kepala Satker.
  - `ppspm_only`: Persetujuan didelegasikan penuh ke PPSPM.
  - `auto_approve`: Otomasi persetujuan instan.

### 2.6 Hardening Stabilitas & Keamanan Runtime
- Konfigurasi log rotasi `daily` dengan retensi 14 hari (`LOG_DAILY_DAYS=14`).
- Pemangkasan otomatis berkas log >50MB melalui daemon kontainer dan scheduled task Laravel guna mencegah kehabisan kapasitas storage.
- Defensive bootstrapping: pencegahan fatal crash loop jika library opsional (Sentry/OpenTelemetry) tidak terpasang.
- Dokumentasi enterprise modular berbasis standar internasional arc42 ([`docs/architecture/`](../../architecture/01_introduction_and_goals.md)).

---

## 3. Matriks Hasil Pengujian & Quality Gate

| Kategori Pengujian | Cakupan | Hasil |
|:---|:---|:---:|
| **Unit & Feature Test** | State machine, honor limits, collision detection, approval delegation | 170+ Tests Passed |
| **Pemeriksaan Tipe Statis** | Analisis PHPStan Level 5 | 0 Errors |
| **Code Style & Format** | Laravel Pint Standards | 100% Compliant |
| **Dokumentasi Provenance** | arc42 Architectural Drift Audit | 100% Up to Date |
