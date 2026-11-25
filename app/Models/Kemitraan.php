<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $mitra_id
 * @property int $tahun
 * @property string $status
 * @property string $status_rekrutmen
 * @property string|null $catatan
 */
class Kemitraan extends Model
{
    use HasFactory;

    public const STATUS_AKTIF = 'AKTIF';
    public const STATUS_TIDAK_AKTIF = 'TIDAK_AKTIF';
    public const STATUS_BLACKLISTED = 'BLACKLISTED';

    public const REKRUTMEN_TERVERIFIKASI = 'TERVERIFIKASI';
    public const REKRUTMEN_BELUM = 'BELUM_REKRUTMEN';
    public const REKRUTMEN_TIDAK_LULUS = 'TIDAK_LULUS';

    protected $fillable = [
        'mitra_id',
        'tahun',
        'status',
        'status_rekrutmen',
        'catatan',
    ];

    /**
     * Daftar opsi status rekrutmen untuk UI dan validasi.
     *
     * @return array<string, string>
     */
    public static function statusRekrutmenOptions(): array
    {
        return [
            self::REKRUTMEN_TERVERIFIKASI => 'Terverifikasi (Lulus Rekrutmen)',
            self::REKRUTMEN_BELUM => 'Belum Lulus Rekrutmen (Pra-Rekrutmen)',
            self::REKRUTMEN_TIDAK_LULUS => 'Tidak Lulus Rekrutmen',
        ];
    }

    public function isBelumRekrutmen(): bool
    {
        return ($this->status_rekrutmen ?? self::REKRUTMEN_TERVERIFIKASI) === self::REKRUTMEN_BELUM;
    }

    /**
     * Sebuah Kemitraan dimiliki oleh satu Mitra.
     */
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class);
    }
}
