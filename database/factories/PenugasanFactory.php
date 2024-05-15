<?php

namespace Database\Factories;

use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\NomorSurat;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\Plh;
use App\Models\RiwayatPengajuan;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penugasan>
 */
class PenugasanFactory extends Factory
{
    protected static ?array $kegiatanIds;
    protected static ?array $pegawaiIds;
    protected static ?array $desKelIds;
    protected static ?array $kecIds;
    protected static ?string $plh;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kegiatanIds = static::$kegiatanIds ??= Kegiatan::pluck('id')->flatten()->toArray();
        $pegawaiIds = static::$pegawaiIds ??= Pegawai::pluck('nip')->flatten()->toArray();
        $kecIds = static::$kecIds ??= MasterSls::pluck('kec_id')->unique()->flatten()->toArray();
        $desKelIds = static::$desKelIds ??= MasterSls::pluck('desa_kel_id')->unique()->flatten()->toArray();
        $date = now()->addDays(rand(-20,20));
        $tanggalPengajuan = $date->addDays(rand(-14,0));
        $res = [
            "nip"=>$pegawaiIds[rand(0,count($pegawaiIds)-1)],
            "kegiatan_id"=>$kegiatanIds[rand(0,count($kegiatanIds)-1)],
            "tgl_mulai_tugas"=>$date,
            "level_tujuan_penugasan"=>Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
            "nama_tempat_tujuan"=>null,
            "tgl_akhir_tugas"=>$date->addDays(rand(0,3)),
            'tgl_pengajuan_tugas'=>$tanggalPengajuan,
            "tbh_hari_jalan_awal"=>rand(0,1),
            "tbh_hari_jalan_akhir"=>rand(0,1),
            "prov_id"=>"61",
            "kabkot_id"=>"6104",
            "desa_kel_id"=>$desKelIds[rand(0,count($desKelIds)-1)],
            "jenis_surat_tugas"=>Constants::flatJenisSuratTugasOptions()[rand(1,count(Constants::flatJenisSuratTugasOptions())-1)],
            "surat_tugas_id"=>NomorSurat::generateNomorSuratTugas($tanggalPengajuan)->id,
            "transportasi"=>Constants::flatJenisTransportasiOptions()[rand(1,count(Constants::flatJenisTransportasiOptions())-1)],
        ];
        $plh = static::$plh ??= Plh::getApprover([$res["nip"]],$date,returnPegawai:true)->nip;
        $res["plh_id"] = $plh;
        if(rand(0,1)){
            $res["desa_kel_id"] = $desKelIds[rand(0,count($desKelIds)-1)];
            $res["kecamatan_id"] = substr($res["desa_kel_id"] ,0,7) ;
        }else{
            $res["kecamatan_id"] = $kecIds[rand(0,count($kecIds)-1)];
        }

        return $res;
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Penugasan $penugasan) {
        })->afterCreating(function (Penugasan $penugasan) {

        });
    }

    public function dikirim(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterMaking(function (Penugasan $penugasan) {
            // ...
        })->afterCreating(function (Penugasan $penugasan) {
            $riwayatPengajuan = RiwayatPengajuan::where('penugasan_id',$penugasan->id)->first();
            if(!$riwayatPengajuan){
                RiwayatPengajuan::kirim([$penugasan->id]);
            }
        });
    }
    public function disetujui(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterMaking(function (Penugasan $penugasan) {
        })->afterCreating(function (Penugasan $penugasan) {
            RiwayatPengajuan::kirim([$penugasan->id]);
            $penugasan->setujui(false);
        });
    }
    public function ditolak(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];

        })->afterMaking(function (Penugasan $penugasan) {
            // ...
        })->afterCreating(function (Penugasan $penugasan) {
            RiwayatPengajuan::kirim([$penugasan->id]);
            $penugasan->tolak(false);
        });
    }
    public function dibatalkan(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterMaking(function (Penugasan $penugasan) {
            // ...
        })->afterCreating(function (Penugasan $penugasan) {
            RiwayatPengajuan::kirim([$penugasan->id]);
            $penugasan->batalkan(false);
        });
    }
    public function dicetak(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterMaking(function (Penugasan $penugasan) {
            // ...
        })->afterCreating(function (Penugasan $penugasan) {
            RiwayatPengajuan::kirim([$penugasan->id]);
            $penugasan->setujui(false);
            $penugasan->cetak(false);
        });
    }
    public function dikumpulkan(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterMaking(function (Penugasan $penugasan) {
            // ...
        })->afterCreating(function (Penugasan $penugasan) {
            RiwayatPengajuan::kirim([$penugasan->id]);
            $penugasan->setujui(false);
            $penugasan->cetak(false);
            $penugasan->kumpulkan(false);
        });
    }
    public function dicairkan(): Factory
    {
        return $this->state(function (array $attributes) {
            return [];
        })->afterMaking(function (Penugasan $penugasan) {
            // ...
        })->afterCreating(function (Penugasan $penugasan) {
            RiwayatPengajuan::kirim([$penugasan->id]);
            $penugasan->setujui(false);
            $penugasan->cetak(false);
            $penugasan->kumpulkan(false);
            $penugasan->cairkan(false);
        });
    }



}
