<?php

namespace App\Enums;

enum KategoriKerja: string
{
    case SHIFT = 'shift';
    case REGULER = 'reguler';

    public function nama(): string
    {
        return match ($this) {
            self::SHIFT => 'Pekerja Shift',
            self::REGULER => 'Pekerja Reguler (Jam Kantor)',
        };
    }

    public static function options(): array
    {
        return array_map(fn($k) => ['value' => $k->value, 'label' => $k->nama()], self::cases());
    }
}
