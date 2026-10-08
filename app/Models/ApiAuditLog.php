<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ApiAuditLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'state_before' => 'array',
        'state_after' => 'array',
        'is_reversible' => 'boolean',
        'is_rolled_back' => 'boolean',
        'rolled_back_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function rolledBackByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by_user_id');
    }

    /**
     * Eksekusi rollback perubahan yang tercatat di audit log.
     * Mengembalikan status dan pesan keberhasilan.
     */
    public function executeRollback(?User $byUser = null, ?string $reason = null): array
    {
        if (!$this->is_reversible) {
            throw new \RuntimeException("Aksi audit log #{$this->id} ({$this->action}) tidak dapat di-rollback secara logis.");
        }

        if ($this->is_rolled_back) {
            throw new \RuntimeException("Aksi audit log #{$this->id} sudah pernah di-rollback pada {$this->rolled_back_at}.");
        }

        return DB::transaction(function () use ($byUser, $reason) {
            if ($this->action === 'ALLOCATE_HONOR') {
                $alokasiId = $this->target_id;
                $alokasi = AlokasiHonor::find($alokasiId);

                if (!$alokasi) {
                    throw new \RuntimeException("Alokasi honor #{$alokasiId} tidak ditemukan atau telah dihapus sebelumnya.");
                }

                $spkId = $alokasi->surat_perjanjian_kerja_id;
                $bastId = $alokasi->surat_bast_id;

                // Hapus alokasi honor
                $alokasi->delete();

                // Bersihkan nomor surat SPK jika tidak lagi dipakai alokasi lain
                if ($spkId && !AlokasiHonor::where('surat_perjanjian_kerja_id', $spkId)->exists()) {
                    NomorSurat::where('id', $spkId)->delete();
                }

                // Bersihkan nomor surat BAST jika tidak lagi dipakai alokasi lain
                if ($bastId && !AlokasiHonor::where('surat_bast_id', $bastId)->exists()) {
                    NomorSurat::where('id', $bastId)->delete();
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback alokasi via audit trail',
                ]);

                return [
                    'success' => true,
                    'message' => "Alokasi honor #{$alokasiId} dan nomor dokumen terkait berhasil dikembalikan (di-rollback).",
                ];
            }

            if ($this->action === 'BATCH_ALLOCATE') {
                $createdIds = $this->state_after['created_ids'] ?? [];
                $deletedCount = 0;

                foreach ($createdIds as $alokasiId) {
                    $alokasi = AlokasiHonor::find($alokasiId);
                    if ($alokasi) {
                        $spkId = $alokasi->surat_perjanjian_kerja_id;
                        $bastId = $alokasi->surat_bast_id;
                        $alokasi->delete();

                        if ($spkId && !AlokasiHonor::where('surat_perjanjian_kerja_id', $spkId)->exists()) {
                            NomorSurat::where('id', $spkId)->delete();
                        }
                        if ($bastId && !AlokasiHonor::where('surat_bast_id', $bastId)->exists()) {
                            NomorSurat::where('id', $bastId)->delete();
                        }
                        $deletedCount++;
                    }
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback batch alokasi via audit trail',
                ]);

                return [
                    'success' => true,
                    'message' => "Sebanyak {$deletedCount} alokasi dari batch #{$this->id} berhasil di-rollback.",
                ];
            }

            if ($this->action === 'DELETE_ALLOCATION') {
                $stateBefore = $this->state_before;
                if (!empty($stateBefore)) {
                    // Pulihkan AlokasiHonor
                    unset($stateBefore['id']);
                    $restored = AlokasiHonor::create($stateBefore);

                    $this->update([
                        'is_rolled_back' => true,
                        'rolled_back_at' => now(),
                        'rolled_back_by_user_id' => $byUser?->id,
                        'rollback_reason' => $reason ?? 'Restorasi alokasi yang dihapus via audit trail',
                    ]);

                    return [
                        'success' => true,
                        'message' => "Alokasi honor yang sebelumnya dihapus telah dipulihkan kembali (ID baru: #{$restored->id}).",
                    ];
                }
            }

            if ($this->action === 'RENAME_KEGIATAN_ID') {
                $oldId = $this->state_before['id'];
                $currentId = $this->state_after['id'];
                $oldNama = $this->state_before['nama'];

                $currentKegiatan = KegiatanManmit::find($currentId);
                if (!$currentKegiatan) {
                    throw new \RuntimeException("Kegiatan dengan ID '{$currentId}' tidak ditemukan untuk di-rollback.");
                }

                // 1. Buat parent KegiatanManmit dengan oldId
                $kegiatanData = $currentKegiatan->getAttributes();
                $kegiatanData['id'] = $oldId;
                $kegiatanData['nama'] = $oldNama;
                unset($kegiatanData['created_at'], $kegiatanData['updated_at']);
                KegiatanManmit::create($kegiatanData);

                // 2. Clone honors kembali ke old prefix
                $honors = Honor::where('kegiatan_manmit_id', $currentId)->get();
                foreach ($honors as $honor) {
                    $currHonorId = $honor->id;
                    if (str_starts_with($currHonorId, $currentId)) {
                        $origHonorId = $oldId . substr($currHonorId, strlen($currentId));
                    } else {
                        $origHonorId = \Illuminate\Support\Str::upper($oldId . '-' . $honor->jabatan . '-' . $honor->jenis_honor);
                    }

                    $honorData = $honor->getAttributes();
                    $honorData['id'] = $origHonorId;
                    $honorData['kegiatan_manmit_id'] = $oldId;
                    unset($honorData['created_at'], $honorData['updated_at']);
                    Honor::create($honorData);

                    AlokasiHonor::where('honor_id', $currHonorId)->update(['honor_id' => $origHonorId]);
                    $honor->delete();
                }

                DB::table('kegiatans')
                    ->where('kegiatan_manmit_id', $currentId)
                    ->update(['kegiatan_manmit_id' => $oldId]);

                $currentKegiatan->delete();

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback rename ID kegiatan ke ID semula via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "ID kegiatan #{$currentId} berhasil dikembalikan ke '{$oldId}'.",
                ];
            }

            if ($this->action === 'UPDATE_MITRA') {
                $mitraId = $this->target_id;
                $mitra = Mitra::find($mitraId);
                if (!$mitra) {
                    throw new \RuntimeException("Mitra #{$mitraId} tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;

                if (isset($stateBefore['kemitraan_status'])) {
                    $tahun = $stateBefore['kemitraan_tahun'] ?? now()->year;
                    Kemitraan::updateOrCreate(
                        ['mitra_id' => $mitra->id, 'tahun' => $tahun],
                        ['status' => $stateBefore['kemitraan_status']]
                    );
                    unset($stateBefore['kemitraan_status'], $stateBefore['kemitraan_tahun']);
                }

                if (!empty($stateBefore)) {
                    $mitra->update($stateBefore);
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan data mitra via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Informasi mitra #{$mitraId} ({$mitra->nama_1}) berhasil dikembalikan ke kondisi sebelumnya.",
                ];
            }

            if ($this->action === 'UPDATE_KEGIATAN_MANMIT') {
                $id = $this->state_before['id'] ?? null;
                $kegiatan = KegiatanManmit::find($id);
                if (!$kegiatan) {
                    throw new \RuntimeException("Kegiatan Manmit '{$id}' tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;
                unset($stateBefore['id']);
                $kegiatan->update($stateBefore);

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan kegiatan manmit via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Data Kegiatan Manmit '{$id}' berhasil dikembalikan ke kondisi sebelumnya.",
                ];
            }

            if ($this->action === 'UPDATE_HONOR') {
                $id = $this->state_before['id'] ?? null;
                $honor = Honor::find($id);
                if (!$honor) {
                    throw new \RuntimeException("Honor '{$id}' tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;
                unset($stateBefore['id']);
                $honor->update($stateBefore);

                // Jika tanggal_akhir_kegiatan dikembalikan, re-propagate ke alokasi dan nomor surat
                if (isset($stateBefore['tanggal_akhir_kegiatan'])) {
                    \App\Services\HonorTanggalService::propagate($honor);
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan honor via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Master Honor '{$id}' berhasil dikembalikan ke kondisi sebelumnya dan tanggal SPK/BAST telah disinkronkan ulang.",
                ];
            }

            if ($this->action === 'CREATE_PEGAWAI') {
                $nip = $this->state_after['nip'] ?? null;
                $pegawai = Pegawai::where('nip', $nip)->first();
                if (!$pegawai) {
                    throw new \RuntimeException("Pegawai NIP '{$nip}' tidak ditemukan untuk di-rollback.");
                }

                $pegawai->delete();

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback penambahan pegawai via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Pegawai NIP '{$nip}' berhasil dihapus (rollback penambahan data).",
                ];
            }

            if ($this->action === 'UPDATE_PEGAWAI') {
                $nip = $this->state_before['nip'] ?? null;
                $pegawai = Pegawai::where('nip', $nip)->first();
                if (!$pegawai) {
                    throw new \RuntimeException("Pegawai NIP '{$nip}' tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;
                unset($stateBefore['nip']);
                $pegawai->update($stateBefore);

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan pegawai via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Data Pegawai '{$pegawai->nama}' (NIP: {$nip}) berhasil dikembalikan ke kondisi sebelumnya.",
                ];
            }

            throw new \RuntimeException("Handler rollback belum diimplementasikan untuk aksi '{$this->action}'.");
        });
    }
}
