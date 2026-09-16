<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSoal extends Model
{
    /** @use HasFactory<\Database\Factories\KategoriSoalFactory> */
    use HasFactory;
    protected $table = 'kategori_soal';
    protected $fillable = ['nama_kategori', 'cerita_bacaan'];

    public function soalTes()
    {
        return $this->hasMany(SoalTes::class, 'kategori_id');
    }
}
