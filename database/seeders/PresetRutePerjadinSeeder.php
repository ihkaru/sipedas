<?php

namespace Database\Seeders;

use App\Models\PresetRutePerjadin;
use Illuminate\Database\Seeder;

class PresetRutePerjadinSeeder extends Seeder
{
    /**
     * Run the database seeds with verified Kantor Camat coordinates for Mempawah.
     */
    public function run(): void
    {
        $presets = [
            [
                'kec_id' => '6104100',
                'nama_kecamatan' => 'MEMPAWAH HILIR',
                'kantor_camat_nama' => 'Kantor Camat Mempawah Hilir',
                'kantor_camat_alamat' => 'Jl. Raden Kusno, Kelurahan Tengah, Mempawah Hilir',
                'kantor_camat_koordinat' => '0.354167, 108.961111',
                'jarak_kategori' => 'Dalam Kota',
                'estimasi_menit' => 15,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 08.20', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Kantor BPS menuju Kantor Camat Mempawah Hilir dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '08.20 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel/wilayah penugasan dan melaksanakan kegiatan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan lapangan, verifikasi data sampel, dan evaluasi hasil kegiatan.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 15.30', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104101',
                'nama_kecamatan' => 'MEMPAWAH TIMUR',
                'kantor_camat_nama' => 'Kantor Camat Mempawah Timur',
                'kantor_camat_alamat' => 'Jl. Daeng Manambon / Jl. Pangsuma, Desa Antibar, Mempawah Timur',
                'kantor_camat_koordinat' => '0.367236, 108.977861',
                'jarak_kategori' => 'Dekat',
                'estimasi_menit' => 30,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 08.30', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Mempawah Timur dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '08.30 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel dan melaksanakan kegiatan pengawasan/pendataan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan kegiatan lapangan, pengecekan konsistensi data, dan verifikasi dokumen.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 15.45', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104090',
                'nama_kecamatan' => 'SUNGAI PINYUH',
                'kantor_camat_nama' => 'Kantor Camat Sungai Pinyuh',
                'kantor_camat_alamat' => 'Jl. Raya Sungai Pinyuh (Jurusan Anjongan), Sungai Pinyuh',
                'kantor_camat_koordinat' => '0.279861, 109.139722',
                'jarak_kategori' => 'Sedang',
                'estimasi_menit' => 45,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 08.45', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Sungai Pinyuh dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '08.45 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sasaran kegiatan dan melaksanakan tugas lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan teknis di lapangan dan pemeriksaan kelengkapan berkas.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 16.00', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104091',
                'nama_kecamatan' => 'ANJONGAN',
                'kantor_camat_nama' => 'Kantor Camat Anjongan',
                'kantor_camat_alamat' => 'Jl. Raya Mandor KM 1.8, Anjungan Melancar, Kec. Anjongan',
                'kantor_camat_koordinat' => '0.328333, 109.186667',
                'jarak_kategori' => 'Sedang',
                'estimasi_menit' => 50,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 08.50', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Anjongan dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '08.50 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel dan melaksanakan kegiatan pengawasan/pendataan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan kegiatan lapangan dan verifikasi isian kuesioner bersama petugas lapangan.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 16.05', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104081',
                'nama_kecamatan' => 'SEGEDONG',
                'kantor_camat_nama' => 'Kantor Camat Segedong',
                'kantor_camat_alamat' => 'Jl. Raya Segedong, Desa Parit Bugis, Kec. Segedong',
                'kantor_camat_koordinat' => '0.153014, 109.186969',
                'jarak_kategori' => 'Sedang',
                'estimasi_menit' => 50,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 08.50', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Segedong dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '08.50 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sasaran desa dan melaksanakan kegiatan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan teknis dan validasi data hasil pendataan.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 16.05', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104080',
                'nama_kecamatan' => 'JONGKAT',
                'kantor_camat_nama' => 'Kantor Camat Jongkat',
                'kantor_camat_alamat' => 'Jl. Raya Jongkat KM 19.8, Desa Wajok Hulu, Kec. Jongkat',
                'kantor_camat_koordinat' => '0.082500, 109.213611',
                'jarak_kategori' => 'Jauh',
                'estimasi_menit' => 60,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 09.00', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Jongkat dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '09.00 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel/wilayah penugasan dan melaksanakan kegiatan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pengawasan/pendataan lapangan dan evaluasi progres kegiatan.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 16.15', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104110',
                'nama_kecamatan' => 'SUNGAI KUNYIT',
                'kantor_camat_nama' => 'Kantor Camat Sungai Kunyit',
                'kantor_camat_alamat' => 'Jl. Raya Sungai Kunyit, Kec. Sungai Kunyit',
                'kantor_camat_koordinat' => '0.499822, 108.911199',
                'jarak_kategori' => 'Sedang',
                'estimasi_menit' => 50,
                'steps' => [
                    ['step' => 1, 'waktu' => '08.00 - 08.50', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Sungai Kunyit dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '08.50 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel/wilayah kerja dan melaksanakan kegiatan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan kegiatan teknis lapangan dan validasi isian kuesioner responden.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 16.05', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104120',
                'nama_kecamatan' => 'TOHO',
                'kantor_camat_nama' => 'Kantor Camat Toho',
                'kantor_camat_alamat' => 'Jl. Raya Toho, Kec. Toho',
                'kantor_camat_koordinat' => '0.414863, 109.222462',
                'jarak_kategori' => 'Jauh',
                'estimasi_menit' => 80,
                'steps' => [
                    ['step' => 1, 'waktu' => '07.30 - 09.00', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Toho dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '09.00 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sasaran desa dan melaksanakan pengawasan/pendataan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan teknis dan pengecekan kelengkapan data.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 16.35', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
            [
                'kec_id' => '6104121',
                'nama_kecamatan' => 'SADANIANG',
                'kantor_camat_nama' => 'Kantor Camat Sadaniang',
                'kantor_camat_alamat' => 'Jl. Raya Sadaniang, Desa Pentek, Kec. Sadaniang',
                'kantor_camat_koordinat' => '0.527728, 109.151875',
                'jarak_kategori' => 'Terjauh',
                'estimasi_menit' => 105,
                'steps' => [
                    ['step' => 1, 'waktu' => '07.30 - 09.30', 'kategori' => 'berangkat_koordinasi', 'uraian' => 'Perjalanan dari Mempawah menuju Kecamatan Sadaniang dan berkoordinasi dengan pihak kecamatan serta penandatanganan/cap visum SPPD.'],
                    ['step' => 2, 'waktu' => '09.30 - 12.00', 'kategori' => 'lapangan_1', 'uraian' => 'Perjalanan menuju lokasi sampel/wilayah penugasan dan melaksanakan kegiatan lapangan.'],
                    ['step' => 3, 'waktu' => '12.00 - 13.00', 'kategori' => 'ishoma', 'uraian' => 'Istirahat, Sholat Zhuhur, dan Makan Siang'],
                    ['step' => 4, 'waktu' => '13.00 - 15.00', 'kategori' => 'lapangan_2', 'uraian' => 'Melanjutkan pelaksanaan kegiatan teknis dan verifikasi data lapangan.'],
                    ['step' => 5, 'waktu' => '15.00 - 15.15', 'kategori' => 'sholat_ashar', 'uraian' => 'Istirahat dan Sholat Ashar'],
                    ['step' => 6, 'waktu' => '15.15 - 17.00', 'kategori' => 'kembali', 'uraian' => 'Perjalanan kembali ke Mempawah.'],
                ],
            ],
        ];

        foreach ($presets as $item) {
            PresetRutePerjadin::updateOrCreate(
                ['nama_kecamatan' => $item['nama_kecamatan']],
                $item
            );
        }
    }
}
