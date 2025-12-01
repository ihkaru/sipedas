<?php

namespace App\Supports;

class Constants {
    const NON_SPPD = "NON_SPPD";
    const PERJALAN_DINAS_DALAM_KOTA = "PERJALAN_DINAS_DALAM_KOTA";
    const PERJALANAN_DINAS_LUAR_KOTA = "PERJALANAN_DINAS_LUAR_KOTA";
    const PERJALANAN_DINAS_PAKET_MEETING = "PERJALANAN_DINAS_PAKET_MEETING";
    const JENIS_SURAT_TUGAS_OPTIONS = [
        "NON_SPPD" => "Non SPPD",
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
    const STATUS_PENGAJUAN_PERLU_REVISI = "STATUS_PENGAJUAN_PERLU_REVISI";
    const STATUS_PENGAJUAN_DITOLAK = "STATUS_PENGAJUAN_DITOLAK";
    const STATUS_PENGAJUAN_DISETUJUI = "STATUS_PENGAJUAN_DISETUJUI";
    const STATUS_PENGAJUAN_DICETAK = "STATUS_PENGAJUAN_DICETAK";
    const STATUS_PENGAJUAN_DIKUMPULKAN = "STATUS_PENGAJUAN_DIKUMPULKAN";
    const STATUS_PENGAJUAN_DICAIRKAN = "STATUS_PENGAJUAN_DICAIRKAN";
    const STATUS_PENGAJUAN_DIBATALKAN = "STATUS_PENGAJUAN_DIBATALKAN";
    const STATUS_PENGAJUAN_OPTIONS = [
        "STATUS_PENGAJUAN_DIKIRIM" => "Dikirim",
        "STATUS_PENGAJUAN_DIBATALKAN" => "Dibatalkan",
        "STATUS_PENGAJUAN_PERLU_REVISI" => "Perlu Revisi",
        "STATUS_PENGAJUAN_DITOLAK" => "Ditolak",
        "STATUS_PENGAJUAN_DISETUJUI" => "Disetujui",
        "STATUS_PENGAJUAN_DICETAK" => "Dicetak",
        "STATUS_PENGAJUAN_DIKUMPULKAN" => "Dikumpulkan",
        "STATUS_PENGAJUAN_DICAIRKAN" => "Dicairkan",
    ];

    const LEVEL_PENUGASAN_TANPA_LOKASI = "LEVEL_PENUGASAN_TANPA_LOKASI";
    const LEVEL_PENUGASAN_DESA_KELURAHAN = "LEVEL_PENUGASAN_DESA_KELURAHAN";
    const LEVEL_PENUGASAN_KECAMATAN = "LEVEL_PENUGASAN_KECAMATAN";
    const LEVEL_PENUGASAN_KABUPATEN_KOTA = "LEVEL_PENUGASAN_KABUPATEN_KOTA";
    const LEVEL_PENUGASAN_NAMA_TEMPAT = "LEVEL_PENUGASAN_NAMA_TEMPAT";
    const LEVEL_PENUGASAN_OPTIONS = [
        "LEVEL_PENUGASAN_TANPA_LOKASI" => "Tanpa Lokasi Tujuan",
        "LEVEL_PENUGASAN_DESA_KELURAHAN" => "Lokasi Tujuan Desa/Kelurahan",
        "LEVEL_PENUGASAN_KECAMATAN" => "Lokasi Tujuan Kecamatan",
        "LEVEL_PENUGASAN_KABUPATEN_KOTA" => "Lokasi Tujuan Kabupaten/Kota",
        "LEVEL_PENUGASAN_NAMA_TEMPAT" => "Lokasi Tujuan Nama Tempat"
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
        "JENIS_NOMOR_SURAT_PERJANJIAN_KERJA" => "Nomor Surat Perjanjian Kerja",
        "JENIS_NOMOR_SURAT_BAST" => "Nomor Surat BAST",
    ];

    const JENIS_PESERTA_SURAT_TUGAS_PEGAWAI = "JENIS_PESERTA_SURAT_TUGAS_PEGAWAI";
    const JENIS_PESERTA_SURAT_TUGAS_MITRA = "JENIS_PESERTA_SURAT_TUGAS_MITRA";
    const JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA = "JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA";
    const JENIS_PESERTA_SURAT_TUGAS = [
        "JENIS_PESERTA_SURAT_TUGAS_PEGAWAI" => "Pegawai",
        "JENIS_PESERTA_SURAT_TUGAS_MITRA" => "Mitra",
        "JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA" => "Pegawai dan Mitra",
    ];
    const JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_OPTIONS = [
        "JENIS_PESERTA_SURAT_TUGAS_PEGAWAI" => "Pegawai",
    ];

    const PANGKAT_I = "I";
    const PANGKAT_II = "II";
    const PANGKAT_III = "III";
    const PANGKAT_IV = "IV";
    const PANGKAT_OPTIONS = [
        'I' => "Juru",
        'II' => "Pengatur",
        'III' => "Penata",
        'IV' => "Pembina",
        'V' => "Operator"
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

    const COLUMN_ALOKASI_HONOR_IMPORT = ['id_sobat', 'id_kegiatan', 'belum_ada', 'bisa_ikut', 'nama_petugas', 'jabatan', 'nik', 'jenis_honor', 'id_honor', 'satuan_honor', 'honor_per_satuan_honor', 'target_per_satuan_honor', 'target_honor', 'honor_bersih', 'tanggal_akhir_kegiatan', 'tahun_akhir_kegiatan', 'bulan_akhir_kegiatan', 'id_batasan_honor', 'id_alokasi_waktu_honor', 'id_alokasi_waktu_batasan_honor', 'sudah_dibayarkan', 'sisa_hari_hingga_batas_pencairan', 'tanggal_pencairan', 'nama_kegiatan', 'kecamatan_domisili', 'desa_domisili', 'tanggal_penanda_tanganan_spk_oleh_petugas', 'tanggal_mulai_perjanjian', 'tanggal_akhir_perjanjian', 'tanggal_terima_hasil_pekerjaan_paling_lambat'];
    const COLUMN_TIMESTAMP_ALOKASI_HONOR_IMPORT = ['tanggal_akhir_kegiatan', 'tanggal_penandan_tanganan_spk_oleh_petugas', 'tanggal_mulai_perjanjian', 'tanggal_akhir_perjanjian', 'tanggal_terima_hasil_paling_lambat'];

    const COLUMN_MITRA_IMPORT = ['id_sobat', 'nama_1', 'kabupaten_domisili', 'kecamatan_domisili', 'desa_domisili', 'nik', 'nama_2', 'posisi', 'status_seleksi_1_terpilih_2_tidak_terpilih', 'email', 'alamat_prov', 'alamat_kab', 'alamat_kec', 'alamat_desa', 'alamat_detail', 'domisili_sama', 'tanggal_lahir_dd_mm_yyyy', 'npwp', 'jenis_kelamin', 'agama', 'status_perkawinan', 'pendidikan', 'pekerjaan', 'deskripsi_pekerjaan_lain', 'no_telp', 'mengikuti_pendataan_bps', 'sp', 'st', 'se', 'susenas', 'sakernas', 'sbh', 'catatan', 'posisi_daftar', 'username', 'sobat_id', 'id_desa',];
    const COLUMN_SURAT_TUGAS_IMPORT = ['no_st', 'nip', 'apakah_sppd', 'nama_kegiatan', 'level_tujuan', 'kabupaten', 'kecamatan', 'desa', 'lokasi', 'tanggal_mulai', 'tanggal_selesai', 'jenis_petugas', 'unique', 'grup_id', 'nomor', 'subnomor', 'tanggal_nomor'];
    const COLUMN_TIMESTAMP_SURAT_TUGAS_IMPORT = ['tanggal_mulai', 'tanggal_selesai', 'tanggal_nomor'];

    const JABATAN_MITRA = "Mitra Statistik";
    const JABATAN_PEGAWAI = "Pegawai";
    const JABATAN_KASUBBAG = "Kepala Sub Bagian Umum";
    const JENIS_PERIODE_TRIWULAN = "TW";
    const JENIS_PERIODE_SUBROUND = "SR";
    const JENIS_PERIODE_SEMESTER = "SM";
    const JENIS_PERIODE_BULAN = "BULAN";
    const JENIS_PERIODE = [
        "TW" => "Triwulan",
        "SR" => "Subround",
        "SM" => "Semester",
        "BULAN" => "Bulan",
    ];

    const COLUMN_KEGIATAN_MANMIT_IMPORT = ['tanggal_mulai_pelaksanaan', 'tanggal_akhir_pelaksanaan', 'tanggal_mulai_penawaran', 'tanggal_akhir_penawaran', 'id_kegiatan', 'id_tahun', 'id_spec', 'kategori_periode', 'id_kegiatan_manmit', 'nama_kegiatan_manmit', 'level_2', 'periode'];
    const COLUMN_TIMESTAMP_KEGIATAN_MANMIT_IMPORT = ['tanggal_mulai_pelaksanaan', 'tanggal_akhir_pelaksanaan', 'tanggal_mulai_penawaran', 'tanggal_akhir_penawaran'];

    // Konstanta untuk Jenis Kegiatan
    const SENSUS = 'SENSUS';
    const SURVEI = 'SURVEI';
    const JENIS_KEGIATAN_OPTIONS = [
        self::SENSUS => 'Sensus',
        self::SURVEI => 'Survei',
    ];

    // Konstanta untuk Frekuensi Kegiatan
    const FREKUENSI_BULANAN = 'BULANAN';
    const FREKUENSI_SUBROUND = 'SUBROUND';
    const FREKUENSI_TRIWULANAN = 'TRIWULANAN';
    const FREKUENSI_SEMESTERAN = 'SEMESTERAN';
    const FREKUENSI_TAHUNAN = 'TAHUNAN';
    const FREKUENSI_PERIODIK = 'PERIODIK';
    const FREKUENSI_ADHOC = 'ADHOC';
    const FREKUENSI_KEGIATAN_OPTIONS = [
        self::FREKUENSI_BULANAN => 'Bulanan',
        self::FREKUENSI_SUBROUND => 'Subround',
        self::FREKUENSI_TRIWULANAN => 'Triwulanan',
        self::FREKUENSI_SEMESTERAN => 'Semesteran',
        self::FREKUENSI_TAHUNAN => 'Tahunan',
        self::FREKUENSI_PERIODIK => 'Periodik',
        self::FREKUENSI_ADHOC => 'Adhoc',
    ];

    // Opsi untuk Checkbox Subround
    const SUBROUND_OPTIONS = [
        'SR1' => 'Subround 1',
        'SR2' => 'Subround 2',
        'SR3' => 'Subround 3',
        'SR4' => 'Subround 4',
    ];

    // Opsi untuk Checkbox Triwulan
    const TRIWULAN_OPTIONS = [
        'TW1' => 'Triwulan 1',
        'TW2' => 'Triwulan 2',
        'TW3' => 'Triwulan 3',
        'TW4' => 'Triwulan 4',
    ];

    // Opsi untuk Checkbox Semester
    const SEMESTER_OPTIONS = [
        'SEM1' => 'Semester 1',
        'SEM2' => 'Semester 2',
    ];

    // Opsi untuk Checkbox Bulan
    const BULAN_OPTIONS = [
        'JANUARI' => 'Januari',
        'FEBRUARI' => 'Februari',
        'MARET' => 'Maret',
        'APRIL' => 'April',
        'MEI' => 'Mei',
        'JUNI' => 'Juni',
        'JULI' => 'Juli',
        'AGUSTUS' => 'Agustus',
        'SEPTEMBER' => 'September',
        'OKTOBER' => 'Oktober',
        'NOVEMBER' => 'November',
        'DESEMBER' => 'Desember',
    ];

    const SINGLE_OCCURRENCE_FREQUENCIES = [
        self::FREKUENSI_TAHUNAN,
        self::FREKUENSI_ADHOC,
        self::FREKUENSI_PERIODIK,
    ];

    // Konstanta untuk Modul Honor
    const JABATAN_PPL = 'PPL';
    const JABATAN_PML = 'PML';
    const JABATAN_PETUGAS_ENTRI = 'PETUGAS ENTRI';
    const JABATAN_KOSEKA = 'KOSEKA';
    const JABATAN_MITRA_OPTIONS = [
        self::JABATAN_PETUGAS_ENTRI => 'Petugas Entri',
        self::JABATAN_PPL => 'Petugas Pencacah Lapangan (PPL)',
        self::JABATAN_PML => 'Petugas Pemeriksa Lapangan (PML)',
        self::JABATAN_KOSEKA => 'Koordinator Sensus Kecamatan (Koseka)',
    ];

    const JENIS_HONOR_PENDATAAN = 'PENDATAAN';
    const JENIS_HONOR_PENDATAAN_KONSUMEN = 'PENDATAAN KONSUMEN';
    const JENIS_HONOR_PENDATAAN_PRODUSEN = 'PENDATAAN PRODUSEN';
    const JENIS_HONOR_KSA_JAGUNG = 'KSA JAGUNG';
    const JENIS_HONOR_KSA_PADI = 'KSA PADI';
    const JENIS_HONOR_PAKET_UPDATING_LISTING = 'PAKET UPDATING LISTING';
    const JENIS_HONOR_UPDATING_LISTING = 'UPDATING LISTING';
    const JENIS_HONOR_PENGOLAHAN_LISTING = 'PENGOLAHAN LISTING';
    const JENIS_HONOR_PENGOLAHAN = 'PENGOLAHAN';
    const JENIS_HONOR_PENGOLAHAN_PENDATAAN = 'PENGOLAHAN PENDATAAN';
    const JENIS_HONOR_PENDATAAN_BARANG = 'PENDATAAN BARANG';
    const JENIS_HONOR_PENDATAAN_JASA = 'PENDATAAN JASA';
    const JENIS_HONOR_PALAWIJA = 'PALAWIJA';
    const JENIS_HONOR_PADI = 'PADI';
    const JENIS_HONOR_PERGUDANGAN = 'PERGUDANGAN';
    const JENIS_HONOR_JASA_PENUNJANG_ANGKUTAN = 'JASA PENUNJANG ANGKUTAN';
    const JENIS_HONOR_UPDATING_PALAWIJA = 'UPDATING PALAWIJA';
    const JENIS_HONOR_LISTING = 'LISTING';
    const JENIS_HONOR_UPDATING = 'UPDATING';
    const JENIS_HONOR_UPDATING_LISTING_1 = 'UPDATING LISTING-1';
    const JENIS_HONOR_PENDATAAN_1 = 'PENDATAAN-1';
    const JENIS_HONOR_UPDATING_LISTING_2 = 'UPDATING LISTING-2';
    const JENIS_HONOR_PENDATAAN_2 = 'PENDATAAN-2';
    const JENIS_HONOR_PENGOLahan_1 = 'PENGOLAHAN-1';
    const JENIS_HONOR_PENGOLahan_2 = 'PENGOLAHAN-2';

    const JENIS_HONOR_OPTIONS = [
        self::JENIS_HONOR_PENDATAAN => 'Pendataan',
        self::JENIS_HONOR_PENDATAAN_KONSUMEN => 'Pendataan Konsumen',
        self::JENIS_HONOR_PENDATAAN_PRODUSEN => 'Pendataan Produsen',
        self::JENIS_HONOR_KSA_JAGUNG => 'KSA Jagung',
        self::JENIS_HONOR_KSA_PADI => 'KSA Padi',
        self::JENIS_HONOR_PAKET_UPDATING_LISTING => 'Paket Updating Listing',
        self::JENIS_HONOR_UPDATING_LISTING => 'Updating Listing',
        self::JENIS_HONOR_PENGOLAHAN_LISTING => 'Pengolahan Listing',
        self::JENIS_HONOR_PENGOLAHAN => 'Pengolahan',
        self::JENIS_HONOR_PENGOLAHAN_PENDATAAN => 'Pengolahan Pendataan',
        self::JENIS_HONOR_PENDATAAN_BARANG => 'Pendataan Barang',
        self::JENIS_HONOR_PENDATAAN_JASA => 'Pendataan Jasa',
        self::JENIS_HONOR_PALAWIJA => 'Palawija',
        self::JENIS_HONOR_PADI => 'Padi',
        self::JENIS_HONOR_PERGUDANGAN => 'Pergudangan',
        self::JENIS_HONOR_JASA_PENUNJANG_ANGKUTAN => 'Jasa Penunjang Angkutan',
        self::JENIS_HONOR_UPDATING_PALAWIJA => 'Updating Palawija',
        self::JENIS_HONOR_LISTING => 'Listing',
        self::JENIS_HONOR_UPDATING => 'Updating',
        self::JENIS_HONOR_UPDATING_LISTING_1 => 'Updating Listing-1',
        self::JENIS_HONOR_PENDATAAN_1 => 'Pendataan-1',
        self::JENIS_HONOR_UPDATING_LISTING_2 => 'Updating Listing-2',
        self::JENIS_HONOR_PENDATAAN_2 => 'Pendataan-2',
        self::JENIS_HONOR_PENGOLahan_1 => 'Pengolahan-1',
        self::JENIS_HONOR_PENGOLahan_2 => 'Pengolahan-2',
    ];

    // -- Satuan Honor (Diperbarui dengan daftar baru) --
    const SATUAN_HONOR_DOKUMEN = 'DOKUMEN';
    const SATUAN_HONOR_SEGMEN = 'SEGMEN';
    const SATUAN_HONOR_BLOK_SENSUS = 'BLOK SENSUS';
    const SATUAN_HONOR_RUMAH_TANGGA = 'RUMAH TANGGA';
    const SATUAN_HONOR_OB = 'OB';
    const SATUAN_HONOR_BS = 'BS';
    const SATUAN_HONOR_SLS = 'SLS';

    const SATUAN_HONOR_OPTIONS = [
        self::SATUAN_HONOR_DOKUMEN => 'Dokumen',
        self::SATUAN_HONOR_SEGMEN => 'Segmen',
        self::SATUAN_HONOR_BLOK_SENSUS => 'Blok Sensus',
        self::SATUAN_HONOR_RUMAH_TANGGA => 'Rumah Tangga',
        self::SATUAN_HONOR_OB => 'Orang Bulan (OB)',
        self::SATUAN_HONOR_BS => 'Blok Sensus (BS)',
        self::SATUAN_HONOR_SLS => 'Satuan Lingkungan Setempat (SLS)',
    ];



    const COLUMN_HONOR_IMPORT = [
        'id_kegiatan',
        'jabatan',
        'jenis_honor',
        'satuan_honor',
        'harga_per_satuan_honor',
        'tanggal_akhir_kegiatan',
    ];

    const COLUMN_TIMESTAMP_HONOR_IMPORT = [
        'tanggal_akhir_kegiatan'
    ];

    public static function getJenisTransportasiOptions() {
        return self::JENIS_TRANSPORTASI_OPTIONS;
    }
    public static function getJenisSuratTugasOptions() {
        return self::JENIS_SURAT_TUGAS_OPTIONS;
    }
    public static function flatJenisSuratTugasOptions() {
        return collect(self::getJenisSuratTugasOptions())->keys()->toArray();
    }
    public static function flatJenisTransportasiOptions() {
        return collect(self::getJenisTransportasiOptions())->keys()->toArray();
    }
    /**
     * Mengekstrak teks di dalam tanda kurung pertama dari sebuah string.
     *
     * @param string $input String yang mungkin berisi teks dalam tanda kurung.
     * @return string Teks yang diekstrak, atau string kosong jika tidak ditemukan.
     */
    public static function getTextInParentheses(string $input): string {
        // Pola regex ini aman dan efisien untuk kasus ini.
        // Tidak rentan terhadap ReDoS karena kesederhanaannya dan penggunaan non-greedy quantifier.
        $pattern = '/\((.*?)\)/';

        if (preg_match($pattern, $input, $matches)) {
            // $matches[1] berisi teks di dalam tanda kurung.
            return $matches[1];
        }

        return '';
    }
}
