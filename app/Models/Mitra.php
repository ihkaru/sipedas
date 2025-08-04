<?php

namespace App\Models;

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
}
