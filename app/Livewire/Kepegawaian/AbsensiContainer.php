<?php

namespace App\Livewire\Kepegawaian;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Traits\AuthorizesFromRoute;

#[Title('Kontrol Absensi')]
class AbsensiContainer extends Component
{
    use AuthorizesFromRoute;

    #[Url]
    public $tab = 'kontrol';

    protected function buildPermission(): string
    {
        return 'view-kepegawaian-absensi';
    }

    public function render()
    {
        $this->authorizeFromRoute();

        if ($this->tab === 'rekap' && !auth()->user()?->hasRole(['Super-Admin', 'Staff-SDM'])) {
            $this->tab = 'kontrol';
        }

        return view('livewire.kepegawaian.absensi-container');
    }
}
