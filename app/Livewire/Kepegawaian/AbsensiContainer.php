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

        abort_unless(
            auth()->user()?->can('view-kepegawaian-absensi'),
            403,
            'Anda tidak memiliki izin (view-kepegawaian-absensi) untuk mengakses Halaman Kontrol Absensi.'
        );

        return view('livewire.kepegawaian.absensi-container');
    }
}
