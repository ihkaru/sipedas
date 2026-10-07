<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $nip
 * @property string|null $nama
 * @property string|null $pangkat
 * @property string|null $golongan
 * @property string|null $jabatan
 * @property string|null $email
 * @property string|null $unit_kerja
 * @property string|null $atasan_langsung_id
 * @property-read string $pangkat_golongan
 * @property-read bool $is_magang
 * @property-read string $label_identitas
 */
class Pegawai extends Model {
    use HasFactory;
    protected $guarded = [];
    protected $primaryKey = "nip";
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array {
        return [
            'nip' => 'string',
        ];
    }

    public function user() {
        return $this->hasOne(User::class, "email", "email");
    }

    public function penugasans() {
        return $this->hasMany(Penugasan::class, "nip", "nip");
    }

    public function atasanLangsung() {
        return $this->hasOne(Pegawai::class, "nip", "atasan_langsung_id");
    }
    protected function pangkatGolongan(): Attribute {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $pangkat = $attributes['pangkat'] ?? null;
                $golongan = $attributes['golongan'] ?? null;

                // Jika Golongan V (PPPK/Operator)
                if ($golongan === 'V') {
                    return Constants::PANGKAT_OPTIONS['V'];
                }

                // Jika tidak memiliki pangkat/golongan atau berupa tanda strip '-'
                if (!$pangkat || $pangkat === '-' || !$golongan || $golongan === '-') {
                    return '-';
                }

                $pangkatText = Constants::PANGKAT_OPTIONS[$pangkat] ?? $pangkat;
                $golonganText = ($pangkat === Constants::PANGKAT_IV)
                    ? (Constants::GOLONGAN_IV_OPTIONS[$golongan] ?? '')
                    : (Constants::GOLONGAN_I_III_OPTIONS[$golongan] ?? '');

                $subText = $golonganText !== '' ? " {$golonganText} " : ' ';
                return trim("{$pangkatText}{$subText}(" . strtoupper($pangkat) . "/{$golongan})");
            },
        );
    }

    protected function isMagang(): Attribute {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $pangkat = trim((string) ($attributes['pangkat'] ?? ''));
                $golongan = trim((string) ($attributes['golongan'] ?? ''));

                return ($pangkat === '-' || $pangkat === '') && ($golongan === '-' || $golongan === '');
            },
        );
    }

    protected function labelIdentitas(): Attribute {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->is_magang ? 'NIK' : 'NIP',
        );
    }

    public static function getPpkByDate($date = null): ?self
    {
        // Delegasi ke RiwayatPpk — tidak ada lagi tanggal/NIP hardcoded di sini.
        // Untuk mengganti PPK, cukup tambah baris baru di menu Sistem > Riwayat PPK.
        return RiwayatPpk::getPpkPadaTanggal($date);
    }
}
