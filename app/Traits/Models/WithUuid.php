<?php

namespace App\Traits\Models;

use Illuminate\Support\Str;


trait WithUuid
{
    /**
     * Override Boot Method from Model Class.
     * 
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            try {
                $model->uuid = Str::uuid();
            } catch (\Throwable $e) {
                abort(500, $e->getMessage());
            }
        });
    }
}