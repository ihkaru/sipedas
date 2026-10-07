<?php

namespace App\Models;

use App\Supports\TanggalMerah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

class AlokasiHonor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'target_per_satuan_honor' => 'decimal:2',
        'total_honor' => 'decimal:2',
        'tanggal_mulai_perjanjian' => 'date',
        'tanggal_akhir_perjanjian' => 'date',
        'tanggal_penanda_tanganan_spk_oleh_petugas' => 'date',
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

    /**
     * Boot method untuk mendaftarkan model event.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validasi kelayakan mitra sebelum disimpan
            $isSensus = $model->honor?->kegiatanManmit?->jenis_kegiatan === 'SENSUS';
            
            $validation = \App\Services\HonorService::validateMitraEligibility(
                $model->mitra_id,
                $model->tanggal_mulai_perjanjian,
                $model->tanggal_akhir_perjanjian,
                $model->total_honor,
                $isSensus,
                $model->id // Exclude self if updating
            );

            if (!$validation['eligible']) {
                throw new \Exception($validation['message']);
            }
        });
    }

    /**
     * Metode terpusat untuk mempersiapkan/menyimpan instance AlokasiHonor.
     * Didelegasikan ke HonorAllocationService untuk memastikan konsistensi validasi dan transaksi atomik.
     */
    public static function createWithRelations(string $idSobat, string $honorId, float $target): self
    {
        return \App\Services\HonorAllocationService::allocate($idSobat, $honorId, $target);
    }
}
