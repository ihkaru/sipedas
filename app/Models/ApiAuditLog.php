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

            if ($this->action === 'CREATE_KEGIATAN_MANMIT') {
                $kegiatanId = $this->state_after['id'] ?? null;
                $kegiatan = KegiatanManmit::with('honors')->find($kegiatanId);
                if (!$kegiatan) {
                    throw new \RuntimeException("Kegiatan Manmit '{$kegiatanId}' tidak ditemukan untuk di-rollback.");
                }

                if ($kegiatan->alokasiHonors()->exists()) {
                    throw new \RuntimeException("Tidak dapat me-rollback pembuatan Kegiatan '{$kegiatanId}' karena sudah memiliki alokasi mitra aktif.");
                }

                $kegiatan->honors()->delete();
                $kegiatan->delete();

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pendaftaran master kegiatan manmit via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Master Kegiatan Manmit '{$kegiatanId}' (dan honor terkait) berhasil dihapus (rollback penambahan data).",
                ];
            }

            if ($this->action === 'DELETE_KEGIATAN_MANMIT') {
                $kegiatanData = $this->state_before['kegiatan'] ?? null;
                if (!$kegiatanData) {
                    throw new \RuntimeException("Data kegiatan sebelumnya tidak ditemukan dalam log audit.");
                }

                $kegiatanId = $kegiatanData['id'];
                if (KegiatanManmit::where('id', $kegiatanId)->exists()) {
                    throw new \RuntimeException("Kegiatan dengan ID '{$kegiatanId}' sudah ada di sistem.");
                }

                unset($kegiatanData['created_at'], $kegiatanData['updated_at']);
                KegiatanManmit::create($kegiatanData);

                $honorsData = $this->state_before['honors'] ?? [];
                foreach ($honorsData as $hData) {
                    unset($hData['created_at'], $hData['updated_at']);
                    Honor::create($hData);
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback penghapusan kegiatan manmit via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Master Kegiatan Manmit '{$kegiatanId}' beserta " . count($honorsData) . " pos honor berhasil dipulihkan.",
                ];
            }

            if ($this->action === 'CREATE_HONOR') {
                $honorId = $this->state_after['id'] ?? null;
                $honor = Honor::find($honorId);
                if (!$honor) {
                    throw new \RuntimeException("Honor '{$honorId}' tidak ditemukan untuk di-rollback.");
                }

                if ($honor->alokasiHonors()->exists()) {
                    throw new \RuntimeException("Tidak dapat me-rollback pembuatan Honor '{$honorId}' karena sudah memiliki alokasi mitra.");
                }

                $honor->delete();

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback penambahan master honor via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Master Pos Honor '{$honorId}' berhasil dihapus (rollback penambahan data).",
                ];
            }

            if ($this->action === 'DELETE_HONOR') {
                $honorData = $this->state_before;
                if (!$honorData) {
                    throw new \RuntimeException("Data honor sebelumnya tidak ditemukan dalam log audit.");
                }

                $honorId = $honorData['id'];
                if (Honor::where('id', $honorId)->exists()) {
                    throw new \RuntimeException("Honor dengan ID '{$honorId}' sudah ada di sistem.");
                }

                unset($honorData['created_at'], $honorData['updated_at']);
                Honor::create($honorData);

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback penghapusan master honor via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Master Pos Honor '{$honorId}' berhasil dipulihkan kembali.",
                ];
            }

            if ($this->action === 'UPDATE_ALOKASI') {
                $alokasiId = $this->target_id;
                $alokasi = AlokasiHonor::find($alokasiId);
                if (!$alokasi) {
                    throw new \RuntimeException("Alokasi honor #{$alokasiId} tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;
                $columns = ['target_per_satuan_honor', 'total_honor', 'honor_id', 'status', 'tanggal_mulai_perjanjian', 'tanggal_akhir_perjanjian', 'tanggal_penanda_tanganan_spk_oleh_petugas'];
                $restoreData = [];
                foreach ($columns as $col) {
                    if (array_key_exists($col, $stateBefore)) {
                        $restoreData[$col] = $stateBefore[$col];
                    }
                }

                $alokasi->update($restoreData);

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan alokasi honor via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Alokasi honor #{$alokasiId} berhasil dikembalikan ke kondisi sebelumnya.",
                ];
            }

            if ($this->action === 'CREATE_MITRA') {
                $mitraId = $this->target_id;
                $mitra = Mitra::find($mitraId);
                if (!$mitra) {
                    throw new \RuntimeException("Mitra #{$mitraId} tidak ditemukan untuk di-rollback.");
                }

                if ($mitra->alokasiHonors()->exists()) {
                    throw new \RuntimeException("Tidak dapat me-rollback pembuatan Mitra #{$mitraId} karena sudah memiliki alokasi honor.");
                }

                $mitra->kemitraans()->delete();
                $mitra->delete();

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pendaftaran mitra baru via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Mitra #{$mitraId} ({$mitra->nama_1}) berhasil dihapus (rollback pendaftaran).",
                ];
            }

            if ($this->action === 'DELETE_MITRA') {
                $mitraData = $this->state_before['mitra'] ?? null;
                if (!$mitraData) {
                    throw new \RuntimeException("Data mitra sebelumnya tidak ditemukan dalam log audit.");
                }

                $mitraId = $mitraData['id'];
                if (Mitra::where('id', $mitraId)->exists()) {
                    throw new \RuntimeException("Mitra dengan ID '{$mitraId}' sudah ada di sistem.");
                }

                unset($mitraData['created_at'], $mitraData['updated_at']);
                Mitra::create($mitraData);

                $kemitraans = $this->state_before['kemitraans'] ?? [];
                foreach ($kemitraans as $kData) {
                    unset($kData['created_at'], $kData['updated_at']);
                    Kemitraan::create($kData);
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback penghapusan data mitra via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Data Mitra #{$mitraId} ({$mitraData['nama_1']}) berhasil dipulihkan kembali.",
                ];
            }

            if ($this->action === 'DELETE_PEGAWAI') {
                $pegawaiData = $this->state_before;
                if (!$pegawaiData) {
                    throw new \RuntimeException("Data pegawai sebelumnya tidak ditemukan dalam log audit.");
                }

                $nip = $pegawaiData['nip'] ?? null;
                if (Pegawai::where('nip', $nip)->exists()) {
                    throw new \RuntimeException("Pegawai dengan NIP '{$nip}' sudah ada di sistem.");
                }

                unset($pegawaiData['created_at'], $pegawaiData['updated_at']);
                Pegawai::create($pegawaiData);

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback penghapusan pegawai via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Data Pegawai '{$pegawaiData['nama']}' (NIP: {$nip}) berhasil dipulihkan kembali.",
                ];
            }

            if ($this->action === 'UPDATE_KONTRAK') {
                $id = $this->target_id;
                $kontrak = NomorSurat::find($id);
                if (!$kontrak) {
                    throw new \RuntimeException("Dokumen Kontrak #{$id} tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;
                $updates = [];
                if (isset($stateBefore['tanggal_nomor'])) {
                    $updates['tanggal_nomor'] = $stateBefore['tanggal_nomor'];
                    $updates['tahun'] = $stateBefore['tahun'] ?? \Illuminate\Support\Carbon::parse($stateBefore['tanggal_nomor'])->year;
                }
                if (isset($stateBefore['nomor'])) {
                    $updates['nomor'] = $stateBefore['nomor'];
                }

                $kontrak->update($updates);

                if (isset($updates['tanggal_nomor'])) {
                    AlokasiHonor::where('surat_perjanjian_kerja_id', $kontrak->id)
                        ->update(['tanggal_penanda_tanganan_spk_oleh_petugas' => $updates['tanggal_nomor']]);
                }

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan dokumen kontrak via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Dokumen Kontrak #{$id} berhasil dikembalikan ke kondisi sebelumnya.",
                ];
            }

            if ($this->action === 'UPDATE_BAST') {
                $id = $this->target_id;
                $bast = NomorSurat::find($id);
                if (!$bast) {
                    throw new \RuntimeException("Dokumen BAST #{$id} tidak ditemukan untuk di-rollback.");
                }

                $stateBefore = $this->state_before;
                $updates = [];
                if (isset($stateBefore['tanggal_nomor'])) {
                    $updates['tanggal_nomor'] = $stateBefore['tanggal_nomor'];
                    $updates['tahun'] = $stateBefore['tahun'] ?? \Illuminate\Support\Carbon::parse($stateBefore['tanggal_nomor'])->year;
                }
                if (isset($stateBefore['nomor'])) {
                    $updates['nomor'] = $stateBefore['nomor'];
                }

                $bast->update($updates);

                $this->update([
                    'is_rolled_back' => true,
                    'rolled_back_at' => now(),
                    'rolled_back_by_user_id' => $byUser?->id,
                    'rollback_reason' => $reason ?? 'Rollback pembaruan dokumen BAST via audit log',
                ]);

                return [
                    'success' => true,
                    'message' => "Dokumen BAST #{$id} berhasil dikembalikan ke kondisi sebelumnya.",
                ];
            }

            throw new \RuntimeException("Handler rollback belum diimplementasikan untuk aksi '{$this->action}'.");
        });
    }
}
