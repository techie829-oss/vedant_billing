<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstMaster extends Model
{
    protected $fillable = [
        'gstin',
        'legal_name',
        'trade_name',
        'address',
        'status',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
