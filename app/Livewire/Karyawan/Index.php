<?php

namespace App\Livewire\Karyawan;

use App\Traits\AuthorizesFromRoute;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

use App\Exports\KaryawanExport;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Karyawan')]
#[Lazy]
class Index extends Component
{
    use AuthorizesFromRoute;
    use Interactions;

    public $content = 'all';

    public function navigateTo($route)
    {
        $this->content = $route;
    }

    public function downloadKaryawan()
    {
        return Excel::download(new KaryawanExport, 'data_karyawan_' . now()->format('Y-m-d') . '.xlsx');
    }

    public function render()
    {
        $this->authorizeFromRoute();
        return view('livewire.karyawan.index');
    }
}
