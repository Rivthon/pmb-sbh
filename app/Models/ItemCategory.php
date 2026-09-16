<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Models\WithSecureScopeData;
use App\Traits\Models\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemCategory extends Model
{
    use HasFactory, WithUuid, WithSecureScopeData;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }
}
