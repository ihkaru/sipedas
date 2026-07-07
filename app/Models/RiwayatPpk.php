<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class RiwayatPpk extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tgl_mulai'   => 'date',
        'tgl_selesai' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip_ppk', 'nip');
    }

    /**
     * Cari PPK yang aktif pada tanggal tertentu.
     * Menggantikan logika hardcoded di Pegawai::getPpkByDate().
     */
    public static function getPpkPadaTanggal(Carbon|string|null $date = null): ?Pegawai
    {
        $date = $date ? Carbon::parse($date) : now();

        $riwayat = self::where('tgl_mulai', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('tgl_selesai')
                  ->orWhere('tgl_selesai', '>=', $date);
            })
            ->orderByDesc('tgl_mulai')
            ->with('pegawai')
            ->first();

        return $riwayat?->pegawai;
    }

    /**
     * Scope: hanya PPK yang sedang aktif saat ini.
     */
    public function scopeAktif($query)
    {
        return $query->where('tgl_mulai', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tgl_selesai')
                  ->orWhere('tgl_selesai', '>=', now());
            });
    }

    /**
     * Accessor: apakah record ini PPK yang aktif saat ini.
     */
    public function getIsAktifAttribute(): bool
    {
        $now = now();
        return $this->tgl_mulai <= $now &&
            ($this->tgl_selesai === null || $this->tgl_selesai >= $now);
    }
}
