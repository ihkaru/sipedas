<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <-- Import ini

class Kemitraan extends Model
{
    use HasFactory;

    protected $fillable = ['mitra_id', 'tahun', 'status'];

    /**
     * Sebuah Kemitraan dimiliki oleh satu Mitra.
     */
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class);
    }
}
