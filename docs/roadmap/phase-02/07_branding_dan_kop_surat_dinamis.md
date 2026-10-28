# 07. Branding, Logo & Kop Naskah Dinas Satker Dinamis

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-07`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P2 (Menengah)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](../02_satker_framework_generalization.md)

---

## 1. Sasaran Modul
Menyediakan mekanisme penggantian logo instansi, alamat kantor, dan kop naskah dinas resmi pada output cetak dokumen PDF (Surat Tugas, SPPD, SPK, BAST, dan Lembar Visum) secara dinamis tanpa perlu menyunting berkas Blade template yang panjang dan kompleks.

---

## 2. Masalah yang Diselesaikan
Saat ini berkas template Blade naskah dinas cetak (seperti [`resources/views/surat_tugas/bersama/pdf.blade.php`](file:///home/ihza/Projects/sipedas/resources/views/surat_tugas/bersama/pdf.blade.php) yang memiliki lebih dari 2.700 baris) masih memuat beberapa string dan styling spesifik BPS Kabupaten Mempawah. Pengembang dari satker lain kesulitan menemukan bagian kop surat yang harus diubah dan berisiko merusak layout PDF dompdf yang sensitif.

---

## 3. Spesifikasi Teknis

### 3.1 Komponen Blade Kop Surat Terisolasi (`resources/views/components/kop-surat.blade.php`)
Mengekstrak blok header kop surat menjadi komponen Blade mandiri yang menerima parameter konfigurasi:
- **Nama Instansi Utama**: `BADAN PUSAT STATISTIK`
- **Nama Satker Tingkat II**: `KABUPATEN [NAMA_KABKOT]` (misal: `KABUPATEN BANDUNG`)
- **Alamat Kantor**: Diambil dari `config('satker.alamat')` dan `config('satker.kodepos')`
- **Kontak & Web**: Diambil dari `config('satker.telepon')`, `config('satker.email')`, dan `config('satker.website')`
- **Logo BPS**: Logo resmi BPS tersimpan di `public/images/logo-bps.png`

### 3.2 Kustomisasi Logo & Stempel via Panel Pengaturan Filament
Menyediakan field upload gambar di halaman Pengaturan:
- `SATKER_LOGO`: Logo resmi satker jika menggunakan logo kustom.
- `SATKER_KOP_IMAGE`: Opsi mengunggah gambar kop surat siap pakai (untuk satker yang menggunakan kop bergambar utuh).

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Seluruh PDF yang di-generate (Surat Tugas, SPPD, BAST, SPK) secara otomatis menampilkan kop surat dengan identitas satker yang aktif.
2. Template PDF bersama ([`pdf.blade.php`](file:///home/ihza/Projects/sipedas/resources/views/surat_tugas/bersama/pdf.blade.php)) bersih dari nama satker hardcoded.
3. Cetak PDF tetap presisi 100% pada ukuran kertas standar F4/A4 tanpa pergeseran margin.
