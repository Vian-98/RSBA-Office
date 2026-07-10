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
        return $user->can('edit-kepegawaian-jadwal-kerja');
    }

    public function publish(User $user, JadwalKerja $jadwalKerja): bool
    {
        return $user->can('edit-kepegawaian-jadwal-kerja');
    }

    public function approveTukar(User $user, JadwalTukar $jadwalTukar): bool
    {
        return $user->can('edit-kepegawaian-jadwal-kerja');
    }
}
