<?php

namespace App\Models\Sdm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PayrollGolonganMatrix extends Model
{
    protected $table = 'sdm_payroll_golongan_matrix';
    protected $guarded = [];

    // Cache key prefix
    const CACHE_KEY = 'payroll_golongan_matrix_cache';

    protected static function booted()
    {
        // Flush cache on any write operations
        static::saved(fn() => self::flushCache());
        static::deleted(fn() => self::flushCache());
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Look up the golongan based on education group and tenure.
     *
     * @param string $kelompok
     * @param float $yearsOfService
     * @return int
     */
    public static function lookup(string $kelompok, float $yearsOfService): int
    {
        $grid = Cache::rememberForever(self::CACHE_KEY, function () {
            $records = self::orderBy('urutan_kelompok', 'asc')
                ->orderBy('masa_kerja_min', 'asc')
                ->get();

            $grouped = [];
            foreach ($records as $rec) {
                $grouped[$rec->kelompok_pendidikan][] = [
                    'min' => (int) $rec->masa_kerja_min,
                    'gol' => (int) $rec->golongan
                ];
            }
            return $grouped;
        });

        $normalizedKelompok = $kelompok;
        if (!isset($grid[$kelompok])) {
            $foundKey = null;
            foreach (array_keys($grid) as $key) {
                if (strtolower(str_replace([' ', '/', '-'], '', $key)) === strtolower(str_replace([' ', '/', '-'], '', $kelompok))) {
                    $foundKey = $key;
                    break;
                }
            }
            $normalizedKelompok = $foundKey ?? array_key_first($grid) ?? 'SMA/SMK';
        }

        $brackets = $grid[$normalizedKelompok] ?? [];
        if (empty($brackets)) {
            return 15; // default fallback
        }

        $selectedGol = 15;
        foreach ($brackets as $b) {
            if ($yearsOfService >= $b['min']) {
                $selectedGol = $b['gol'];
            } else {
                break;
            }
        }

        return $selectedGol;
    }
}
