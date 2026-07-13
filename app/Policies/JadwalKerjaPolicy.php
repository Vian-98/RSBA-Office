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

        $ruanganIds = $user->getRuanganKoordinatorIds();
        return $ruanganIds !== null && in_array($jadwalKerja->ruangan_id, $ruanganIds);
    }

    public function publish(User $user, JadwalKerja $jadwalKerja): bool
    {
        if (!$user->can('edit-kepegawaian-jadwal-kerja')) {
            return false;
        }

        if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return true;
        }

        $ruanganIds = $user->getRuanganKoordinatorIds();
        return $ruanganIds !== null && in_array($jadwalKerja->ruangan_id, $ruanganIds);
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
        $ruanganIds = $user->getRuanganKoordinatorIds();
        return $ruanganIds !== null && in_array($ruanganIdDetail, $ruanganIds);
    }
}
