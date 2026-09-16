<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kuesioner extends Model
{
    use HasFactory;
    protected $table = 'kuesioners';

    // Field yang dapat diisi
    protected $fillable = [
        'nama_kusioner',
    ];
    // Di model Kuesioner.php
    public function users() {
        return $this->hasMany(User::class, 'kuesioner_id');
    }

    public function periode() {
        return $this->belongsTo(Periode::class);
    }

    public function gelombang() {
        return $this->belongsTo(Gelombang::class);
    }
}
