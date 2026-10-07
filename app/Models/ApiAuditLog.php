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

            throw new \RuntimeException("Handler rollback belum diimplementasikan untuk aksi '{$this->action}'.");
        });
    }
}
