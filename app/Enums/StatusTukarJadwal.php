<?php

namespace App\Enums;

enum StatusTukarJadwal: string
{
    case MENUNGGU_KONFIRMASI_DOKTER = 'MENUNGGU_KONFIRMASI_DOKTER';
    case DITOLAK_DOKTER             = 'DITOLAK_DOKTER';
    case MENUNGGU_WADIR             = 'MENUNGGU_WADIR';
    case DITOLAK_WADIR              = 'DITOLAK_WADIR';
    case DISETUJUI                  = 'DISETUJUI';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_KONFIRMASI_DOKTER => 'Menunggu Konfirmasi Dokter B',
            self::DITOLAK_DOKTER             => 'Ditolak Dokter B',
            self::MENUNGGU_WADIR             => 'Menunggu Approval Wadir',
            self::DITOLAK_WADIR              => 'Ditolak Wadir',
            self::DISETUJUI                  => 'Disetujui (Shift Tertukar)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MENUNGGU_KONFIRMASI_DOKTER => 'amber',
            self::DITOLAK_DOKTER             => 'rose',
            self::MENUNGGU_WADIR             => 'blue',
            self::DITOLAK_WADIR              => 'red',
            self::DISETUJUI                  => 'emerald',
        };
    }
}
