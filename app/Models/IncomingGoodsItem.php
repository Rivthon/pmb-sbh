<?php

namespace App\Models;

use App\Models\Item;
use App\Models\IncomingGoods;
use App\Traits\Models\WithSecureScopeData;
use App\Traits\Models\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncomingGoodsItem extends Model
{
    use HasFactory, WithUuid, WithSecureScopeData;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function item()
    {
        return $this->hasOne(Item::class, 'uuid', 'item_id');
    }

    public function incomingGoods()
    {
        return $this->hasOne(IncomingGoods::class, 'uuid', 'incoming_goods_id');
    }

    public function scopeWhereDateRange($query, $column, $start_date, $end_date)
    {
        $query->when(!empty($start_date), fn ($q) => $q->whereDate($column, '>=', $start_date))
            ->when(!empty($end_date), fn ($q) => $q->whereDate($column, '<=', $end_date));
    }
}
