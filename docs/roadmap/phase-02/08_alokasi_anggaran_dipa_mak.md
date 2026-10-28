# 08. Manajemen Alokasi Anggaran DIPA & Akun MAK Satker

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-08`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P2 (Menengah)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](README.md)

---

## 1. Sasaran Modul
Menyediakan modul pengelolaan kode akun anggaran Mata Anggaran Keluaran (MAK) dan nomor DIPA yang fleksibel per satuan kerja, serta memvalidasi ketersediaan alokasi dana sebelum surat tugas dan SPPD diterbitkan.

---

## 2. Masalah yang Diselesaikan
Setiap satker BPS memiliki nomor DIPA, kode Program, Kegiatan, KRO, RO, dan Komponen MAK yang berbeda-beda. Saat ini pemilihan MAK pada form penugasan dan pembayaran masih berupa input teks bebas atau mengandalkan data yang terikat pada DIPA BPS Mempawah, sehingga rentan salah ketik dan sulit direkonsiliasi dengan laporan keuangan SAS/SAKTI.

---

## 3. Spesifikasi Teknis

### 3.1 Model `MakAnggaran` & Skema Database
Membuat model `App\Models\MakAnggaran` dengan atribut:
- `kode_mak`: String lengkap MAK (misal: `054.01.GG.2891.QDB.001.051.A.524111`)
- `program`: Kode program BPS
- `kegiatan`: Kode kegiatan statistik (misal: `2891`)
- `kro`: Kode Klasifikasi Rincian Output
- `ro`: Kode Rincian Output
- `komponen`: Kode komponen dan sub-komponen
- `akun`: Akun belanja (misal: `524111` Belanja Perjalanan Dinas Biasa, `521213` Honor Output Kegiatan)
- `nama_anggaran`: Uraian nama kegiatan anggaran
- `tahun_anggaran`: Tahun anggaran berjalan
- `pagu_anggaran`: Total alokasi dana
- `realisasi_anggaran`: Total dana yang telah terserap

### 3.2 Validasi Anggaran saat Pengajuan Surat Tugas
Sebelum surat tugas disetujui, sistem memeriksa apakah saldo sisa anggaran pada MAK yang dipilih mencukupi untuk membiayai perjalanan dinas / transport lokal yang diajukan.

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Satker dapat mengimpor struktur MAK DIPA kantor mereka sendiri melalui panel Admin atau CSV.
2. Dropdown pemilihan pembebanan anggaran pada pembuatan surat tugas dan pengajuan pembayaran menampilkan daftar MAK resmi satker.
3. Rekap penyerapan anggaran per kegiatan dapat dipantau secara real-time.
