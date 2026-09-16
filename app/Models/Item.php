<?php

namespace App\Models;

use App\Traits\Models\WithSecureScopeData;
use App\Traits\Models\WithUuid;
use App\Traits\Utilities\WithCodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory, WithUuid, WithCodeGenerator, WithSecureScopeData;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    const MIN_STOCK_ALERT = 10;

    const UNIQUE_CODE_PREFIX = 'ITM';

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }

    public function category()
    {
        return $this->hasOne(ItemCategory::class, 'uuid', 'item_category_id');
    }
}
