<?php

namespace App\Livewire\Kepegawaian;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Traits\AuthorizesFromRoute;

#[Title('Konfigurasi Jadwal')]
class KonfigurasiJadwal extends Component
{
    use AuthorizesFromRoute;

    #[Url]
    public $tab = 'master-shift';

    public function mount()
    {
        $this->tab = 'master-shift';
    }

    public function render()
    {
        $user = auth()->user();
        $canAccess = $user && (
            $user->isSuperAdmin()
            || $user->isKoordinator()
            || $user->can('view-kepegawaian-konfigurasi-jadwal')
        );

        abort_unless(
            $canAccess,
            403,
            "Akses Ditolak: Anda belum memiliki hak akses ke halaman Konfigurasi Jadwal. Fitur ini memerlukan wewenang Koordinator Ruangan atau izin khusus (view-kepegawaian-konfigurasi-jadwal)."
        );

        return view('livewire.kepegawaian.konfigurasi-jadwal');
    }
}
