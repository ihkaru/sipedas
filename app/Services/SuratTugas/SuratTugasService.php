<?php

namespace App\Services\SuratTugas;

use App\Models\NomorSurat;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\Plh;
use App\Models\RiwayatPengajuan;
use App\Models\TujuanSuratTugas;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuratTugasService
{
    protected SuratTugasValidationService $validator;

    public function __construct(SuratTugasValidationService $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Dapatkan instance validator.
     */
    public function validator(): SuratTugasValidationService
    {
        return $this->validator;
    }

    /**
     * Validasi input penugasan.
     *
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        return $this->validator->validate($data);
    }

    /**
     * Pre-flight dry-run check kelayakan penugasan (Agent-Native).
     */
    public function check(array $data): array
    {
        return $this->validator->checkEligibility($data);
    }

    /**
     * Buat penugasan baru (Tunggal atau Tim) secara atomik.
     * Mengembalikan array dari instance Penugasan yang berhasil dibuat.
     *
     * @throws ValidationException
     */
    public function create(array $rawData, ?string $pengajuNip = null): array
    {
        $data = $this->validator->validate($rawData);

        return DB::transaction(function () use ($data, $pengajuNip) {
            // Tentukan NIP Pengaju
            $effectivePengajuNip = $pengajuNip 
                ?? auth()?->user()?->pegawai?->nip 
                ?? $data['nip_pengaju'] 
                ?? (!empty($data['nips']) ? $data['nips'][0] : null)
                ?? Pengaturan::key('ID_PLH_DEFAULT')?->nilai
                ?? '198008112005021004';

            $tglMulai = Carbon::parse($data['tgl_mulai_tugas'])->toDateTimeString();
            $tglAkhir = Carbon::parse($data['tgl_akhir_tugas'])->toDateTimeString();

            // Selesaikan Approver / Plh
            $pegawaiPlh = Plh::getApprover($data['nips'] ?? null, $tglMulai, true);
            $plhNip = $pegawaiPlh?->nip ?? Pengaturan::key('ID_PLH_DEFAULT')?->nilai;

            $grupId = Penugasan::getGrupId();
            $createdPenugasans = [];

            // 1. Buat Penugasan untuk semua Pegawai
            foreach ($data['nips'] as $nip) {
                $tglPengajuan = $data['tgl_pengajuan_tugas'] ?? Penugasan::getNearestPemberiTugasDate(
                    now() >= Carbon::parse($data['tgl_mulai_tugas']) ? Carbon::parse($data['tgl_mulai_tugas'])->toDateString() : now(),
                    Carbon::parse($data['tgl_mulai_tugas'])->toDateString(),
                    $data['nips']
                );

                $pengajuan = Penugasan::create([
                    'nip' => $nip,
                    'kegiatan_id' => $data['kegiatan_id'],
                    'nip_pengaju' => $effectivePengajuNip,
                    'level_tujuan_penugasan' => $data['level_tujuan_penugasan'],
                    'nama_tempat_tujuan' => $data['nama_tempat_tujuan'] ?? null,
                    'tgl_mulai_tugas' => $tglMulai,
                    'tgl_akhir_tugas' => $tglAkhir,
                    'tbh_hari_jalan_awal' => $data['tbh_hari_jalan_awal'] ?? null,
                    'tbh_hari_jalan_akhir' => $data['tbh_hari_jalan_akhir'] ?? null,
                    'tgl_pengajuan_tugas' => $tglPengajuan,
                    'jenis_peserta' => $data['jenis_peserta'],
                    'grup_id' => $grupId,
                    'jenis_surat_tugas' => $data['jenis_surat_tugas'],
                    'plh_id' => $plhNip,
                    'transportasi' => $data['transportasi'] ?? null,
                ]);

                RiwayatPengajuan::kirim([$pengajuan->id]);
                TujuanSuratTugas::ajukan($data, $pengajuan->id);

                // Jika Non-SPPD, langsung otomatis disetujui (auto-approved)
                if ($pengajuan->jenis_surat_tugas === Constants::NON_SPPD) {
                    $pengajuan->setujui(checkRole: false);
                }

                $createdPenugasans[] = $pengajuan->fresh(['riwayatPengajuan', 'tujuanSuratTugas', 'suratTugas', 'suratPerjadin', 'pegawai', 'kegiatan']);
            }

            // 2. Buat Penugasan untuk semua Mitra
            foreach ($data['mitras'] as $idSobat) {
                $tglPengajuan = $data['tgl_pengajuan_tugas'] ?? Penugasan::getNearestPemberiTugasDate(
                    now() >= Carbon::parse($data['tgl_mulai_tugas']) ? Carbon::parse($data['tgl_mulai_tugas'])->toDateString() : now(),
                    Carbon::parse($data['tgl_mulai_tugas'])->toDateString(),
                    $data['nips']
                );

                $pengajuan = Penugasan::create([
                    'id_sobat' => $idSobat,
                    'kegiatan_id' => $data['kegiatan_id'],
                    'nip_pengaju' => $effectivePengajuNip,
                    'level_tujuan_penugasan' => $data['level_tujuan_penugasan'],
                    'nama_tempat_tujuan' => $data['nama_tempat_tujuan'] ?? null,
                    'tgl_mulai_tugas' => $tglMulai,
                    'tgl_akhir_tugas' => $tglAkhir,
                    'tbh_hari_jalan_awal' => $data['tbh_hari_jalan_awal'] ?? null,
                    'tbh_hari_jalan_akhir' => $data['tbh_hari_jalan_akhir'] ?? null,
                    'tgl_pengajuan_tugas' => $tglPengajuan,
                    'jenis_peserta' => $data['jenis_peserta'],
                    'grup_id' => $grupId,
                    'jenis_surat_tugas' => $data['jenis_surat_tugas'],
                    'plh_id' => $plhNip,
                    'transportasi' => $data['transportasi'] ?? null,
                ]);

                RiwayatPengajuan::kirim([$pengajuan->id]);
                TujuanSuratTugas::ajukan($data, $pengajuan->id);

                // Jika Non-SPPD, langsung otomatis disetujui (auto-approved)
                if ($pengajuan->jenis_surat_tugas === Constants::NON_SPPD) {
                    $pengajuan->setujui(checkRole: false);
                }

                $createdPenugasans[] = $pengajuan->fresh(['riwayatPengajuan', 'tujuanSuratTugas', 'suratTugas', 'suratPerjadin', 'mitra', 'kegiatan']);
            }

            return $createdPenugasans;
        });
    }

    /**
     * Perbarui data penugasan yang masih dalam draf, dikirim, atau perlu revisi.
     */
    public function update(Penugasan $penugasan, array $updateData): Penugasan
    {
        $status = $penugasan->riwayatPengajuan?->status;
        $editableStatuses = [
            Constants::STATUS_PENGAJUAN_DIKIRIM,
            Constants::STATUS_PENGAJUAN_PERLU_REVISI,
        ];

        if (!in_array($status, $editableStatuses)) {
            throw new \RuntimeException("Penugasan #{$penugasan->id} dengan status '{$status}' tidak dapat diubah lagi secara langsung.");
        }

        return DB::transaction(function () use ($penugasan, $updateData) {
            $fillable = [
                'kegiatan_id',
                'level_tujuan_penugasan',
                'nama_tempat_tujuan',
                'tgl_mulai_tugas',
                'tgl_akhir_tugas',
                'tbh_hari_jalan_awal',
                'tbh_hari_jalan_akhir',
                'tgl_pengajuan_tugas',
                'transportasi',
                'jenis_surat_tugas',
            ];

            $updates = array_intersect_key($updateData, array_flip($fillable));

            if (isset($updates['tgl_mulai_tugas'])) {
                $updates['tgl_mulai_tugas'] = Carbon::parse($updates['tgl_mulai_tugas'])->toDateTimeString();
            }
            if (isset($updates['tgl_akhir_tugas'])) {
                $updates['tgl_akhir_tugas'] = Carbon::parse($updates['tgl_akhir_tugas'])->toDateTimeString();
            }
            if (isset($updates['tgl_pengajuan_tugas'])) {
                $updates['tgl_pengajuan_tugas'] = Carbon::parse($updates['tgl_pengajuan_tugas'])->toDateTimeString();
            }

            $penugasan->update($updates);

            // Perbarui tujuan jika ada field lokasi yang diubah
            if (isset($updateData['level_tujuan_penugasan']) || isset($updateData['nama_tempat_tujuan']) || isset($updateData['kabkot_ids']) || isset($updateData['kecamatan_ids']) || isset($updateData['desa_kel_ids'])) {
                TujuanSuratTugas::where('penugasan_id', $penugasan->id)->delete();
                $mergedLocationData = array_merge([
                    'level_tujuan_penugasan' => $penugasan->level_tujuan_penugasan,
                    'nama_tempat_tujuan' => $penugasan->nama_tempat_tujuan,
                ], $updateData);
                $normalized = $this->validator->normalize($mergedLocationData);
                TujuanSuratTugas::ajukan($normalized, $penugasan->id);
            }

            return $penugasan->fresh(['riwayatPengajuan', 'tujuanSuratTugas', 'suratTugas', 'suratPerjadin', 'pegawai', 'mitra', 'kegiatan']);
        });
    }

    /**
     * Batalkan penugasan dengan pembersihan nomor surat jika tidak terbagi dengan anggota lain.
     */
    public function batalkan(Penugasan $penugasan, bool $checkRole = false): bool
    {
        return DB::transaction(function () use ($penugasan, $checkRole) {
            return (bool) $penugasan->batalkan($checkRole);
        });
    }

    /**
     * Eksekusi transisi status penugasan secara deterministik.
     */
    public function transition(Penugasan $penugasan, string $action, array $params = [], bool $checkRole = false): Penugasan
    {
        $action = strtolower(trim($action));

        DB::transaction(function () use ($penugasan, $action, $params, $checkRole) {
            switch ($action) {
                case 'setujui':
                case 'approve':
                    $modeLangsung = $params['mode_langsung'] ?? false;
                    $dataNomor = $params['nomor_manual'] ?? null;
                    $success = $penugasan->setujui($checkRole, $modeLangsung, $dataNomor);
                    if (!$success) {
                        throw new \RuntimeException("Gagal menyetujui penugasan #{$penugasan->id}. Periksa hak akses dan status saat ini.");
                    }
                    break;

                case 'tolak':
                case 'reject':
                    $success = $penugasan->tolak($checkRole);
                    if (!$success) {
                        throw new \RuntimeException("Gagal menolak penugasan #{$penugasan->id}. Periksa hak akses dan status saat ini.");
                    }
                    break;

                case 'revisi':
                case 'perlu_perbaikan':
                    $catatan = $params['catatan'] ?? $params['catatan_butuh_perbaikan'] ?? 'Perlu perbaikan data';
                    $success = Penugasan::perluPerbaikan(['id' => $penugasan->id, 'catatan_butuh_perbaikan' => $catatan], $checkRole);
                    if (!$success) {
                        throw new \RuntimeException("Gagal mengirim arahan perbaikan untuk penugasan #{$penugasan->id}.");
                    }
                    break;

                case 'ajukan_revisi':
                case 'submit_revisi':
                    $success = Penugasan::ajukanRevisi(array_merge($params, ['id' => $penugasan->id]));
                    if (!$success) {
                        throw new \RuntimeException("Gagal mengajukan revisi penugasan #{$penugasan->id}.");
                    }
                    break;

                case 'cetak':
                case 'print':
                    $success = $penugasan->cetak($checkRole);
                    if (!$success) {
                        throw new \RuntimeException("Gagal mengubah status penugasan #{$penugasan->id} menjadi Dicetak.");
                    }
                    break;

                case 'kumpulkan':
                    $success = $penugasan->kumpulkan($checkRole);
                    if (!$success) {
                        throw new \RuntimeException("Gagal mengubah status penugasan #{$penugasan->id} menjadi Dikumpulkan.");
                    }
                    break;

                case 'batalkan_pengumpulan':
                    $success = $penugasan->batalkanPengumpulan($checkRole);
                    if (!$success) {
                        throw new \RuntimeException("Gagal membatalkan pengumpulan penugasan #{$penugasan->id}.");
                    }
                    break;

                case 'cairkan':
                    $success = $penugasan->cairkan($checkRole);
                    if (!$success) {
                        throw new \RuntimeException("Gagal mengubah status penugasan #{$penugasan->id} menjadi Dicairkan.");
                    }
                    break;

                default:
                    throw new \InvalidArgumentException("Aksi transisi '{$action}' tidak dikenal. Aksi valid: setujui, tolak, revisi, ajukan_revisi, cetak, kumpulkan, batalkan_pengumpulan, cairkan.");
            }
        });

        return $penugasan->fresh(['riwayatPengajuan', 'tujuanSuratTugas', 'suratTugas', 'suratPerjadin']);
    }
}
