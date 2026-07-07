# Business Rules — Sistem Kontrak & Dokumen Mitra

> **Status:** Aktif | **Terakhir diperbarui:** 2026-07-07
> Dokumen ini adalah **referensi kanonik** untuk semua aturan bisnis terkait kontrak SPK, BAST, PPK, dan perhitungan tanggal di Sikendis.
> Setiap perubahan logika di kode **harus** merujuk dan memperbarui dokumen ini.

---

## Daftar Isi

1. [Sumber Kebenaran Tunggal](#1-sumber-kebenaran-tunggal)
2. [Aturan Tanggal Kontrak SPK](#2-aturan-tanggal-kontrak-spk)
3. [Aturan Tanggal BAST](#3-aturan-tanggal-bast)
4. [Aturan PPK](#4-aturan-ppk)
5. [Aturan Nomor Surat](#5-aturan-nomor-surat)
6. [Aturan Kegiatan Lintas Bulan](#6-aturan-kegiatan-lintas-bulan)
7. [Propagasi Perubahan Tanggal](#7-propagasi-perubahan-tanggal)
8. [Referensi Implementasi](#8-referensi-implementasi)

---

## 1. Sumber Kebenaran Tunggal

### `honors.tanggal_akhir_kegiatan`

**Satu-satunya** field yang menentukan bulan kontrak, PPK, dan semua tanggal dokumen turunan.

```
honors.tanggal_akhir_kegiatan
    │
    ├── Menentukan BULAN kontrak SPK (startOfMonth s/d endOfMonth)
    ├── Menentukan PPK yang menandatangani (via RiwayatPpk)
    ├── Menentukan tanggal nomor SPK (hari kerja sebelum kontrak mulai)
    ├── Menentukan tanggal nomor BAST (<= tanggal_akhir_kegiatan)
    └── Menentukan batas pembayaran (+ 20 hari)
```

> **PENTING:** `kegiatan_manmits.tgl_mulai_pelaksanaan` dan `tgl_akhir_pelaksanaan`
> **TIDAK** digunakan untuk menentukan tanggal kontrak atau BAST.
> Field tersebut hanya untuk referensi jadwal kegiatan, bukan basis dokumen administratif.

---

## 2. Aturan Tanggal Kontrak SPK

### 2.1 Periode Kontrak

Kontrak selalu **bulanan penuh** — dari tanggal 1 hingga hari terakhir bulan,
mengacu pada bulan dari `honor.tanggal_akhir_kegiatan`.

| Field | Nilai | Keterangan |
|---|---|---|
| `tanggal_mulai_perjanjian` | `startOfMonth(tanggal_akhir_kegiatan)` | Selalu tanggal 1 bulan tersebut |
| `tanggal_akhir_perjanjian` | `endOfMonth(tanggal_akhir_kegiatan)` | Selalu hari terakhir bulan tersebut |

**Contoh:** `tanggal_akhir_kegiatan = 29 Mei 2026`
Kontrak: **1 Mei 2026 s/d 31 Mei 2026**

### 2.2 Tanggal Penanda Tanganan SPK oleh Petugas

```
tanggal_penanda_tanganan_spk_oleh_petugas
    = getNextWorkDay(startOfMonth(tanggal_akhir_kegiatan) - 1 hari, mundur)
```

Artinya: **hari kerja terakhir sebelum kontrak mulai** (hari kerja terakhir bulan sebelumnya).

**Contoh:** Kontrak mulai 1 Mei 2026 -> TTD SPK = **30 April 2026** (Kamis, hari kerja)

### 2.3 Satu Honor, Satu Kontrak Per Bulan

Satu mitra hanya boleh memiliki **satu nomor SPK per bulan**.
Jika mitra memiliki dua kegiatan di bulan yang sama, mereka berbagi nomor SPK yang sama.

---

## 3. Aturan Tanggal BAST

### 3.1 Tanggal Nomor Surat BAST

```
tanggal_nomor (BAST) = getNextWorkDay(tanggal_akhir_kegiatan, mundur)
```

**Aturan:**
- `tanggal_nomor` BAST **tidak boleh melebihi** `tanggal_akhir_kegiatan`
- Jika `tanggal_akhir_kegiatan` adalah hari kerja -> gunakan tanggal itu sendiri
- Jika `tanggal_akhir_kegiatan` adalah hari libur/weekend -> mundur ke hari kerja sebelumnya

**Contoh:**
- `tanggal_akhir_kegiatan = 31 Mei 2026` (Minggu) -> BAST = **29 Mei 2026** (Jumat)
- `tanggal_akhir_kegiatan = 29 Mei 2026` (Jumat) -> BAST = **29 Mei 2026** (Jumat)

### 3.2 Tanggal Penandatanganan BAST

- BAST **harus ditandatangani maksimal pada** `tanggal_akhir_kegiatan`
- Idealnya 1 hari kerja sebelum `tanggal_akhir_kegiatan`
- `tanggal_nomor` surat boleh sama dengan tanggal penandatanganan

### 3.3 Filter BAST per Bulan

BAST difilter berdasarkan **bulan dari `honor.tanggal_akhir_kegiatan`**, bukan dari parameter lain.
Untuk kegiatan lintas bulan, UI menampilkan satu tombol BAST per bulan.

---

## 4. Aturan PPK (Pejabat Pembuat Komitmen)

### 4.1 Penentuan PPK

PPK ditentukan secara **dinamis** berdasarkan tanggal dokumen, bukan hardcoded.

```php
Pegawai::getPpkByDate($tanggal) -> RiwayatPpk::getPpkPadaTanggal($tanggal)
```

### 4.2 Sumber Tanggal untuk PPK

| Dokumen | Tanggal yang digunakan untuk PPK |
|---|---|
| SPK | `tanggal_nomor` surat SPK (hari kerja terakhir sebelum kontrak mulai) |
| BAST | `tanggal_nomor` surat BAST aktual dari database, **bukan** dari URL parameter |

> **PENTING:** PPK di BAST **harus** diambil dari `alokasi_honors.bast.tanggal_nomor`,
> bukan dari parameter `tahun-bulan` di URL. Parameter URL hanya untuk filtering, bukan untuk PPK.

### 4.3 Data Historis PPK

Riwayat PPK disimpan di tabel `riwayat_ppks` dan dapat dikelola melalui UI admin
di menu **Pengaturan -> Riwayat PPK**.

| NIP | Nama | Mulai | Selesai |
|---|---|---|---|
| 199212312015031003 | Arief Yuandi SST | 2024-01-01 | 2026-03-31 |
| 198706082009121004 | Budiman Aller Silaban S.Tr.Stat. | 2026-04-01 | (aktif) |

> Jika ada pergantian PPK, **tambahkan baris baru** di tabel `riwayat_ppks` melalui UI.
> Jangan mengubah kode.

---

## 5. Aturan Nomor Surat

### 5.1 Format Nomor

- **SPK:** `{nomor}/6104/PL.300/{bulan_romawi}/{tahun}` — contoh: `021/6104/PL.300/04/2026`
- **BAST:** `{nomor}/6104/BAST/{bulan_romawi}/{tahun}` — contoh: `086/6104/BAST/05/2026`

### 5.2 Konsistensi Tanggal Nomor

`nomor_surats.tanggal_nomor` harus selalu konsisten dengan:
- SPK -> hari kerja terakhir sebelum bulan kontrak mulai
- BAST -> hari kerja pada/sebelum `tanggal_akhir_kegiatan`

Jika `honor.tanggal_akhir_kegiatan` berubah, `HonorObserver` akan otomatis memperbarui
`tanggal_nomor` di semua surat terkait dalam satu database transaction.

---

## 6. Aturan Kegiatan Lintas Bulan

Satu `kegiatan_manmit` dapat memiliki beberapa `honor` dengan `tanggal_akhir_kegiatan`
di bulan yang berbeda. Setiap honor diperlakukan **independen** berdasarkan bulannya.

**Contoh:** SUSENAS26-SEM1-maret memiliki honor di Januari, Februari, dan Maret:
- Honor Januari -> tombol "BAST Januari 2026" -> PPK sesuai Januari
- Honor Februari -> tombol "BAST Februari 2026" -> PPK sesuai Februari
- Honor Maret -> tombol "BAST Maret 2026" -> PPK sesuai Maret

UI menampilkan tombol per-bulan dari `DISTINCT YEAR/MONTH(tanggal_akhir_kegiatan)`
pada honor kegiatan tersebut — konsisten antara tombol Cetak Kontrak dan Cetak BAST.

---

## 7. Propagasi Perubahan Tanggal

Jika `honor.tanggal_akhir_kegiatan` diubah setelah alokasi dibuat, sistem otomatis
memperbarui semua data turunan via `HonorObserver -> HonorTanggalService::propagate()`:

```
honor.tanggal_akhir_kegiatan berubah
    │
    ├── alokasi_honors.tanggal_mulai_perjanjian     <- diperbarui
    ├── alokasi_honors.tanggal_akhir_perjanjian     <- diperbarui
    ├── alokasi_honors.tanggal_penanda_tanganan_spk <- diperbarui
    ├── nomor_surats.tanggal_nomor (SPK)            <- diperbarui
    └── nomor_surats.tanggal_nomor (BAST)           <- diperbarui
```

UI menampilkan **pesan error merah (strict validation)** dan menyediakan **tombol pintas** ke halaman edit Kegiatan Manmit utama jika tanggal akhir kegiatan dipilih di luar rentang pelaksanaan kegiatan utama. Jika sudah ada alokasi honor aktif, UI juga menampilkan **peringatan kuning** mengenai dampak alokasi.

### Bulk Fix untuk Data Historis

Untuk memperbaiki data historis yang tidak konsisten:

```bash
php artisan honor:fix-tanggal --dry-run   # Preview — tidak mengubah data
php artisan honor:fix-tanggal             # Terapkan perbaikan
```

---

## 8. Referensi Implementasi

| Aturan | File | Method/Class |
|---|---|---|
| Propagasi tanggal | `app/Services/HonorTanggalService.php` | `HonorTanggalService::propagate()` |
| Observer perubahan | `app/Observers/HonorObserver.php` | `HonorObserver::updated()` |
| PPK dinamis | `app/Models/Pegawai.php` | `Pegawai::getPpkByDate()` |
| PPK historis | `app/Models/RiwayatPpk.php` | `RiwayatPpk::getPpkPadaTanggal()` |
| Cetak SPK | `app/Http/Controllers/PdfController.php` | `cetakKontrak()` |
| Cetak BAST | `app/Http/Controllers/PdfController.php` | `cetakBast()` |
| Buat alokasi | `app/Models/AlokasiHonor.php` | `AlokasiHonor::createWithRelations()` |
| Tombol cetak UI | `app/Filament/Resources/KegiatanManmitResource/RelationManagers/AlokasiHonorRelationManager.php` | `getDynamicPrintActions()` |
| Bulk fix data | `app/Console/Commands/FixHonorTanggalPerjanjian.php` | `honor:fix-tanggal` |

---

*Dibuat berdasarkan analisis kode, diskusi dengan PJK, dan validasi data lapangan — Juli 2026.*
