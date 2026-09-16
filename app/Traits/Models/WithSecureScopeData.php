<?php

namespace App\Traits\Models;

use App\Models\User;

trait WithSecureScopeData
{
    public function scopeFilterByUser($query)
    {
        return $query->when(auth()->user()->role == User::USER_ROLE, function ($q) {
            $q->where('user_id', auth()->user()->uuid);
        });
    }

    public function scopeFilterChainingByUser($query, string $relation_name)
    {
        return $query->when(auth()->user()->role == User::USER_ROLE, function ($que) use ($relation_name) {
            $que->whereHas($relation_name, fn ($q) => $q->where('user_id', auth()->user()->uuid));
        });
    }
}