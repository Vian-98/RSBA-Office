<?php

namespace App\Enums;

enum KodeAturanJadwal: string
{
    case MAX_SHIFT_MALAM_BERTURUT       = 'max_shift_malam_berturut';       // int, default 3
    case MIN_ISTIRAHAT_JAM              = 'min_istirahat_jam';              // int, default 10
    case MAX_HARI_KERJA_BERTURUT        = 'max_hari_kerja_berturut';        // int, default 6
    case IZINKAN_TUKAR_LINTAS_KATEGORI  = 'izinkan_tukar_lintas_kategori';  // bool, default false

    public function nama(): string
    {
        return match ($this) {
            self::MAX_SHIFT_MALAM_BERTURUT => 'Maks. Shift Malam Berturut-turut',
            self::MIN_ISTIRAHAT_JAM => 'Minimum Jeda Istirahat Antar Shift (jam)',
            self::MAX_HARI_KERJA_BERTURUT => 'Maks. Hari Kerja Berturut-turut',
            self::IZINKAN_TUKAR_LINTAS_KATEGORI => 'Izinkan Tukar Lintas Kategori Kerja',
        };
    }

    public function tipe(): string
    {
        return $this === self::IZINKAN_TUKAR_LINTAS_KATEGORI ? 'bool' : 'int';
    }

    public function defaultNilai(): string
    {
        return match ($this) {
            self::MAX_SHIFT_MALAM_BERTURUT => '3',
            self::MIN_ISTIRAHAT_JAM => '10',
            self::MAX_HARI_KERJA_BERTURUT => '6',
            self::IZINKAN_TUKAR_LINTAS_KATEGORI => '0',
        };
    }
}
