<?php

namespace App\Http\Resources\Api;

use App\Supports\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenugasanApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusKey = $this->riwayatPengajuan?->status ?? Constants::STATUS_PENGAJUAN_DIKIRIM;
        $statusLabel = Constants::STATUS_PENGAJUAN_OPTIONS[$statusKey] ?? $statusKey;

        $isPegawai = !empty($this->nip);
        $personil = [
            'jenis' => $isPegawai ? 'PEGAWAI' : 'MITRA',
            'id' => $isPegawai ? $this->nip : $this->id_sobat,
            'nama' => $isPegawai ? ($this->pegawai?->nama ?? $this->nip) : ($this->mitra?->nama_1 ?? $this->id_sobat),
            'jabatan' => $isPegawai ? ($this->pegawai?->jabatan ?? 'Pegawai') : Constants::JABATAN_MITRA,
        ];

        $suratTugasNomor = $this->suratTugas?->nomor_surat_tugas;
        $suratPerjadinNomor = $this->suratPerjadin?->nomor_surat_perjadin;

        return [
            'id' => $this->id,
            'grup_id' => $this->grup_id,
            'jenis_surat_tugas' => $this->jenis_surat_tugas,
            'jenis_surat_tugas_label' => Constants::JENIS_SURAT_TUGAS_OPTIONS[$this->jenis_surat_tugas] ?? $this->jenis_surat_tugas,
            'jenis_peserta' => $this->jenis_peserta,
            'status' => $statusKey,
            'status_label' => $statusLabel,
            'catatan_revisi' => $this->riwayatPengajuan?->catatan_butuh_perbaikan,
            'last_status_timestamp' => $this->riwayatPengajuan?->last_status_timestamp,
            'personil' => $personil,
            'kegiatan' => [
                'id' => $this->kegiatan_id,
                'nama' => $this->kegiatan?->nama,
            ],
            'periode' => [
                'tgl_mulai_tugas' => $this->tgl_mulai_tugas ? substr((string) $this->tgl_mulai_tugas, 0, 10) : null,
                'tgl_akhir_tugas' => $this->tgl_akhir_tugas ? substr((string) $this->tgl_akhir_tugas, 0, 10) : null,
                'tbh_hari_jalan_awal' => (int) ($this->tbh_hari_jalan_awal ?? 0),
                'tbh_hari_jalan_akhir' => (int) ($this->tbh_hari_jalan_akhir ?? 0),
                'tgl_pengajuan_tugas' => $this->tgl_pengajuan_tugas ? substr((string) $this->tgl_pengajuan_tugas, 0, 10) : null,
            ],
            'lokasi' => [
                'level' => $this->level_tujuan_penugasan,
                'level_label' => Constants::LEVEL_PENUGASAN_OPTIONS[$this->level_tujuan_penugasan] ?? $this->level_tujuan_penugasan,
                'deskripsi' => $this->tujuan_penugasan ?? $this->nama_tempat_tujuan ?? '-',
                'nama_tempat_tujuan' => $this->nama_tempat_tujuan,
            ],
            'transportasi' => $this->transportasi,
            'pengaju' => [
                'nip' => $this->nip_pengaju,
                'nama' => $this->pengaju?->nama ?? $this->nip_pengaju,
            ],
            'penyetuju' => [
                'nip' => $this->plh_id,
                'nama' => $this->plh?->nama ?? $this->plh_id,
            ],
            'dokumen' => [
                'surat_tugas' => [
                    'id' => $this->surat_tugas_id,
                    'nomor' => $suratTugasNomor,
                    'tanggal_nomor' => $this->suratTugas?->tanggal_nomor,
                    'url_cetak' => url("/cetak/penugasan/{$this->id}"),
                    'url_cetak_bersama' => url("/cetak/penugasan-bersama/{$this->id}"),
                ],
                'surat_perjadin' => $this->jenis_surat_tugas !== Constants::NON_SPPD ? [
                    'id' => $this->surat_perjadin_id,
                    'nomor' => $suratPerjadinNomor,
                    'tanggal_nomor' => $this->suratPerjadin?->tanggal_nomor,
                    'url_cetak' => url("/cetak/penugasan/{$this->id}"),
                ] : null,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
