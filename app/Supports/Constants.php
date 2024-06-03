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
        "PERJALANAN_DINAS_BIASA" => "Perjalanan Dinas Biasa",
        "PERJALANAN_DINAS_LUAR_KOTA" => "Perjalanan Dinas Luar Kota",
        "PERJALAN_DINAS_DALAM_KOTA" => "Perjalanan Dinas Dalam Kota",
        "PERJALANAN_DINAS_PAKET_MEETING" => "Perjalanan Dinas Paket Meeting",
    ];

    const NON_TRANSPORTASI = "NON_TRANSPORTASI";
    const TRANSPORTASI_KENDARAAN_UMUM = "TRANSPORTASI_KENDARAAN_UMUM";
    const TRANSPORTASI_KENDARAAN_DINAS = "TRANSPORTASI_KENDARAAN_DINAS";
    const TRANSPORTASI_KENDARAAN_PRIBADI = "TRANSPORTASI_KENDARAAN_PRIBADI";
    const JENIS_TRANSPORTASI_OPTIONS = [
        "NON_TRANSPORTASI" => "-",
        "TRANSPORTASI_KENDARAAN_UMUM" => "Kendaraan Umum",
        "TRANSPORTASI_KENDARAAN_DINAS" => "Kendaraan Dinas",
        "TRANSPORTASI_KENDARAAN_PRIBADI" => "Kendaraan Pribadi",
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
    const JENIS_NOMOR_SURAT_PERJALAN_DINAS = "JENIS_NOMOR_SURAT_PERJALAN_DINAS";
    const JENIS_NOMOR_SURAT_PERJANJIAN_KERJA = "JENIS_NOMOR_SURAT_PERJANJIAN_KERJA";
    const JENIS_NOMOR_SURAT_BAST = "JENIS_NOMOR_SURAT_BAST";
    const JENIS_NOMOR_SURAT_OPTIONS = [
        "JENIS_NOMOR_SURAT_TUGAS" => "Nomor Surat Tugas",
        "JENIS_NOMOR_SURAT_PERJALAN_DINAS" => "Nomor Surat Perjalanan Dinas",
    ];
    const JENIS_NOMOR_SURAT_PERJANJIAN_KERJA_OPTIONS = [
        "JENIS_NOMOR_SURAT_PERJANJIAN_KERJA"=>"Nomor Surat Perjanjian Kerja",
        "JENIS_NOMOR_SURAT_BAST"=>"Nomor Surat BAST",
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

    const COLUMN_ALOKASI_HONOR_IMPORT = ['id_sobat','id_kegiatan','nama_petugas','jabatan','nik','jenis_honor','id_honor','satuan_honor','honor_per_satuan_honor','target_per_satuan_honor','target_honor','honor_bersih','tanggal_akhir_kegiatan','tahun_akhir_kegiatan','bulan_akhir_kegiatan','id_batasan_honor','id_alokasi_waktu_honor','id_alokasi_waktu_batasan_honor','sudah_dibayarkan','sisa_hari_hingga_batas_pencairan','tanggal_pencairan','nama_kegiatan','kecamatan_domisili','desa_domisili','tanggal_penanda_tanganan_spk_oleh_petugas','tanggal_mulai_perjanjian','tanggal_akhir_perjanjian','tanggal_terima_hasil_pekerjaan_paling_lambat','tambahan_waktu_tanggal_terima_hasil_pekerjaan_paling_lambat',];
    const COLUMN_TIMESTAMP_ALOKASI_HONOR_IMPORT = ['tanggal_akhir_kegiatan','tanggal_penandan_tanganan_spk_oleh_petugas','tanggal_mulai_perjanjian','tanggal_akhir_perjanjian','tanggal_terima_hasil_paling_lambat','tambahan_waktu_tanggal_terima_hasil_pekerjaan_paling_lambat'];

    const COLUMN_MITRA_IMPORT = ['id_sobat','nama_1','kabupaten_domisili','kecamatan_domisili','desa_domisili','nik','nama_2','posisi','status_seleksi_1_terpilih_2_tidak_terpilih','email','alamat_prov','alamat_kab','alamat_kec','alamat_desa','alamat_detail','domisili_sama','tanggal_lahir_dd_mm_yyyy','npwp','jenis_kelamin','agama','status_perkawinan','pendidikan','pekerjaan','deskripsi_pekerjaan_lain','no_telp','mengikuti_pendataan_bps','sp','st','se','susenas','sakernas','sbh','catatan','posisi_daftar','username','sobat_id','id_desa',];
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
