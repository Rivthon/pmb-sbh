<?php

namespace App\Models;

use App\Models\User;
use App\Models\Supplier;
use App\Traits\Models\WithUuid;
use App\Models\IncomingGoodsItem;
use App\Traits\Models\WithSecureScopeData;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Utilities\WithCodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingGoods extends Model
{
    use HasFactory, WithUuid, WithCodeGenerator, WithSecureScopeData;

    protected $guarded = [];
    protected $casts = [
        'incoming_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    const UNIQUE_CODE_PREFIX = 'IN';

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }

    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'uuid', 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(IncomingGoodsItem::class, 'incoming_goods_id', 'uuid');
    }

    public function scopeWhereDateRange($query, $column, $start_date, $end_date)
    {
        $query->when(!empty($start_date), fn ($q) => $q->whereDate($column, '>=', $start_date))
            ->when(!empty($end_date), fn ($q) => $q->whereDate($column, '<=', $end_date));
    }
}
