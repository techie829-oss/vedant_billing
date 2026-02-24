<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'whatsapp_number',
        'message',
        'status',
        'notes',
    ];
}
