<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'key', 'value'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $maxId = (int) (static::max('id') ?? 0);
                $model->id = $maxId + 1;
            }
        });
    }

    public static function get(string $key, $default = null)
    {
        $val = static::where('key', $key)->value('value');
        if ($val !== null) {
            return $val;
        }

        return match ($key) {
            'st_approval_mode' => config('satker.defaults.st_approval_mode', $default ?? 'delegasi_ppspm'),
            'st_non_sppd_mode' => config('satker.defaults.st_non_sppd_mode', $default ?? 'auto_approve_notify'),
            'whatsapp_notify_st_tembusan_kepala' => config('satker.defaults.st_tembusan_kepala', $default ?? true),
            'ppk_instant_auto_approve' => config('satker.defaults.ppk_instant_auto_approve', $default ?? false),
            default => $default,
        };
    }
}
