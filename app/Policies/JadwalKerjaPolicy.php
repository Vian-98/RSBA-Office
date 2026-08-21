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
        if ($user->isSuperAdmin() || $user->can('add-kepegawaian-jadwal-kerja')) {
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
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->can('edit-kepegawaian-jadwal-kerja')) {
            return false;
        }

        $ruanganIds = $user->getRuanganKoordinatorIds();
        if ($ruanganIds === null) {
            return true;
        }

        if (in_array($jadwalKerja->ruangan_id, $ruanganIds)) {
            return true;
        }

        if ($user->karyawan?->ruangan_id === $jadwalKerja->ruangan_id) {
            return true;
        }

        return false;
    }

    public function ajukanKabid(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!in_array($jadwalKerja->status, [StatusJadwalKerja::DRAFT, StatusJadwalKerja::DITOLAK])) {
            return false;
        }

        return $this->kelola($user, $jadwalKerja);
    }

    public function ajukanWadirLangsung(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!in_array($jadwalKerja->status, [StatusJadwalKerja::DRAFT, StatusJadwalKerja::DITOLAK])) {
            return false;
        }

        return $this->kelola($user, $jadwalKerja);
    }

    public function konfirmasiKabid(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($jadwalKerja->status !== StatusJadwalKerja::MENUNGGU_KABID) {
            return false;
        }

        return $user->can('approve-jadwal-kabid') || $user->isKepalaDept();
    }

    public function setujuiWadir(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($jadwalKerja->status !== StatusJadwalKerja::MENUNGGU_WADIR) {
            return false;
        }

        return $user->can('approve-jadwal-wadir') || $user->isWadir();
    }

    public function kembalikanDraft(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

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
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->setujuiWadir($user, $jadwalKerja);
    }

    public function approveTukar(User $user, JadwalTukar $jadwalTukar): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (!$user->can('edit-kepegawaian-jadwal-kerja')) {
            return false;
        }

        $ruanganIds = $user->getRuanganKoordinatorIds();
        if ($ruanganIds === null) {
            return true;
        }

        // Restrict based on the ruangan of the schedule details being swapped
        $ruanganIdDetail = $jadwalTukar->detailPemohon?->jadwalKerja?->ruangan_id;
        return in_array($ruanganIdDetail, $ruanganIds);
    }
}
