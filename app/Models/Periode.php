<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periode extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['tgl_mulai', 'deskripsi', 'status_periode', 'tanggal_tes', 'linked'];

    public function gelombangs()
    {
        return $this->hasMany(Gelombang::class, 'periode_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
