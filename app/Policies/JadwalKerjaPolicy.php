<?php

namespace App\Policies;

use App\Enums\StatusJadwalKerja;
use App\Models\Sdm\JadwalKerja;
use App\Models\Sdm\JadwalTukar;
use App\Models\User;

class JadwalKerjaPolicy
{
    public function generate(User $user): bool
    {
        if ($user->hasRole(['Super-Admin', 'Staff-SDM'])) {
            return true;
        }

        // Hanya Koordinator Ruangan yang ditugaskan (penanggung jawab unit shift/klinis)
        if ($user->isKoordinator()) {
            return true;
        }

        return false;
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

    public function ajukanKabid(User $user, JadwalKerja $jadwalKerja): bool
    {
        if (!in_array($jadwalKerja->status, [StatusJadwalKerja::DRAFT, StatusJadwalKerja::DITOLAK])) {
            return false;
        }

        return $this->kelola($user, $jadwalKerja);
    }

    public function ajukanWadirLangsung(User $user, JadwalKerja $jadwalKerja): bool
    {
        if (!in_array($jadwalKerja->status, [StatusJadwalKerja::DRAFT, StatusJadwalKerja::DITOLAK])) {
            return false;
        }

        return $this->kelola($user, $jadwalKerja);
    }

    public function konfirmasiKabid(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($jadwalKerja->status !== StatusJadwalKerja::MENUNGGU_KABID) {
            return false;
        }

        return $user->hasRole('Super-Admin') || $user->can('approve-jadwal-kabid') || $user->hasRole('Kepala-Bidang');
    }

    public function setujuiWadir(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($jadwalKerja->status !== StatusJadwalKerja::MENUNGGU_WADIR) {
            return false;
        }

        return $user->hasRole('Super-Admin') || $user->can('approve-jadwal-wadir') || $user->hasRole('Wakil-Direktur');
    }

    public function kembalikanDraft(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($jadwalKerja->status === StatusJadwalKerja::MENUNGGU_KABID) {
            return $this->konfirmasiKabid($user, $jadwalKerja);
        }

        if ($jadwalKerja->status === StatusJadwalKerja::MENUNGGU_WADIR) {
            return $this->setujuiWadir($user, $jadwalKerja);
        }

        return false;
    }

    public function publish(User $user, JadwalKerja $jadwalKerja): bool
    {
        return $this->setujuiWadir($user, $jadwalKerja);
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
