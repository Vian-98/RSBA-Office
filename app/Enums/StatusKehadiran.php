<?php

namespace App\Enums;

enum StatusKehadiran: string
{
    case BELUM_DICEK = 'belum_dicek';
    case HADIR = 'hadir';
    case TERLAMBAT = 'terlambat';
    case PULANG_CEPAT = 'pulang_cepat';
    case TIDAK_HADIR = 'tidak_hadir';
    case CUTI = 'cuti';
    case IZIN = 'izin';

    public function nama(): string
    {
        return match ($this) {
            self::BELUM_DICEK => 'Belum Dicek',
            self::HADIR => 'Hadir',
            self::TERLAMBAT => 'Terlambat',
            self::PULANG_CEPAT => 'Pulang Cepat',
            self::TIDAK_HADIR => 'Tidak Hadir',
            self::CUTI => 'Cuti',
            self::IZIN => 'Izin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BELUM_DICEK => 'gray',
            self::HADIR => 'success',
            self::TERLAMBAT, self::PULANG_CEPAT => 'warning',
            self::TIDAK_HADIR => 'danger',
            self::CUTI, self::IZIN => 'info',
        };
    }
}
