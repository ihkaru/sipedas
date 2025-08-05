<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class AlokasiHonor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'target_per_satuan_honor' => 'decimal:2',
        'total_honor' => 'decimal:2',
        'tanggal_mulai_perjanjian' => 'date',
        'tanggal_akhir_perjanjian' => 'date',
    ];

    /**
     * Relasi ke Mitra yang dialokasikan.
     */
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class);
    }

    /**
     * Relasi ke jenis Honor yang dialokasikan.
     */
    public function honor(): BelongsTo
    {
        return $this->belongsTo(Honor::class, 'honor_id');
    }

    /**
     * Relasi ke nomor surat kontrak (Perjanjian Kerja).
     */
    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(NomorSurat::class, 'surat_perjanjian_kerja_id');
    }

    /**
     * Relasi ke nomor surat BAST.
     */
    public function bast(): BelongsTo
    {
        return $this->belongsTo(NomorSurat::class, 'surat_bast_id');
    }

    /**
     * Relasi shortcut untuk mendapatkan Kegiatan Manmit melalui Honor.
     * AlokasiHonor -> Honor -> KegiatanManmit
     */
    public function kegiatanManmit(): HasOneThrough
    {
        return $this->hasOneThrough(
            KegiatanManmit::class,
            Honor::class,
            'id', // Foreign key on honors table...
            'id', // Foreign key on kegiatan_manmits table...
            'honor_id', // Local key on alokasi_honors table...
            'kegiatan_manmit_id' // Local key on honors table...
        );
    }
}
