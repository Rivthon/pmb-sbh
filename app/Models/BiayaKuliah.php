<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaKuliah extends Model
{
    use HasFactory;

    protected $table = 'biaya_kuliah';

    protected $fillable = [
        'prodi_key',
        'prodi_nama',
        'jumlah_semester',
        'gelombang',
        'semester',
        'biaya',
        'is_visible',
    ];

    protected $casts = [
        'gelombang'       => 'integer',
        'semester'        => 'integer',
        'jumlah_semester' => 'integer',
        'biaya'           => 'integer',
        'is_visible'      => 'boolean',
    ];

    /**
     * Ambil data biaya dalam format yang siap dipakai landing page JS.
     *
     * Output: [
     *   'd3' => ['semesters' => 6, 'nama' => '...', 'gel' => [1 => [12000000, ...], ...]],
     *   'farmasi' => [...],
     * ]
     */
    public static function getForLandingPage(): array
    {
        $all = self::where('is_visible', true)
            ->orderBy('prodi_key')
            ->orderBy('gelombang')
            ->orderBy('semester')
            ->get();

        $result = [];

        foreach ($all->groupBy('prodi_key') as $key => $items) {
            $first = $items->first();
            $gelData = [];

            foreach ($items->groupBy('gelombang') as $gel => $semesterItems) {
                $gelData[$gel] = $semesterItems->sortBy('semester')->pluck('biaya')->values()->toArray();
            }

            $result[$key] = [
                'semesters' => $first->jumlah_semester,
                'nama'      => $first->prodi_nama,
                'gel'       => $gelData,
            ];
        }

        return $result;
    }
}
