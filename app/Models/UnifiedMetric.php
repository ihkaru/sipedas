<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnifiedMetric extends Model
{
    use HasFactory;

    protected $table = 'unified_metrics';

    protected $fillable = [
        'activity_id',
        'metric_id',
        'label',
        'target',
        'completed',
        'worked',
        'percentage',
        'context_json',
    ];

    protected $casts = [
        'context_json' => 'array',
    ];
}
