<?php

namespace App\Policies;

use App\Models\Sdm\Bagian;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalTukar;
use App\Models\User;

class JadwalKerjaPolicy
{
    public function generate(User $user): bool
    {
        return $user->can('add-kepegawaian-jadwal-kerja');
    }

    public function kelola(User $user, JadwalKerja $jadwalKerja): bool
    {
        if (!$user->can('edit-kepegawaian-jadwal-kerja')) {
            return false;
        }

        if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return true;
        }

        return ($user->karyawan->ruangan_id ?? 0) === $jadwalKerja->ruangan_id;
    }

    public function publish(User $user, JadwalKerja $jadwalKerja): bool
    {
        if (!$user->can('edit-kepegawaian-jadwal-kerja')) {
            return false;
        }

        if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return true;
        }

        return ($user->karyawan->ruangan_id ?? 0) === $jadwalKerja->ruangan_id;
    }

    public function approveTukar(User $user, JadwalTukar $jadwalTukar): bool
    {
        if (!$user->can('edit-kepegawaian-jadwal-kerja')) {
            return false;
        }

        if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return true;
        }

        // Restrict based on the ruangan of the schedule details being swapped
        $ruanganIdDetail = $jadwalTukar->detailPemohon?->jadwalKerja?->ruangan_id;
        return ($user->karyawan->ruangan_id ?? 0) === $ruanganIdDetail;
    }
}
