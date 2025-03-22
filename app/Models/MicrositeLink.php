<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MicrositeLink extends Model
{
    use HasFactory;
    protected $fillable = [
        'microsite_id',
        'title',
        'url',
        'icon',
        'order',
        'is_active'
    ];

    public function microsite(): BelongsTo
    {
        return $this->belongsTo(Microsite::class);
    }
}
