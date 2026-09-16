<?php

namespace App\Models;

use App\Traits\Models\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerMessage extends Model
{
    use HasFactory, WithUuid;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
