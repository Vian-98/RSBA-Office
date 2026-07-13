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

    protected function buildPermission(): string
    {
        return 'view-kepegawaian-konfigurasi-jadwal';
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.kepegawaian.konfigurasi-jadwal');
    }
}
