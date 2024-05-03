<?php

namespace Database\Seeders;

use App\DTO\PenugasanCreation;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\Plh;
use App\Supports\Constants;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PenugasanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawais = Pegawai::limit(5)->get();
        $kegiatan = Kegiatan::first();
        $nips = [];
        foreach ($pegawais as $p) {
            $nips[] = $p->nip;
        }
        $penugasanDTO = new PenugasanCreation();
        $penugasanDTO->nips = $nips;
        $penugasanDTO->kegiatanId = $kegiatan->id;
        $penugasanDTO->tglMulaiTugas = "02-05-2024";
        $penugasanDTO->tglAkhirTugas = "03-05-2024";
        $penugasanDTO->tbhHariJalanAwal = 1;
        $penugasanDTO->tbhHariJalanAkhir = 1;
        $penugasanDTO->provId = "61";
        $penugasanDTO->kabkotId = "6104";
        $penugasanDTO->kecamatanId = "6104080";
        $penugasanDTO->desaKelId = "6104080002";
        $penugasanDTO->jenisSuratTugas = Constants::PERJALANAN_DINAS_BIASA;
        $penugasanDTO->transportasi = Constants::TRANSPORTASI_KENDARAAN_UMUM;
        Penugasan::ajukan($penugasanDTO);
    }
}
