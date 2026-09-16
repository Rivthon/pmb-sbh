<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingInfoCard extends Model
{
    use HasFactory;

    public const SECTION_ADVANTAGES = 'advantages';
    public const SECTION_COST_INFO = 'cost_info';

    protected $fillable = [
        'section', 'title', 'description', 'items', 'icon', 'color', 'variant',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
