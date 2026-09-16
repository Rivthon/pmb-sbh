<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Models\WithSecureScopeData;
use App\Traits\Models\WithUuid;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Utilities\WithCodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory, WithUuid, WithCodeGenerator, WithSecureScopeData;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const UNIQUE_CODE_PREFIX = 'SUP';

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }
}
