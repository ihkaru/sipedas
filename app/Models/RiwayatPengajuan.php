<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPengajuan extends Model
{
    use HasFactory;

    public function penugasan(){
        return $this->belongsTo(Penugasan::class,"id","penugasan_id");
    }
}
