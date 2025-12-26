<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InUser extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'in_users';

    protected $fillable = [
        'user_id',
        'access_level',
        'permissions',
        'status',
        'meta',
        'last_access_at',
    ];

    protected $casts = [
        'permissions' => 'array',
        'meta' => 'array',
        'last_access_at' => 'datetime',
    ];

    // Access Levels
    const LEVEL_SUPER_ADMIN = 'super_admin';
    const LEVEL_SUPPORT = 'support';
    const LEVEL_OPS = 'ops';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
