<?php

namespace App\Models\Sipancong;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusPembayaran extends Model
{
    use HasFactory;
    protected $table = 'sp_status_pembayaran';
    protected $guarded = [];
}
