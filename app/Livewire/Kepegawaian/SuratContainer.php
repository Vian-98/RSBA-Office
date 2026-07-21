<?php

namespace App\Livewire\Kepegawaian;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Traits\AuthorizesFromRoute;

#[Title('Manajemen Surat Kepegawaian')]
class SuratContainer extends Component
{
    use AuthorizesFromRoute;

    #[Url]
    public $tab = 'cuti-bersama';

    public function render()
    {
        $this->authorizeFromRoute();

        // Validasi tab default berdasarkan permission
        $allowedTabs = [];
        $user = auth()->user();

        if ($user?->can('view-kepegawaian-cuti-bersama') || $user?->hasRole('Super-Admin')) {
            $allowedTabs[] = 'cuti-bersama';
        }
        if ($user?->can('view-kepegawaian-surat-cuti') || $user?->can('create-cuti-other-karyawan') || $user?->hasRole('Super-Admin')) {
            $allowedTabs[] = 'izin-cuti';
        }
        if ($user?->can('view-kepegawaian-surat-sp3') || $user?->hasRole('Super-Admin')) {
            $allowedTabs[] = 'sp3';
        }
        if ($user?->can('view-surat-verification') || $user?->hasRole('Super-Admin')) {
            $allowedTabs[] = 'verifikasi';
        }

        if (!in_array($this->tab, $allowedTabs) && !empty($allowedTabs)) {
            $this->tab = $allowedTabs[0];
        }

        return view('livewire.kepegawaian.surat-container', [
            'allowedTabs' => $allowedTabs,
        ]);
    }
}
