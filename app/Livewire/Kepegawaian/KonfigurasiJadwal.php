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
    public $tab = 'aturan-jadwal';

    public function mount()
    {
        // Cek jika tab koordinator diakses oleh non-admin/non-sdm, kembalikan ke default
        if ($this->tab === 'koordinator' && !auth()->user()?->hasRole(['Super-Admin', 'Staff-SDM'])) {
            $this->tab = 'aturan-jadwal';
        }
    }

    public function updatedTab($value)
    {
        if ($value === 'koordinator' && !auth()->user()?->hasRole(['Super-Admin', 'Staff-SDM'])) {
            $this->tab = 'aturan-jadwal';
        }
    }

    public function render()
    {
        // Izinkan semua koordinator, Staff-SDM, dan Super-Admin
        abort_unless(
            auth()->user()?->isKoordinator(),
            403,
            "Anda tidak memiliki hak akses ke halaman Konfigurasi Jadwal."
        );

        return view('livewire.kepegawaian.konfigurasi-jadwal');
    }
}
