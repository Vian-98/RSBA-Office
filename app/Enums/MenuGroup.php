<?php

namespace App\Enums;

enum MenuGroup: string
{
    case NULL = '';
    case ADMIN = 'admin';
    case ADM = 'adm';
    case SDM = 'sdm';
    case UMU = 'umu';
    case KEU = 'keu';
    case FAR = 'far';
    case KEP = 'kep';
    case DOK = 'dok';
    case IPS = 'ips';
    case IT = 'it';

    public function nama(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::ADM => 'Administrasi',
            self::SDM => 'Kepegawaian',
            self::UMU => 'Umum',
            self::KEU => 'Keuangan',
            self::FAR => 'Farmasi',
            self::KEP => 'Keperawatan',
            self::DOK => 'Dokter',
            self::IPS => 'IPSRS',
            self::IT => 'IT',
            default => '',
        };
    }


    public static function options(): array
    {
        return array_map(
            fn($status) => [
                'value' => $status->value,
                'label' => $status->nama()
            ],
            self::cases()
        );
    }
}
