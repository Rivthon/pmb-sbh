<?php

namespace App\Models;

use App\Models\User;
use App\Models\Kabupaten;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provinsi extends Model
{
     use HasFactory;

    protected $table = 'provinsi';

    protected $fillable = [
        'nama',
    ];

    // Relasi ke Kabupaten
    public function kabupaten()
    {
        return $this->hasMany(Kabupaten::class);
    }
    public function mahasiswa()
    {
        return $this->hasMany(User::class);
    }
}