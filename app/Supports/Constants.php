<?php
namespace App\Supports;
class Constants {
    const NON_SPPD = "NON_SPPD";
    const PERJALANAN_DINAS_BIASA="PERJALANAN_DINAS_BIASA";
    const PERJALAN_DINAS_DALAM_KOTA="PERJALAN_DINAS_DALAM_KOTA";
    const PERJALANAN_DINAS_LUAR_KOTA="PERJALANAN_DINAS_LUAR_KOTA";
    const PERJALANAN_DINAS_PAKET_MEETING="PERJALANAN_DINAS_PAKET_MEETING";
    const JENIS_SURAT_TUGAS_OPTIONS = [
        "NON_SPPD" => "Non SPPD",
        "PERJALANAN_DINAS_BIASA" => "Perjalan Dinas Biasa",
        "PERJALANAN_DINAS_LUAR_KOTA" => "Perjalanan Dinas Luar Kota",
        "PERJALAN_DINAS_DALAM_KOTA" => "Perjalanan Dinas Dalam Kota",
        "PERJALANAN_DINAS_PAKET_MEETING" => "Perjalanan Dinas Paket Meeting",
    ];

    const NON_TRANSPORTASI = "NON_TRANSPORTASI";
    const TRANSPORTASI_KENDARAAN_UMUM = "TRANSPORTASI_KENDARAAN_UMUM";
    const TRANSPORTASI_KENDARAAN_DINAS = "TRANSPORTASI_KENDARAAN_DINAS";
    const JENIS_TRANSPORTASI_OPTIONS = [
        "NON_TRANSPORTASI" => "-",
        "TRANSPORTASI_KENDARAAN_UMUM" => "Kendaraan Umum",
        "TRANSPORTASI_KENDARAAN_DINAS" => "Kendaraan Dinas",
    ];
    const STATUS_PENGAJUAN_DIKIRIM = "STATUS_PENGAJUAN_DIKIRIM";
    const STATUS_PENGAJUAN_PERLU_REVISI= "STATUS_PENGAJUAN_PERLU_REVISI";
    const STATUS_PENGAJUAN_DITOLAK = "STATUS_PENGAJUAN_DITOLAK";
    const STATUS_PENGAJUAN_DISETUJUI = "STATUS_PENGAJUAN_DISETUJUI";
    const STATUS_PENGAJUAN_DICETAK= "STATUS_PENGAJUAN_DICETAK";
    const STATUS_PENGAJUAN_DIKUMPULKAN = "STATUS_PENGAJUAN_DIKUMPULKAN";
    const STATUS_PENGAJUAN_DICAIRKAN = "STATUS_PENGAJUAN_DICAIRKAN";
    const STATUS_PENGAJUAN_DIBATALKAN = "STATUS_PENGAJUAN_DIBATALKAN";
    const STATUS_PENGAJUAN_OPTIONS = [
        "STATUS_PENGAJUAN_DIKIRIM"=>"Dikirim",
        "STATUS_PENGAJUAN_DIBATALKAN"=>"Dibatalkan",
        "STATUS_PENGAJUAN_PERLU_REVISI"=>"Perlu Revisi",
        "STATUS_PENGAJUAN_DITOLAK"=>"Ditolak",
        "STATUS_PENGAJUAN_DISETUJUI"=>"Disetujui",
        "STATUS_PENGAJUAN_DICETAK"=>"Dicetak",
        "STATUS_PENGAJUAN_DIKUMPULKAN"=>"Dikumpulkan",
        "STATUS_PENGAJUAN_DICAIRKAN"=>"Dicairkan",
    ];

    const LEVEL_PENUGASAN_TANPA_LOKASI = "LEVEL_PENUGASAN_TANPA_LOKASI";
    const LEVEL_PENUGASAN_DESA_KELURAHAN = "LEVEL_PENUGASAN_DESA_KELURAHAN";
    const LEVEL_PENUGASAN_KECAMATAN = "LEVEL_PENUGASAN_KECAMATAN";
    const LEVEL_PENUGASAN_KABUPATEN_KOTA = "LEVEL_PENUGASAN_KABUPATEN_KOTA";
    const LEVEL_PENUGASAN_NAMA_TEMPAT = "LEVEL_PENUGASAN_NAMA_TEMPAT";
    const LEVEL_PENUGASAN_OPTIONS = [
        "LEVEL_PENUGASAN_TANPA_LOKASI"=>"Tanpa Lokasi Tujuan",
        "LEVEL_PENUGASAN_DESA_KELURAHAN"=>"Lokasi Tujuan Desa/Kelurahan",
        "LEVEL_PENUGASAN_KECAMATAN"=>"Lokasi Tujuan Kecamatan",
        "LEVEL_PENUGASAN_KABUPATEN_KOTA"=>"Lokasi Tujuan Kabupaten/Kota",
        "LEVEL_PENUGASAN_NAMA_TEMPAT"=>"Lokasi Tujuan Nama Tempat"
    ];

    const JENIS_NOMOR_SURAT_TUGAS = "JENIS_NOMOR_SURAT_TUGAS";
    const JENIS_NOMOR_SURAT_OPTIONS = [
        "JENIS_NOMOR_SURAT_TUGAS" => "Nomor Surat Tugas"
    ];

    const PANGKAT_I = "I";
    const PANGKAT_II = "II";
    const PANGKAT_III = "II";
    const PANGKAT_IV = "IV";
    const PANGKAT_OPTIONS = [
        'I'=>"Juru",
        'II'=>"Pengatur",
        'III'=>"Penata",
        'IV'=>"Pembina",
    ];

    const GOLONGAN_A = "a";
    const GOLONGAN_B = "b";
    const GOLONGAN_C = "c";
    const GOLONGAN_D = "d";
    const GOLONGAN_E = "e";
    const GOLONGAN_I_III_OPTIONS = [
        'a' => 'Muda',
        'b' => 'Muda Tk I',
        'c' => '',
        'd' => 'Tk I',
    ];
    const GOLONGAN_IV_OPTIONS = [
        'a' => '',
        'b' => 'Tk I',
        'c' => 'Muda',
        'd' => 'Madya',
        'e' => 'Utama',
    ];

    public static function getJenisTransportasiOptions(){
        return self::JENIS_TRANSPORTASI_OPTIONS;
    }
    public static function getJenisSuratTugasOptions(){
        return self::JENIS_SURAT_TUGAS_OPTIONS;
    }
    public static function flatJenisSuratTugasOptions(){
        return collect(self::getJenisSuratTugasOptions())->keys()->toArray();
    }
    public static function flatJenisTransportasiOptions(){
        return collect(self::getJenisTransportasiOptions())->keys()->toArray();
    }

}
