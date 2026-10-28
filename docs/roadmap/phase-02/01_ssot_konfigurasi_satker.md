# 01. Abstraksi SSOT Konfigurasi Satker (`config/satker.php`)

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-01`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P0 (Fondasi Wajib)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](README.md)

---

## 1. Sasaran Modul
Menyediakan satu sumber kebenaran tunggal (*Single Source of Truth* / SSOT) untuk seluruh identitas, level administrasi, kontak kantor, dan pejabat definitif yang dapat dikonfigurasi melalui berkas konfigurasi Laravel dan environment `.env` tanpa perlu mengubah kode program.

---

## 2. Masalah yang Diselesaikan
Saat ini, identitas kantor (seperti nama instansi "BPS Kabupaten Mempawah", kode satker `6104`, level `KAKO`, dan provinsi "Kalimantan Barat") tersebar secara terpisah di tabel database `pengaturans`, seeder, dan string statis pada halaman tampilan (misal pada halaman [Login.php](file:///home/ihza/Projects/sipedas/app/Filament/Pages/Auth/Login.php)). Satker lain yang mengkloning repositori ini kesulitan menemukan di mana saja identitas kantor harus diubah.

---

## 3. Spesifikasi Teknis

### 3.1 Berkas `config/satker.php`
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identitas Satuan Kerja BPS
    |--------------------------------------------------------------------------
    */
    'kode' => env('SATKER_KODE', '6104'),
    'kode_surat' => env('SATKER_KODE_SURAT', env('SATKER_KODE', '6104') . '1'),
    'nama' => env('SATKER_NAMA', 'BPS Kabupaten Mempawah'),
    'level' => env('SATKER_LEVEL', 'kako'), // 'kako' (Kabupaten/Kota) | 'provinsi'
    'kabkot' => env('SATKER_KABKOT', 'Kabupaten Mempawah'),
    'provinsi' => env('SATKER_PROVINSI', 'Kalimantan Barat'),
    'ibukota' => env('SATKER_IBUKOTA', 'Mempawah'), // Kota kedudukan kantor
    'alamat' => env('SATKER_ALAMAT', 'Jl. Raden Kusno No. 1, Mempawah'),
    'kodepos' => env('SATKER_KODEPOS', '78912'),
    'telepon' => env('SATKER_TELEPON', ''),
    'email' => env('SATKER_EMAIL', 'bps6104@bps.go.id'),
    'website' => env('SATKER_WEBSITE', 'https://mempawahkab.bps.go.id'),

    /*
    |--------------------------------------------------------------------------
    | Pejabat Definitif Default
    |--------------------------------------------------------------------------
    */
    'pejabat' => [
        'nip_kepala' => env('SATKER_NIP_KEPALA', null),
        'nip_kasubbag' => env('SATKER_NIP_KASUBBAG', null),
        'nip_ppk' => env('SATKER_NIP_PPK', null),
        'nip_bendahara' => env('SATKER_NIP_BENDAHARA', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Preferensi Alur Kerja Default
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'st_approval_mode' => env('ST_APPROVAL_MODE', 'delegasi_ppspm'),
        'st_tembusan_kepala' => env('ST_TEMBUSAN_KEPALA', true),
        'ppk_instant_auto_approve' => env('PPK_INSTANT_AUTO_APPROVE', false),
    ],
];
```

### 3.2 Sinkronisasi Helper `Pengaturan` & `Setting`
Model `Pengaturan` yang ada saat ini (`Pengaturan::key('NAMA_KAKO')`) dimodifikasi agar memiliki fallback otomatis ke `config('satker.*')` jika rekaman di database belum diisi:
```php
public static function getValue(string $key, $default = null)
{
    $record = self::key($key);
    if ($record && !empty($record->nilai)) {
        return $record->nilai;
    }
    
    return match ($key) {
        'NAMA_KAKO' => config('satker.kabkot', $default),
        'NAMA_PROVINSI' => config('satker.provinsi', $default),
        'LEVEL_SATUAN_KERJA' => strtoupper(config('satker.level', 'KAKO')),
        'ID_PLH_DEFAULT' => config('satker.pejabat.nip_kasubbag', $default),
        'NIP_PPK_SATER' => config('satker.pejabat.nip_ppk', $default),
        default => $default,
    };
}
```

### 3.3 Pembersihan Teks Hardcoded di Antarmuka
Mengganti teks *"Masuk via SSO BPS Mempawah"* di `app/Filament/Pages/Auth/Login.php` dan tampilan email/kop menjadi dinamis mengikuti `config('satker.nama')`.

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Perubahan variabel `SATKER_NAMA` di `.env` langsung tercermin di judul aplikasi, halaman login, kop surat, dan email notifikasi tanpa perlu mengubah kode PHP/Blade.
2. Basis kode yang belum memiliki data di database `pengaturans` tetap dapat berjalan normal dengan mengandalkan fallback konfigurasi.
3. Seluruh unit test eksisting tetap lulus 100%.
