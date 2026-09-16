<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jurusan extends Model
{
    use HasFactory, SoftDeletes;

    // Nama tabel, jika berbeda dengan nama default (yakni nama model jamak)
    protected $table = 'jurusan';

    // Field yang dapat diisi
    protected $fillable = [
        'kd_jurusan',
        'nama_jurusan',
        'deskripsi',
    ];
    public function mahasiswaBaru()
    {
        return $this->hasMany(User::class, 'jurusan_id');
    }
    public function wawancaraPmb()
    {
        return $this->hasMany(WawancaraPmb::class, 'jurusan_id');
    }

    public function interviewers()
    {
        return $this->hasMany(Admin::class, 'jurusan_id');
    }

    public function interviewPoolKey(): string
    {
        $name = strtoupper((string) $this->nama_jurusan);

        return str_contains($name, 'S1') && str_contains($name, 'FARMASI')
            ? 's1_farmasi'
            : 'jurusan_' . $this->getKey();
    }

    public function interviewPoolLabel(): string
    {
        return $this->interviewPoolKey() === 's1_farmasi'
            ? 'S1 Farmasi'
            : (string) $this->nama_jurusan;
    }

    public function isD3Kebidanan(): bool
    {
        $name = strtoupper((string) $this->nama_jurusan);
        $description = strtoupper((string) $this->deskripsi);
        $combined = $name . ' ' . $description;

        return str_contains($combined, 'KEBIDANAN')
            && (
                str_contains($combined, 'D3')
                || str_contains($combined, 'DIII')
                || str_contains($combined, 'DIPLOMA TIGA')
            );
    }

    public function interviewPoolIds(): array
    {
        if ($this->interviewPoolKey() !== 's1_farmasi') {
            return [(int) $this->getKey()];
        }

        return self::query()
            ->whereRaw('UPPER(nama_jurusan) LIKE ?', ['%S1%FARMASI%'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function interviewAccountOptions()
    {
        return self::query()
            ->orderBy('nama_jurusan')
            ->get(['id', 'nama_jurusan'])
            ->reject(function (self $jurusan): bool {
                $name = strtoupper((string) $jurusan->nama_jurusan);

                return str_contains($name, 'S1')
                    && str_contains($name, 'FARMASI')
                    && str_contains($name, 'KARYAWAN');
            })
            ->values();
    }
}
