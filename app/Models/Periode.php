<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periode extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['tgl_mulai', 'deskripsi', 'status_periode', 'tanggal_tes', 'linked'];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tanggal_tes' => 'date',
    ];

    public function getAcademicYearLabelAttribute(): string
    {
        $description = trim((string) $this->deskripsi);

        if (preg_match('/\b(20\d{2})\s*[\/-]\s*(20\d{2})\b/', $description, $matches)) {
            return $matches[1] . '/' . $matches[2];
        }

        if (preg_match('/\b(\d{2})\s*[\/-]\s*(\d{2})\b/', $description, $matches)) {
            return '20' . $matches[1] . '/20' . $matches[2];
        }

        if (!$this->tgl_mulai) {
            return $description ?: '-';
        }

        $year = $this->tgl_mulai->year;

        return $year . '/' . ($year + 1);
    }

    public function gelombangs()
    {
        return $this->hasMany(Gelombang::class, 'periode_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
