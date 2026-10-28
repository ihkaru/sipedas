<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static $values;

    public static function key(string $key)
    {
        self::$values = self::$values ?? self::get();
        $item = self::$values->where('key', $key)->first();
        if ($item) {
            return $item;
        }

        $fallback = self::getFallbackValue($key);
        if ($fallback !== null) {
            return new self(['key' => $key, 'nilai' => $fallback]);
        }

        return null;
    }

    public static function getValue(string $key, $default = null)
    {
        $item = self::key($key);

        return $item ? $item->nilai : $default;
    }

    public static function getFallbackValue(string $key)
    {
        return match ($key) {
            'NAMA_KAKO' => config('satker.kabkot', 'KABUPATEN MEMPAWAH'),
            'NAMA_PROVINSI' => config('satker.provinsi', 'KALIMANTAN BARAT'),
            'LEVEL_SATUAN_KERJA' => strtoupper((string) config('satker.level', 'KAKO')),
            'ID_PLH_DEFAULT' => config('satker.pejabat.kepala_nip', config('satker.pejabat.nip_kepala', '196908031992111001')),
            'NIP_PPK_SATER' => config('satker.pejabat.nip_ppk', '199306062016021001'),
            'NIP_KASUBBAG_TU' => config('satker.pejabat.kasubbag_tu_nip', config('satker.pejabat.nip_kasubbag', '198008112005021004')),
            'NIP_BENDAHARA' => config('satker.pejabat.bendahara_nip', config('satker.pejabat.nip_bendahara', '198801012010121001')),
            'SURTUG_JML_HARI_BATAS_PENGEMBALIAN' => 13,
            'SURTUG_JML_HARI_MULAI_PENGINGAT_SURTUG' => 10,
            'SURTUG_JML_HARI_BATAS_PENCAIRAN' => 17,
            default => null,
        };
    }

    public static function namaSatker(bool $denganLevelAdministrasi = true)
    {
        $namaSatker = self::getValue('NAMA_KAKO', config('satker.kabkot', 'KABUPATEN MEMPAWAH'));
        if ($denganLevelAdministrasi) {
            return $namaSatker;
        }
        $res = '';
        foreach (explode(' ', (string) $namaSatker) as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $res = $res.' '.$v;
        }

        return trim($res);
    }
}
