<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gelombang extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['periode_id', 'nama_gelombang', 'tgl_mulai', 'tgl_selesai', 'status_gelombang'];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
