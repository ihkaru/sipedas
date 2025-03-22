<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Microsite extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'theme',
        'is_active'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(MicrositeLink::class)->orderBy('order');
    }
}
