<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstState extends Model
{
    protected $fillable = ['name', 'code'];

    public $timestamps = false;
}
