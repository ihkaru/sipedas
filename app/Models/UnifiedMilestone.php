<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnifiedMilestone extends Model
{
    use HasFactory;

    protected $table = 'unified_milestones';

    protected $fillable = [
        'activity_id',
        'kategori',
        'tanggal',
        'kegiatan',
        'status',
        'pic',
        'attributes_json',
    ];

    protected $casts = [
        'attributes_json' => 'array',
        'tanggal' => 'date:Y-m-d',
    ];
}
