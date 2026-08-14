<?php

namespace App\Enums;

enum TahapApprovalSp3: string
{
    case VERIFIKASI_KEUANGAN = 'verifikasi_keuangan';
    case TTD_ATASAN = 'ttd_atasan';

    public function nama(): string
    {
        return match ($this) {
            self::VERIFIKASI_KEUANGAN => 'Verifikasi Keuangan',
            self::TTD_ATASAN => 'Tanda Tangan Atasan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VERIFIKASI_KEUANGAN => 'info',
            self::TTD_ATASAN => 'success',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn ($tahap) => [
                'value' => $tahap->value,
                'label' => $tahap->nama(),
            ],
            self::cases()
        );
    }
}
