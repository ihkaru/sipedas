<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Import ini

class Mitra extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Sebuah Mitra bisa memiliki banyak record Kemitraan (satu per tahun).
     */
    public function kemitraans(): HasMany
    {
        return $this->hasMany(Kemitraan::class);
    }
    public function alokasiHonors()
    {
        return $this->hasMany(AlokasiHonor::class);
    }
    /**
     * Accessor untuk mendapatkan nama Kabupaten Domisili, dioptimalkan dengan indeks.
     */
    protected function kabupatenName(): Attribute
    {
        return Attribute::make(get: function () {
            if (!$this->alamat_prov || !$this->alamat_kab) return 'N/A';

            $kabId = $this->alamat_prov . $this->alamat_kab;
            $indexedSls = MasterSls::getIndexedSls()['by_kab'];

            return $indexedSls[$kabId]->kabkot ?? 'N/A';
        });
    }

    /**
     * Accessor untuk mendapatkan nama Kecamatan Domisili, dioptimalkan dengan indeks.
     */
    protected function kecamatanName(): Attribute
    {
        return Attribute::make(get: function () {
            if (!$this->alamat_prov || !$this->alamat_kab || !$this->alamat_kec) return 'N/A';

            $kecId = $this->alamat_prov . $this->alamat_kab . $this->alamat_kec;
            $indexedSls = MasterSls::getIndexedSls()['by_kec'];

            return $indexedSls[$kecId]->kecamatan ?? 'N/A';
        });
    }

    /**
     * Accessor untuk mendapatkan nama Desa/Kelurahan Domisili, dioptimalkan dengan indeks.
     */
    protected function desaName(): Attribute
    {
        return Attribute::make(get: function () {
            if (!$this->alamat_prov || !$this->alamat_kab || !$this->alamat_kec || !$this->alamat_desa) return 'N/A';

            $desaId = $this->alamat_prov . $this->alamat_kab . $this->alamat_kec . $this->alamat_desa;
            $indexedSls = MasterSls::getIndexedSls()['by_desa'];

            return $indexedSls[$desaId]->desa_kel ?? 'N/A';
        });
    }

    /**
     * Accessor untuk status kemitraan (TIDAK BERUBAH)
     */
    protected function kemitraansStatus(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->kemitraans->isEmpty()) {
                return '<span class="fi-badge fi-color-gray" style="--c-50:var(--gray-50); --c-400:var(--gray-400); --c-600:var(--gray-600);">Belum Ada</span>';
            }
            return $this->kemitraans->map(function ($kemitraan) {
                $color = match ($kemitraan->status) {
                    'AKTIF' => 'success',
                    'TIDAK_AKTIF' => 'warning',
                    'BLACKLISTED' => 'danger',
                    default => 'gray'
                };
                return sprintf('<span class="fi-badge fi-color-%s" style="--c-50:var(--%s-50); --c-400:var(--%s-400); --c-600:var(--%s-600);">%s: %s</span>', $color, $color, $color, $color, $kemitraan->tahun, $kemitraan->status);
            })->implode(' ');
        });
    }
}
