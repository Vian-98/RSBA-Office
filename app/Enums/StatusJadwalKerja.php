<?php

namespace App\Enums;

enum StatusJadwalKerja: string
{
    case DRAFT = 'draft';
    case MENUNGGU_KABID = 'menunggu_kabid';
    case MENUNGGU_WADIR = 'menunggu_wadir';
    case PUBLISHED = 'published';
    case DITOLAK = 'ditolak';
    case LOCKED = 'locked';

    public function nama(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::MENUNGGU_KABID => 'Menunggu Diketahui Kabid',
            self::MENUNGGU_WADIR => 'Menunggu Disetujui Wadir',
            self::PUBLISHED => 'Dipublikasikan',
            self::DITOLAK => 'Ditolak (Revisi)',
            self::LOCKED => 'Terkunci',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::MENUNGGU_KABID => 'info',
            self::MENUNGGU_WADIR => 'amber',
            self::PUBLISHED => 'success',
            self::DITOLAK => 'rose',
            self::LOCKED => 'purple',
        };
    }

    public function stepIndex(): int
    {
        return match ($this) {
            self::DRAFT, self::DITOLAK => 1,
            self::MENUNGGU_KABID => 2,
            self::MENUNGGU_WADIR => 3,
            self::PUBLISHED, self::LOCKED => 4,
        };
    }
}
