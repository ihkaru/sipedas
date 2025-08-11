<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Honor extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'kegiatan_manmit_id',
        'jabatan',
        'jenis_honor',
        'satuan_honor',
        'harga_per_satuan',
        'tanggal_akhir_kegiatan',
        'tanggal_pembayaran_maksimal', // Tambahkan ke fillable
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2', // Casting ke decimal
        'tanggal_akhir_kegiatan' => 'date',
        'tanggal_pembayaran_maksimal' => 'date',
    ];

    /**
     * Boot method untuk mendaftarkan model event.
     */
    protected static function boot()
    {
        parent::boot();

        // Event untuk auto-generate ID saat creating
        static::creating(function ($model) {
            // Generate composite ID jika belum ada
            if (empty($model->id)) {
                $model->id = $model->kegiatan_manmit_id . '-' . $model->jabatan . '-' . $model->jenis_honor;
            }
        });

        // Event untuk menghitung tanggal pembayaran maksimal
        static::saving(function ($model) {
            // Hitung tanggal pembayaran maksimal jika tanggal akhir kegiatan ada
            if ($model->tanggal_akhir_kegiatan) {
                $model->tanggal_pembayaran_maksimal = $model->tanggal_akhir_kegiatan->clone()->addDays(20);
            }
        });
    }

    /**
     * Relasi: Satu Honor hanya dimiliki oleh satu KegiatanManmit.
     */
    public function kegiatanManmit(): BelongsTo
    {
        return $this->belongsTo(KegiatanManmit::class, 'kegiatan_manmit_id');
    }

    /**
     * Accessor: Atribut virtual untuk membuat ID Batasan Honor.
     * Contoh: 'SURVEI-PPL'
     */
    protected function idBatasanHonor(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Pastikan relasi kegiatanManmit sudah dimuat untuk mengakses jenis_kegiatan
                // Gunakan null-safe operator (?) untuk menghindari error jika relasi belum ada
                if ($this->kegiatanManmit) {
                    return $this->kegiatanManmit->jenis_kegiatan . '-' . $this->jabatan;
                }
                return 'N/A';
            }
        );
    }

    /**
     * Factory method untuk membuat instance Honor dari data impor.
     *
     * @param array $data Data baris yang sudah dibersihkan dari importer.
     * @return \App\Models\Honor
     */
    public static function importHonor(array $data): Honor
    {
        // Membuat ID komposit dari data
        $compositeId = Str::upper(implode('-', [
            $data['id_kegiatan'],
            $data['jabatan'],
            $data['jenis_honor']
        ]));

        // Membuat instance baru
        return new self([
            'id' => $compositeId,
            'kegiatan_manmit_id' => $data['id_kegiatan'],
            'jabatan' => $data['jabatan'],
            'jenis_honor' => $data['jenis_honor'],
            'satuan_honor' => $data['satuan_honor'],
            'harga_per_satuan' => $data['harga_per_satuan_honor'],
            'tanggal_akhir_kegiatan' => $data['tanggal_akhir_kegiatan'],
        ]);
    }
    public function alokasiHonors()
    {
        return $this->hasMany(AlokasiHonor::class, 'honor_id');
    }
}
