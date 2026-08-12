<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanPerjadin extends Model
{
    use HasFactory;

    protected $fillable = [
        'penugasan_id',
        'tanggal_laporan',
        'isi_kegiatan',
        'hasil_kegiatan',
        'foto_dokumentasi',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'isi_kegiatan' => 'array',
        'hasil_kegiatan' => 'array',
        'foto_dokumentasi' => 'array',
    ];

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }
}
