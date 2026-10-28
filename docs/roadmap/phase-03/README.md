# 03. Fase 3: Interoperabilitas Ekosistem BPS & Sinkronisasi Data Otomatis

> [!INFO] **Metadata Dokumen**
> - **ID Rencana**: `PLN-PHASE-03`
> - **Fase**: Fase 3 — *BPS Ecosystem Interoperability & Automated Data Synchronization*
> - **Status**: `DIRENCANAKAN (PLANNED)`
> - **Target Implementasi**: Ekosistem Aplikasi Nasional BPS (FASIH-SM, SBR, Wilkerstat, SIMLHP)
> - **Dokumen Induk**: [Roadmap Keseluruhan Dokter-V](../../ROADMAP.md) | [Katalog Roadmap](../README.md)

---

## 1. Latar Belakang & Visi Strategis

Setelah kerangka kerja Dokter-V digeneralisasi untuk multi-satker pada Fase 2, langkah strategis berikutnya pada Fase 3 adalah **menghubungkan Dokter-V secara langsung ke dalam ekosistem aplikasi nasional Badan Pusat Statistik (BPS)**. Tujuannya adalah mengeliminasi entri data ganda (*double entry*), mempercepat rekonsiliasi beban kerja mitra, dan memverifikasi bukti pelaksanaan lapangan secara otomatis.

---

## 2. Modul Interoperabilitas yang Direncanakan

### 2.1 Integrasi Portal CAPI FASIH-SM
- **Penarikan Progres Lapangan Otomatis**: Menghubungkan akun survei ke portal CAPI FASIH-SM untuk memantau progres pencacahan PPL/PML per SLS secara langsung dari dasbor Dokter-V.
- **Validasi Bukti Lapangan (S3 Object Storage)**: Mengintegrasikan verifikasi foto geotagging responden (foto rumah tampak depan & ruang tamu) dari bucket S3 FASIH BPS untuk memastikan keabsahan pencacahan sebelum BAST diterbitkan.
- **Otomasi Nilai Beban Kerja**: Menghitung capaian riil dokumen survei mitra secara otomatis sebagai dasar perhitungan honor.

### 2.2 Sinkronisasi SBR (Statistical Business Register)
- **Validasi Direktori Usaha**: Menghubungkan database penugasan listing/survei ekonomi dengan registrasi direktori usaha terpusat BPS.
- **Pembaruan Status Keberadaan**: Sinkronisasi status aktif/tutup/pindah perusahaan hasil kunjungan lapangan mitra ke server pusat.

### 2.3 Konektor Master Wilayah Wilkerstat / MFD Online
- **Pembaruan Kode Wilayah Otomatis**: Mengintegrasikan API Master Wilayah Wilkerstat BPS untuk memperbarui pemetaan kode SLS, kelurahan/desa, dan kecamatan tanpa perlu impor manual berkas CSV.
- **Peta Digital & Estimasi Jarak Geospasial**: Pemanfaatan koordinat polygon SLS untuk kalkulasi otomatis jarak tempuh riil perjalanan dinas.

### 2.4 Integrasi Pelaporan Pengawasan SIMLHP
- **Koneksi Laporan Hasil Pengawasan**: Menghubungkan modul Laporan Perjalanan Dinas (LPD) AI Dokter-V ke sistem pengawasan internal BPS (SIMLHP).
- **Audit Trail Terpadu**: Memberikan akses terverifikasi kepada auditor/inspektorat untuk meninjau rekonsiliasi SPPD, honor, dan output survei secara transparan.

---

## 3. Matriks Kriteria Keberhasilan (Success Criteria)

| Modul | Indikator Keberhasilan | Target |
|:---|:---|:---:|
| **FASIH-SM** | Sinkronisasi beban kerja mitra otomatis tanpa unggah manual | Waktu rekonsiliasi < 1 menit |
| **Wilkerstat** | 0 inkonsistensi kode wilayah antara Dokter-V dan Pusat | 100% Valid |
| **SBR** | Validasi direktori usaha real-time saat pembuatan surat tugas | Respon API < 500ms |
| **Keamanan Data** | Enkripsi kredensial API eksternal via Infisical/Vault | Standar ISO 27001 BPS |
