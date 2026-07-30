<?php

namespace App\Enums;

enum StatusApproval: string
{
    case PENDING = 'pending';
    case WAITING = 'waiting';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case MANUAL = 'manual';

    public function nama(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::WAITING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Tidak Disetujui',
            self::MANUAL => 'Disetujui Manual',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::WAITING => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::MANUAL => 'warning'
        };
    }

    public function colorHex(): string
    {
        return match ($this) {
            self::PENDING  => '#f59e0b', // warning - amber
            self::WAITING  => '#3b82f6', // info - blue
            self::APPROVED => '#22c55e', // success - green
            self::REJECTED => '#ef4444', // danger - red
            self::MANUAL   => '#6366f1', // primary - indigo
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
