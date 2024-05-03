<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $primaryKey="nip";

    protected function casts(): array
    {
        return [
            'nip' => 'string',
        ];
    }

    public function user(){
        return $this->hasOne(User::class,"email","email");
    }

    public function penugasans(){
        return $this->hasMany(Penugasan::class,"nip","nip");
    }

    public function atasanLangsung(){
        return $this->hasOne(Pegawai::class,"nip","atasan_langsung_id");
    }


}
