<?php

namespace App\Enums;

enum StatusKuitansi: string
{
    case PENDING = 'pending';
    case WAITING = 'waiting';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case MANUAL = 'manual';
    case DIBATALKAN = 'dibatalkan';

    public function nama(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::WAITING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Tidak Disetujui',
            self::MANUAL => 'Disetujui Manual',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::WAITING => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::MANUAL => 'warning',
            self::DIBATALKAN => 'danger',
        };
    }

    public function colorHex(): string
    {
        return match ($this) {
            self::PENDING  => '#f59e0b',
            self::WAITING  => '#3b82f6',
            self::APPROVED => '#22c55e',
            self::REJECTED => '#ef4444',
            self::MANUAL   => '#6366f1',
            self::DIBATALKAN => '#991b1b',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn ($status) => [
                'value' => $status->value,
                'label' => $status->nama(),
            ],
            self::cases()
        );
    }
}
