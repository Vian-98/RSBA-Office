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
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass') || $user->can('approve-jadwal-wadir') || $user->can('add-kepegawaian-jadwal-kerja')) {
            return true;
        }

        // Koordinator Ruangan yang memiliki ruangan penugasan
        if ($user->isKoordinator()) {
            return true;
        }

        return false;
    }

    public function view(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass') || $user->can('approve-jadwal-wadir') || $user->can('view-kepegawaian-laporan')) {
            return true;
        }

        $accessible = $user->getAccessibleRuanganIds('view');
        if ($accessible === null) {
            return true;
        }

        if (in_array((int)$jadwalKerja->ruangan_id, $accessible, true)) {
            // Jika user adalah koordinator atau pejabat struktural bidang, boleh melihat draft & revisi
            if ($user->can('approve-jadwal-kabid') || $user->isKoordinator() || $user->can('edit-kepegawaian-jadwal-kerja')) {
                return true;
            }
            // Jika staf/dokter biasa, hanya boleh melihat jika published atau locked
            return in_array($jadwalKerja->status, [StatusJadwalKerja::PUBLISHED, StatusJadwalKerja::LOCKED], true);
        }

        return false;
    }

    public function kelola(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass') || $user->can('approve-jadwal-wadir')) {
            return true;
        }

        // Jika KaBid, cek apakah jadwal berada di bawah bagian aktifnya
        if ($user->can('approve-jadwal-kabid') || $user->isKepalaDept()) {
            $bagianIds = $user->getActiveBagianIds();
            $jadwalBagianId = $jadwalKerja->bagian_id ?? $jadwalKerja->ruangan?->bagian_id;
            if ($jadwalBagianId && in_array((int)$jadwalBagianId, $bagianIds, true)) {
                return true;
            }
            $deptRuanganIds = $user->getBagianScopedRuanganIds() ?? [];
            if (in_array((int)$jadwalKerja->ruangan_id, $deptRuanganIds, true)) {
                return true;
            }
        }

        // Jika Koordinator Ruangan
        if ($user->isKoordinator()) {
            $koorRuangans = $user->getRuanganKoordinatorIdsOnly();
            if (in_array((int)$jadwalKerja->ruangan_id, $koorRuangans, true)) {
                return true;
            }
        }

        return false;
    }

    public function ajukanKabid(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass')) {
            return true;
        }

        if (!in_array($jadwalKerja->status, [StatusJadwalKerja::DRAFT, StatusJadwalKerja::DITOLAK])) {
            return false;
        }

        return $this->kelola($user, $jadwalKerja);
    }

    public function ajukanWadirLangsung(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass')) {
            return true;
        }

        if (!in_array($jadwalKerja->status, [StatusJadwalKerja::DRAFT, StatusJadwalKerja::DITOLAK])) {
            return false;
        }

        return $this->kelola($user, $jadwalKerja);
    }

    public function konfirmasiKabid(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass')) {
            return true;
        }

        if ($jadwalKerja->status !== StatusJadwalKerja::MENUNGGU_KABID) {
            return false;
        }

        if (!$user->can('approve-jadwal-kabid') && !$user->isKepalaDept()) {
            return false;
        }

        // Validasi bahwa jadwal berada di bawah bagian yang dinaungi oleh KaBid ini
        $bagianIds = $user->getActiveBagianIds();
        $jadwalBagianId = $jadwalKerja->bagian_id ?? $jadwalKerja->ruangan?->bagian_id;
        if ($jadwalBagianId && in_array((int)$jadwalBagianId, $bagianIds, true)) {
            return true;
        }
        $deptRuanganIds = $user->getBagianScopedRuanganIds() ?? [];
        return in_array((int)$jadwalKerja->ruangan_id, $deptRuanganIds, true);
    }

    public function setujuiWadir(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass')) {
            return true;
        }

        if ($jadwalKerja->status !== StatusJadwalKerja::MENUNGGU_WADIR) {
            return false;
        }

        return $user->can('approve-jadwal-wadir') || $user->isWadir();
    }

    public function kembalikanDraft(User $user, JadwalKerja $jadwalKerja): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass')) {
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
        return $this->setujuiWadir($user, $jadwalKerja);
    }

    public function approveTukar(User $user, JadwalTukar $jadwalTukar): bool
    {
        if ($user->isSuperAdmin() || $user->can('super-admin-bypass') || $user->can('approve-jadwal-wadir')) {
            return true;
        }

        $ruanganIdDetail = $jadwalTukar->detailPemohon?->jadwalKerja?->ruangan_id;
        if (!$ruanganIdDetail) {
            return false;
        }

        return $user->canManageRuangan($ruanganIdDetail);
    }
}
