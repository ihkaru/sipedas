<?php

namespace App\Models;

use App\Models\Sipancong\Pengajuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionToken extends Model
{
    use HasFactory;
    protected $fillable = ['pengajuan_id', 'token', 'action', 'expires_at', 'used_at'];
    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function pengajuan() // <-- 2. TAMBAHKAN METODE INI
    {
        // Parameter kedua ('pengajuan_id') bersifat opsional jika Anda mengikuti konvensi Laravel,
        // tapi menuliskannya membuat kode lebih jelas.
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}
