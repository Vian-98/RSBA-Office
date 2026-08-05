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
            $user->hasRole(['Super-Admin', 'Staff-SDM', 'Wakil-Direktur', 'Wadir-SDM-Umum', 'Koordinator'])
            || $user->isKoordinator()
            || $user->can('view-kepegawaian-konfigurasi-jadwal')
        );

        abort_unless(
            $canAccess,
            403,
            "Anda tidak memiliki hak akses ke halaman Konfigurasi Jadwal."
        );

        return view('livewire.kepegawaian.konfigurasi-jadwal');
    }
}
