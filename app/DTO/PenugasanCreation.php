<?php

namespace App\DTO;

class PenugasanCreation
{
    public array $nips;
    public string $kegiatanId;
    public string $tglMulaiTugas;
    public string $tglAkhirTugas;
    public int $tbhHariJalanAwal;
    public int $tbhHariJalanAkhir;
    public string $provId;
    public string|null $kabkotId;
    public string|null $kecamatanId;
    public string|null $desaKelId;
    public string $jenisSuratTugas;
    public string $suratTugasId;
    public string $plhId;
    public string $transportasi;

    public static function create(array $formData){
        $penugasanDto = new PenugasanCreation();
        $penugasanDto->nips = $formData["nips"];
        $penugasanDto->jenisSuratTugas = $formData["jenis_surat_tugas"];
        $penugasanDto->kegiatanId = $formData["kegiatan_id"];
        $penugasanDto->tglMulaiTugas = $formData["tgl_mulai_tugas"];
        $penugasanDto->tglAkhirTugas = $formData["tgl_selesai_tugas"];
        $penugasanDto->tbhHariJalanAwal = $formData["tbh_awal_perjalan"];
        $penugasanDto->tbhHariJalanAkhir = $formData["tbh_akhir_perjalan"];
        $penugasanDto->provId = $formData["prov_id"];
        $penugasanDto->kabkotId = $formData["kabkot_id"];
        $penugasanDto->kecamatanId = $formData["kec_id"];
        $penugasanDto->desaKelId = $formData["desa_kel_id"];
        $penugasanDto->transportasi = $formData["transportasi"];
        return $penugasanDto;
    }
}
