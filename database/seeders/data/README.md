# Panduan Berkas Starter Pack Satker BPS (Data Master)

Direktori ini memuat template dan berkas data master rujukan untuk menginisialisasi Dokter-V di Satker BPS Kabupaten/Kota baru.

## Daftar Berkas & Struktur Kolom

### 1. Master SLS Wilkerstat (`master_sls.csv`)
- **Format**: CSV (koma sebagai pemisah, baris pertama header)
- **Letak file**: `database/seeders/data/master_sls.csv` (atau rujukan `--file` pada CLI)
- **Template referensi**: `master_sls_sample.csv`
- **Kolom Wajib**:
  1. `desa_kel_id`: Kode 10 digit desa/kelurahan (contoh: `6104050001`)
  2. `kec_id`: Kode 7 digit kecamatan (contoh: `6104050`)
  3. `kabkot_id`: Kode 4 digit kab/kota (contoh: `6104`)
  4. `prov_id`: Kode 2 digit provinsi (contoh: `61`)
  5. `sls_id`: Kode 14 digit SLS (contoh: `61040500010001`)
  6. `provinsi`: Nama provinsi (UPPERCASE)
  7. `kabkot`: Nama kabupaten/kota (UPPERCASE)
  8. `kecamatan`: Nama kecamatan (UPPERCASE)
  9. `desa_kel`: Nama kelurahan/desa (UPPERCASE)
  10. `nama`: Nama SLS / RT / RW / Dusun

### 2. Data Pegawai Organik (`pegawai.csv` atau `pegawai.json`)
- **Format**: CSV atau JSON
- **Letak file**: `database/seeders/data/pegawai.csv` atau `database/seeders/data/pegawai.json`
- **Template referensi**: `pegawai_sample.csv`
- **Kolom Wajib**:
  1. `nip`: NIP 18 digit pegawai BPS (Primary Key)
  2. `nip9`: NIP lama 9 digit / NIP BPS
  3. `nama`: Nama lengkap beserta gelar resmi kedinasan
  4. `jabatan`: Jabatan fungsional / struktural
  5. `pangkat`: Pangkat romawi (`IV`, `III`, `II`)
  6. `golongan`: Golongan ruang (`a`, `b`, `c`, `d`)
  7. `email`: Email kedinasan resmi BPS (`...@bps.go.id`)
  8. `nomor_wa`: Nomor WhatsApp aktif format 628...
  9. `panggilan`: Nama panggilan akrab
  10. `unit_kerja`: Nama unit kerja instansi (contoh: `BPS Kabupaten Mempawah`)

### 3. Data Mitra Statistik (`mitra.xlsx` atau `mitra.csv`)
- **Format**: XLSX (ekspor dari SOBAT / KEPKA BPS) atau CSV
- **Letak file**: `database/seeders/data/mitra.xlsx` atau `database/seeders/data/mitra.csv`
- **Template referensi**: `mitra_sample.csv`
- **Kolom Wajib**:
  - `sobat_id`: ID Mitra dari sistem SOBAT BPS (contoh: `61040001`)
  - `nama_lengkap`: Nama lengkap mitra
  - `posisi`: Posisi mitra (Pendataan / Pengolahan)
  - `status_seleksi`: 1 (Terpilih) / 2 (Tidak Terpilih)
  - `alamat_detail`: Alamat domisili mitra
  - `alamat_provinsi`, `alamat_kab_kota`, `alamat_kecamatan`, `alamat_desa_kel`
  - `email`, `no_hp`, `nik`

---

## Perintah Eksekusi Otomatis

Jalankan perintah wizard untuk konfigurasi identitas satker:
```bash
php artisan satker:init
```

Lalu jalankan import seluruh data master secara berurutan:
```bash
php artisan satker:import-all --create-users
```
Atau jalankan per modul secara terpisah:
```bash
php artisan satker:import-sls
php artisan satker:import-pegawai --create-users
php artisan satker:import-mitra --tahun=2026
```
