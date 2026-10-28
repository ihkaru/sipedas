<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Satuan Kerja BPS
    |--------------------------------------------------------------------------
    |
    | Konfigurasi identitas kantor BPS. Nilai default mengacu ke BPS Kabupaten
    | Mempawah (6104) untuk menjamin backward-compatibility 100%. Satker lain
    | cukup menyesuaikan nilai ini di file .env kantor masing-masing.
    |
    */

    'kode' => env('SATKER_KODE', '6104'),
    'kode_surat' => env('SATKER_KODE_SURAT', env('SATKER_KODE', '6104').'1'),
    'nama' => env('SATKER_NAMA', 'BPS Kabupaten Mempawah'),
    'level' => env('SATKER_LEVEL', env('SATKER_TINGKAT', 'kako')), // 'kako' | 'provinsi' | 'kota'
    'tingkat' => env('SATKER_TINGKAT', env('SATKER_LEVEL', 'kako')),
    'kabkot' => env('SATKER_KABKOT', 'Kabupaten Mempawah'),
    'kabupaten' => env('SATKER_KABUPATEN', 'Mempawah'),
    'provinsi' => env('SATKER_PROVINSI', 'Kalimantan Barat'),
    'ibukota' => env('SATKER_IBUKOTA', 'Mempawah'), // Kota kedudukan kantor
    'alamat' => env('SATKER_ALAMAT', 'Jl. Raden Kusno No. 1, Mempawah'),
    'kodepos' => env('SATKER_KODEPOS', '78912'),
    'telepon' => env('SATKER_TELEPON', ''),
    'email' => env('SATKER_EMAIL', 'bps6104@bps.go.id'),
    'website' => env('SATKER_WEBSITE', 'https://mempawahkab.bps.go.id'),

    /*
    |--------------------------------------------------------------------------
    | Pejabat Definitif Inti Satker
    |--------------------------------------------------------------------------
    |
    | NIP 18 digit pejabat pemegang kewenangan formal di kantor BPS.
    |
    */

    'pejabat' => [
        'nip_kepala' => env('SATKER_NIP_KEPALA', '196908031992111001'),
        'kepala_nip' => env('SATKER_NIP_KEPALA', '196908031992111001'),
        'nip_kasubbag' => env('SATKER_NIP_KASUBBAG_TU', env('SATKER_NIP_KASUBBAG', '198008112005021004')),
        'kasubbag_tu_nip' => env('SATKER_NIP_KASUBBAG_TU', env('SATKER_NIP_KASUBBAG', '198008112005021004')),
        'nip_ppk' => env('SATKER_NIP_PPK', '199306062016021001'),
        'ppk_nip' => env('SATKER_NIP_PPK', '199306062016021001'),
        'nip_bendahara' => env('SATKER_NIP_BENDAHARA', '198801012010121001'),
        'bendahara_nip' => env('SATKER_NIP_BENDAHARA', '198801012010121001'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mode Autentikasi Pengguna
    |--------------------------------------------------------------------------
    |
    | 'sso_saml'       : Langsung auto-redirect ke SSO SAML 2.0 (Default Mempawah).
    | 'email_password' : Form login lokal email & password standar (Rekomendasi Satker baru).
    | 'hybrid'         : Form email/password sekaligus tombol login SSO SAML.
    |
    */

    'auth' => [
        'mode' => env('AUTH_MODE', 'sso_saml'),
        'sso_label' => env('AUTH_SSO_LABEL', 'Masuk dengan SSO BPS (SAML 2.0)'),
        'sso_route' => env('AUTH_SSO_ROUTE', 'saml.login'),
        'allow_password_reset' => env('AUTH_ALLOW_PASSWORD_RESET', true),
        'default_password' => env('AUTH_DEFAULT_PASSWORD', 'BpsSatker2026!'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Format Template Penomoran Naskah Dinas
    |--------------------------------------------------------------------------
    |
    | Placeholder token: {nomor}, {sub_nomor}, {kode_satker}, {kode_surat},
    | {kode_klasifikasi}, {bulan}, {bulan_romawi}, {tahun}.
    |
    */

    'format_surat' => [
        'surat_tugas' => env('FORMAT_SURAT_TUGAS', 'B-{nomor}{sub_nomor}/{kode_surat}/{kode_klasifikasi}/{bulan}/{tahun}'),
        'sppd' => env('FORMAT_SURAT_SPPD', '{nomor}{sub_nomor}/{kode_surat}/PL.300/{bulan}/{tahun}'),
        'bast' => env('FORMAT_SURAT_BAST', '{nomor}{sub_nomor}/{kode_satker}/BAST/{bulan}/{tahun}'),
        'spk' => env('FORMAT_SURAT_SPK', 'B-{nomor}{sub_nomor}/{kode_surat}/{kode_klasifikasi}/{bulan}/{tahun}'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Preferensi Alur Kerja Default
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'st_approval_mode' => env('ST_APPROVAL_MODE', 'delegasi_ppspm'),
        'st_tembusan_kepala' => env('WHATSAPP_NOTIFY_ST_TEMBUSAN_KEPALA', true),
        'ppk_instant_auto_approve' => env('PPK_INSTANT_AUTO_APPROVE', false),
    ],

];
