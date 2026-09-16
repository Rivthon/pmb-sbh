<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agama extends Model
{
    protected $table = 'agama';

    // Field yang dapat diisi
    protected $fillable = [
        'nama_agama',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'agama_id');
    }

}
