<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessUser extends Pivot
{
    use HasUuids, SoftDeletes;

    protected $table = 'business_users';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'business_id',
        'user_id',
        'role',
        'status',
        'meta',
        'joined_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'joined_at' => 'datetime',
    ];

    // Role Constants
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_STAFF = 'staff';
    const ROLE_ACCOUNTANT = 'accountant';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
