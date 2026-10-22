<?php

namespace App\Models;

use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $penugasan_id
 * @property string|null $nama_tempat_tujuan
 * @property string|null $prov_id
 * @property string|null $kabkot_id
 * @property string|null $kecamatan_id
 * @property string|null $desa_kel_id
 */
class TujuanSuratTugas extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function penugasan(){
        return $this->belongsTo(Penugasan::class);
    }
    public function desa(){
        return $this->belongsTo(MasterSls::class,"desa_kel_id","desa_kel_id");
    }
    public function kecamatan(){
        return $this->belongsTo(MasterSls::class,"kecamatan_id","kec_id");
    }
    public function kabkot(){
        return $this->belongsTo(MasterSls::class,"kabkot_id","kabkot_id");
    }
    public function provinsi(){
        return $this->belongsTo(MasterSls::class,"prov_id");
    }
    public static function ajukan($data,$pengajuan_id){
        $now = now();
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_NAMA_TEMPAT){
            self::create([
                "penugasan_id"=>$pengajuan_id,
                "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                "nama_tempat_tujuan" => $data["nama_tempat_tujuan"],
                "created_at"=>$now,
                "updated_at"=>$now,
            ]);
        }
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA){
            $res = [];
            foreach ($data["kabkot_ids"] as $d) {
                $res[] = [
                    "penugasan_id"=>$pengajuan_id,
                    "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                    "prov_id" => $data["prov_ids"][0],
                    "kabkot_id" => $d,
                    "created_at"=>$now,
                    "updated_at"=>$now,
                ];
            }
            self::insert($res);
        };
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_KECAMATAN){
            $res = [];
            foreach ($data["kecamatan_ids"] as $d) {
                $res[] = [
                    "penugasan_id"=>$pengajuan_id,
                    "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                    "prov_id" => $data["prov_ids"][0],
                    "kabkot_id" => $data["kabkot_ids"][0],
                    "kecamatan_id" => $d,
                    "created_at"=>$now,
                    "updated_at"=>$now,
                ];
            }
            self::insert($res);
        };
        if($data["level_tujuan_penugasan"] == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN){
            $res = [];
            foreach ($data["desa_kel_ids"] as $d) {
                $res[] = [
                    "penugasan_id"=>$pengajuan_id,
                    "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                    "prov_id" => $data["prov_ids"][0],
                    "kabkot_id" => $data["kabkot_ids"][0],
                    "kecamatan_id" => $data["kecamatan_ids"][0],
                    "desa_kel_id" => $d,
                    "created_at"=>$now,
                    "updated_at"=>$now,
                ];
            }
            self::insert($res);
        };


    }
    public static function combinerTujuan($data, $masterSls = null){
        static $cachedByKabkot = null;
        static $cachedByKec = null;
        static $cachedByDesa = null;

        $res = "";
        foreach ($data as $d) {
            $level = is_array($d) ? ($d['level_tujuan_penugasan'] ?? null) : ($d->level_tujuan_penugasan ?? null);

            if ($level == Constants::LEVEL_PENUGASAN_NAMA_TEMPAT) {
                $nama = is_array($d) ? ($d['nama_tempat_tujuan'] ?? '') : ($d->nama_tempat_tujuan ?? '');
                return ucwords(strtolower((string) $nama));
            }

            if ($level == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA) {
                $kabkotId = is_array($d) ? ($d['kabkot_id'] ?? null) : ($d->kabkot_id ?? null);
                $kabkotName = null;
                if (!is_array($d) && $d->relationLoaded('kabkot') && $d->kabkot) {
                    $kabkotName = $d->kabkot->kabkot;
                } elseif ($masterSls) {
                    if ($cachedByKabkot === null) {
                        $cachedByKabkot = $masterSls->keyBy('kabkot_id');
                    }
                    $kabkotName = $cachedByKabkot[$kabkotId]->kabkot ?? null;
                }
                if ($kabkotName) {
                    $res .= ", " . ucwords(strtolower((string) $kabkotName));
                }
            } elseif ($level == Constants::LEVEL_PENUGASAN_KECAMATAN) {
                $kecId = is_array($d) ? ($d['kecamatan_id'] ?? null) : ($d->kecamatan_id ?? null);
                $kecName = null;
                if (!is_array($d) && $d->relationLoaded('kecamatan') && $d->kecamatan) {
                    $kecName = $d->kecamatan->kecamatan;
                } elseif ($masterSls) {
                    if ($cachedByKec === null) {
                        $cachedByKec = $masterSls->keyBy('kec_id');
                    }
                    $kecName = $cachedByKec[$kecId]->kecamatan ?? null;
                }
                if ($kecName) {
                    $res .= ", " . ucwords(strtolower((string) $kecName));
                }
            } elseif ($level == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN) {
                $desaId = is_array($d) ? ($d['desa_kel_id'] ?? null) : ($d->desa_kel_id ?? null);
                $desaName = null;
                if (!is_array($d) && $d->relationLoaded('desa') && $d->desa) {
                    $desaName = $d->desa->desa_kel;
                } elseif ($masterSls) {
                    if ($cachedByDesa === null) {
                        $cachedByDesa = $masterSls->keyBy('desa_kel_id');
                    }
                    $desaName = $cachedByDesa[$desaId]->desa_kel ?? null;
                }
                if ($desaName) {
                    $res .= ", " . ucwords(strtolower((string) $desaName));
                }
            }
        }
        return ltrim($res, ", ");
    }
}
