<?php

namespace App\Models\Sdm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalApprovalLog extends Model
{
    protected $table = 'sdm_jadwal_approval_log';
    protected $guarded = [];

    // ─── Aksi Constants ───────────────────────────────────────────────────────
    const AKSI_DRAFT_CREATED       = 'DRAFT_CREATED';
    const AKSI_AJUKAN_KABID        = 'AJUKAN_KABID';
    const AKSI_AJUKAN_WADIR        = 'AJUKAN_WADIR';
    const AKSI_DIKETAHUI_KABID     = 'DIKETAHUI_KABID';
    const AKSI_DISETUJUI_WADIR     = 'DISETUJUI_WADIR';
    const AKSI_REVISI_DRAFT        = 'REVISI_DRAFT';
    const AKSI_EDIT_PASCA_PUBLISH  = 'EDIT_PASCA_PUBLISH';

    // ─── Relations ────────────────────────────────────────────────────────────
    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Return human-readable label for aksi.
     */
    public function aksiLabel(): string
    {
        return match ($this->aksi) {
            self::AKSI_DRAFT_CREATED       => 'Draf Dibuat',
            self::AKSI_AJUKAN_KABID        => 'Diajukan ke Kepala Bidang',
            self::AKSI_AJUKAN_WADIR        => 'Diajukan ke Wakil Direktur',
            self::AKSI_DIKETAHUI_KABID     => 'Diketahui / Disetujui Kabid',
            self::AKSI_DISETUJUI_WADIR     => 'Disetujui Wadir & Dipublikasikan',
            self::AKSI_REVISI_DRAFT        => 'Dikembalikan ke Draf (Revisi)',
            self::AKSI_EDIT_PASCA_PUBLISH  => 'Diedit Pasca Publish',
            default                        => $this->aksi,
        };
    }

    /**
     * Return icon name for timeline display.
     */
    public function aksiIcon(): string
    {
        return match ($this->aksi) {
            self::AKSI_DRAFT_CREATED       => 'tabler.file-plus',
            self::AKSI_AJUKAN_KABID        => 'tabler.send',
            self::AKSI_AJUKAN_WADIR        => 'tabler.send-2',
            self::AKSI_DIKETAHUI_KABID     => 'tabler.check',
            self::AKSI_DISETUJUI_WADIR     => 'tabler.checks',
            self::AKSI_REVISI_DRAFT        => 'tabler.arrow-back-up',
            self::AKSI_EDIT_PASCA_PUBLISH  => 'tabler.pencil',
            default                        => 'tabler.clock',
        };
    }

    /**
     * Return Tailwind color class for badge/icon.
     */
    public function aksiColor(): string
    {
        return match ($this->aksi) {
            self::AKSI_DRAFT_CREATED       => 'slate',
            self::AKSI_AJUKAN_KABID        => 'blue',
            self::AKSI_AJUKAN_WADIR        => 'indigo',
            self::AKSI_DIKETAHUI_KABID     => 'amber',
            self::AKSI_DISETUJUI_WADIR     => 'emerald',
            self::AKSI_REVISI_DRAFT        => 'rose',
            self::AKSI_EDIT_PASCA_PUBLISH  => 'violet',
            default                        => 'slate',
        };
    }

    /**
     * Return icon bg Tailwind class for timeline dot.
     */
    public function aksiIconBg(): string
    {
        return match ($this->aksi) {
            self::AKSI_DRAFT_CREATED       => 'bg-slate-400',
            self::AKSI_AJUKAN_KABID        => 'bg-blue-500',
            self::AKSI_AJUKAN_WADIR        => 'bg-indigo-500',
            self::AKSI_DIKETAHUI_KABID     => 'bg-amber-500',
            self::AKSI_DISETUJUI_WADIR     => 'bg-emerald-500',
            self::AKSI_REVISI_DRAFT        => 'bg-rose-500',
            self::AKSI_EDIT_PASCA_PUBLISH  => 'bg-violet-500',
            default                        => 'bg-slate-400',
        };
    }

    /**
     * Return human-readable status label.
     */
    public static function statusLabel(?string $status): string
    {
        if (!$status) return '-';
        return match ($status) {
            'draft'           => 'Draf',
            'menunggu_kabid'  => 'Menunggu Kabid',
            'menunggu_wadir'  => 'Menunggu Wadir',
            'published'       => 'Dipublikasikan',
            'ditolak'         => 'Dikembalikan (Revisi)',
            'locked'          => 'Terkunci',
            default           => $status,
        };
    }
}
