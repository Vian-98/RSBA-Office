<?php

namespace App\Services;

use App\Enums\KodeAturanJadwal;
use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalAturan;
use App\Models\Sdm\JadwalShift;
use Illuminate\Database\Eloquent\Collection;

class AturanJadwalService
{
    public function get(int $bagianId, KodeAturanJadwal $kode): int|bool
    {
        $nilai = JadwalAturan::where('bagian_id', $bagianId)
            ->where('kode', $kode->value)
            ->where('aktif', true)
            ->value('nilai') ?? $kode->defaultNilai();

        return $kode->tipe() === 'bool' ? (bool) $nilai : (int) $nilai;
    }

    /**
     * Kembalikan daftar RuanganShift (pivot) aktif untuk ruangan ini.
     * Fallback: jika ruangan belum dikonfigurasi, kembalikan semua shift aktif (tanpa override).
     */
    public function shiftValidUntukRuangan(int $ruanganId): Collection
    {
        $ruanganShifts = \App\Models\Sdm\RuanganShift::where('ruangan_id', $ruanganId)
            ->with('shift')
            ->get()
            ->filter(fn($rs) => $rs->shift && $rs->shift->aktif);

        if ($ruanganShifts->isEmpty()) {
            // Fallback: bungkus shift global ke dalam objek sementara
            return JadwalShift::where('aktif', true)->get()->map(function ($shift) use ($ruanganId) {
                $rs = new \App\Models\Sdm\RuanganShift();
                $rs->ruangan_id = $ruanganId;
                $rs->shift_id   = $shift->id;
                $rs->setRelation('shift', $shift);
                return $rs;
            });
        }

        return $ruanganShifts->values();
    }
}
