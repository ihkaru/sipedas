# 03. Desentralisasi Master Wilayah & Rute Perjalanan Dinas

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-03`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P1 (Penting)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](README.md)

---

## 1. Sasaran Modul
Menghapus dependensi hardcoded 9 kecamatan Mempawah dan titik awal perjalanan ("Mempawah") pada model [`PresetRutePerjadin.php`](file:///home/ihza/Projects/sipedas/app/Models/PresetRutePerjadin.php), service AI [`AIService.php`](file:///home/ihza/Projects/sipedas/app/Services/AIService.php), dan master SLS [`MasterSls.php`](file:///home/ihza/Projects/sipedas/app/Models/MasterSls.php), sehingga dapat menghasilkan rute dan deskripsi dinas yang akurat untuk kabupaten/kota manapun di Indonesia.

---

## 2. Masalah yang Diselesaikan
1. Di [`PresetRutePerjadin.php`](file:///home/ihza/Projects/sipedas/app/Models/PresetRutePerjadin.php), nama kecamatan di-*fallback* ke `'MEMPAWAH HILIR'`, teks narasi rute terkunci ke *"Perjalanan dari Mempawah menuju lokasi penugasan..."*, dan pemetaan desa hanya mencakup desa-desa di Mempawah.
2. Di [`AIService.php`](file:///home/ihza/Projects/sipedas/app/Services/AIService.php), prompt AI mengasumsikan pegawai berangkat dari Mempawah menuju kecamatan Mempawah Timur/Hilir.
3. Di [`MasterSls.php`](file:///home/ihza/Projects/sipedas/app/Models/MasterSls.php), kode kabkot dikunci pada `["6104"]`.

---

## 3. Spesifikasi Teknis

### 3.1 Dinamisasi Titik Kedudukan & Narasi
- Titik awal keberangkatan dan kepulangan diambil dari:
  ```php
  $kedudukan = config('satker.ibukota', 'Mempawah');
  ```
- Teks narasi di `PresetRutePerjadin.php`:
  ```php
  ['step' => 1, 'waktu' => $startCoord, 'kategori' => 'berangkat_koordinasi', 'uraian' => "Perjalanan dari {$kedudukan} menuju lokasi penugasan dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD."]
  ['step' => 6, 'waktu' => "15.15 - {$pulangEnd}", 'kategori' => 'kembali', 'uraian' => "Perjalanan kembali ke {$kedudukan}."]
  ```

### 3.2 Dinamisasi Prompt AI LPD ([`AIService.php`](file:///home/ihza/Projects/sipedas/app/Services/AIService.php))
- Mengganti teks statis Mempawah pada prompt AI:
  ```php
  $ibukota = config('satker.ibukota', 'Mempawah');
  $kabkot = config('satker.kabkot', 'Kabupaten Mempawah');
  
  // Prompt builder menggunakan $ibukota dan $kabkot secara dinamis
  ```

### 3.3 Struktur Universal Tabel `preset_rute_perjadins`
Tabel `preset_rute_perjadins` tetap menggunakan skema yang ada, namun diisi berdasarkan master wilayah satker lokal:
- `nama_kecamatan`: Nama kecamatan tujuan (misal: "SOREANG", "CIMAUNG", dll).
- `estimasi_waktu`: Waktu tempuh dari kantor ke kecamatan.
- `biaya_transport_lokal`: Besaran biaya transport lokal sesuai Standar Biaya Umum (SBU) Pemda / BPS setempat.
- `step_rute_json`: Urutan kegiatan lapangan yang otomatis menggunakan nama ibu kota satker.

### 3.4 Desentralisasi `MasterSls.php`
Mengganti `kabkot_ids = ["6104"]` menjadi:
```php
$res["kabkot_ids"] = [config('satker.kode', '6104')];
```

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Narasi laporan perjalanan dinas yang dihasilkan oleh LPD AI otomatis menyebutkan kota kedudukan satker pengguna (misal: *"Perjalanan dari Soreang menuju Kecamatan Cimaung..."*).
2. Dropdown tujuan perjalanan dinas dan estimasi rute menampilkan kecamatan lokal satker.
3. Master SLS hanya memfilter blok sensus / SLS milik satker yang aktif.
