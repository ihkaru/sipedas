<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AlokasiHonorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $tahun = $this->tanggal_mulai_perjanjian ? Carbon::parse($this->tanggal_mulai_perjanjian)->year : null;
        $bulan = $this->tanggal_mulai_perjanjian ? Carbon::parse($this->tanggal_mulai_perjanjian)->month : null;
        $kegiatanId = $this->honor?->kegiatan_manmit_id;

        return [
            'id' => $this->id,
            'target' => (float)$this->target_per_satuan_honor,
            'total_honor' => (float)$this->total_honor,
            'periode' => [
                'mulai' => $this->tanggal_mulai_perjanjian?->format('Y-m-d'),
                'akhir' => $this->tanggal_akhir_perjanjian?->format('Y-m-d'),
                'tgl_ttd_spk' => $this->tanggal_penanda_tanganan_spk_oleh_petugas?->format('Y-m-d'),
            ],
            'mitra' => [
                'id' => $this->mitra?->id,
                'id_sobat' => $this->mitra?->id_sobat,
                'nama' => $this->mitra?->nama_1,
            ],
            'honor' => [
                'id' => $this->honor?->id,
                'jabatan' => $this->honor?->jabatan,
                'jenis_honor' => $this->honor?->jenis_honor,
                'satuan' => $this->honor?->satuan_honor,
                'harga_per_satuan' => (float)($this->honor?->harga_per_satuan ?? 0),
                'tanggal_akhir_kegiatan' => $this->honor?->tanggal_akhir_kegiatan,
                'kegiatan' => [
                    'id' => $this->honor?->kegiatanManmit?->id,
                    'nama' => $this->honor?->kegiatanManmit?->nama,
                    'jenis_kegiatan' => $this->honor?->kegiatanManmit?->jenis_kegiatan,
                ],
            ],
            'kontrak' => $this->surat_perjanjian_kerja_id ? [
                'id' => $this->surat_perjanjian_kerja_id,
                'nomor_surat' => $this->kontrak?->nomor_surat_perjanjian_kerja,
                'tanggal_nomor' => $this->kontrak?->tanggal_nomor,
                'url_cetak' => url("/cetak/kontrak?tahun={$tahun}&bulan={$bulan}&mitra_id={$this->mitra_id}"),
            ] : null,
            'bast' => $this->surat_bast_id ? [
                'id' => $this->surat_bast_id,
                'nomor_surat' => $this->bast?->nomor_surat_bast,
                'tanggal_nomor' => $this->bast?->tanggal_nomor,
                'url_cetak' => url("/cetak/bast?tahun={$tahun}&bulan={$bulan}&id_kegiatan_manmit={$kegiatanId}&mitra_id={$this->mitra_id}"),
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
