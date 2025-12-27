<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Blameable
{
    public static function bootBlameable()
    {
        static::creating(function ($model) {
            // Only set if not already set (allows manual override if needed)
            if (!$model->created_by && Auth::check()) {
                $model->created_by = Auth::id();
            }
            if (!$model->updated_by && Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }
}
