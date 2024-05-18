<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiHonor extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function suratPerjanjianKerja(){
        return $this->belongsTo(NomorSurat::class,"surat_perjanjian_kerja_id","id");
    }
}
